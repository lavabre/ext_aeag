<?php

namespace Aeag\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\UserBundle\Entity\User;
use Aeag\AeagBundle\Entity\Statistiques;
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

}
