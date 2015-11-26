<?php

namespace Aeag\DieBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Aeag\DieBundle\Entity\SousTheme;
use Aeag\DieBundle\Form\SousThemeType;

/**
 * SousTheme controller.
 *
 */
class SousThemeController extends Controller {

    /**
     * Lists all SousTheme entities.
     *
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getEntityManager('die');
        
        $entities = $em->getRepository('AeagDieBundle:SousTheme')->findAll();
        
        return $this->render('AeagDieBundle:SousTheme:index.html.twig', array(
                    'entities' => $entities
        ));
    }

    /**
     * Finds and displays a SousTheme entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getEntityManager('die');

        $entity = $em->getRepository('AeagDieBundle:SousTheme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SousTheme entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AeagDieBundle:SousTheme:show.html.twig', array(
                    'entity' => $entity,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new SousTheme entity.
     *
     */
    public function newAction() {
        $entity = new SousTheme();
        $form = $this->createForm(new SousThemeType(), $entity);

        return $this->render('AeagDieBundle:SousTheme:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
        ));
    }

    /**
     * Creates a new SousTheme entity.
     *
     */
    public function createAction() {
        $entity = new SousTheme();
        $request = $this->getRequest();
        $form = $this->createForm(new SousThemeType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager('die');
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('soustheme'));
        }

        return $this->render('AeagDieBundle:SousTheme:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing SousTheme entity.
     *
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getEntityManager('die');

        $entity = $em->getRepository('AeagDieBundle:SousTheme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SousTheme entity.');
        }

        $editForm = $this->createForm(new SousThemeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AeagDieBundle:SousTheme:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing SousTheme entity.
     *
     */
    public function updateAction($id) {
        $em = $this->getDoctrine()->getEntityManager('die');

        $entity = $em->getRepository('AeagDieBundle:SousTheme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SousTheme entity.');
        }

        $editForm = $this->createForm(new SousThemeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('soustheme'));
        }

        return $this->render('AeagDieBundle:SousTheme:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a SousTheme entity.
     *
     */
    public function deleteAction($id) {

        $em = $this->getDoctrine()->getEntityManager('die');
        $entity = $em->getRepository('AeagDieBundle:SousTheme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SousTheme entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('soustheme'));
    }

    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

}
