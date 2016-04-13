<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\AeagBundle\Controller\AeagController;

class EchangeFichiersController extends Controller {

    public function indexAction() {
        
        $user = $this->getUser();
        if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
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
        if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
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
        $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandeByLotan($pgProgLotAn);
        
        $reponses = array();
        $reponsesMax = array();
        foreach ($pgCmdDemandes as $pgCmdDemande) {
            $reponses[$pgCmdDemande->getId()] = $repoPgCmdFichiersRps->getReponsesValidesByDemande($pgCmdDemande->getId());
            $reponsesMax[$pgCmdDemande->getId()] = $repoPgCmdFichiersRps->findBy(array('demande' => $pgCmdDemande->getId(), 'typeFichier' => 'RPS','suppr' => 'N'));
        }
        return $this->render('AeagSqeBundle:EchangeFichiers:demandes.html.twig', array('user' => $pgProgWebUser,
                    'demandes' => $pgCmdDemandes,
                    'lotan' => $pgProgLotAn,
                    'reponses' => $reponses,
                    'reponsesMax' => $reponsesMax));
    }

    public function telechargerAction($demandeId) {
        $user = $this->getUser();
        if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
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

            $pathBase = $this->getCheminEchange($pgCmdDemande);

            // Changement de la phase s'il est téléchargé par un presta pour la première fois
            if ($user->hasRole('ROLE_PRESTASQE') && substr($pgCmdDemande->getPhaseDemande()->getCodePhase(), 1) < '25') {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D25');
                if (count($pgProgPhases) > 0) {
                    $pgCmdDemande->setPhaseDemande($pgProgPhases);
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
            header('Content-Length: ' . filesize($pathBase . $zipName));
            readfile($pathBase . $zipName);
            exit();
        }
    }

    public function reponsesAction($demandeId) {
        $user = $this->getUser();
        if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'reponses');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findBy(array('demande' => $demandeId,
            'typeFichier' => 'RPS',
            'suppr' => 'N'));
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());

        return $this->render('AeagSqeBundle:EchangeFichiers:reponses.html.twig', array('reponses' => $pgCmdFichiersRps,
                    'demande' => $pgCmdDemande,
                    'user' => $pgProgWebUser));
    }

    public function selectionnerReponseAction($demandeId) {
        $user = $this->getUser();
        if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'reponses');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');

        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);

        return $this->render('AeagSqeBundle:EchangeFichiers:selectionnerReponse.html.twig', array('demande' => $pgCmdDemande));
    }

    public function deposerReponseAction($demandeId) {
        $user = $this->getUser();
        if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'deposerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $em = $this->get('doctrine')->getManager();

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R10');

        // Récupération des valeurs du fichier
        $nomFichier = $_FILES["fichier"]["name"];
        if (substr($nomFichier, -3) != "zip") {
            $session->getFlashBag()->add('notice-error', 'Le fichier déposé n\'est pas un fichier zip');
            return $this->redirect($this->generateUrl('AeagSqeBundle_echangefichiers_demandes', array('lotanId' => $pgCmdDemande->getLotan()->getId())));
        }

        // Enregistrement des valeurs en base
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
        $pathBase = $this->getCheminEchange($pgCmdDemande, $reponse->getId());
        if (!mkdir($pathBase)) {
            $session->getFlashBag()->add('notice-error', 'Le répertoire n\'a pas pu être créé');
        }

        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $pathBase . '/' . $nomFichier)) {

            // Envoi du fichier sur le serveur du sandre pour validationFormat
            if ($this->envoiFichierValidationFormat($emSqe, $session, $reponse, $pathBase . '/' . $nomFichier)) {
                // Changement de la phase de la réponse 
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R15');
                $reponse->setPhaseFichier($pgProgPhases);
                $emSqe->persist($reponse);
                $emSqe->flush();

                // Envoi d'un mail
                $objetMessage = "RAI " . $reponse->getId() . " soumise et en cours de validation";
                $txtMessage = "Votre RAI (id " . $reponse->getId() . ") concernant la DAI " . $pgCmdDemande->getCodeDemandeCmd() . " a été soumise. Le fichier " . $reponse->getNomFichier() . " est en cours de validation. "
                        . "Vous serez informé lorsque celle-ci sera validée. ";
                $this->_envoiMessage($em, $txtMessage, $pgProgWebUser, $objetMessage);

                $session->getFlashBag()->add('notice-success', 'Le fichier ' . $nomFichier . ' a été traité, un email vous a été envoyé');
            } else {
                $session->getFlashBag()->add('notice-error', 'Le fichier ' . $nomFichier . ' a rencontré une erreur lors de la validation');
                $this->_rmdirRecursive($pathBase);
                $emSqe->remove($reponse);
                $emSqe->flush();
            }
        } else {
            $emSqe->remove($reponse);
            $emSqe->flush();

            $session->getFlashBag()->add('notice-error', 'Erreur lors du téléchargement du fichier ' . $nomFichier);
        }
        return $this->redirect($this->generateUrl('AeagSqeBundle_echangefichiers_demandes', array('lotanId' => $pgCmdDemande->getLotan()->getId())));
    }

    public function telechargerReponseAction($reponseId, $typeFichier) {
        $user = $this->getUser();
        if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'telechargerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findOneById($reponseId);

        // Récupération du fichier
        $pathBase = $this->getCheminEchange($pgCmdFichiersRps->getDemande(), $reponseId);

        switch ($typeFichier) {
            case "RPS" :
                $contentType = "application/zip";
                $fileName = $pgCmdFichiersRps->getNomFichier();
                break;
            case "CR" :
                $contentType = "application/octet-stream";
                $fileName = $pgCmdFichiersRps->getNomFichierCompteRendu();
                break;
            case "DB" :
                $contentType = "application/octet-stream";
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

        header('Content-Type', $contentType);
        header('Content-disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($pathBase . '/' . $fileName));
        readfile($pathBase . '/' . $fileName);
        exit();
    }

    public function supprimerReponseAction($reponseId) {

        $user = $this->getUser();
        if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'deposerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');

        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findOneById($reponseId);

        // Suppression physique des fichiers
        $pathBase = $this->getCheminEchange($pgCmdFichiersRps->getDemande(), $reponseId);
        if (file_exists($pathBase)) {
            if ($this->_rmdirRecursive($pathBase)) {
                // Suppression en base
                $pgCmdFichiersRps->setSuppr('O');
                $emSqe->persist($pgCmdFichiersRps);
                $emSqe->flush();
            }
        } else {
            $pgCmdFichiersRps->setSuppr('O');
            $emSqe->persist($pgCmdFichiersRps);
            $emSqe->flush();
        }

        return $this->redirect($this->generateUrl('AeagSqeBundle_echangefichiers_demandes', array('lotanId' => $pgCmdFichiersRps->getDemande()->getLotan()->getId())));
    }

    public function envoiFichierValidationFormat($em, $session, $pgCmdFichierRps, $fullFileName) {
        
        $xmlFileName = $this->extractXmlFile($session, $fullFileName);
        
        if ($xmlFileName == false) {
            return false;
        }
        
        $versionEdilabo = $this->getVersionEdilabo($xmlFileName);
        
        // Données POST
        $params = array (
                "XML" => $xmlFileName,
                "XSD" => $versionEdilabo, 
                "NomSI" => "Logiciel version 1", 
                "VersionSI" => "4.3", 
                "Transformation" => "1",
                "NomIntervenant" => "AGENCE DE L'EAU ADOUR-GARONNE",
                "CdIntervenant" => "18310006400033"
        );
        
        $r = $this->post("http://sandre.eaufrance.fr/PS/parseurSANDRE", $params);
        
        if ($r !== '') {
            // Analyse de la réponse 
            // Récupération des valeurs dans la réponse
            $reponseTab = json_decode(json_encode(\simplexml_load_string($r)), true);

            // Stockage des valeurs en base
            if (isset($reponseTab['LienAcquittement']) && isset($reponseTab['LienCertificat'])) {
                $pgCmdFichierRps->setLienAcquitSandre($reponseTab['LienAcquittement']);
                $pgCmdFichierRps->setLienCertifSandre($reponseTab['LienCertificat']);
                $em->persist($pgCmdFichierRps);
                $em->flush();

                return true;
            }        
            return false;
        } else {
            $session->getFlashBag()->add('notice-error', 'Le webservice retourne un fichier vide');
            return false;
        }
        
    }

    protected function extractXmlFile($session, $fullFileName) {
        
        // Récupération de la version d'edilabo
        // Dézippe du fichier
        $zip = new \ZipArchive();
        // Ouvrir l'archive
        if ($zip->open($fullFileName) !== true) {
            $session->getFlashBag()->add('notice-error', 'Fichier zip erroné');
            return false;
        }
        // Extraire le contenu dans le dossier de destination
        $destination = str_replace(".zip", "", $fullFileName);
        $zip->extractTo($destination);
        // Fermer l'archive
        $zip->close();

        // Lecture du fichier
        $files = scandir($destination);

        if (count($files) != 3) {
            $session->getFlashBag()->add('notice-error', 'L\'archive zip contient trop de fichier (un seul requis)');
            return false;
        }

        $fichierXml = '';
        foreach ($files as $file) {
            if (strpos($file, '.xml') !== false) {
                $fichierXml = $destination . '/' . $file;
            }
        }

        if ($fichierXml == '') {
            $session->getFlashBag()->add('notice-error', 'Le fichier RAI n\'est pas au format XML ' . strpos($file, '.xml') . ' || ' . $destination . '/' . $file);
            return false;
        }
        
        return $fichierXml;
    }
    
    protected function getVersionEdilabo($xmlFileName) {
        $raiTab = json_decode(json_encode(\simplexml_load_file($xmlFileName)), true);
        
        $codeScenario = $raiTab['Scenario']['CodeScenario'];
        $versionScenario = $raiTab['Scenario']['VersionScenario'];
        
        return $codeScenario.';'.$versionScenario;
        
    }

    protected function getCheminEchange($pgCmdDemande, $reponseId = null) {
        $chemin = $this->container->getParameter('repertoire_echange');
        $chemin .= $pgCmdDemande->getAnneeProg() . '/' . $pgCmdDemande->getCommanditaire()->getNomCorres() .
                '/' . $pgCmdDemande->getLotan()->getLot()->getId() . '/' . $pgCmdDemande->getLotan()->getId() . '/';
        if (!is_null($reponseId)) {
            $chemin .= $reponseId;
        }

        return $chemin;
    }
    
    protected function post($url, $params) {

        if (isset ($params['XML'])) {
            $params['XML'] = \curl_file_create($params['XML'], 'application/xml', 'data.xml');
        }
        
        // Initialisation de CURL
        $ch = \curl_init($url);
                        
        //\curl_setopt($ch, CURLOPT_URL, $url);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($ch, CURLOPT_POST, 1);
        \curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        // Executer et fermer la session curl
        $result = \curl_exec($ch);
        $result = \urldecode($result);
        \curl_close($ch);
        return $result;
    }

    protected function _envoiMessage($em, $txtMessage, $destinataire, $objet, $expediteur = 'automate@eau-adour-garonne.fr') {

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
                ->setBody($this->renderView('AeagSqeBundle:EchangeFichiers:reponseEmail.txt.twig', array('message' => $txtMessage
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
                       $this->_rmdirRecursive($entry);
                    }
                }
            }
        }
        //On a bien effacé toutes les entrées du dossier, on peut à présent l'effacer
        return rmdir($dir);
    }

}
