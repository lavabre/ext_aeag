<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\AeagBundle\Controller\AeagController;
use Symfony\Component\HttpFoundation\Response;

class DepotHydrobioController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'index');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        // Récupération des programmations
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');

        if ($user->hasRole('ROLE_ADMINSQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAdmin();
        } else if ($user->hasRole('ROLE_PRESTASQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByPresta($user);
        } else if ($user->hasRole('ROLE_PROGSQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByProg($user);
        } else if ($user->hasRole('ROLE_SQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAdmin();
        }

        $tabProglotAns = array();
        $i = 0;
        foreach ($pgProgLotAns as $pgProgLotAn) {
            $pgProgLot = $pgProgLotAn->getLot();
            $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
            if (substr($pgProgTypeMilieu->getCodeMilieu(), 1, 2) === 'HB' or $pgProgTypeMilieu->getCodeMilieu() === 'RHM') {
                $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
                if (count($pgProgLotPeriodeAns) > 0) {
                    $trouve = false;
                    foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
                        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
                        if (count($pgProgLotPeriodeProgs) > 0) {
                            $trouve = true;
                            break;
                        }
                    }
                    if ($trouve) {
                        $tabProglotAns[$i] = $pgProgLotAn;
                        $i++;
                    }
                }
            }
        }

        return $this->render('AeagSqeBundle:DepotHydrobio:index.html.twig', array('user' => $user,
                    'lotans' => $pgProgLotAns));
    }

    public function demandesAction($lotanId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
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
            $reponses[$pgCmdDemande->getId()] = $repoPgCmdFichiersRps->getReponsesExcelByDemande($pgCmdDemande->getId());
        }
        return $this->render('AeagSqeBundle:DepotHydrobio:demandes.html.twig', array('user' => $pgProgWebUser,
                    'demandes' => $pgCmdDemandes,
                    'lotan' => $pgProgLotAn,
                    'reponses' => $reponses));
    }

    public function prelevementsAction($demandeId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'prelevements');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        if ($pgCmdDemande) {
            $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
        } else {
            $pgCmdPrelevs = null;
        }
        return $this->render('AeagSqeBundle:DepotHydrobio:prelevements.html.twig', array('user' => $pgProgWebUser,
                    'demande' => $pgCmdDemande,
                    'prelevs' => $pgCmdPrelevs,));
    }
    
    public function prelevementDetailAction($prelevId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'prelevementDetail');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdPrelevHbInvert = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevHbInvert');
        $repoPgCmdInvertRecouv = $emSqe->getRepository('AeagSqeBundle:PgCmdInvertRecouv');
        $repoPgCmdInvertPrelem = $emSqe->getRepository('AeagSqeBundle:PgCmdInvertPrelem');
        $repoPgCmdInvertListe = $emSqe->getRepository('AeagSqeBundle:PgCmdInvertListe');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgSandreHbNomemclatures = $emSqe->getRepository('AeagSqeBundle:PgSandreHbNomemclatures');
        $repoPgSandreAppellationTaxon = $emSqe->getRepository('AeagSqeBundle:PgSandreAppellationTaxon');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdDemande = $pgCmdPrelev->getdemande();
        
         $pgCmdPrelevHbInvert = $repoPgCmdPrelevHbInvert->getPgCmdPrelevHbInvertByPrelev($pgCmdPrelev);
           $pgCmdInvertRecouvs = $repoPgCmdInvertRecouv->getPgCmdInvertRecouvByPrelev($pgCmdPrelevHbInvert);
         $tabRecouvs = array();
         $i = 0;
         foreach($pgCmdInvertRecouvs as $pgCmdInvertRecouv){
             $tabRecouvs[$i]['recouv'] =  $pgCmdInvertRecouv;
             $pgSandreHbNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElement(274, $pgCmdInvertRecouv->getSubstrat());
             $tabRecouvs[$i]['nomenclature'] = $pgSandreHbNomemclature;
             $i++;
         }
         $pgCmdInvertPrelems = $repoPgCmdInvertPrelem->getPgCmdInvertPrelemByPrelev($pgCmdPrelevHbInvert);
  //        \Symfony\Component\VarDumper\VarDumper::dump($pgCmdInvertPrelems);
 //        return new response ('nb recouv : ' . count($pgCmdInvertRecouvs) . ' nb prelem :  ' . count($pgCmdInvertPrelems));
        $tabPrelems = array();
         $i = 0;
         foreach($pgCmdInvertPrelems as $pgCmdInvertPrelem){
             $tabPrelems[$i]['prelem'] = $pgCmdInvertPrelem;
               $pgSandreHbNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElement(274, $pgCmdInvertPrelem->getSubstrat());
             $tabPrelems[$i]['nomenclature'] = $pgSandreHbNomemclature;
             $i++;
         }
         $pgCmdInvertListes = $repoPgCmdInvertListe->getPgCmdInvertListeByPrelev($pgCmdPrelevHbInvert);
         $tabListes = array();
         $i = 0;
         foreach($pgCmdInvertListes as $pgCmdInvertListe){
             $tabListes[$i]['liste'] = $pgCmdInvertListe;
             $pgSandreAppellationTaxon = $repoPgSandreAppellationTaxon->getPgSandreAppellationTaxonByCodeAppelTaxonCodeSupport($pgCmdInvertListe->getTaxon(), '13');
             $tabListes[$i]['taxon']  =  $pgSandreAppellationTaxon;    
             $i++;
         }
      
        return $this->render('AeagSqeBundle:DepotHydrobio:prelevementDetail.html.twig', array('user' => $pgProgWebUser,
                    'demande' => $pgCmdDemande,
                    'prelev' => $pgCmdPrelev,
                    'pgCmdPrelevHbInvert' => $pgCmdPrelevHbInvert,
                    'pgCmdInvertRecouvs' => $tabRecouvs,
                    'pgCmdInvertPrelems' => $tabPrelems,
                    'pgCmdInvertListes' => $tabListes ));
    }

    public function telechargerAction($demandeId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
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
            $chemin = $this->getParameter('repertoire_depotHydrobio');
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
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'telecharger');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        if ($pgCmdDemande) {
            // Récupération du fichier
            $chemin = $this->getParameter('repertoire_depotHydrobio');
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
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'reponses');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgCmdFichiersRps = $repoPgCmdFichiersRps->getReponseByDemandeType($demandeId, 'EXL');
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());

        return $this->render('AeagSqeBundle:DepotHydrobio:reponses.html.twig', array(
                    'reponses' => $pgCmdFichiersRps,
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
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'reponses');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');

        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);

        return $this->render('AeagSqeBundle:DepotHydrobio:selectionnerReponse.html.twig', array('demande' => $pgCmdDemande));
    }

    public function deposerReponseAction($demandeId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'deposerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $em = $this->get('doctrine')->getManager();

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('DH10');

        // Récupération des valeurs du fichier
        $nomFichier = $_FILES["fichier"]["name"];
        if (substr($nomFichier, -3) != "zip") {
            $session->getFlashBag()->add('notice-error', 'Le fichier déposé n\'est pas un fichier zip');
            return $this->redirect($this->generateUrl('AeagSqeBundle_depotHydrobio_demandes', array('lotanId' => $pgCmdDemande->getLotan()->getId())));
        }

        // Enregistrement des valeurs en base
        $reponse = new \Aeag\SqeBundle\Entity\PgCmdFichiersRps();
        $reponse->setDemande($pgCmdDemande);
        $reponse->setNomFichier($nomFichier);
        $reponse->setDateDepot(new \DateTime());
        $reponse->setTypeFichier('EXL');
        $reponse->setPhaseFichier($pgProgPhases);
        $reponse->setUser($pgProgWebUser);
        $reponse->setSuppr('N');

        $emSqe->persist($reponse);


        // Enregistrement du fichier sur le serveur
        $chemin = $this->getParameter('repertoire_depotHydrobio');
        $pathBase = $this->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdDemande, $reponse->getId());

        if (!mkdir($pathBase, 0755, true)) {
            $session->getFlashBag()->add('notice-error', 'Le répertoire n\'a pas pu être créé');
        }

        $emSqe->flush();

        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $pathBase . '/' . $nomFichier)) {

            // Envoi du fichier sur le serveur du sandre pour validationFormat
            $excelObj = $this->get('xls.load_xls5');
            $tabFichiers = $this->get('aeag_sqe.depotHydrobio')->extraireFichier($demandeId, $emSqe, $reponse, $pathBase, $nomFichier, $session, $excelObj);

//             \Symfony\Component\VarDumper\VarDumper::dump($tabFichiers);
//              return new Response ('');   


            $erreur = false;
            for ($i = 0; $i < count($tabFichiers); $i++) {
                for ($j = 0; $j < count($tabFichiers[$i]['feuillet']); $j++) {
                    $erreur = $tabFichiers[$i]['feuillet'][$j]['erreur'];
                }
            }

            if (!$erreur) {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('DH30');
                $reponse->setPhaseFichier($pgProgPhases);
                $emSqe->persist($reponse);
                $emSqe->flush();
                // Envoi d'un mail
                $objetMessage = "Dépôt Hydrobio  " . $reponse->getId() . " soumis correctement";
                $txtMessage = "Votre dépôt hydrobio (id " . $reponse->getId() . ") concernant la DAI " . $pgCmdDemande->getCodeDemandeCmd() . " a été soumis.<br/><br/>";
                $txtMessage = $txtMessage . "Le fichier " . $reponse->getNomFichier() . " contient " . count($tabFichiers);
                if (count($tabFichiers) == 1) {
                    $txtMessage = $txtMessage . " fichier :  <br/><br/>";
                } else {
                    $txtMessage = $txtMessage . " fichiers :  <br/><br/>";
                }
                for ($i = 0; $i < count($tabFichiers); $i++) {
                    $txtMessage = $txtMessage . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- ' . $tabFichiers[$i]['fichier'] . '<br/>';
                    for ($j = 0; $j < count($tabFichiers[$i]['feuillet']); $j++) {
                        $txtMessage = $txtMessage . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - ' . $tabFichiers[$i]['feuillet'][$j]['feuillet'] . '  Correct <br/>';
                    }
                }
                $mailer = $this->get('mailer');
                if ($this->get('aeag_sqe.message')->envoiMessage($em, $mailer, $txtMessage, $pgProgWebUser, $objetMessage)) {
                    $session->getFlashBag()->add('notice-success', 'Le fichier ' . $nomFichier . ' a été traité, un email vous a été envoyé');
                } else {
                    $session->getFlashBag()->add('notice-warning', 'Le fichier ' . $nomFichier . ' a été traité, mais l\'email n\'a pas pu être envoyé');
                }
            } else {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('DH40');
                $reponse->setPhaseFichier($pgProgPhases);
                $emSqe->persist($reponse);
                $emSqe->flush();
                $objetMessage = "Dépôt Hydrobio  " . $reponse->getId() . " soumis avec des erreurs";
                $txtMessage = "Votre dépôt hydrobio (id " . $reponse->getId() . ") concernant la DAI " . $pgCmdDemande->getCodeDemandeCmd() . " a été soumis avec des erreurs. <br/><br/>";
                $txtMessage = $txtMessage . "Le fichier " . $reponse->getNomFichier() . " contient " . count($tabFichiers);
                if (count($tabFichiers) == 1) {
                    $txtMessage = $txtMessage . " fichier :  <br/><br/>";
                } else {
                    $txtMessage = $txtMessage . " fichiers :  <br/><br/>";
                }
                for ($i = 0; $i < count($tabFichiers); $i++) {
                    $txtMessage = $txtMessage . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - ' . $tabFichiers[$i]['fichier'] . '<br/>';
                    for ($j = 0; $j < count($tabFichiers[$i]['feuillet']); $j++) {
                        if (!$tabFichiers[$i]['feuillet'][$j]['erreur']) {
                            $txtMessage = $txtMessage . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - ' . $tabFichiers[$i]['feuillet'][$j]['feuillet'] . '  Correct <br/>';
                        } else {
                            $txtMessage = $txtMessage . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - ' . $tabFichiers[$i]['feuillet'][$j]['feuillet'] . '  Incorrect <br/>';
                        }
                    }
                }
                $mailer = $this->get('mailer');
                if ($this->get('aeag_sqe.message')->envoiMessage($em, $mailer, $txtMessage, $pgProgWebUser, $objetMessage)) {
                    $session->getFlashBag()->add('notice-warning', 'Le fichier ' . $nomFichier . ' n\'a  pas été traité, un email vous a été envoyé');
                } else {
                    $session->getFlashBag()->add('notice-warning', 'Le fichier ' . $nomFichier . ' n\'a  pas été traité, mais l\'email n\'a pas pu être envoyé');
                }
//                $session->getFlashBag()->add('notice-error', 'Le fichier ' . $nomFichier . ' a rencontré une erreur lors du traitement. Merci de réessayer plus tard.');
//                $this->_rmdirRecursive($pathBase);
//                $emSqe->remove($reponse);
//                $emSqe->flush();
            }
        } else {
            $emSqe->remove($reponse);
            $emSqe->flush();

            $session->getFlashBag()->add('notice-error', 'Erreur lors du téléchargement du fichier ' . $nomFichier);
        }

        $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
        $nbPrelevs = count($pgCmdPrelevs);
        $nbPrelevM40 = 0;
        foreach ($pgCmdPrelevs as $pgCmdPrelev) {
            if ($pgCmdPrelev->getPhaseDmd()->getCodePhase() == 'M40') {
                $nbPrelevM40++;
            }
        }
        if ($nbPrelevs == $nbPrelevM40) {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D40');
            $pgCmdDemande->setPhaseDemande($pgProgPhases);
        } else {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D30');
            $pgCmdDemande->setPhaseDemande($pgProgPhases);
       }
        $emSqe->persist($pgCmdDemande);
        $emSqe->flush();


        return $this->redirect($this->generateUrl('AeagSqeBundle_depotHydrobio_demandes', array('lotanId' => $pgCmdDemande->getLotan()->getId())));
    }

    public function telechargerReponseAction($reponseId, $typeFichier) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'telechargerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findOneById($reponseId);

        // Récupération du fichier
        $chemin = $this->getParameter('repertoire_depotHydrobio');
        $pathBase = $this->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdFichiersRps->getDemande(), $reponseId);
        switch ($typeFichier) {
            case "EXL" :
                $contentType = "application/zip";
                $fileName = $pgCmdFichiersRps->getNomFichier();
                break;
            case "CR" :
                $contentType = "application/octet-stream";
                $fileName = $pgCmdFichiersRps->getNomFichierCompteRendu();
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
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'deposerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findOneById($reponseId);
        $pgCmdDemande = $pgCmdFichiersRps->getDemande();
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');

        // Suppression physique des fichiers
        $chemin = $this->getParameter('repertoire_depotHydrobio');
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

        $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M10');
        foreach ($pgCmdPrelevs as $pgCmdPrelev) {
            $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            $emSqe->persist($pgCmdPrelev);
        }
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D30');
        $pgCmdDemande->setPhaseDemande($pgProgPhases);
        $emSqe->persist($pgCmdDemande);

        $emSqe->flush();

        return $this->redirect($this->generateUrl('AeagSqeBundle_depotHydrobio_demandes', array('lotanId' => $pgCmdFichiersRps->getDemande()->getLotan()->getId())));
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

}
