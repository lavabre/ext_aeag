<?php

namespace Aeag\DieBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Aeag\DieBundle\Entity\Organisme;
use Aeag\DieBundle\Form\OrganismeType;

/**
 * Organisme controller.
 *
 */
class OrganismeController extends Controller
{
    /**
     * Lists all Organisme entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager('die');

        $entities = $em->getRepository('AeagDieBundle:Organisme')->findAll();

        return $this->render('AeagDieBundle:Organisme:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Organisme entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager('die');

        $entity = $em->getRepository('AeagDieBundle:Organisme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Organisme entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AeagDieBundle:Organisme:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new Organisme entity.
     *
     */
    public function newAction()
    {
        $entity = new Organisme();
        $form   = $this->createForm(new OrganismeType(), $entity);

        return $this->render('AeagDieBundle:Organisme:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Organisme entity.
     *
     */
    public function createAction()
    {
        $entity  = new Organisme();
        $request = $this->getRequest();
        $form    = $this->createForm(new OrganismeType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager('die');
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('organisme'));
            
        }

        return $this->render('AeagDieBundle:Organisme:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Organisme entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager('die');

        $entity = $em->getRepository('AeagDieBundle:Organisme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Organisme entity.');
        }

        $editForm = $this->createForm(new OrganismeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AeagDieBundle:Organisme:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'        => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Organisme entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager('die');

        $entity = $em->getRepository('AeagDieBundle:Organisme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Organisme entity.');
        }

        $editForm   = $this->createForm(new OrganismeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('organisme'));
        }

        return $this->render('AeagDieBundle:Organisme:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Organisme entity.
     *
     */
    public function deleteAction($id)
    {
    	$em = $this->getDoctrine()->getEntityManager('die');
    	
        	$entity = $em->getRepository('AeagDieBundle:Organisme')->find($id);
    	
    	if (!$entity) {
    		throw $this->createNotFoundException('Unable to find Organisme entity.');
    	}
    	
    	$em->remove($entity);
    	$em->flush();
    
        return $this->redirect($this->generateUrl('organisme'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
