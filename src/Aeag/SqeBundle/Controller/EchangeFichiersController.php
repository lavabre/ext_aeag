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
        $pgProgLotAns = array();
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
    
    public function demandesAction($lotanId) {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'demandes');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        
        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgProgLotAn = $repoPgProgLotAn->findOneById($lotanId);
        $pgCmdDemandes = $repoPgCmdDemande->findBy(array('lotan' => $lotanId));

        return $this->render('AeagSqeBundle:EchangeFichiers:demandes.html.twig', 
                array('user' => $pgProgWebUser, 
                    'demandes' => $pgCmdDemandes, 
                    'lotan' => $pgProgLotAn));
        
    }
    
    public function telechargerAction($demandeId) {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'telecharger');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        
        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        if ($pgCmdDemande) {
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
            
            // On log le téléchargement
            $log = new \Aeag\SqeBundle\Entity\PgCmdDwnldUsrDmd();
            $log->setUser($pgProgWebUser);
            $log->setDemande($pgCmdDemande);
            $log->setDate(new \DateTime());
            $emSqe->persist($log);
            $emSqe->flush();
            
            header('Content-Type', 'application/zip');
            header('Content-disposition: attachment; filename="' . $zipName . '"');
            header('Content-Length: ' . filesize($pathBase.$zipName));
            readfile($pathBase.$zipName);
            
        }

    }
    
    public function reponsesAction($demandeId) {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'reponses');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        
        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findBy(array('demande' => $demandeId,
                                                                'suppr' => 'N'));
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());

        return $this->render('AeagSqeBundle:EchangeFichiers:reponses.html.twig', 
                array('reponses' => $pgCmdFichiersRps, 
                    'demande' => $pgCmdDemande,
                    'user' => $pgProgWebUser));
    }
    
    public function deposerReponseAction($demandeId) {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'deposerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        
        // Récupération des valeurs du fichier
        $nomFichier = $_FILES["fichier"]["name"];
        
        // Enregistrement des valeurs en base
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        
        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R10');
        
        $reponse = new \Aeag\SqeBundle\Entity\PgCmdFichiersRps();
        $reponse->setDemande($pgCmdDemande);
        $reponse->setNomFichier($nomFichier);
        $reponse->setDateDepot(new \DateTime());
        $reponse->setTypeFichier('RPS');
        $reponse->setPhaseFichier($pgProgPhases);
        $reponse->setUser($pgProgWebUser);
        $reponse->setSuppr('N');
        
        $emSqe->persist($reponse);
        $emSqe->flush();
        
        // Enregistrement du fichier sur le serveur
        $pathBase = "/base/extranet/Transfert/Sqe/Echanges/";
        $pathBase .= $pgCmdDemande->getAnneeProg().'/'.$pgCmdDemande->getCommanditaire()->getNomCorres().
                '/'.$pgCmdDemande->getLotan()->getLot()->getId().'/'.$pgCmdDemande->getLotan()->getId().'/'.$reponse->getId();
        
        if (!mkdir($pathBase)) {
            echo 'Le répertoire n\'a pas pu être créé';
        }
        
        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $pathBase.'/'.$nomFichier)) {
            return $this->redirect($this->generateUrl('AeagSqeBundle_echangefichiers_demandes',array('lotanId' => $pgCmdDemande->getLotan()->getId())));
        } else {
            echo "Attaque potentielle par téléchargement de fichiers.
                  Voici plus d'informations :\n";
        }
    }
    
    public function telechargerReponseAction($reponseId, $typeFichier) {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'deposerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        
        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findOneById($reponseId);
        
        // Récupération du fichier
        $pathBase = "/base/extranet/Transfert/Sqe/Echanges/";
        $pathBase .= $pgCmdFichiersRps->getDemande()->getAnneeProg().'/'.$pgCmdFichiersRps->getDemande()->getCommanditaire()->getNomCorres().
                '/'.$pgCmdFichiersRps->getDemande()->getLotan()->getLot()->getId().'/'.$pgCmdFichiersRps->getDemande()->getLotan()->getId().'/'.$reponseId;
        
        switch($typeFichier) {
            case "RPS" :
                $fileName = $pgCmdFichiersRps->getNomFichier();
                break;
            case "CR" :
                $fileName = $pgCmdFichiersRps->getNomFichierCompteRendu();
                break;
            case "DB" :    
                $fileName = $pgCmdFichiersRps->getNomFichierDonneesBrutes();
                break;
        }
        
        
        // On log le téléchargement
        $log = new \Aeag\SqeBundle\Entity\PgCmdDwnldUsrRps();
        $log->setUser($pgProgWebUser);
        $log->setFichierReponse($pgCmdFichiersRps);
        $log->setDate(new \DateTime());
        $log->setTypeFichier($typeFichier);
        $emSqe->persist($log);
        $emSqe->flush();
        
        header('Content-Type', 'application/zip');
        header('Content-disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($pathBase.'/'.$fileName));
        readfile($pathBase.'/'.$fileName);
    }
    
    public function supprimerReponseAction($reponseId) {
        
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'deposerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        
        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findOneById($reponseId);
        
        // Suppression physique des fichiers
        $pathBase = "/base/extranet/Transfert/Sqe/Echanges/";
        $pathBase .= $pgCmdFichiersRps->getDemande()->getAnneeProg().'/'.$pgCmdFichiersRps->getDemande()->getCommanditaire()->getNomCorres().
                '/'.$pgCmdFichiersRps->getDemande()->getLotan()->getLot()->getId().'/'.$pgCmdFichiersRps->getDemande()->getLotan()->getId().'/'.$reponseId;
        
        if ($this->_rmdirRecursive($pathBase)) {
            // Suppression en base
            $pgCmdFichiersRps->setSuppr('O');
            $emSqe->flush();
        }
        
        return $this->redirect($this->generateUrl('AeagSqeBundle_echangefichiers_demandes',array('lotanId' => $pgCmdFichiersRps->getDemande()->getLotan()->getId())));
    }
    
    private function _rmdirRecursive($dir) {
        //Liste le contenu du répertoire dans un tableau
        $dir_content = scandir($dir);
        //Est-ce bien un répertoire?
        if ($dir_content !== FALSE) {
            //Pour chaque entrée du répertoire
            foreach ($dir_content as $entry) {
                //Raccourcis symboliques sous Unix, on passe
                if (!in_array($entry, array('.', '..'))) {
                    //On retrouve le chemin par rapport au début
                    $entry = $dir . '/' . $entry;
                    //Cette entrée n'est pas un dossier: on l'efface
                    if (!is_dir($entry)) {
                        unlink($entry);
                    }
                    //Cette entrée est un dossier, on recommence sur ce dossier
                    else {
                        rmdir_recursive($entry);
                    }
                }
            }
        }
        //On a bien effacé toutes les entrées du dossier, on peut à présent l'effacer
        return rmdir($dir);
    }

}
