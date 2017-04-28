<?php
namespace Vipa\DoiBundle\Controller;

use Vipa\CoreBundle\Controller\VipaController as Controller;
use Vipa\JournalBundle\Entity\Article;
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
        $em = $this->getDoctrine()->getManager();
        $serializer = $serializer = $this->get('serializer');
        $journal = $this->get('vipa.journal_service')->getSelectedJournal();
        $doiGenerator = $this->get('doi.meta_generator');

        $crossrefConfig = $em->getRepository('VipaDoiBundle:CrossrefConfig')->findOneBy(array('journal' => $journal));

        if (!$this->isGranted('VIEW', $journal, 'articles')) {
            throw new AccessDeniedException("You not authorized for this page!");
        }
        $this->throw404IfNotFound($crossrefConfig);

        $doi = $doiGenerator->getArticle($article, $crossrefConfig);
        $data = $serializer->serialize($doi, 'xml');

        return new Response(
            $data, 200, array(
                'Content-Type' => 'application/vnd.crossref.deposit+xml',
                'Content-Disposition' => 'attachment; filename="'.$doi->head->doiBatchId.'.xml"'
            )
        );
    }
}
