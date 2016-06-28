<?php

namespace Aeag\DieBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller {

    public function indexAction() {
        
         $user = $this->getUser();
          if (!$user) {
            return $this->render('AeagDieBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Admin');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'index');
        $em = $this->getDoctrine()->getEntityManager('die');
        
        return $this->render('AeagDieBundle:Admin:index.html.twig');
    }

}
