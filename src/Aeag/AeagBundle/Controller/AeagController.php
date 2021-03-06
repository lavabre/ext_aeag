<?php

namespace Aeag\AeagBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Aeag\AeagBundle\Form\EnvoyerMessageType;
use Aeag\AeagBundle\Form\EnvoyerMessageAllType;
use Aeag\AeagBundle\Form\DocumentType;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\AeagBundle\Entity\Document;
use Aeag\UserBundle\Entity\Statistiques;
use Aeag\UserBundle\Entity\Connectes;
use Aeag\AeagBundle\Entity\Form\EnvoyerMessage;
use Aeag\AeagBundle\Entity\Form\EnvoyerMessageAll;

class AeagController extends Controller {

    public function indexAction() {

        //       $user = $this->getUser();
//         if (!$user) {
//            return $this->render('AeagAeagBundle:Default:interdit.html.twig');
//        }
        $session = $this->get('session');
        $session->set('menu', 'accueil');
        $session->set('controller', 'Aeag');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();

        $security = $this->get('security.authorization_checker');

        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

//        $session->clear();
//        \apc_clear_cache();


        $session->set('retourErreur', $this->generateUrl('aeag_homepage'));

        if (!$session->get('appli')) {
            $session->set('appli', 'aeag');
        }

        if (!is_object($user)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }


        if ($security->isGranted('ROLE_ADMIN')) {
            $session->set('appli', 'admin');
//            $message = $this->majUsers();
//            $session->getFlashBag()->add('notice-success', $message);
            return $this->render('AeagAeagBundle:Admin:index.html.twig');
        };

        if ($security->isGranted('ROLE_ADMINEDL')) {
            $session->set('appli', 'edl');
            return $this->redirect($this->generateUrl('aeag_edl'));
        };

        if ($security->isGranted('ROLE_ODEC')) {
            $session->set('appli', 'dec');
            return $this->redirect($this->generateUrl('aeag_dec'));
        };
        if ($security->isGranted('ROLE_FRD')) {
            $session->set('appli', 'frd');
            return $this->redirect($this->generateUrl('aeag_frd'));
        };

        if ($security->isGranted('ROLE_EDL')) {
            $session->set('appli', 'edl');
            return $this->redirect($this->generateUrl('aeag_edl'));
        };

        if ($security->isGranted('ROLE_STOCK')) {
            $session->set('appli', 'stock');
            return $this->redirect($this->generateUrl('aeag_stock'));
        };

        if ($security->isGranted('ROLE_SQE')) {
            $session->set('appli', 'sqe');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        };

        if ($security->isGranted('ROLE_ADMINDIE')) {
            $session->set('appli', 'die');
            return $this->redirect($this->generateUrl('aeag_die_admin'));
        };


        return $this->render('AeagAeagBundle:Default:interdit.html.twig');
//        return $this->render('AeagAeagBundle:Default:index.html.twig', array('roles' => $roles));
    }

    public function envoyerMessageAllAction($id = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagAeagBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'accueil');
        $session->set('controller', 'Aeag');
        $session->set('fonction', 'envoyerMessageAll');
        $em = $this->get('doctrine')->getManager();

        $security = $this->get('security.authorization_checker');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');

        if ($security->isGranted('ROLE_ADMIN')) {
            $utilisateurs = $repoUsers->getUsersByRole('ROLE_AEAG');
        } elseif ($security->isGranted('ROLE_ODEC')) {
            $utilisateurs = $repoUsers->getUsersByRole('ROLE_ODEC');
        } elseif ($security->isGranted('ROLE_FRD')) {
            $utilisateurs = $repoUsers->getUsersByRole('ROLE_FRD');
        } elseif ($security->isGranted('ROLE_SQE')) {
            $utilisateurs = $repoUsers->getUsersByRole('ROLE_SQE');
        } elseif ($security->isGranted('ROLE_EDL')) {
            $utilisateurs = $repoUsers->getUsersByRole('ROLE_EDL');
        } else {
            $utilisateurs = $repoUsers->getUsersByRole('ROLE_AEAG');
        }


        $envoyerMessageAll = new EnvoyerMessageAll();
        $mailListe = array();
        $i = 1;
        foreach ($utilisateurs as $utilisateur) {
            if ($utilisateur->getEmail()) {
                $domain = strstr($utilisateur->getEmail(), '@');
                if ($domain <> '@a-renseigner-merci.svp') {
                    $correspondant = null;
                    if ($utilisateur->getCorrespondant()) {
                        $correspondant = $repoCorrespondant->getCorrespondantById($utilisateur->getCorrespondant());
                    } else {
                        $correspondant = $repoCorrespondant->getCorrespondant($utilisateur->getUsername());
                    }
                    $mailListe[$i] = $utilisateur->getEmail() . ' (' . $utilisateur->getUserName();
                    if ($correspondant) {
                        $mailListe[$i] = $mailListe[$i] . ' ' . $correspondant->getAdr1() . ' ' . $correspondant->getAdr2() . ')';
                    } else {
                        $mailListe[$i] = $mailListe[$i] . ')';
                    }
                    $i++;
                }
            }
            if ($utilisateur->getEmail1()) {
                $domain = strstr($utilisateur->getEmail1(), '@');
                if ($domain <> '@a-renseigner-merci.svp') {
                    $correspondant = null;
                    if ($utilisateur->getCorrespondant()) {
                        $correspondant = $repoCorrespondant->getCorrespondantById($utilisateur->getCorrespondant());
                    } else {
                        $correspondant = $repoCorrespondant->getCorrespondant($utilisateur->getUsername());
                    }
                    $mailListe[$i] = $utilisateur->getEmail1() . ' (' . $utilisateur->getUserName();
                    if ($correspondant) {
                        $mailListe[$i] = $mailListe[$i] . ' ' . $correspondant->getAdr1() . ' ' . $correspondant->getAdr2() . ')';
                    } else {
                        $mailListe[$i] = $mailListe[$i] . ')';
                    }
                    $i++;
                }
            }
            if ($utilisateur->getEmail2()) {
                $domain = strstr($utilisateur->getEmail2(), '@');
                if ($domain <> '@a-renseigner-merci.svp') {
                    $correspondant = null;
                    if ($utilisateur->getCorrespondant()) {
                        $correspondant = $repoCorrespondant->getCorrespondantById($utilisateur->getCorrespondant());
                    } else {
                        $correspondant = $repoCorrespondant->getCorrespondant($utilisateur->getUsername());
                    }
                    $mailListe[$i] = $utilisateur->getEmail2() . ' (' . $utilisateur->getUserName();
                    if ($correspondant) {
                        $mailListe[$i] = $mailListe[$i] . ' ' . $correspondant->getAdr1() . ' ' . $correspondant->getAdr2() . ')';
                    } else {
                        $mailListe[$i] = $mailListe[$i] . ')';
                    }
                    $i++;
                }
            }
        }
        $form = $this->createForm(new EnvoyerMessageAllType($mailListe), $envoyerMessageAll);
        $message = null;


        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);
            if ($form->isValid()) {
                $messages = $this->get('aeag.messages');
                $texte = $envoyerMessageAll->getMessage();
                foreach ($envoyerMessageAll->getDestinataire() as $destinataire) {
                    $messages->createMessage($user, $destinataire, $em, $session, $texte);
                }

                $notifications = $this->get('aeag.notifications');
                $notifications->createNotification($user, $user, $em, $session, 'Message envoyé à tous les utilisateurs');

                // Récupération du service.
                $mailer = $this->get('mailer');
                foreach ($envoyerMessageAll->getDestinataire() as $destinataire) {
                    // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
                    $desti = explode(" ", $destinataire);
                    $mail = \Swift_Message::newInstance('Wonderful Subject')
                            ->setSubject($envoyerMessageAll->getSujet())
                            ->setFrom('automate@eau-adour-garonne.fr')
                            ->setTo(array($desti[0]))
                            ->setBody($envoyerMessageAll->getMessage(), 'text/html');

                    // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
                    $mailer->send($mail);
                }



                $this->get('session')->getFlashBag()->add('notice-success', 'Message envoyé avec succès !');

                return $this->redirect($session->get('retour'));
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
        $session->set('controller', 'Aeag');
        $session->set('fonction', 'envoyerMessage');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $security = $this->get('security.authorization_checker');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoInterlocuteur = $em->getRepository('AeagAeagBundle:Interlocuteur');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        if ($security->isGranted('ROLE_SQE')) {
            $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByid($id);
            if (!$pgProgWebuser) {
                $User = $repoUsers->getUserById($id);
                $correspondant = null;
            } else {
                $User = $repoUsers->getUserByUsernamePassword($pgProgWebuser->getLogin(), $pgProgWebuser->getPwd());
                $correspondant = null;
            }
        } else {
            $User = $repoUsers->getUserById($id);
            if ($User->getCorrespondant()) {
                $correspondant = $repoCorrespondant->getCorrespondantById($User->getCorrespondant());
            } else {
                $correspondant = null;
            }
        }


        if (!$User) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement avec la cle : ' . $id);
        }

        $envoyerMessage = new EnvoyerMessage();
        $mailListe = array();
        if ($correspondant) {
            $mailListe[0] = $User->getEmail() . ' (' . $correspondant->getAdr1() . ' ' . $correspondant->getCp() . ' ' . $correspondant->getVille() . ')';
        } else {
            $mailListe[0] = $User->getEmail() . ' (' . $User->getUserName() . ' ' . $User->getPrenom() . ')';
        }
        $i = 1;
        if ($User->getEmail1()) {
            $mailListe[$i] = $User->getEmail1();
            $i++;
        }
        if ($User->getEmail2()) {
            $mailListe[$i] = $User->getEmail2();
            $i++;
        }


        if ($correspondant) {
            $interlocuteurs = $repoInterlocuteur->getInterlocuteursByCorrespondant($correspondant->getId());
            foreach ($interlocuteurs as $interlocuteur) {
                if ($interlocuteur->getEmail()) {
                    $mailListe[$i] = $interlocuteur->getEmail() . ' (' . $interlocuteur->getNom() . ' ' . $interlocuteur->getPrenom() . ' ' . $interlocuteur->getFonction() . ')';
                    $i++;
                }
            }
        }

        //echo var_dump($mailListe);
        //return new Response ('ici');

        $form = $this->createForm(new EnvoyerMessageType($mailListe), $envoyerMessage);
        $message = null;
        $maj = 'ko';

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);
            if ($form->isValid()) {

                $messages = $this->get('aeag.messages');
                $texte = $envoyerMessage->getMessage();
                $messages->createMessage($user, $User, $em, $session, $texte);


                $notifications = $this->get('aeag.notifications');
                $notifications->createNotification($user, $User, $em, $session, 'Message envoyé à ' . $User->getUsername());

                $maj = 'ok';

                $this->get('session')->getFlashBag()->add('notice-success', 'Message envoyé avec succès à ' . $User->getUsername() . ' !');

                // Récupération du service.
                $mailer = $this->get('mailer');
                foreach ($envoyerMessage->getDestinataire() as $destinataire) {
                    // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
                    $desti = explode(" ", $destinataire);
                    $mail = \Swift_Message::newInstance('Wonderful Subject')
                            ->setSubject($envoyerMessage->getSujet())
                            ->setFrom('automate@eau-adour-garonne.fr')
                            ->setTo(array($desti[0]))
                            ->setBody($envoyerMessage->getMessage());

                    // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
                    $mailer->send($mail);
                }
                return $this->redirect($session->get('retour'));
            } else {
                return new Response('user ' . $user->getUsername() . ' dest : ' . $User->getUsername() . ' mail : ' . $envoyerMessage->getDestinataire());
            }
        }

        return $this->render('AeagAeagBundle:Default:envoyerMessage.html.twig', array(
                    'User' => $User,
                    'correspondant' => $correspondant,
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
        $session->set('controller', 'Aeag');
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
        $session->set('controller', 'Aeag');
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

    private function majUsers() {

        $em = $this->get('doctrine')->getManager();
        $factory = $this->get('security.encoder_factory');

        $repoUsers = $em->getRepository('AeagUserBundle:User');

        $entities = $repoUsers->getUsers();
        $nbModifies = 0;
        $message = '';

        foreach ($entities as $entity) {
//            if ($entity->getid() == 24) {
            $encoder = $factory->getEncoder($entity);
            $entity->setSalt('');
            // $entity->setPlainPwd($entity->getPassword());
            $password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
            $entity->setpassword($password);
            $em->persist($entity);
            $nbModifies++;
//            }
        }
        $em->flush();
        $message = "user  modifiés : " . $nbModifies;
        return $message;
    }

}
