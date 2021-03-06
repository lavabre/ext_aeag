<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\AeagBundle\Controller\AeagController;
use Symfony\Component\HttpFoundation\Response;

class EchangeFichiersController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'index');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        // Récupération des programmations
        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgProgLotAns = array();
        if ($user->hasRole('ROLE_ADMINSQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAdminAlt('PC');
        } else if ($user->hasRole('ROLE_PRESTASQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByPrestaAlt($user, 'PC');
        } else if ($user->hasRole('ROLE_PROGSQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByProgAlt($user, 'PC');
        }

        $tabLotAns = array();
        $i = 0;
        foreach ($pgProgLotAns as $pgProgLot) {
            $tabLotAns[$i]['lotan'] = $repoPgProgLot->findOneById($pgProgLot['id']);
            $tabLotAns[$i]['anneeProg'] = $pgProgLot['annee_prog'];
            $tabPgProgLotAns = $repoPgProgLotAn->findBy(array("lot" => $pgProgLot['id'], "anneeProg" => $pgProgLot['annee_prog']));
            /*
              $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandeByLotans($tabPgProgLotAns);
              $nbReponses = 0;
              $nbReponsesMax = 0;
              foreach ($pgCmdDemandes as $pgCmdDemande) {
              $reponses[$pgCmdDemande->getId()] = $repoPgCmdFichiersRps->getReponsesValidesByDemande($pgCmdDemande->getId());
              $reponsesMax[$pgCmdDemande->getId()] = $repoPgCmdFichiersRps->findBy(array('demande' => $pgCmdDemande->getId(), 'typeFichier' => 'RPS', 'suppr' => 'N'));
              if (count($reponses[$pgCmdDemande->getId()]) > 0) {
              $nbReponses = $nbReponses + count($reponses[$pgCmdDemande->getId()]);
              }
              if (count($reponsesMax[$pgCmdDemande->getId()]) > 0) {
              $nbReponsesMax = $nbReponsesMax + count($reponsesMax[$pgCmdDemande->getId()]);
              } else {
              $nbReponsesMax++;
              }
              }
             */
            $nbReponses = 0;
            $nbReponsesMax = 0;
            foreach ($tabPgProgLotAns as $pgProgLotan) {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D40');
                $nbReponses = $nbReponses + $repoPgCmdDemande->getCountPgCmdDemandeByLotanPhase($pgProgLotan, $pgProgPhases);
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D50');
                $nbReponses = $nbReponses + $repoPgCmdDemande->getCountPgCmdDemandeByLotanPhase($pgProgLotan, $pgProgPhases);
                $nbReponsesMax = $nbReponsesMax + $repoPgCmdDemande->getCountPgCmdDemandeByLotan($pgProgLotan);
            }

            $tabLotAns[$i]['nbReponses'] = $nbReponses;
            $tabLotAns[$i]['nbReponsesMax'] = $nbReponsesMax;
            $i++;
        }

//    \Symfony\Component\VarDumper\VarDumper::dump($tabLotAns);
//        return new Response('');

        return $this->render('AeagSqeBundle:EchangeFichiers:index.html.twig', array('user' => $user,
                    'lotans' => $tabLotAns));
    }

    public function demandesAction($lotId, $anneeProg) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'demandes');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgProgLot = $repoPgProgLot->findOneById($lotId);
        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        //$pgProgLotAn = $repoPgProgLotAn->findOneById($lotanId);
        $pgProgLotAns = $repoPgProgLotAn->findBy(array("lot" => $lotId, "anneeProg" => $anneeProg));
        $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandeByLotans($pgProgLotAns);
        $autoriser = false;
        foreach ($pgCmdDemandes as $pgCmdDemande) {
            if ($pgCmdDemande->getPrestataire() == $pgProgWebUser->getPrestataire()) {
                $autoriser = true;
                break;
            }
        }
        if (!$autoriser and $user->hasRole('ROLE_PRESTASQE')) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }

        $reponses = array();
        $reponsesMax = array();
        foreach ($pgCmdDemandes as $pgCmdDemande) {
            $reponses[$pgCmdDemande->getId()] = $repoPgCmdFichiersRps->getReponsesValidesByDemande($pgCmdDemande->getId());
            $reponsesMax[$pgCmdDemande->getId()] = $repoPgCmdFichiersRps->findBy(array('demande' => $pgCmdDemande->getId(), 'typeFichier' => 'RPS', 'suppr' => 'N'));
        }
        return $this->render('AeagSqeBundle:EchangeFichiers:demandes.html.twig', array('user' => $pgProgWebUser,
                    'demandes' => $pgCmdDemandes,
                    'lot' => $pgProgLot,
                    'anneeProg' => $anneeProg,
                    //'lotan' => $pgProgLotAn,
                    'reponses' => $reponses,
                    'reponsesMax' => $reponsesMax));
    }

    public function telechargerAction($demandeId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
            $chemin = $this->getParameter('repertoire_echange');
            $pathBase = $this->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdDemande);

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

    public function telechargerFichierAction($demandeId = null, $nomFichier = null) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
            $chemin = $this->getParameter('repertoire_echange');
            $pathBase = $this->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdDemande);

            // Changement de la phase s'il est téléchargé par un presta pour la première fois
            if ($user->hasRole('ROLE_PRESTASQE') && substr($pgCmdDemande->getPhaseDemande()->getCodePhase(), 1) < '25') {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D25');
                if (count($pgProgPhases) > 0) {
                    $pgCmdDemande->setPhaseDemande($pgProgPhases);
                    $emSqe->persist($pgCmdDemande);
                    $emSqe->flush();
                }
            }

            $type = substr($nomFichier, -3);

            // On log le téléchargement
            $log = new \Aeag\SqeBundle\Entity\PgCmdDwnldUsrDmd();
            $log->setUser($pgProgWebUser);
            $log->setDemande($pgCmdDemande);
            $log->setDate(new \DateTime());
            $emSqe->persist($log);
            $emSqe->flush();



            header('Content-Type', "'application/" . $type . "'");
            header('Content-disposition: attachment; filename="' . $nomFichier . '"');
            header('Content-Length: ' . filesize($pathBase . $nomFichier));
            readfile($pathBase . $nomFichier);
            exit();
        }
    }

    public function reponsesAction($demandeId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
        $session->set('menu', 'donnees');
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
        $session->set('menu', 'donnees');
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
            return $this->redirect($this->generateUrl('AeagSqeBundle_echangefichiers_demandes', array('lotId' => $pgCmdDemande->getLotan()->getLot()->getId(), 'anneeProg' => $pgCmdDemande->getLotan()->getAnneeProg())));
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
        $chemin = $this->getParameter('repertoire_echange');
        $pathBase = $this->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdDemande, $reponse->getId());
        if (!mkdir($pathBase)) {
            $session->getFlashBag()->add('notice-error', 'Le répertoire n\'a pas pu être créé');
        }

        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $pathBase . '/' . $nomFichier)) {

            // Envoi du fichier sur le serveur du sandre pour validationFormat
            if ($this->get('aeag_sqe.process_rai')->envoiFichierValidationFormat($emSqe, $reponse, $pathBase . '/' . $nomFichier, $session)) {
                // Changement de la phase de la réponse
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R15');
                $reponse->setPhaseFichier($pgProgPhases);
                $emSqe->persist($reponse);
                $emSqe->flush();

                // Envoi d'un mail
                $objetMessage = "RAI " . $reponse->getId() . " soumise et en cours de validation";
                $txtMessage = "Votre RAI (id " . $reponse->getId() . ") concernant la DAI " . $pgCmdDemande->getCodeDemandeCmd() . " a été soumise. Le fichier " . $reponse->getNomFichier() . " est en cours de validation. "
                        . "Vous serez informé lorsque celle-ci sera validée. ";
                $mailer = $this->get('mailer');
                if ($this->get('aeag_sqe.message')->envoiMessage($em, $mailer, $txtMessage, $pgProgWebUser, $objetMessage)) {
                    $session->getFlashBag()->add('notice-success', 'Le fichier ' . $nomFichier . ' a été traité, un email vous a été envoyé');
                } else {
                    $session->getFlashBag()->add('notice-warning', 'Le fichier ' . $nomFichier . ' a été traité, mais l\'email n\'a pas pu être envoyé');
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'Le fichier ' . $nomFichier . ' est en attente de traitement, un email vous sera envoyé lorsque celui-ci sera traité');
                /* $session->getFlashBag()->add('notice-error', 'Le fichier ' . $nomFichier . ' a rencontré une erreur lors de la validation auprès du Sandre. Merci de réessayer plus tard.');
                  $this->_rmdirRecursive($pathBase);
                  $emSqe->remove($reponse);
                  $emSqe->flush(); */
            }
        } else {
            $emSqe->remove($reponse);
            $emSqe->flush();

            $session->getFlashBag()->add('notice-error', 'Erreur lors du téléchargement du fichier ' . $nomFichier);
        }
        return $this->redirect($this->generateUrl('AeagSqeBundle_echangefichiers_demandes', array('lotId' => $pgCmdDemande->getLotan()->getLot()->getId(), 'anneeProg' => $pgCmdDemande->getLotan()->getAnneeProg())));
    }

    public function telechargerReponseAction($reponseId, $typeFichier) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'telechargerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findOneById($reponseId);

        // Récupération du fichier
        $chemin = $this->getParameter('repertoire_echange');
        $pathBase = $this->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdFichiersRps->getDemande(), $reponseId);
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
        $session->set('menu', 'donnees');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'deposerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');

        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findOneById($reponseId);
        $pgCmdDemande = $pgCmdFichiersRps->getDemande();

        // Suppression physique des fichiers
        $chemin = $this->getParameter('repertoire_echange');
        $pathBase = $this->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdFichiersRps->getDemande(), $reponseId);
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

        return $this->redirect($this->generateUrl('AeagSqeBundle_echangefichiers_demandes', array('lotId' => $pgCmdDemande->getLotan()->getLot()->getId(), 'anneeProg' => $pgCmdDemande->getLotan()->getAnneeProg())));
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

    protected function unzip($file, $path = '', $effacer_zip = false) {/* Méthode qui permet de décompresser un fichier zip $file dans un répertoire de destination $path
      et qui retourne un tableau contenant la liste des fichiers extraits
      Si $effacer_zip est égal à true, on efface le fichier zip d'origine $file */

        $tab_liste_fichiers = array(); //Initialisation

        $zip = zip_open($file);

        if ($zip) {
            while ($zip_entry = zip_read($zip)) { //Pour chaque fichier contenu dans le fichier zip
                if (zip_entry_filesize($zip_entry) >= 0) {
                    $complete_path = $path . dirname(zip_entry_name($zip_entry));

                    /* On supprime les éventuels caractères spéciaux et majuscules */
                    $nom_fichier = zip_entry_name($zip_entry);
                    $nom_fichier = strtr($nom_fichier, "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ", "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn");
                    $nom_fichier = strtolower($nom_fichier);
                    $nom_fichier = ereg_replace('[^a-zA-Z0-9.]', '-', $nom_fichier);

                    /* On ajoute le nom du fichier dans le tableau */
                    array_push($tab_liste_fichiers, $nom_fichier);

                    $complete_name = $path . $nom_fichier; //Nom et chemin de destination

                    if (!file_exists($complete_path)) {
                        $tmp = '';
                        foreach (explode('/', $complete_path) AS $k) {
                            $tmp .= $k . '/';

                            if (!file_exists($tmp)) {
                                mkdir($tmp, 0755);
                            }
                        }
                    }

                    /* On extrait le fichier */
                    if (zip_entry_open($zip, $zip_entry, "r")) {
                        $fd = fopen($complete_name, 'w');

                        fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));

                        fclose($fd);
                        zip_entry_close($zip_entry);
                    }
                }
            }

            zip_close($zip);

            /* On efface éventuellement le fichier zip d'origine */
            if ($effacer_zip === true)
                unlink($file);
        }

        return $tab_liste_fichiers;
    }
    
    public function stationsAction($demandeId) {
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'EchangeFichier');
        $session->set('fonction', 'stations');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        
        $pgCmdPrelevs = $repoPgCmdPrelev->findBy(array('demande' => $demandeId));
        
        return $this->render('AeagSqeBundle:EchangeFichiers:stations.html.twig', array('prelevs' => $pgCmdPrelevs));
    }
    
    public function aSecStationAction() {
        $request = $this->get('request');

        $emSqe = $this->get('doctrine')->getManager('sqe');
        
        $prelevId = $request->get('prelevId');
        
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        
        $pgCmdPrelev = $repoPgCmdPrelev->findOneById($prelevId);
        $pgCmdPrelevPc = $repoPgCmdPrelevPc->findOneByPrelev($prelevId);
        
        return $this->render('AeagSqeBundle:EchangeFichiers:aSecStation.html.twig', array('prelev' => $pgCmdPrelev, 'prelevPc' => $pgCmdPrelevPc));
    }
    
    public function modifierStationAction() {
        $request = $this->get('request');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        
        $formStr = $request->get('form');

        $formTabs = explode('&', $formStr);
        $form = array();
        foreach ($formTabs as $formTab) {
            $formLine = explode('=', $formTab);
            $form[$formLine[0]] = urldecode($formLine[1]);
        }
        
        $pgCmdPrelev = $repoPgCmdPrelev->findOneById($form['prelev-id']);
        $pgCmdPrelevPc = $repoPgCmdPrelevPc->findOneByPrelev($form['prelev-id']);
        
        $datePrel = \DateTime::createFromFormat('d/m/Y H:i', $form['date-prelev']);
        $pgCmdPrelev->setDatePrelev($datePrel);
        $pgCmdPrelev->setRealise('N');
        
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');
        $pgCmdPrelev->setPhaseDmd($pgProgPhases);
        
        $pgCmdPrelevPc->setCommentaire($form['commentaire']);
        
        $emSqe->persist($pgCmdPrelev);
        $emSqe->persist($pgCmdPrelevPc);
        
        $emSqe->flush();
        $result = true;
        
        return new Response(
                json_encode($result)
        );
        
    }
    
    public function abandonnerStationAction() {
        $request = $this->get('request');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        
        $formStr = $request->get('form');

        $formTabs = explode('&', $formStr);
        $form = array();
        foreach ($formTabs as $formTab) {
            $formLine = explode('=', $formTab);
            $form[$formLine[0]] = urldecode($formLine[1]);
        }
        
        $pgCmdPrelev = $repoPgCmdPrelev->findOneById($form['prelev-id']);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M60');
        
        $pgCmdPrelev->setRealise('N');
        $pgCmdPrelev->setPhaseDmd($pgProgPhases);
        
        $emSqe->persist($pgCmdPrelev);
        
        $emSqe->flush();
        $result = true;
        
        return new Response(
                json_encode($result)
        );
    }
    
    

}
