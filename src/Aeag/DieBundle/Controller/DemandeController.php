<?php

namespace Aeag\DieBundle\Controller;

use Aeag\DieBundle\Entity\SousTheme;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Aeag\DieBundle\Entity\Demande;
use Aeag\DieBundle\Form\DemandeType;
use Aeag\DieBundle\Form\DemandeEditType;
use Aeag\PagerBundle\Pager;
use Aeag\PagerBundle\Adapter\ArrayAdapter;

/**
 * Demande controller.
 *
 */
class DemandeController extends Controller {

    /**
     * Lists all Demande entities.
     *
     */
    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Demande');
        $session->set('fonction', 'index');
        $em = $this->getDoctrine()->getEntityManager('die');

        $repoDemande = $em->getRepository('AeagDieBundle:Demande');

        $entities = $repoDemande->getDemandes();

        return $this->render('AeagDieBundle:Demande:index.html.twig', array(
                    'entities' => $entities
        ));
    }

  
    /**
     * Finds and displays a Demande entity.
     *
     */
    public function showAction($id) {
        
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Demande');
        $session->set('fonction', 'show');
        $em = $this->getDoctrine()->getEntityManager('die');
       
        $entity = $em->getRepository('AeagDieBundle:Demande')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Demande entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AeagDieBundle:Demande:show.html.twig', array(
                    'entity' => $entity,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Demande entity.
     *
     */
    public function newAction() {
        
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Demande');
        $session->set('fonction', 'index');
        $em = $this->getDoctrine()->getEntityManager('die');
        
        $entity = new Demande();
        $form = $this->createForm(new DemandeType(), $entity);

        return $this->render('AeagDieBundle:Demande:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
        ));
    }

    /**
     * Creates a new Demande entity.
     *
     */
    public function createAction(Request $request) {
        
         $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Demande');
        $session->set('fonction', 'create');
        $em = $this->getDoctrine()->getEntityManager('die');
        
        $entity = new Demande();
          
        $form = $this->createForm(new DemandeType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
       
            $Organisme = $em->getRepository('AeagDieBundle:Organisme')->find($entity->getOrganisme()->getId());

            $Theme = $em->getRepository('AeagDieBundle:Theme')->find($entity->getTheme()->getId());

            $SousTheme = $em->getRepository('AeagDieBundle:SousTheme')->findOneBy(array('theme' => $Theme->getId()));
            
            $departement = $entity->getDept();

            $entity->setDept($departement->getLibelle());
            $entity->setOrganisme($Organisme->getOrganisme());
            $entity->setTheme($Theme->getTheme());
            $entity->setSousTheme($SousTheme->getSousTheme());
            $entity->setDateCreation(new \Datetime());
            $date = new \Datetime();
            $date->add(new \DateInterval('P' . $SousTheme->getEcheance() . 'D'));
            $entity->setDateEcheance($date);


            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('demande'));
        }

        return $this->render('AeagDieBundle:Demande:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Demande entity.
     *
     */
    public function editAction($id) {
        
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Demande');
        $session->set('fonction', 'edit');
        $em = $this->getDoctrine()->getEntityManager('die');
     
        $entity = $em->getRepository('AeagDieBundle:Demande')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Demande entity.');
        }

        $form = $this->createForm(new DemandeEditType(), $entity);
    
        return $this->render('AeagDieBundle:Demande:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
         ));
    }

    /**
     * Edits an existing Demande entity.
     *
     */
    public function updateAction($id, Request $request) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Demande');
        $session->set('fonction', 'update');
        $em = $this->getDoctrine()->getEntityManager('die');
   
        $entity = $em->getRepository('AeagDieBundle:Demande')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Demande entity.');
        }

        $editForm = $this->createForm(new DemandeEditType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('demande'));
        }

        return $this->render('AeagDieBundle:Demande:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Demande entity.
     *
     */
    public function deleteAction($id) {
        $em = $this->getDoctrine()->getEntityManager('die');
        $entity = $em->getRepository('AeagDieBundle:Demande')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Demande entity.');
        }

        $em->remove($entity);
        $em->flush();


        return $this->redirect($this->generateUrl('demande'));
    }

    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

    /*
     *  liste des sous-thèmes associés à un thème
     */

    public function listesousthemeAction() {

        $list_sous_theme = array();
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {

            $id_theme = '';
            $id_theme = $request->get('id_theme');

            $em = $this->container->get('doctrine')->getEntityManager('die');


            if ($id_theme != '') {
                $qb = $em->createQueryBuilder();

                $qb->select('a')
                        ->from('AeagDieBundle:SousTheme', 'a')
                        ->where("a.theme = :id_theme")
                        ->orderBy('a.sousTheme', 'DESC')
                        ->setParameter('id_theme', $id_theme);

                $query = $qb->getQuery();
                $sousthemes = $query->getResult();
            } else {
                $sousthemes = $em->getRepository('AeagDieBundle:SousTheme')->findAll();
            }

            return $this->container->get('templating')->renderResponse('AeagDieBundle:SousTheme:liste.html.twig', array(
                        'sousthemes' => $sousthemes
            ));
        }
    }

}
