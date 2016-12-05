<?php

namespace Aeag\AeagBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\AeagBundle\Entity\LivreOr;
use Aeag\AeagBundle\Form\LivreOrType;

/**
 * LivreOr controller.
 *
 */
class LivreOrController extends Controller {

    /**
     * Lists all LivreOr entities.
     *
     */
    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagAeagBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'accueil');
        $session->set('controller', 'LivreOr');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();

        $repoLivreOr = $em->getRepository('AeagAeagBundle:LivreOr');
        $repoUsers = $em->getRepository('AeagUserBundle:User');

        $livreors = $repoLivreOr->getLivreOrs();

        $tabEntities = array();
        $i = 0;
        foreach ($livreors as $livreor) {
            $emetteur = $repoUsers->getUserById($livreor->getEmetteur());
            $tabEntities[$i]['emetteur'] = $emetteur;
            $tabEntities[$i]['livreor'] = $livreor;
            $i++;
        }



        return $this->render('AeagAeagBundle:LivreOr:index.html.twig', array(
                    'entities' => $tabEntities,
        ));
    }

    /**
     * Creates a new LivreOr entity.
     *
     */
    public function createAction(Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagAeagBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'accueil');
        $session->set('controller', 'LivreOr');
        $session->set('fonction', 'create');
        $em = $this->get('doctrine')->getManager();

        $repoUsers = $em->getRepository('AeagUserBundle:User');

        $entity = new LivreOr();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setEmetteur($user->getId());
            $entity->setApplication($session->get('appli'));
            $em->persist($entity);
            $em->flush();

            // Récupération du service.
            $mailer = $this->get('mailer');

            // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
            $message = \Swift_Message::newInstance()
                    ->setSubject('Livre d\'or  :  nouveau message')
                    ->setFrom('automate@eau-adour-garonne.fr')
                    ->setTo('jle@eau-adour-garonne.fr')
                    ->setBody($this->renderView('AeagAeagBundle:LivreOr:accuseResponsableEmail.txt.twig', array(
                        'entity' => $entity,
                        'user' => $user)));

            // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
            $mailer->send($message);

            return $this->redirect($this->generateUrl('livreor'));
        }

        return $this->render('AeagAeagBundle:LivreOr:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a LivreOr entity.
     *
     * @param LivreOr $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(LivreOr $entity) {
        $form = $this->createForm(new LivreOrType(), $entity, array(
            'action' => $this->generateUrl('livreor_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new LivreOr entity.
     *
     */
    public function newAction() {
        $entity = new LivreOr();
        $form = $this->createCreateForm($entity);

        return $this->render('AeagAeagBundle:LivreOr:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a LivreOr entity.
     *
     */

    /**
     * Deletes a LivreOr entity.
     *
     */
    public function deleteAction(Request $request, $id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagAeagBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'accueil');
        $session->set('controller', 'LivreOr');
        $session->set('fonction', 'delete');
        $em = $this->get('doctrine')->getManager();

        $repoLivreOr = $em->getRepository('AeagAeagBundle:LivreOr');

        $livreor = $repoLivreOr->getLivreOrById($id);

        $em->remove($livreor);
        $em->flush();

        return $this->redirect($this->generateUrl('livreor'));
    }

}
