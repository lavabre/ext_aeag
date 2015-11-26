<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Aeag\SqeBundle\Entity\Parametre;
use Aeag\SqeBundle\Form\Parametres\NewParametreType;
use Aeag\SqeBundle\Form\Parametres\MajParametreType;

class ParametresController extends Controller {

    public function indexAction() {

        $session = $this->get('session');
        $session->set('menu', 'parametres');
        $session->set('controller', ' Parametres');
        $session->set('fonction', 'index');
        $emSqe = $this->getDoctrine()->getManager('sqe');
        $repoPara = $emSqe->getRepository('AeagSqeBundle:Parametre');
        $parametres = $repoPara->getParametres();
        return $this->render('AeagSqeBundle:Parametres:index.html.twig', array(
                    'entities' => $parametres
        ));
    }

    public function parametreAction($code = null, Request $request) {

        $session = $this->get('session');
        $session->set('menu', 'parametres');
        $session->set('controller', ' Parametres');
        $session->set('fonction', 'parametre');

        $emSqe = $this->getDoctrine()->getManager('sqe');

        $repoPara = $emSqe->getRepository('AeagSqeBundle:Parametre');

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
                $emSqe->persist($parametre);
                $emSqe->flush();
                $entities = $repoPara->getParametres();
                return $this->render('AeagSqeBundle:Parametres:index.html.twig', array(
                            'entities' => $entities
                ));
            }
        }

        return $this->render('AeagSqeBundle:Parametres:majParametres.html.twig', array(
                    'form' => $form->createView(),
                    'parametre' => $parametre,
        ));
    }

}
