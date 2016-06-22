<?php

namespace Aeag\DecBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Aeag\DecBundle\Entity\Parametre;
use Aeag\DecBundle\Form\Parametres\NewParametreType;
use Aeag\DecBundle\Form\Parametres\MajParametreType;

class ParametresController extends Controller {

    public function indexAction() {
        
        $user = $this->getUser();
         if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'parametres');
        $session->set('controller', 'Parametres');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoPara = $emDec->getRepository('AeagDecBundle:Parametre');
        $parametres = $repoPara->getParametres();
        return $this->render('AeagDecBundle:Parametres:index.html.twig', array(
                    'entities' => $parametres
        ));
    }

    public function parametreAction($code = null, Request $request) {
        
        $user = $this->getUser();
         if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'parametres');
        $session->set('controller', 'Parametres');
        $session->set('fonction', 'parametre');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');
  
        $repoPara = $emDec->getRepository('AeagDecBundle:Parametre');

        if (!$code or $code == 'new') {
            $parametre = new Parametre();
            $form = $this->createForm(new NewParametreType(), $parametre);
        } else {
            $parametre = $repoPara->getParametreByCode($code);
            $form = $this->createForm(new MajParametreType(), $parametre);
         }

           if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $emDec->persist($parametre);
                $emDec->flush();
                $entities = $repoPara->getParametres();
                return $this->render('AeagDecBundle:Parametres:index.html.twig', array(
                            'entities' => $entities
                ));
            }
        }

        return $this->render('AeagDecBundle:Parametres:majParametres.html.twig', array(
                    'form' => $form->createView(),
                    'parametre' => $parametre,
        ));
    }

}

