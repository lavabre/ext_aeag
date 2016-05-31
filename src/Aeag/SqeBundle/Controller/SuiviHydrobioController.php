<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\SqeBundle\Entity\PgCmdSuiviPrel;
use Aeag\SqeBundle\Entity\PgCmdFichiersRps;
use \Aeag\SqeBundle\Entity\PgCmdMesureEnv;
use \Aeag\SqeBundle\Entity\PgCmdAnalyse;
use \Aeag\SqeBundle\Entity\PgCmdPrelevPc;
use Aeag\SqeBundle\Form\PgCmdSuiviPrelMajType;
use Aeag\SqeBundle\Form\PgCmdSuiviPrelVoirType;
use Aeag\SqeBundle\Form\LotPeriodeStationDemandeSuiviSaisirType;
use Aeag\AeagBundle\Controller\AeagController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class SuiviHydrobioController extends Controller {

    public function indexAction() {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }

        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
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
        }

        $tabProglotAns = array();
        $i = 0;
        foreach ($pgProgLotAns as $pgProgLotAn) {
            $pgProgLot = $pgProgLotAn->getLot();
            $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
            if (substr($pgProgTypeMilieu->getCodeMilieu(), 1, 2) === 'HB') {
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


        return $this->render('AeagSqeBundle:SuiviHydrobio:index.html.twig', array('user' => $user,
                    'lotans' => $tabProglotAns));
    }

    public function lotPeriodesAction($lotanId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodes');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotanId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $tabPeriodeAns = array();
        $i = 0;
        foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
            if ($pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'DEL' and $pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'INV') {
                $tabPeriodeAns[$i]['pgProgLotPeriodeAn'] = $pgProgLotPeriodeAn;
                $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
                $tabStations = array();
                $nbStations = 0;
                $j = 0;
                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                    if ($pgProgLot->getDelaiPrel()) {
                        $dateFin = clone($pgProgLotPeriodeProg->getPeriodan()->getPeriode()->getDateDeb());
                        $delai = $pgProgLot->getDelaiPrel();
                        $dateFin->add(new \DateInterval('P' . $delai . 'D'));
                    } else {
                        $dateFin = $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getDateFin();
                    }
                    $tabPeriodeAns[$i]['dateFin'] = $dateFin;
                    $trouve = false;
                    for ($k = 0; $k < count($tabStations); $k++) {
                        if ($tabStations[$k]->getOuvFoncid() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncid()) {
                            $trouve = true;
                            break;
                        }
                    }
                    if (!$trouve) {
                        $tabStations[$j] = $pgProgLotPeriodeProg->getStationAn()->getStation();
                        $nbStations++;
                        $j++;
                    }
                }
                $tabPeriodeAns[$i]['nbStations'] = $nbStations;
                $tabPeriodeAns[$i]['stations'] = $tabStations;
                $i++;
            }
        }

        if (count($tabPeriodeAns) == 1) {
            return $this->redirect($this->generateUrl('AeagSqeBundle_suiviHydrobio_lot_periode_stations', array('periodeAnId' => $tabPeriodeAns[0]['pgProgLotPeriodeAn']->getId())));
        }

        return $this->render('AeagSqeBundle:SuiviHydrobio:lotPeriodes.html.twig', array(
                    'user' => $pgProgWebUser,
                    'lotan' => $pgProgLotAn,
                    'periodeAns' => $tabPeriodeAns));
    }

    public function lotPeriodeStationsAction($periodeAnId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStations');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgPrestaTypfic = $emSqe->getRepository('AeagSqeBundle:PgProgPrestaTypfic');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
        $tabStations = array();
        $pgCmdDemande = null;
        $dateFin = null;
        $i = 0;
        $j = 0;
        $k = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            if ($pgProgLot->getDelaiPrel()) {
                $dateFin = clone($pgProgLotPeriodeProg->getPeriodan()->getPeriode()->getDateDeb());
                $delai = $pgProgLot->getDelaiPrel();
                $dateFin->add(new \DateInterval('P' . $delai . 'D'));
            } else {
                $dateFin = $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getDateFin();
            }
            $trouve = false;
            if (count($tabStations) > 0) {
                for ($k = 0; $k < count($tabStations); $k++) {
                    if ($tabStations[$k]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                        $trouve = true;
                        break;
                    }
                }
            }
            if (!$trouve) {
                $pgProgLotStationAn = $pgProgLotPeriodeProg->getStationAn();
                $tabStations[$i]['station'] = $pgProgLotStationAn->getStation();
                $tabStations[$i]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $pgProgLotPeriodeProg->getStationAn()->getStation()->getCode()) . '.pdf';
                $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
                if ($pgRefReseauMesure) {
                    $tabStations[$i]['reseau'] = $pgRefReseauMesure;
                } else {
                    $tabStations[$i]['reseau'] = null;
                }
                $tabStations[$i]['cmdPrelevs'] = null;
                $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeByLotanPrestatairePeriode($pgProgLotAn, $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft(), $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                if ($pgCmdDemande) {
                    $tabStations[$i]['cmdDemande'] = $pgCmdDemande;
                    $tabCmdPrelevs = array();
                    $nbCmdPrelevs = 0;
                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($pgProgLotPeriodeProg->getGrparAn()->getPrestaDft(), $pgCmdDemande, $pgProgLotPeriodeProg->getStationAn()->getStation(), $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                        $tabCmdPrelevs[$nbCmdPrelevs]['cmdPrelev'] = $pgCmdPrelev;
                        $tabCmdPrelevs[$nbCmdPrelevs]['maj'] = 'N';
                        $pgCmdSuiviPrels = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevOrderDate($pgCmdPrelev);
                        $tabSuiviPrels = array();
                        $nbSuiviPrels = 0;
                        if (count($pgCmdSuiviPrels) == 0) {
                            $tabSuiviPrels[$nbSuiviPrels]['suiviPrel'] = array();
                            $tabSuiviPrels[$nbSuiviPrels]['maj'] = 'O';
                            $tabCmdPrelevs[$nbCmdPrelevs]['maj'] = 'O';
                        } else {
                            foreach ($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
                                $tabSuiviPrels[$nbSuiviPrels]['suiviPrel'] = $pgCmdSuiviPrel;
                                $tabSuiviPrels[$nbSuiviPrels]['maj'] = 'N';
                                if ($user->hasRole('ROLE_ADMINSQE') or ( $pgCmdSuiviPrel->getUser()->getPrestataire() == $pgCmdDemande->getPrestataire())) {
                                    if ($pgCmdSuiviPrel->getStatutPrel() != 'F' or ( $pgCmdSuiviPrel->getStatutPrel() == 'F' and $pgCmdSuiviPrel->getValidation() != 'A')) {
                                        $tabSuiviPrels[$nbSuiviPrels]['maj'] = 'O';
                                        $tabCmdPrelevs[$nbCmdPrelevs]['maj'] = 'O';
                                    }
                                } else {
                                    if ($user->hasRole('ROLE_ADMINSQE')) {
                                        if ($pgCmdSuiviPrel->getStatutPrel() != 'F' or ( $pgCmdSuiviPrel->getStatutPrel() == 'F' and $pgCmdSuiviPrel->getValidation() != 'A')) {
                                            $tabSuiviPrels[$nbSuiviPrels]['maj'] = 'O';
                                            $tabCmdPrelevs[$nbCmdPrelevs]['maj'] = 'O';
                                        }
                                    } else {
                                        $tabSuiviPrels[$nbSuiviPrels]['maj'] = 'N';
                                    }
                                }
                                $nbSuiviPrels++;
                            }
                        }
                        if (count($tabSuiviPrels) > 0) {
                            $tabCmdPrelevs[$nbCmdPrelevs]['suiviPrels'] = $tabSuiviPrels;
                        } else {
                            $tabCmdPrelevs[$nbCmdPrelevs]['suiviPrels'] = null;
                        }
                        $tabAutrePrelevs = $repoPgCmdPrelev->getAutrePrelevs($pgCmdPrelev);
                        if (count($tabAutrePrelevs) > 0) {
                            $tabCmdPrelevs[$nbCmdPrelevs]['autrePrelevs'] = $tabAutrePrelevs;
                        } else {
                            $tabCmdPrelevs[$nbCmdPrelevs]['autrePrelevs'] = null;
                        }
//                         if ($pgCmdPrelev->getStation()->getOuvFoncId() == 557655){
//                             for($j = 0 ; $j < count($tabCmdPrelevs[$nbCmdPrelevs]['autrePrelevs']); $j++){
//                                 echo('j : ' . $j . ' date : ' . $tabCmdPrelevs[$nbCmdPrelevs]['autrePrelevs'][$j]['datePrel'] . ' support : ' . $tabCmdPrelevs[$nbCmdPrelevs]['autrePrelevs'][$j]['support'] . '</br>');
//                             }
//                             echo('nb: ' . count($tabCmdPrelevs[$nbCmdPrelevs]['autrePrelevs']));
//                           \Symfony\Component\VarDumper\VarDumper::dump($tabCmdPrelevs[$nbCmdPrelevs]['autrePrelevs']);
//                            return new Response ('');   
//                        }
                        $nbCmdPrelevs++;
                    }
                    $tabStations[$i]['cmdPrelevs'] = $tabCmdPrelevs;
                } else {
                    $tabStations[$i]['cmdDemande'] = null;
                }
                $i++;
            }
        }

        $dateDepot = new \DateTime();
        $chemin = '/base/extranet/Transfert/Sqe/csv';
        $fichier = $user->getId() . '_' . $dateDepot->format('Y-m-d-H') . '_rapport.csv';
        if (file_exists($chemin . '/' . $fichier)) {
            $rapport = $fichier;
        } else {
            $rapport = null;
        }

//        \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response ('');   

        return $this->render('AeagSqeBundle:SuiviHydrobio:lotPeriodeStations.html.twig', array(
                    'user' => $pgProgWebUser,
                    'lotan' => $pgProgLotAn,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'demande' => $pgCmdDemande,
                    'dateFin' => $dateFin,
                    'stations' => $tabStations,
                    'rapport' => $rapport));
    }

    public function lotPeriodeStationsIntegrerAction($periodeAnId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationsIntegrer');
        $emSqe = $this->get('doctrine')->getManager('sqe');


        return $this->render('AeagSqeBundle:SuiviHydrobio:lotPeriodeStationsIntegrer.html.twig', array(
                    'periodeAnId' => $periodeAnId
        ));



//          \Symfony\Component\VarDumper\VarDumper::dump($tabDemande);
//        return new Response ('');
    }

    public function lotPeriodeStationsIntegrerFichierAction($periodeAnId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationsIntegrerFichier');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgSandreSupports = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgPeriode = $pgProgLotPeriodeAn->getPeriode();
        if ($pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'DEL' and $pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'INV') {
            $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
            $tabStations = array();
            $nbStations = 0;
            $j = 0;
            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                $trouve = false;
                for ($k = 0; $k < count($tabStations); $k++) {
                    if ($tabStations[$k]['station']->getOuvFoncid() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncid()) {
                        $trouve = true;
                        break;
                    }
                }
                if (!$trouve) {
                    $tabStations[$j]['station'] = $pgProgLotPeriodeProg->getStationAn()->getStation();
                    $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeByLotanPrestatairePeriode($pgProgLotAn, $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft(), $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                    if ($pgCmdDemande) {
                        $tabStations[$j]['demande'] = $pgCmdDemande;
                        $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($pgProgLotPeriodeProg->getGrparAn()->getPrestaDft(), $pgCmdDemande, $pgProgLotPeriodeProg->getStationAn()->getStation(), $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                        $tabCmdPrelevs = array();
                        $nbCmdPrelevs = 0;
                        foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                            $tabCmdPrelevs[$nbCmdPrelevs]['cmdPrelev'] = $pgCmdPrelev;
                            $pgCmdSuiviPrels = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevOrderDate($pgCmdPrelev);
                            $tabCmdPrelevs[$nbCmdPrelevs]['cmdSuiviPrelevs'] = $pgCmdSuiviPrels;
                            $nbCmdPrelevs++;
                        }
                        $tabStations[$j]['prelevs'] = $tabCmdPrelevs;
                    }
                    $nbStations++;
                    $j++;
                }
            }
        }

//        \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response ('');   
// Récupération des valeurs du fichier

        $name = $_FILES['file']['name'];
        $tmpName = $_FILES['file']['tmp_name'];
        $error = $_FILES['file']['error'];
        $size = $_FILES['file']['size'] / 1024;
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $response = null;

        switch ($error) {
            case UPLOAD_ERR_OK:
                $valid = true;
//validate file size
                if ($size / 1024 / 1024 > 2) {
                    $valid = false;
                    $response = 'La taille du fichier est plus grande que la taille autorisée.';
                }
//upload file
                if ($valid) {
// Enregistrement du fichier sur le serveur
                    $pathBase = '/base/extranet/Transfert/Sqe/csv';
                    if (!is_dir($pathBase)) {
                        if (!mkdir($pathBase, 0777, true)) {
                            $session->getFlashBag()->add('notice-error', 'Le répertoire : ' . $pathBase . ' n\'a pas pu être créé');
                            ;
                        }
                    }
                    move_uploaded_file($_FILES['file']['tmp_name'], $pathBase . '/' . $name);

                    $dateDepot = new \DateTime();
                    $response = $name . ' déposé le ' . $dateDepot->format('d/m/Y');
                    break;
                }
            case UPLOAD_ERR_INI_SIZE:
                $response = 'La taille (' . $size . ' octets' . ') du fichier téléchargé excède la taille de upload_max_filesize dans php.ini.';
                $valid = false;
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $response = 'La taille (' . $size . ') du fichier téléchargé excède la taille de MAX_FILE_SIZE qui a été spécifié dans le formulaire HTML.';
                $valid = false;
                break;
            case UPLOAD_ERR_PARTIAL:
                $response = 'Le fichier n\'a été que partiellement téléchargé.';
                $valid = false;
                break;
            case UPLOAD_ERR_NO_FILE:
                $response = 'Aucun fichier sélectionné.';
                $valid = false;
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $response = 'Manquantes dans un dossier temporaire. Introduit en PHP 4.3.10 et PHP 5.0.3.';
                $valid = false;
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $response = 'Impossible d\'écrire le fichier sur le disque. Introduit en PHP 5.1.0.';
                $valid = false;
                break;
            case UPLOAD_ERR_EXTENSION:
                $response = 'Le téléchargement du fichier arrêté par extension. Introduit en PHP 5.2.0.';
                $valid = false;
                break;
            default:
                $response = 'erreur inconnue';
                $valid = false;
                break;
        }


        if ($valid) {
            $fichier = fopen($pathBase . '/' . $name, "r");
            $rapport = fopen($pathBase . '/' . $user->getId() . '_' . $dateDepot->format('Y-m-d-H') . '_rapport.csv', "w+");
            $contenu = 'rapport d\'intégration du fichier : ' . $name . ' déposé le ' . $dateDepot->format('d/m/Y') . CHR(13) . CHR(10) . CHR(13) . CHR(10);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            fputs($rapport, $contenu);
            $erreur = 0;
            $ligne = 0;

            $tab = fgetcsv($fichier, 1024, ';');
            while (!feof($fichier)) {
                $tab = fgetcsv($fichier, 1024, ';');
                if (count($tab) > 1) {
                    $err = false;
                    $ligne++;
                    $codeStation = $tab[0];
                    $prelevs = array();
                    $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByCode($codeStation);
                    if (!$pgRefStationMesure) {
                        $err = true;
                        $contenu = 'ligne  ' . $ligne . '  :  code station inconnu (' . $codeStation . ')' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } else {
                        $trouve = false;
                        for ($i = 0; $i < count($tabStations); $i++) {
                            if ($tabStations[$i]['station'] == $pgRefStationMesure) {
                                $trouve = true;
                                $prelevs = $tabStations[$i]['prelevs'];
                                break;
                            }
                        }
                        if (!$trouve) {
                            $err = true;
                            $contenu = 'ligne  ' . $ligne . '  :  code station  (' . $codeStation . ') non référencé dans la liste' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                    }

                    $codeSupport = $tab[1];
                    $pgSandreSupports = $repoPgSandreSupports->getPgSandreSupportsByCodeSupport($codeSupport);
                    if (!$pgSandreSupports) {
                        $err = true;
                        $contenu = 'ligne  ' . $ligne . '  :  code support inconnu (' . $codeSupport . ')' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }

                    $statutPrel = $tab[2];
                    if ($statutPrel != 'P' and $statutPrel != 'F' and $statutPrel != 'N') {
                        $err = true;
                        $contenu = 'ligne  ' . $ligne . '  :  code statut inconnu (\'' . $statutPrel . '\')' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }

                    $dateActuel = new \DateTime();
                    $dateActuel->add(new \DateInterval('P15D'));
                    $date = $tab[3];
                    list( $jour, $mois, $annee, $heure, $min ) = sscanf($date, "%d/%d/%d %d:%d");
                    $datePrel = new \DateTime($annee . '-' . $mois . '-' . $jour . ' ' . $heure . ':' . $min . ':00');

                    if (!$datePrel) {
                        $err = true;
                        $contenu = 'ligne  ' . $ligne . '  :  date heure incorrecte (' . $date . ')' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }
                    if ($pgProgLot->getDelaiPrel()) {
                        $dateFin = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
                        $delai = $pgProgLot->getDelaiPrel();
                        $dateFin->add(new \DateInterval('P' . $delai . 'D'));
                    } else {
                        $dateFin = $pgProgLotPeriodeAn->getPeriode()->getDateFin();
                    }
                    if ($statutPrel == 'P') {
                        if ($datePrel < $dateActuel or $datePrel > $dateFin) {
                            $contenu = 'ligne  ' . $ligne . '  :  Avertissement date  (' . $datePrel->format('d/m/Y H:i') . ') non comprise entre ' . $dateActuel->format('d/m/Y H:i') . ' et ' . $dateFin->format('d/m/Y H:i') . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                    }
                    $dateDebut = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
                    $dateActuel = new \DateTime();
                    if ($statutPrel != 'P') {
                        if ($datePrel < $dateDebut or $datePrel > $dateActuel) {
                            $err = true;
                            $contenu = 'ligne  ' . $ligne . '  :   date  (' . $datePrel->format('d/m/Y H:i') . ') non comprise entre ' . $dateDebut->format('d/m/Y H:i') . ' et ' . $dateActuel->format('d/m/Y H:i') . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                    }

                    $commentaire = $tab[4];
                    if ($statutPrel == 'P') {
                        if (!$commentaire or $commentaire == '') {
                            $contenu = 'ligne  ' . $ligne . '  :  Avertissement commentaire renseigner l’équipe et le contact (portable)  ' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                    }
                    if ($statutPrel == 'N') {
                        if (!$commentaire or $commentaire == '') {
                            $err = true;
                            $contenu = 'ligne  ' . $ligne . '  :  commentaire obligatoire indiquer pourquoi   ' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                    }

                    $trouve = false;
                    $prelev = null;
                    for ($j = 0; $j < count($prelevs); $j++) {
                        $prelev = $prelevs[$j]['cmdPrelev'];
                        if ($prelev->getCodeSupport()->getCodeSupport() != '10' && $prelev->getCodeSupport()->getCodeSupport() != '11') {
                            $autrePgCmdPrelevs = $repoPgCmdPrelev->getAutrePrelevs($prelev);
                            for ($i = 0; $i < count($autrePgCmdPrelevs); $i++) {
                                $autreSuport = $autrePgCmdPrelevs[$i]['codeSupport'];
                                if ($autreSuport != '10' && $autreSuport != '11') {
                                    $autreDateDebut = new \DateTime($autrePgCmdPrelevs[$i]['datePrel']);
                                    $autreDateDebut->sub(new \DateInterval('P7D'));
                                    $autreDateFin = new \DateTime($autrePgCmdPrelevs[$i]['datePrel']);
                                    $autreDateFin->add(new \DateInterval('P7D'));
                                    if ($datePrel >= $autreDateDebut or $datePrel <= $autreDateFin) {
                                        $err = true;
                                        $contenu = 'ligne  ' . $ligne . '  : Date  (' . $datePrel->format('d/m/Y H:i') . ') doit être inférieure à ' . $autreDateDebut->format('d/m/Y H:i') . ' ou supérieure à  ' . $autreDateFin->format('d/m/Y H:i');
                                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                        fputs($rapport, $contenu);
                                    }
                                }
                            }
                        }
                        if ($prelev->getCodeSupport()->getCodeSupport() == $codeSupport) {
                            $trouve = true;
                            $suiviPrels = $prelevs[$j]['cmdSuiviPrelevs'];
                            $suiviPrelActuel = null;
                            for ($k = 0; $k < count($suiviPrels); $k++) {
                                if ($k = 0) {
                                    $suiviPrelActuel = $suiviPrels[$k];
                                }
                                $suiviPrel = $suiviPrels[$k];
                                if ($suiviPrel->getDatePrel() == $datePrel and
                                        $suiviPrel->getStatutPrel() == $statutPrel and
                                        $suiviPrel->getCommentaire() == $commentaire) {
                                    $err = true;
                                    $contenu = 'ligne  ' . $ligne . '  :  suivi déja intégré ' . CHR(13) . CHR(10);
                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($rapport, $contenu);
                                }
                            }
                        }
                    }
                    if (!$trouve) {
                        $err = true;
                        $contenu = 'ligne  ' . $ligne . '  :  code support ne correspond pas à celui du prélèvement associé à la station ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } else {
                        if ($suiviPrelActuel) {
                            if ($suiviPrelActuel->getStatutPrel() == 'E' and $statutPrel == 'P') {
                                $err = true;
                                $contenu = 'ligne  ' . $ligne . '  :  code statut ne peut être à \'P\' ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                            if ($suiviPrelActuel->getStatutPrel() == 'P' and $statutPrel == 'P') {
                                if ($suiviPrelActuel->getdatePrel() != $datePrel) {
                                    $contenu = 'ligne  ' . $ligne . '  :  Attention modification de la date de prélevement ' . CHR(13) . CHR(10);
                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($rapport, $contenu);
                                }
                            }
                            if ($suiviPrelActuel->getStatutPrel() == 'P' and $statutPrel == 'E') {
                                if ($suiviPrelActuel->getdatePrel() != $datePrel and ( !$commentaire or $commentaire == '')) {
                                    $contenu = 'ligne  ' . $ligne . '  :  commentaire obligatoire  ' . CHR(13) . CHR(10);
                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($rapport, $contenu);
                                }
                            }
                        }
                    }

                    if ($err) {
                        $erreur++;
                    } else {
                        if ($user->hasRole('ROLE_ADMINSQE')) {
                            $pgProgWebUsers = $repoPgProgWebUsers->getPgProgWebusersByPrestataire($pgCmdPrelev->getPrestaPrel());
                            $pgProgWebUser = $pgProgWebUsers[0];
                        } else {
                            $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
                        }
                        $pgCmdSuiviPrel = new PgCmdSuiviPrel();
                        $pgCmdSuiviPrel->setPrelev($prelev);
                        $pgCmdSuiviPrel->setUser($pgProgWebUser);
                        $pgCmdSuiviPrel->setDatePrel($datePrel);
                        $pgCmdSuiviPrel->setStatutPrel($statutPrel);
                        $pgCmdSuiviPrel->setCommentaire($commentaire);
                        $pgCmdSuiviPrel->setValidation('E');
                        $emSqe->persist($pgCmdSuiviPrel);
                        if ($pgCmdSuiviPrel->getStatutPrel() == 'N') {
                            $pgCmdPrelev->setDatePrelev($datePrel);
                            $pgCmdPrelev->setRealise('N');
                        }
                        $emSqe->persist($pgCmdPrelev);
                        $emSqe->flush();
                    }
                }
            }
            $contenu = CHR(13) . CHR(10) . 'nombre de lignes traitées  : ' . $ligne . CHR(13) . CHR(10);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            fputs($rapport, $contenu);
            $contenu = 'nombre de lignes en erreur  : ' . $erreur . CHR(13) . CHR(10);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            fputs($rapport, $contenu);
            fclose($rapport);
            fclose($fichier);
        }

        $tabMessage = array();
        $tabMessage[0] = $name;
        $tabMessage[1] = 'rapport_' . $name;
        $tabMessage[2] = $response;

        //$session->getFlashBag()->add('notice-warning', $response);

        return new Response(json_encode($tabMessage));
    }

    public function lotPeriodeStationsSupprimerFichierAction($periodeAnId = null, $fichier = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationsSupprimerFichier');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $pathBase = '/base/extranet/Transfert/Sqe/csv';

        $fic = $pathBase . '/' . $fichier;

        unlink($fic);

        $dateDepot = new \DateTime();
        $response = $fichier . ' supprimé le ' . $dateDepot->format('d/m/Y');

        return new Response($response);
    }

    public function lotPeriodeStationsTelechargerRapportAction($periodeAnId = null, $fichier = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationsTelechargerRapport');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $chemin = '/base/extranet/Transfert/Sqe/csv';
        $ext = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));

        header('Content-Type', 'application/' . $ext);
        header('Content-disposition: attachment; filename="' . $fichier . '"');
        header('Content-Length: ' . filesize($chemin . '/' . $fichier));
        readfile($chemin . '/' . $fichier);
        exit();
    }

    public function lotPeriodeStationDemandeAction($stationId = null, $periodeAnId = null, $cmdDemandeId = null) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationDemande');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgPrestaTypfic = $emSqe->getRepository('AeagSqeBundle:PgProgPrestaTypfic');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeById($cmdDemandeId);
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgPeriode = $pgProgLotPeriodeAn->getPeriode();
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);

        if ($pgProgLot->getDelaiPrel()) {
            $dateFin = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
            $delai = $pgProgLot->getDelaiPrel();
            $dateFin->add(new \DateInterval('P' . $delai . 'D'));
        } else {
            $dateFin = $pgProgLotPeriodeAn->getPeriode()->getDateFin();
        }

        $tabDemande = array();

        if ($pgCmdDemande) {
            $tabDemande['cmdDemande'] = $pgCmdDemande;
            $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
            $tabCmdPrelevs = array();
            if ($pgCmdPrelevs) {
                $i = 0;
                foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                    if ($pgCmdPrelev->getStation()->getOuvFoncId() == $stationId and $pgCmdPrelev->getPeriode()->getId() == $pgProgPeriode->getId()) {
                        $tabCmdPrelevs[$i]['cmdPrelev'] = $pgCmdPrelev;
                        $tabCmdPrelevs[$i]['maj'] = 'N';
                        $pgCmdSuiviPrels = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelev($pgCmdPrelev);
                        $tabSuiviPrels = array();
                        $j = 0;
                        if (count($pgCmdSuiviPrels) == 0) {
                            $tabSuiviPrels[$j]['suiviPrel'] = array();
                            $tabSuiviPrels[$j]['maj'] = 'O';
                            $tabCmdPrelevs[$i]['maj'] = 'O';
                            $tabDemande[$i]['maj'] = 'O';
                        } else {
                            foreach ($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
                                $tabSuiviPrels[$j]['suiviPrel'] = $pgCmdSuiviPrel;
                                $tabSuiviPrels[$j]['maj'] = 'N';
                                if ($user->hasRole('ROLE_ADMINSQE') or $pgCmdSuiviPrel->getUser()->getPrestataire()) {
                                    $tabSuiviPrels[$j]['maj'] = 'O';
                                    $tabCmdPrelevs[$i]['maj'] = 'O';
                                    $tabDemande[$i]['maj'] = 'O';
                                } else {
                                    if ($user->hasRole('ROLE_ADMINSQE')) {
                                        $tabSuiviPrels[$j]['maj'] = 'O';
                                        $tabCmdPrelevs[$i]['maj'] = 'O';
                                        $tabDemande[$i]['maj'] = 'O';
                                    } else {
                                        $tabSuiviPrels[$j]['maj'] = 'N';
                                    }
                                }
                                $j++;
                            }
                        }
                        $tabCmdPrelevs[$i]['suiviPrels'] = $tabSuiviPrels;
                        $i++;
                    }
                }
            }
            $tabDemande['cmdPrelevs'] = $tabCmdPrelevs;
        }



//       \Symfony\Component\VarDumper\VarDumper::dump($tabDemande);
//        return new Response('');

        return $this->render('AeagSqeBundle:SuiviHydrobio:lotPeriodeStationDemande.html.twig', array(
                    'user' => $pgProgWebUser,
                    'lotan' => $pgProgLotAn,
                    'station' => $pgRefStationMesure,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'dateFin' => $dateFin,
                    'demande' => $tabDemande));
    }

    public function lotPeriodeStationDemandeSuiviNewAction($prelevId = null, $periodeAnId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviNew');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        if ($pgProgLot->getDelaiPrel()) {
            $dateFin = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
            $delai = $pgProgLot->getDelaiPrel();
            $dateFin->add(new \DateInterval('P' . $delai . 'D'));
        } else {
            $dateFin = $pgProgLotPeriodeAn->getPeriode()->getDateFin();
        }
        $dateActuel = new \DateTime();
        $dateActuel->add(new \DateInterval('P15D'));
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);

        if ($user->hasRole('ROLE_ADMINSQE')) {
            $pgProgWebUsers = $repoPgProgWebUsers->getPgProgWebusersByPrestataire($pgCmdPrelev->getPrestaPrel());
            $pgProgWebUser = $pgProgWebUsers[0];
        } else {
            $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        }
        $pgCmdSuiviPrels = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevOrderDate($pgCmdPrelev);
        if ($pgCmdSuiviPrels) {
            $pgCmdSuiviPrelActuel = $pgCmdSuiviPrels[0];
        } else {
            $pgCmdSuiviPrelActuel = null;
        }
        $pgCmdSuiviPrel = new PgCmdSuiviPrel();
        $form = $this->createForm(new PgCmdSuiviPrelMajType($user, $pgCmdSuiviPrelActuel), $pgCmdSuiviPrel);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $tabMessage = array();
            $nbMessages = 0;
            $err = false;
            if (!$pgCmdSuiviPrel->getDatePrel()) {
                $err = true;
                $contenu = 'veuillez renseigner la date svp';
                $tabMessage[$nbMessages][0] = 'ko';
                $tabMessage[$nbMessages][1] = $contenu;
                $nbMessages++;
            }
            if ($pgCmdSuiviPrel->getStatutPrel() == 'P') {
                if ($pgCmdSuiviPrel->getDatePrel() < $dateActuel or $pgCmdSuiviPrel->getDatePrel() > $dateFin) {
                    $contenu = 'Avertissement date  (' . $pgCmdSuiviPrel->getDatePrel()->format('d/m/Y H:i') . ') non comprise entre ' . $dateActuel->format('d/m/Y H:i') . ' et ' . $dateFin->format('d/m/Y H:i');
                    $tabMessage[$nbMessages][0] = 'av';
                    $tabMessage[$nbMessages][1] = $contenu;
                    $nbMessages++;
                }
            }
            $dateDebut = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
            $dateActuel = new \DateTime();
           if ($pgCmdSuiviPrel->getStatutPrel() != 'P') {
                if ($pgCmdSuiviPrel->getDatePrel() < $dateDebut or $pgCmdSuiviPrel->getDatePrel() > $dateActuel) {
                    $err = true;
                    $contenu = 'Date  (' . $pgCmdSuiviPrel->getDatePrel()->format('d/m/Y H:i') . ') non comprise entre ' . $dateDebut->format('d/m/Y H:i') . ' et ' . $dateActuel->format('d/m/Y H:i');
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $contenu;
                    $nbMessages++;
                }
            }

            if ($pgCmdPrelev->getCodeSupport()->getCodeSupport() != '10') {
                $autrePgCmdPrelevs = $repoPgCmdPrelev->getAutrePrelevs($pgCmdPrelev);
                for ($i = 0; $i < count($autrePgCmdPrelevs); $i++) {
                    $autreSuport = $autrePgCmdPrelevs[$i]['codeSupport'];
                    if ($autreSuport != '10') {
                        $autreDateDebut = new \DateTime($autrePgCmdPrelevs[$i]['datePrel']);
                        $autreDateDebut->sub(new \DateInterval('P7D'));
                        $autreDateFin = new \DateTime($autrePgCmdPrelevs[$i]['datePrel']);
                        $autreDateFin->add(new \DateInterval('P7D'));
                        if ($pgCmdSuiviPrel->getDatePrel() >= $autreDateDebut or $pgCmdSuiviPrel->getDatePrel() <= $autreDateFin) {
                            $err = true;
                            $contenu = 'Date  (' . $pgCmdSuiviPrel->getDatePrel()->format('d/m/Y H:i') . ') doit être inférieure à ' . $autreDateDebut->format('d/m/Y H:i') . ' ou supérieure à  ' . $autreDateFin->format('d/m/Y H:i');
                            $tabMessage[$nbMessages][0] = 'ko';
                            $tabMessage[$nbMessages][1] = $contenu;
                            $nbMessages++;
                        }
                    }
                }
            }



            if ($pgCmdSuiviPrel->getStatutPrel() == 'P') {
                if (!$pgCmdSuiviPrel->getCommentaire() or $pgCmdSuiviPrel->getCommentaire() == '') {
                    $err = true;
                    $contenu = 'Renseigner l’équipe et le contact (portable)  ';
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $contenu;
                    $nbMessages++;
                }
            }
            if ($pgCmdSuiviPrel->getStatutPrel() == 'N') {
                if (!$pgCmdSuiviPrel->getCommentaire() or $pgCmdSuiviPrel->getCommentaire() == '') {
                    $err = true;
                    $contenu = ' Commentaire obligatoire indiquer pourquoi   ';
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $contenu;
                    $nbMessages++;
                }
            }
            foreach ($pgCmdSuiviPrels as $suiviPrel) {
                if ($suiviPrel->getDatePrel() == $pgCmdSuiviPrel->getDatePrel() and
                        $suiviPrel->getStatutPrel() == $pgCmdSuiviPrel->getStatutPrel() and
                        $suiviPrel->getCommentaire() == $pgCmdSuiviPrel->getCommentaire() and
                        $suiviPrel->getValidation() == $pgCmdSuiviPrel->getValidation()) {
                    $err = true;
                    $contenu = 'Suivi déja intégré ';
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $contenu;
                    $nbMessages++;
                }
            }
            if ($pgCmdSuiviPrelActuel) {
                if ($pgCmdSuiviPrelActuel->getStatutPrel() == 'E' and $pgCmdSuiviPrel->getStatutPrel() == 'P') {
                    $err = true;
                    $contenu = 'Le statut ne peut être à \'Prévisionnel\' ' . CHR(13) . CHR(10);
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $contenu;
                    $nbMessages++;
                }
                if ($pgCmdSuiviPrelActuel->getStatutPrel() == 'P' and $pgCmdSuiviPrel->getStatutPrel() == 'P') {
                    if ($pgCmdSuiviPrelActuel->getdatePrel() != $pgCmdSuiviPrel->getDatePrel()) {
                        $contenu = ' Avertissement  modification de la date de prélevement ';
                        $tabMessage[$nbMessages][0] = 'av';
                        $tabMessage[$nbMessages][1] = $contenu;
                        $nbMessages++;
                    }
                }
                if ($pgCmdSuiviPrelActuel->getStatutPrel() == 'P' and $pgCmdSuiviPrel->getStatutPrel() == 'E') {
                    if ($pgCmdSuiviPrelActuel->getdatePrel() != $pgCmdSuiviPrel->getDatePrel() and ( !$pgCmdSuiviPrel->getCommentaire() or $pgCmdSuiviPrel->getCommentaire() == '')) {
                        $err = true;
                        $contenu = 'Commentaire obligatoire  ';
                        $tabMessage[$nbMessages][0] = 'ko';
                        $tabMessage[$nbMessages][1] = $contenu;
                        $nbMessages++;
                    }
                }
            }

            if (!$err) {
                $pgCmdSuiviPrel->setPrelev($pgCmdPrelev);
                $pgCmdSuiviPrel->setUser($pgProgWebUser);
                if (!$pgCmdSuiviPrel->getValidation()) {
                    $pgCmdSuiviPrel->setValidation('E');
                }

                $datePrel = $pgCmdSuiviPrel->getDatePrel();
                $emSqe->persist($pgCmdSuiviPrel);
                if ($pgCmdSuiviPrel->getStatutPrel() == 'F' and $pgCmdSuiviPrel->getValidation() == 'A') {
                    $pgCmdPrelev->setDatePrelev($datePrel);
                    $pgCmdPrelev->setRealise('O');
                } elseif ($pgCmdSuiviPrel->getStatutPrel() == 'N') {
                    $pgCmdPrelev->setDatePrelev($datePrel);
                    $pgCmdPrelev->setRealise('N');
                } else {
                    $pgCmdPrelev->setDatePrelev($pgCmdPrelev->getDemande()->getDateDemande());
                    $pgCmdPrelev->setRealise(null);
                }
                $emSqe->persist($pgCmdPrelev);
                $emSqe->flush();
                $session->getFlashBag()->add('notice-success', 'le suivi du ' . $datePrel->format('d/m/Y') . ' a été créé !');
                if ($nbMessages == 0) {
                    $contenu = 'ok ';
                    $tabMessage[$nbMessages][0] = $contenu;
                }
                return new Response(json_encode($tabMessage));
            } else {
                return new Response(json_encode($tabMessage));
            }
        }

        return $this->render('AeagSqeBundle:SuiviHydrobio:lotPeriodeStationDemandeSuiviNew.html.twig', array(
                    'prelev' => $pgCmdPrelev,
                    'periodeAnId' => $periodeAnId,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'dateFin' => $dateFin,
                    'form' => $form->createView(),
        ));



//          \Symfony\Component\VarDumper\VarDumper::dump($tabDemande);
//        return new Response ('');
    }

    public function lotPeriodeStationDemandeSuiviVoirAction($suiviPrelId = null, $periodeAnId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviVoir');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        if ($pgCmdSuiviPrel->getFichierRps()) {
            $pgCmdFichiersRps = $pgCmdSuiviPrel->getFichierRps();
            $pathBase = $this->getCheminEchange($pgCmdSuiviPrel, $pgCmdFichiersRps->getId());
        } else {
            $pathBase = null;
        }
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $form = $this->createForm(new PgCmdSuiviPrelVoirType($user), $pgCmdSuiviPrel);

        return $this->render('AeagSqeBundle:SuiviHydrobio:lotPeriodeStationDemandeSuiviVoir.html.twig', array(
                    'prelev' => $pgCmdPrelev,
                    'periodeAnId' => $periodeAnId,
                    'suiviPrel' => $pgCmdSuiviPrel,
                    'chemin' => $pathBase,
                    'form' => $form->createView(),
        ));



//          \Symfony\Component\VarDumper\VarDumper::dump($tabDemande);
//        return new Response ('');
    }

    public function lotPeriodeStationDemandeSuiviDeposerAction($suiviPrelId = null, $periodeAnId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviDeposer');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();


        return $this->render('AeagSqeBundle:SuiviHydrobio:lotPeriodeStationDemandeSuiviDeposer.html.twig', array(
                    'prelev' => $pgCmdPrelev,
                    'periodeAnId' => $periodeAnId,
                    'suiviPrel' => $pgCmdSuiviPrel
        ));



//          \Symfony\Component\VarDumper\VarDumper::dump($tabDemande);
//        return new Response ('');
    }

    public function lotPeriodeStationDemandeSuiviSupprimerAction($suiviPrelId = null, $periodeAnId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviSupprimer');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $datePrel = $pgCmdSuiviPrel->getDatePrel();
        if ($pgCmdSuiviPrel->getFichierRps()) {
            $pgCmdFichiersRps = $pgCmdSuiviPrel->getFichierRps();
            $dossier = $this->getCheminEchange($pgCmdSuiviPrel);
            $dir_iterator = new \RecursiveDirectoryIterator($dossier);
            $iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::CHILD_FIRST);

// On supprime chaque dossier et chaque fichier	du dossier cible
            foreach ($iterator as $fichier) {
                if ($fichier != "." && $fichier != "..") {
                    is_dir($fichier) ? null : unlink($fichier);
                }
            }
// On supprime le dossier cible
            rmdir($dossier);
// On supprime l'enregistrement  $pgCmdFichiersRps
            $pgCmdSuiviPrel->setFichierRps(null);
            $emSqe->persist($pgCmdSuiviPrel);
            $emSqe->remove($pgCmdFichiersRps);
        }
        $emSqe->remove($pgCmdSuiviPrel);
        $emSqe->flush();

        $session->getFlashBag()->add('notice-success', 'le suivi du prélèvement du   : ' . $datePrel->format('d/m/Y') . ' a été supprimé !');

        return $this->redirect($this->generateUrl('AeagSqeBundle_suiviHydrobio_lot_periode_stations', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                            'periodeAnId' => $periodeAnId)));


//          \Symfony\Component\VarDumper\VarDumper::dump($tabDemande);
//        return new Response ('');
    }

    public function lotPeriodeStationDemandeSuiviFichierDeposerAction($suiviPrelId = null, $periodeAnId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviFichierDeposer');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R10');

// Récupération des valeurs du fichier

        $name = $_FILES['file']['name'];
        $tmpName = $_FILES['file']['tmp_name'];
        $error = $_FILES['file']['error'];
        $size = $_FILES['file']['size'] / 1024;
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $response = null;

        switch ($error) {
            case UPLOAD_ERR_OK:
                $valid = true;
//validate file size
                if ($size / 1024 / 1024 > 2) {
                    $valid = false;
                    $response = 'La taille du fichier est plus grande que la taille autorisée.';
                }
//upload file
                if ($valid) {
// Enregistrement des valeurs en base
                    if ($pgCmdSuiviPrel->getFichierRps()) {
                        $pgCmdFichiersRps = $pgCmdSuiviPrel->getFichierRps();
                        $emSqe->remove($pgCmdFichiersRps);
                    }
                    $pgCmdFichiersRps = new PgCmdFichiersRps();
                    $pgCmdFichiersRps->setDemande($pgCmdPrelev->getDemande());
                    $pgCmdFichiersRps->setNomFichier($name);
                    $pgCmdFichiersRps->setDateDepot(new \DateTime());
                    $pgCmdFichiersRps->setTypeFichier('SUI');
                    $pgCmdFichiersRps->setPhaseFichier($pgProgPhases);
                    $pgCmdFichiersRps->setUser($pgProgWebUser);
                    $pgCmdFichiersRps->setSuppr('N');

                    $emSqe->persist($pgCmdFichiersRps);
                    $pgCmdSuiviPrel->setFichierRps($pgCmdFichiersRps);
                    $emSqe->persist($pgCmdSuiviPrel);
                    $emSqe->flush();
// Enregistrement du fichier sur le serveur
                    $pathBase = $this->getCheminEchange($pgCmdSuiviPrel, $pgCmdFichiersRps->getId());
                    if (!is_dir($pathBase)) {
                        if (!mkdir($pathBase, 0777, true)) {
                            $session->getFlashBag()->add('notice-error', 'Le répertoire : ' . $pathBase . ' n\'a pas pu être créé');
                            ;
                        }
                    }
                    move_uploaded_file($_FILES['file']['tmp_name'], $pathBase . '/' . $name);

                    $dateDepot = $pgCmdFichiersRps->getDateDepot();
                    $response = $name . ' déposé le ' . $dateDepot->format('d/m/Y');
                    break;
                }
            case UPLOAD_ERR_INI_SIZE:
                $response = 'La taille (' . $size . ' octets' . ') du fichier téléchargé excède la taille de upload_max_filesize dans php.ini.';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $response = 'La taille (' . $size . ') du fichier téléchargé excède la taille de MAX_FILE_SIZE qui a été spécifié dans le formulaire HTML.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $response = 'Le fichier n\'a été que partiellement téléchargé.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $response = 'Aucun fichier sélectionné.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $response = 'Manquantes dans un dossier temporaire. Introduit en PHP 4.3.10 et PHP 5.0.3.';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $response = 'Impossible d\'écrire le fichier sur le disque. Introduit en PHP 5.1.0.';
                break;
            case UPLOAD_ERR_EXTENSION:
                $response = 'Le téléchargement du fichier arrêté par extension. Introduit en PHP 5.2.0.';
                break;
            default:
                $response = 'erreur inconnue';
                break;
        }


        return new Response($response);
    }

    public function lotPeriodeStationDemandeSuiviFichierSupprimerAction($suiviPrelId = null, $periodeAnId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviFichierSupprimer');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $pgCmdFichiersRps = $pgCmdSuiviPrel->getFichierRps();

        $dossier = $this->getCheminEchange($pgCmdSuiviPrel);
        $dir_iterator = new \RecursiveDirectoryIterator($dossier);
        $iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::CHILD_FIRST);

// On supprime chaque dossier et chaque fichier	du dossier cible
        foreach ($iterator as $fichier) {
            if ($fichier != "." && $fichier != "..") {
                is_dir($fichier) ? null : unlink($fichier);
            }
        }
// On supprime le dossier cible
        rmdir($dossier);
// On supprime l'enregistrement  $pgCmdFichiersRps
        $pgCmdSuiviPrel->setFichierRps(null);
        $emSqe->persist($pgCmdSuiviPrel);
        $emSqe->remove($pgCmdFichiersRps);
        $emSqe->flush();
        $response = null;

//        $html = '<div id="idSelection" class="col-xs-7">';
//        $html += '<form method="POST" enctype="multipart/form-data" action="#" id="idFormFichier">';
//        $html += '<input class="form-control" type="file" name="file" >';
//        $html += '</form>';
//        $html += '</div>';
//        $html += '<div class="col-xs-1">';
//        $html += '<button type="button" id="idDeposer" class="btn btn-success" >Déposer</button>';
//        $html += '</div>';
//
//        $response = $html;

        return new Response($response);
    }

    public function lotPeriodeStationDemandeSuiviFichierTelechargerAction($suiviPrelId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviFichierTelecharger');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');

        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $pgCmdFichiersRps = $pgCmdSuiviPrel->getFichierRps();
        $chemin = $this->getCheminEchange($pgCmdSuiviPrel);
        $fichier = $pgCmdFichiersRps->getNomFichier();
        $ext = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));

        header('Content-Type', 'application/' . $ext);
        header('Content-disposition: attachment; filename="' . $fichier . '"');
        header('Content-Length: ' . filesize($chemin . '/' . $fichier));
        readfile($chemin . '/' . $fichier);
        exit();
    }

    protected function getCheminEchange($pgCmdSuiviPrel) {
        $chemin = $this->container->getParameter('repertoire_echange');
        $chemin .= $pgCmdSuiviPrel->getPrelev()->getDemande()->getAnneeProg() . '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getCommanditaire()->getNomCorres();
        $chemin .= '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getLot()->getId() . '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getId();
        $chemin .= '/SUIVI/' . $pgCmdSuiviPrel->getPrelev()->getId() . '/' . $pgCmdSuiviPrel->getId();

        return $chemin;
    }

}
