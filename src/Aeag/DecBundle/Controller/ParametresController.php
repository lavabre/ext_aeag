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

        $session = $this->get('session');
        $session->set('menu', 'parametres');
        $emDec = $this->getDoctrine()->getManager('dec');
        $repoPara = $emDec->getRepository('AeagDecBundle:Parametre');
        $parametres = $repoPara->getParametres();
        return $this->render('AeagDecBundle:Parametres:index.html.twig', array(
                    'entities' => $parametres
        ));
    }

    public function parametreAction($code = null, Request $request) {


        $emDec = $this->getDoctrine()->getManager('dec');
        
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

