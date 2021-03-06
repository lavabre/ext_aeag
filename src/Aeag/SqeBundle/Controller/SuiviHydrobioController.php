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
use Aeag\SqeBundle\Entity\PgCmdDwnldUsrRps;
use Aeag\SqeBundle\Form\PgCmdSuiviPrelMajType;
use Aeag\SqeBundle\Form\PgCmdSuiviPrelVoirType;
use \Aeag\SqeBundle\Form\SyntheseSupportStationType;
use Aeag\SqeBundle\Form\LotPeriodeStationDemandeSuiviSaisirType;
use Aeag\AeagBundle\Controller\AeagController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class SuiviHydrobioController extends Controller {

    public function indexAction() {

        $user = $this->getUser();

        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        if (!is_object($user)) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }


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
            if (substr($pgProgTypeMilieu->getCodeMilieu(), 1, 2) === 'HB' || $pgProgTypeMilieu->getCodeMilieu() === 'RHM') {
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
            if ($pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'DEL' && $pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'INV') {
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
                    if (count($tabStations) > 0) {
                        for ($k = 0; $k < count($tabStations); $k++) {
                            if ($tabStations[$k]->getOuvFoncid() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncid()) {
                                $trouve = true;
                                break;
                            }
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

        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStations');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        if (!is_object($user)) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }

        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();

        $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandeByLotan($pgProgLotAn);
        $autoriser = false;
        foreach ($pgCmdDemandes as $pgCmdDemande) {
            if ($pgCmdDemande->getPrestataire() == $pgProgWebUser->getPrestataire()) {
                $autoriser = true;
                break;
            }
        }
        if (!$autoriser && $user->hasRole('ROLE_PRESTASQE')) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }


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
                        $tabCmdPrelevs[$nbCmdPrelevs]['commentaire'] = null;
                        $pgCmdSuiviPrels = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevOrderId($pgCmdPrelev);
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
                                $tabSuiviPrels[$nbSuiviPrels]['avisSaisie'] = 'N';
                                if ($pgCmdSuiviPrel->getCommentaire()) {
                                    if ($tabCmdPrelevs[$nbCmdPrelevs]['commentaire'] == null) {
                                        $tabCmdPrelevs[$nbCmdPrelevs]['commentaire'] = $pgCmdSuiviPrel->getCommentaire();
                                    }
                                }
                                if ($user->hasRole('ROLE_ADMINSQE') || ( $pgCmdPrelev->getPrestaPrel() == $pgCmdDemande->getPrestataire())) {
                                    if ($pgCmdSuiviPrel->getStatutPrel() != 'F') {
                                        $tabSuiviPrels[$nbSuiviPrels]['maj'] = 'O';
                                        $tabCmdPrelevs[$nbCmdPrelevs]['maj'] = 'O';
                                    } else {
                                        if (!$pgCmdSuiviPrel->getFichierRps()) {
                                            if ($pgCmdSuiviPrel->getValidation() != 'A') {
                                                $tabSuiviPrels[$nbSuiviPrels]['maj'] = 'O';
                                                $tabCmdPrelevs[$nbCmdPrelevs]['maj'] = 'O';
                                            }
                                        }
                                    }
                                }
                                $commentaire = $pgCmdSuiviPrel->getCommentaire();
                                $tabCommentaires = explode(CHR(13) . CHR(10), $commentaire);
                                for ($nbLignes = 0; $nbLignes < count($tabCommentaires); $nbLignes++) {
                                    $pos = explode(' ', $tabCommentaires[$nbLignes]);
                                    //echo ('ligne : ' . $nbLignes . '  pos : ' . $pos[0] .  ' ligne : ' . $tabCommentaires[$nbLignes] . '</br>');
                                    if ($pos[0] == 'Déposé' && $pos[1] = 'le' && $pos[3] == 'à' && $pos[5] == 'par' && $pos[7] == ':') {
                                        $commentaireBis = null;
                                        for ($nbLignesBis = 0; $nbLignesBis < $nbLignes; $nbLignesBis++) {
                                            $commentaireBis .= $tabCommentaires[$nbLignesBis] . CHR(13) . CHR(10);
                                        }
                                        if (!$user->hasRole('ROLE_ADMINSQE')) {
                                            $tabSuiviPrels[$nbSuiviPrels]['suiviPrel']->setCommentaire($commentaireBis);
                                            break;
                                        }
                                    }
                                }
                                $nbSuiviPrels++;
                                break;
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
//       return new Response ('');

        return $this->render('AeagSqeBundle:SuiviHydrobio:lotPeriodeStations.html.twig', array(
                    'user' => $pgProgWebUser,
                    'lotan' => $pgProgLotAn,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'demande' => $pgCmdDemande,
                    'dateFin' => $dateFin,
                    'stations' => $tabStations,
                    'rapport' => $rapport));
    }

    public function lotPeriodeStationsImporterAction($periodeAnId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationsIntegrer');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        return $this->render('AeagSqeBundle:SuiviHydrobio:lotPeriodeStationsImporter.html.twig', array(
                    'periodeAnId' => $periodeAnId,
                    'rapport' => null));



//          \Symfony\Component\VarDumper\VarDumper::dump($tabDemande);
//        return new Response ('');
    }

    public function lotPeriodeStationsImporterFichierAction($periodeAnId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationsIntegrerFichier');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgSandreSupports = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgWebUserTypmil = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserTypmil');
        $repoPgProgWebuserZgeoref = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserZgeoref');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgCmdDwnldUsrRps = $emSqe->getRepository('AeagSqeBundle:PgCmdDwnldUsrRps');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgProgWebUserTypmils = $repoPgProgWebUserTypmil->getPgProgWebuserTypmilByWebuser($pgProgWebUser);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R10');

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgPeriode = $pgProgLotPeriodeAn->getPeriode();
        if ($pgProgLot->getDelaiPrel()) {
            $dateFin = clone($pgProgPeriode->getDateDeb());
            $delai = $pgProgLot->getDelaiPrel();
            $dateFin->add(new \DateInterval('P' . $delai . 'D'));
        } else {
            $dateFin = $pgProgPeriode->getDateFin();
        }
        if ($pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'DEL' && $pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'INV') {
            $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
            $tabStations = array();
            $nbStations = 0;
            $j = 0;
            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                $trouve = false;
                if (count($tabStations) > 0) {
                    for ($k = 0; $k < count($tabStations); $k++) {
                        if ($tabStations[$k]['station']->getOuvFoncid() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncid()) {
                            $trouve = true;
                            break;
                        }
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
                            $pgCmdSuiviPrels = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevOrderId($pgCmdPrelev);
                            $tabCmdPrelevs[$nbCmdPrelevs]['cmdSuiviPrelevs'] = $pgCmdSuiviPrels;
                            $nbCmdPrelevs++;
                        }
                        $tabStations[$j]['prelevs'] = $tabCmdPrelevs;
                        $tabStations[$j]['fichiers'] = array();
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
        $size = $_FILES['file']['size'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $response = null;
        $tabMessage = array();
        $nbMessages = 0;
        $tabRapport = array();
        $nbRapport = 0;
        $nbCorrect = 0;
        $nbIncorrect = 0;

        $dateDepot = new \DateTime();
        $pathBase = '/base/extranet/Transfert/Sqe/csv-' . $dateDepot->format('Y-m-d-H-i-s');
        $pathRapport = '/base/extranet/Transfert/Sqe/csv';
        $ficRapport = $user->getId() . '_' . $dateDepot->format('Y-m-d-H') . '_rapport.csv';
        if (!is_dir($pathBase)) {
            if (!mkdir($pathBase, 0777, true)) {
                $session->getFlashBag()->add('notice-error', 'Le répertoire : ' . $pathBase . ' n\'a pas pu être créé');
                ;
            }
        }

        switch ($error) {
            case UPLOAD_ERR_OK:
                $valid = true;
                if (!in_array($ext, array('zip'))) {
                    $valid = false;
                    $response = 'extension du fichier incorrecte.';
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $response;
                    $nbMessages++;
                }
//validate file size
                if ($size > 335544320) {
                    $valid = false;
                    $response = 'La taille du fichier (' . $size / 1024 . ') est plus grande que la taille autorisée.';
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $response;
                    $nbMessages++;
                }
//upload file
                if ($valid) {
// Enregistrement du fichier sur le serveur
                    move_uploaded_file($_FILES['file']['tmp_name'], $pathBase . '/' . $name);

                    $response = $name . ' déposé le ' . $dateDepot->format('d/m/Y');
                }
                break;
            case UPLOAD_ERR_INI_SIZE:
                $response = 'La taille du fichier téléchargé excède la taille de upload_max_filesize dans php.ini.';
                $tabMessage[$nbMessages][0] = 'ko';
                $tabMessage[$nbMessages][1] = $response;
                $nbMessages++;
                $valid = false;
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $response = 'La taille (' . $size . ') du fichier téléchargé excède la taille de MAX_FILE_SIZE qui a été spécifié dans le formulaire HTML.';
                $tabMessage[$nbMessages][0] = 'ko';
                $tabMessage[$nbMessages][1] = $response;
                $nbMessages++;
                $valid = false;
                break;
            case UPLOAD_ERR_PARTIAL:
                $response = 'Le fichier n\'a été que partiellement téléchargé.';
                $tabMessage[$nbMessages][0] = 'ko';
                $tabMessage[$nbMessages][1] = $response;
                $nbMessages++;
                $valid = false;
                break;
            case UPLOAD_ERR_NO_FILE:
                $response = 'Aucun fichier sélectionné.';
                $tabMessage[$nbMessages][0] = 'ko';
                $tabMessage[$nbMessages][1] = $response;
                $nbMessages++;
                $valid = false;
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $response = 'Manquantes dans un dossier temporaire. Introduit en PHP 4.3.10 et PHP 5.0.3.';
                $tabMessage[$nbMessages][0] = 'ko';
                $tabMessage[$nbMessages][1] = $response;
                $nbMessages++;
                $valid = false;
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $response = 'Impossible d\'écrire le fichier sur le disque. Introduit en PHP 5.1.0.';
                $tabMessage[$nbMessages][0] = 'ko';
                $tabMessage[$nbMessages][1] = $response;
                $nbMessages++;
                $valid = false;
                break;
            case UPLOAD_ERR_EXTENSION:
                $response = 'Le téléchargement du fichier arrêté par extension. Introduit en PHP 5.2.0.';
                $tabMessage[$nbMessages][0] = 'ko';
                $tabMessage[$nbMessages][1] = $response;
                $nbMessages++;
                $valid = false;
                break;
            default:
                $response = 'erreur inconnue';
                $tabMessage[$nbMessages][0] = 'ko';
                $tabMessage[$nbMessages][1] = $response;
                $nbMessages++;
                $valid = false;
                break;
        }

        if ($valid) {
            $liste = array();
            $liste = $this->unzip($pathBase . '/' . $name, $pathBase . '/');
            $rapport = fopen($pathRapport . '/' . $user->getId() . '_' . $dateDepot->format('Y-m-d-H') . '_rapport.csv', "w+");
            $contenu = '                                  Rapport d\'intégration du fichier : ' . $name . ' déposé le ' . $dateDepot->format('d/m/Y') . CHR(13) . CHR(10) . CHR(13) . CHR(10);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            $tabRapport[$nbRapport] = '<h4><div class="text-center">Rapport d\'intégration du fichier : ' . $name . ' déposé le ' . $dateDepot->format('d/m/Y') . '</div></h4>';
            $nbRapport++;
            fputs($rapport, $contenu);
            $contenu = 'Le fichier zip contient  ' . count($liste) . ' fichier(s)' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            $tabRapport[$nbRapport] = 'Le fichier zip contient  ' . count($liste) . ' fichier(s)</br>';
            $nbRapport++;
            fputs($rapport, $contenu);

            $erreur = 0;

            foreach ($liste as $nomFichier) {
//$tabNomFichier = explode('-', $nomFichier);
                $trouve = false;
                if (count($tabStations) > 0) {
                    for ($k = 0; $k < count($tabStations); $k++) {
//if ($tabStations[$k]['station']->getCode() == $tabNomFichier[0]) {
                        if (strpos($nomFichier, $tabStations[$k]['station']->getCode()) !== false || strpos($nomFichier, $tabStations[$k]['station']->getNumero()) !== false) {
                            $trouve = true;
                            break;
                        }
                    }
                }
                if (!$trouve) {
                    if (filesize($pathBase . '/' . $nomFichier) > 0) {
                        $contenu = 'pas de station à raccorder au fichier ' . $nomFichier . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                        $nbIncorrect++;
//$erreur = 1;
                    }
                } else {
                    $st = count($tabStations[$k]['fichiers']);
                    $tabStations[$k]['fichiers'][$st] = $nomFichier;
                    $nbCorrect++;
                }
            }

            if (count($tabStations) > 0) {
                for ($k = 0; $k < count($tabStations); $k++) {
                    if (count($tabStations[$k]['fichiers']) > 0) {
                        $tabFichiers = $tabStations[$k]['fichiers'];
                        if (count($tabFichiers) < 3) {
                            $contenu = 'La station  ' . $tabStations[$k]['station']->getCode() . ' doit regrouper au moins 3 fichiers ' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                            $erreur = 1;
                            $nbCorrect = $nbCorrect - count($tabFichiers);
                            $nbIncorrect = $nbIncorrect + count($tabFichiers);
                        } elseif (count($tabFichiers) > 0) {
                            $NbFt = 0;
                            $NbPhoto = 0;
                            for ($nb = 0; $nb < count($tabFichiers); $nb++) {
//$tabNomFichier = explode('-', $tabFichiers[$nb]);
//if ($tabNomFichier[1] != 'ft' && $tabNomFichier[1] != 'photo1' && $tabNomFichier[1] != 'photo2') {
                                if ((strpos(strtoupper($tabFichiers[$nb]), 'FT') === false) && (strpos(strtoupper($tabFichiers[$nb]), 'PHOTO') === false)) {
                                    $contenu = 'La station  ' . $tabStations[$k]['station']->getCode() . ' ne peut pas regrouper  le fichier : ' . $tabFichiers[$nb] . ' (non reconnu comme photo ni fiche terrain)' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($rapport, $contenu);
                                    $erreur = 1;
                                    $nbCorrect = $nbCorrect - 1;
                                    $nbIncorrect = $nbIncorrect + 1;
                                } else {
                                    if (strpos(strtoupper($tabFichiers[$nb]), 'FT') !== false) {
                                        $NbFt++;
                                    }
                                    if (strpos(strtoupper($tabFichiers[$nb]), 'PHOTO') !== false) {
                                        $NbPhoto++;
                                    }
                                }
                            }
                            if ($NbFt < 1 || $NbPhoto < 2) {
                                $contenu = 'La station  ' . $tabStations[$k]['station']->getCode() . ' doit  regrouper  au moins un fichier dont le nom contient \'ft\' et 2 fichiers dont le nom contient \'photo\'.' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                                $erreur = 1;
                            }
                        }
                    } else {
                        $tabFichiers = 0;
                    }
                }
            }
            if ($erreur == 0) {
                $tabSupport = array();
                $nbSupport = 0;
                if (count($tabStations) > 0) {
                    for ($k = 0; $k < count($tabStations); $k++) {
                        if (count($tabStations[$k]['fichiers']) > 0) {
                            $tabFichiers = $tabStations[$k]['fichiers'];
                            if (count($tabFichiers) > 1) {
                                $fichier_archive = true;
                                $nb_archive = count($tabFichiers);
                                $files = array();
                                $zip = new \ZipArchive();
                                $zipName = $tabStations[$k]['station']->getCode() . "-archive.zip";
                                $contenu = 'le fichier  ' . $zipName . ' regroupe ' . count($tabFichiers) . ' fichiers : ' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                                for ($nb = 0; $nb < count($tabFichiers); $nb++) {
                                    array_push($files, $pathBase . '/' . $tabFichiers[$nb]);
                                    $contenu = '                  -  ' . $tabFichiers[$nb] . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($rapport, $contenu);
                                }
                                $zip->open($pathBase . '/' . $zipName, \ZipArchive::CREATE);
                                foreach ($files as $f) {
                                    $zip->addFromString(basename($f), file_get_contents($f));
                                }
                                $zip->close();
                                $name = $zipName;
                            } else {
                                $fichier_archive = false;
                                $name = $tabFichiers[0];
                            }

                            $pgCmdPrelev = $tabStations[$k]['prelevs'][0]['cmdPrelev'];
                            $trouve = false;
                            if (count($tabSupport) > 0) {
                                for ($nbSupport = 0; $nbSupport < count($tabSupport); $nbSupport++) {
                                    if ($tabSupport[$nbSupport] == $pgCmdPrelev->getCodeSupport()->getCodeSupport()) {
                                        $trouve = true;
                                        break;
                                    }
                                }
                            }
                            if (!$trouve) {
                                $nbSupport = count($tabSupport);
                                $tabSupport[$nbSupport] = $pgCmdPrelev->getCodeSupport()->getCodeSupport();
                            }
                            $pgCmdSuiviPrels = $tabStations[$k]['prelevs'][0]['cmdSuiviPrelevs'];
                            if ($pgCmdSuiviPrels) {
                                $pgCmdSuiviPrel = $tabStations[$k]['prelevs'][0]['cmdSuiviPrelevs'][0];
                                if (($pgCmdSuiviPrel->getStatutPrel() == 'N') || ( $pgCmdSuiviPrel->getStatutPrel() == 'F') || ( $pgCmdSuiviPrel->getStatutPrel() == 'R')) {
                                    if ($pgCmdSuiviPrel->getFichierRps()) {
                                        $pgCmdFichiersRps = $pgCmdSuiviPrel->getFichierRps();
                                        $pgCmdDwnldUsrRpss = $repoPgCmdDwnldUsrRps->getPgCmdDwnldUsrRpsByFichierReponse($pgCmdFichiersRps);
                                        foreach ($pgCmdDwnldUsrRpss as $pgCmdDwnldUsrRps) {
                                            $emSqe->remove($pgCmdDwnldUsrRps);
                                        }
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
                                    $pathBaseFic = $this->getCheminEchange($pgCmdSuiviPrel, $pgCmdFichiersRps->getId());
                                    if (!is_dir($pathBaseFic)) {
                                        if (!mkdir($pathBaseFic, 0777, true)) {
                                            $session->getFlashBag()->add('notice-error', 'Le répertoire : ' . $pathBaseFic . ' n\'a pas pu être créé');
                                            ;
                                        }
                                    }
//                        $contenu = 'Le fichier ' . $pathBase . '/' . $name . ' a été déposé vers  ' . $pathBaseFic . '/' . $name . CHR(13) . CHR(10) . CHR(13) . CHR(10);
//                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
//                        fputs($rapport, $contenu);
                                    copy($pathBase . '/' . $name, $pathBaseFic . '/' . $name);
                                    // unlink($pathBase . '/' . $name);
                                    $contenu = 'Le fichier ' . $name . ' a été déposé sur la station ' . $tabStations[$k]['station']->getCode() . ' ' . $tabStations[$k]['station']->getLibelle() . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($rapport, $contenu);
                                } else {
                                    $contenu = 'Association impossible : le dernier suivi de la station  ' . $tabStations[$k]['station']->getCode() . ' doit être "Effectué" ou "Non effectué".' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($rapport, $contenu);
                                    if ($fichier_archive) {
                                        $nbCorrect = $nbCorrect - $nb_archive;
                                        $nbIncorrect = $nbIncorrect + $nb_archive;
                                    } else {
                                        $nbCorrect = $nbCorrect - 1;
                                        $nbIncorrect = $nbIncorrect + 1;
                                    }
                                }
                            } else {
                                $contenu = 'Association impossible : pas de suivi renseigné pour la station  ' . $tabStations[$k]['station']->getCode() . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        } else {
                            $pgCmdSuiviPrels = $tabStations[$k]['prelevs'][0]['cmdSuiviPrelevs'];
                            if ($pgCmdSuiviPrels) {
                                $pgCmdSuiviPrel = $tabStations[$k]['prelevs'][0]['cmdSuiviPrelevs'][0];
                                if ($pgCmdSuiviPrel->getStatutPrel() == 'F' && !$pgCmdSuiviPrel->getfichierRps()) {
                                    $contenu = 'Attention : le dernier suivi de la station  ' . $tabStations[$k]['station']->getCode() . ' a le statut : "Effectué" et il n\'y a pas de fichier terrain associé.' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($rapport, $contenu);
                                }
                            }
                        }
                    }
                }
            } else {
                $contenu = 'Au moins une erreur rencontrée. Aucun fichier intégré' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }
            fclose($rapport);
        } else {
            $erreur = 1;
        }

        if ($erreur == 0) {
            $objetMessage = "fichier terrrain déposé ";
            $txtMessage = "Un ou plusieurs fichiers terrain ont été déposés sur le lot " . $pgProgLot->getNomLot() . " pour la période du " . $pgProgPeriode->getDateDeb()->format('d/m/Y') . " au " . $dateFin->format('d/m/Y');
            $mailer = $this->get('mailer');
            // envoi mail  aux XHBIO
            $pgProgWebusers = $repoPgProgWebUsers->getPgProgWebusersByTypeUser('XHBIO');
            foreach ($pgProgWebusers as $destinataire) {
                if ($destinataire->getCodeSupport()) {
                    $trouve = false;
                    if (count($tabSupport) > 0) {
                        for ($nbSupport = 0; $nbSupport < count($tabSupport); $nbSupport++) {
                            if ($tabSupport[$nbSupport] == $destinataire->getCodeSupport()) {
                                $trouve = true;
                                break;
                            }
                        }
                    }
                } else {
                    $trouve = true;
                }
                if ($trouve) {
                    $trouve = false;
                    $pgProgWebuserZgeorefs = $repoPgProgWebuserZgeoref->getPgProgWebuserZgeorefByWebuser($destinataire);
                    foreach ($pgProgWebuserZgeorefs as $pgProgWebuserZgeoref) {
                        if ($pgProgWebuserZgeoref->getZgeoref()->getId() == $pgProgLot->getZgeoref()->getId()) {
                            $trouve = true;
                        }
                    }
                    if ($trouve) {
                        // Envoi d'un mail
                        if ($this->get('aeag_sqe.message')->envoiMessage($emSqe, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                            $texte = 'un email  vous a été envoyé par ' . $pgProgWebUser->getNom() . ' suite à l\'intégration de plusieurs fichiers de terrain ' . CHR(13) . CHR(10) . ' sur le lot ' . $pgProgLot->getNomLot() . ' pour la période du ' . $pgProgPeriode->getDateDeb()->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y');
                            $notifications = $this->get('aeag.notifications');
                            $userDest = $repoUsers->getUserById($destinataire->getExtId());
                            $notifications->createNotification($user, $userDest, $em, $session, $texte);
                        } else {
                            $session->getFlashBag()->add('notice-warning', 'Le dépôt a été traité, mais l\'email n\'a pas pu être envoyé à ' . $destinataire->getNom());
                        }
                    }
                }
            }
            // envoi mail  aux presta connecte
            $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
            if ($pgProgWebUser) {
                $txtMessage .= '<br/><br/>Veullez trouver en pièce jointe le rapport d\'intégration';
                $htmlMessage = "<html><head></head><body>";
                $htmlMessage .= "Bonjour, <br/><br/>";
                $htmlMessage .= $txtMessage;
                $htmlMessage .= "<br/><br/>Cordialement, <br/>L'équipe SQE";
                $htmlMessage .= "</body></html>";
                $mail = \Swift_Message::newInstance('Wonderful Subject')
                        ->setSubject($objetMessage)
                        ->setFrom('automate@eau-adour-garonne.fr')
                        ->setTo($pgProgWebUser->getMail())
                        ->setBody($htmlMessage, 'text/html');

                $mail->attach(\Swift_Attachment::fromPath($pathRapport . '/' . $ficRapport));
                $mailer->send($mail);

                $texte = 'un email  vous a été envoyé avec en pièce jointe le fichier rapport du dépôt ';
                $notifications = $this->get('aeag.notifications');
                $notifications->createNotification($user, $user, $em, $session, $texte);
            }
        }

        $tabRapport[$nbRapport] = "Nombre de fichiers intégrés : " . $nbCorrect;
        $nbRapport++;
        $tabRapport[$nbRapport] = "Nombre de fichiers incorrects : " . $nbIncorrect;
        $nbRapport++;
        $tabRapport[$nbRapport] = "</br><h5><div class='text-center'>Voir le rapport d'integration </div></h5>";
        if ($nbIncorrect == 0 && $valid) {
            $this->rmAllDir($pathBase);
        }

        $tabReponse = array();
        $tabReponse[0] = $name;
        $tabReponse[1] = 'rapport_' . $name;
        $tabReponse[2] = $tabMessage;
        $tabReponse[3] = $tabRapport;
        $tabReponse[4] = $ficRapport;

//         \Symfony\Component\VarDumper\VarDumper::dump($tabReponse);
//          return new Response ('');
//$session->getFlashBag()->add('notice-warning', $response);

        return new Response(json_encode($tabReponse));
    }

    public function lotPeriodeStationsSupprimerImportAction($periodeAnId = null, $fichier = null) {

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
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoUsers = $em->getRepository('AeagUserBundle:User');
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
        if ($pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'DEL' && $pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'INV') {
            $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
            $tabStations = array();
            $nbStations = 0;
            $j = 0;
            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                $trouve = false;
                if (count($tabStations) > 0) {
                    for ($k = 0; $k < count($tabStations); $k++) {
                        if ($tabStations[$k]['station']->getOuvFoncid() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncid()) {
                            $trouve = true;
                            break;
                        }
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
                            $pgCmdSuiviPrels = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevOrderId($pgCmdPrelev);
                            $tabCmdPrelevs[$nbCmdPrelevs]['cmdSuiviPrelevs'] = $pgCmdSuiviPrels;
                            $nbCmdPrelevs++;
                        }
                        $tabStations[$j]['prelevs'] = $tabCmdPrelevs;
                        $tabStations[$j]['fichiers'] = array();
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
                if (!in_array($ext, array('csv'))) {
                    $valid = false;
                    $response = 'extension du fichier incorrecte.';
                }
//validate file size
                if ($size > 335544320) {
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
                }
                break;
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
            $fichierIn = fopen($pathBase . '/' . $name, "r");
            $fichierOut = fopen($pathBase . '/' . 'trans-' . $user->getId() . '.csv', "w+");
            $rapport = fopen($pathBase . '/' . $user->getId() . '_' . $dateDepot->format('Y-m-d-H') . '_rapport.csv', "w+");
            $contenu = 'rapport d\'intégration du fichier : ' . $name . ' déposé le ' . $dateDepot->format('d/m/Y') . CHR(13) . CHR(10) . CHR(13) . CHR(10);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            fputs($rapport, $contenu);
            $erreur = 0;
            $ligne = 0;
            while (($n = fgets($fichierIn, 1024)) !== false) {
                $n = str_replace(CHR(10), "", $n);
                $n = str_replace(CHR(13), "\r\n", $n);
                fputs($fichierOut, $n);
            }
            fclose($fichierIn);
            fclose($fichierOut);
            $ligne = 0;
            $fichier = fopen($pathBase . '/' . 'trans-' . $user->getId() . '.csv', "r");
            $tab = fgetcsv($fichier, 1024, ';', '\'');
            while (($tab = fgetcsv($fichier, 1024, ';', '\'')) !== false) {
//            while (!feof($fichier)) {
//                $tab = fgetcsv($fichier, 1024, ';');
                if (count($tab) > 1) {
                    $err = false;
                    $ligne++;
                    $codeStation = $tab[0];
                    $prelevs = array();
                    $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByCode($codeStation);
                    if (!$pgRefStationMesure) {
                        $err = true;
                        $contenu = 'ligne  ' . $ligne . ' : Erreur code station inconnu (' . $tab[0] . ')' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } else {
                        $trouve = false;
                        if (count($tabStations) > 0) {
                            for ($i = 0; $i < count($tabStations); $i++) {
                                if ($tabStations[$i]['station'] == $pgRefStationMesure) {
                                    $trouve = true;
                                    $prelevs = $tabStations[$i]['prelevs'];
                                    break;
                                }
                            }
                        }
                        if (!$trouve) {
                            $err = true;
                            $contenu = 'ligne  ' . $ligne . ' : Erreur code station  (' . $codeStation . ') non référencé dans la liste' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                    }

                    $codeSupport = $tab[1];
                    $pgSandreSupports = $repoPgSandreSupports->getPgSandreSupportsByCodeSupport($codeSupport);
                    if (!$pgSandreSupports) {
                        $err = true;
                        $contenu = 'ligne  ' . $ligne . ' : Erreur code support inconnu (' . $codeSupport . ')' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }

                    $statutPrel = $tab[2];
                    if ($statutPrel != 'P' && $statutPrel != 'F' && $statutPrel != 'N' && $statutPrel != 'R') {
                        $err = true;
                        $contenu = 'ligne  ' . $ligne . ' : Erreur code statut inconnu (\'' . $statutPrel . '\')' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }

                    $dateActuel = new \DateTime();
                    //$dateActuel->add(new \DateInterval('P15D'));
                    $date = $tab[3];
                    $tabDate = explode(' ', $date);
                    if (count($tabDate) != 2) {
                        $err = true;
                        $contenu = 'ligne  ' . $ligne . ' : Erreur date heure incorrecte (' . $date . ')' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } else {
                        list( $jour, $mois, $annee, $heure, $min ) = sscanf($date, "%d/%d/%d %d:%d");
                        $datePrel = new \DateTime($annee . '-' . $mois . '-' . $jour . ' ' . $heure . ':' . $min . ':00');

                        if (!$datePrel) {
                            $err = true;
                            $contenu = 'ligne  ' . $ligne . ' : Erreur date heure incorrecte (' . $date . ')' . CHR(13) . CHR(10);
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
                            if ($datePrel < $dateActuel || $datePrel > $dateFin) {
                                //$contenu = 'ligne  ' . $ligne . '  :  Avertissement date  (' . $datePrel->format('d/m/Y H:i') . ') non comprise entre ' . $dateActuel->format('d/m/Y H:i') . ' et ' . $dateFin->format('d/m/Y H:i') . CHR(13) . CHR(10);
                                if ($datePrel < $dateActuel) {
                                    $contenu = 'ligne  ' . $ligne . ' : Avertissement date prévisionnelle (' . $datePrel->format('d/m/Y H:i') . ') antérieure à la date actuelle (' . $dateActuel->format('d/m/Y H:i') . ')' . CHR(13) . CHR(10);
                                } else {
                                    $contenu = 'ligne  ' . $ligne . ' : Avertissement date prévisionnelle (' . $datePrel->format('d/m/Y H:i') . ') postérieure à la date de fin de la période programmée (' . $dateFin->format('d/m/Y H:i') . ')' . CHR(13) . CHR(10);
                                }
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        }
                        $dateDebut = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
                        $dateActuel = new \DateTime();
                        if ($statutPrel != 'P') {
                            if ($datePrel < $dateDebut || $datePrel > $dateActuel) {
                                if (!$user->hasRole('ROLE_ADMINSQE')) {
                                    $err = true;
                                }
                                //$contenu = 'ligne  ' . $ligne . '  :   date  (' . $datePrel->format('d/m/Y H:i') . ') non comprise entre ' . $dateDebut->format('d/m/Y H:i') . ' et ' . $dateActuel->format('d/m/Y H:i') . CHR(13) . CHR(10);
                                if ($datePrel < $dateDebut) {
                                    $contenu = 'ligne  ' . $ligne . ' : Erreur date de prélèvement (' . $datePrel->format('d/m/Y H:i') . ') antérieure à la date de début de la période programmée (' . $dateDebut->format('d/m/Y H:i') . ')' . CHR(13) . CHR(10);
                                } else {
                                    $contenu = 'ligne  ' . $ligne . ' : Erreur date de prélèvement (' . $datePrel->format('d/m/Y H:i') . ') postérieure à la date actuelle (' . $dateActuel->format('d/m/Y H:i') . ')' . CHR(13) . CHR(10);
                                }
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        }
                    }

                    $commentaire = $tab[4];
                    if ($statutPrel == 'P') {
                        if (!$commentaire || $commentaire == '') {
                            $contenu = 'ligne  ' . $ligne . ' : Avertissement commentaire renseigner l’équipe et le contact (portable)  ' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                    }
                    if ($statutPrel == 'N' || $statutPrel == 'R') {
                        if (!$commentaire || $commentaire == '') {
                            $err = true;
                            $contenu = 'ligne  ' . $ligne . ' : Erreur commentaire obligatoire, indiquer pourquoi  ' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                    }

                    $trouve = false;
                    $prelev = null;
                    if (count($prelevs) > 0) {
                        for ($j = 0; $j < count($prelevs); $j++) {
                            $prelev = $prelevs[$j]['cmdPrelev'];
                            if ($prelev->getCodeSupport()->getCodeSupport() != '10' && $prelev->getCodeSupport()->getCodeSupport() != '11') {
                                $autrePgCmdPrelevs = $repoPgCmdPrelev->getAutrePrelevs($prelev);
                                for ($i = 0; $i < count($autrePgCmdPrelevs); $i++) {
                                    $autreSuport = $autrePgCmdPrelevs[$i]['codeSupport'];
                                    //if (( $autreSuport == '4' && $autreSuport == '69') ||
                                    //        ($prelev->getCodeSupport()->getCodeSupport() == '69' && $autreSuport != '4') ||
                                    //        ($prelev->getCodeSupport()->getCodeSupport() == '4' && $autreSuport != '69')) {
                                    if ((($autreSuport == '4') || ($autreSuport == '69')) && ($pgCmdPrelev->getCodeSupport()->getCodeSupport() != '69') && ($pgCmdPrelev->getCodeSupport()->getCodeSupport() != '4')) {
                                        $autreDateDebut = clone($autrePgCmdPrelevs[$i]['datePrel']);
                                        $autreDateDebut->sub(new \DateInterval('P4D'));
                                        $autreDateFin = clone($autrePgCmdPrelevs[$i]['datePrel']);
                                        $autreDateFin->add(new \DateInterval('P7D'));
                                        if ($datePrel >= $autreDateDebut && $datePrel <= $autreDateFin) {
                                            if (!$user->hasRole('ROLE_ADMINSQE')) {
                                                $err = true;
                                            }
                                            $contenu = 'ligne  ' . $ligne . '  : Date  (' . $datePrel->format('d/m/Y H:i') . ') doit être inférieure à ' . $autreDateDebut->format('d/m/Y H:i') . ' ou supérieure à  ' . $autreDateFin->format('d/m/Y H:i') . ' (autre prélèvement Onema prévu)';
                                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                            fputs($rapport, $contenu);
                                        }
                                    } else if (($autreSuport != '10') && ($autreSuport != '11')) {
                                        $autreDateDebut = clone($autrePgCmdPrelevs[$i]['datePrel']);
                                        $autreDateDebut->sub(new \DateInterval('P4D'));
                                        $autreDateFin = clone($autrePgCmdPrelevs[$i]['datePrel']);
                                        $autreDateFin->add(new \DateInterval('P4D'));
                                        if ($datePrel >= $autreDateDebut && $datePrel <= $autreDateFin) {
                                            $contenu = 'ATTENTION, autre prestation programmée le : ' . $autrePgCmdPrelevs[$i]['datePrel']->format('d/m/Y H:i') . ' pour le support ' . $autrePgCmdPrelevs[$i]['support'];
                                            ;
                                            $tabMessage[$nbMessages][0] = 'av';
                                            $tabMessage[$nbMessages][1] = $contenu;
                                            $nbMessages++;
                                        }
                                    }
                                }
                            }
                            if ($prelev->getCodeSupport()->getCodeSupport() == $codeSupport) {
                                $trouve = true;
                                $suiviPrels = $prelevs[$j]['cmdSuiviPrelevs'];
                                $suiviPrelActuel = null;
                                for ($k = 0; $k < count($suiviPrels); $k++) {
                                    if ($k == 0) {
                                        $suiviPrelActuel = $suiviPrels[$k];
                                    }
                                    $suiviPrel = $suiviPrels[$k];
                                    if ($suiviPrel->getDatePrel() == $datePrel and
                                            $suiviPrel->getStatutPrel() == $statutPrel and
                                            $suiviPrel->getCommentaire() == $commentaire) {
                                        $err = true;
                                        $contenu = 'ligne  ' . $ligne . ' :  Erreur suivi déja intégré ' . CHR(13) . CHR(10);
                                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                        fputs($rapport, $contenu);
                                        $k = count($suiviPrels) + 1;
                                    }
                                }
                                $nbStations = 0;
                                for ($k = 0; $k < count($suiviPrels); $k++) {
                                    $suiviPrel = $suiviPrels[$k];
                                    if ($suiviPrel->getDatePrel() == $datePrel and
                                            $suiviPrel->getCommentaire() == $commentaire) {
                                        $nbStations++;
                                    }
                                }
                                if ($pgCmdPrelev->getCodeSupport()->getCodeSupport() == '13' || $pgCmdPrelev->getCodeSupport()->getCodeSupport() == '11') {
                                    if ($nbStations > 4) {
                                        $err = true;
                                        $contenu = 'ligne  ' . $ligne . ' : Erreur 4 stations maxi le même jour pour une même équipe ' . CHR(13) . CHR(10);
                                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                        fputs($rapport, $contenu);
                                    }
                                }
                                if ($pgCmdPrelev->getCodeSupport()->getCodeSupport() == '10') {
                                    if ($nbStations > 6) {
                                        $err = true;
                                        $contenu = 'ligne  ' . $ligne . ' : Erreur 6 stations maxi le même jour pour une même équipe ' . CHR(13) . CHR(10);
                                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                        fputs($rapport, $contenu);
                                    }
                                }
                            }
                        }
                    }
                    if (!$trouve) {
                        $err = true;
                        $contenu = 'ligne  ' . $ligne . ' : Erreur  code support ne correspond pas à celui du prélèvement associé à la station ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } else {
                        if ($suiviPrelActuel) {
                            if ($suiviPrelActuel->getStatutPrel() == 'E' && $statutPrel == 'P') {
                                $err = true;
                                $contenu = 'ligne  ' . $ligne . ' : Erreur code statut ne peut être à \'P\' ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                            if ($suiviPrelActuel->getStatutPrel() == 'P' && $statutPrel == 'P') {
                                if ($suiviPrelActuel->getdatePrel() != $datePrel) {
                                    $contenu = 'ligne  ' . $ligne . ' : Attention modification de la date de prélevement ' . CHR(13) . CHR(10);
                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($rapport, $contenu);
                                }
                            }
                            if ($suiviPrelActuel->getStatutPrel() == 'P' && $statutPrel == 'E') {
                                if ($suiviPrelActuel->getdatePrel() != $datePrel && (!$commentaire || $commentaire == '')) {
                                    $err = true;
                                    $contenu = 'ligne  ' . $ligne . ' : Erreur commentaire obligatoire  ' . CHR(13) . CHR(10);
                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($rapport, $contenu);
                                }
                            }
                            if ($suiviPrelActuel->getStatutPrel() == 'F' && $suiviPrelActuel->getFichierRps()) {
                                $err = true;
                                $contenu = 'ligne  ' . $ligne . ' : Erreur Impossible d\'intégrer un suivi après un suivi effectué avec un fichier terrain déposé  ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        }
                    }

                    if ($err) {
                        $erreur++;
                    } else {
                        if ($user->hasRole('ROLE_ADMINSQE')) {
                            $pgProgWebUsers = $repoPgProgWebUsers->getPgProgWebusersByPrestataire($pgCmdPrelev->getPrestaPrel());
                            if (count($pgProgWebUsers) > 0) {
                                $pgProgWebUser = $pgProgWebUsers[0];
                            } else {
                                $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
                            }
                        } else {
                            $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
                        }
                        $pgCmdSuiviPrel = new PgCmdSuiviPrel();
                        $pgCmdSuiviPrel->setPrelev($prelev);
                        $pgCmdSuiviPrel->setUser($pgProgWebUser);
                        $pgCmdSuiviPrel->setDatePrel($datePrel);
                        $pgCmdSuiviPrel->setStatutPrel($statutPrel);
                        $pgCmdSuiviPrel->setCommentaire(utf8_encode($commentaire));
                        $pgCmdSuiviPrel->setValidation('E');
                        $emSqe->persist($pgCmdSuiviPrel);
                        if ($pgCmdSuiviPrel->getStatutPrel() == 'N' || $pgCmdSuiviPrel->getStatutPrel() == 'R') {
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
            unlink($pathBase . '/' . $name);
            unlink($pathBase . '/trans-' . $user->getId() . '.csv');

            $objetMessage = "fichier de suivi ";
            $txtMessage = "Un fichier de suivi a été déposé sur le lot " . $pgProgLot->getNomLot() . " pour la période du " . $pgProgPeriode->getDateDeb()->format('d/m/Y') . " au " . $dateFin->format('d/m/Y');
            $mailer = $this->get('mailer');
            // envoi mail  aux XHBIO
            $pgProgWebusers = $repoPgProgWebUsers->getPgProgWebusersByTypeUser('XHBIO');
            foreach ($pgProgWebusers as $destinataire) {
                if ($destinataire->getCodeSupport()) {
                    $trouve = false;
                    if (count($tabSupport) > 0) {
                        for ($nbSupport = 0; $nbSupport < count($tabSupport); $nbSupport++) {
                            if ($tabSupport[$nbSupport] == $destinataire->getCodeSupport()) {
                                $trouve = true;
                                break;
                            }
                        }
                    }
                } else {
                    $trouve = true;
                }
                if ($trouve) {
                    $trouve = false;
                    $pgProgWebuserZgeorefs = $repoPgProgWebuserZgeoref->getPgProgWebuserZgeorefByWebuser($destinataire);
                    foreach ($pgProgWebuserZgeorefs as $pgProgWebuserZgeoref) {
                        if ($pgProgWebuserZgeoref->getZgeoref()->getId() == $pgProgLot->getZgeoref()->getId()) {
                            $trouve = true;
                        }
                    }
                    if ($trouve) {
                        // Envoi d'un mail
                        if ($this->get('aeag_sqe.message')->envoiMessage($emSqe, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                            $texte = 'un email  vous a été envoyé par ' . $pgProgWebUser->getNom() . ' suite à l\'intégration de plusieurs fichiers de terrain ' . CHR(13) . CHR(10) . ' sur le lot ' . $pgProgLot->getNomLot() . ' pour la période du ' . $pgProgPeriode->getDateDeb()->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y');
                            $notifications = $this->get('aeag.notifications');
                            $userDest = $repoUsers->getUserById($destinataire->getExtId());
                            $notifications->createNotification($user, $userDest, $em, $session, $texte);
                        } else {
                            $session->getFlashBag()->add('notice-warning', 'Le dépôt a été traité, mais l\'email n\'a pas pu être envoyé à ' . $destinataire->getNom());
                        }
                    }
                }
            }
            // envoi mail  aux presta connecte
            $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
            if ($pgProgWebUser) {
                $objetMessage = "fichier de suivi ";
                $txtMessage = "Un fichier de suivi a été déposé sur le lot " . $pgProgLot->getNomLot() . " pour la période du " . $pgProgPeriode->getDateDeb()->format('d/m/Y') . " au " . $dateFin->format('d/m/Y');
                $mailer = $this->get('mailer');

                $txtMessage .= '<br/><br/>Veullez trouver en pièce jointe le rapport d\'intégration';
                $htmlMessage = "<html><head></head><body>";
                $htmlMessage .= "Bonjour, <br/><br/>";
                $htmlMessage .= $txtMessage;
                $htmlMessage .= "<br/><br/>Cordialement, <br/>L'équipe SQE";
                $htmlMessage .= "</body></html>";
                $mail = \Swift_Message::newInstance('Wonderful Subject')
                        ->setSubject($objetMessage)
                        ->setFrom('automate@eau-adour-garonne.fr')
                        ->setTo($pgProgWebUser->getMail())
                        ->setBody($htmlMessage, 'text/html');

                $mail->attach(\Swift_Attachment::fromPath($pathBase . '/' . '/' . $user->getId() . '_' . $dateDepot->format('Y-m-d-H') . '_rapport.csv'));
                $mailer->send($mail);
                $texte = 'un email  vous a été envoyé avec en pièce jointe le fichier rapport du dépôt ';
                $notifications = $this->get('aeag.notifications');
                $notifications->createNotification($user, $user, $em, $session, $texte);
            }
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
                    if ($pgCmdPrelev->getStation()->getOuvFoncId() == $stationId && $pgCmdPrelev->getPeriode()->getId() == $pgProgPeriode->getId()) {
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
                                if ($user->hasRole('ROLE_ADMINSQE') || $pgCmdSuiviPrel->getUser()->getPrestataire()) {
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
        //$dateActuel->add(new \DateInterval('P15D'));
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);

        if ($user->hasRole('ROLE_ADMINSQE')) {
//$pgProgWebUsers = $repoPgProgWebUsers->getPgProgWebusersByPrestataire($pgCmdPrelev->getPrestaPrel());
//$pgProgWebUser = $pgProgWebUsers[0];
            $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        } else {
            $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        }
        $pgCmdSuiviPrels = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevOrderId($pgCmdPrelev);
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
                if ($pgCmdSuiviPrel->getDatePrel() < $dateActuel || $pgCmdSuiviPrel->getDatePrel() > $dateFin) {
                    if ($pgCmdSuiviPrel->getDatePrel() < $dateActuel) {
                        $contenu = 'Avertissement : date prévisionnelle (' . $pgCmdSuiviPrel->getDatePrel()->format('d/m/Y H:i') . ') antérieure à la date actuelle (' . $dateActuel->format('d/m/Y H:i') . ')';
                    } else {
                        $contenu = 'Avertissement : date prévisionnelle (' . $pgCmdSuiviPrel->getDatePrel()->format('d/m/Y H:i') . ') postérieure à la date de fin de la période programmée (' . $dateFin->format('d/m/Y H:i') . ')';
                    }
                    $tabMessage[$nbMessages][0] = 'av';
                    $tabMessage[$nbMessages][1] = $contenu;
                    $nbMessages++;
                }
            }
            $dateDebut = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
            $dateActuel = new \DateTime();
            if ($pgCmdSuiviPrel->getStatutPrel() != 'P') {
                if ($pgCmdSuiviPrel->getDatePrel() < $dateDebut || $pgCmdSuiviPrel->getDatePrel() > $dateActuel) {
                    if (!$user->hasRole('ROLE_ADMINSQE')) {
                        $err = true;
                    }
                    if ($pgCmdSuiviPrel->getDatePrel() < $dateDebut) {
                        $contenu = 'Erreur : date de prélèvement (' . $pgCmdSuiviPrel->getDatePrel()->format('d/m/Y H:i') . ') antérieure à la date de début de la période programmée (' . $dateDebut->format('d/m/Y H:i') . ')';
                    } else {
                        $contenu = 'Erreur : date de prélèvement (' . $pgCmdSuiviPrel->getDatePrel()->format('d/m/Y H:i') . ') postérieure à la date actuelle (' . $dateActuel->format('d/m/Y H:i') . ')';
                    }
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $contenu;
                    $nbMessages++;
                }
            }

            if ($pgCmdPrelev->getCodeSupport()->getCodeSupport() != '10' && $pgCmdPrelev->getCodeSupport()->getCodeSupport() != '11') {
                $autrePgCmdPrelevs = $repoPgCmdPrelev->getAutrePrelevs($pgCmdPrelev);

                for ($i = 0; $i < count($autrePgCmdPrelevs); $i++) {
                    $autreSuport = $autrePgCmdPrelevs[$i]['codeSupport'];
                    //if (( $autreSuport == '4' && $autreSuport == '69') ||
                    //        ($pgCmdPrelev->getCodeSupport()->getCodeSupport() == '69' && $autreSuport != '4') ||
                    //        ($pgCmdPrelev->getCodeSupport()->getCodeSupport() == '4' && $autreSuport != '69')) {
                    if ((($autreSuport == '4') || ($autreSuport == '69')) && ($pgCmdPrelev->getCodeSupport()->getCodeSupport() != '69') && ($pgCmdPrelev->getCodeSupport()->getCodeSupport() != '4')) {
                        $autreDateDebut = clone($autrePgCmdPrelevs[$i]['datePrel']);
                        $autreDateDebut->sub(new \DateInterval('P4D'));
                        $autreDateFin = clone($autrePgCmdPrelevs[$i]['datePrel']);
                        $autreDateFin->add(new \DateInterval('P7D'));
                        if ($pgCmdSuiviPrel->getDatePrel() >= $autreDateDebut && $pgCmdSuiviPrel->getDatePrel() <= $autreDateFin) {
                            if (!$user->hasRole('ROLE_ADMINSQE')) {
                                $err = true;
                            }
                            $contenu = 'Date  (' . $pgCmdSuiviPrel->getDatePrel()->format('d/m/Y H:i') . ') doit être inférieure à ' . $autreDateDebut->format('d/m/Y H:i') . ' ou supérieure à  ' . $autreDateFin->format('d/m/Y H:i') . ' (autre prélèvement Onema prévu)';
                            $tabMessage[$nbMessages][0] = 'ko';
                            $tabMessage[$nbMessages][1] = $contenu;
                            $nbMessages++;
                        }
                    } else if (($autreSuport != '10') && ($autreSuport != '11')) {
                        $autreDateDebut = clone($autrePgCmdPrelevs[$i]['datePrel']);
                        $autreDateDebut->sub(new \DateInterval('P4D'));
                        $autreDateFin = clone($autrePgCmdPrelevs[$i]['datePrel']);
                        $autreDateFin->add(new \DateInterval('P4D'));
                        if ($pgCmdSuiviPrel->getDatePrel() >= $autreDateDebut && $pgCmdSuiviPrel->getDatePrel() <= $autreDateFin) {
                            $contenu = 'ATTENTION, autre prestation programmée le ' . $autrePgCmdPrelevs[$i]['datePrel']->format('d/m/Y H:i') . ' pour le support ' . $autrePgCmdPrelevs[$i]['support'];
                            ;
                            $tabMessage[$nbMessages][0] = 'av';
                            $tabMessage[$nbMessages][1] = $contenu;
                            $nbMessages++;
                        }
                    }
                }
            }



            if ($pgCmdSuiviPrel->getStatutPrel() == 'P') {
                if (!$pgCmdSuiviPrel->getCommentaire() || $pgCmdSuiviPrel->getCommentaire() == '') {
                    $err = true;
                    $contenu = 'Renseigner l’équipe et le contact (portable)  ';
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $contenu;
                    $nbMessages++;
                }
            }
            if ($pgCmdSuiviPrel->getStatutPrel() == 'N' || $pgCmdSuiviPrel->getStatutPrel() == 'R') {
                if (!$pgCmdSuiviPrel->getCommentaire() || $pgCmdSuiviPrel->getCommentaire() == '') {
                    $err = true;
                    $contenu = ' Commentaire obligatoire, indiquer pourquoi  ';
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
                    break;
                }
            }

            $nbStations = 0;
            foreach ($pgCmdSuiviPrels as $suiviPrel) {
                if ($suiviPrel->getDatePrel() == $pgCmdSuiviPrel->getDatePrel() and
                        $suiviPrel->getCommentaire() == $pgCmdSuiviPrel->getCommentaire()) {
                    $nbStations++;
                }
            }
            if ($pgCmdPrelev->getCodeSupport()->getCodeSupport() == '13' || $pgCmdPrelev->getCodeSupport()->getCodeSupport() == '11') {
                if ($nbStations > 4) {
                    $err = true;
                    $contenu = '4 stations maxi le même jour pour une même équipe' . CHR(13) . CHR(10);
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $contenu;
                    $nbMessages++;
                }
            }
            if ($pgCmdPrelev->getCodeSupport()->getCodeSupport() == '10') {
                if ($nbStations > 6) {
                    $err = true;
                    $contenu = '6 stations maxi le même jour pour une même équipe' . CHR(13) . CHR(10);
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $contenu;
                    $nbMessages++;
                }
            }

            if ($pgCmdSuiviPrelActuel) {
                if ($pgCmdSuiviPrelActuel->getStatutPrel() == 'E' && $pgCmdSuiviPrel->getStatutPrel() == 'P') {
                    $err = true;
                    $contenu = 'Le statut ne peut être à \'Prévisionnel\' ' . CHR(13) . CHR(10);
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $contenu;
                    $nbMessages++;
                }
                if ($pgCmdSuiviPrelActuel->getStatutPrel() == 'P' && $pgCmdSuiviPrel->getStatutPrel() == 'P') {
                    if ($pgCmdSuiviPrelActuel->getdatePrel() != $pgCmdSuiviPrel->getDatePrel()) {
                        $contenu = ' Avertissement : modification de la date de prélevement ';
                        $tabMessage[$nbMessages][0] = 'av';
                        $tabMessage[$nbMessages][1] = $contenu;
                        $nbMessages++;
                    }
                }
                if ($pgCmdSuiviPrelActuel->getStatutPrel() == 'P' && $pgCmdSuiviPrel->getStatutPrel() == 'E') {
                    if ($pgCmdSuiviPrelActuel->getdatePrel() != $pgCmdSuiviPrel->getDatePrel() && (!$pgCmdSuiviPrel->getCommentaire() || $pgCmdSuiviPrel->getCommentaire() == '')) {
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
                if ($pgCmdSuiviPrel->getStatutPrel() == 'F' && $pgCmdSuiviPrel->getValidation() == 'A') {
                    $pgCmdPrelev->setDatePrelev($datePrel);
                    $pgCmdPrelev->setRealise('O');
                } elseif ($pgCmdSuiviPrel->getStatutPrel() == 'N' || $pgCmdSuiviPrel->getStatutPrel() == 'R') {
                    $pgCmdPrelev->setDatePrelev($datePrel);
                    $pgCmdPrelev->setRealise('N');
                } else {
                    $pgCmdPrelev->setDatePrelev($pgCmdPrelev->getDemande()->getDateDemande());
                    $pgCmdPrelev->setRealise(null);
                }
                $emSqe->persist($pgCmdPrelev);
                $emSqe->flush();
                $session->getFlashBag()->add('notice-success', 'le suivi du ' . $datePrel->format('d/m/Y') . ' a été créé sur la station : ' . $pgCmdPrelev->getStation()->getCode() . ' !');
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

    public function lotPeriodeStationDemandeSuiviMajAction($suiviPrelId = null, $periodeAnId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviMaj');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
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

        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $pgCmdSuiviPrels = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevOrderId($pgCmdPrelev);
        if ($pgCmdSuiviPrels) {
            $pgCmdSuiviPrelActuel = $pgCmdSuiviPrels[0];
        } else {
            $pgCmdSuiviPrelActuel = null;
        }
        $form = $this->createForm(new PgCmdSuiviPrelMajType($user, $pgCmdSuiviPrelActuel), $pgCmdSuiviPrel);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $datePrel = $pgCmdSuiviPrel->getDatePrel();
            $commentaireAvis = $pgCmdSuiviPrel->getCommentaireAvis();
            if ($commentaireAvis) {
                if (substr($commentaireAvis, 0, 8) != 'Déposé') {
                    $date = date("d/m/Y");
                    $heure = date("H:i");
                    $commentaireAvisNew = 'Déposé le ' . $date . ' à ' . $heure . ' par ' . $pgProgWebUser->getnom();
                    $commentaireAvisNew .= ' : ' . CHR(13) . CHR(10);
                    $commentaireAvisNew .= '      ' . $commentaireAvis;
                    $pgCmdSuiviPrel->setCommentaireAvis($commentaireAvisNew);
                }
            }

            if (($pgCmdSuiviPrel->getStatutPrel() == 'F' || $pgCmdSuiviPrel->getStatutPrel() == 'D' || $pgCmdSuiviPrel->getStatutPrel() == 'DF' || $pgCmdSuiviPrel->getStatutPrel() == 'DO') && $pgCmdSuiviPrel->getValidation() == 'A') {
                $pgCmdPrelev->setDatePrelev($datePrel);
                $pgCmdPrelev->setRealise('O');
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            } elseif ($pgCmdSuiviPrel->getStatutPrel() == 'N' || $pgCmdSuiviPrel->getStatutPrel() == 'R') {
                $pgCmdPrelev->setDatePrelev($datePrel);
                $pgCmdPrelev->setRealise('N');
            } else {
//$pgCmdPrelev->setDatePrelev(null);
                $pgCmdPrelev->setRealise(null);
            }
            if ($pgCmdSuiviPrel->getValidation() == 'F') {
                $pgCmdPrelev->setDatePrelev($datePrel);
                $pgCmdPrelev->setRealise('N');
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M60');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            }

            if ($pgCmdSuiviPrel->getValidation() == 'R') {
                $pgCmdPrelev->setRealise('N');
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M10');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            }

            $emSqe->persist($pgCmdSuiviPrel);
            $emSqe->persist($pgCmdPrelev);
            $emSqe->flush();
            $session->getFlashBag()->add('notice-success', 'le suivi du ' . $datePrel->format('d/m/Y') . ' a été modifié sur la station : ' . $pgCmdPrelev->getStation()->getCode() . ' !');

            if ($pgCmdSuiviPrel->getStatutPrel() != 'D') {
                return $this->redirect($this->generateUrl('AeagSqeBundle_suiviHydrobio_lot_periode_stations', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                    'periodeAnId' => $periodeAnId)));
            } else {
                return $this->redirect($this->generateUrl('AeagSqeBundle_depotHydrobio_prelevements', array('demandeId' => $pgCmdPrelev->getDemande()->getId(),
                                    'periodeAnId' => $periodeAnId)));
            }
        }

        return $this->render('AeagSqeBundle:SuiviHydrobio:lotPeriodeStationDemandeSuiviMaj.html.twig', array(
                    'prelev' => $pgCmdPrelev,
                    'periodeAnId' => $periodeAnId,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'dateFin' => $dateFin,
                    'suiviPrel' => $pgCmdSuiviPrel,
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

    public function lotPeriodeStationDemandeSuiviDeposerAction($stationId = null, $suiviPrelId = null, $periodeAnId = null, Request $request) {

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
                    'stationId' => $stationId,
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
        $repoPgCmdDwnldUsrRps = $emSqe->getRepository('AeagSqeBundle:PgCmdDwnldUsrRps');

        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $datePrel = $pgCmdSuiviPrel->getDatePrel();
        if ($pgCmdSuiviPrel->getFichierRps()) {
            $pgCmdFichiersRps = $pgCmdSuiviPrel->getFichierRps();
            $dossier = $this->getCheminEchange($pgCmdSuiviPrel);
            if (is_dir($dossier)) {
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
            }
// On supprime l'enregistrement  $pgCmdFichiersRps
            $pgCmdSuiviPrel->setFichierRps(null);
            $emSqe->persist($pgCmdSuiviPrel);
            $pgCmdDwnldUsrRps = $repoPgCmdDwnldUsrRps->getPgCmdDwnldUsrRpsByFichierReponse($pgCmdFichiersRps);
            foreach ($pgCmdDwnldUsrRps as $pgCmdDwnldUsrRp) {
                $emSqe->remove($pgCmdDwnldUsrRp);
            }
            $emSqe->remove($pgCmdFichiersRps);
        }
        $emSqe->remove($pgCmdSuiviPrel);
        $emSqe->flush();

        $session->getFlashBag()->add('notice-success', 'le suivi du prélèvement du   : ' . $datePrel->format('d/m/Y') . ' a été supprimé sur la station : ' . $pgCmdPrelev->getStation()->getCode() . ' !');

//        return $this->redirect($this->generateUrl('AeagSqeBundle_suiviHydrobio_lot_periode_stations', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
//                            'periodeAnId' => $periodeAnId)));
//          \Symfony\Component\VarDumper\VarDumper::dump($tabDemande);
        return new Response('');
    }

    public function lotPeriodeStationDemandeSuiviFichierDeposerAction($stationId = null, $suiviPrelId = null, $periodeAnId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviFichierDeposer');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgWebuserZgeoref = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserZgeoref');
        $repoPgCmdDwnldUsrRps = $emSqe->getRepository('AeagSqeBundle:PgCmdDwnldUsrRps');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdFichiersRps = $pgCmdSuiviPrel->getFichierRps();
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $pgCmdDemande = $pgCmdPrelev->getDemande();
        $pgProgLotAn = $pgCmdDemande->getLotan();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R10');
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgPeriode = $pgProgLotPeriodeAn->getPeriode();
        if ($pgProgLot->getDelaiPrel()) {
            $dateFin = clone($pgProgPeriode->getDateDeb());
            $delai = $pgProgLot->getDelaiPrel();
            $dateFin->add(new \DateInterval('P' . $delai . 'D'));
        } else {
            $dateFin = $pgProgPeriode->getDateFin();
        }

// Récupération des valeurs du fichier

        $response = null;
        $tabMessage = array();
        $nbMessages = 0;
        $tabRapport = array();
        $nbRapport = 0;
        $nbCorrect = 0;
        $nbIncorrect = 0;
        $valid = true;
        $name = 'inconu';

        $dateDepot = new \DateTime();
        $pathBase = '/base/extranet/Transfert/Sqe/csv-' . $dateDepot->format('Y-m-d-H-i-s');
        $pathRapport = '/base/extranet/Transfert/Sqe/csv';
        $ficRapport = $user->getId() . '_' . $dateDepot->format('Y-m-d-H') . '_rapport.csv';
        if (!is_dir($pathBase)) {
            if (!mkdir($pathBase, 0777, true)) {
                $session->getFlashBag()->add('notice-error', 'Le répertoire : ' . $pathBase . ' n\'a pas pu être créé');
                ;
            }
        }

        if (!file_exists($_FILES['file']['tmp_name'])) {
            $response = 'Aucun fichier sélectionné.';
            $tabMessage[$nbMessages][0] = 'ko';
            $tabMessage[$nbMessages][1] = $response;
            $nbMessages++;
            $valid = false;
        } else {

            $name = $_FILES['file']['name'];
            $tmpName = $_FILES['file']['tmp_name'];
            $error = $_FILES['file']['error'];
            $size = $_FILES['file']['size'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            switch ($error) {
                case UPLOAD_ERR_OK:
                    $valid = true;
                    if (!in_array($ext, array('zip'))) {
                        $valid = false;
                        $response = 'extension du fichier incorrecte.';
                        $tabMessage[$nbMessages][0] = 'ko';
                        $tabMessage[$nbMessages][1] = $response;
                        $nbMessages++;
                    }
//validate file size
                    if ($size > 335544320) {
                        $valid = false;
                        $response = 'La taille du fichier (' . $size / 1024 . ') est plus grande que la taille autorisée.';
                        $tabMessage[$nbMessages][0] = 'ko';
                        $tabMessage[$nbMessages][1] = $response;
                        $nbMessages++;
                    }
//upload file
                    if ($valid) {

// Enregistrement du fichier sur le serveur
                        move_uploaded_file($_FILES['file']['tmp_name'], $pathBase . '/' . $name);
                        $response = $name . ' déposé le ' . $dateDepot->format('d/m/Y');
                        break;
                    }
                case UPLOAD_ERR_INI_SIZE:
                    $response = 'La taille (' . $size . ' octets' . ') du fichier téléchargé excède la taille de upload_max_filesize dans php.ini.';
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $response;
                    $nbMessages++;
                    $valid = false;
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $response = 'La taille (' . $size . ') du fichier téléchargé excède la taille de MAX_FILE_SIZE qui a été spécifié dans le formulaire HTML.';
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $response;
                    $nbMessages++;
                    $valid = false;
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $response = 'Le fichier n\'a été que partiellement téléchargé.';
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $response;
                    $nbMessages++;
                    $valid = false;
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $response = 'Aucun fichier sélectionné.';
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $response;
                    $nbMessages++;
                    $valid = false;
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $response = 'Manquantes dans un dossier temporaire. Introduit en PHP 4.3.10 et PHP 5.0.3.';
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $response;
                    $nbMessages++;
                    $valid = false;
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $response = 'Impossible d\'écrire le fichier sur le disque. Introduit en PHP 5.1.0.';
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $response;
                    $nbMessages++;
                    $valid = false;
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $response = 'Le téléchargement du fichier arrêté par extension. Introduit en PHP 5.2.0.';
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $response;
                    $nbMessages++;
                    $valid = false;
                    break;
                default:
                    $response = 'erreur inconnue';
                    $tabMessage[$nbMessages][0] = 'ko';
                    $tabMessage[$nbMessages][1] = $response;
                    $nbMessages++;
                    $valid = false;
                    break;
            }
        }

        if ($valid) {
            $liste = array();
            $liste = $this->unzip($pathBase . '/' . $name, $pathBase . '/');
            $rapport = fopen($pathRapport . '/' . $user->getId() . '_' . $dateDepot->format('Y-m-d-H') . '_rapport.csv', "w+");
            $contenu = '                                  Rapport d\'intégration du fichier : ' . $name . ' déposé le ' . $dateDepot->format('d/m/Y') . CHR(13) . CHR(10) . CHR(13) . CHR(10);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            $tabRapport[$nbRapport] = '<h4><div class="text-center">Rapport d\'intégration du fichier : ' . $name . ' déposé le ' . $dateDepot->format('d/m/Y') . '</div></h4>';
            $nbRapport++;
            fputs($rapport, $contenu);
            $contenu = 'Le fichier zip contient  ' . count($liste) . ' fichier(s)' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            $tabRapport[$nbRapport] = 'Le fichier zip contient  ' . count($liste) . ' fichier(s)</br>';
            $nbRapport++;
            fputs($rapport, $contenu);

            $erreur = 0;
            $tabFichiers = array();
            $nbFichier = 0;
            foreach ($liste as $nomFichier) {
                $tabFichiers[$nbFichier] = $nomFichier;
                $nbFichier++;
                $nbCorrect++;
            }

            if (count($tabFichiers) > 0) {
                if (count($tabFichiers) < 3) {
                    $contenu = 'La station  ' . $pgRefStationMesure->getCode() . ' doit regrouper au moins 3 fichiers ' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                    $erreur = 1;
                    $nbCorrect = $nbCorrect - count($tabFichiers);
                    $nbIncorrect = $nbIncorrect + count($tabFichiers);
                } elseif (count($tabFichiers) > 0) {
                    $NbFt = 0;
                    $NbPhoto = 0;
                    for ($nb = 0; $nb < count($tabFichiers); $nb++) {
                        if (strpos($tabFichiers[$nb], $pgRefStationMesure->getCode()) === false) {
                            if (filesize($pathBase . '/' . $tabFichiers[$nb]) > 0) {
                                $contenu = 'pas de station ' . $pgRefStationMesure->getCode() . ' à raccorder au fichier ' . $tabFichiers[$nb] . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                                $erreur = 1;
                                $nbCorrect = $nbCorrect - 1;
                                $nbIncorrect = $nbIncorrect + 1;
                            }
                        }
//$tabNomFichier = explode('-', $tabFichiers[$nb]);
//if ($tabNomFichier[1] != 'ft' && $tabNomFichier[1] != 'photo1' && $tabNomFichier[1] != 'photo2') {
                        if ((strpos(strtoupper($tabFichiers[$nb]), 'FT') === false) && (strpos(strtoupper($tabFichiers[$nb]), 'PHOTO') === false)) {
                            $contenu = 'La station  ' . $pgRefStationMesure->getCode() . ' ne peut pas regrouper  le fichier : ' . $tabFichiers[$nb] . ' (non reconnu comme photo ni fiche terrain)' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                            $erreur = 1;
                            $nbCorrect = $nbCorrect - 1;
                            $nbIncorrect = $nbIncorrect + 1;
                        } else {
                            if (strpos(strtoupper($tabFichiers[$nb]), 'FT') !== false) {
                                $NbFt++;
                            }
                            if (strpos(strtoupper($tabFichiers[$nb]), 'PHOTO') !== false) {
                                $NbPhoto++;
                            }
                        }
                    }
                    if ($NbFt < 1 || $NbPhoto < 2) {
                        $contenu = 'La station  ' . $pgRefStationMesure->getCode() . ' doit  regrouper  au moins un fichier dont le nom contient \'ft\' et 2 fichiers dont le nom contient \'photo\'.' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                        $erreur = 1;
                    }
                }
            }
            if ($erreur == 0) {
                $tabSupport = array();
                $nbSupport = 0;
                if (count($tabFichiers) > 0) {
                    if (count($tabFichiers) > 1) {
                        $fichier_archive = true;
                        $nb_archive = count($tabFichiers);
                        $files = array();
                        $zip = new \ZipArchive();
                        $zipName = $pgRefStationMesure->getCode() . "-archive.zip";
                        $contenu = 'le fichier  ' . $zipName . ' regroupe ' . count($tabFichiers) . ' fichiers : ' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                        for ($nb = 0; $nb < count($tabFichiers); $nb++) {
                            array_push($files, $pathBase . '/' . $tabFichiers[$nb]);
                            $contenu = '                  -  ' . $tabFichiers[$nb] . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                        $zip->open($pathBase . '/' . $zipName, \ZipArchive::CREATE);
                        foreach ($files as $f) {
                            $zip->addFromString(basename($f), file_get_contents($f));
                        }
                        $zip->close();
                        $name = $zipName;
                    } else {
                        $fichier_archive = false;
                        $name = $tabFichiers[0];
                    }

                    // Enregistrement des valeurs en base
                    $trouve = false;
                    if (count($tabSupport) > 0) {
                        for ($nbSupport = 0; $nbSupport < count($tabSupport); $nbSupport++) {
                            if ($tabSupport[$nbSupport] == $pgCmdPrelev->getCodeSupport()->getCodeSupport()) {
                                $trouve = true;
                                break;
                            }
                        }
                    }
                    if (!$trouve) {
                        $nbSupport = count($tabSupport);
                        $tabSupport[$nbSupport] = $pgCmdPrelev->getCodeSupport()->getCodeSupport();
                    }
                    if (($pgCmdSuiviPrel->getStatutPrel() == 'N') || ( $pgCmdSuiviPrel->getStatutPrel() == 'F') || ( $pgCmdSuiviPrel->getStatutPrel() == 'R')) {
                        if ($pgCmdSuiviPrel->getFichierRps()) {
                            $pgCmdFichiersRps = $pgCmdSuiviPrel->getFichierRps();
                            $pgCmdDwnldUsrRpss = $repoPgCmdDwnldUsrRps->getPgCmdDwnldUsrRpsByFichierReponse($pgCmdFichiersRps);
                            foreach ($pgCmdDwnldUsrRpss as $pgCmdDwnldUsrRps) {
                                $emSqe->remove($pgCmdDwnldUsrRps);
                            }
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
                        $pathBaseFic = $this->getCheminEchange($pgCmdSuiviPrel, $pgCmdFichiersRps->getId());
                        if (!is_dir($pathBaseFic)) {
                            if (!mkdir($pathBaseFic, 0777, true)) {
                                $session->getFlashBag()->add('notice-error', 'Le répertoire : ' . $pathBaseFic . ' n\'a pas pu être créé');
                                ;
                            }
                        }
//                        $contenu = 'Le fichier ' . $pathBase . '/' . $name . ' a été déposé vers  ' . $pathBaseFic . '/' . $name . CHR(13) . CHR(10) . CHR(13) . CHR(10);
//                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
//                        fputs($rapport, $contenu);
                        copy($pathBase . '/' . $name, $pathBaseFic . '/' . $name);
                        // unlink($pathBase . '/' . $name);
                        $contenu = 'Le fichier ' . $name . ' a été déposé sur la station ' . $pgRefStationMesure->getCode() . ' ' . $pgRefStationMesure->getLibelle() . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } else {
                        $contenu = 'Association impossible : le dernier suivi de la station  ' . $pgRefStationMesure->getCode() . ' doit être "Effectué" ou "Non effectué".' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                        if ($fichier_archive) {
                            $nbCorrect = $nbCorrect - $nb_archive;
                            $nbIncorrect = $nbIncorrect + $nb_archive;
                        } else {
                            $nbCorrect = $nbCorrect - 1;
                            $nbIncorrect = $nbIncorrect + 1;
                        }
                    }
                } else {
                    if ($pgCmdSuiviPrel->getStatutPrel() == 'F' && !$pgCmdSuiviPrel->getfichierRps()) {
                        $contenu = 'Attention : le dernier suivi de la station  ' . $pgRefStationMesure->getCode() . ' a le statut : "Effectué" et il n\'y a pas de fichier terrain associé.' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }
                }
            } else {
                $contenu = 'Au moins une erreur rencontrée. Aucun fichier intégré' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }
            fclose($rapport);
        } else {
            $erreur = 1;
        }


        if ($erreur == 0) {
            $objetMessage = "fichier terrrain déposé ";
            $txtMessage = "Un ou plusieurs fichiers terrain ont été déposés sur le lot " . $pgProgLot->getNomLot() . " pour la période du " . $pgProgPeriode->getDateDeb()->format('d/m/Y') . " au " . $dateFin->format('d/m/Y');
            $mailer = $this->get('mailer');
            // envoi mail  aux XHBIO
            $pgProgWebusers = $repoPgProgWebUsers->getPgProgWebusersByTypeUser('XHBIO');
            foreach ($pgProgWebusers as $destinataire) {
                if ($destinataire->getCodeSupport()) {
                    $trouve = false;
                    if (count($tabSupport) > 0) {
                        for ($nbSupport = 0; $nbSupport < count($tabSupport); $nbSupport++) {
                            if ($tabSupport[$nbSupport] == $destinataire->getCodeSupport()) {
                                $trouve = true;
                                break;
                            }
                        }
                    }
                } else {
                    $trouve = true;
                }
                if ($trouve) {
                    $trouve = false;
                    $pgProgWebuserZgeorefs = $repoPgProgWebuserZgeoref->getPgProgWebuserZgeorefByWebuser($destinataire);
                    foreach ($pgProgWebuserZgeorefs as $pgProgWebuserZgeoref) {
                        if ($pgProgWebuserZgeoref->getZgeoref()->getId() == $pgProgLot->getZgeoref()->getId()) {
                            $trouve = true;
                        }
                    }
                    if ($trouve) {
                        // Envoi d'un mail
                        if ($this->get('aeag_sqe.message')->envoiMessage($emSqe, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                            $texte = 'un email  vous a été envoyé par ' . $pgProgWebUser->getNom() . ' suite à l\'intégration de plusieurs fichiers de terrain ' . CHR(13) . CHR(10) . ' sur le lot ' . $pgProgLot->getNomLot() . ' pour la période du ' . $pgProgPeriode->getDateDeb()->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y');
                            $notifications = $this->get('aeag.notifications');
                            $userDest = $repoUsers->getUserById($destinataire->getExtId());
                            $notifications->createNotification($user, $userDest, $em, $session, $texte);
                        } else {
                            $session->getFlashBag()->add('notice-warning', 'Le dépôt a été traité, mais l\'email n\'a pas pu être envoyé à ' . $destinataire->getNom());
                        }
                    }
                }
            }
            // envoi mail  aux presta connecte
            $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
            if ($pgProgWebUser) {
                $txtMessage .= '<br/><br/>Veullez trouver en pièce jointe le rapport d\'intégration';
                $htmlMessage = "<html><head></head><body>";
                $htmlMessage .= "Bonjour, <br/><br/>";
                $htmlMessage .= $txtMessage;
                $htmlMessage .= "<br/><br/>Cordialement, <br/>L'équipe SQE";
                $htmlMessage .= "</body></html>";
                $mail = \Swift_Message::newInstance('Wonderful Subject')
                        ->setSubject($objetMessage)
                        ->setFrom('automate@eau-adour-garonne.fr')
                        ->setTo($pgProgWebUser->getMail())
                        ->setBody($htmlMessage, 'text/html');

                $mail->attach(\Swift_Attachment::fromPath($pathRapport . '/' . $ficRapport));
                $mailer->send($mail);
                $texte = 'un email  vous a été envoyé avec en pièce jointe le fichier rapport du dépôt ';
                $notifications = $this->get('aeag.notifications');
                $notifications->createNotification($user, $user, $em, $session, $texte);
            }
        }

        $tabRapport[$nbRapport] = "Nombre de fichiers intégrés : " . $nbCorrect;
        $nbRapport++;
        $tabRapport[$nbRapport] = "Nombre de fichiers incorrects : " . $nbIncorrect;
        $nbRapport++;
        $tabRapport[$nbRapport] = "</br><h5><div class='text-center'>Voir le rapport d'integration </div></h5>";
        if ($nbIncorrect == 0 && $valid) {
            $this->rmAllDir($pathBase);
        }

        $tabReponse = array();
        $tabReponse[0] = $name;
        $tabReponse[1] = 'rapport_' . $name;
        $tabReponse[2] = $tabMessage;
        $tabReponse[3] = $tabRapport;
        $tabReponse[4] = $ficRapport;

//         \Symfony\Component\VarDumper\VarDumper::dump($tabReponse);
//          return new Response ('');
//$session->getFlashBag()->add('notice-warning', $response);

        return new Response(json_encode($tabReponse));
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
        $repoPgCmdDwnldUsrRps = $emSqe->getRepository('AeagSqeBundle:PgCmdDwnldUsrRps');

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
        $pgCmdDwnldUsrRps = $repoPgCmdDwnldUsrRps->getPgCmdDwnldUsrRpsByFichierReponse($pgCmdFichiersRps);
        foreach ($pgCmdDwnldUsrRps as $pgCmdDwnldUsrRp) {
            $emSqe->remove($pgCmdDwnldUsrRp);
        }
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
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $pgCmdFichiersRps = $pgCmdSuiviPrel->getFichierRps();
        $chemin = $this->getCheminEchange($pgCmdSuiviPrel);
        $fichier = $pgCmdFichiersRps->getNomFichier();
        $ext = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));

        $pgCmdDwnldUsrRps = new PgCmdDwnldUsrRps();
        $pgCmdDwnldUsrRps->setUser($pgProgWebUser);
        $pgCmdDwnldUsrRps->setFichierReponse($pgCmdFichiersRps);
        $pgCmdDwnldUsrRps->setDate(new \DateTime());
        $pgCmdDwnldUsrRps->setTypeFichier($pgCmdFichiersRps->getTypeFichier());
        $emSqe->persist($pgCmdDwnldUsrRps);
        $emSqe->flush();

        header('Content-Type', 'application/' . $ext);
        header('Content-disposition: attachment; filename="' . $fichier . '"');
        header('Content-Length: ' . filesize($chemin . '/' . $fichier));
        readfile($chemin . '/' . $fichier);
        exit();
    }

    public function lotPeriodeStationDemandeSuiviFichierListeAction($suiviPrelId = null) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviFichierListe');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $pgRefStationMesure = $pgCmdPrelev->getStation();
        $pgCmdFichiersRps = $pgCmdSuiviPrel->getFichierRps();
        $pathBase = $this->getCheminEchange($pgCmdSuiviPrel);
        $fichierZip = $pgCmdFichiersRps->getNomFichier();
        $ext = strtolower(pathinfo($fichierZip, PATHINFO_EXTENSION));


        if ($ext != 'zip') {
            return $this->redirect($this->generateUrl('AeagSqeBundle_suiviHydrobio_lot_periode_station_demande_suivi_fichier_telecharger', array('suiviPrelId' => $suiviPrelId)));
        }


        if ($pgProgWebUser) {
            $pgCmdDwnldUsrRps = new PgCmdDwnldUsrRps();
            $pgCmdDwnldUsrRps->setUser($pgProgWebUser);
            $pgCmdDwnldUsrRps->setFichierReponse($pgCmdFichiersRps);
            $pgCmdDwnldUsrRps->setDate(new \DateTime());
            $pgCmdDwnldUsrRps->setTypeFichier($pgCmdFichiersRps->getTypeFichier());
            $emSqe->persist($pgCmdDwnldUsrRps);
            $emSqe->flush();
        }

        $chemin = $pathBase . '/' . $fichierZip;
        //print_r('chemin : ' . $chemin);
        $fichiers = $this->unzip($chemin, $pathBase . '/');
        $tabFichiers = array();
        $i = 0;
        foreach ($fichiers as $fichier) {
            $ext = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));
            if ($ext) {
                if ($pgRefStationMesure->getCode() == substr($fichier, 0, strlen($pgRefStationMesure->getCode()))) {
                    $tabFichiers[$i]['fichier'] = $fichier;
                    $tabFichiers[$i]['chemin'] = $pathBase . '/' . $fichier;
                    chmod($pathBase . '/' . $fichier, 0775);
                    chown($pathBase . '/' . $fichier, 'www-data');
                    $repertoire = "fichiers";
                    rename($pathBase . '/' . $fichier, $repertoire . "/" . $fichier);
                    $i++;
                }
                if ($ext == 'prn') {
                    $ficStation = $pgRefStationMesure->getCode() . '_prn.csv';
                    $ficSortie = fopen($pathBase . '/' . $ficStation, "w+");
                    $nomFichier = fopen($pathBase . '/' . $fichier, "r");
                    $codeStation = null;
                    while (!feof($nomFichier)) {
                        $ligne = fgets($nomFichier, 1024);
                        if (strlen($ligne) > 0) {
                            $tab = explode("\t", $ligne);
                            if (strlen($tab[0]) > 0) {
                                $tab1 = explode('*', $tab[0]);
                                if ($pgRefStationMesure->getCode() == $tab1[5]) {
//                                    $contenu = $ligne . CHR(13) . CHR(10);
//                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($ficSortie, $ligne);
                                    $codeStation = $tab1[5];
                                }
                            } else {
                                if ($codeStation == $pgRefStationMesure->getCode()) {
                                    $contenu = $ligne . CHR(13) . CHR(10);
                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($ficSortie, $contenu);
                                }
                            }
                        }
                    }
                    fclose($ficSortie);
                    $tabFichiers[$i]['fichier'] = $ficStation;
                    $tabFichiers[$i]['chemin'] = $pathBase . '/' . $ficStation;
                    chmod($pathBase . '/' . $ficStation, 0775);
                    chown($pathBase . '/' . $ficStation, 'www-data');
                    $repertoire = "fichiers";
                    rename($pathBase . '/' . $ficStation, $repertoire . "/" . $ficStation);
                    $i++;
                }
            }
        }
        return $this->render('AeagSqeBundle:SuiviHydrobio:lotPeriodeStationDemandeSuiviFichierListe.html.twig', array(
                    'suiviPrel' => $pgCmdSuiviPrel,
                    'repertoire' => $pathBase,
                    'fichier' => $fichierZip,
                    'chemin' => $chemin,
                    'fichiers' => $tabFichiers));
    }

    public function prelevSuiviPrelsAction($prelevId = null) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }

        $session = $this->get('session');
        $session->set('menu', 'syntheseHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'prelevSuiviPrels');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');

        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdSuiviPrels = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevOrderId($pgCmdPrelev);
        $tabCmdPrelevs = array();
        $tabCmdPrelevs['cmdPrelev'] = $pgCmdPrelev;
        $tabCmdPrelevs['suiviPrels'] = $pgCmdSuiviPrels;

//         \Symfony\Component\VarDumper\VarDumper::dump($tabCmdPrelevs);
//        return new Response('');

        return $this->render('AeagSqeBundle:SuiviHydrobio:prelevSuiviPrels.html.twig', array('cmdPrelev' => $tabCmdPrelevs));
    }

    public function syntheseAction() {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }

        $session = $this->get('session');
        $session->set('menu', 'syntheseHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'synthese');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgSandreSupport = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');

        $pgSandreSupports = array();
        $pgSandreSupport = null;

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());

        if ($pgProgWebUser->getTypeUser() == 'XHBIO') {
            $pgSandreSupport = $repoPgSandreSupport->getPgSandreSupportsByCodeSupport($pgProgWebUser->getCodeSupport());
        }
        if ($pgSandreSupport) {
            $pgSandreSupports[0] = $pgSandreSupport;
        } else {
            $pgSandreSupports = $repoPgSandreSupport->getPgSandreSupports();
        }

        return $this->render('AeagSqeBundle:SuiviHydrobio:synthese.html.twig', array('supports' => $pgSandreSupports));
    }

    public function syntheseCriteresAction($codeSupport = null) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }

        $session = $this->get('session');
        $session->set('menu', 'syntheseHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'syntheseCriteres');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgSandreSupport = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgSandreSupport = $repoPgSandreSupport->getPgSandreSupportsByCodeSupport($codeSupport);

        $tabPrestataires = $repoPgCmdPrelev->getPrestataireBySupport($pgSandreSupport);


//         \Symfony\Component\VarDumper\VarDumper::dump($tabPrestataires);
//        return new Response('');

        return $this->render('AeagSqeBundle:SuiviHydrobio:syntheseCriteres.html.twig', array(
                    'prestataires' => $tabPrestataires,
                    'support' => $pgSandreSupport,));
    }

    public function syntheseFiltresAction($codeSupport = null) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }

        $session = $this->get('session');
        $session->set('menu', 'syntheseHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'syntheseFiltres');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgWebuserZgeoref = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserZgeoref');
        $repoPgProgZgeorefStation = $emSqe->getRepository('AeagSqeBundle:PgProgZgeorefStation');
        $repoPgSandreSupport = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgSandreSupport = $repoPgSandreSupport->getPgSandreSupportsByCodeSupport($codeSupport);

        if (isset($_POST['prestataire'])) {
            $presta = $_POST['prestataire'];
        } else {
            $presta = null;
        }
        if (isset($_POST['avis'])) {
            $avis = $_POST['avis'];
        } else {
            $avis = null;
        }
        if (isset($_POST['statut'])) {
            $statut = $_POST['statut'];
        } else {
            $statut = null;
        }
        if (isset($_POST['validation'])) {
            $validation = $_POST['validation'];
        } else {
            $validation = null;
        }

        if ($presta) {
            $pgRefCorresPresta = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($presta);
            $session->set('suiviHydrobio_prestataire', $pgRefCorresPresta->getNomCorres());
        } else {
            $session->remove('suiviHydrobio_prestataire');
        }
        if ($avis) {
            if ($avis == 'F') {
                $session->set('suiviHydrobio_avis', 'Favorable');
            }
            if ($avis == 'D') {
                $session->set('suiviHydrobio_avis', 'Défavorable');
            }
        } else {
            $session->remove('suiviHydrobio_avis');
        }
        if ($statut) {
            if ($statut == 'P') {
                $session->set('suiviHydrobio_statut', 'Prévisionnel');
            }
            if ($statut == 'F') {
                $session->set('suiviHydrobio_statut', 'Effectué');
            }
            if ($statut == 'N') {
                $session->set('suiviHydrobio_statut', 'Non effectué');
            }
        } else {
            $session->remove('suiviHydrobio_statut');
        }
        if ($validation) {
            if ($validation == 'A') {
                $session->set('suiviHydrobio_validation', 'Accepté');
            }
            if ($validation == 'R') {
                $session->set('suiviHydrobio_validation', 'Refusé');
            }
            if ($validation == 'E') {
                $session->set('suiviHydrobio_validation', 'En attente');
            }
        } else {
            $session->remove('suiviHydrobio_validation');
        }

        $tabResultats = $repoPgCmdPrelev->getPgCmdPrelevBySupportPrestaAvisStatutValidation($pgSandreSupport, $presta, $avis, $statut, $validation);
        $tabStations = array();
        $i = 0;
        for ($nbStations = 0; $nbStations < count($tabResultats); $nbStations++) {
            $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($tabResultats[$nbStations]['prelevId']);
            $pgCmdSuiviPrelDernier = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($tabResultats[$nbStations]['suiviPrelId']);
            $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($tabResultats[$nbStations]['ouvFoncId']);
            if (!$user->hasRole('ROLE_ADMINSQE')) {
                $pgProgWebuserZgeorefs = $repoPgProgWebuserZgeoref->getPgProgWebuserZgeorefByWebuser($pgProgWebUser);
                $trouve = false;
                foreach ($pgProgWebuserZgeorefs as $pgProgWebuserZgeoref) {
                    $pgProgZoneGeoref = $pgProgWebuserZgeoref->getZgeoref();
                    $pgProgZgeorefStations = $repoPgProgZgeorefStation->getpgProgZgeorefStationByZgeoref($pgProgZoneGeoref);
                    foreach ($pgProgZgeorefStations as $pgProgZgeorefStation) {
                        if ($pgRefStationMesure->getOuvFoncId() == $pgProgZgeorefStation->getStationMesure()->getOuvFoncId()) {
                            $trouve = true;
                            break;
                        }
                    }
                    if ($trouve) {
                        break;
                    }
                }
            } else {
                $trouve = true;
            }
            if ($trouve) {
                if ($pgCmdSuiviPrelDernier) {
                    $tabStations[$i]['station']['ouvFoncId'] = $pgRefStationMesure->getOuvFoncId();
                    $tabStations[$i]['station']['code'] = $pgRefStationMesure->getCode();
                    $tabStations[$i]['station']['libelle'] = $pgRefStationMesure->getLibelle();
                    $tabStations[$i]['station']['dept'] = substr($pgRefStationMesure->getInseeCommune(), 0, 2);
                    $tabStations[$i]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $pgRefStationMesure->getCode()) . '.pdf';
                    $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgCmdPrelev->getDemande()->getLotan(), $pgRefStationMesure);
                    $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
                    if ($pgRefReseauMesure) {
                        $tabStations[$i]['reseau'] = $pgRefReseauMesure->getnomRsx();
                    } else {
                        $tabStations[$i]['reseau'] = null;
                    }
                    $tabStations[$i]['cmdDemande'] = $pgCmdPrelev->getDemande();
                    $dateLimite = null;
                    if ($pgCmdSuiviPrelDernier->getFichierRps()) {
                        if ($pgCmdSuiviPrelDernier->getFichierRps()->getDateDepot()) {
                            $dateDepot = $pgCmdSuiviPrelDernier->getFichierRps()->getDateDepot();
                            $delai = 21;
                            $dateLimite = $dateDepot->add(new \DateInterval('P' . $delai . 'D'));
                        }
                    }
                    $tabStations[$i]['dateLimite'] = $dateLimite;
                    $tabStations[$i]['cmdPrelev'] = $pgCmdPrelev;
                    $tabStations[$i]['suiviPrel'] = $pgCmdSuiviPrelDernier;
                    $i++;
                }
            }
//                }
//            }
        }

//         \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response('');

        return $this->render('AeagSqeBundle:SuiviHydrobio:syntheseSupport.html.twig', array('pgProgWebUser' => $pgProgWebUser,
                    'support' => $pgSandreSupport,
                    'stations' => $tabStations,));
    }

    public function syntheseSupportAction($codeSupport = null) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }

        $session = $this->get('session');
        $session->set('menu', 'syntheseHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'syntheseSupport');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $session->remove('suiviHydrobio_prestataire');
        $session->remove('suiviHydrobio_avis');
        $session->remove('suiviHydrobio_statut');
        $session->remove('suiviHydrobio_validation');

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgWebuserZgeoref = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserZgeoref');
        $repoPgProgZgeorefStation = $emSqe->getRepository('AeagSqeBundle:PgProgZgeorefStation');
        $repoPgSandreSupport = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgSandreSupport = $repoPgSandreSupport->getPgSandreSupportsByCodeSupport($codeSupport);

        $tabResultats = $repoPgCmdPrelev->getPgCmdPrelevBySyntheseSupport($pgSandreSupport);
        $tabStations = array();
        $i = 0;
        for ($nbStations = 0; $nbStations < count($tabResultats); $nbStations++) {
            $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($tabResultats[$nbStations]['prelevId']);
            $pgCmdSuiviPrelDernier = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($tabResultats[$nbStations]['suiviPrelId']);
            $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($tabResultats[$nbStations]['ouvFoncId']);
            if (!$user->hasRole('ROLE_ADMINSQE')) {
                $pgProgWebuserZgeorefs = $repoPgProgWebuserZgeoref->getPgProgWebuserZgeorefByWebuser($pgProgWebUser);
                $trouve = false;
                foreach ($pgProgWebuserZgeorefs as $pgProgWebuserZgeoref) {
                    $pgProgZoneGeoref = $pgProgWebuserZgeoref->getZgeoref();
                    $pgProgZgeorefStations = $repoPgProgZgeorefStation->getpgProgZgeorefStationByZgeoref($pgProgZoneGeoref);
                    foreach ($pgProgZgeorefStations as $pgProgZgeorefStation) {
                        if ($pgRefStationMesure->getOuvFoncId() == $pgProgZgeorefStation->getStationMesure()->getOuvFoncId()) {
                            $trouve = true;
                            break;
                        }
                    }
                    if ($trouve) {
                        break;
                    }
                }
            } else {
                $trouve = true;
            }
            if ($trouve) {
                if ($pgCmdSuiviPrelDernier) {
                    $tabStations[$i]['station']['ouvFoncId'] = $pgRefStationMesure->getOuvFoncId();
                    $tabStations[$i]['station']['code'] = $pgRefStationMesure->getCode();
                    $tabStations[$i]['station']['libelle'] = $pgRefStationMesure->getLibelle();
                    $tabStations[$i]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $pgRefStationMesure->getCode()) . '.pdf';
                    $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgCmdPrelev->getDemande()->getLotan(), $pgRefStationMesure);
                    $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
                    if ($pgRefReseauMesure) {
                        $tabStations[$i]['reseau'] = $pgRefReseauMesure->getnomRsx();
                    } else {
                        $tabStations[$i]['reseau'] = null;
                    }
                    $tabStations[$i]['cmdDemande'] = $pgCmdPrelev->getDemande();
                    $dateLimite = null;
                    if ($pgCmdSuiviPrelDernier->getFichierRps()) {
                        if ($pgCmdSuiviPrelDernier->getFichierRps()->getDateDepot()) {
                            $dateDepot = $pgCmdSuiviPrelDernier->getFichierRps()->getDateDepot();
                            $delai = 21;
                            $dateLimite = $dateDepot->add(new \DateInterval('P' . $delai . 'D'));
                        }
                    }
                    $tabStations[$i]['dateLimite'] = $dateLimite;
                    $tabStations[$i]['cmdPrelev'] = $pgCmdPrelev;
                    $tabStations[$i]['suiviPrel'] = $pgCmdSuiviPrelDernier;
                    $i++;
                }
            }
//                }
//            }
        }

//         \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response('');

        return $this->render('AeagSqeBundle:SuiviHydrobio:syntheseSupport.html.twig', array('pgProgWebUser' => $pgProgWebUser,
                    'support' => $pgSandreSupport,
                    'stations' => $tabStations,));
    }

    public function syntheseSupportStationAction($codeSupport = null, $stationId = null, $suiviPrelId = null, $tr = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'syntheseHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'syntheseSupportStation');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgSandreSupport = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgSandreSupport = $repoPgSandreSupport->getPgSandreSupportsByCodeSupport($codeSupport);
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdSuiviPrelActuel = clone($pgCmdSuiviPrel);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $lien = '/sqe_fiches_stations/' . str_replace('/', '-', $pgRefStationMesure->getCode()) . '.pdf';
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgCmdPrelev->getDemande()->getLotan(), $pgCmdPrelev->getStation());
        $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
        if ($pgRefReseauMesure) {
            $reseau = $pgRefReseauMesure;
        } else {
            $reseau = null;
        }
        $dateLimite = null;
        if ($pgCmdSuiviPrel->getFichierRps()) {
            if ($pgCmdSuiviPrel->getFichierRps()->getDateDepot()) {
                $dateDepot = $pgCmdSuiviPrel->getFichierRps()->getDateDepot();
                $delai = 21;
                $dateLimite = $dateDepot->add(new \DateInterval('P' . $delai . 'D'));
            }
        }

        $form = $this->createForm(new SyntheseSupportStationType($pgCmdSuiviPrel));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $date = date("d/m/Y");
            $heure = date("H:i");
            $avis = $_POST['avis'];
            $commentaireAvis = 'Déposé le ' . $date . ' à ' . $heure . ' par ' . $pgProgWebUser->getnom();
            if ($_POST['commentaireAvis'] != '') {
                $commentaireAvis .= ' : ' . CHR(13) . CHR(10);
                $commentaireAvis .= '      ' . $_POST['commentaireAvis'];
            } else {
                if ($avis == 'F') {
                    $commentaireAvis .= ' : Favorable';
                } else {
                    $commentaireAvis .= ' : Défavorable';
                }
            }
            if ($avis == 'F') {
                if ($pgCmdSuiviPrelActuel->getCommentaire()) {
                    $commentaire = $pgCmdSuiviPrelActuel->getCommentaire();
                    $commentaire .= CHR(13) . CHR(10) . $commentaireAvis;
                } else {
                    $commentaire = $commentaireAvis;
                }
                $pgCmdSuiviPrel->setCommentaire($commentaire);
            }
            $pgCmdSuiviPrel->setValidation('A');
            $pgCmdSuiviPrel->setValidAuto('N');
            $pgCmdSuiviPrel->setCommentaireAvis($commentaireAvis);
            $pgCmdSuiviPrel->setAvis($avis);
            $emSqe->persist($pgCmdSuiviPrel);
            $emSqe->flush();
            if ($pgCmdSuiviPrel->getAvis() == 'D') {
                $objetMessage = "Suivi Hydobio : Avis Défavorable sur la la station " . $pgRefStationMesure->getCode();
                $txtMessage = "Un avis défavorable viens d'être déposé : <br/>";
                $txtMessage = $txtMessage . " <ul><li>  Station : " . $pgRefStationMesure->getCode() . "  " . $pgRefStationMesure->getLibelle() . '</li>';
                $txtMessage = $txtMessage . "<li> Support : " . $pgSandreSupport->getNomSupport();
                "</li>";
                $txtMessage = $txtMessage . " <li> Date : " . $pgCmdSuiviPrel->getdatePrel()->format('d/m/Y H:i') . '</li>';
                $txtMessage = $txtMessage . " <li> " . $commentaireAvis . '</li></ul>';
                $mailer = $this->get('mailer');
                // envoi mail  à Margaux
                $destinataire = $repoPgProgWebUsers->getPgProgWebusersByid('3');
                // Envoi d'un mail
                if ($this->get('aeag_sqe.message')->envoiMessage($emSqe, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                    $message = 'un email vous a été envoyé par ' . $pgProgWebUser->getNom();
                    $message = $message . ' suite à un avis défavorable déposé ' . CHR(13) . CHR(10);
                    $message = $message . ' sur la station ' . $pgRefStationMesure->getCode() . " : " . $pgRefStationMesure->getLibelle() . CHR(13) . CHR(10);
                    $message = $message . ' pour le support  ' . $pgSandreSupport->getNomSupport() . CHR(13) . CHR(10);
                    $message = $message . ' à la date du ' . $pgCmdSuiviPrel->getdatePrel()->format('d/m/Y H:i') . CHR(13) . CHR(10);
                    $notifications = $this->get('aeag.notifications');
                    $userDest = $repoUsers->getUserById($destinataire->getExtId());
                    $notifications->createNotification($user, $userDest, $em, $session, $message);
                } else {
                    $session->getFlashBag()->add('notice-warning', ' l\'email n\'a pas pu être envoyé à ' . $destinataire->getNom());
                }
            }

            return $this->render('AeagSqeBundle:SuiviHydrobio:syntheseSupportStationMaj.html.twig', array('support' => $pgSandreSupport,
                        'station' => $pgRefStationMesure,
                        'lien' => $lien,
                        'reseau' => $reseau,
                        'cmdPrelev' => $pgCmdPrelev,
                        'suiviPrel' => $pgCmdSuiviPrel,
                        'dateLimite' => $dateLimite,
                        'nb' => $tr));
        }

        return $this->render('AeagSqeBundle:SuiviHydrobio:syntheseSupportStation.html.twig', array('support' => $pgSandreSupport,
                    'station' => $pgRefStationMesure,
                    'lien' => $lien,
                    'reseau' => $reseau,
                    'cmdPrelev' => $pgCmdPrelev,
                    'suiviPrel' => $pgCmdSuiviPrel,
                    'tr' => $tr,
                    'dateLimite' => $dateLimite,
                    'form' => $form->createView(),
        ));



//          \Symfony\Component\VarDumper\VarDumper::dump($tabDemande);
//        return new Response ('');
    }

    public function syntheseSupportStationvaliderAction($codeSupport = null, $stationId = null, $suiviPrelId = null, $tr = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'syntheseHydrobio');
        $session->set('controller', 'SuiviHydrobio');
        $session->set('fonction', 'syntheseSupportStation');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgSandreSupport = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgSandreSupport = $repoPgSandreSupport->getPgSandreSupportsByCodeSupport($codeSupport);
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdSuiviPrelActuel = clone($pgCmdSuiviPrel);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $lien = '/sqe_fiches_stations/' . str_replace('/', '-', $pgRefStationMesure->getCode()) . '.pdf';
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgCmdPrelev->getDemande()->getLotan(), $pgCmdPrelev->getStation());
        $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
        if ($pgRefReseauMesure) {
            $reseau = $pgRefReseauMesure;
        } else {
            $reseau = null;
        }
        $dateLimite = null;
        if ($pgCmdSuiviPrel->getFichierRps()) {
            if ($pgCmdSuiviPrel->getFichierRps()->getDateDepot()) {
                $dateDepot = $pgCmdSuiviPrel->getFichierRps()->getDateDepot();
                $delai = 21;
                $dateLimite = $dateDepot->add(new \DateInterval('P' . $delai . 'D'));
            }
        }

        $form = $this->createForm(new PgCmdSuiviPrelMajType($user, $pgCmdSuiviPrelActuel), $pgCmdSuiviPrel);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $datePrel = $pgCmdSuiviPrel->getDatePrel();
            $pgCmdSuiviPrel->setValidAuto('N');
            $emSqe->persist($pgCmdSuiviPrel);
            if ($pgCmdSuiviPrel->getStatutPrel() == 'F' && $pgCmdSuiviPrel->getValidation() == 'A') {
                $pgCmdPrelev->setDatePrelev($datePrel);
                $pgCmdPrelev->setRealise('O');
            } elseif ($pgCmdSuiviPrel->getStatutPrel() == 'N' || $pgCmdSuiviPrel->getStatutPrel() == 'R') {
                $pgCmdPrelev->setDatePrelev($datePrel);
                $pgCmdPrelev->setRealise('N');
            } else {
//$pgCmdPrelev->setDatePrelev(null);
                $pgCmdPrelev->setRealise(null);
            }
            $emSqe->persist($pgCmdPrelev);
            $emSqe->flush();


//            $session->getFlashBag()->add('notice-success', 'le suivi du ' . $datePrel->format('d/m/Y') . ' a été modifié !');
            return $this->render('AeagSqeBundle:SuiviHydrobio:syntheseSupportStationValiderRetour.html.twig', array(
                        'pgProgWebUser' => $pgProgWebUser,
                        'support' => $pgSandreSupport,
                        'station' => $pgRefStationMesure,
                        'lien' => $lien,
                        'reseau' => $reseau,
                        'cmdPrelev' => $pgCmdPrelev,
                        'suiviPrel' => $pgCmdSuiviPrel,
                        'dateLimite' => $dateLimite,
                        'nb' => $tr));
        }

        return $this->render('AeagSqeBundle:SuiviHydrobio:syntheseSupportStationValider.html.twig', array(
                    'support' => $pgSandreSupport,
                    'station' => $pgRefStationMesure,
                    'lien' => $lien,
                    'reseau' => $reseau,
                    'prelev' => $pgCmdPrelev,
                    'suiviPrel' => $pgCmdSuiviPrel,
                    'tr' => $tr,
                    'dateLimite' => $dateLimite,
                    'form' => $form->createView(),
        ));




//          \Symfony\Component\VarDumper\VarDumper::dump($tabDemande);
//        return new Response ('');
    }

    protected function getCheminEchange($pgCmdSuiviPrel) {
        if (substr($pgCmdSuiviPrel->getStatutPrel(), 0, 1) != 'D') {
            $chemin = $this->container->getParameter('repertoire_echange');
            $chemin .= $pgCmdSuiviPrel->getPrelev()->getDemande()->getAnneeProg() . '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getCommanditaire()->getNomCorres();
            $chemin .= '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getLot()->getId() . '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getId();
            $chemin .= '/SUIVI/' . $pgCmdSuiviPrel->getPrelev()->getId() . '/' . $pgCmdSuiviPrel->getId();
        } else {
            $chemin = $this->container->getParameter('repertoire_depotHydrobio');
            $chemin .= $pgCmdSuiviPrel->getPrelev()->getDemande()->getAnneeProg() . '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getCommanditaire()->getNomCorres();
            $chemin .= '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getLot()->getId() . '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getId();
            $chemin .= '/' . $pgCmdSuiviPrel->getFichierRps()->getId();
        }


        return $chemin;
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
                    $nom_fichier = preg_replace('[^a-zA-Z0-9.]', '-', $nom_fichier);

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

    protected function rmAllDir($strDirectory) {
        $handle = opendir($strDirectory);
        if ($handle != false) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != '.' && $entry != '..') {
                    if (is_dir($strDirectory . '/' . $entry)) {
                        $this->rmAllDir($strDirectory . '/' . $entry);
                    } elseif (is_file($strDirectory . '/' . $entry)) {
                        unlink($strDirectory . '/' . $entry);
                    }
                }
            }
            rmdir($strDirectory . '/' . $entry);
            closedir($handle);
        }
    }

}
