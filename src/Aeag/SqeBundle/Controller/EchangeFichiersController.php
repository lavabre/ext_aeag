<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\AeagBundle\Controller\AeagController;

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
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        
        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgProgLotAn = $repoPgProgLotAn->findOneById($lotanId);
        $pgCmdDemandes = $repoPgCmdDemande->findBy(array('lotan' => $lotanId));
        $reponses = array();
        $reponsesMax = array();
        foreach($pgCmdDemandes as $pgCmdDemande) {
            $reponses[$pgCmdDemande->getId()] = $repoPgCmdFichiersRps->getReponseByDemande($pgCmdDemande->getId());
            $reponsesMax[$pgCmdDemande->getId()] = $repoPgCmdFichiersRps->findBy(array('demande' => $pgCmdDemande->getId(),'suppr' => 'N'));
        }        
        return $this->render('AeagSqeBundle:EchangeFichiers:demandes.html.twig', 
                array('user' => $pgProgWebUser, 
                    'demandes' => $pgCmdDemandes, 
                    'lotan' => $pgProgLotAn,
                    'reponses' => $reponses,
                    'reponsesMax' => $reponsesMax));
        
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
                    'user' => $pgProgWebUser, 
                    'reponseMax' => $repoPgCmdDemande->getNbReponseByDemande($pgCmdDemande)));
    }
    
    public function deposerReponseAction($demandeId) {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'deposerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $em = $this->get('doctrine')->getManager();
        
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
            
            // Envoi d'un mail
            $objetMessage = "RAI ".$reponse->getId()." soumise et en cours de validation";
            $txtMessage = "Votre RAI (id ".$reponse->getId().") concernant la DAI ".$pgCmdDemande->getCodeDemandeCmd()." a été soumise. Le fichier ".$reponse->getNomFichier()." est en cours de validation. "
                    . "Vous serez informé lorsque celle-ci sera validée. ";
            $this->_envoiMessage($em, $txtMessage, $pgProgWebUser, $objetMessage);
            
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
    
    protected function _envoiMessage($em, $txtMessage, $destinataire, $objet, $expediteur = 'automate@eau-adour-garonne.fr'){
        
        $message = new Message();
        $message->setRecepteur($destinataire->getId());
        $message->setEmetteur($destinataire->getId());
        $message->setNouveau(true);
        $message->setIteration(2);
        $texte = "Bonjour ," . PHP_EOL;
        $texte .= " " . PHP_EOL;
        $texte .= $txtMessage;
        $texte .= " " . PHP_EOL;
        $texte .= "Cordialement.";
        $message->setMessage($texte);
        $em->persist($message);

        $notification = new Notification();
        $notification->setRecepteur($destinataire->getId());
        $notification->setEmetteur($destinataire->getId());
        $notification->setNouveau(true);
        $notification->setIteration(2);
        $notification->setMessage($txtMessage);
        $em->persist($notification);
        
        // Récupération du service
        $mailer = $this->get('mailer');
        // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
        $mail = \Swift_Message::newInstance('Wonderful Subject')
                ->setSubject($objet)
                ->setFrom($expediteur)
                ->setTo($destinataire->getMail())
                ->setBody($this->renderView('AeagSqeBundle:EchangeFichiers:validerReponse.txt.twig', array(
        )));

        $mailer->send($mail);

        $em->flush();
        
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
