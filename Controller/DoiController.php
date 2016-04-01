<?php
namespace BulutYazilim\OjsDoiBundle\Controller;

use BulutYazilim\OjsDoiBundle\Entity\CrossrefConfig;
use BulutYazilim\OjsDoiBundle\Entity\DoiStatus;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Ojs\CoreBundle\Controller\OjsController as Controller;
use Ojs\CoreBundle\Params\DoiStatuses;
use Ojs\JournalBundle\Entity\Article;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DoiController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function crossrefPingBackAction(Request $request)
    {
        $logger = $this->get('logger');
        $logger->addAlert('pingback', [$request->query->all(), $request->request->all(), $request->getClientIps()]);

        return new Response();
    }

    /**
     * @param Article $article
     * @return Response
     */
    public function getArticleDoiAction(Article $article)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $serializer = $this->get('serializer');
        $doiGenerator = $this->get('doi.meta_generator');

        $journal = $this->get('ojs.journal_service')->getSelectedJournal();

        $crossrefConfig = $em->getRepository(CrossrefConfig::class)->findOneBy(array('journal' => $journal));

        if (!$this->isGranted('EDIT', $journal, 'articles')) {
            throw new AccessDeniedException("You not authorized for this page!");
        }
        $this->throw404IfNotFound($crossrefConfig);


        $doi = $doiGenerator->getArticle($article, $crossrefConfig);

        $data = $serializer->serialize($doi, 'xml');

        $client = new Client(
            [
                'base_uri' => 'https://api.crossref.org/',
                'timeout' => 0,
                'auth' => [$crossrefConfig->getUsername(), $crossrefConfig->getPassword()]
            ]
        );
        try {

            $response = $client->request(
                'POST',
                'deposits?pingback='.urlencode(
                    $this->generateUrl('bulut_yazilim_doi_crossref_pingback', [], UrlGeneratorInterface::ABSOLUTE_URL)
                ),
                [
                    'body' => $data,
                    'headers' => [
                        'Content-Type' => 'application/vnd.crossref.deposit+xml'
                    ]
                ]
            );
            $doi = json_decode($response->getBody()->getContents(), true);
            $doiStatus = new DoiStatus();
            $doiStatus
                ->setArticle($article)
                ->setStatus($doi['message']['status'])
                ->setBatchId($doi['message']['batch-id']);
            if (!empty($doi['message']['dois'][0])) {
                $article->setDoi($doi['message']['dois'][0]);
                $article->setDoiStatus(DoiStatuses::WAITING);
            }
            $em->persist($doiStatus);
            $em->persist($article);
            $em->flush();

        } catch (ServerException $e) {
            $this->get('logger')->addError('doiFailed', array($e->getResponse()->getReasonPhrase(), $article->getId()));
        }

        return $this->redirectToRoute('ojs_journal_article_index', array('journalId' => $journal->getId()));
    }
}
