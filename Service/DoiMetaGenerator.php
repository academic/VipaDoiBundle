<?php

namespace OkulBilisim\OjsDoiBundle\Service;

use Ojs\JournalBundle\Entity\Article;
use Ojs\JournalBundle\Entity\ArticleTranslation;
use OkulBilisim\OjsDoiBundle\Entity\CrossrefConfig;
use OkulBilisim\OjsDoiBundle\Object\DoiBatch;
use OkulBilisim\OjsDoiBundle\Object\Person;

class DoiMetaGenerator
{
    /**
     * @param Article $article
     * @param CrossrefConfig $crossrefConfig
     * @return DoiBatch
     */
    public function getArticle(Article $article, CrossrefConfig $crossrefConfig)
    {
        $doi = new DoiBatch();

        $doi->head->doiBatchId = 'article_'.$article->getId().'_'.time();
        $doi->head->registrant = $article->getJournal()->getPublisher()->getName();
        $doi->head->depositor->emailAddress = $crossrefConfig->getEmail();
        $doi->head->depositor->name = $crossrefConfig->getFullName();

        $doi->body->journal->journalMetadata->fullTitle = $article->getJournal()->getTitle();
        $doi->body->journal->journalMetadata->issn->value = $article->getJournal()->getIssn();

        $doi->body->journal->journalIssue->issue = $article->getIssue()->getId();
        $doi->body->journal->journalIssue->journalVolume->volume = $article->getIssue()->getVolume();
        $doi->body->journal->journalIssue->publicationDate->setDate($article->getIssue()->getDatePublished());

        $doi->body->journal->journalArticle->publicationDate->setDate($article->getPubdate());
        $doi->body->journal->journalArticle->language = $article->getPrimaryLanguage();
        $doi->body->journal->journalArticle->pages->firstPage = $article->getFirstPage();
        $doi->body->journal->journalArticle->pages->lastPage = $article->getLastPage();


        /** @var ArticleTranslation[] $translations */
        $translations = $article->getTranslations();
        foreach ($translations as $translation) {
            $doi->body->journal->journalArticle->titles->add($translation->getTitle());
        }
        $k = 0;
        foreach ($article->getArticleAuthors() as $author) {
            $person = new Person();
            if (0 === $k) {
                $person->sequence = "first";
            }
            $person->givenName = $author->getAuthor()->getFirstName();
            $person->surname = $author->getAuthor()->getLastName();
            $doi->body->journal->journalArticle->contributors->add($person);
            $k++;
        }

        return $doi;
    }
}
