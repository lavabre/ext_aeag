<?php

namespace Aeag\DieBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Aeag\DieBundle\Entity\Organisme;
use Aeag\DieBundle\Form\OrganismeType;

/**
 * Organisme controller.
 *
 */
class OrganismeController extends Controller {

    /**
     * Lists all Organisme entities.
     *
     */
    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Organisme');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager('die');

        $repoOrganisme = $em->getRepository('AeagDieBundle:Organisme');
        $entities = $repoOrganisme->getOrganismes();

        return $this->render('AeagDieBundle:Organisme:index.html.twig', array(
                    'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Organisme entity.
     *
     */
    public function showAction($id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Organisme');
        $session->set('fonction', 'show');
        $em = $this->get('doctrine')->getManager('die');


        $entity = $em->getRepository('AeagDieBundle:Organisme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Organisme entity.');
        }

        return $this->render('AeagDieBundle:Organisme:show.html.twig', array(
                    'entity' => $entity,
        ));
    }

    /**
     * Displays a form to create a new Organisme entity.
     *
     */
    public function newAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Organisme');
        $session->set('fonction', 'new');
        $em = $this->get('doctrine')->getManager('die');

        $entity = new Organisme();
        $form = $this->createForm(new OrganismeType(), $entity);

        return $this->render('AeagDieBundle:Organisme:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
        ));
    }

    /**
     * Creates a new Organisme entity.
     *
     */
    public function createAction(Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Organisme');
        $session->set('fonction', 'create');
        $em = $this->get('doctrine')->getManager('die');

        $entity = new Organisme();
        $form = $this->createForm(new OrganismeType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('organisme'));
        }

        return $this->render('AeagDieBundle:Organisme:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Organisme entity.
     *
     */
    public function editAction($id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Organisme');
        $session->set('fonction', 'edit');
        $em = $this->get('doctrine')->getManager('die');

        $entity = $em->getRepository('AeagDieBundle:Organisme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Organisme entity.');
        }

        $form = $this->createForm(new OrganismeType(), $entity);

        return $this->render('AeagDieBundle:Organisme:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Edits an existing Organisme entity.
     *
     */
    public function updateAction($id, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Organisme');
        $session->set('fonction', 'update');
        $em = $this->get('doctrine')->getManager('die');

        $entity = $em->getRepository('AeagDieBundle:Organisme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Organisme entity.');
        }

        $form = $this->createForm(new OrganismeType(), $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('organisme'));
        }

        return $this->render('AeagDieBundle:Organisme:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Deletes a Organisme entity.
     *
     */
    public function deleteAction($id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Organisme');
        $session->set('fonction', 'delete');
        $em = $this->get('doctrine')->getManager('die');

        $entity = $em->getRepository('AeagDieBundle:Organisme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Organisme entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('organisme'));
    }


}
