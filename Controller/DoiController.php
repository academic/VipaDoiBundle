<?php
namespace OkulBilisim\OjsDoiBundle\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Ojs\CoreBundle\Controller\OjsController as Controller;
use Ojs\JournalBundle\Entity\Article;
use OkulBilisim\OjsDoiBundle\Service\DoiMetaGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DoiController extends Controller
{
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

        $crossrefConfig = $em->getRepository('OjsDoiBundle:CrossrefConfig')->findOneBy(array('journal' => $journal));

        if (!$this->isGranted('EDIT', $journal, 'articles')) {
            throw new AccessDeniedException("You not authorized for this page!");
        }
        $this->throw404IfNotFound($crossrefConfig);


        $doi = $doiGenerator->getArticle($article, $crossrefConfig);

        $data = $serializer->serialize($doi, 'xml');

        $client = new Client(
            [
                'base_uri' => 'https://api.crossref.org/'
                ,
                'timeout' => 0,
                'auth' => [$crossrefConfig->getUsername(), $crossrefConfig->getPassword()]
            ]
        );
        try {

            $response = $client->request(
                'POST',
                'deposits',
                [
                    'body' => $data,
                    'headers' => [
                        'Content-Type' => 'application/vnd.crossref.deposit+xml'
                    ]
                ]
            );
            $doi = json_decode($response->getBody()->getContents(), true);

            if(!empty($doi['message']['dois'][0])) {
                $article->setDoi($doi['message']['dois'][0]);
            }
            $em->persist($article);
            $em->flush();

        } catch (ServerException $e) {
            $this->get('logger')->addError('doiFailed', array($e->getResponse()->getReasonPhrase(), $article->getId()));
        }


    }

    public function getPostAction(Request $request)
    {

        var_dump($request->getContent());
        die('XXX');
    }
}
