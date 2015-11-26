<?php

namespace Aeag\DieBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Aeag\DieBundle\Entity\Historique;
use Aeag\DieBundle\Form\HistoriqueType;

/**
 * Historique controller.
 *
 */
class HistoriqueController extends Controller
{
    /**
     * Lists all Historique entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager('die');

        $entities = $em->getRepository('AeagDieBundle:Historique')->findAll();

        return $this->render('AeagDieBundle:Historique:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Historique entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager('die');

        $entity = $em->getRepository('AeagDieBundle:Historique')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Historique entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AeagDieBundle:Historique:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new Historique entity.
     *
     */
    public function newAction()
    {
        $entity = new Historique();
        $form   = $this->createForm(new HistoriqueType(), $entity);

        return $this->render('AeagDieBundle:Historique:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Historique entity.
     *
     */
    public function createAction()
    {
        $entity  = new Historique();
        $request = $this->getRequest();
        $form    = $this->createForm(new HistoriqueType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager('die');
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('historique_show', array('id' => $entity->getId())));
            
        }

        return $this->render('AeagDieBundle:Historique:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Historique entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager('die');

        $entity = $em->getRepository('AeagDieBundle:Historique')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Historique entity.');
        }

        $editForm = $this->createForm(new HistoriqueType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AeagDieBundle:Historique:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Historique entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager('die');

        $entity = $em->getRepository('AeagDieBundle:Historique')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Historique entity.');
        }

        $editForm   = $this->createForm(new HistoriqueType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('historique_edit', array('id' => $id)));
        }

        return $this->render('AeagDieBundle:Historique:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Historique entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager('die');
            $entity = $em->getRepository('AeagDieBundle:Historique')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Historique entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('historique'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
