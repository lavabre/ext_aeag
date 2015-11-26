<?php

namespace Aeag\DecBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Aeag\DecBundle\Entity\Browser;
use Aeag\DecBundle\Entity\Parametre;
use Aeag\DecBundle\Entity\Statut;
use Aeag\DecBundle\Entity\Taux;
use Aeag\AeagBundle\Controller\AeagController;

class DefaultController extends Controller {

    public function indexAction() {
        $user = $this->getUser();
        $session = $this->get('session');

        $session->set('retourErreur', $this->generateUrl('aeag_dec'));

        $session->set('appli', 'dec');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $session->set('default', 'acceuil');
        $session->set('menu', '');

        $ua = $this->getBrowser();
//        $yourbrowser= "Your browser: " . $ua['name'] . " " . $ua['version'] . " on " .$ua['platform'] . " reports: <br >" . $ua['userAgent'];
//        print_r($yourbrowser);
        $session->set('browser', $ua['name']);

        $annee = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));

        if (!$annee) {
            $this->initBase($emDec);
        }

        $annee = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));

        $session->set('annee', $annee->getLibelle());

        $message = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'LIB_MESSAGE'));
        if (strlen($message->getLibelle()) > 0) {
            $session->set('messageAdmin', $message->getLibelle());
        }


        $parametre = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'MAINTENANCE'));
        $session->set('maintenance', $parametre->getLibelle());

        if (is_object($user) && !($this->get('security.authorization_checker')->isGranted('ROLE_ADMINDEC')) && !($this->get('security.authorization_checker')->isGranted('ROLE_ADMINDEC'))) {
            if ($parametre->getLibelle() == 'O') {
                $parametre = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'LIB_MAINTENANCE'));
                return $this->render('AeagDecBundle:Default:maintenance.html.twig', array(
                            'user' => $user,
                            'maintenance' => $parametre->getLibelle(),
                ));
            }
        }

        if (is_object($user)) {
            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINDEC')) {
            $session->set('role', 'ROLE_ADMINDEC');
            return $this->redirect($this->generateUrl('AeagDecBundle_admin', array(
                                'user' => $user)));
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ODEC')) {
            $session->set('role', 'ROLE_ODEC');
            return $this->redirect($this->generateUrl('AeagDecBundle_collecteur', array(
                                'user' => $user)));
        }


        return $this->render('AeagDecBundle:Default:index.html.twig', array(
                    'user' => $user,
                    'navigateur' => $browser->showInfo('browser'),
                    'version' => $browser->showInfo('version')
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

    public static function initBase($param_em) {
        $emDec = $param_em;
        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $parametre = new Parametre();
        $parametre->setCode('ANNEE');
        $parametre->setLibelle('2014');
        $emDec->persist($parametre);
        $parametre = new Parametre();
        $parametre->setCode('LIB_MESSAGE');
        $parametre->setLibelle('Message administrateur');
        $emDec->persist($parametre);
        $parametre = new Parametre();
        $parametre->setCode('MAINTENANCE');
        $parametre->setLibelle('N');
        $emDec->persist($parametre);
        $parametre = new Parametre();
        $parametre->setCode('LIB_MAINTENANCE');
        $parametre->setLibelle('Le site est en maintenance');
        $emDec->persist($parametre);
        $parametre = new Parametre();
        $parametre->setCode('REP_REFERENTIEL');
        $parametre->setLibelle('/base/extranet/Transfert/Dec/Referentiel');
        $emDec->persist($parametre);
        $parametre = new Parametre();
        $parametre->setCode('REP_IMPORT');
        $parametre->setLibelle('/base/extranet/Transfert/Dec/Import');
        $emDec->persist($parametre);
        $parametre = new Parametre();
        $parametre->setCode('REP_EXPORT');
        $parametre->setLibelle('/base/extranet/Transfert/Dec/Export');
        $emDec->persist($parametre);
        $parametre = new Parametre();
        $parametre->setCode('REP_DOCUMENT');
        $parametre->setLibelle('/base/extranet/Transfert/Dec/Document');
        $emDec->persist($parametre);
        $emDec->flush();

//        $statut = new Statut();
//        $statut->setCode('10');
//        $statut->setLibelle('En préparation');
//        $emDec->persist($statut);
//        $statut = new Statut();
//        $statut->setCode('11');
//        $statut->setLibelle('Saisie non conforme');
//        $emDec->persist($statut);
//        $statut = new Statut();
//        $statut->setCode('20');
//        $statut->setLibelle('Déclaration conforme');
//        $emDec->persist($statut);
//        $statut = new Statut();
//        $statut->setCode('21');
//        $statut->setLibelle('Déclaration non conforme');
//        $emDec->persist($statut);
//        $statut = new Statut();
//        $statut->setCode('22');
//        $statut->setLibelle('Déclaration pré-validée');
//        $emDec->persist($statut);
//        $statut = new Statut();
//        $statut->setCode('30');
//        $statut->setLibelle('Déclaration approuvée');
//        $emDec->persist($statut);
//        $statut = new Statut();
//        $statut->setCode('40');
//        $statut->setLibelle('Déclaration transférée');
//        $emDec->persist($statut);
//        $statut = new Statut();
//        $statut->setCode('50');
//        $statut->setLibelle('Déclaration traitée');
//        $emDec->persist($statut);
//        $statut = new Statut();
//        $statut->setCode('60');
//        $statut->setLibelle('Déclaration payée');
//        $emDec->persist($statut);
//        $statut = new Statut();
//        $statut->setCode('70');
//        $statut->setLibelle('Déclaration refusée');
//        $emDec->persist($statut);
//        $emDec->flush();

        $anneeDecl = $repoParametre->getParametreByCode('ANNEE');
        $taux = new Taux();
        $taux->setAnnee($anneeDecl->getLibelle());
        $taux->setCode('TAUXAIDE');
        $taux->setLibelle('taux agence de l\'eau');
        $taux->setValeur(0.35);
        $emDec->persist($taux);
        $emDec->flush();
        $taux = new Taux();
        $taux->setAnnee($anneeDecl->getLibelle() - 1);
        $taux->setCode('TAUXAIDE');
        $taux->setLibelle('taux agence de l\'eau');
        $taux->setValeur(0.35);
        $emDec->persist($taux);
        $emDec->flush();
    }

}
