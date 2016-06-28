<?php

namespace Aeag\FrdBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Aeag\FrdBundle\Entity\Parametre;
use Aeag\FrdBundle\Form\Parametres\NewParametreType;
use Aeag\FrdBundle\Form\Parametres\MajParametreType;

class ParametresController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'parametres');
        $session->set('controller', 'Parametres');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoPara = $emFrd->getRepository('AeagFrdBundle:Parametre');
        $entities = $repoPara->getParametres();
        return $this->render('AeagFrdBundle:Parametres:index.html.twig', array(
                    'entities' => $entities
        ));
    }

    public function parametreAction($code = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'parametres');
        $session->set('controller', 'Parametres');
        $session->set('fonction', 'parametre');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoPara = $emFrd->getRepository('AeagFrdBundle:Parametre');

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
                $emFrd->persist($parametre);
                $emFrd->flush();
                $entities = $repoPara->getParametres();
                return $this->render('AeagFrdBundle:Parametres:index.html.twig', array(
                            'entities' => $entities
                ));
            }
        }

        return $this->render('AeagFrdBundle:Parametres:majParametres.html.twig', array(
                    'form' => $form->createView(),
                    'parametre' => $parametre,
        ));
    }

}
