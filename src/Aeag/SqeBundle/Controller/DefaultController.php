<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Aeag\SqeBundle\Entity\Parametre;
use Aeag\SqeBundle\Entity\Statut;
use Aeag\UserBundle\Entity\User;
use Aeag\AeagBundle\Entity\Message;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\SqeBundle\Entity\Form\EnvoyerMessage;
use Aeag\SqeBundle\Form\EnvoyerMessageType;
use Aeag\AeagBundle\Controller\AeagController;

class DefaultController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->clear();
        $session->set('retourErreur', $this->generateUrl('aeag_sqe'));
        $session->set('controller', 'default');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $factory = $this->get('security.encoder_factory');
        $repoParametre = $emSqe->getRepository('AeagSqeBundle:Parametre');
        $annee = $repoParametre->getParametreByCode('ANNEE');
        if (!$annee) {
            $this->initBase($emSqe, $em);
        }

        if (is_object($user)) {
            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);
        } else {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }

        $ua = $this->getBrowser();
//        $yourbrowser= "Your browser: " . $ua['name'] . " " . $ua['version'] . " on " .$ua['platform'] . " reports: <br >" . $ua['userAgent'];
//        print_r($yourbrowser);
        $session->set('browser', $ua['name']);

        $parametre = $repoParametre->getParametreByCode('EVOLUTION');
        if ($parametre->getLibelle() == 'false') {
            $session->set('evolution', false);
        } else {
            $session->set('evolution', true);
        }

        $message = $repoParametre->getParametreByCode('LIB_MESSAGE');
        if (strlen($message->getLibelle()) > 0) {
            $session->set('messageAdmin', $message->getLibelle());
        }


        $parametre = $repoParametre->getParametreByCode('MAINTENANCE');
        $session->set('maintenance', $parametre->getLibelle());

        if (is_object($user) && !($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) && !($this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE'))) {
            if ($parametre->getLibelle() == 'O') {
                $parametre = $repoParametre->getParametreByCode('LIB_MAINTENANCE');
                return $this->render('AeagSqeBundle:Default:maintenance.html.twig', array(
                            'user' => $user,
                            'maintenance' => $parametre->getLibelle(),
                ));
            }
        }

        if (is_object($user) && ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE'))) {
            // insertion des users
            $message = $this->majPgProgWebusers();
            $message = $this->initPgProgWebusers();
            // return new Response  ($message);
            //$session->getFlashBag()->add('notice-success', $message);
        }


        return $this->render('AeagSqeBundle:Default:index.html.twig');
    }

    public function envoyerMessageAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $session = $this->get('session');
        $session->set('controller', 'Default');
        $session->set('fonction', 'envoyerMessage');
        $user = $this->getUser();
        if (is_object($user)) {
            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);
        } else {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'contact');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoMessages = $em->getRepository('AeagAeagBundle:Message');
        $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');

        $admins = $repoUsers->getUsersByRole('ROLE_ADMINSQE');

        if (!$admins) {
            throw $this->createNotFoundException('Impossible de retouver les adminuistrateurs du site');
        }

        $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByLoginPassword($user->getusername(), $user->getpassword());

        $envoyerMessage = new EnvoyerMessage();
        $form = $this->createForm(new EnvoyerMessageType(), $envoyerMessage);
        $message = null;

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            $destinataires = $request->get('destinataire');

// Récupération du service.
            $mailer = $this->get('mailer');
            $dest = array();
            $nb = 0;
            foreach ($destinataires as $destinataire) {
                // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
                $bodyMsg = 'Envoyé par : ' . $pgProgWebuser->getNom() . ' (' . $pgProgWebuser->getMail() . ')' . PHP_EOL . PHP_EOL;
                $bodyMsg .= $envoyerMessage->getMessage();
                $desti = explode(" ", $destinataire);
                $mail = \Swift_Message::newInstance()
                        ->setSubject('[SQE Question] - ' . $envoyerMessage->getSujet())
                        ->setFrom(array('automate@eau-adour-garonne.fr'))
                        ->setTo(array($desti[0]))
                        ->setBody($bodyMsg);
                if ($envoyerMessage->getCopie()) {
                    $mail->addCc($envoyerMessage->getCopie());
                }
                // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
                $mailer->send($mail);

                for ($i = 0; $i < count($admins); $i++) {
                    if ($admins[$i]->getEmail() == $desti[0]) {
                        $nb++;
                        $message = new Message();
                        $message->setRecepteur($admins[$i]->getId());
                        $message->setEmetteur($user->getid());
                        $message->setNouveau(true);
                        $message->setIteration(2);
                        $texte = $envoyerMessage->getMessage();
                        $message->setMessage($texte);
                        $em->persist($message);
                    }
                }
            }

            $notification = new Notification();
            $notification->setRecepteur($user->getId());
            $notification->setEmetteur($user->getId());
            $notification->setNouveau(true);
            $notification->setIteration(2);
            $notification->setMessage('Message envoyé à ' . $nb . ' responsable(s)');
            $em->persist($notification);
            $em->flush();
            $notifications = $repoNotifications->getNotificationByRecepteur($user);
            $session->set('Notifications', $notifications);

            $this->get('session')->getFlashBag()->add('notice-success', 'Message envoyé avec succès !');


            return $this->redirect($this->generateUrl('aeag_sqe'));
        }


        return $this->render('AeagSqeBundle:Default:envoyerMessage.html.twig', array(
                    'admins' => $admins,
                    'user' => $pgProgWebuser,
                    'form' => $form->createView()
        ));
    }

    public function getBrowser() {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $path = '#(?<browser>' . join('|', $known) .
                ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($path, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        // check if we have a number
        if ($version == null || $version == "") {
            $version = "?";
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'path' => $path
        );
    }

    public static function initBase($param_emSqe, $param_em) {
        $emSqe = $param_emSqe;
        $parametre = new Parametre();
        $parametre->setCode('ANNEE');
        $parametre->setLibelle('2014');
        $emSqe->persist($parametre);
        $parametre = new Parametre();
        $parametre->setCode('LIB_MESSAGE');
        $parametre->setLibelle('Message administrateur');
        $emSqe->persist($parametre);
        $parametre = new Parametre();
        $parametre->setCode('MAINTENANCE');
        $parametre->setLibelle('N');
        $emSqe->persist($parametre);
        $parametre = new Parametre();
        $parametre->setCode('LIB_MAINTENANCE');
        $parametre->setLibelle('Le site est en maintenance');
        $emSqe->persist($parametre);
        $parametre = new Parametre();
        $parametre->setCode('REP_CSV');
        $parametre->setLibelle('/base/extranet/Transfert/Sqe/Csv');
        $emSqe->persist($parametre);
        $emSqe->flush();

//        $statut = new Statut();
//        $statut->setCode('P-10');
//        $statut->setLibelle('Programmation encours');
//        $emSqe->persist($statut);
//        $statut = new Statut();
//        $statut->setCode('P-20');
//        $statut->setLibelle('Programmtion pré-validée');
//        $emSqe->persist($statut);
//        $statut = new Statut();
//        $statut->setCode('P-30');
//        $statut->setLibelle('Programmtion validée');
//        $emSqe->persist($statut);
//        $statut = new Statut();
//        $statut->setCode('P-40');
//        $statut->setLibelle('Programmtion transférée');
//        $emSqe->persist($statut);
//        $emSqe->flush();
    }

    public function majPgProgWebusers() {

        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $factory = $this->get('security.encoder_factory');

        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebusers = $repoPgProgWebusers->getPgProgWebusers();
        $pgProgWebusersNbModifies = 0;
        $message = '';


        foreach ($pgProgWebusers as $pgProgWebuser) {
            $entityUser = $repoUsers->getUserByUsernamePassword($pgProgWebuser->getlogin(), $pgProgWebuser->getPwd());
            if ($entityUser) {
                $pgProgWebuser->setExtId($entityUser->getId());
                $pgProgWebuser->setMail($entityUser->getEmail());
                $pgProgWebuser->setPwd($entityUser->getPassword());
                $emSqe->persist($pgProgWebuser);
                $pgProgWebusersNbModifies++;
            }
        }
        $em->flush();
        $emSqe->flush();
        $message = "pgProgWebuser  modifiés : " . $pgProgWebusersNbModifies;
        return $message;
    }

    public function initPgProgWebusers() {

        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $factory = $this->get('security.encoder_factory');

        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebusers = $repoPgProgWebusers->getPgProgWebusers();

        $pgProgWebusersNbCrees = 0;
        $pgProgWebusersNbModifies = 0;
        $message = '';


        foreach ($pgProgWebusers as $pgProgWebuser) {
            if ($pgProgWebuser->getExtId()) {
                $user = $repoUsers->getUserById($pgProgWebuser->getExtId());
            } else {
                $user = null;
            }
            if (!$user) {
                $entityUser = new User();
                $entityUser->setEnabled(true);
                $pgProgWebusersNbCrees++;
            } else {
                $entityUser = $user;
                $pgProgWebusersNbModifies++;
            }
            $tabRoles = array();
            $tabRoles[] = 'ROLE_AEAG';
            $tabRoles[] = 'ROLE_SQE';
            if ($pgProgWebuser->getTypeUser() == 'PROG') {
                $entityUser->removeRole('ROLE_ADMINSQE');
                $tabRoles[] = 'ROLE_PROGSQE';
            }
            if ($pgProgWebuser->getTypeUser() == 'ADMIN') {
                $entityUser->removeRole('ROLE_PROGSQE');
                $tabRoles[] = 'ROLE_ADMINSQE';
            }
            if ($pgProgWebuser->getTypeUser() == 'PREST') {
                $tabRoles[] = 'ROLE_PRESTASQE';
            }
            $entityUser->setRoles($tabRoles);
            $encoder = $factory->getEncoder($entityUser);
            $entityUser->setUsername($pgProgWebuser->getLogin());
            $entityUser->setSalt('');
            $password = $encoder->encodePassword($pgProgWebuser->getPwd(), $entityUser->getSalt());
            $entityUser->setpassword($password);
            $entityUser->setPlainPassword($entityUser->getPassword());
            $email = $pgProgWebuser->getMail();
            if ($email) {
                $entityUser->setEmail($email);
            } else {
                $entityUser->setEmail($pgProgWebuser->getLogin() . '@a-renseigner-merci.svp');
            }
            $em->persist($entityUser);

            //print_r('user : ' . $entityUser->getid() . ' ' . $entityUser->getUsername() .  ' ' . $entityUser->getEmail() . ' ' . $entityUser->getPassword() . '  prohuser : ' . $pgProgWebuser->getid() . ' ' .  $pgProgWebuser->getNom() . '\n  ');

            $pgProgWebuser->setExtId($entityUser->getId());
            $pgProgWebuser->setMail($entityUser->getEmail());
            $pgProgWebuser->setPwd($entityUser->getPassword());
            $emSqe->persist($pgProgWebuser);
        }
        $em->flush();
        $emSqe->flush();
        $message = "users sqe crees : " . $pgProgWebusersNbCrees . "   users sqe modifiés : " . $pgProgWebusersNbModifies;
        return $message;
    }

}
