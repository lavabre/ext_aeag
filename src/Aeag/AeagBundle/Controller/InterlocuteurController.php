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

    public function envoyerMessageAllAction($id = null, Request $request) {
        
         $user = $this->getUser();
         if (!$user) {
            return $this->render('AeagAeagBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'accueil');
        $session->set('controller', 'Interlocuteur');
        $session->set('fonction', 'envoyerMessageAll');
        $em = $this->get('doctrine')->getManager();
        
        $security = $this->get('security.authorization_checker');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');

        if ($security->isGranted('ROLE_ADMIN')) {
            $utilisateurs = $repoUsers->getUsersByRole('ROLE_AEAG');
        } elseif ($security->isGranted('ROLE_ODEC')) {
            $utilisateurs = $repoUsers->getUsersByRole('ROLE_ODEC');
        } elseif ($security->isGranted('ROLE_FRD')) {
            $utilisateurs = $repoUsers->getUsersByRole('ROLE_FRD');
        } else {
            $utilisateurs = $repoUsers->getUsersByRole('ROLE_AEAG');
        }

        $envoyerMessageAll = new EnvoyerMessageAll();
        $form = $this->createForm(new EnvoyerMessageAllType(), $envoyerMessageAll);
        $message = null;

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);
            if ($form->isValid()) {

                foreach ($utilisateurs as $utilisateur) {
                    $message = new Message();
                    $message->setRecepteur($utilisateur->getId());
                    $message->setEmetteur($user->getid());
                    $message->setNouveau(true);
                    $message->setIteration(0);
                    $texte = $envoyerMessageAll->getMessage();
                    $message->setMessage($texte);
                    $em->persist($message);
                }

                $mailer = $this->get('mailer');
                $dest = null;
                foreach ($utilisateurs as $utilisateur) {
                    if ($utilisateur->getEmail()) {
                        $domain = strstr($utilisateur->getEmail(), '@');
                        if ($domain <> '@a-renseigner-merci.svp') {
                            if (!$dest) {
                                $dest = $utilisateur->getEmail();
                            } else {
                                $dest = $dest . ';' . $utilisateur->getEmail();
                            }
                        }
                    }
                }
                // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
                $destinataires = explode(";", $dest);
                foreach ($destinataires as $destinataire) {
                    $mail = \Swift_Message::newInstance()
                            ->setSubject($envoyerMessageAll->getSujet())
                            ->setFrom(array('automate@eau-adour-garonne.fr'))
                            ->setTo(array($destinataire))
                            ->setBody($envoyerMessageAll->getMessage());

                    // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
                    $mailer->send($mail);
                }

                $notification = new Notification();
                $notification->setRecepteur($user->getId());
                $notification->setEmetteur($user->getId());
                $notification->setNouveau(true);
                $notification->setIteration(0);
                $notification->setMessage('Message envoyé à tous les utilisateurs ');
                $em->persist($notification);
                $em->flush();

                if (is_object($user)) {
                    $mes = $this->notificationAction($user, $em, $session);
                    $mes1 = $this->messageAction($user, $em, $session);
                }

                //$this->get('session')->getFlashBag()->add('notice', 'Vous avez un nouveau message');

                if ($security->isGranted('ROLE_ADMIN')) {
                    return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_AEAG')));
                };
                if ($security->isGranted('ROLE_ODEC')) {
                    return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_ODEC')));
                };
                if ($security->isGranted('ROLE_FRD')) {
                    return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_FRD')));
                };

                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_AEAG')));
            }
        }

        return $this->render('AeagAeagBundle:Default:envoyerMessageAll.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function envoyerMessageAction($id = null, Request $request) {
        
         $user = $this->getUser();
         if (!$user) {
            return $this->render('AeagAeagBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'accueil');
        $session->set('controller', 'Interlocuteur');
        $session->set('fonction', 'envoyerMessage');
        $em = $this->get('doctrine')->getManager();
        
        $security = $this->get('security.authorization_checker');
        $repoInterlocuteur = $em->getRepository('AeagAeagBundle:Interlocuteur');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoUser = $em->getRepository('AeagUserBundle:User');
        $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');

        $interlocuteur = $repoInterlocuteur->getInterlocuteurById($id);

        if (!$interlocuteur) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement avec la cle : ' . $id);
        }

        $correspondant = $repoCorrespondant->getCorrespondantById($interlocuteur->getCorrespondant());
        $userInterlocuteur = $repoUser->getUserByCorrespondant($correspondant->getId());

        $envoyerMessage = new EnvoyerMessageInterlocuteur();
        $envoyerMessage->setDestinataire($interlocuteur->getEmail());
        $form = $this->createForm(new EnvoyerMessageInterlocuteurType, $envoyerMessage);
        $message = null;
        $maj = 'ko';

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);
            if ($form->isValid()) {
                $message = new Message();
                $message->setRecepteur($userInterlocuteur->getId());
                $message->setEmetteur($user->getid());
                $message->setNouveau(true);
                $message->setIteration(0);
                $texte = $envoyerMessage->getMessage();
                $message->setMessage($texte);
                $em->persist($message);


                $notification = new Notification();
                $notification->setRecepteur($user->getId());
                $notification->setEmetteur($user->getId());
                $notification->setNouveau(true);
                $notification->setIteration(2);
                $notification->setMessage('Message envoyé à ' . $userInterlocuteur->getUsername());
                $em->persist($notification);
                $em->flush();

                if (is_object($user)) {
                    $mes = $this->notificationAction($user, $em, $session);
                    $mes1 = $this->messageAction($user, $em, $session);
                }

                $maj = 'ok';

                // Récupération du service.
                $mailer = $this->get('mailer');
                // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
                $mail = \Swift_Message::newInstance()
                        ->setSubject($envoyerMessage->getSujet())
                        ->setFrom(array('automate@eau-adour-garonne.fr'))
                        ->setTo(array($envoyerMessage->getDestinataire()))
                        ->setBody($envoyerMessage->getMessage());

                // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
                $mailer->send($mail);
            } else {
                return new Response('user ' . $user->getUsername() . ' dest : ' . $interlocuteur->getNom() . ' mail : ' . $envoyerMessage->getDestinataire());
            }
        }

        return $this->render('AeagAeagBundle:Interlocuteur:envoyerMessage.html.twig', array(
                    'User' => $userInterlocuteur,
                    'correspondant' => $correspondant,
                    'interlocuteur' => $interlocuteur,
                    'form' => $form->createView(),
                    'maj' => $maj
        ));
    }

    public function consulterMessageAction($id = null) {
        
         $user = $this->getUser();
         if (!$user) {
            return $this->render('AeagAeagBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'accueil');
        $session->set('controller', 'Interlocuteur');
        $session->set('fonction', 'consulterMessage');
        $em = $this->get('doctrine')->getManager();

        $repoMessages = $em->getRepository('AeagAeagBundle:Message');
        $repoUser = $em->getRepository('AeagUserBundle:User');
        $message = $repoMessages->getMessageById($id);
        $mess = array();
        $emetteur = $repoUser->getUserById($message->getEmetteur());
        $recepteur = $repoUser->getUserById($message->getRecepteur());
        $mess['emetteur'] = $emetteur;
        $mess['recepteur'] = $recepteur;
        $mess['message'] = $message;
        $lignes = explode('<br />', nl2br($message->getMessage()));
        return $this->render('AeagAeagBundle:Default:consulterMessage.html.twig', array(
                    'message' => $mess,
                    'lignes' => $lignes,
        ));
    }

    public function supprimerMessageAction($id = null) {
        
         $user = $this->getUser();
         if (!$user) {
            return $this->render('AeagAeagBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'accueil');
        $session->set('controller', 'Interlocuteur');
        $session->set('fonction', 'supprimerMessage');
        $em = $this->get('doctrine')->getManager();

        $messages = null;

        $repoMessages = $em->getRepository('AeagAeagBundle:Message');
        $message = $repoMessages->getMessageById($id);
        $session->set('Messages', '');
        $em->remove($message);
        $em->flush();

        if (is_object($user)) {
            $mes = $this->notificationAction($user, $em, $session);
            $mes1 = $this->messageAction($user, $em, $session);
        }

        return $this->render('AeagAeagBundle:Default:listeMessages.html.twig', array(
                    'messages' => $session->get('Messages')
        ));
    }

    /**
     * @return Response
     */
    public static function notificationAction($user, $em, $session) {

        $notifications = null;

        if (is_object($user)) {


            $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');
            $notifications = $repoNotifications->getNotificationByRecepteur($user);
            $session->set('Notifications', '');
            if ($notifications) {
                foreach ($notifications as $notification) {
                    if ($notification->getIteration() == 0) {
                        $em->remove($notification);
                    } else {
                        $iteration = $notification->getIteration() - 1;
                        $notification->setIteration($iteration);
                        $em->persist($notification);
                    }
                }
                $em->flush();
                $notifications = $repoNotifications->getNotificationByRecepteur($user);
                $noti = array();
                $i = 0;
                foreach ($notifications as $notification) {
                    $repoUser = $em->getRepository('AeagUserBundle:User');
                    $emetteur = $repoUser->getUserById($notification->getEmetteur());
                    $recepteur = $repoUser->getUserById($notification->getRecepteur());
                    $noti[$i]['emetteur'] = $emetteur;
                    $noti[$i]['recepteur'] = $recepteur;
                    $noti[$i]['notification'] = $notification;
                    $i++;
                }
                $session->set('Notifications', $noti);
            }
        }

        return "ok";
    }

    /**
     * @return Response
     */
    public static function messageAction($user, $em, $session) {

        $messages = null;
        $mess = array();
        if (is_object($user)) {
            $repoUser = $em->getRepository('AeagUserBundle:User');
            $repoMessages = $em->getRepository('AeagAeagBundle:Message');
            $messages = $repoMessages->getMessageByRecepteur($user);
            $i = 0;
            foreach ($messages as $message) {
                $emetteur = $repoUser->getUserById($message->getEmetteur());
                $recepteur = $repoUser->getUserById($message->getRecepteur());
                $mess[$i]['emetteur'] = $emetteur;
                $mess[$i]['recepteur'] = $recepteur;
                $mess[$i]['message'] = $message;
                $i++;
            }
            $session->set('Messages', $mess);
        }

        return "ok";
    }

}
