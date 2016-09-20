<?php

namespace Aeag\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\UserBundle\Entity\User;
use Aeag\UserBundle\Entity\Statistiques;
use Aeag\UserBundle\Entity\Connectes;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Statistiques controller.
 *
 */
class StatistiquesController extends Controller {

    /**
     * Lists all Statistiques entities.
     *
     */
    public function indexAction() {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'index');
        $session->set('controller', 'Statistiques');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

        $repoStatistiques = $em->getRepository('AeagUserBundle:Statistiques');
        $repoUsers = $em->getRepository('AeagUserBundle:User');

        $roles = $user->getRoles();
        $admin = false;
        foreach ($roles as $role) {
            if (strstr($role, 'ADMIN')) {
                $admin = true;
                break;
            }
        }

        if ($admin) {
            $statistiques = $repoStatistiques->getStatistiques();
            $tabStats = array();
            $i = 0;
            foreach ($statistiques as $statistique) {
                $tabStats[$i]['stat'] = $statistique;
                $utilisateur = $repoUsers->getUserById($statistique->getUser());
                if ($utilisateur) {
                    $tabStats[$i]['user'] = $utilisateur;
                } else {
                    $tabStats[$i]['user'] = null;
                }
                $i++;
            }

            $session->set('retour', $this->generateUrl('AeagUserBundle_Statistiques'));

            return $this->render('AeagUserBundle:Statistiques:index.html.twig', array(
                        'user' => $user,
                        'entities' => $tabStats
            ));
        } else {
            return $this->redirect($this->generateUrl('aeag_homepage'));
        }
    }

    public function majStatistiquesAction() {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'index');
        $session->set('controller', 'Statistiques');
        $session->set('fonction', 'majStatistiques');
        $em = $this->get('doctrine')->getManager();

        $repoStatistiques = $em->getRepository('AeagUserBundle:Statistiques');
        $repoConnectes = $em->getRepository('AeagUserBundle:Connectes');

        $timestamp_5min = time() - (60 * 60); // 60 * 60 = nombre de secondes écoulées en 60 minutes
        $connectes = $repoConnectes->getConnectes5Minutes($timestamp_5min);
        foreach ($connectes as $connecte) {
            $statistiques = $repoStatistiques->getStatistiquesByIp($connecte->getIp());
            foreach ($statistiques as $statistique) {
                if (!$statistique->getDateFinConnexion()) {
                    $statistique->setDateFinConnexion(new \DateTime());
                    $em->persist($statistique);
                }
            }
            $em->remove($connecte);
        }

        $statistiques = $repoStatistiques->getConnectes();
        foreach ($statistiques as $statistique) {
            $connecte = $repoConnectes->getConnectesByIp($statistique->getIp());
            if (!$connecte) {
                if (!$statistique->getDateFinConnexion()) {
                    $statistique->setDateFinConnexion(new \DateTime());
                    $em->persist($statistique);
                }
            }
        }
        
        $em->flush();
        //      return new Response ('time : ' .  $timestamp_5min . ' connectes : ' . count($connectes));


        $connecte = $repoConnectes->getConnectesByIp($_SERVER['REMOTE_ADDR']);
        if (!$connecte) {
            $connecte = new Connectes();
            $connecte->setIp($_SERVER['REMOTE_ADDR']);
            $connecte->setTime(time());
            $em->persist($connecte);
            $em->flush();
        }


        if ($user) {
            $statistique = $repoStatistiques->getStatistiquesByUser($user->getId());
        } else {
            $statistique = $repoStatistiques->getStatistiquesByUser(0);
        }
        if (!$statistique) {
            $statistique = new Statistiques();
            if ($user) {
                $statistique->setUser($user->getId());
            } else {
                $statistique->setUser(0);
            }
            $statistique->setIp($_SERVER['REMOTE_ADDR']);
            $statistique->setNbConnexion(1);
            if ($user) {
                $statistique->setAppli($session->get('appli'));
            }
            $statistique->setDateDebutConnexion(new \DateTime());
        } else {
            if ($statistique->getDateFinConnexion()) {
                $statistique->setAppli($session->get('appli'));
                $statistique->setNbConnexion($statistique->getNbConnexion() + 1);
                $statistique->setDateDebutConnexion(new \DateTime());
                $statistique->setDateFinConnexion(null);
            } else {
                $statistique->setAppli($session->get('appli'));
            }
        }
        $em->persist($statistique);
        $em->flush();


        $nbStatistiques = $repoStatistiques->getNbStatistiques();
        $session->set('nbStatistiques', $nbStatistiques);
        $nbConnectes = $repoStatistiques->getNbConnectes();
        $session->set('nbConnectes', $nbConnectes);
        echo json_encode('ok');
        exit();
    }

}
