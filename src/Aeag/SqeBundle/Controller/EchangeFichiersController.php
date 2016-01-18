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
    
    public function telechargerAction() {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'index');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        
        $request = $this->container->get('request');
        $demandeId = $request->get('demande');
        
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        
        $pgCmdDemandes = $repoPgCmdDemande->findById($demandeId);
        if (count($pgCmdDemandes) > 0) {
            $pgCmdDemande = $pgCmdDemandes[0];
            // Récupération du fichier
            $zipName = str_replace('xml', 'zip', $pgCmdDemande->getNomFichier());
            $pathBase = "/base/extranet/Transfert/Sqe/Echanges/";
            $pathBase .=$pgCmdDemande->getAnneeProg().'/'.$pgCmdDemande->getCommanditaire()->getNomCorres().
                    '/'.$pgCmdDemande->getLotan()->getLot()->getId().'/'.$pgCmdDemande->getLotan()->getId().'/';
            
            // Changement de la phase s'il est téléchargé par un presta pour la première fois
            if ($user->hasRole('ROLE_PRESTASQE') && $pgCmdDemande->getPhaseDemande()->getId() !== 12) {
                $pgProgPhases = $repoPgProgPhases->findById(12);
                if (count($pgProgPhases) > 0) {
                    $pgCmdDemande->setPhaseDemande($pgProgPhases[0]);
                    $emSqe->persist($pgCmdDemande);
                    $emSqe->flush();
                }
            }
            
            header('Content-Type', 'application/zip');
            header('Content-disposition: attachment; filename="' . $zipName . '"');
            header('Content-Length: ' . filesize($pathBase.$zipName));
            readfile($pathBase.$zipName);
            
        }

    }
    
}
