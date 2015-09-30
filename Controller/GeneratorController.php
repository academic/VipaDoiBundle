<?php
namespace OkulBilisim\OjsDoiBundle\Controller;

use Ojs\CoreBundle\Controller\OjsController as Controller;
use Ojs\JournalBundle\Entity\Article;
use Ojs\JournalBundle\Entity\ArticleTranslation;
use OkulBilisim\OjsDoiBundle\Object\DoiBatch;
use OkulBilisim\OjsDoiBundle\Object\Person;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GeneratorController extends Controller
{
    /**
     * @param Article $article
     * @return Response
     */
    public function articleDoiAction(Article $article)
    {
        $journal = $this->get('ojs.journal_service')->getSelectedJournal();

        if (!$this->isGranted('VIEW', $journal, 'articles')) {
            throw new AccessDeniedException("You not authorized for this page!");
        }

        $serializer = $serializer = $this->get('serializer');

        $doi = new DoiBatch();

        $doi->head->doiBatchId = 'article_'.$article->getId().'_'.time();
        $doi->head->registrant = $article->getJournal()->getPublisher()->getName();
        $doi->head->depositor->emailAddress = 'mail@ojs.io';
        $doi->head->depositor->name = 'OJS ADMIN';

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

        $data = $serializer->serialize($doi, 'xml');

        return new Response(
            $data, 200, array(
                'Content-Type' => 'application/vnd.crossref.deposit+xml',
                'Content-Disposition' => 'attachment; filename="'.$doi->head->doiBatchId.'.xml"'
            )
        );
    }
}
