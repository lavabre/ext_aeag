<?php

namespace Aeag\DieBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\DieBundle\Entity\Demande;
use Aeag\DieBundle\Entity\Organisme;
use Aeag\DieBundle\Entity\Theme;
use Aeag\DieBundle\Entity\SousTheme;
use Aeag\DieBundle\Form\DemandeType;
use Aeag\DieBundle\Form\DemandeEnvoyeeType;
use Aeag\DieBundle\Form\DemandeThemeType;
use Aeag\DieBundle\Form\DemandeSousThemeType;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller {

    public function indexAction($theme = null, $sousTheme = null, $light = null) {
        
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'Default');
        $session->set('controller', 'Default');
        $session->set('fonction', 'index');
        $em = $this->getDoctrine()->getEntityManager('die');
     
        $session->set('logo', '1');
        $session->set('size', '2');


        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            // accres au menu adminitrateur
            return $this->render('AeagDieBundle:Admin:index.html.twig', array(
                        'logo' => $session->get('logo'),
                        'size' => $session->get('size'),
                    ));
        }

        //return new Response("theme: " . $theme );


        // $session->getFlashBag()->add('notice-success', '');
   
        $entity = new Demande();


        if (!is_null($theme)) {
           $Theme = $em->getRepository('AeagDieBundle:Theme')->findOneById($theme);
            if (!$Theme) {
                throw $this->createNotFoundException('pas de theme associé à : ' . $theme);
            }

            /* if (!is_null($sousTheme)) {
              $em = $this->getDoctrine()->getEntityManager('die');
              $SousTheme = $em->getRepository('AeagDieBundle:SousTheme')->findOneBySousTheme($sousTheme);
              if (!$SousTheme) {
              throw $this->createNotFoundException('pas de sous-theme associé à : ' . $sousTheme .' du theme : ' . $theme);
              }
              $form = $this->createForm(new DemandeSousThemeType($Theme->getId(), $SousTheme->getId() ), $entity);
              } else { */

            // return new Response("theme: " . $theme . " id : " . $Theme->getId() );
            $session->set('theme', $Theme->getId());
            $form = $this->createForm(new DemandeThemeType($Theme->getId()), $entity);
            //}
        } else {
            $session->remove('theme');
            $form = $this->createForm(new DemandeType(), $entity);
        }
        return $this->render('AeagDieBundle:Default:index.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'logo' => $session->get('logo'),
                    'size' => $session->get('size'),
                    'theme' => $session->get('theme'),
                ));
    }

    public function indexLightAction($theme = null, $sousTheme = null) {
        
         $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'Default');
        $session->set('controller', 'Default');
        $session->set('fonction', 'indexLight');
        $em = $this->getDoctrine()->getEntityManager('die');

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            // accres au menu adminitrateur
            return $this->render('AeagDieBundle:Admin:index.html.twig');
        }

        //return new Response("theme: " . $theme );

        $session = $this->get('session');

        $session->set('logo', '2');
        $session->set('size', '1');


         $entity = new Demande();
//         return new Response("theme : " . $theme );
        

        if (!is_null($theme)) {
             $Theme = $em->getRepository('AeagDieBundle:Theme')->findOneById($theme);
            if (!$Theme) {
                throw $this->createNotFoundException('pas de theme associé à : ' . $theme);
            }

            /* if (!is_null($sousTheme)) {
              $em = $this->getDoctrine()->getEntityManager('die');
              $SousTheme = $em->getRepository('AeagDieBundle:SousTheme')->findOneBySousTheme($sousTheme);
              if (!$SousTheme) {
              throw $this->createNotFoundException('pas de sous-theme associé à : ' . $sousTheme .' du theme : ' . $theme);
              }
              $form = $this->createForm(new DemandeSousThemeType($Theme->getId(), $SousTheme->getId() ), $entity);
              } else { */

            //return new Response("theme: " . $theme . " id : " . $Theme->getId() );

            $session->set('theme', $Theme->getId());

            $form = $this->createForm(new DemandeThemeType($Theme->getId()), $entity);
            //}
        } else {
            $session->remove('theme');

            $form = $this->createForm(new DemandeType(), $entity);
        }
        return $this->render('AeagDieBundle:Default:indexLight.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'logo' => $session->get('logo'),
                    'size' => $session->get('size'),
                    'theme' => $session->get('theme'),
                ));
    }

    /**
     * Creates a new Demande entity .
     *
     */
    public function createAction(Request $request) {
        
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'Default');
        $session->set('controller', 'Default');
        $session->set('fonction', 'create');
        $em = $this->getDoctrine()->getEntityManager('die');


        $entity = new Demande();
        //$session = $request->getSession();
        $session = $this->get('session');
        $idTheme = $session->get('theme');
        if ($idTheme) {
            $form = $this->createForm(new DemandeThemeType($idTheme), $entity);
            // return new Response("theme : " . $idTheme );
        } else {
            $form = $this->createForm(new DemandeType(), $entity);
        }
       $form->handleRequest($request);

        //return new Response("theme : " . $session->get('theme')->getId() );

        if ($form->isValid()) {

            $Organisme = $em->getRepository('AeagDieBundle:Organisme')->find($entity->getOrganisme()->getId());

            $Departement = $em->getRepository('AeagDieBundle:Departement')->find($entity->getDept()->getDept());


            if ($idTheme) {
                $Theme = $em->getRepository('AeagDieBundle:Theme')->find($idTheme);
            } else {
                $Theme = $em->getRepository('AeagDieBundle:Theme')->find($entity->getTheme()->getId());
            }
            //$SousTheme = $em->getRepository('AeagDieBundle:SousTheme')->find($entity->getSousTheme()->getId());
            $SousTheme = $em->getRepository('AeagDieBundle:SousTheme')->findOneBy(array('theme' => $Theme->getId()));

            //return new Response("Theme : " . $Theme->getId() . " sosuteheme : " . $SousTheme->getId() );

            $entity->setDept($Departement->getLibelle());
            $entity->setOrganisme($Organisme->getOrganisme());
            $entity->setTheme($Theme->getTheme());
            $entity->setSousTheme($SousTheme->getSousTheme());
            $entity->setDateCreation(new \Datetime());
            $date = new \Datetime();
            $date->add(new \DateInterval('P' . $SousTheme->getEcheance() . 'D'));
            $entity->setDateEcheance($date);



            $em->persist($entity);
            $em->flush();

            $this->sendAccuseReception($entity, $Organisme, $Theme, $SousTheme);

            $this->sendDestinataire($entity, $Organisme, $Theme, $SousTheme);
            
             if ($idTheme) {
                 return $this->redirect($this->generateUrl('aeag_die'));
             }else{
             return $this->redirect($this->generateUrl('aeag_die'));
             }

//            $form = $this->createForm(new DemandeEnvoyeeType(), $entity);
//
//            return $this->render('AeagDieBundle:Default:envoye.html.twig', array(
//                        'demande' => $entity,
//                        'form' => $form->createView(),
//                        'logo' => $session->get('logo'),
//                        'size' => $session->get('size'),
//                        'theme' => $Theme->getId(),
//                    ));
        }


       
        
        
        return $this->render('AeagDieBundle:Default:index.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'logo' => $session->get('logo'),
                    'size' => $session->get('size'),
                    'theme' => $session->get('theme'),
                ));
    }

    public function createLightAction($theme = null, Request $request) {
        
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'Default');
        $session->set('controller', 'Default');
        $session->set('fonction', 'createLight');
        $em = $this->getDoctrine()->getEntityManager('die');

        $entity = new Demande();
       //$session = $request->getSession();
        $session = $this->get('session');
        $idTheme = $theme;
        if ($idTheme) {
            $Theme = $em->getRepository('AeagDieBundle:Theme')->find($idTheme);
            $form = $this->createForm(new DemandeThemeType($Theme->getId()), $entity);
            // return new Response("theme : " . $idTheme );
        } else {
            $form = $this->createForm(new DemandeType(), $entity);
        }
        $form->handleRequest($request);

       // return new Response("theme : " . $Theme->getId() );

        if ($form->isValid()) {

            $Organisme = $em->getRepository('AeagDieBundle:Organisme')->find($entity->getOrganisme()->getId());

            $Departement = $em->getRepository('AeagDieBundle:Departement')->find($entity->getDept()->getDept());


            if ($idTheme) {
                $Theme = $em->getRepository('AeagDieBundle:Theme')->find($idTheme);
            } else {
                $Theme = $em->getRepository('AeagDieBundle:Theme')->find($entity->getTheme()->getId());
            }
            //$SousTheme = $em->getRepository('AeagDieBundle:SousTheme')->find($entity->getSousTheme()->getId());
            $SousTheme = $em->getRepository('AeagDieBundle:SousTheme')->findOneBy(array('theme' => $Theme->getId()));

            //return new Response("Theme : " . $Theme->getId() . " sosuteheme : " . $SousTheme->getId() );

            $entity->setDept($Departement->getLibelle());
            $entity->setOrganisme($Organisme->getOrganisme());
            $entity->setTheme($Theme->getTheme());
            $entity->setSousTheme($SousTheme->getSousTheme());
            $entity->setDateCreation(new \Datetime());
            $date = new \Datetime();
            $date->add(new \DateInterval('P' . $SousTheme->getEcheance() . 'D'));
            $entity->setDateEcheance($date);



            $em->persist($entity);
            $em->flush();

            $this->sendAccuseReception($entity, $Organisme, $Theme, $SousTheme);

            $this->sendDestinataire($entity, $Organisme, $Theme, $SousTheme);

           
            if ($idTheme) {
                  return $this->redirect($this->generateUrl('aeag_die_Light_theme', array('theme' => $idTheme)));
             }else{
                return $this->redirect($this->generateUrl('aeag_die_Light'));
             }
        }


        return $this->render('AeagDieBundle:Default:indexLight.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'logo' => $session->get('logo'),
                    'size' => $session->get('size'),
                    'theme' => $idTheme 
                ));
    }

    /*
     * envoi d'un mail accusé de reception au demandeur
     */

    public function sendAccuseReception($Demande, $Organisme, $Theme, $SousTheme) {
        // Récupération du service.
        $mailer = $this->get('mailer');

        // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
        $message = \Swift_Message::newInstance()
                ->setSubject($Demande->getObjet())
                ->setFrom('automate@eau-adour-garonne.fr')
                ->setTo($Demande->getEmail())
                ->setBody($this->renderView('AeagDieBundle:Default:accuseReceptionEmail.txt.twig', array(
                    'demande' => $Demande,
                    'organisme' => $Organisme,
                    'theme' => $Theme,
                    'soustheme' => $SousTheme)));

        // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
        $mailer->send($message);


        //$this->get('session')->setFlash('notice', 'Votre demande a bien été prise en compte, vous allez recevoir un accusé de réception ');
    }

    /*
     * envoi d'un mail au destinataire du sous-theme
     */

    public function sendDestinataire($Demande, $Organisme, $Theme, $SousTheme) {
        // Récupération du service.
        $mailer = $this->get('mailer');

//        // Création du corps
//        $body = $SousTheme->getCorps();
//        $body = str_replace("#NOM#", $Demande->getNom(), $body);
//        $body = str_replace("#PRENOM#", $Demande->getPrenom(), $body);
//        $body = str_replace("#DEPARTEMENT#", $Demande->getDept(), $body);
//        $body = str_replace("#COURRIEL#", $Demande->getEmail(), $body);
//        $body = str_replace("#DATE_ECHEANCE#", $Demande->getDateEcheance()->format('d-m-Y'), $body);
//        $body = str_replace("#SOUS_THEME#", $SousTheme->getSousTheme(), $body);
//        $body = str_replace("#ORGANISME#", $Organisme->getOrganisme(), $body);
//        $body = str_replace("#OBJET#", $Demande->getObjet(), $body);
//        $body = str_replace("#DESCRIPTION#", $Demande->getCorps(), $body);

        // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
         $message = \Swift_Message::newInstance()
                ->setSubject($Demande->getObjet())
                ->setFrom('automate@eau-adour-garonne.fr')
                ->setTo($SousTheme->getDestinataire())
                //->setTo('jerome.carre@eau-adour-garonne.fr')
                //->setTo('jle@eau-adour-garonne.fr')
                //->setBody($body);
                ->setBody($this->renderView('AeagDieBundle:Default:sendDestinataireEmail.txt.twig', array(
                    'demande' => $Demande,
                    'organisme' => $Organisme,
                    'theme' => $Theme,
                    'soustheme' => $SousTheme)));

        // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
        $mailer->send($message);

        //$this->get('session')->setFlash('message', $body);
        $this->get('session')->getFlashBag()->add('notice-success', 'Votre demande a bien été prise en compte, vous allez recevoir un accusé de réception.');
    }

    /*
     *  liste des sous-thèmes associés à un thème
     */

    public function listesousthemeAction() {
        
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'Default');
        $session->set('controller', 'Default');
        $session->set('fonction', 'listesoustheme');
        $em = $this->getDoctrine()->getEntityManager('die');

        $list_sous_theme = array();
        $request = $this->container->get('request');

        if ($request->isXmlHttpRequest()) {

            $id_theme = '';
            $id_theme = $request->get('id_theme');

            if ($id_theme != '') {
                $qb = $em->createQueryBuilder();

                $qb->select('a')
                        ->from('AeagDieBundle:SousTheme', 'a')
                        ->where("a.theme = :id_theme")
                        ->orderBy('a.sousTheme', 'ASC')
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
