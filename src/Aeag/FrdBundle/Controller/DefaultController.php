<?php

namespace Aeag\FrdBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Aeag\FrdBundle\Entity\Parametre;
use Aeag\FrdBundle\Controller\ReferentielController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Aeag\FrdBundle\Entity\Browser;

class DefaultController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
        $session = $this->get('session');
        
        $session->set('retourErreur',$this->generateUrl('aeag_frd'));
        
        $emFrd = $this->getDoctrine()->getManager('frd');

        $session->set('appli', 'frd');
        $session->set('default', 'acceuil');
        $session->set('menu', '');


        $annee = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));
        
        if (!$annee) {
            $message = $this->forward('AeagFrdBundle:Referentiel:chargeAppli');
            $annee = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));
        }
       
        $annee = new \DateTime($annee->getLibelle());
     
        $session->set('annee', $annee);

        $message = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => 'LIB_MESSAGE'));
        if (strlen($message->getLibelle()) > 0) {
            $session->set('messageAdmin', $message->getLibelle());
        }
        
              
        if (is_object($user) && !($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))) {
            $parametre = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => 'MAINTENANCE'));
            if ($parametre->getLibelle() == 'O') {
                $parametre = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => 'LIB_MAINTENANCE'));
                return $this->render('AeagFrdBundle:Default:maintenance.html.twig', array(
                            'user' => $user,
                            'maintenance' => $parametre->getLibelle(),
                ));
            }
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINFRD')) {
            return $this->redirect($this->generateUrl('AeagFrdBundle_admin', array(
                                'user' => $user)));
        }
        
        return $this->redirect($this->generateUrl('AeagFrdBundle_membre'));

        /*$browser = new Browser();

        if ($browser->showInfo('browser') == 'Internet Explorer' && $browser->showInfo('version') < 8) {
            return $this->render('AeagFrdBundle:Default:version.html.twig', array(
                        'user' => $user,
                        'navigateur' => $browser->showInfo('browser'),
                        'version' => $browser->showInfo('version')
            ));
        } else {
            return $this->redirect($this->generateUrl('AeagFrdBundle_membre'));
        }*/
    }

    public function getBrowser() {
        if (isset($_SERVER["HTTP_USER_AGENT"]) OR ($_SERVER["HTTP_USER_AGENT"] != "")) {
            $visitor_user_agent = $_SERVER["HTTP_USER_AGENT"];
        } else {
            $visitor_user_agent = "Unknown";
        }
        $bname = 'Unknown';
        $version = "0.0.0";

        // Next get the name of the useragent yes seperately and for good reason
        if (eregi('MSIE', $visitor_user_agent) && !eregi('Opera', $visitor_user_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (eregi('Firefox', $visitor_user_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (eregi('Chrome', $visitor_user_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (eregi('Safari', $visitor_user_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (eregi('Opera', $visitor_user_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (eregi('Netscape', $visitor_user_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } elseif (eregi('Seamonkey', $visitor_user_agent)) {
            $bname = 'Seamonkey';
            $ub = "Seamonkey";
        } elseif (eregi('Konqueror', $visitor_user_agent)) {
            $bname = 'Konqueror';
            $ub = "Konqueror";
        } elseif (eregi('Navigator', $visitor_user_agent)) {
            $bname = 'Navigator';
            $ub = "Navigator";
        } elseif (eregi('Mosaic', $visitor_user_agent)) {
            $bname = 'Mosaic';
            $ub = "Mosaic";
        } elseif (eregi('Lynx', $visitor_user_agent)) {
            $bname = 'Lynx';
            $ub = "Lynx";
        } elseif (eregi('Amaya', $visitor_user_agent)) {
            $bname = 'Amaya';
            $ub = "Amaya";
        } elseif (eregi('Omniweb', $visitor_user_agent)) {
            $bname = 'Omniweb';
            $ub = "Omniweb";
        } elseif (eregi('Avant', $visitor_user_agent)) {
            $bname = 'Avant';
            $ub = "Avant";
        } elseif (eregi('Camino', $visitor_user_agent)) {
            $bname = 'Camino';
            $ub = "Camino";
        } elseif (eregi('Flock', $visitor_user_agent)) {
            $bname = 'Flock';
            $ub = "Flock";
        } elseif (eregi('AOL', $visitor_user_agent)) {
            $bname = 'AOL';
            $ub = "AOL";
        } elseif (eregi('AIR', $visitor_user_agent)) {
            $bname = 'AIR';
            $ub = "AIR";
        } elseif (eregi('Fluid', $visitor_user_agent)) {
            $bname = 'Fluid';
            $ub = "Fluid";
        } else {
            $bname = 'Unknown';
            $ub = "Unknown";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $path = '#(?<browser>' . join('|', $known) .
                ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($path, $visitor_user_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($visitor_user_agent, "Version") < strripos($visitor_user_agent, $ub)) {
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
            'userAgent' => $visitor_user_agent,
            'name' => $bname,
            'version' => $version,
            'path' => $path
        );
    }

}
