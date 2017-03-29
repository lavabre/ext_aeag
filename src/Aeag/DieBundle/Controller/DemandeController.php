<?php

namespace Aeag\DieBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Aeag\DieBundle\Entity\Demande;
use Aeag\DieBundle\Form\DemandeType;
use Aeag\DieBundle\Form\DemandeEditType;

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
        $em = $this->get('doctrine')->getManager('die');

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
        $em = $this->get('doctrine')->getManager('die');

        $entity = $em->getRepository('AeagDieBundle:Demande')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Demande entity.');
        }

        return $this->render('AeagDieBundle:Demande:show.html.twig', array(
                    'entity' => $entity,
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
        $em = $this->get('doctrine')->getManager('die');

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
        $em = $this->get('doctrine')->getManager('die');

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

            $this->sendAccuseReception($entity, $Organisme, $Theme, $SousTheme);
            $this->sendDestinataire($entity, $Organisme, $Theme, $SousTheme);

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
        $em = $this->get('doctrine')->getManager('die');

        $entity = $em->getRepository('AeagDieBundle:Demande')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Demande entity.');
        }

        $form = $this->createForm(new DemandeEditType(), $entity);
        $organisme = $em->getRepository('AeagDieBundle:Organisme')->getOrganismesByOrganisme($entity->getOrganisme());
        $departement = $em->getRepository('AeagDieBundle:Departement')->getDepartementByLibelle($entity->getDept());
        $theme = $em->getRepository('AeagDieBundle:Theme')->getThemesByTheme($entity->getTheme());

        return $this->render('AeagDieBundle:Demande:edit.html.twig', array(
                    'entity' => $entity,
                    'organisme' => $organisme,
                    'departement' => $departement,
                    'theme' => $theme,
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
        $em = $this->get('doctrine')->getManager('die');

        $entity = $em->getRepository('AeagDieBundle:Demande')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Demande entity.');
        }

        $editForm = $this->createForm(new DemandeEditType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

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

            $this->get('session')->getFlashBag()->add('notice-success', 'La  demande n° ' . $entity->getId() . ' a bien été modifiée.');

            return $this->redirect($this->generateUrl('demande'));
        }

        return $this->render('AeagDieBundle:Demande:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a Demande entity.
     *
     */
    public function deleteAction($id) {
        $em = $this->get('doctrine')->getManager('die');
        $entity = $em->getRepository('AeagDieBundle:Demande')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Demande entity.');
        }

        $demande = clone($entity);
        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('notice-success', 'La  demande n° ' . $demande->getId() . ' a  été supprimée.');

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

    /*
     * envoi d'un mail accusé de reception au demandeur
     */

    private function sendAccuseReception($Demande, $Organisme, $Theme, $SousTheme) {
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

    private function sendDestinataire($Demande, $Organisme, $Theme, $SousTheme) {
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

}
