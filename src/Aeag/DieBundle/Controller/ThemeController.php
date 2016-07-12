<?php

namespace Aeag\DieBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Aeag\DieBundle\Entity\Theme;
use Aeag\DieBundle\Form\ThemeType;

/**
 * Theme controller.
 *
 */
class ThemeController extends Controller {

    /**
     * Lists all Theme entities.
     *
     */
    public function indexAction() {
        
         $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Theme');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager('die');
        
        $repoTheme = $em->getRepository('AeagDieBundle:Theme');
        $repoSousTheme = $em->getRepository('AeagDieBundle:SousTheme');
       
        $themes = $repoTheme->getThemes();
        $entities = array();
        $i= 0;
        foreach($themes as $theme){
            $entities[$i]['theme'] = $theme;
            $sousThemes = $repoSousTheme->getSousThemesByTheme($theme);
            $entities[$i]['nbSousThemes'] = count($sousThemes);
            if (count($sousThemes) == 1){
                $entities[$i]['sousTheme'] = $sousThemes[0];
            }else{
                $entities[$i]['sousTheme'] = null;
            }
            $i++;
        }
        
        $session->set('urlRetour', $this->generateUrl('theme'));

        return $this->render('AeagDieBundle:Theme:index.html.twig', array(
                    'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Theme entity.
     *
     */
    public function showAction($id) {
        
           $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Theme');
        $session->set('fonction', 'show');
        $em = $this->get('doctrine')->getManager('die');
     
        $entity = $em->getRepository('AeagDieBundle:Theme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Theme entity.');
        }

        return $this->render('AeagDieBundle:Theme:show.html.twig', array(
                    'entity' => $entity,
        ));
    }

    /**
     * Displays a form to create a new Theme entity.
     *
     */
    public function newAction() {
        
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Theme');
        $session->set('fonction', 'new');
        $em = $this->get('doctrine')->getManager('die');
        
        $entity = new Theme();
        $form = $this->createForm(new ThemeType(), $entity);

        return $this->render('AeagDieBundle:Theme:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
        ));
    }

    /**
     * Creates a new Theme entity.
     *
     */
    public function createAction(Request $request) {
        
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Theme');
        $session->set('fonction', 'create');
        $em = $this->get('doctrine')->getManager('die');
        
        $entity = new Theme();
        $form = $this->createForm(new ThemeType(), $entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('theme'));
        }

        return $this->render('AeagDieBundle:Theme:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Theme entity.
     *
     */
    public function editAction($id) {
        
         $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Theme');
        $session->set('fonction', 'edit');
        $em = $this->get('doctrine')->getManager('die');
    
        $entity = $em->getRepository('AeagDieBundle:Theme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Theme entity.');
        }

        $form = $this->createForm(new ThemeType(), $entity);
      
        return $this->render('AeagDieBundle:Theme:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
            ));
    }

    /**
     * Edits an existing Theme entity.
     *
     */
    public function updateAction($id, Request $request) {
        
         $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Theme');
        $session->set('fonction', 'update');
        $em = $this->get('doctrine')->getManager('die');
  
        $entity = $em->getRepository('AeagDieBundle:Theme')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Theme entity.');
        }

        $form = $this->createForm(new ThemeType(), $entity);
       
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('theme'));
        }

        return $this->render('AeagDieBundle:Theme:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
         ));
    }

    /**
     * Deletes a Theme entity.
     *
     */
    public function deleteAction($id) {
        
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Theme');
        $session->set('fonction', 'delete');
        $em = $this->get('doctrine')->getManager('die');

        $qb = $em->createQueryBuilder();
        $qb->select('count(a)')
                ->from('AeagDieBundle:SousTheme', 'a')
                ->where("a.theme = :id_theme")
                ->setParameter('id_theme', $id);
        $nb_sousthemes = $qb->getQuery()->getSingleScalarResult();

        if ($nb_sousthemes == 0) {
            $entity = $em->getRepository('AeagDieBundle:Theme')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Theme entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('theme'));
    }

 

}
