<?php

namespace BulutYazilim\OjsDoiBundle\Service;

use Ojs\JournalBundle\Entity\Article;
use BulutYazilim\OjsDoiBundle\Entity\CrossrefConfig;
use BulutYazilim\OjsDoiBundle\Object\DoiBatch;
use BulutYazilim\OjsDoiBundle\Object\DoiData;
use BulutYazilim\OjsDoiBundle\Object\PersonName;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Router;

class DoiMetaGenerator
{
    /** @var DoiGenerator */
    protected $doiGenerator;

    /** @var Router */
    protected $router;

    /**
     * DoiMetaGenerator constructor.
     * @param DoiGenerator $doiGenerator
     * @param Router $router
     */
    public function __construct(DoiGenerator $doiGenerator, Router $router)
    {
        $this->doiGenerator = $doiGenerator;
        $this->router = $router;
    }

    /**
     * @param Article $article
     * @param CrossrefConfig $crossrefConfig
     * @return DoiBatch
     */
    public function getArticle(Article $article, CrossrefConfig $crossrefConfig)
    {
        $doi = new DoiBatch();

        $accessor = PropertyAccess::createPropertyAccessor();
        $doi->head->doiBatchId = 'article_'.$article->getId().'_'.time();
        $doi->head->registrant = $accessor->getValue($article, 'journal.publisher.name');
        $doi->head->depositor->emailAddress = $crossrefConfig->getEmail();
        $doi->head->depositor->depositorName = $crossrefConfig->getFullName();

        $doi->body->journal->journalMetadata->fullTitle = $accessor->getValue($article, 'journal.title');
        if(!empty($accessor->getValue($article, 'journal.issn'))) {
            $doi->body->journal->journalMetadata->issn->value = $accessor->getValue($article, 'journal.issn');
        }
        elseif(!empty($accessor->getValue($article, 'journal.eissn'))) {
            $doi->body->journal->journalMetadata->issn->value = $accessor->getValue($article, 'journal.eissn');
            $doi->body->journal->journalMetadata->issn->mediaType = 'electronic';
        }
        else {
            $doi->body->journal->journalMetadata->issn = null;
        }


        if($article->getIssue()) {
            $doi->body->journal->journalIssue->issue = $accessor->getValue($article, 'issue.id');
            $doi->body->journal->journalIssue->journalVolume->volume = $accessor->getValue($article, 'issue.volume');
            $doi->body->journal->journalIssue->publicationDate->setDate(
                $accessor->getValue($article, 'issue.datePublished')
            );
        }
        else {
            $doi->body->journal->journalIssue = null;
        }

        $doi->body->journal->journalArticle->publicationDate->setDate($accessor->getValue($article, 'pubdate'));
        $doi->body->journal->journalArticle->language = $article->getPrimaryLanguage();
        $doi->body->journal->journalArticle->pages->firstPage = $article->getFirstPage();
        $doi->body->journal->journalArticle->pages->lastPage = $article->getLastPage();
        $doi->body->journal->journalArticle->titles->add($article->getTitle());

        $k = 0;
        foreach ($article->getArticleAuthors() as $author) {
            $person = new PersonName();
            if (0 === $k) {
                $person->sequence = "first";
            }
            $person->givenName = $accessor->getValue($author, 'author.firstName');
            $person->surname = $accessor->getValue($author, 'author.lastName');
            $doi->body->journal->journalArticle->contributors->add($person);
            $k++;
        }

        $doiData = new DoiData();
        $doiData->doi = $this->doiGenerator->generate($article);

        $routeParams = array(
            'publisher' => $article->getJournal()->getPublisher()->getSlug(),
            'article_id' => $article->getId(),
            'slug' => $article->getJournal()->getSlug()
        );
        if($article->getIssue()) {
            $routeParams['issue_id'] = $article->getIssue()->getId();
        }
        $routeName = $article->getIssue()?'ojs_article_page':'ojs_article_withoutIssue_page';

        $doiData->resource = $this->router->generate($routeName, $routeParams, Router::ABSOLUTE_URL);
        $doi->body->journal->journalArticle->doiData = $doiData;


        return $doi;
    }
}
