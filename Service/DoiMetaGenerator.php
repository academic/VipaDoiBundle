<?php

namespace Ojs\DoiBundle\Service;

use Ojs\JournalBundle\Entity\Article;
use Ojs\DoiBundle\Entity\CrossrefConfig;
use Ojs\DoiBundle\Object\DoiBatch;
use Ojs\DoiBundle\Object\DoiData;
use Ojs\DoiBundle\Object\PersonName;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Router;

class DoiMetaGenerator
{
    /**
     * @var DoiGenerator
     */
    protected $doiGenerator;

    /**
     * @var Router
     */
    protected $router;

    /**
     * DoiMetaGenerator constructor.
     * @param DoiGenerator $doiGenerator
     * @param Router $router
     */
    public function __construct(DoiGenerator $doiGenerator, Router $router)
    {
        $this->doiGenerator = $doiGenerator;
        $this->router       = $router;
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
        $doi->head->depositor->name = $crossrefConfig->getFullName();

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
            $doi->body->journal->journalIssue->issue = $accessor->getValue($article, 'issue.number');

            if ($article->getIssue()->getVolume() != "") {
                $volume = $accessor->getValue($article, 'issue.volume');
                $doi->body->journal->journalIssue->journalVolume->volume = $volume;
            } else {
                $doi->body->journal->journalIssue->journalVolume = null;
            }

            $doi->body->journal->journalIssue->publicationDate->setDate(
                $accessor->getValue($article, 'issue.datePublished')
            );
        }
        else {
            $doi->body->journal->journalIssue = null;
        }

        $doi->body->journal->journalArticle->publicationDate->setDate($accessor->getValue($article, 'pubdate'));
        $doi->body->journal->journalArticle->language = strtolower($article->getPrimaryLanguage());

        if ($article->getFirstPage() == null && $article->getLastPage() == null) {
            $doi->body->journal->journalArticle->pages = null;
        } else {
            $first = $article->getFirstPage() !== null ? $article->getFirstPage() : null;
            $last = $article->getFirstPage() !== null ? $article->getFirstPage() : null;

            $doi->body->journal->journalArticle->pages->firstPage = $first;
            $doi->body->journal->journalArticle->pages->lastPage = $last;
        }

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

        $doiData->resource = $this->router->generate('site_shortlink_doi', ['doi' => $doiData->doi], Router::ABSOLUTE_URL);
        $doi->body->journal->journalArticle->doiData = $doiData;

        return $doi;
    }
}
