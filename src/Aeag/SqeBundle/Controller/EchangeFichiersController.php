<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EchangeFichiersController extends Controller{
    
    public function indexAction() {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'index');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        
        // Récupération des programmations
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        if ($user->hasRole('ROLE_ADMINSQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAdmin();
        } else if ($user->hasRole('ROLE_PRESTASQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByPresta($user);
        } else if ($user->hasRole('ROLE_PROGSQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByProg($user);
        }
        
        return $this->render('AeagSqeBundle:EchangeFichiers:index.html.twig', array('user' => $user,
            'lotans' => $pgProgLotAns));
    }
    
    public function demandesAction() {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'index');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        
        $request = $this->container->get('request');
        $lotanId = $request->get('lotan');
        
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        
        $pgCmdDemandes = $repoPgCmdDemande->findByLotan($lotanId);
        
        return $this->render('AeagSqeBundle:EchangeFichiers:demandes.html.twig', array('user' => $user, 'demandes' => $pgCmdDemandes));
        
    }
    
}
