<?php

namespace Aeag\DieBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'SousTheme');
        $session->set('fonction', 'index');
        $em = $this->getDoctrine()->getEntityManager('die');

        $repoSousTheme = $em->getRepository('AeagDieBundle:SousTheme');
        $entities = $repoSousTheme->getSousThemes();

        return $this->render('AeagDieBundle:SousTheme:index.html.twig', array(
                    'entities' => $entities
        ));
    }

    /**
     * Finds and displays a SousTheme entity.
     *
     */
    public function showAction($id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'SousTheme');
        $session->set('fonction', 'show');
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

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'SousTheme');
        $session->set('fonction', 'new');
        $em = $this->getDoctrine()->getEntityManager('die');

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
    public function createAction(Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'SousTheme');
        $session->set('fonction', 'create');
        $em = $this->getDoctrine()->getEntityManager('die');

        $entity = new SousTheme();
        $form = $this->createForm(new SousThemeType(), $entity);
        $form->handleRequest($request);

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

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'SousTheme');
        $session->set('fonction', 'edit');
        $em = $this->getDoctrine()->getEntityManager('die');

        $entity = $em->getRepository('AeagDieBundle:SousTheme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SousTheme entity.');
        }

        $form = $this->createForm(new SousThemeType(), $entity);

        return $this->render('AeagDieBundle:SousTheme:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Edits an existing SousTheme entity.
     *
     */
    public function updateAction($id, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'SousTheme');
        $session->set('fonction', 'update');
        $em = $this->getDoctrine()->getEntityManager('die');

        $entity = $em->getRepository('AeagDieBundle:SousTheme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SousTheme entity.');
        }

        $form = $this->createForm(new SousThemeType(), $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('soustheme'));
        }

        return $this->render('AeagDieBundle:SousTheme:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Deletes a SousTheme entity.
     *
     */
    public function deleteAction($id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'SousTheme');
        $session->set('fonction', 'delete');
        $em = $this->getDoctrine()->getEntityManager('die');

        $entity = $em->getRepository('AeagDieBundle:SousTheme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SousTheme entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('soustheme'));
    }

}
