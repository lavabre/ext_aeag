<?php

namespace Aeag\AeagBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Aeag\AeagBundle\Form\EnvoyerMessageType;
use Aeag\AeagBundle\Form\EnvoyerMessageInterlocuteurType;
use Aeag\AeagBundle\Form\EnvoyerMessageAllType;
use Aeag\AeagBundle\Form\MajInterlocuteurType;
use Aeag\AeagBundle\Form\DocumentType;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\AeagBundle\Entity\Document;
use Aeag\AeagBundle\Entity\Interlocuteur;
use Aeag\AeagBundle\Entity\Form\MajInterlocuteur;
use Aeag\AeagBundle\Entity\Form\EnvoyerMessage;
use Aeag\AeagBundle\Entity\Form\EnvoyerMessageInterlocuteur;
use Aeag\AeagBundle\Entity\Form\EnvoyerMessageAll;

class InterlocuteurController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagAeagBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'accueil');
        $session->set('controller', 'Interlocuteur');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();

        $repoInterlocuteur = $em->getRepository('AeagAeagBundle:Interlocuteur');
        $interlocuteurs = array();
        if ($user->getCorrespondant()) {
            $interlocuteurs = $repoInterlocuteur->getInterlocuteursByCorrespondant($user->getCorrespondant());
        }

        return $this->render('AeagAeagBundle:Interlocuteur:index.html.twig', array('entities' => $interlocuteurs));
    }

    public function ajouterAction(Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagAeagBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'accueil');
        $session->set('controller', 'Interlocuteur');
        $session->set('fonction', 'ajouter');
        $em = $this->get('doctrine')->getManager();

        $majInterlocuteur = new MajInterlocuteur();
        $form = $this->createForm(new MajInterlocuteurType(), $majInterlocuteur);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);
            if ($form->isValid()) {
                $interlocuteur = new Interlocuteur();
                $interlocuteur->setCorrespondant($user->getCorrespondant());
                $interlocuteur->setNom($majInterlocuteur->getNom());
                $interlocuteur->setPrenom($majInterlocuteur->getprenom());
                $interlocuteur->setFonction($majInterlocuteur->getFonction());
                $interlocuteur->setTel($majInterlocuteur->getTel());
                $interlocuteur->setEmail($majInterlocuteur->getEmail());
                $em->persist($interlocuteur);
                $em->flush();
                return $this->redirect($this->generateUrl('Aeag_interlocuteur'));
            }
        }

        return $this->render('AeagAeagBundle:Interlocuteur:ajouter.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function editerAction($id = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagAeagBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'accueil');
        $session->set('controller', 'Interlocuteur');
        $session->set('fonction', 'editer');
        $em = $this->get('doctrine')->getManager();

        $repoInterlocuteur = $em->getRepository('AeagAeagBundle:Interlocuteur');
        $interlocuteur = $repoInterlocuteur->getInterlocuteurById($id);
        $majInterlocuteur = clone($interlocuteur);
        $form = $this->createForm(new MajInterlocuteurType(), $majInterlocuteur);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);
            if ($form->isValid()) {
                $interlocuteur->setCorrespondant($user->getCorrespondant());
                $interlocuteur->setNom($majInterlocuteur->getNom());
                $interlocuteur->setPrenom($majInterlocuteur->getprenom());
                $interlocuteur->setFonction($majInterlocuteur->getFonction());
                $interlocuteur->setTel($majInterlocuteur->getTel());
                $interlocuteur->setEmail($majInterlocuteur->getEmail());
                $em->persist($interlocuteur);
                $em->flush();
                return $this->redirect($this->generateUrl('Aeag_interlocuteur'));
            }
        }

        return $this->render('AeagAeagBundle:Interlocuteur:editer.html.twig', array(
                    'form' => $form->createView(),
                    'interlocuteur' => $interlocuteur
        ));
    }

    public function supprimerAction($id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagAeagBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'accueil');
        $session->set('controller', 'Interlocuteur');
        $session->set('fonction', 'supprimer');
        $em = $this->get('doctrine')->getManager();

        $repoInterlocuteur = $em->getRepository('AeagAeagBundle:Interlocuteur');
        $interlocuteur = $repoInterlocuteur->getInterlocuteurById($id);
        $em->remove($interlocuteur);
        $em->flush();
        return $this->redirect($this->generateUrl('Aeag_interlocuteur'));
    }

}
