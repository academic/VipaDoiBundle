<?php

namespace Vipa\DoiBundle\Controller;

use Vipa\DoiBundle\Entity\CrossrefConfig;
use Vipa\DoiBundle\Form\Type\CrossrefConfigType;
use Vipa\CoreBundle\Controller\VipaController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ConfigController extends Controller
{
    /**
     * @return Response
     */
    public function editAction()
    {
        $em = $this->getDoctrine()->getManager();
        $journal = $this->get('vipa.journal_service')->getSelectedJournal();

        if (!$this->isGranted('EDIT', $journal) || !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException("You not authorized for this page!");
        }
        $crossrefConfig = $em->getRepository('VipaDoiBundle:CrossrefConfig')->findOneBy(array('journal' => $journal));
        if (!$crossrefConfig) {
            $crossrefConfig = new CrossrefConfig();
        }
        $form = $this->createEditForm($crossrefConfig);


        $suffixMapping = array(
            '%j' => 'doi.suffix.journal',
            '%v' => 'doi.suffix.volume',
            '%i' => 'doi.suffix.issue',
            '%Y' => 'doi.suffix.year',
            '%a' => 'doi.suffix.article',
            '%p' => 'doi.suffix.page',
        );

        return $this->render(
            'VipaDoiBundle:Config:edit.html.twig',
            [
                'entity' => $crossrefConfig,
                'form' => $form->createView(),
                'suffixMapping' => $suffixMapping
            ]
        );
    }

    /**
     * Creates a form to edit a Lang entity.
     *
     * @param  CrossrefConfig $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(CrossrefConfig $entity)
    {
        $journal = $this->get('vipa.journal_service')->getSelectedJournal();
        $form = $this->createForm(
            new CrossrefConfigType(),
            $entity,
            [
                'action' => $this->generateUrl(
                    'bulut_yazilim_doi_config_update',
                    ['journalId' => $journal->getId()]
                ),
                'method' => 'PUT',
            ]
        );

        $form->add('submit', 'submit', ['label' => 'Update']);

        return $form;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function updateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $journal = $this->get('vipa.journal_service')->getSelectedJournal();

        if (!$this->isGranted('EDIT', $journal) || !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException("You not authorized for this page!");
        }
        $crossrefConfig = $em->getRepository('VipaDoiBundle:CrossrefConfig')->findOneBy(array('journal' => $journal));
        if (!$crossrefConfig) {
            $crossrefConfig = new CrossrefConfig();
        }

        $crossrefConfig->setJournal($journal);

        $form = $this->createEditForm($crossrefConfig);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($crossrefConfig);
            $em->flush();
        }
        $suffixMapping = array(
            '%j' => 'doi.suffix.journal',
            '%v' => 'doi.suffix.volume',
            '%i' => 'doi.suffix.issue',
            '%Y' => 'doi.suffix.year',
            '%a' => 'doi.suffix.article',
            '%p' => 'doi.suffix.page'
        );

        return $this->render(
            'VipaDoiBundle:Config:edit.html.twig',
            [
                'entity' => $crossrefConfig,
                'form' => $form->createView(),
                'suffixMapping' => $suffixMapping
            ]
        );
    }
}
