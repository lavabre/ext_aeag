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
use Aeag\AeagBundle\Controller\AeagController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class SaisieDonneesController extends Controller {

    public function indexAction() {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }

        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
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

        return $this->render('AeagSqeBundle:SaisieDonnees:index.html.twig', array('user' => $user,
                    'lotans' => $pgProgLotAns));
    }

    public function lotPeriodesAction($lotanId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodes');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotanId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }

        $tabPeriodeAns = array();
        $i = 0;
        foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {

            if ($pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'DEL' and $pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'INV') {
                $tabPeriodeAns[$i]['pgProgLotPeriodeAn'] = $pgProgLotPeriodeAn;
                $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
                $tabStations = array();
                $nbStations = 0;
                $nbStationCorrectes = 0;
                $j = 0;
                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                    $prestataire = $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft();
                    if ($user->hasRole('ROLE_ADMINSQE') or ( $userPrestataire == $prestataire)) {
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
                            $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeByLotanPrestatairePeriode($pgProgLotAn, $prestataire, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                            if ($pgCmdDemande) {
                                $tabStations[$j] = $pgProgLotPeriodeProg->getStationAn()->getStation();
                                $nbStations++;
                                $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($pgCmdDemande->getPrestataire(), $pgCmdDemande, $tabStations[$j], $pgProgLotPeriodeAn->getPeriode());
                                $nbPrelevs = 0;
                                $nbPrelevCorrects = 0;
                                foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                    if ($pgCmdPrelev) {
                                        $nbPrelevs++;
                                        if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M40') {
                                            $nbPrelevCorrects++;
                                        }
                                    }
                                }
                                if ($nbPrelevs == $nbPrelevCorrects) {
                                    $nbStationCorrectes++;
                                }
                                $j++;
                            }
                        }
                    }
                }
                $tabPeriodeAns[$i]['nbStations'] = $nbStations;
                $tabPeriodeAns[$i]['nbStationCorrectes'] = $nbStationCorrectes;
                $tabPeriodeAns[$i]['stations'] = $tabStations;
                $i++;
            }
        }


        return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodes.html.twig', array(
                    'user' => $pgProgWebUser,
                    'typeMilieu' => $pgProgTypeMilieu,
                    'lotan' => $pgProgLotAn,
                    'periodeAns' => $tabPeriodeAns));
    }

    public function lotPeriodeTelechargerAction($periodeAnId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', ' lotPeriodeTelecharger');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgPrestaTypfic = $emSqe->getRepository('AeagSqeBundle:PgProgPrestaTypfic');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());


        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } elseif ($pgProgLot->getTitulaire()) {
            $userPrestataire = $pgProgLot->getTitulaire();
        } else {
            $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByPrestataire($pgProgLotAn);
            foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
                if ($pgProgLotGrparAn->getValide() == 'O' and ( $pgProgLotGrparAn->getOrigine() == 'R' or $pgProgLotGrparAn->getOrigine() == 'A')) {
                    $userPrestataire = $pgProgLotGrparAn->getPrestaDft();
                    break;
                }
            }
        }

        $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeByLotanPrestatairePeriode($pgProgLotAn, $userPrestataire, $pgProgLotPeriodeAn->getPeriode());


        $tabStations = array();
        $i = 0;
        $j = 0;
        $k = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $trouve = false;
            if (count($tabStations) > 0) {
                for ($k = 0; $k < count($tabStations); $k++) {
                    if ($tabStations[$k]->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                        $trouve = true;
                        break;
                    }
                }
            }
            if (!$trouve) {
                $tabStations[$i] = $pgProgLotPeriodeProg->getStationAn()->getStation();
                $i++;
            }
        }
        asort($tabStations);

        $chemin = '/base/extranet/Transfert/Sqe/csv';
        $fichier = 'Saisie-des-donnees-periode-' . $pgProgLotPeriodeAn->getId() . '-' . $pgProgLotPeriodeAn->getPeriode()->getTypePeriode()->getCodeTypePeriode() . '-' . $pgProgLotPeriodeAn->getPeriode()->getNumPeriode() . '.csv';
        $fullFileName = $chemin . '/' . $fichier;
        $ext = strtolower(pathinfo($fullFileName, PATHINFO_EXTENSION));
        if (file_exists($fullFileName)) {
            unlink($fullFileName);
        }
        $fichier_csv = fopen($fullFileName, 'w+');
        // Entete
        $ligne = array('Année', 'Code station', 'Nom station', 'Code masse d\'eau', 'Code du prélèvement',
            'Siret préleveur', 'Nom préleveur', 'Date-heure du prélèvement', 'Code du paramètre',
            'Libellé court paramètre', 'Nom paramètre', 'Zone verticale', 'Profondeur', 'Code support',
            'Nom support', 'Code fraction', 'Nom fraction', 'Code méthode', 'Nom méthode', 'Code remarque',
            'Résultat', 'Valeur textuelle', 'Code unité', 'libellé unité', 'symbole unité', 'LQ', 'Siret labo',
            'Nom labo', 'Code réseau', 'Nom réseau', 'Siret prod', 'Nom prod', 'Commentaire');
        for ($i = 0; $i < count($ligne); $i++) {
            $ligne[$i] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$i]);
        }

        fputcsv($fichier_csv, $ligne, ';');

        for ($i = 0; $i < count($tabStations); $i++) {
            $station = $tabStations[$i];
            $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($pgCmdDemande->getPrestataire(), $pgCmdDemande, $station, $pgProgLotPeriodeAn->getPeriode());
            asort($pgCmdPrelevs);
            $fichierRps = null;
            $donneesBrutes = array();
            foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                if ($pgCmdPrelev) {
                    if ($pgCmdPrelev->getFichierRps()) {
                        if ($fichierRps != $pgCmdPrelev->getFichierRps()) {
                            //echo ('station : ' . $station->getCode() . ' prelev : ' . $pgCmdPrelev->getId() . ' fichierRps : ' .  $pgCmdPrelev->getFichierRps()->getId() . ' </br>');
                            $donneesBrutes = $repoPgCmdPrelev->getDonneesBrutes($pgCmdPrelev->getFichierRps());
                            foreach ($donneesBrutes as $ligne) {
                                foreach ($ligne as $j => $value) {
                                    $ligne[$j] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$j]);
                                }
                                fputcsv($fichier_csv, $ligne, ';');
                            }
                            $fichierRps = $pgCmdPrelev->getFichierRps();
                        }
                    }
                }
            }
        }
        fclose($fichier_csv);

//        \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response ('');
//         return new Response ('fichier : ' . $chemin . '/' . $fichier . ' ext : ' . $ext. ' size : ' . filesize($chemin . '/' . $fichier));

        \header("Cache-Control: no-cahe, must-revalidate");
        \header('Content-Type', 'text/' . $ext);
        \header('Content-disposition: attachment; filename="' . $fichier . '"');
        \header('Expires: 0');
        \header('Content-Length: ' . filesize($chemin . '/' . $fichier));
        readfile($chemin . '/' . $fichier);
        exit();
    }

    public function lotPeriodeStationsHoldAction($periodeAnId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStations');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgPrestaTypfic = $emSqe->getRepository('AeagSqeBundle:PgProgPrestaTypfic');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());


        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }



        $tabStations = array();
        $i = 0;
        $j = 0;
        $k = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $prestataire = $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft();
            if ($user->hasRole('ROLE_ADMINSQE') or ( $userPrestataire == $prestataire)) {
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
                    if (!$user->hasRole('ROLE_ADMINSQE1')) {
                        $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeByLotanPrestatairePeriode($pgProgLotAn, $prestataire, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                        $pgProgLotStationAn = $pgProgLotPeriodeProg->getStationAn();
                        $tabStations[$i]['stationAn'] = $pgProgLotStationAn;
                        $tabStations[$i]['station'] = $pgProgLotStationAn->getStation();
                        $tabStations[$i]['prestataire'] = $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft();
                        $tabStations[$i]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $pgProgLotPeriodeProg->getStationAn()->getStation()->getCode()) . '.pdf';
                        $tabStations[$i]['cmdDemande'] = $pgCmdDemande;
                        $tabStations[$i]['cmdPrelev'] = null;
                        $tabStations[$i]['autreCmdDemande'] = null;
                        $tabStations[$i]['autreCmdPrelev'] = null;
                        $autrePgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                        foreach ($autrePgCmdDemandes as $autrePgCmdDemande) {
                            if ($autrePgCmdDemande != $pgCmdDemande) {
                                $tabStations[$i]['autreCmdDemande'] = $autrePgCmdDemande;
                                $tabStations[$i]['autreCmdPrelev'] = null;
                            }
                        }
                        //echo('station : ' . $pgProgLotStationAn->getStation()->getOuvFoncId() . ' ' . $pgProgLotStationAn->getStation()->getCode()    . ' prestataire : ' . $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft()->getAdrCorId() . ' ' . $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft()->getAncnum() . '</br>' );
                        $i++;
                    } else {
                        $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                        foreach ($pgCmdDemandes as $pgCmdDemande) {
                            $pgProgLotStationAn = $pgProgLotPeriodeProg->getStationAn();
                            $tabStations[$i]['stationAn'] = $pgProgLotStationAn;
                            $tabStations[$i]['station'] = $pgProgLotStationAn->getStation();
                            $tabStations[$i]['prestataire'] = $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft();
                            $tabStations[$i]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $pgProgLotPeriodeProg->getStationAn()->getStation()->getCode()) . '.pdf';
                            $tabStations[$i]['cmdDemande'] = $pgCmdDemande;
                            $tabStations[$i]['cmdPrelev'] = null;
                            $tabStations[$i]['autreCmdDemande'] = null;
                            $tabStations[$i]['autreCmdPrelev'] = null;
                            $autrePgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                            foreach ($autrePgCmdDemandes as $autrePgCmdDemande) {
                                if ($autrePgCmdDemande != $pgCmdDemande) {
                                    $tabStations[$i]['autreCmdDemande'] = $autrePgCmdDemande;
                                }
                            }
                            //echo('station : ' . $pgProgLotStationAn->getStation()->getOuvFoncId() . ' ' . $pgProgLotStationAn->getStation()->getCode()    . ' prestataire : ' . $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft()->getAdrCorId() . ' ' . $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft()->getAncnum() . '</br>' );
                            $i++;
                        }
                    }
                }
            }
        }
//                \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response('');

        for ($i = 0; $i < count($tabStations); $i++) {
            $pgProgLotStationAn = $tabStations[$i]['stationAn'];
            $station = $tabStations[$i]['station'];
            $pgCmdDemande = $tabStations[$i]['cmdDemande'];
            $j = 0;
            if ($pgCmdDemande) {
                if (!$user->hasRole('ROLE_ADMINSQE1')) {
                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($pgCmdDemande->getPrestataire(), $pgCmdDemande, $station, $pgProgLotPeriodeAn->getPeriode());
                } else {
                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemandeStationPeriode($pgCmdDemande, $station, $pgProgLotPeriodeAn->getPeriode());
                }

                foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                    if ($pgCmdPrelev) {
                        $tabStations[$i]['cmdPrelev'][$j]['cmdPrelev'] = $pgCmdPrelev;
                        $tabStations[$i]['cmdPrelev'][$j]['saisieTerrain'] = 'N';
                        $tabStations[$i]['cmdPrelev'][$j]['nbParametresTerrain'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrain'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrainCorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrainIncorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrainErreur'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['saisieAnalyse'] = 'N';
                        $tabStations[$i]['cmdPrelev'][$j]['nbParametresAnalyse'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyse'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyseCorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyseIncorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyseErreur'] = 0;
                        $pgPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                        if ($pgPrelevPcs) {
                            $tabStations[$i]['cmdPrelev'][$j]['prelevPc'] = $pgPrelevPcs[0];
                        } else {
                            $tabStations[$i]['cmdPrelev'][$j]['prelevPc'] = null;
                        }
                        $pgProgPrestaTypfic = $repoPgProgPrestaTypfic->getPgProgPrestaTypficByCodeMilieu($pgProgTypeMilieu, $pgCmdPrelev->getprestaPrel());
                        if ($pgProgPrestaTypfic) {
                            $NbProgLotParamAn = 0;
                            if ($NbProgLotParamAn == 0) {
                                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                                    if ($tabStations[$i]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                                        $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                                        if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV') {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                $pgCmdMesureEnvs = $repoPgCmdMesureEnv->getPgCmdMesureEnvsByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                                if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    if (count($pgCmdMesureEnvs) > 0) {
                                                        $NbProgLotParamAn += count($pgCmdMesureEnvs);
                                                    } else {
                                                        $NbProgLotParamAn++;
                                                    }
                                                }
                                            }
                                        }
                                        if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT') {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                                if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    if (count($pgCmdAnalyses) > 0) {
                                                        $NbProgLotParamAn += count($pgCmdAnalyses);
                                                    } else {
                                                        $NbProgLotParamAn++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $tabStations[$i]['cmdPrelev'][$j]['nbParametresTerrain'] = $NbProgLotParamAn;
                            if ($NbProgLotParamAn == 0) {
                                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                                    if ($tabStations[$i]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                                        $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                                        if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV') {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                $pgCmdMesureEnvs = $repoPgCmdMesureEnv->getPgCmdMesureEnvsByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                                if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() != $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    if (count($pgCmdMesureEnvs) > 0) {
                                                        $NbProgLotParamAn += count($pgCmdMesureEnvs);
                                                    } else {
                                                        $NbProgLotParamAn++;
                                                    }
                                                }
                                            }
                                        }
                                        if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT') {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                                if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() != $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    if (count($pgCmdAnalyses) > 0) {
                                                        $NbProgLotParamAn += count($pgCmdAnalyses);
                                                    } else {
                                                        $NbProgLotParamAn++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $tabStations[$i]['cmdPrelev'][$j]['nbAutresParametresTerrain'] = $NbProgLotParamAn;
                            $NbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
                            $NbCmdMesureEnvCorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '0');
                            $NbCmdMesureEnvIncorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '1');
                            $NbCmdMesureEnvErreur = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '2');
                            $NbCmdAnalyse = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
                            $NbCmdAnalyseCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '0');
                            $NbCmdAnalyseIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '1');
                            $NbCmdAnalyseErreur = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '2');
                            $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrain'] = $NbCmdMesureEnv + $NbCmdAnalyse;
                            $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrainCorrect'] = $NbCmdMesureEnvCorrect + $NbCmdAnalyseCorrect;
                            $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrainIncorrect'] = $NbCmdMesureEnvIncorrect + $NbCmdAnalyseIncorrect;
                            $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrainErreur'] = $NbCmdMesureEnvErreur + $NbCmdAnalyseErreur;
                            if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() <= 'M40') {
                                if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_T') {
                                    $tabStations[$i]['cmdPrelev'][$j]['saisieTerrain'] = 'O';
                                }
                                if ($user->hasRole('ROLE_ADMINSQE') and $pgCmdPrelev->getPhaseDmd()->getcodePhase() < 'M50') {
                                    $tabStations[$i]['cmdPrelev'][$j]['saisieTerrain'] = 'O';
                                }
                            }

                            $NbProgLotParamAn = 0;
                            if ($NbProgLotParamAn == 0) {
                                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                                    if ($tabStations[$i]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                                        $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                                        if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    $NbProgLotParamAn++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $tabStations[$i]['cmdPrelev'][$j]['nbParametresAnalyse'] = $NbProgLotParamAn;
                            if ($NbProgLotParamAn == 0) {
                                $nbp = 0;
                                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                                    if ($tabStations[$i]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                                        $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                                        if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() != $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    $NbProgLotParamAn++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $tabStations[$i]['cmdPrelev'][$j]['nbAutresParametresAnalyse'] = $NbProgLotParamAn;
                            $NbCmdAnalyse = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
                            $NbCmdAnalyseCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '0');
                            $NbCmdAnalyseIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '1');
                            $NbCmdAnalyseErreur = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '2');
                            $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyse'] = $NbCmdAnalyse;
                            $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyseCorrect'] = $NbCmdAnalyseCorrect;
                            $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyseIncorrect'] = $NbCmdAnalyseIncorrect;
                            $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyseErreur'] = $NbCmdAnalyseErreur;
                            if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() <= 'M40') {
                                if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_A' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_T') {
                                    $tabStations[$i]['cmdPrelev'][$j]['saisieAnalyse'] = 'O';
                                }
                                if ($user->hasRole('ROLE_ADMINSQE') and $pgCmdPrelev->getPhaseDmd()->getcodePhase() < 'M50') {
                                    $tabStations[$i]['cmdPrelev'][$j]['saisieAnalyse'] = 'O';
                                }
                            }
                            if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M30') {
                                $tabStations[$i]['cmdPrelev'][$j]['valider'] = 'O';
                                $tabStations[$i]['cmdPrelev'][$j]['autreValider'] = 'O';
                            } else {
                                $tabStations[$i]['cmdPrelev'][$j]['valider'] = 'N';
                                $tabStations[$i]['cmdPrelev'][$j]['autreValider'] = 'O';
                            }
                            if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M40') {
                                $tabStations[$i]['cmdPrelev'][$j]['devalider'] = 'O';
                                $tabStations[$i]['cmdPrelev'][$j]['autreDevalider'] = 'O';
                                $tabStations[$i]['cmdPrelev'][$j]['saisieAnalyse'] = 'N';
                                $tabStations[$i]['cmdPrelev'][$j]['saisieTerrain'] = 'N';
                            } else {
                                $tabStations[$i]['cmdPrelev'][$j]['devalider'] = 'N';
                                $tabStations[$i]['cmdPrelev'][$j]['autreDevalider'] = 'O';
                            }
                        }
                    }
                    $j++;
                }
            }
            $autrePgCmdDemande = $tabStations[$i]['autreCmdDemande'];

            $j = 0;
            if ($autrePgCmdDemande) {
                if (!$user->hasRole('ROLE_ADMINSQE1')) {
                    $autrePgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($autrePgCmdDemande->getPrestataire(), $autrePgCmdDemande, $station, $pgProgLotPeriodeAn->getPeriode());
                } else {
                    $autrePgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemandeStationPeriode($autrePgCmdDemande, $station, $pgProgLotPeriodeAn->getPeriode());
                }

                foreach ($autrePgCmdPrelevs as $autrePgCmdPrelev) {
                    if ($autrePgCmdPrelev) {
                        $tabStations[$i]['cmdPrelev'][$j]['autreCmdPrelev'] = $autrePgCmdPrelev;
                        $tabStations[$i]['cmdPrelev'][$j]['autreSaisieTerrain'] = 'N';
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbParametresTerrain'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrain'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrainCorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrainIncorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrainErreur'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreSaisieAnalyse'] = 'N';
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbParametresAnalyse'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyse'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyseCorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyseIncorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyseErreur'] = 0;
                        $autrePgPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($autrePgCmdPrelev);
                        if ($autrePgPrelevPcs) {
                            $tabStations[$i]['cmdPrelev'][$j]['autrePrelevPc'] = $autrePgPrelevPcs[0];
                        } else {
                            $tabStations[$i]['cmdPrelev'][$j]['autrePrelevPc'] = null;
                        }
                        $autrePgProgPrestaTypfic = $repoPgProgPrestaTypfic->getPgProgPrestaTypficByCodeMilieu($pgProgTypeMilieu, $autrePgCmdPrelev->getprestaPrel());
                        if ($autrePgProgPrestaTypfic) {
                            $autreNbProgLotParamAn = 0;
                            if ($autreNbProgLotParamAn == 0) {
                                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                                    if ($tabStations[$i]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                                        $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                                        if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV') {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                $autrePgCmdMesureEnvs = $repoPgCmdMesureEnv->getPgCmdMesureEnvsByPrelevParamProg($autrePgCmdPrelev, $pgProgLotParamAn);
                                                if ($autrePgCmdPrelev->getprestaPrel()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    if (count($pgCmdMesureEnvs) > 0) {
                                                        $autreNbProgLotParamAn += count($autrePgCmdMesureEnvs);
                                                    } else {
                                                        $autreNbProgLotParamAn++;
                                                    }
                                                }
                                            }
                                        }
                                        if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT') {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                $autrePgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($autrePgCmdPrelev, $pgProgLotParamAn);
                                                if ($autrePgCmdPrelev->getprestaPrel()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    if (count($autrePgCmdAnalyses) > 0) {
                                                        $autreNbProgLotParamAn += count($autrePgCmdAnalyses);
                                                    } else {
                                                        $autreNbProgLotParamAn++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $tabStations[$i]['cmdPrelev'][$j]['autreNbParametresTerrain'] = $autreNbProgLotParamAn;
                            if ($autreNbProgLotParamAn == 0) {
                                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                                    if ($tabStations[$i]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                                        $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                                        if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV') {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                $autrePgCmdMesureEnvs = $repoPgCmdMesureEnv->getPgCmdMesureEnvsByPrelevParamProg($autrePgCmdPrelev, $pgProgLotParamAn);
                                                if ($autrePgCmdPrelev->getprestaPrel()->getAdrCorId() != $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    if (count($autrePgCmdMesureEnvs) > 0) {
                                                        $autreNbProgLotParamAn += count($autrePgCmdMesureEnvs);
                                                    } else {
                                                        $autreNbProgLotParamAn++;
                                                    }
                                                }
                                            }
                                        }
                                        if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT') {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                $autrePgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($autrePgCmdPrelev, $pgProgLotParamAn);
                                                if ($autrePgCmdPrelev->getprestaPrel()->getAdrCorId() != $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    if (count($autrePgCmdAnalyses) > 0) {
                                                        $autreNbProgLotParamAn += count($autrePgCmdAnalyses);
                                                    } else {
                                                        $autreNbProgLotParamAn++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $tabStations[$i]['cmdPrelev'][$j]['autreNbParametresTerrain'] = $autreNbProgLotParamAn;
                            $autreNbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($autrePgCmdPrelev);
                            $autreNbCmdMesureEnvCorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($autrePgCmdPrelev, '0');
                            $autreNbCmdMesureEnvIncorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($autrePgCmdPrelev, '1');
                            $autreNbCmdMesureEnvErreur = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($autrePgCmdPrelev, '2');
                            $autreNbCmdAnalyse = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($autrePgCmdPrelev);
                            $autreNbCmdAnalyseCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($autrePgCmdPrelev, '0');
                            $autreNbCmdAnalyseIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($autrePgCmdPrelev, '1');
                            $autreNbCmdAnalyseErreur = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($autrePgCmdPrelev, '2');
                            $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrain'] = $autreNbCmdMesureEnv + $autreNbCmdAnalyse;
                            $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrainCorrect'] = $autreNbCmdMesureEnvCorrect + $autreNbCmdAnalyseCorrect;
                            $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrainIncorrect'] = $autreNbCmdMesureEnvIncorrect + $autreNbCmdAnalyseIncorrect;
                            $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrainErreur'] = $autreNbCmdMesureEnvErreur + $autreNbCmdAnalyseErreur;
                            if ($autrePgCmdPrelev->getPhaseDmd()->getcodePhase() <= 'M40') {
                                if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_T') {
                                    $tabStations[$i]['cmdPrelev'][$j]['autreSaisieTerrain'] = 'O';
                                }
                                if ($user->hasRole('ROLE_ADMINSQE') and $autrePgCmdPrelev->getPhaseDmd()->getcodePhase() < 'M50') {
                                    $tabStations[$i]['cmdPrelev'][$j]['autreSaisieTerrain'] = 'O';
                                }
                            }

                            $autreNbProgLotParamAn = 0;
                            if ($autreNbProgLotParamAn == 0) {
                                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                                    if ($tabStations[$i]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                                        $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                                        if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                if ($autrePgCmdPrelev->getprestaPrel()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    $autreNbProgLotParamAn++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $tabStations[$i]['cmdPrelev'][$j]['autreNbParametresAnalyse'] = $autreNbProgLotParamAn;
                            if ($autreNbProgLotParamAn == 0) {
                                $nbp = 0;
                                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                                    if ($tabStations[$i]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                                        $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                                        if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                if ($autrePgCmdPrelev->getprestaPrel()->getAdrCorId() != $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    $autreNbProgLotParamAn++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $tabStations[$i]['cmdPrelev'][$j]['autreNbAutresParametresAnalyse'] = $autreNbProgLotParamAn;
                            $autreNbCmdAnalyse = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($autrePgCmdPrelev);
                            $autreNbCmdAnalyseCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($autrePgCmdPrelev, '0');
                            $autreNbCmdAnalyseIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($autrePgCmdPrelev, '1');
                            $autreNbCmdAnalyseErreur = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($autrePgCmdPrelev, '2');
                            $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyse'] = $autreNbCmdAnalyse;
                            $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyseCorrect'] = $autreNbCmdAnalyseCorrect;
                            $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyseIncorrect'] = $autreNbCmdAnalyseIncorrect;
                            $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyseErreur'] = $autreNbCmdAnalyseErreur;
                            if ($autrePgCmdPrelev->getPhaseDmd()->getcodePhase() <= 'M40') {
                                if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_A' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_T') {
                                    $tabStations[$i]['cmdPrelev'][$j]['autreSaisieAnalyse'] = 'O';
                                }
                                if ($user->hasRole('ROLE_ADMINSQE') and $autrePgCmdPrelev->getPhaseDmd()->getcodePhase() < 'M50') {
                                    $tabStations[$i]['cmdPrelev'][$j]['autreSaisieAnalyse'] = 'O';
                                }
                            }
                            if ($autrePgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M30') {
                                $tabStations[$i]['cmdPrelev'][$j]['autreValider'] = 'O';
                            } else {
                                $tabStations[$i]['cmdPrelev'][$j]['autreValider'] = 'N';
                            }
                            if ($autrePgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M40') {
                                $tabStations[$i]['cmdPrelev'][$j]['autreDevalider'] = 'O';
                                $tabStations[$i]['cmdPrelev'][$j]['autreSaisieAnalyse'] = 'N';
                                $tabStations[$i]['cmdPrelev'][$j]['autreSaisieTerrain'] = 'N';
                            } else {
                                $tabStations[$i]['cmdPrelev'][$j]['autreDevalider'] = 'N';
                            }
                        }
                    }
                    $j++;
                }
            }
            //   $tabStations[$i]['suiviPrels'] = $tabSuiviPrels;
        }

//        \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response('');

        return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeStations.html.twig', array(
                    'user' => $pgProgWebUser,
                    'typeMilieu' => $pgProgTypeMilieu,
                    'lotan' => $pgProgLotAn,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'stations' => $tabStations));
    }

    public function lotPeriodeStationsAction($periodeAnId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStations');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgPrestaTypfic = $emSqe->getRepository('AeagSqeBundle:PgProgPrestaTypfic');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());


        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }



        $tabStations = array();
        $is = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $prestataire = $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft();
            $periode = $pgProgLotPeriodeProg->getPeriodan()->getPeriode();
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
                $station = $pgProgLotStationAn->getStation();
                $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                $tabStations[$is]['stationAn'] = $pgProgLotStationAn;
                $tabStations[$is]['station'] = $station;
                $tabStations[$is]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $pgProgLotPeriodeProg->getStationAn()->getStation()->getCode()) . '.pdf';
                $tabStations[$is]['pgCmdDemandes'] = null;
                $tabPgCmdDemandes = array();
                $id = 0;
                $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                foreach ($pgCmdDemandes as $pgCmdDemande) {
                    $tabPgCmdDemandes[$id]['pgCmdDemande'] = $pgCmdDemande;
                    $tabPgCmdDemandes[$id]['prestataire'] = $pgCmdDemande->getPrestataire();
                    $tabPgCmdDemandes[$id]['pgCmdPrelevs'] = null;
                    $tabPgCmdPrelevs = array();
                    $ip = 0;
                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemandeStationPeriode($pgCmdDemande, $station, $periode);
                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                        $tabPgCmdPrelevs[$ip]['cmdPrelev'] = $pgCmdPrelev;
                        $tabPgCmdPrelevs[$ip]['valider'] = 'N';
                        $tabPgCmdPrelevs[$ip]['devalider'] = 'N';
                        $tabPgCmdPrelevs[$ip]['saisieTerrain'] = 'N';
                        $tabPgCmdPrelevs[$ip]['nbParametresTerrain'] = 0;
                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrain'] = 0;
                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainCorrect'] = 0;
                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainIncorrect'] = 0;
                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainErreur'] = 0;
                        $tabPgCmdPrelevs[$ip]['saisieAnalyse'] = 'N';
                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyse'] = 0;
                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseCorrect'] = 0;
                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseIncorrect'] = 0;
                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseErreur'] = 0;
                        $pgProgPrestaTypfic = $repoPgProgPrestaTypfic->getPgProgPrestaTypficByCodeMilieu($pgProgTypeMilieu, $pgCmdPrelev->getprestaPrel());
                        if ($pgProgPrestaTypfic) {
                            $nbParametresTerrain = 0;
                            $nbParametresAnalyse = 0;
                            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg1) {
                                if ($station->getOuvFoncId() == $pgProgLotPeriodeProg1->getStationAn()->getStation()->getOuvFoncId()) {
                                    $pgProgLotGrparAn = $pgProgLotPeriodeProg1->getGrparAn();
                                    if ($pgProgLotGrparAn->getvalide() == 'O') {
                                        $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);

                                        foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                            if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV') {
                                                    $nbParametresTerrain++;
                                                }
                                                if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT') {
                                                    $nbParametresTerrain++;
                                                }
                                                if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                                                    $nbParametresAnalyse++;
                                                }
                                            }
                                        }
                                    }

                                    $tabPgCmdPrelevs[$ip]['nbParametresTerrain'] = $nbParametresTerrain;
                                    $tabPgCmdPrelevs[$ip]['nbParametresAnalyse'] = $nbParametresAnalyse;
                                    $NbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
                                    $NbCmdMesureEnvCorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '0');
                                    $NbCmdMesureEnvIncorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '1');
                                    $NbCmdMesureEnvErreur = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '2');
                                    $NbCmdAnalyseSitu = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
                                    $NbCmdAnalyseSituCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '0');
                                    $NbCmdAnalyseSituIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '1');
                                    $NbCmdAnalyseSituErreur = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '2');
                                    $NbCmdAnalyse = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
                                    $NbCmdAnalyseCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '0');
                                    $NbCmdAnalyseIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '1');
                                    $NbCmdAnalyseErreur = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '2');
                                    $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrain'] = $NbCmdMesureEnv + $NbCmdAnalyseSitu;
                                    $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainCorrect'] = $NbCmdMesureEnvCorrect + $NbCmdAnalyseSituCorrect;
                                    $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainIncorrect'] = $NbCmdMesureEnvIncorrect + $NbCmdAnalyseSituIncorrect;
                                    $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainErreur'] = $NbCmdMesureEnvErreur + $NbCmdAnalyseSituErreur;
                                    $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyse'] = $NbCmdAnalyse;
                                    $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseCorrect'] = $NbCmdAnalyseCorrect;
                                    $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseIncorrect'] = $NbCmdAnalyseIncorrect;
                                    $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseErreur'] = $NbCmdAnalyseErreur;
                                    if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() <= 'M40') {
                                        if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_A' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_T') {
                                            $tabPgCmdPrelevs[$ip]['saisieTerrain'] = 'O';
                                            $tabPgCmdPrelevs[$ip]['saisieAnalyse'] = 'O';
                                        }
                                        if ($user->hasRole('ROLE_ADMINSQE') and $pgCmdPrelev->getPhaseDmd()->getcodePhase() < 'M50') {
                                            $tabPgCmdPrelevs[$ip]['saisieTerrain'] = 'O';
                                            $tabPgCmdPrelevs[$ip]['saisieAnalyse'] = 'O';
                                        }
                                    }
                                    if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M30') {
                                        $tabPgCmdPrelevs[$ip]['valider'] = 'O';
                                    }
                                    if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M40') {
                                        $tabPgCmdPrelevs[$ip]['devalider'] = 'O';
                                        $tabPgCmdPrelevs[$ip]['saisieTerrain'] = 'N';
                                        $tabPgCmdPrelevs[$ip]['saisieAnalyse'] = 'N';
                                    }
                                }
                            }
                        }
                        $ip++;
                    }
                    $tabPgCmdDemandes[$id]['pgCmdPrelevs'] = $tabPgCmdPrelevs;
                    $id++;
                }
                $tabStations[$is]['pgCmdDemandes'] = $tabPgCmdDemandes;
                $is++;
            }
        }
//        \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response('');


        return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeStations.html.twig', array(
                    'pgProgWebUser' => $pgProgWebUser,
                    'typeMilieu' => $pgProgTypeMilieu,
                    'lotan' => $pgProgLotAn,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'stations' => $tabStations));
    }

    public function lotPeriodeStationSaisirEnvSituAction($prelevId = null, $periodeAnId = null, $stationId = null, $maj = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationSaisirEnvSitu');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgProgUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $repoPgSandreUnitesPossiblesParamsEnv = $emSqe->getRepository('AeagSqeBundle:PgSandreUnitesPossiblesParamsEnv');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAnOrderByGrparAn($pgProgLotStationAn, $pgProgLotPeriodeAn);

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }

        $tabGroupes = array();
        $nbGroupes = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            if ($pgProgLot->getDelaiPrel()) {
                $dateFin = clone($pgProgLotPeriodeProg->getPeriodan()->getPeriode()->getDateDeb());
                $delai = $pgProgLot->getDelaiPrel();
                $dateFin->add(new \DateInterval('P' . $delai . 'D'));
            } else {
                $dateFin = $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getDateFin();
            }
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            $prestataire = $pgProgLotGrparAn->getPrestaDft();
            if ($pgProgLotGrparAn->getvalide() == 'O' and ( $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV' or $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT')) {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $tabGroupes[$nbGroupes]['grparAn'] = $pgProgLotGrparAn;
                $tabGroupes[$nbGroupes]['correct'] = 0;
                $tabGroupes[$nbGroupes]['warning'] = 0;
                $tabGroupes[$nbGroupes]['erreur'] = 0;
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                $tabParamAns = array();
                $nbParamAns = 0;
                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                    $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($pgProgLotParamAn->getCodeUnite());
                    if ($pgSandreUnites) {
                        $tabParamAns[$nbParamAns]['unite'] = $pgSandreUnites;
                    } else {
                        $tabParamAns[$nbParamAns]['unite'] = null;
                    }
                    if ($pgProgGrpParamRef->getTypeGrp() == 'ENV') {
                        $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                        if ($pgCmdMesureEnv) {
                            if ($pgCmdMesureEnv->getCodeStatut() == '0') {
                                $tabGroupes[$nbGroupes]['correct'] = $tabGroupes[$nbGroupes]['correct'] + 1;
                            } elseif ($pgCmdMesureEnv->getCodeStatut() == '1') {
                                $tabGroupes[$nbGroupes]['warning'] = $tabGroupes[$nbGroupes]['warning'] + 1;
                            } elseif ($pgCmdMesureEnv->getCodeStatut() == '2') {
                                $tabGroupes[$nbGroupes]['erreur'] = $tabGroupes[$nbGroupes]['erreur'] + 1;
                            }
                            $tabParamAns[$nbParamAns]['pgCmdMesureEnv'] = $pgCmdMesureEnv;
                            $tabParamAns[$nbParamAns]['unite'] = $pgCmdMesureEnv->getCodeUnite();
                        } else {
                            $tabParamAns[$nbParamAns]['pgCmdMesureEnv'] = null;
                        }
                        $tabParamAns[$nbParamAns]['pgCmdPrelevPcs'] = null;
                        $pgSandreUnitesPossiblesParamsEnv = $repoPgSandreUnitesPossiblesParamsEnv->getPgSandreUnitesPossiblesParamsEnvByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                        if ($pgSandreUnitesPossiblesParamsEnv) {
                            $tabParamAns[$nbParamAns]['valeurs'] = $pgSandreUnitesPossiblesParamsEnv;
                        } else {
                            $tabParamAns[$nbParamAns]['valeurs'] = null;
                        }
                        $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                        if ($pgProgUnitesPossiblesParam) {
                            $tabParamAns[$nbParamAns]['unites'] = $pgProgUnitesPossiblesParam;
                        } else {
                            $tabParamAns[$nbParamAns]['unites'] = null;
                        }
                        if ($pgProgLotParamAn->getCodeFraction()) {
                            $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                            $tabParamAns[$nbParamAns]['fraction'] = $pgSandreFraction;
                        } else {
                            $tabParamAns[$nbParamAns]['fraction'] = null;
                        }
                        $nbParamAns++;
                    }

                    if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {
                        $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                        if ($pgCmdPrelevPcs) {
                            foreach ($pgCmdPrelevPcs as $pgCmdPrelevPc) {
                                $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, $pgProgLotParamAn->getCodeParametre()->getCodeParametre(), $pgCmdPrelevPc->getNumOrdre());
                                if ($pgCmdAnalyse) {
                                    if ($pgCmdAnalyse->getCodeStatut() == '0') {
                                        $tabGroupes[$nbGroupes]['correct'] = $tabGroupes[$nbGroupes]['correct'] + 1;
                                    } elseif ($pgCmdAnalyse->getCodeStatut() == '1') {
                                        $tabGroupes[$nbGroupes]['warning'] = $tabGroupes[$nbGroupes]['warning'] + 1;
                                    } elseif ($pgCmdAnalyse->getCodeStatut() == '2') {
                                        $tabGroupes[$nbGroupes]['erreur'] = $tabGroupes[$nbGroupes]['erreur'] + 1;
                                    }
                                    $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                                    $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = $pgCmdAnalyse;
                                    $tabParamAns[$nbParamAns]['pgCmdPrelevPc'] = $pgCmdPrelevPc;
                                    if ($pgCmdAnalyse->getCodeUnite()) {
                                        $tabParamAns[$nbParamAns]['unite'] = $pgCmdAnalyse->getCodeUnite();
                                    } else {
                                        $tabParamAns[$nbParamAns]['unite'] = null;
                                    }
                                    $pgSandreUnitesPossiblesParamsEnv = $repoPgSandreUnitesPossiblesParamsEnv->getPgSandreUnitesPossiblesParamsEnvByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                                    if ($pgSandreUnitesPossiblesParamsEnv) {
                                        $tabParamAns[$nbParamAns]['valeurs'] = $pgSandreUnitesPossiblesParamsEnv;
                                    } else {
                                        $tabParamAns[$nbParamAns]['valeurs'] = null;
                                    }
                                    $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                                    if ($pgProgUnitesPossiblesParam) {
                                        $tabParamAns[$nbParamAns]['unites'] = $pgProgUnitesPossiblesParam;
                                    } else {
                                        $tabParamAns[$nbParamAns]['unites'] = null;
                                    }

                                    if ($pgProgLotParamAn->getCodeFraction()) {
                                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                        $tabParamAns[$nbParamAns]['fraction'] = $pgSandreFraction;
                                    } else {
                                        $tabParamAns[$nbParamAns]['fraction'] = null;
                                    }
                                } else {
                                    $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                                    $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = null;
                                    $tabParamAns[$nbParamAns]['pgCmdPrelevPc'] = $pgCmdPrelevPc;
                                    $pgSandreUnitesPossiblesParamsEnv = $repoPgSandreUnitesPossiblesParamsEnv->getPgSandreUnitesPossiblesParamsEnvByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                                    if ($pgSandreUnitesPossiblesParamsEnv) {
                                        $tabParamAns[$nbParamAns]['valeurs'] = $pgSandreUnitesPossiblesParamsEnv;
                                    } else {
                                        $tabParamAns[$nbParamAns]['valeurs'] = null;
                                    }
                                    $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                                    if ($pgProgUnitesPossiblesParam) {
                                        $tabParamAns[$nbParamAns]['unites'] = $pgProgUnitesPossiblesParam;
                                    } else {
                                        $tabParamAns[$nbParamAns]['unites'] = null;
                                    }
                                    $tabParamAns[$nbParamAns]['unite'] = null;

                                    if ($pgProgLotParamAn->getCodeFraction()) {
                                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                        $tabParamAns[$nbParamAns]['fraction'] = $pgSandreFraction;
                                    } else {
                                        $tabParamAns[$nbParamAns]['fraction'] = null;
                                    }
                                }
                                $nbParamAns++;
                            }
                        }
                    }
                }
                sort($tabParamAns);
                $tabGroupes[$nbGroupes]['paramAns'] = $tabParamAns;
                $nbGroupes++;
// }
            }
        }


        if ($maj == 'C') {
            return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeStationVoirEnvSitu.html.twig', array(
                        'user' => $pgProgWebUser,
                        'typeMilieu' => $pgProgTypeMilieu,
                        'lotan' => $pgProgLotAn,
                        'station' => $pgRefStationMesure,
                        'periodeAn' => $pgProgLotPeriodeAn,
                        'dateFin' => $dateFin,
                        'demande' => $pgCmdPrelev->getDemande(),
                        'cmdPrelev' => $pgCmdPrelev,
                        'groupes' => $tabGroupes,
                        'maj' => $maj,));
        } else {
            return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeStationSaisirEnvSitu.html.twig', array(
                        'user' => $pgProgWebUser,
                        'typeMilieu' => $pgProgTypeMilieu,
                        'lotan' => $pgProgLotAn,
                        'station' => $pgRefStationMesure,
                        'periodeAn' => $pgProgLotPeriodeAn,
                        'dateFin' => $dateFin,
                        'demande' => $pgCmdPrelev->getDemande(),
                        'cmdPrelev' => $pgCmdPrelev,
                        'groupes' => $tabGroupes,
                        'maj' => $maj,));
        }
    }

    public function lotPeriodeStationResultatEnvSituAction($prelevId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', ' otPeriodeStationResultatEnvSitu');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgProgUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();


        $now = date('Ymd');
        $today = new \DateTime($now);

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdDemande = $pgCmdPrelev->getDemande();

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);
        $nbErreurs = 0;
        $okControlesSpecifiques = 0;
        $okControleVraisemblance = 0;
        $nbParametresEnvSit = 0;
        $nbParametresAna = 0;


        if (isset($_POST['datePrel'])) {
            $dateSaisie = str_replace('/', '-', $_POST['datePrel']);
            $datePrel = new \DateTime($dateSaisie);
        } else {
            $datePrel = null;
        }
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            $prestataire = $pgProgLotGrparAn->getPrestaDft();
            if ($pgProgLotGrparAn->getvalide() == 'O') {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();

// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);

                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire)) {

                        if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {


                            if (isset($_POST['valeur' . $pgProgLotParamAn->getId()])) {
                                $valeur = $_POST['valeur' . $pgProgLotParamAn->getId()];
                            } else {
                                $valeur = null;
                            }
                            if (isset($_POST['uniteCode' . $pgProgLotParamAn->getId()])) {
                                $unite = $_POST['uniteCode' . $pgProgLotParamAn->getId()];
                            } else {
                                $unite = null;
                            }
                            if (isset($_POST['remarque' . $pgProgLotParamAn->getId()])) {
                                $remarque = $_POST['remarque' . $pgProgLotParamAn->getId()];
                            } else {
                                $remarque = null;
                            }

                            $AnalyseNumOrdre = 1;


                            $nbParametresEnvSit++;
                            $tabStatut = array();
                            $tabStatut['ko'] = 0;
                            $tabStatut['statut'] = 0;
                            $tabStatut['libelle'] = null;
                            $parametre = $pgProgLotParamAn->getCodeParametre()->getCodeparametre();
                            $inSitu = 1;

                            if (strlen($valeur) > 0) {
                                $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                $tabStatut = $this->_controleVraisemblance($parametre, $valeur, $remarque, $unite, $inSitu, $pgSandreFraction, $tabStatut);
                                $okControleVraisemblance = $okControleVraisemblance + $tabStatut['ko'];
                            }
                            if ($pgProgLotParamAn->getCodeFraction()) {
                                $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                            } else {
                                $pgSandreFraction = null;
                            }
                            if (strlen($valeur) > 0) {
                                $pgCmdPrelevPc = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelevNumOrdre($pgCmdPrelev, $AnalyseNumOrdre);
                                if (!$pgCmdPrelevPc) {
                                    $pgCmdPrelevPc = new PgCmdPrelevPc();
                                    $pgCmdPrelevPc->setPrelev($pgCmdPrelev);
                                    $pgCmdPrelevPc->setNumOrdre($AnalyseNumOrdre);
                                    $emSqe->persist($pgCmdPrelevPc);
                                    $emSqe->flush();
                                }
                                $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, $parametre, $AnalyseNumOrdre);
                                if (!$pgCmdAnalyse) {
                                    $pgCmdAnalyse = new PgCmdAnalyse();
                                    $pgCmdAnalyse->setLieuAna('1');
                                    $pgCmdAnalyse->setPrelevId($pgCmdPrelev->getId());
                                    $pgCmdAnalyse->setNumOrdre($AnalyseNumOrdre);
                                    $pgCmdAnalyse->setParamProg($pgProgLotParamAn);
                                    $pgCmdAnalyse->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                    if ($pgSandreFraction) {
                                        $pgCmdAnalyse->setCodeFraction($pgSandreFraction);
                                    }
                                }

                                $pgCmdAnalyse->setDateAna($today);
                                $pgCmdAnalyse->setResultat($valeur);
                                $pgCmdAnalyse->setCodeRemarque($remarque);
                                $pgCmdAnalyse->setCodeMethode('0');
                                $pgCmdAnalyse->setCodeStatut($tabStatut['statut']);
                                $pgCmdAnalyse->setLibelleStatut($tabStatut['libelle']);
                                if ($unite) {
                                    $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($unite);
                                    if ($pgSandreUnites) {
                                        $pgCmdAnalyse->setCodeUnite($pgSandreUnites);
                                    }
                                }
                                $emSqe->persist($pgCmdAnalyse);
                            } else {
                                if ($remarque == '0') {
                                    $pgCmdPrelevPc = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelevNumOrdre($pgCmdPrelev, $AnalyseNumOrdre);
                                    if (!$pgCmdPrelevPc) {
                                        $pgCmdPrelevPc = new PgCmdPrelevPc();
                                        $pgCmdPrelevPc->setPrelev($pgCmdPrelev);
                                        $pgCmdPrelevPc->setNumOrdre($AnalyseNumOrdre);
                                        $emSqe->persist($pgCmdPrelevPc);
                                        $emSqe->flush();
                                    }
                                    $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, $parametre, $AnalyseNumOrdre);
                                    if (!$pgCmdAnalyse) {
                                        $pgCmdAnalyse = new PgCmdAnalyse();
                                        $pgCmdAnalyse->setLieuAna('1');
                                        $pgCmdAnalyse->setPrelevId($pgCmdPrelev->getId());
                                        $pgCmdAnalyse->setNumOrdre($AnalyseNumOrdre);
                                        $pgCmdAnalyse->setParamProg($pgProgLotParamAn);
                                        $pgCmdAnalyse->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                        if ($pgSandreFraction) {
                                            $pgCmdAnalyse->setCodeFraction($pgSandreFraction);
                                        }
                                    }
                                    $pgCmdAnalyse->setDateAna($today);
                                    $pgCmdAnalyse->setResultat(null);
                                    $pgCmdAnalyse->setCodeRemarque($remarque);
                                    $pgCmdAnalyse->setCodeMethode('0');
                                    $pgCmdAnalyse->setCodeStatut(1);
                                    $pgCmdAnalyse->setLibelleStatut('Valeur absente');
                                    if ($unite) {
                                        $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($unite);
                                        if ($pgSandreUnites) {
                                            $pgCmdAnalyse->setCodeUnite($pgSandreUnites);
                                        }
                                    }
                                    $emSqe->persist($pgCmdAnalyse);
                                }
                            }
                        }

                        if ($pgProgGrpParamRef->getTypeGrp() == 'ENV') {

                            $nbParametresEnvSit++;
                            if (isset($_POST['valeur' . $pgProgLotParamAn->getId()])) {
                                $valeur = $_POST['valeur' . $pgProgLotParamAn->getId()];
                            } else {
                                $valeur = null;
                            }
                            if (isset($_POST['uniteCode' . $pgProgLotParamAn->getId()])) {
                                $unite = $_POST['uniteCode' . $pgProgLotParamAn->getId()];
                            } else {
                                $unite = null;
                            }
                            if (isset($_POST['remarque' . $pgProgLotParamAn->getId()])) {
                                $remarque = $_POST['remarque' . $pgProgLotParamAn->getId()];
                            } else {
                                $remarque = null;
                            }

                            $tabStatut = array();
                            $tabStatut['ko'] = 0;
                            $tabStatut['statut'] = 0;
                            $tabStatut['libelle'] = null;
                            $parametre = $pgProgLotParamAn->getCodeParametre()->getCodeparametre();
                            $inSitu = 1;


                            if (strlen($valeur) > 0) {
                                $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                $tabStatut = $this->_controleVraisemblance($parametre, $valeur, $remarque, $unite, $inSitu, $pgSandreFraction, $tabStatut);
                                $okControleVraisemblance = $okControleVraisemblance + $tabStatut['ko'];
                            }

                            $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                            if (strlen($valeur) > 0) {
                                if (!$pgCmdMesureEnv) {
                                    $pgCmdMesureEnv = new PgCmdMesureEnv();
                                    $pgCmdMesureEnv->setDateMes($today);
                                    $pgCmdMesureEnv->setPrelev($pgCmdPrelev);
                                    $pgCmdMesureEnv->setParamProg($pgProgLotParamAn);
                                    $pgCmdMesureEnv->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                }
                                $pgCmdMesureEnv->setResultat($valeur);
                                $pgCmdMesureEnv->setCodeRemarque($remarque);
                                $pgCmdMesureEnv->setCodeMethode('0');
                                $pgCmdMesureEnv->setCodeStatut($tabStatut['statut']);
                                $pgCmdMesureEnv->setLibelleStatut($tabStatut['libelle']);
                                if ($unite) {
                                    $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($unite);
                                    if ($pgSandreUnites) {
                                        $pgCmdMesureEnv->setCodeUnite($pgSandreUnites);
                                    } else {
                                        $pgCmdMesureEnv->setCodeUnite(null);
                                    }
                                } else {
                                    $pgCmdMesureEnv->setCodeUnite(null);
                                }
                                $emSqe->persist($pgCmdMesureEnv);
                            } else {
                                if ($remarque == '0') {
                                    if (!$pgCmdMesureEnv) {
                                        $pgCmdMesureEnv = new PgCmdMesureEnv();
                                        $pgCmdMesureEnv->setDateMes($today);
                                        $pgCmdMesureEnv->setPrelev($pgCmdPrelev);
                                        $pgCmdMesureEnv->setParamProg($pgProgLotParamAn);
                                        $pgCmdMesureEnv->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                    }
                                    $pgCmdMesureEnv->setResultat(null);
                                    $pgCmdMesureEnv->setCodeRemarque($remarque);
                                    $pgCmdMesureEnv->setCodeMethode('0');
                                    $pgCmdMesureEnv->setCodeStatut(1);
                                    $pgCmdMesureEnv->setLibelleStatut('Valeur absente');
                                    if ($unite) {
                                        $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($unite);
                                        if ($pgSandreUnites) {
                                            $pgCmdMesureEnv->setCodeUnite($pgSandreUnites);
                                        } else {
                                            $pgCmdMesureEnv->setCodeUnite(null);
                                        }
                                    } else {
                                        $pgCmdMesureEnv->setCodeUnite(null);
                                    }
                                    $emSqe->persist($pgCmdMesureEnv);
                                } else {
                                    if ($pgCmdMesureEnv) {
                                        $emSqe->remove($pgCmdMesureEnv);
                                    }
                                }
                            }
                        }

                        if ($pgProgGrpParamRef->getTypeGrp() == 'ANA') {
                            $nbParametresAna++;
                        }
                    }
                }


                $emSqe->flush();

// }
            }
        }


        if ($okControleVraisemblance != 0) {
            $nbErreurs++;
        } else {
            $okControlesSpecifiques = $this->_controlesSpecifiques($pgCmdPrelev);
        }
        if ($okControlesSpecifiques != 0) {
            $nbErreurs++;
        }


        //return new Response ('$okControleVraisemblance : ' . $okControleVraisemblance . '  $okControlesSpecifiques :  ' . $okControlesSpecifiques . '   $nbErreurs : ' . $nbErreurs);
        $okPhase = false;

        if ($nbErreurs == 0) {
            $nbSaisieParametresEnv = 0;
            $nbSaisieParametresSit = 0;
            $nbSaisieParametresAna = 0;
            $nbParametresEnvSit = 0;
            $nbParametresAna = 0;
            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                $prestataire = $pgProgLotGrparAn->getPrestaDft();
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
                $pgProgPeriodes = $pgProgLotPeriodeProg->getPeriodAn()->getPeriode();
                if ($pgProgLotGrparAn->getvalide() == 'O') {
                    $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                    foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                        if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire)) {

                            if ($pgProgGrpParamRef->getTypeGrp() == 'ENV') {
                                // echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                $nbParametresEnvSit++;
                                $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                if ($pgCmdMesureEnv) {
                                    $nbSaisieParametresEnv ++;
                                }
                            }

                            if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {
                                if ($prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire) {
                                    //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                    $nbParametresEnvSit++;
                                    $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                    if ($pgCmdAnalyse) {
                                        $nbSaisieParametresSit ++;
                                    }
                                }
                            }



                            if ($pgProgGrpParamRef->getTypeGrp() == 'ANA') {
                                //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                $nbParametresAna++;
                                $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                if ($pgCmdAnalyse) {
                                    $nbSaisieParametresAna ++;
                                }
                            }
                        }
                    }
                }
            }



            // $nbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
            echo ('$nbCmdMesureEnv : ' . $nbSaisieParametresEnv . ' </br>');
            // $nbCmdSit = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
            echo ('$nbCmdSit : ' . $nbSaisieParametresSit . ' </br>');
            $nbSaisieParametresEnvSit = $nbSaisieParametresEnv + $nbSaisieParametresSit;
            echo ('$nbSaisieParametresEnvSit : ' . $nbSaisieParametresEnvSit . ' </br>');
            //$nbSaisieParametresAna = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
            echo ('$nbSaisieParametresAna : ' . $nbSaisieParametresAna . ' </br>');
            $nbParametresTotal = $nbParametresEnvSit + $nbParametresAna;
            echo ('$nbParametresTotal : ' . $nbParametresTotal . ' </br>');
            $nbSaisieParametresTotal = $nbSaisieParametresEnvSit + $nbSaisieParametresAna;
            echo ('$nbSaisieParametresTotal : ' . $nbSaisieParametresTotal . ' </br>');

            // return new Response('');

            if ($nbParametresTotal == $nbSaisieParametresTotal) {
                $okPhase = true;
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M30');
                $pgCmdPrelev->setRealise('O');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            } else {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M20');
                $pgCmdPrelev->setRealise('N');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            }
        }

//        
//        if ($nbErreurs == 0) {
//            $nbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
//            $nbCmdMesureSit = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
//            $nbSaisieParametresEnvSit = $nbCmdMesureEnv + $nbCmdMesureSit;
//            $nbSaisieParametresAna = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
//            $nbParametresTotal = $nbParametresEnvSit + $nbParametresAna;
//            $nbSaisieParametresTotal = $nbSaisieParametresEnvSit + $nbSaisieParametresAna;
//            if ($nbParametresTotal == $nbSaisieParametresTotal) {
//                $okPhase = true;
//                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M30');
//                $pgCmdPrelev->setRealise('O');
//                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
//            } else {
//                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M20');
//                $pgCmdPrelev->setRealise('N');
//                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
//            }
//        }

        $pgCmdPrelev->setDatePrelev($datePrel);
        $emSqe->persist($pgCmdPrelev);
        $emSqe->flush();

//  return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//return new Response ('');
        if ($nbErreurs == 0) {
            if ($okPhase) {
                return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_station_saisir_env_situ', array(
                                    'prelevId' => $pgCmdPrelev->getId(),
                                    'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                    'stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                    'maj' => 'V',
                )));
            } else {
                return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_stations', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                    'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                    'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
            }
        } else {
            return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_station_saisir_env_situ', array(
                                'prelevId' => $pgCmdPrelev->getId(),
                                'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                'stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                'maj' => 'M',
            )));
        }
    }

    public function lotPeriodeStationSaisirAnaAction($prelevId = null, $periodeAnId = null, $stationId = null, $maj = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationSaisirAna');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgProgUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $repoPgSandreUnitesPossiblesParamsEnv = $emSqe->getRepository('AeagSqeBundle:PgSandreUnitesPossiblesParamsEnv');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAnOrderByGrparAn($pgProgLotStationAn, $pgProgLotPeriodeAn);

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }

        $tabGroupes = array();
        $nbGroupes = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            if ($pgProgLot->getDelaiPrel()) {
                $dateFin = clone($pgProgLotPeriodeProg->getPeriodan()->getPeriode()->getDateDeb());
                $delai = $pgProgLot->getDelaiPrel();
                $dateFin->add(new \DateInterval('P' . $delai . 'D'));
            } else {
                $dateFin = $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getDateFin();
            }
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            $prestataire = $pgProgLotGrparAn->getPrestaDft();
            if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $tabGroupes[$nbGroupes]['grparAn'] = $pgProgLotGrparAn;
                $tabGroupes[$nbGroupes]['correct'] = 0;
                $tabGroupes[$nbGroupes]['warning'] = 0;
                $tabGroupes[$nbGroupes]['erreur'] = 0;
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                $tabParamAns = array();
                $nbParamAns = 0;
                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                    $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($pgProgLotParamAn->getCodeUnite());
                    if ($pgSandreUnites) {
                        $tabParamAns[$nbParamAns]['unite'] = $pgSandreUnites;
                    } else {
                        $tabParamAns[$nbParamAns]['unite'] = null;
                    }

                    if ($pgProgLotParamAn->getCodeFraction()) {
                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                        $tabParamAns[$nbParamAns]['fraction'] = $pgSandreFraction;
                    } else {
                        $tabParamAns[$nbParamAns]['fraction'] = null;
                    }

                    $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                    if (count($pgCmdAnalyses) > 0) {
                        foreach ($pgCmdAnalyses as $pgCmdAnalyse) {
                            if ($pgCmdAnalyse->getCodeStatut() == '0') {
                                $tabGroupes[$nbGroupes]['correct'] = $tabGroupes[$nbGroupes]['correct'] + 1;
                            } elseif ($pgCmdAnalyse->getCodeStatut() == '1') {
                                $tabGroupes[$nbGroupes]['warning'] = $tabGroupes[$nbGroupes]['warning'] + 1;
                            } elseif ($pgCmdAnalyse->getCodeStatut() == '2') {
                                $tabGroupes[$nbGroupes]['erreur'] = $tabGroupes[$nbGroupes]['erreur'] + 1;
                            }
                            $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = $pgCmdAnalyse;
                            $tabParamAns[$nbParamAns]['unite'] = $pgCmdAnalyse->getCodeUnite();
                            $pgSandreUnitesPossiblesParamsEnv = $repoPgSandreUnitesPossiblesParamsEnv->getPgSandreUnitesPossiblesParamsEnvByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                            if ($pgSandreUnitesPossiblesParamsEnv) {
                                $tabParamAns[$nbParamAns]['valeurs'] = $pgSandreUnitesPossiblesParamsEnv;
                            } else {
                                $tabParamAns[$nbParamAns]['valeurs'] = null;
                            }
                            $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                            if ($pgProgUnitesPossiblesParam) {
                                $tabParamAns[$nbParamAns]['unites'] = $pgProgUnitesPossiblesParam;
                            } else {
                                $tabParamAns[$nbParamAns]['unites'] = null;
                            }

                            if ($pgProgLotParamAn->getCodeFraction()) {
                                $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                $tabParamAns[$nbParamAns]['fraction'] = $pgSandreFraction;
                            } else {
                                $tabParamAns[$nbParamAns]['fraction'] = null;
                            }
                            $nbParamAns++;
                        }
                    } else {
                        $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = null;

                        $pgSandreUnitesPossiblesParamsEnv = $repoPgSandreUnitesPossiblesParamsEnv->getPgSandreUnitesPossiblesParamsEnvByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                        if ($pgSandreUnitesPossiblesParamsEnv) {
                            $tabParamAns[$nbParamAns]['valeurs'] = $pgSandreUnitesPossiblesParamsEnv;
                        } else {
                            $tabParamAns[$nbParamAns]['valeurs'] = null;
                        }
                        $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                        if ($pgProgUnitesPossiblesParam) {
                            $tabParamAns[$nbParamAns]['unites'] = $pgProgUnitesPossiblesParam;
                        } else {
                            $tabParamAns[$nbParamAns]['unites'] = null;
                        }


                        $nbParamAns++;
                    }

//                    if ($pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '5479') {
//                        return new Response(\Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//                    }
                }

                $tabGroupes[$nbGroupes]['paramAns'] = $tabParamAns;
                $nbGroupes++;
// }
            }
        }

//return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//return new Response ('');

        if ($maj == 'C') {
            return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeStationVoirAna.html.twig', array(
                        'user' => $pgProgWebUser,
                        'lotan' => $pgProgLotAn,
                        'station' => $pgRefStationMesure,
                        'periodeAn' => $pgProgLotPeriodeAn,
                        'dateFin' => $dateFin,
                        'demande' => $pgCmdPrelev->getDemande(),
                        'cmdPrelev' => $pgCmdPrelev,
                        'groupes' => $tabGroupes,
                        'maj' => $maj,));
        } else {
            return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeStationSaisirAna.html.twig', array(
                        'user' => $pgProgWebUser,
                        'lotan' => $pgProgLotAn,
                        'station' => $pgRefStationMesure,
                        'periodeAn' => $pgProgLotPeriodeAn,
                        'dateFin' => $dateFin,
                        'demande' => $pgCmdPrelev->getDemande(),
                        'cmdPrelev' => $pgCmdPrelev,
                        'groupes' => $tabGroupes,
                        'maj' => $maj,));
        }
    }

    public function lotPeriodeStationResultatAnaAction($prelevId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationResultatAna');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgProgUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $now = date('Ymd');
        $today = new \DateTime($now);

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdDemande = $pgCmdPrelev->getDemande();
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);
        $nbErreurs = 0;
        $okControlesSpecifiques = 0;
        $okControleVraisemblance = 0;
        $nbParametresEnvSit = 0;
        $nbParametresAna = 0;

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }

        if (isset($_POST['datePrel'])) {
            $dateSaisie = str_replace('/', '-', $_POST['datePrel']);
            $datePrel = new \DateTime($dateSaisie);
        } else {
            $datePrel = null;
        }

        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            $prestataire = $pgProgLotGrparAn->getPrestaDft();
            if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);

                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire)) {
                        $nbParametresAna++;
                        if (isset($_POST['valeur' . $pgProgLotParamAn->getId()])) {
                            $valeur = $_POST['valeur' . $pgProgLotParamAn->getId()];
                        } else {
                            $valeur = null;
                        }
                        if (isset($_POST['uniteCode' . $pgProgLotParamAn->getId()])) {
                            $unite = $_POST['uniteCode' . $pgProgLotParamAn->getId()];
                            $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($unite);
                        } else {
                            $unite = null;
                            $pgSandreUnites = null;
                        }
                        if (isset($_POST['remarque' . $pgProgLotParamAn->getId()])) {
                            $remarque = $_POST['remarque' . $pgProgLotParamAn->getId()];
                        } else {
                            $remarque = null;
                        }

                        $tabStatut = array();
                        $tabStatut['ko'] = 0;
                        $tabStatut['statut'] = 0;
                        $tabStatut['libelle'] = null;
                        $parametre = $pgProgLotParamAn->getCodeParametre()->getCodeparametre();
                        $inSitu = 0;

                        if (strlen($valeur) > 0) {
                            $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                            $tabStatut = $this->_controleVraisemblance($parametre, $valeur, $remarque, $unite, $inSitu, $pgSandreFraction, $tabStatut);
                            $okControleVraisemblance = $okControleVraisemblance + $tabStatut['ko'];
                        }

                        $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                        if ($pgProgLotParamAn->getCodeFraction()) {
                            $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                        } else {
                            $pgSandreFraction = null;
                        }
                        if (strlen($valeur) > 0) {
                            if (!$pgCmdAnalyse) {
                                $pgCmdPrelevPc = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelevNumOrdre($pgCmdPrelev, 1);
                                if (!$pgCmdPrelevPc) {
                                    $pgCmdPrelevPc = new PgCmdPrelevPc();
                                    $pgCmdPrelevPc->setPrelev($pgCmdPrelev);
                                    $pgCmdPrelevPc->setNumOrdre(1);
                                    $emSqe->persist($pgCmdPrelevPc);
                                    $emSqe->flush();
                                }
                                $pgCmdAnalyse = new PgCmdAnalyse();
                                $pgCmdAnalyse->setNumOrdre(1);
                                $pgCmdAnalyse->setLieuAna('2');
                                $pgCmdAnalyse->setPrelevId($pgCmdPrelev->getId());
                                $pgCmdAnalyse->setParamProg($pgProgLotParamAn);
                                $pgCmdAnalyse->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                if ($pgSandreFraction) {
                                    $pgCmdAnalyse->setCodeFraction($pgSandreFraction);
                                }
                            }
                            $pgCmdAnalyse->setDateAna($today);
                            $pgCmdAnalyse->setResultat($valeur);
                            $pgCmdAnalyse->setCodeRemarque($remarque);
                            $pgCmdAnalyse->setCodeMethode('0');
                            $pgCmdAnalyse->setCodeStatut($tabStatut['statut']);
                            $pgCmdAnalyse->setlibelleStatut($tabStatut['libelle']);
                            if ($pgSandreUnites) {
                                $pgCmdAnalyse->setCodeUnite($pgSandreUnites);
                            }
                            $emSqe->persist($pgCmdAnalyse);
                        } else {
                            if ($pgCmdAnalyse) {
                                $emSqe->remove($pgCmdAnalyse);
                            }
                        }
                    }
                }

                $emSqe->flush();



// }
            } else {
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire)) {
                        $nbParametresEnvSit++;
                    }
                }
            }
        }


        if ($okControleVraisemblance != 0) {
            $nbErreurs++;
        } else {
            $okControlesSpecifiques = $this->_controlesSpecifiques($pgCmdPrelev);
        }
        if ($okControlesSpecifiques != 0) {
            $nbErreurs++;
        }

        //return new Response ('$okControleVraisemblance : ' . $okControleVraisemblance . '  $okControlesSpecifiques :  ' . $okControlesSpecifiques . '   $nbErreurs : ' . $nbErreurs);

        $ok = false;

        if ($nbErreurs == 0) {
            $nbSaisieParametresEnv = 0;
            $nbSaisieParametresSit = 0;
            $nbSaisieParametresAna = 0;
            $nbParametresEnvSit = 0;
            $nbParametresAna = 0;
            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                $prestataire = $pgProgLotGrparAn->getPrestaDft();
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
                $pgProgPeriodes = $pgProgLotPeriodeProg->getPeriodAn()->getPeriode();
                if ($pgProgLotGrparAn->getvalide() == 'O') {
                    $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                    foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                        if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire)) {

                            if ($pgProgGrpParamRef->getTypeGrp() == 'ENV') {
                                if ($prestataire == $pgProgLotParamAn->getPrestataire()) {
                                    //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                    $nbParametresEnvSit++;
                                    $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                    if ($pgCmdMesureEnv) {
                                        $nbSaisieParametresEnv++;
                                    }
                                }
                            }

                            if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {
                                if ($prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire) {
                                    //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                    $nbParametresEnvSit++;
                                    $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                    if (count($pgCmdAnalyses) > 0) {
                                        $nbSaisieParametresSit++;
                                    }
                                }
                            }



                            if ($pgProgGrpParamRef->getTypeGrp() == 'ANA') {
                                if ($prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire) {
                                    //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                    $nbParametresAna++;
                                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
                                    $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                    if (count($pgCmdAnalyses) > 0) {
                                        $nbSaisieParametresAna++;
                                    }
                                }
                            }
                        }
                    }
                }
            }



            // $nbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
            echo ('$nbCmdMesureEnv : ' . $nbSaisieParametreEnv . ' </br>');
            // $nbCmdSit = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
            echo ('$nbCmdSit : ' . $nbSaisieParametreSit . ' </br>');
            $nbSaisieParametresEnvSit = $nbSaisieParametreEnv + $nbSaisieParametreSit;
            echo ('$nbSaisieParametresEnvSit : ' . $nbSaisieParametresEnvSit . ' </br>');
            //$nbSaisieParametresAna = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
            echo ('$nbSaisieParametresAna : ' . $nbSaisieParametresAna . ' </br>');
            $nbParametresTotal = $nbParametresEnvSit + $nbParametresAna;
            echo ('$nbParametresTotal : ' . $nbParametresTotal . ' </br>');
            $nbSaisieParametresTotal = $nbSaisieParametresEnvSit + $nbSaisieParametresAna;
            echo ('$nbSaisieParametresTotal : ' . $nbSaisieParametresTotal . ' </br>');

            // return new Response('');

            if ($nbParametresTotal == $nbSaisieParametresTotal) {
                $okPhase = true;
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M30');
                $pgCmdPrelev->setRealise('O');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            } else {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M20');
                $pgCmdPrelev->setRealise('N');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            }
        }

//        if ($nbErreurs == 0) {
//            $nbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
//            echo ('$nbCmdMesureEnv : ' . $nbCmdMesureEnv . ' </br>');
//            $nbCmdSit = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
//            echo ('$nbCmdSit : ' . $nbCmdSit . ' </br>');
//            $nbSaisieParametresEnvSit = $nbCmdMesureEnv + $nbCmdSit;
//            echo ('$nbSaisieParametresEnvSit : ' . $nbSaisieParametresEnvSit . ' </br>');
//            $nbSaisieParametresAna = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
//            echo ('$nbSaisieParametresAna : ' . $nbSaisieParametresAna . ' </br>');
//            $nbParametresTotal = $nbParametresEnvSit + $nbParametresAna;
//            echo ('$nbParametresTotal : ' . $nbParametresTotal . ' </br>');
//            $nbSaisieParametresTotal = $nbSaisieParametresEnvSit + $nbSaisieParametresAna;
//            echo ('$nbSaisieParametresTotal : ' . $nbSaisieParametresTotal . ' </br>');
//
//            //return new Response ('');
//
//            if ($nbParametresTotal == $nbSaisieParametresTotal) {
//                $ok = true;
//                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M30');
//                $pgCmdPrelev->setRealise('O');
//                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
//            } else {
//                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M20');
//                $pgCmdPrelev->setRealise('N');
//                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
//            }
//        }
        //return new Response ('prelev : ' . $pgCmdPrelev->getid() . '  phase :  ' . $pgProgPhases->getCodePhase());

        $pgCmdPrelev->setDatePrelev($datePrel);
        $emSqe->persist($pgCmdPrelev);
        $emSqe->flush();

        //return new Response('$nbParametresTotal  : ' . $nbParametresTotal . ' $nbParametresEnvSit : ' . $nbParametresEnvSit . '  $nbParametresAna : ' . $nbParametresAna . '  $nbSaisieParametresTotal :  ' . $nbSaisieParametresTotal);
//  return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//return new Response ('');

        if ($nbErreurs == 0) {
            if ($ok) {
                return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_station_saisir_ana', array(
                                    'prelevId' => $pgCmdPrelev->getId(),
                                    'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                    'stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                    'maj' => 'V',
                )));
            } else {
                return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_stations', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                    'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                    'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
            }
        } else {
            return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_station_saisir_ana', array(
                                'prelevId' => $pgCmdPrelev->getId(),
                                'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                'stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                'maj' => 'M',
            )));
        }
    }

    public function lotPeriodeStationValiderAction($prelevId = null, $periodeAnId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationValider');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdDemande = $pgCmdPrelev->getDemande();

        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R45');

        if (!$pgCmdPrelev->getFichierRps()) {
            $pgCmdFichiersRps = new PgCmdFichiersRps();
            $pgCmdFichiersRps->setDemande($pgCmdPrelev->getDemande());
            $pgCmdFichiersRps->setNomFichier('SAISIE_' . $pgCmdPrelev->getId());
            $pgCmdFichiersRps->setDateDepot(new \DateTime());
            $pgCmdFichiersRps->setTypeFichier('SAI');
            $pgCmdFichiersRps->setUser($pgProgWebUser);
            $pgCmdFichiersRps->setSuppr('N');
        } else {
            $pgCmdFichiersRps = $pgCmdPrelev->getFichierRps();
        }
        $pgCmdFichiersRps->setPhaseFichier($pgProgPhases);
        $emSqe->persist($pgCmdFichiersRps);
        $pgCmdPrelev->setFichierRps($pgCmdFichiersRps);

        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');
        $pgCmdPrelev->setPhaseDmd($pgProgPhases);
        $emSqe->persist($pgCmdPrelev);

        $emSqe->flush();

        $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
        $nbPrelevs = count($pgCmdPrelevs);
        $nbPrelevM40 = 0;
        foreach ($pgCmdPrelevs as $pgCmdPrelev) {
            if ($pgCmdPrelev->getPhaseDmd() == 'M40') {
                $nbPrelevM40++;
            }
        }
        if ($nbPrelevs == $nbPrelevM40) {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D40');
            $pgCmdDemande->setPhaseDemande($pgProgPhases);
            $emSqe->persist($pgCmdDemande);
            $emSqe->flush();
        } else {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D30');
            $pgCmdDemande->setPhaseDemande($pgProgPhases);
            $emSqe->persist($pgCmdDemande);
            $emSqe->flush();
        }

//  return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//return new Response ('');

        return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_stations', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                            'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                            'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
    }

    public function lotPeriodeStationDeValiderAction($prelevId = null, $periodeAnId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationValider');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdDemande = $pgCmdPrelev->getDemande();
        $pgProgLotAn = $pgCmdDemande->getLotan();

        $autrePgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeAn->getPeriode());
        $tabAutreDemandes = array();
        $i = 0;
        $tabDemandes[$i]['CmdDemande'] = $pgCmdDemande;
        $tabDemandes[$i]['CmdPrelev'] = $pgCmdPrelev;
        $i++;
        foreach ($autrePgCmdDemandes as $autrePgCmdDemande) {
            if ($autrePgCmdDemande != $pgCmdDemande) {
                $autrePgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($autrePgCmdDemande);
                foreach ($autrePgCmdPrelevs as $autrePgCmdPrelev) {
                    if ($autrePgCmdPrelev->getStation() == $pgCmdPrelev->getStation()) {
                        $tabDemandes[$i]['CmdDemande'] = $autrePgCmdDemande;
                        $tabDemandes[$i]['CmdPrelev'] = $autrePgCmdPrelev;
                        $i++;
                    }
                }
            }
        }

        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R10');

        for ($i = 0; $i < count($tabDemandes); $i++) {
            $pgCmdDemande = $tabDemandes[$i]['CmdDemande'];
            $pgCmdPrelev = $tabDemandes[$i]['CmdPrelev'];


            if (!$pgCmdPrelev->getFichierRps()) {
                $pgCmdFichiersRps = new PgCmdFichiersRps();
                $pgCmdFichiersRps->setDemande($pgCmdPrelev->getDemande());
                $pgCmdFichiersRps->setNomFichier('SAISIE_' . $pgCmdPrelev->getId());
                $pgCmdFichiersRps->setDateDepot(new \DateTime());
                $pgCmdFichiersRps->setTypeFichier('SAI');
                $pgCmdFichiersRps->setUser($pgProgWebUser);
                $pgCmdFichiersRps->setSuppr('N');
            } else {
                $pgCmdFichiersRps = $pgCmdPrelev->getFichierRps();
            }
            $pgCmdFichiersRps->setPhaseFichier($pgProgPhases);
            $emSqe->persist($pgCmdFichiersRps);
            $pgCmdPrelev->setFichierRps($pgCmdFichiersRps);

            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M30');
            $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            $emSqe->persist($pgCmdPrelev);

            $emSqe->flush();

            $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
            $nbPrelevM40 = 0;
            foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                if ($pgCmdPrelev->getPhaseDmd() == 'M40') {
                    $nbPrelevM40++;
                }
            }
            if ($nbPrelevM40 == 0) {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D10');
                $pgCmdDemande->setPhaseDemande($pgProgPhases);
                $emSqe->persist($pgCmdDemande);
                $emSqe->flush();
            } else {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D30');
                $pgCmdDemande->setPhaseDemande($pgProgPhases);
                $emSqe->persist($pgCmdDemande);
                $emSqe->flush();
            }
        }

//  return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//return new Response ('');

        return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_stations', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                            'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                            'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
    }

    public function lotPeriodeStationTelechargerAction($prelevId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationTelecharger');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');

        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdFichiersRps = $pgCmdPrelev->getFichierRps();

        $chemin = '/base/extranet/Transfert/Sqe/csv';
        $fichier = 'Saisie-des-donnees-prelev-' . $pgCmdPrelev->getId() . '.csv';
        $fullFileName = $chemin . '/' . $fichier;
        $ext = strtolower(pathinfo($fullFileName, PATHINFO_EXTENSION));
        if (file_exists($fullFileName)) {
            unlink($fullFileName);
        }
        $fichier_csv = fopen($fullFileName, 'w+');
        // Entete
        $ligne = array('Année', 'Code station', 'Nom station', 'Code masse d\'eau', 'Code du prélèvement',
            'Siret préleveur', 'Nom préleveur', 'Date-heure du prélèvement', 'Code du paramètre',
            'Libellé court paramètre', 'Nom paramètre', 'Zone verticale', 'Profondeur', 'Code support',
            'Nom support', 'Code fraction', 'Nom fraction', 'Code méthode', 'Nom méthode', 'Code remarque',
            'Résultat', 'Valeur textuelle', 'Code unité', 'libellé unité', 'symbole unité', 'LQ', 'Siret labo',
            'Nom labo', 'Code réseau', 'Nom réseau', 'Siret prod', 'Nom prod', 'Commentaire');
        for ($i = 0; $i < count($ligne); $i++) {
            $ligne[$i] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$i]);
        }

        fputcsv($fichier_csv, $ligne, ';');
        $donneesBrutes = $repoPgCmdPrelev->getDonneesBrutes($pgCmdPrelev->getFichierRps());
        foreach ($donneesBrutes as $ligne) {
            foreach ($ligne as $j => $value) {
                $ligne[$j] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$j]);
            }
            fputcsv($fichier_csv, $ligne, ';');
        }
        fclose($fichier_csv);

        //return new Response ('fichier : ' . $chemin . '/' . $fichier . ' ext : ' . $ext. ' size : ' . filesize($chemin . '/' . $fichier));

        \header("Cache-Control: no-cahe, must-revalidate");
        \header('Content-Type', 'text/' . $ext);
        \header('Content-disposition: attachment; filename="' . $fichier . '"');
        \header('Expires: 0');
        \header('Content-Length: ' . filesize($chemin . '/' . $fichier));
        readfile($chemin . '/' . $fichier);
        exit();
    }

    public function lotPeriodeLacsAction($periodeAnId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeLacs');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgPrestaTypfic = $emSqe->getRepository('AeagSqeBundle:PgProgPrestaTypfic');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgGrparRefZoneVert = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefZoneVert');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());


        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }

        $tabStations = array();
        $i = 0;
        $j = 0;
        $k = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $prestataire = $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft();
            if ($user->hasRole('ROLE_ADMINSQE') or ( $userPrestataire == $prestataire)) {
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
                    if (!$user->hasRole('ROLE_ADMINSQE')) {
                        $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeByLotanPrestatairePeriode($pgProgLotAn, $prestataire, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                        $pgProgLotStationAn = $pgProgLotPeriodeProg->getStationAn();
                        $tabStations[$i]['stationAn'] = $pgProgLotStationAn;
                        $tabStations[$i]['station'] = $pgProgLotStationAn->getStation();
                        $tabStations[$i]['prestataire'] = $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft();
                        $tabStations[$i]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $pgProgLotPeriodeProg->getStationAn()->getStation()->getCode()) . '.pdf';
                        $tabStations[$i]['cmdDemande'] = $pgCmdDemande;
                        $tabStations[$i]['cmdPrelev'] = null;
                        $tabStations[$i]['autreCmdDemande'] = null;
                        $tabStations[$i]['autreCmdPrelev'] = null;
                        $autrePgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                        foreach ($autrePgCmdDemandes as $autrePgCmdDemande) {
                            if ($autrePgCmdDemande != $pgCmdDemande) {
                                $tabStations[$i]['autreCmdDemande'] = $autrePgCmdDemande;
                                $tabStations[$i]['autreCmdPrelev'] = null;
                            }
                        }
                        //echo('station : ' . $pgProgLotStationAn->getStation()->getOuvFoncId() . ' ' . $pgProgLotStationAn->getStation()->getCode()    . ' prestataire : ' . $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft()->getAdrCorId() . ' ' . $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft()->getAncnum() . '</br>' );
                        $i++;
                    } else {
                        $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                        foreach ($pgCmdDemandes as $pgCmdDemande) {
                            $pgProgLotStationAn = $pgProgLotPeriodeProg->getStationAn();
                            $tabStations[$i]['stationAn'] = $pgProgLotStationAn;
                            $tabStations[$i]['station'] = $pgProgLotStationAn->getStation();
                            $tabStations[$i]['prestataire'] = $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft();
                            $tabStations[$i]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $pgProgLotPeriodeProg->getStationAn()->getStation()->getCode()) . '.pdf';
                            $tabStations[$i]['cmdDemande'] = $pgCmdDemande;
                            $tabStations[$i]['cmdPrelev'] = null;
                            $tabStations[$i]['autreCmdDemande'] = null;
                            $tabStations[$i]['autreCmdPrelev'] = null;
                            $autrePgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                            foreach ($autrePgCmdDemandes as $autrePgCmdDemande) {
                                if ($autrePgCmdDemande != $pgCmdDemande) {
                                    $tabStations[$i]['autreCmdDemande'] = $autrePgCmdDemande;
                                }
                            }
                            //echo('station : ' . $pgProgLotStationAn->getStation()->getOuvFoncId() . ' ' . $pgProgLotStationAn->getStation()->getCode()    . ' prestataire : ' . $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft()->getAdrCorId() . ' ' . $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft()->getAncnum() . '</br>' );
                            $i++;
                        }
                    }
                }
            }
        }

        for ($i = 0; $i < count($tabStations); $i++) {
            $pgProgLotStationAn = $tabStations[$i]['stationAn'];
            $station = $tabStations[$i]['station'];
            $pgCmdDemande = $tabStations[$i]['cmdDemande'];
            $j = 0;
            if ($pgCmdDemande) {
                $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($pgCmdDemande->getPrestataire(), $pgCmdDemande, $station, $pgProgLotPeriodeAn->getPeriode());
                $tabStations[$i]['nbPrelev'] = count($pgCmdPrelevs);
                $tabStations[$i]['saisieTerrain'] = 'N';
                $tabStations[$i]['nbParametresTerrain'] = 0;
                $tabStations[$i]['nbAutresParametresTerrain'] = 0;
                $tabStations[$i]['nbSaisisParametresTerrain'] = 0;
                $tabStations[$i]['nbSaisisParametresTerrainCorrect'] = 0;
                $tabStations[$i]['nbSaisisParametresTerrainIncorrect'] = 0;
                $tabStations[$i]['nbSaisisParametresTerrainErreur'] = 0;

                $pgProgPrestaTypfic = $repoPgProgPrestaTypfic->getPgProgPrestaTypficByCodeMilieu($pgProgTypeMilieu, $pgCmdDemande->getPrestataire());
                if ($pgProgPrestaTypfic) {
                    $NbProgLotParamAn = 0;
                    $NbAutresProgLotParamAn = 0;
                    foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                        if ($tabStations[$i]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                            if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV') {
                                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                    if ($pgCmdDemande->getPrestataire()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                        $NbProgLotParamAn++;
                                        $tabStations[$i]['nbParametresTerrain'] ++;
                                    }
                                    if ($pgCmdDemande->getPrestataire()->getAdrCorId() != $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                        $NbAutresProgLotParamAn++;
                                        $tabStations[$i]['nbAutresParametresTerrain'] ++;
                                    }
                                }
                            }
                            if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT') {
                                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                    if ($pgCmdDemande->getPrestataire()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                        $NbProgLotParamAn++;
                                        if ($pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1301' or
                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1302' or
                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1303' or
                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1311' or
                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1312') {
                                            foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                                $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                                                foreach ($pgCmdPrelevPcs as $pgCmdPrelevPc) {
                                                    if ($pgCmdPrelevPc->getZoneVerticale()->getCodeZone() == '6') {
                                                        $tabStations[$i]['nbParametresTerrain'] ++;
                                                    }
                                                }
                                            }
                                        } else {
                                            $tabStations[$i]['nbParametresTerrain'] ++;
                                        }
                                    }
                                    if ($pgCmdDemande->getPrestataire()->getAdrCorId() != $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                        $NbAutresProgLotParamAn++;
                                        if ($pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1301' or
                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1302' or
                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1303' or
                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1311' or
                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1312') {
                                            foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                                $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                                                foreach ($pgCmdPrelevPcs as $pgCmdPrelevPc) {
                                                    if ($pgCmdPrelevPc->getZoneVerticale()->getCodeZone() == '6') {
                                                        $tabStations[$i]['nbAutresParametresTerrain'] ++;
                                                    }
                                                }
                                            }
                                        } else {
                                            $tabStations[$i]['nbAutresParametresTerrain'] ++;
                                        }
                                    }
                                }
                            }
                        }
                    }



                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                        $tabStations[$i]['cmdPrelev'][$j]['cmdPrelev'] = $pgCmdPrelev;
                        $tabStations[$i]['cmdPrelev'][$j]['saisieTerrain'] = 'N';
                        $tabStations[$i]['cmdPrelev'][$j]['nbParametresTerrain'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrain'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrainCorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrainIncorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrainErreur'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['saisieAnalyse'] = 'N';
                        $tabStations[$i]['cmdPrelev'][$j]['nbParametresAnalyse'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyse'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyseCorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyseIncorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyseErreur'] = 0;
                        $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                        $pgPrelevPc = $pgCmdPrelevPcs[0];
                        if ($pgPrelevPc) {
                            $tabStations[$i]['cmdPrelev'][$j]['prelevPc'] = $pgPrelevPc;
                        } else {
                            $tabStations[$i]['cmdPrelev'][$j]['prelevPc'] = null;
                        }
                        $tabStations[$i]['cmdPrelev'][$j]['nbParametresTerrain'] = $NbProgLotParamAn;

                        $tabStations[$i]['cmdPrelev'][$j]['nbAutresParametresTerrain'] = $NbAutresProgLotParamAn;

                        $NbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
                        $NbCmdMesureEnvCorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '0');
                        $NbCmdMesureEnvIncorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '1');
                        $NbCmdMesureEnvErreur = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '2');
                        $NbCmdAnalyse = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
                        $NbCmdAnalyseCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '0');
                        $NbCmdAnalyseIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '1');
                        $NbCmdAnalyseErreur = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '2');
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrain'] = $NbCmdMesureEnv + $NbCmdAnalyse;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrainCorrect'] = $NbCmdMesureEnvCorrect + $NbCmdAnalyseCorrect;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrainIncorrect'] = $NbCmdMesureEnvIncorrect + $NbCmdAnalyseIncorrect;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresTerrainErreur'] = $NbCmdMesureEnvErreur + $NbCmdAnalyseErreur;

                        $tabStations[$i]['nbSaisisParametresTerrain'] += $NbCmdMesureEnv + $NbCmdAnalyse;
                        $tabStations[$i]['nbSaisisParametresTerrainCorrect'] += $NbCmdMesureEnvCorrect + $NbCmdAnalyseCorrect;
                        $tabStations[$i]['nbSaisisParametresTerrainIncorrect'] += $NbCmdMesureEnvIncorrect + $NbCmdAnalyseIncorrect;
                        $tabStations[$i]['nbSaisisParametresTerrainErreur'] += $NbCmdMesureEnvErreur + $NbCmdAnalyseErreur;

                        if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() <= 'M40') {
                            if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_T') {
                                $tabStations[$i]['cmdPrelev'][$j]['saisieTerrain'] = 'O';
                                $tabStations[$i]['saisieTerrain'] = 'O';
                            }
                            if ($user->hasRole('ROLE_ADMINSQE') and $pgCmdPrelev->getPhaseDmd()->getcodePhase() < 'M50') {
                                $tabStations[$i]['cmdPrelev'][$j]['saisieTerrain'] = 'O';
                                $tabStations[$i]['saisieTerrain'] = 'O';
                            }
                        }

                        $NbProgLotParamAn = 0;
                        if ($NbProgLotParamAn == 0) {
                            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                                if ($tabStations[$i]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                                    $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                                    $prestataire = $pgProgLotGrparAn->getPrestaDft();
                                    if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                                        if ($pgPrelevPc) {
                                            $nbgProgGrparRefZoneVert = $repoPgProgGrparRefZoneVert->getNbPgProgGrparRefZoneVertByGrparRefPgSandreZoneVerticaleProspectee($pgProgLotGrparAn->getGrparRef(), $pgPrelevPc->getZoneVerticale());
                                        } else {
                                            $nbgProgGrparRefZoneVert = 0;
                                        }
                                        if ($nbgProgGrparRefZoneVert > 0) {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    $NbProgLotParamAn++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $tabStations[$i]['cmdPrelev'][$j]['nbParametresAnalyse'] = $NbProgLotParamAn;
                        if ($NbProgLotParamAn == 0) {
                            $nbp = 0;
                            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                                if ($tabStations[$i]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                                    $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                                    $prestataire = $pgProgLotGrparAn->getPrestaDft();
                                    if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                                        if ($pgPrelevPc) {
                                            $nbgProgGrparRefZoneVert = $repoPgProgGrparRefZoneVert->getNbPgProgGrparRefZoneVertByGrparRefPgSandreZoneVerticaleProspectee($pgProgLotGrparAn->getGrparRef(), $pgPrelevPc->getZoneVerticale());
                                        } else {
                                            $nbgProgGrparRefZoneVert = 0;
                                        }
                                        if ($nbgProgGrparRefZoneVert > 0) {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() != $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    $NbProgLotParamAn++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $tabStations[$i]['cmdPrelev'][$j]['nbAutresParametresAnalyse'] = $NbProgLotParamAn;
                        $NbCmdAnalyse = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
                        $NbCmdAnalyseCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '0');
                        $NbCmdAnalyseIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '1');
                        $NbCmdAnalyseErreur = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '2');
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyse'] = $NbCmdAnalyse;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyseCorrect'] = $NbCmdAnalyseCorrect;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyseIncorrect'] = $NbCmdAnalyseIncorrect;
                        $tabStations[$i]['cmdPrelev'][$j]['nbSaisisParametresAnalyseErreur'] = $NbCmdAnalyseErreur;
                        if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() <= 'M40') {
                            if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_A' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_T') {
                                $tabStations[$i]['cmdPrelev'][$j]['saisieAnalyse'] = 'O';
                            }
                            if ($user->hasRole('ROLE_ADMINSQE')) {
                                $tabStations[$i]['cmdPrelev'][$j]['saisieAnalyse'] = 'O';
                            }
                        }

                        if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M30') {
                            $tabStations[$i]['cmdPrelev'][$j]['valider'] = 'O';
                            $tabStations[$i]['valider'] = 'O';
                        } else {
                            $tabStations[$i]['cmdPrelev'][$j]['valider'] = 'N';
                            $tabStations[$i]['valider'] = 'N';
                        }
                        if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M40') {
                            $tabStations[$i]['cmdPrelev'][$j]['devalider'] = 'O';
                            $tabStations[$i]['devalider'] = 'O';
                            $tabStations[$i]['cmdPrelev'][$j]['saisieAnalyse'] = 'N';
                            $tabStations[$i]['cmdPrelev'][$j]['saisieTerrain'] = 'N';
                            $tabStations[$i]['saisieTerrain'] = 'N';
                        } else {
                            $tabStations[$i]['cmdPrelev'][$j]['devalider'] = 'N';
                            $tabStations[$i]['devalider'] = 'N';
                        }

                        $j++;
                    }
                }
            }
            $autrePgCmdDemande = $tabStations[$i]['autreCmdDemande'];
            $j = 0;
            if ($autrePgCmdDemande) {
                $autrePgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($autrePgCmdDemande->getPrestataire(), $autrePgCmdDemande, $station, $pgProgLotPeriodeAn->getPeriode());
                $tabStations[$i]['autreNbPrelev'] = count($autrePgCmdPrelevs);
                $tabStations[$i]['autreSaisieTerrain'] = 'N';
                $tabStations[$i]['autreNbParametresTerrain'] = 0;
                $tabStations[$i]['nbAutresParametresTerrain'] = 0;
                $tabStations[$i]['autreNbSaisisParametresTerrain'] = 0;
                $tabStations[$i]['autreNbSaisisParametresTerrainCorrect'] = 0;
                $tabStations[$i]['autreNbSaisisParametresTerrainIncorrect'] = 0;
                $tabStations[$i]['autreNbSaisisParametresTerrainErreur'] = 0;

                $pgProgPrestaTypfic = $repoPgProgPrestaTypfic->getPgProgPrestaTypficByCodeMilieu($pgProgTypeMilieu, $autrePgCmdDemande->getPrestataire());
                if ($pgProgPrestaTypfic) {
                    $autreNbProgLotParamAn = 0;
                    $NbAutresProgLotParamAn = 0;
                    foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                        if ($tabStations[$i]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                            if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV') {
                                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                    if ($autrePgCmdDemande->getPrestataire()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                        $autreNbProgLotParamAn++;
                                        $tabStations[$i]['autreNbParametresTerrain'] ++;
                                    }
                                }
                            }
                            if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT') {
                                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                    if ($autrePgCmdDemande->getPrestataire()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                        $autreNbProgLotParamAn++;
                                        if ($pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1301' or
                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1302' or
                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1303' or
                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1311' or
                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1312') {
                                            foreach ($autrePgCmdPrelevs as $autrePgCmdPrelev) {
                                                $autrePgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($autrePgCmdPrelev);
                                                foreach ($autrePgCmdPrelevPcs as $autrePgCmdPrelevPc) {
                                                    if ($autrePgCmdPrelevPc->getZoneVerticale()->getCodeZone() == '6') {
                                                        $tabStations[$i]['autreNbParametresTerrain'] ++;
                                                    }
                                                }
                                            }
                                        } else {
                                            $tabStations[$i]['autreNbParametresTerrain'] ++;
                                        }
                                    }
                                }
                            }
                        }
                    }



                    foreach ($autrePgCmdPrelevs as $autrePgCmdPrelev) {
                        $tabStations[$i]['cmdPrelev'][$j]['autreCmdPrelev'] = $autrePgCmdPrelev;
                        $tabStations[$i]['cmdPrelev'][$j]['autreSaisieTerrain'] = 'N';
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbParametresTerrain'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrain'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrainCorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrainIncorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrainErreur'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreSaisieAnalyse'] = 'N';
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbParametresAnalyse'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyse'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyseCorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyseIncorrect'] = 0;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyseErreur'] = 0;
                        $autrePgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($autrePgCmdPrelev);
                        $autrePgPrelevPc = $autrePgCmdPrelevPcs[0];
                        if ($pgPrelevPc) {
                            $tabStations[$i]['cmdPrelev'][$j]['autrePrelevPc'] = $autrePgPrelevPc;
                        } else {
                            $tabStations[$i]['cmdPrelev'][$j]['autrePrelevPc'] = null;
                        }
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbParametresTerrain'] = $autreNbProgLotParamAn;


                        $autreNbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($autrePgCmdPrelev);
                        $autreNbCmdMesureEnvCorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($autrePgCmdPrelev, '0');
                        $autreNbCmdMesureEnvIncorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($autrePgCmdPrelev, '1');
                        $autreNbCmdMesureEnvErreur = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($autrePgCmdPrelev, '2');
                        $autreNbCmdAnalyse = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($autrePgCmdPrelev);
                        $autreNbCmdAnalyseCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($autrePgCmdPrelev, '0');
                        $autreNbCmdAnalyseIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($autrePgCmdPrelev, '1');
                        $autreNbCmdAnalyseErreur = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($autrePgCmdPrelev, '2');
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrain'] = $autreNbCmdMesureEnv + $autreNbCmdAnalyse;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrainCorrect'] = $autreNbCmdMesureEnvCorrect + $autreNbCmdAnalyseCorrect;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrainIncorrect'] = $autreNbCmdMesureEnvIncorrect + $autreNbCmdAnalyseIncorrect;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresTerrainErreur'] = $autreNbCmdMesureEnvErreur + $autreNbCmdAnalyseErreur;

                        $tabStations[$i]['autreNbSaisisParametresTerrain'] += $autreNbCmdMesureEnv + $autreNbCmdAnalyse;
                        $tabStations[$i]['autreNbSaisisParametresTerrainCorrect'] += $autreNbCmdMesureEnvCorrect + $autreNbCmdAnalyseCorrect;
                        $tabStations[$i]['autreNbSaisisParametresTerrainIncorrect'] += $autreNbCmdMesureEnvIncorrect + $autreNbCmdAnalyseIncorrect;
                        $tabStations[$i]['autreNbSaisisParametresTerrainErreur'] += $autreNbCmdMesureEnvErreur + $autreNbCmdAnalyseErreur;

                        if ($autrePgCmdPrelev->getPhaseDmd()->getcodePhase() <= 'M40') {
                            if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_T') {
                                $tabStations[$i]['cmdPrelev'][$j]['autreSaisieTerrain'] = 'O';
                                $tabStations[$i]['autreSaisieTerrain'] = 'O';
                            }
                            if ($user->hasRole('ROLE_ADMINSQE') and $autrePgCmdPrelev->getPhaseDmd()->getcodePhase() < 'M50') {
                                $tabStations[$i]['cmdPrelev'][$j]['autreSaisieTerrain'] = 'O';
                                $tabStations[$i]['autreSaisieTerrain'] = 'O';
                            }
                        }

                        $autreNbProgLotParamAn = 0;
                        if ($autreNbProgLotParamAn == 0) {
                            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                                if ($tabStations[$i]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                                    $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                                    $prestataire = $pgProgLotGrparAn->getPrestaDft();
                                    if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                                        if ($autrePgPrelevPc) {
                                            $nbgProgGrparRefZoneVert = $repoPgProgGrparRefZoneVert->getNbPgProgGrparRefZoneVertByGrparRefPgSandreZoneVerticaleProspectee($pgProgLotGrparAn->getGrparRef(), $autrePgPrelevPc->getZoneVerticale());
                                        } else {
                                            $nbgProgGrparRefZoneVert = 0;
                                        }
                                        if ($nbgProgGrparRefZoneVert > 0) {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                if ($autrePgCmdPrelev->getprestaPrel()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    $autreNbProgLotParamAn++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbParametresAnalyse'] = $autreNbProgLotParamAn;
                        if ($autreNbProgLotParamAn == 0) {
                            $nbp = 0;
                            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                                if ($tabStations[$i]['station']->getOuvFoncId() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                                    $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                                    $prestataire = $pgProgLotGrparAn->getPrestaDft();
                                    if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                                        if ($autrePgPrelevPc) {
                                            $nbgProgGrparRefZoneVert = $repoPgProgGrparRefZoneVert->getNbPgProgGrparRefZoneVertByGrparRefPgSandreZoneVerticaleProspectee($pgProgLotGrparAn->getGrparRef(), $autrePgPrelevPc->getZoneVerticale());
                                        } else {
                                            $nbgProgGrparRefZoneVert = 0;
                                        }
                                        if ($nbgProgGrparRefZoneVert > 0) {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                if ($autrePgCmdPrelev->getprestaPrel()->getAdrCorId() != $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    $autreNbProgLotParamAn++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbParametresAnalyse'] = $autreNbProgLotParamAn;
                        $autreNbCmdAnalyse = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($autrePgCmdPrelev);
                        $autreNbCmdAnalyseCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($autrePgCmdPrelev, '0');
                        $autreNbCmdAnalyseIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($autrePgCmdPrelev, '1');
                        $autreNbCmdAnalyseErreur = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($autrePgCmdPrelev, '2');
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyse'] = $autreNbCmdAnalyse;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyseCorrect'] = $autreNbCmdAnalyseCorrect;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyseIncorrect'] = $autreNbCmdAnalyseIncorrect;
                        $tabStations[$i]['cmdPrelev'][$j]['autreNbSaisisParametresAnalyseErreur'] = $autreNbCmdAnalyseErreur;
                        if ($autrePgCmdPrelev->getPhaseDmd()->getcodePhase() <= 'M40') {
                            if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_A' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_T') {
                                $tabStations[$i]['cmdPrelev'][$j]['autreSaisieAnalyse'] = 'O';
                            }
                            if ($user->hasRole('ROLE_ADMINSQE')) {
                                $tabStations[$i]['cmdPrelev'][$j]['autreSaisieAnalyse'] = 'O';
                            }
                        }

                        if ($autrePgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M30') {
                            $tabStations[$i]['cmdPrelev'][$j]['autreValider'] = 'O';
                        } else {
                            $tabStations[$i]['cmdPrelev'][$j]['autreValider'] = 'N';
                        }
                        if ($autrePgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M40') {
                            $tabStations[$i]['cmdPrelev'][$j]['autreDevalider'] = 'O';
                            $tabStations[$i]['cmdPrelev'][$j]['autreSaisieAnalyse'] = 'N';
                            $tabStations[$i]['cmdPrelev'][$j]['autreSaisieTerrain'] = 'N';
                            $tabStations[$i]['autreSaisieTerrain'] = 'N';
                        } else {
                            $tabStations[$i]['cmdPrelev'][$j]['autreDevalider'] = 'N';
                        }

                        $j++;
                    }
                }
            }
        }

//        \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response('');

        return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeLacs.html.twig', array(
                    'user' => $pgProgWebUser,
                    'typeMilieu' => $pgProgTypeMilieu,
                    'lotan' => $pgProgLotAn,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'stations' => $tabStations));
    }

    public function lotPeriodeLacSaisirEnvSituAction($demandeId = null, $periodeAnId = null, $stationId = null, $maj = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationSaisirEnvSitu');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgProgUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $repoPgSandreUnitesPossiblesParamsEnv = $emSqe->getRepository('AeagSqeBundle:PgSandreUnitesPossiblesParamsEnv');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeById($demandeId);
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAnOrderByGrparAn($pgProgLotStationAn, $pgProgLotPeriodeAn);

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }



        $tabGroupes = array();
        $nbGroupes = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            if ($pgProgLot->getDelaiPrel()) {
                $dateFin = clone($pgProgLotPeriodeProg->getPeriodan()->getPeriode()->getDateDeb());
                $delai = $pgProgLot->getDelaiPrel();
                $dateFin->add(new \DateInterval('P' . $delai . 'D'));
            } else {
                $dateFin = $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getDateFin();
            }
            $pgProgPeriodes = $pgProgLotPeriodeProg->getPeriodAn()->getPeriode();

            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            $prestataire = $pgProgLotGrparAn->getPrestaDft();
            $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
            if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV') {

// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $tabGroupes[$nbGroupes]['grparAn'] = $pgProgLotGrparAn;
                $tabGroupes[$nbGroupes]['correct'] = 0;
                $tabGroupes[$nbGroupes]['warning'] = 0;
                $tabGroupes[$nbGroupes]['erreur'] = 0;
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                $tabParamAns = array();
                $nbParamAns = 0;

                $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
                $trouve = false;
                foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                    $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                    foreach ($pgCmdPrelevPcs as $pgCmdPrelevPc) {
                        if ($pgCmdPrelevPc->getZoneVerticale()->getCodeZone() == '1') {
                            $trouve = true;
                            break;
                        }
                    }
                    if ($trouve) {
                        break;
                    }
                }

                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                    $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($pgProgLotParamAn->getCodeUnite());
                    if ($pgSandreUnites) {
                        $tabParamAns[$nbParamAns]['unite'] = $pgSandreUnites;
                    }
                    $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                    if ($pgCmdMesureEnv) {
                        if ($pgCmdMesureEnv->getCodeStatut() == '0') {
                            $tabGroupes[$nbGroupes]['correct'] = $tabGroupes[$nbGroupes]['correct'] + 1;
                        } elseif ($pgCmdMesureEnv->getCodeStatut() == '1') {
                            $tabGroupes[$nbGroupes]['warning'] = $tabGroupes[$nbGroupes]['warning'] + 1;
                        } elseif ($pgCmdMesureEnv->getCodeStatut() == '2') {
                            $tabGroupes[$nbGroupes]['erreur'] = $tabGroupes[$nbGroupes]['erreur'] + 1;
                        }
                        $tabParamAns[$nbParamAns]['pgCmdMesureEnv'] = $pgCmdMesureEnv;
                        $tabParamAns[$nbParamAns]['unite'] = $pgCmdMesureEnv->getCodeUnite();
                    } else {
                        $tabParamAns[$nbParamAns]['pgCmdMesureEnv'] = null;
                    }
                    $tabParamAns[$nbParamAns]['pgCmdPrelevPcs'] = null;
                    $pgSandreUnitesPossiblesParamsEnv = $repoPgSandreUnitesPossiblesParamsEnv->getPgSandreUnitesPossiblesParamsEnvByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                    if ($pgSandreUnitesPossiblesParamsEnv) {
                        $tabParamAns[$nbParamAns]['valeurs'] = $pgSandreUnitesPossiblesParamsEnv;
                    } else {
                        $tabParamAns[$nbParamAns]['valeurs'] = null;
                    }
                    $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                    if ($pgProgUnitesPossiblesParam) {
                        $tabParamAns[$nbParamAns]['unites'] = $pgProgUnitesPossiblesParam;
                    } else {
                        $tabParamAns[$nbParamAns]['unites'] = null;
                    }
                    if ($pgProgLotParamAn->getCodeFraction()) {
                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                        $tabParamAns[$nbParamAns]['fraction'] = $pgSandreFraction;
                    } else {
                        $tabParamAns[$nbParamAns]['fraction'] = null;
                    }
                    $nbParamAns++;
                }
                sort($tabParamAns);
                $tabGroupes[$nbGroupes]['paramAns'] = $tabParamAns;
                $nbGroupes++;
// }
            }

            if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT') {

// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $tabGroupes[$nbGroupes]['grparAn'] = $pgProgLotGrparAn;
                $tabGroupes[$nbGroupes]['correct'] = 0;
                $tabGroupes[$nbGroupes]['warning'] = 0;
                $tabGroupes[$nbGroupes]['erreur'] = 0;
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                $tabParamAns = array();
                $nbParamAns = 0;

                $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
                $trouve = false;
                foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                    $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                    foreach ($pgCmdPrelevPcs as $pgCmdPrelevPc) {
                        if ($pgCmdPrelevPc->getZoneVerticale()->getCodeZone() == '6') {
                            $trouve = true;
                            break;
                        }
                    }
                    if ($trouve) {
                        break;
                    }
                }

                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    $pgCmdPrelev = $pgCmdPrelevPc->getPrelev();
                    if ($pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1301' or
                            $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1302' or
                            $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1303' or
                            $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1311' or
                            $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1312') {
                        $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                        if ($pgCmdPrelevPcs) {
                            foreach ($pgCmdPrelevPcs as $pgCmdPrelevPc) {
                                $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, $pgProgLotParamAn->getCodeParametre()->getCodeParametre(), $pgCmdPrelevPc->getNumOrdre());
                                if ($pgCmdAnalyse) {
                                    if ($pgCmdAnalyse->getCodeStatut() == '0') {
                                        $tabGroupes[$nbGroupes]['correct'] = $tabGroupes[$nbGroupes]['correct'] + 1;
                                    } elseif ($pgCmdAnalyse->getCodeStatut() == '1') {
                                        $tabGroupes[$nbGroupes]['warning'] = $tabGroupes[$nbGroupes]['warning'] + 1;
                                    } elseif ($pgCmdAnalyse->getCodeStatut() == '2') {
                                        $tabGroupes[$nbGroupes]['erreur'] = $tabGroupes[$nbGroupes]['erreur'] + 1;
                                    }
                                    $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                                    $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = $pgCmdAnalyse;
                                    $tabParamAns[$nbParamAns]['pgCmdPrelevPc'] = $pgCmdPrelevPc;
                                    $tabParamAns[$nbParamAns]['unite'] = $pgCmdAnalyse->getCodeUnite();
                                    $pgSandreUnitesPossiblesParamsEnv = $repoPgSandreUnitesPossiblesParamsEnv->getPgSandreUnitesPossiblesParamsEnvByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                                    if ($pgSandreUnitesPossiblesParamsEnv) {
                                        $tabParamAns[$nbParamAns]['valeurs'] = $pgSandreUnitesPossiblesParamsEnv;
                                    } else {
                                        $tabParamAns[$nbParamAns]['valeurs'] = null;
                                    }
                                    $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                                    if ($pgProgUnitesPossiblesParam) {
                                        $tabParamAns[$nbParamAns]['unites'] = $pgProgUnitesPossiblesParam;
                                    } else {
                                        $tabParamAns[$nbParamAns]['unites'] = null;
                                    }

                                    if ($pgProgLotParamAn->getCodeFraction()) {
                                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                        $tabParamAns[$nbParamAns]['fraction'] = $pgSandreFraction;
                                    } else {
                                        $tabParamAns[$nbParamAns]['fraction'] = null;
                                    }
                                } else {
                                    $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                                    $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = null;
                                    $tabParamAns[$nbParamAns]['pgCmdPrelevPc'] = $pgCmdPrelevPc;
                                    $pgSandreUnitesPossiblesParamsEnv = $repoPgSandreUnitesPossiblesParamsEnv->getPgSandreUnitesPossiblesParamsEnvByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                                    if ($pgSandreUnitesPossiblesParamsEnv) {
                                        $tabParamAns[$nbParamAns]['valeurs'] = $pgSandreUnitesPossiblesParamsEnv;
                                    } else {
                                        $tabParamAns[$nbParamAns]['valeurs'] = null;
                                    }
                                    $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                                    if ($pgProgUnitesPossiblesParam) {
                                        $tabParamAns[$nbParamAns]['unites'] = $pgProgUnitesPossiblesParam;
                                    } else {
                                        $tabParamAns[$nbParamAns]['unites'] = null;
                                    }
                                    $tabParamAns[$nbParamAns]['unite'] = null;

                                    if ($pgProgLotParamAn->getCodeFraction()) {
                                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                        $tabParamAns[$nbParamAns]['fraction'] = $pgSandreFraction;
                                    } else {
                                        $tabParamAns[$nbParamAns]['fraction'] = null;
                                    }
                                }
                                $nbParamAns++;
                            }
                        }
                    } else {
                        $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, $pgProgLotParamAn->getCodeParametre()->getCodeParametre(), 1);
                        if ($pgCmdAnalyse) {
                            if ($pgCmdAnalyse->getCodeStatut() == '0') {
                                $tabGroupes[$nbGroupes]['correct'] = $tabGroupes[$nbGroupes]['correct'] + 1;
                            } elseif ($pgCmdAnalyse->getCodeStatut() == '1') {
                                $tabGroupes[$nbGroupes]['warning'] = $tabGroupes[$nbGroupes]['warning'] + 1;
                            } elseif ($pgCmdAnalyse->getCodeStatut() == '2') {
                                $tabGroupes[$nbGroupes]['erreur'] = $tabGroupes[$nbGroupes]['erreur'] + 1;
                            }
                            $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                            $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = $pgCmdAnalyse;
                            $tabParamAns[$nbParamAns]['pgCmdPrelevPc'] = null;
                            $tabParamAns[$nbParamAns]['unite'] = $pgCmdAnalyse->getCodeUnite();
                            $pgSandreUnitesPossiblesParamsEnv = $repoPgSandreUnitesPossiblesParamsEnv->getPgSandreUnitesPossiblesParamsEnvByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                            if ($pgSandreUnitesPossiblesParamsEnv) {
                                $tabParamAns[$nbParamAns]['valeurs'] = $pgSandreUnitesPossiblesParamsEnv;
                            } else {
                                $tabParamAns[$nbParamAns]['valeurs'] = null;
                            }
                            $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                            if ($pgProgUnitesPossiblesParam) {
                                $tabParamAns[$nbParamAns]['unites'] = $pgProgUnitesPossiblesParam;
                            } else {
                                $tabParamAns[$nbParamAns]['unites'] = null;
                            }

                            if ($pgProgLotParamAn->getCodeFraction()) {
                                $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                $tabParamAns[$nbParamAns]['fraction'] = $pgSandreFraction;
                            } else {
                                $tabParamAns[$nbParamAns]['fraction'] = null;
                            }
                        } else {
                            $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                            $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = null;
                            $tabParamAns[$nbParamAns]['pgCmdPrelevPc'] = null;
                            $pgSandreUnitesPossiblesParamsEnv = $repoPgSandreUnitesPossiblesParamsEnv->getPgSandreUnitesPossiblesParamsEnvByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                            if ($pgSandreUnitesPossiblesParamsEnv) {
                                $tabParamAns[$nbParamAns]['valeurs'] = $pgSandreUnitesPossiblesParamsEnv;
                            } else {
                                $tabParamAns[$nbParamAns]['valeurs'] = null;
                            }
                            $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                            if ($pgProgUnitesPossiblesParam) {
                                $tabParamAns[$nbParamAns]['unites'] = $pgProgUnitesPossiblesParam;
                            } else {
                                $tabParamAns[$nbParamAns]['unites'] = null;
                            }
                            $tabParamAns[$nbParamAns]['unite'] = null;

                            if ($pgProgLotParamAn->getCodeFraction()) {
                                $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                $tabParamAns[$nbParamAns]['fraction'] = $pgSandreFraction;
                            } else {
                                $tabParamAns[$nbParamAns]['fraction'] = null;
                            }
                        }
                        $nbParamAns++;
                    }
                }
                sort($tabParamAns);
                $tabGroupes[$nbGroupes]['paramAns'] = $tabParamAns;
                $nbGroupes++;
// }
            }
        }

//\Symfony\Component\VarDumper\VarDumper::dump($tabParamAns);
//return new Response ('');

        if ($maj = 'C') {
            return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeLacVoirEnvSitu.html.twig', array(
                        'user' => $pgProgWebUser,
                        'typeMilieu' => $pgProgTypeMilieu,
                        'lotan' => $pgProgLotAn,
                        'station' => $pgRefStationMesure,
                        'periodeAn' => $pgProgLotPeriodeAn,
                        'dateFin' => $dateFin,
                        'demande' => $pgCmdPrelev->getDemande(),
                        'cmdPrelev' => $pgCmdPrelev,
                        'groupes' => $tabGroupes,
                        'maj' => $maj));
        } else {
            return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeLacSaisirEnvSitu.html.twig', array(
                        'user' => $pgProgWebUser,
                        'typeMilieu' => $pgProgTypeMilieu,
                        'lotan' => $pgProgLotAn,
                        'station' => $pgRefStationMesure,
                        'periodeAn' => $pgProgLotPeriodeAn,
                        'dateFin' => $dateFin,
                        'demande' => $pgCmdPrelev->getDemande(),
                        'cmdPrelev' => $pgCmdPrelev,
                        'groupes' => $tabGroupes,
                        'maj' => $maj));
        }
    }

    public function lotPeriodeLacResultatEnvSituAction($demandeId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', ' otPeriodeStationResultatEnvSitu');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgProgUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();


        $now = date('Ymd');
        $today = new \DateTime($now);

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeById($demandeId);

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
            ;
        }

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);
        $nbErreurs = 0;
        $okControlesSpecifiques = 0;
        $okControleVraisemblance = 0;
        $nbParametresEnvSit = 0;
        $nbParametresAna = 0;


        if (isset($_POST['datePrel'])) {
            $dateSaisie = str_replace('/', '-', $_POST['datePrel']);
            $datePrel = new \DateTime($dateSaisie);
        } else {
            $datePrel = null;
        }
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            $prestataire = $pgProgLotGrparAn->getPrestaDft();
            $pgProgPeriodes = $pgProgLotPeriodeProg->getPeriodAn()->getPeriode();
            if ($pgProgLotGrparAn->getvalide() == 'O') {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();

// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);

                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire)) {

                        if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {

                            $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
                            $trouve = false;
                            foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                                foreach ($pgCmdPrelevPcs as $pgCmdPrelevPc) {
                                    if ($pgCmdPrelevPc->getZoneVerticale()->getCodeZone() == '6') {
                                        $trouve = true;
                                        break;
                                    }
                                }
                                if ($trouve) {
                                    break;
                                }
                            }

                            $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                            $nbPassages = count($pgCmdPrelevPcs);
                            foreach ($pgCmdPrelevPcs as $pgCmdPrelevPc) {

                                $numOrdre = $pgCmdPrelevPc->getNumOrdre();

                                if (isset($_POST['valeur' . $pgProgLotParamAn->getId() . '-' . $numOrdre])) {
                                    $valeur = $_POST['valeur' . $pgProgLotParamAn->getId() . '-' . $numOrdre];
                                } elseif (isset($_POST['valeur' . $pgProgLotParamAn->getId()])) {
                                    $valeur = $_POST['valeur' . $pgProgLotParamAn->getId()];
                                    $nbPassages = 1;
                                } else {
                                    $valeur = null;
                                }
                                if (isset($_POST['uniteCode' . $pgProgLotParamAn->getId() . '-' . $numOrdre])) {
                                    $unite = $_POST['uniteCode' . $pgProgLotParamAn->getId() . '-' . $numOrdre];
                                } else if (isset($_POST['uniteCode' . $pgProgLotParamAn->getId()])) {
                                    $unite = $_POST['uniteCode' . $pgProgLotParamAn->getId()];
                                } else {
                                    $unite = null;
                                }
                                if (isset($_POST['remarque' . $pgProgLotParamAn->getId() . '-' . $numOrdre])) {
                                    $remarque = $_POST['remarque' . $pgProgLotParamAn->getId() . '-' . $numOrdre];
                                } else if (isset($_POST['remarque' . $pgProgLotParamAn->getId()])) {
                                    $remarque = $_POST['remarque' . $pgProgLotParamAn->getId()];
                                } else {
                                    $remarque = null;
                                }

                                $AnalyseNumOrdre = $numOrdre;


                                $nbParametresEnvSit++;
                                $tabStatut = array();
                                $tabStatut['ko'] = 0;
                                $tabStatut['statut'] = 0;
                                $tabStatut['libelle'] = null;
                                $parametre = $pgProgLotParamAn->getCodeParametre()->getCodeparametre();
                                $inSitu = 1;


                                if (strlen($valeur) > 0) {
                                    $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                    $tabStatut = $this->_controleVraisemblance($parametre, $valeur, $remarque, $unite, $inSitu, $pgSandreFraction, $tabStatut);
                                    $okControleVraisemblance = $okControleVraisemblance + $tabStatut['ko'];
                                }
                                if ($pgProgLotParamAn->getCodeFraction()) {
                                    $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                } else {
                                    $pgSandreFraction = null;
                                }
                                if ($numOrdre <= $nbPassages) {
                                    if (strlen($valeur) > 0) {
                                        $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, $parametre, $AnalyseNumOrdre);
                                        if (!$pgCmdAnalyse) {
                                            $pgCmdAnalyse = new PgCmdAnalyse();
                                            $pgCmdAnalyse->setLieuAna('1');
                                            $pgCmdAnalyse->setPrelevId($pgCmdPrelev->getId());
                                            $pgCmdAnalyse->setNumOrdre($AnalyseNumOrdre);
                                            $pgCmdAnalyse->setParamProg($pgProgLotParamAn);
                                            $pgCmdAnalyse->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                            if ($pgSandreFraction) {
                                                $pgCmdAnalyse->setCodeFraction($pgSandreFraction);
                                            }
                                        }

                                        $pgCmdAnalyse->setDateAna($today);
                                        $pgCmdAnalyse->setResultat($valeur);
                                        $pgCmdAnalyse->setCodeRemarque($remarque);
                                        $pgCmdAnalyse->setCodeMethode('0');
                                        $pgCmdAnalyse->setCodeStatut($tabStatut['statut']);
                                        $pgCmdAnalyse->setLibelleStatut($tabStatut['libelle']);
                                        if ($unite) {
                                            $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($unite);
                                            if ($pgSandreUnites) {
                                                $pgCmdAnalyse->setCodeUnite($pgSandreUnites);
                                            }
                                        }
                                        $emSqe->persist($pgCmdAnalyse);
                                    } else {
                                        if ($remarque == '0') {
                                            $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, $parametre, $AnalyseNumOrdre);
                                            if (!$pgCmdAnalyse) {
                                                $pgCmdAnalyse = new PgCmdAnalyse();
                                                $pgCmdAnalyse->setLieuAna('1');
                                                $pgCmdAnalyse->setPrelevId($pgCmdPrelev->getId());
                                                $pgCmdAnalyse->setNumOrdre($AnalyseNumOrdre);
                                                $pgCmdAnalyse->setParamProg($pgProgLotParamAn);
                                                $pgCmdAnalyse->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                                if ($pgSandreFraction) {
                                                    $pgCmdAnalyse->setCodeFraction($pgSandreFraction);
                                                }
                                            }
                                            $pgCmdAnalyse->setDateAna($today);
                                            $pgCmdAnalyse->setResultat(null);
                                            $pgCmdAnalyse->setCodeRemarque($remarque);
                                            $pgCmdAnalyse->setCodeMethode('0');
                                            $pgCmdAnalyse->setCodeStatut(1);
                                            $pgCmdAnalyse->setLibelleStatut('Valeur absente');
                                            if ($unite) {
                                                $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($unite);
                                                if ($pgSandreUnites) {
                                                    $pgCmdAnalyse->setCodeUnite($pgSandreUnites);
                                                }
                                            }
                                            $emSqe->persist($pgCmdAnalyse);
                                        }
                                    }
                                }
                            }
                        }

                        if ($pgProgGrpParamRef->getTypeGrp() == 'ENV') {

                            $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
                            $trouve = false;
                            foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                                foreach ($pgCmdPrelevPcs as $pgCmdPrelevPc) {
                                    if ($pgCmdPrelevPc->getZoneVerticale()->getCodeZone() == '1') {
                                        $trouve = true;
                                        break;
                                    }
                                }
                                if ($trouve) {
                                    break;
                                }
                            }

                            $nbParametresEnvSit++;
                            if (isset($_POST['valeur' . $pgProgLotParamAn->getId()])) {
                                $valeur = $_POST['valeur' . $pgProgLotParamAn->getId()];
                            } else {
                                $valeur = null;
                            }
                            if (isset($_POST['uniteCode' . $pgProgLotParamAn->getId()])) {
                                $unite = $_POST['uniteCode' . $pgProgLotParamAn->getId()];
                            } else {
                                $unite = null;
                            }
                            if (isset($_POST['remarque' . $pgProgLotParamAn->getId()])) {
                                $remarque = $_POST['remarque' . $pgProgLotParamAn->getId()];
                            } else {
                                $remarque = null;
                            }

                            $tabStatut = array();
                            $tabStatut['ko'] = 0;
                            $tabStatut['statut'] = 0;
                            $tabStatut['libelle'] = null;
                            $parametre = $pgProgLotParamAn->getCodeParametre()->getCodeparametre();
                            $inSitu = 1;


                            if (strlen($valeur) > 0) {
                                $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                $tabStatut = $this->_controleVraisemblance($parametre, $valeur, $remarque, $unite, $inSitu, $pgSandreFraction, $tabStatut);
                                $okControleVraisemblance = $okControleVraisemblance + $tabStatut['ko'];
                            }


                            $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                            if (strlen($valeur) > 0) {
                                if (!$pgCmdMesureEnv) {
                                    $pgCmdMesureEnv = new PgCmdMesureEnv();
                                    $pgCmdMesureEnv->setDateMes($today);
                                    $pgCmdMesureEnv->setPrelev($pgCmdPrelev);
                                    $pgCmdMesureEnv->setParamProg($pgProgLotParamAn);
                                    $pgCmdMesureEnv->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                }
                                $pgCmdMesureEnv->setResultat($valeur);
                                $pgCmdMesureEnv->setCodeRemarque($remarque);
                                $pgCmdMesureEnv->setCodeMethode('0');
                                $pgCmdMesureEnv->setCodeStatut($tabStatut['statut']);
                                $pgCmdMesureEnv->setLibelleStatut($tabStatut['libelle']);
                                if ($unite) {
                                    $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($unite);
                                    if ($pgSandreUnites) {
                                        $pgCmdMesureEnv->setCodeUnite($pgSandreUnites);
                                    } else {
                                        $pgCmdMesureEnv->setCodeUnite(null);
                                    }
                                } else {
                                    $pgCmdMesureEnv->setCodeUnite(null);
                                }
                                $emSqe->persist($pgCmdMesureEnv);
                            } else {
                                if ($remarque == '0') {
                                    if (!$pgCmdMesureEnv) {
                                        $pgCmdMesureEnv = new PgCmdMesureEnv();
                                        $pgCmdMesureEnv->setDateMes($today);
                                        $pgCmdMesureEnv->setPrelev($pgCmdPrelev);
                                        $pgCmdMesureEnv->setParamProg($pgProgLotParamAn);
                                        $pgCmdMesureEnv->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                    }
                                    $pgCmdMesureEnv->setResultat(null);
                                    $pgCmdMesureEnv->setCodeRemarque($remarque);
                                    $pgCmdMesureEnv->setCodeMethode('0');
                                    $pgCmdMesureEnv->setCodeStatut(1);
                                    $pgCmdMesureEnv->setLibelleStatut('Valeur absente');
                                    if ($unite) {
                                        $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($unite);
                                        if ($pgSandreUnites) {
                                            $pgCmdMesureEnv->setCodeUnite($pgSandreUnites);
                                        } else {
                                            $pgCmdMesureEnv->setCodeUnite(null);
                                        }
                                    } else {
                                        $pgCmdMesureEnv->setCodeUnite(null);
                                    }
                                    $emSqe->persist($pgCmdMesureEnv);
                                } else {
                                    if ($pgCmdMesureEnv) {
                                        $emSqe->remove($pgCmdMesureEnv);
                                    }
                                }
                            }
                        }

                        if ($pgProgGrpParamRef->getTypeGrp() == 'ANA') {
                            $nbParametresAna++;
                        }
                    }
                }


                $emSqe->flush();

// }
            }
        }


        if ($okControleVraisemblance != 0) {
            $nbErreurs++;
        } else {
            $okControlesSpecifiques = $this->_controlesSpecifiques($pgCmdPrelev);
        }
        if ($okControlesSpecifiques != 0) {
            $nbErreurs++;
        }


        //return new Response ('$okControleVraisemblance : ' . $okControleVraisemblance . '  $okControlesSpecifiques :  ' . $okControlesSpecifiques . '   $nbErreurs : ' . $nbErreurs);
        $okPhase = false;
        if ($nbErreurs == 0) {
            $nbSaisieParametreEnv = 0;
            $nbSaisieParametreSit = 0;
            $nbSaisieParametresAna = 0;
            $nbParametresEnvSit = 0;
            $nbParametresAna = 0;
            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                $prestataire = $pgProgLotGrparAn->getPrestaDft();
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
                $pgProgPeriodes = $pgProgLotPeriodeProg->getPeriodAn()->getPeriode();
                if ($pgProgLotGrparAn->getvalide() == 'O') {
                    $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                    foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                        if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire)) {

                            if ($pgProgGrpParamRef->getTypeGrp() == 'ENV') {
                                if ($prestataire == $pgProgLotParamAn->getPrestataire()) {
                                    //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                    $nbParametresEnvSit++;
                                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
                                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                        $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                        if ($pgCmdMesureEnv) {
                                            $nbSaisieParametreEnv++;
                                        }
                                    }
                                }
                            }

                            if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {
                                if ($prestataire == $pgProgLotParamAn->getPrestataire()) {
                                    //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                    $nbParametresEnvSit++;
                                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
                                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                        $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                        if (count($pgCmdAnalyses) > 0) {
                                            $nbSaisieParametreSit++;
                                        }
                                    }
                                }
                            }



                            if ($pgProgGrpParamRef->getTypeGrp() == 'ANA') {
                                if ($prestataire == $pgProgLotParamAn->getPrestataire()) {
                                    //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                    $nbParametresAna++;
                                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
                                    $trouve = false;
                                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                        //echo ('groupe: ' . $pgProgLotGrparAn . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId()  . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                        $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                        if (count($pgCmdAnalyses) > 0) {
                                            $nbSaisieParametresAna++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }



            // $nbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
            echo ('$nbCmdMesureEnv : ' . $nbSaisieParametreEnv . ' </br>');
            // $nbCmdSit = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
            echo ('$nbCmdSit : ' . $nbSaisieParametreSit . ' </br>');
            $nbSaisieParametresEnvSit = $nbSaisieParametreEnv + $nbSaisieParametreSit;
            echo ('$nbSaisieParametresEnvSit : ' . $nbSaisieParametresEnvSit . ' </br>');
            //$nbSaisieParametresAna = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
            echo ('$nbSaisieParametresAna : ' . $nbSaisieParametresAna . ' </br>');
            $nbParametresTotal = $nbParametresEnvSit + $nbParametresAna;
            echo ('$nbParametresTotal : ' . $nbParametresTotal . ' </br>');
            $nbSaisieParametresTotal = $nbSaisieParametresEnvSit + $nbSaisieParametresAna;
            echo ('$nbSaisieParametresTotal : ' . $nbSaisieParametresTotal . ' </br>');

            //return new Response('');

            if ($nbParametresTotal == $nbSaisieParametresTotal) {
                $okPhase = true;
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M30');
                $pgCmdPrelev->setRealise('O');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            } else {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M20');
                $pgCmdPrelev->setRealise('N');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            }
        }


        foreach ($pgCmdPrelevs as $pgCmdPrelev) {
            $pgCmdPrelev->setDatePrelev($datePrel);
            $emSqe->persist($pgCmdPrelev);
        }
        $emSqe->flush();




//  return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//return new Response ('');
        if ($nbErreurs == 0) {
            if ($okPhase) {
                return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_lac_saisir_env_situ', array(
                                    'demandeId' => $pgCmdPrelev->getDemande()->getId(),
                                    'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                    'stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                    'maj' => 'V',)));
            } else {
                return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_lacs', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                    'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                    'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
            }
        } else {
            return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_lac_saisir_env_situ', array(
                                'demandeId' => $pgCmdPrelev->getDemande()->getId(),
                                'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                'stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                'maj' => 'M',
            )));
        }
    }

    public function lotPeriodeLacGenererEnvSituAction($periodeAnId = null, $prelevId = null, $groupeId = null, $profMax = null, $ecart = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', ' otPeriodeStationResultatEnvSitu');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgProgUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $repoPgSandreUnitesPossiblesParamsEnv = $emSqe->getRepository('AeagSqeBundle:PgSandreUnitesPossiblesParamsEnv');
        $repoPgSandreZoneVerticaleProspectee = $emSqe->getRepository('AeagSqeBundle:PgSandreZoneVerticaleProspectee');


        $now = date('Ymd');
        $today = new \DateTime($now);

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgProgLotGrparAn = $repoPgProgLotGrparAn->getPgProgLotGrparAnById($groupeId);
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAnPeriodeAn($pgProgLotGrparAn, $pgProgLotPeriodeAn);
        $nbErreurs = 0;

        $pgSandreZoneVerticaleProspectee = $repoPgSandreZoneVerticaleProspectee->getPgSandreZoneVerticaleProspecteeByCodeZone(0);
        $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
        $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);


        if ($profMax <= 3) {
            $profDeb = 0;
            $profFin = $profMax;
            $ecartRet = 0.5;
        } else {
            $profDeb = 0;
            $profFin = $profMax;
            $ecartRet = $ecart;
        }

        $numOrdre = 0;
        for ($prof = 0; $prof <= $profFin; $prof += $ecartRet) {
            $numOrdre++;
            $pgCmdPrelevPc = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelevNumOrdre($pgCmdPrelev, $numOrdre);
            if ($pgCmdPrelevPc) {
                $pgSandreZoneVerticaleProspectee = $pgCmdPrelevPc->getZoneVerticale();
                $pgCmdPrelevPc->setProfondeur($prof);
            } else {
                $pgCmdPrelevPc = new PgCmdPrelevPc();
                $pgCmdPrelevPc->setPrelev($pgCmdPrelev);
                $pgCmdPrelevPc->setNumOrdre($numOrdre);
                $pgCmdPrelevPc->setProfondeur($prof);
                $pgCmdPrelevPc->setZoneVerticale($pgSandreZoneVerticaleProspectee);
                $emSqe->persist($pgCmdPrelevPc);
            }

            $pgCmdPrelev->setProfMax($profMax);
            $emSqe->persist($pgCmdPrelev);

            $emSqe->flush();

            $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
            foreach ($pgCmdPrelevPcs as $pgCmdPrelevPc) {
                if ($pgCmdPrelevPc->getNumOrdre() > $numOrdre) {
                    $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevNumOrdre($pgCmdPrelev, $pgCmdPrelevPc->getNumOrdre());
                    foreach ($pgCmdAnalyses as $pgCmdAnalyse) {
                        $emSqe->remove($pgCmdAnalyse);
                        $emSqe->flush();
                    }
                    $emSqe->remove($pgCmdPrelevPc);
                }
            }
            $emSqe->flush();
        }

        return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_lac_saisir_env_situ', array(
                            'demandeId' => $pgCmdPrelev->getDemande()->getId(),
                            'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                            'stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                            'maj' => 'M',
        )));
    }

    public function lotPeriodeLacSaisirAnaAction($prelevId = null, $periodeAnId = null, $stationId = null, $maj = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationSaisirAna');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgProgUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $repoPgSandreUnitesPossiblesParamsEnv = $emSqe->getRepository('AeagSqeBundle:PgSandreUnitesPossiblesParamsEnv');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgProgGrparRefZoneVert = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefZoneVert');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
        $pgPrelevPc = $pgPrelevPcs[0];

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAnOrderByGrparAn($pgProgLotStationAn, $pgProgLotPeriodeAn);

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }

        $tabGroupes = array();
        $nbGroupes = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            if ($pgProgLot->getDelaiPrel()) {
                $dateFin = clone($pgProgLotPeriodeProg->getPeriodan()->getPeriode()->getDateDeb());
                $delai = $pgProgLot->getDelaiPrel();
                $dateFin->add(new \DateInterval('P' . $delai . 'D'));
            } else {
                $dateFin = $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getDateFin();
            }
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            $prestataire = $pgProgLotGrparAn->getPrestaDft();
            if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                if ($pgPrelevPc) {
                    $nbgProgGrparRefZoneVert = $repoPgProgGrparRefZoneVert->getNbPgProgGrparRefZoneVertByGrparRefPgSandreZoneVerticaleProspectee($pgProgLotGrparAn->getGrparRef(), $pgPrelevPc->getZoneVerticale());
                } else {
                    $nbgProgGrparRefZoneVert = 0;
                }
                if ($nbgProgGrparRefZoneVert > 0) {

                    $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                    $tabGroupes[$nbGroupes]['grparAn'] = $pgProgLotGrparAn;
                    $tabGroupes[$nbGroupes]['correct'] = 0;
                    $tabGroupes[$nbGroupes]['warning'] = 0;
                    $tabGroupes[$nbGroupes]['erreur'] = 0;
                    $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                    $tabParamAns = array();
                    $nbParamAns = 0;
                    foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                        $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                        $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($pgProgLotParamAn->getCodeUnite());
                        if ($pgSandreUnites) {
                            $tabParamAns[$nbParamAns]['unite'] = $pgSandreUnites;
                        }

                        $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                        if (count($pgCmdAnalyses) > 0) {
                            foreach ($pgCmdAnalyses as $pgCmdAnalyse) {
                                if ($pgCmdAnalyse->getCodeStatut() == '0') {
                                    $tabGroupes[$nbGroupes]['correct'] = $tabGroupes[$nbGroupes]['correct'] + 1;
                                } elseif ($pgCmdAnalyse->getCodeStatut() == '1') {
                                    $tabGroupes[$nbGroupes]['warning'] = $tabGroupes[$nbGroupes]['warning'] + 1;
                                } elseif ($pgCmdAnalyse->getCodeStatut() == '2') {
                                    $tabGroupes[$nbGroupes]['erreur'] = $tabGroupes[$nbGroupes]['erreur'] + 1;
                                }
                                $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = $pgCmdAnalyse;
                                $tabParamAns[$nbParamAns]['unite'] = $pgCmdAnalyse->getCodeUnite();
                                $pgSandreUnitesPossiblesParamsEnv = $repoPgSandreUnitesPossiblesParamsEnv->getPgSandreUnitesPossiblesParamsEnvByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                                if ($pgSandreUnitesPossiblesParamsEnv) {
                                    $tabParamAns[$nbParamAns]['valeurs'] = $pgSandreUnitesPossiblesParamsEnv;
                                } else {
                                    $tabParamAns[$nbParamAns]['valeurs'] = null;
                                }
                                $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                                if ($pgProgUnitesPossiblesParam) {
                                    $tabParamAns[$nbParamAns]['unites'] = $pgProgUnitesPossiblesParam;
                                } else {
                                    $tabParamAns[$nbParamAns]['unites'] = null;
                                }

                                if ($pgProgLotParamAn->getCodeFraction()) {
                                    $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                    $tabParamAns[$nbParamAns]['fraction'] = $pgSandreFraction;
                                } else {
                                    $tabParamAns[$nbParamAns]['fraction'] = null;
                                }
                                $nbParamAns++;
                            }
                        } else {
                            $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = null;

                            $pgSandreUnitesPossiblesParamsEnv = $repoPgSandreUnitesPossiblesParamsEnv->getPgSandreUnitesPossiblesParamsEnvByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                            if ($pgSandreUnitesPossiblesParamsEnv) {
                                $tabParamAns[$nbParamAns]['valeurs'] = $pgSandreUnitesPossiblesParamsEnv;
                            } else {
                                $tabParamAns[$nbParamAns]['valeurs'] = null;
                            }
                            $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametre($pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                            if ($pgProgUnitesPossiblesParam) {
                                $tabParamAns[$nbParamAns]['unites'] = $pgProgUnitesPossiblesParam;
                            } else {
                                $tabParamAns[$nbParamAns]['unites'] = null;
                            }

                            if ($pgProgLotParamAn->getCodeFraction()) {
                                $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                $tabParamAns[$nbParamAns]['fraction'] = $pgSandreFraction;
                            } else {
                                $tabParamAns[$nbParamAns]['fraction'] = null;
                            }
                        }
                        $nbParamAns++;
//                    if ($pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1319') {
//                        return new Response(\Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//                    }
                    }

                    $tabGroupes[$nbGroupes]['paramAns'] = $tabParamAns;
                    $nbGroupes++;
// }
                }
            }
        }

//return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//return new Response ('');

        if ($maj == 'C') {
            return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeLacVoirAna.html.twig', array(
                        'user' => $pgProgWebUser,
                        'lotan' => $pgProgLotAn,
                        'station' => $pgRefStationMesure,
                        'periodeAn' => $pgProgLotPeriodeAn,
                        'dateFin' => $dateFin,
                        'demande' => $pgCmdPrelev->getDemande(),
                        'cmdPrelev' => $pgCmdPrelev,
                        'groupes' => $tabGroupes,
                        'maj' => $maj));
        } else {
            return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeLacSaisirAna.html.twig', array(
                        'user' => $pgProgWebUser,
                        'lotan' => $pgProgLotAn,
                        'station' => $pgRefStationMesure,
                        'periodeAn' => $pgProgLotPeriodeAn,
                        'dateFin' => $dateFin,
                        'demande' => $pgCmdPrelev->getDemande(),
                        'cmdPrelev' => $pgCmdPrelev,
                        'groupes' => $tabGroupes,
                        'maj' => $maj));
        }
    }

    public function lotPeriodeLacResultatAnaAction($prelevId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationResultatAna');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgProgUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $now = date('Ymd');
        $today = new \DateTime($now);

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdDemande = $pgCmdPrelev->getDemande();
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);
        $nbErreurs = 0;
        $okControlesSpecifiques = 0;
        $okControleVraisemblance = 0;
        $nbParametresEnvSit = 0;
        $nbParametresAna = 0;

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }

        if (isset($_POST['datePrel'])) {
            $dateSaisie = str_replace('/', '-', $_POST['datePrel']);
            $datePrel = new \DateTime($dateSaisie);
        } else {
            $datePrel = null;
        }

        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            $prestataire = $pgProgLotGrparAn->getPrestaDft();
            if ($user->hasRole('ROLE_ADMINSQE') or ( $userPrestataire == $prestataire)) {
                if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                    $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                    $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);

                    foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                        if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire())) {
                            $nbParametresAna++;
                            if (isset($_POST['valeur' . $pgProgLotParamAn->getId()])) {
                                $valeur = $_POST['valeur' . $pgProgLotParamAn->getId()];
                            } else {
                                $valeur = null;
                            }
                            if (isset($_POST['uniteCode' . $pgProgLotParamAn->getId()])) {
                                $unite = $_POST['uniteCode' . $pgProgLotParamAn->getId()];
                                $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($unite);
                            } else {
                                $unite = null;
                                $pgSandreUnites = null;
                            }
                            if (isset($_POST['remarque' . $pgProgLotParamAn->getId()])) {
                                $remarque = $_POST['remarque' . $pgProgLotParamAn->getId()];
                            } else {
                                $remarque = null;
                            }

                            $tabStatut = array();
                            $tabStatut['ko'] = 0;
                            $tabStatut['statut'] = 0;
                            $tabStatut['libelle'] = null;
                            $parametre = $pgProgLotParamAn->getCodeParametre()->getCodeparametre();
                            $inSitu = 0;

                            if (strlen($valeur) > 0) {
                                $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                $tabStatut = $this->_controleVraisemblance($parametre, $valeur, $remarque, $unite, $inSitu, $pgSandreFraction, $tabStatut);
                                $okControleVraisemblance = $okControleVraisemblance + $tabStatut['ko'];
                            }

                            $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                            if ($pgProgLotParamAn->getCodeFraction()) {
                                $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                            } else {
                                $pgSandreFraction = null;
                            }
                            if (strlen($valeur) > 0) {
                                if (!$pgCmdAnalyse) {
                                    $pgCmdPrelevPc = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelevNumOrdre($pgCmdPrelev, 1);
                                    if (!$pgCmdPrelevPc) {
                                        $pgCmdPrelevPc = new PgCmdPrelevPc();
                                        $pgCmdPrelevPc->setPrelev($pgCmdPrelev);
                                        $pgCmdPrelevPc->setNumOrdre(1);
                                        $emSqe->persist($pgCmdPrelevPc);
                                        $emSqe->flush();
                                    }
                                    $pgCmdAnalyse = new PgCmdAnalyse();
                                    $pgCmdAnalyse->setNumOrdre(1);
                                    $pgCmdAnalyse->setLieuAna('2');
                                    $pgCmdAnalyse->setPrelevId($pgCmdPrelev->getId());
                                    $pgCmdAnalyse->setParamProg($pgProgLotParamAn);
                                    $pgCmdAnalyse->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                    if ($pgSandreFraction) {
                                        $pgCmdAnalyse->setCodeFraction($pgSandreFraction);
                                    }
                                }
                                $pgCmdAnalyse->setDateAna($today);
                                $pgCmdAnalyse->setResultat($valeur);
                                $pgCmdAnalyse->setCodeRemarque($remarque);
                                $pgCmdAnalyse->setCodeMethode('0');
                                $pgCmdAnalyse->setCodeStatut($tabStatut['statut']);
                                $pgCmdAnalyse->setlibelleStatut($tabStatut['libelle']);
                                if ($pgSandreUnites) {
                                    $pgCmdAnalyse->setCodeUnite($pgSandreUnites);
                                }
                                $emSqe->persist($pgCmdAnalyse);
                            } else {
                                if ($pgCmdAnalyse) {
                                    $emSqe->remove($pgCmdAnalyse);
                                }
                            }
                        }
                    }

                    $emSqe->flush();



// }
                } else {
                    $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                    foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                        if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire())) {
                            $nbParametresEnvSit++;
                        }
                    }
                }
            }
        }


        if ($okControleVraisemblance != 0) {
            $nbErreurs++;
        } else {
            $okControlesSpecifiques = $this->_controlesSpecifiques($pgCmdPrelev);
        }
        if ($okControlesSpecifiques != 0) {
            $nbErreurs++;
        }

        // return new Response ('$okControleVraisemblance : ' . $okControleVraisemblance . '  $okControlesSpecifiques :  ' . $okControlesSpecifiques . '   $nbErreurs : ' . $nbErreurs);

        $ok = false;

        if ($nbErreurs == 0) {
            $nbSaisieParametreEnv = 0;
            $nbSaisieParametreSit = 0;
            $nbSaisieParametresAna = 0;
            $nbParametresEnvSit = 0;
            $nbParametresAna = 0;
            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                $prestataire = $pgProgLotGrparAn->getPrestaDft();
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
                $pgProgPeriodes = $pgProgLotPeriodeProg->getPeriodAn()->getPeriode();
                if ($pgProgLotGrparAn->getvalide() == 'O') {
                    $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                    foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                        if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire)) {

                            if ($pgProgGrpParamRef->getTypeGrp() == 'ENV') {
                                if ($prestataire == $pgProgLotParamAn->getPrestataire()) {
                                    //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                    $nbParametresEnvSit++;
                                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
                                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                        $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                        if ($pgCmdMesureEnv) {
                                            $nbSaisieParametreEnv++;
                                        }
                                    }
                                }
                            }

                            if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {
                                if ($prestataire == $pgProgLotParamAn->getPrestataire()) {
                                    //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                    $nbParametresEnvSit++;
                                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
                                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                        $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                        if (count($pgCmdAnalyses) > 0) {
                                            $nbSaisieParametreSit++;
                                        }
                                    }
                                }
                            }



                            if ($pgProgGrpParamRef->getTypeGrp() == 'ANA') {
                                if ($prestataire == $pgProgLotParamAn->getPrestataire()) {
                                    //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                    $nbParametresAna++;
                                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
                                    $trouve = false;
                                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                        //echo ('groupe: ' . $pgProgLotGrparAn . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId()  . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                        $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                        if (count($pgCmdAnalyses) > 0) {
                                            $nbSaisieParametresAna++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }



            // $nbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
            echo ('$nbCmdMesureEnv : ' . $nbSaisieParametreEnv . ' </br>');
            // $nbCmdSit = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
            echo ('$nbCmdSit : ' . $nbSaisieParametreSit . ' </br>');
            $nbSaisieParametresEnvSit = $nbSaisieParametreEnv + $nbSaisieParametreSit;
            echo ('$nbSaisieParametresEnvSit : ' . $nbSaisieParametresEnvSit . ' </br>');
            //$nbSaisieParametresAna = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
            echo ('$nbSaisieParametresAna : ' . $nbSaisieParametresAna . ' </br>');
            $nbParametresTotal = $nbParametresEnvSit + $nbParametresAna;
            echo ('$nbParametresTotal : ' . $nbParametresTotal . ' </br>');
            $nbSaisieParametresTotal = $nbSaisieParametresEnvSit + $nbSaisieParametresAna;
            echo ('$nbSaisieParametresTotal : ' . $nbSaisieParametresTotal . ' </br>');

            //return new Response('');

            if ($nbParametresTotal == $nbSaisieParametresTotal) {
                $okPhase = true;
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M30');
                $pgCmdPrelev->setRealise('O');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            } else {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M20');
                $pgCmdPrelev->setRealise('N');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            }
        }

//        if ($nbErreurs == 0) {
//            $nbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
//            $nbCmdSit = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
//            $nbSaisieParametresEnvSit = $nbCmdMesureEnv + $nbCmdSit;
//            $nbSaisieParametresAna = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
//            $nbParametresTotal = $nbParametresEnvSit + $nbParametresAna;
//            $nbSaisieParametresTotal = $nbSaisieParametresEnvSit + $nbSaisieParametresAna;
//            if ($nbParametresTotal == $nbSaisieParametresTotal) {
//                $ok = true;
//                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M30');
//                $pgCmdPrelev->setRealise('O');
//                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
//            } else {
//                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M20');
//                $pgCmdPrelev->setRealise('N');
//                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
//            }
//        }
        //return new Response('$nbParametresTotal  : ' . $nbParametresTotal . ' $nbParametresEnvSit : ' . $nbParametresEnvSit . '  $nbParametresAna : ' . $nbParametresAna . '  $nbSaisieParametresTotal :  ' . $nbSaisieParametresTotal);

        $pgCmdPrelev->setDatePrelev($datePrel);
        $emSqe->persist($pgCmdPrelev);
        $emSqe->flush();

//  return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//return new Response ('');

        if ($nbErreurs == 0) {
            if ($ok) {
                return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_lac_saisir_ana', array(
                                    'prelevId' => $pgCmdPrelev->getId(),
                                    'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                    'stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                    'maj' => 'V',
                )));
            } else {
                return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_lacs', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                    'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                    'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
            }
        } else {
            return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_lac_saisir_ana', array(
                                'prelevId' => $pgCmdPrelev->getId(),
                                'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                'stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                'maj' => 'M',
            )));
        }
    }

    public function lotPeriodeLacValiderAction($demandeId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeLacValiderEnvSitu');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $prestataire = $pgProgLotGrparAn->getPrestaDft();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();


        $now = date('Ymd');
        $today = new \DateTime($now);

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeById($demandeId);

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }


        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);

        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            $prestataire = $pgProgLotGrparAn->getPrestaDft();
            $pgProgPeriodes = $pgProgLotPeriodeProg->getPeriodAn()->getPeriode();
            $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);

            $tabDonneesBrutes = array();
            $i = 0;
            foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R45');

                if (!$pgCmdPrelev->getFichierRps()) {
                    $pgCmdFichiersRps = new PgCmdFichiersRps();
                    $pgCmdFichiersRps->setDemande($pgCmdPrelev->getDemande());
                    $pgCmdFichiersRps->setNomFichier('SAISIE_' . $pgCmdPrelev->getId());
                    $pgCmdFichiersRps->setDateDepot(new \DateTime());
                    $pgCmdFichiersRps->setTypeFichier('SAI');
                    $pgCmdFichiersRps->setUser($pgProgWebUser);
                    $pgCmdFichiersRps->setSuppr('N');
                } else {
                    $pgCmdFichiersRps = $pgCmdPrelev->getFichierRps();
                }
                $pgCmdFichiersRps->setPhaseFichier($pgProgPhases);
                $emSqe->persist($pgCmdFichiersRps);
                $pgCmdPrelev->setFichierRps($pgCmdFichiersRps);

                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
                $emSqe->persist($pgCmdPrelev);

                $tabDonneesBrutes[$i] = $repoPgCmdPrelev->getDonneesBrutes($pgCmdPrelev->getFichierRps());
                $i++;
            }
        }

        $chemin = '/base/extranet/Transfert/Sqe/csv/';

        $this->get('aeag_sqe.process_rai')->exportCsvDonneesLacsSaisies($emSqe, $chemin, $pgProgLotStationAn, $tabDonneesBrutes);

        $emSqe->flush();

        $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
        $nbPrelevs = count($pgCmdPrelevs);
        $nbPrelevM40 = 0;
        foreach ($pgCmdPrelevs as $pgCmdPrelev) {
            if ($pgCmdPrelev->getPhaseDmd() == 'M40') {
                $nbPrelevM40++;
            }
        }
        if ($nbPrelevs == $nbPrelevM40) {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D40');
            $pgCmdDemande->setPhaseDemande($pgProgPhases);
            $emSqe->persist($pgCmdDemande);
            $emSqe->flush();
        } else {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D30');
            $pgCmdDemande->setPhaseDemande($pgProgPhases);
            $emSqe->persist($pgCmdDemande);
            $emSqe->flush();
        }






//  return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//return new Response ('');
        return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_lacs', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                            'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                            'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
    }

    public function lotPeriodeLacDevaliderAction($demandeId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeLacDevaliderEnvSitu');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();


        $now = date('Ymd');
        $today = new \DateTime($now);

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeById($demandeId);

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }


        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);

        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            $prestataire = $pgProgLotGrparAn->getPrestaDft();
            $pgProgPeriodes = $pgProgLotPeriodeProg->getPeriodAn()->getPeriode();
            $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);

            foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R10');

                if (!$pgCmdPrelev->getFichierRps()) {
                    $pgCmdFichiersRps = new PgCmdFichiersRps();
                    $pgCmdFichiersRps->setDemande($pgCmdPrelev->getDemande());
                    $pgCmdFichiersRps->setNomFichier('SAISIE_' . $pgCmdPrelev->getId());
                    $pgCmdFichiersRps->setDateDepot(new \DateTime());
                    $pgCmdFichiersRps->setTypeFichier('SAI');
                    $pgCmdFichiersRps->setUser($pgProgWebUser);
                    $pgCmdFichiersRps->setSuppr('N');
                } else {
                    $pgCmdFichiersRps = $pgCmdPrelev->getFichierRps();
                }
                $pgCmdFichiersRps->setPhaseFichier($pgProgPhases);
                $emSqe->persist($pgCmdFichiersRps);
                $pgCmdPrelev->setFichierRps($pgCmdFichiersRps);

                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M30');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
                $emSqe->persist($pgCmdPrelev);
            }
        }
        $emSqe->flush();

        $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
        $nbPrelevM40 = 0;
        foreach ($pgCmdPrelevs as $pgCmdPrelev) {
            if ($pgCmdPrelev->getPhaseDmd() == 'M40') {
                $nbPrelevM40++;
            }
        }
        if ($nbPrelevM40 == 0) {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D10');
            $pgCmdDemande->setPhaseDemande($pgProgPhases);
            $emSqe->persist($pgCmdDemande);
            $emSqe->flush();
        } else {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D30');
            $pgCmdDemande->setPhaseDemande($pgProgPhases);
            $emSqe->persist($pgCmdDemande);
            $emSqe->flush();
        }


//  return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//return new Response ('');
        return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_lacs', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                            'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                            'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
    }

    public function lotPeriodeLacTelechargerAction($prelevId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationTelecharger');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');

        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdFichiersRps = $pgCmdPrelev->getFichierRps();
        $chemin = $this->getParameter('repertoire_echange');
        $fichier = $pgCmdFichiersRps->getNomFichier();
        $ext = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));

        header('Content-Type', 'application/' . $ext);
        header('Content-disposition: attachment; filename="' . $fichier . '"');
        header('Content-Length: ' . filesize($chemin . '/' . $fichier));
        readfile($chemin . '/' . $fichier);
        exit();
    }

    protected function getCheminEchange($pgCmdSuiviPrel) {
        $chemin = $this->getParameter('repertoire_echange');
        $chemin .= $pgCmdSuiviPrel->getPrelev()->getDemande()->getAnneeProg() . '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getCommanditaire()->getNomCorres();
        $chemin .= '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getLot()->getId() . '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getId();
        $chemin .= '/SUIVI/' . $pgCmdSuiviPrel->getPrelev()->getId() . '/' . $pgCmdSuiviPrel->getId();

        return $chemin;
    }

    protected function _controleVraisemblance($parametre, $valeur, $remarque, $unite, $inSitu, $fraction, $tabStatut) {

        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', '_controleVraisemblance');

        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $controleVraisemblance = $this->get('aeag_sqe.controle_vraisemblance');

// Contrôles sur toutes les valeurs insérées
        $mesure = $valeur;
        $codeRq = $remarque;
        $codeParametre = $parametre;


// III.1 Champs non renseignés (valeurs et code remarque) ou valeurs non numériques ou valeurs impossibles params env / code remarque peut ne pas être renseigné pour cette liste (car réponse en edilabo 1.0) => avertissement
        $result = $controleVraisemblance->champsNonRenseignes($mesure, $codeRq, $codeParametre, $inSitu);
        if (is_bool($result)) {
            $result = $controleVraisemblance->valeursNumeriques($mesure, $codeRq);
        }
        if (is_bool($result)) {
            $result = $controleVraisemblance->valeursEgalZero($mesure, $codeParametre, $inSitu);
        }
        if (is_bool($result)) {
            $result = $controleVraisemblance->valeursInfZero($mesure, $codeParametre);
        }
        if (is_bool($result)) {
            $result = $controleVraisemblance->valeursSupTrois($codeParametre, $codeRq);
        }
        if (is_bool($result)) {
            if ($codeParametre == '1302') {
                $result = $controleVraisemblance->pH($mesure);
            }
        }

        if (is_bool($result)) {

// III.12 Valeur de pourcentage hors 1312 oxygène (ex : matière sèche ou granulo) : non compris entre 0 et 100 si code unité = 243 ou 246
            if ($unite == '243' or $unite == '246') {
                if ($codeParametre != '1312') {
                    if ($mesure < 0 or $mesure > 100) {
                        $tabStatut['ko'] = $tabStatut['ko'] + 1;
                        $tabStatut['statut'] = 2;
                        $tabStatut['libelle'] = 'Valeur pourcentage : pourcentage n\'est pas entre 0 et 100';
                    }
                }
            }

            //  Résultat d’analyse< Valeur max de la base

            $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametreCodeUniteNatureFraction($codeParametre, $unite, $fraction);
            if ($pgProgUnitesPossiblesParam) {
                if (strlen($pgProgUnitesPossiblesParam->getValMax()) > 0) {
                    if ($mesure > $pgProgUnitesPossiblesParam->getValMax()) {
                        $tabStatut['ko'] = $tabStatut['ko'] + 1;
                        $tabStatut['statut'] = 2;
                        $tabStatut['libelle'] = 'Valeur doit être inferieure à ' . $pgProgUnitesPossiblesParam->getValMax();
                    }
                }
            }
        }

        if (!is_bool($result)) {
            $tabRetour = $result;
            if ($tabRetour[0] == 'warning') {
                $tabStatut['statut'] = 1;
            } else {
                $tabStatut['statut'] = 2;
            }
            $tabStatut['libelle'] = $tabRetour[1];
            $tabStatut['ko'] = $tabStatut['ko'] + 1;
        }

        return $tabStatut;
    }

    protected function _controlesSpecifiques($pgCmdPrelev) {
// Contrôles spécifiques

        $okControles = 0;

// III.7
        $okControles += $this->_modeleWeiss($pgCmdPrelev);
        echo ('III.7 : ' . $okControles . ' </br>');
        // III.8  et III.9
        $okControles += $this->_balanceIonique($pgCmdPrelev);
        echo ('IIII.8 : ' . $okControles . ' </br>');


// III.10
        $okControles += $this->_ortophosphate($pgCmdPrelev);
        echo ('III.10 : ' . $okControles . ' </br>');

// III.11
        $okControles += $this->_ammonium($pgCmdPrelev);
        echo ('III.11 : ' . $okControles . ' </br>');

// III.12
        $okControles += $this->_pourcentageHorsOxygene($pgCmdPrelev);
        echo ('III.12 : ' . $okControles . ' </br>');

        // III.13
        $okControles += $this->_sommeParametresDistincts($pgCmdPrelev);
        echo ('III.13 : ' . $okControles . ' </br>');

        // III.14
        $okControles += $this->_controleVraisemblanceMacroPolluants($pgCmdPrelev);
        echo ('III.14 : ' . $okControles . ' </br>');

        return $okControles;
    }

// III.7 modèle  de WEISS : cohérence Teau, % O2, Concentration O2  sauf si Conductivité > 10 000
    protected function _modeleWeiss($pgCmdPrelev) {

        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', '_modeleWeiss');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $controleVraisemblance = $this->get('aeag_sqe.controle_vraisemblance');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');

        $tabStatut = array();
        $tabStatut['statut'] = 0;
        $tabStatut['libelle'] = null;
        $okWeiss = 0;

        $mTxSatOx = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1312');
        if (!$mTxSatOx) {
            $mTxSatOx = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1312', 1);
        }
        if ($mTxSatOx) {
            $mTxSatOxResultat = $mTxSatOx->getResultat();
        } else {
            $mTxSatOxResultat = null;
        }

        $mOxDiss = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1311');
        if (!$mOxDiss) {
            $mOxDiss = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1311', 1);
        }
        if ($mOxDiss) {
            $mOxDissResultat = $mOxDiss->getResultat();
        } else {
            $mOxDissResultat = null;
        }

        $mTEau = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1301');
        if (!$mTEau) {
            $mTEau = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1301', 1);
        }
        if ($mTEau) {
            $mTEauResultat = $mTEau->getResultat();
        } else {
            $mTEauResultat = null;
        }

        $mConductivite = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1303');
        if (!$mConductivite) {
            $mConductivite = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1303', 1);
        }
        if ($mConductivite) {
            $mConductiviteResultat = $mConductivite->getResultat();
        } else {
            $mConductiviteResultat = null;
        }

        $result = $controleVraisemblance->modeleWeiss($mTxSatOxResultat, $mOxDissResultat, $mTEauResultat, $mConductiviteResultat);

        if (!is_bool($result)) {
            $tabRetour = $result;
            if ($tabRetour[0] == 'warning') {
                $tabStatut['statut'] = 1;
            } else {
                $tabStatut['statut'] = 2;
                $okWeiss = 1;
            }
            $tabStatut['libelle'] = $tabRetour[1];
            $mTxSatOx->setCodeStatut($tabStatut['statut']);
            $mTxSatOx->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($mTxSatOx);
            $mOxDiss->setCodeStatut($tabStatut['statut']);
            $mOxDiss->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($mOxDiss);
            $mTEau->setCodeStatut($tabStatut['statut']);
            $mTEau->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($mTEau);
            $mConductivite->setCodeStatut($tabStatut['statut']);
            $mConductivite->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($mConductivite);
            $emSqe->flush();
        }
        return $okWeiss;
    }

// III.8 Balance ionique (meq) sauf si tous les résultats < LQ
    protected function _balanceIonique($pgCmdPrelev) {

        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', '_balanceIonique');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $controleVraisemblance = $this->get('aeag_sqe.controle_vraisemblance');

        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');

        $tabStatut = array();
        $tabStatut['statut'] = 0;
        $tabStatut['libelle'] = null;
        $okBalanceIonique = 0;

        $par1374 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1374');
        if (!$par1374) {
            $par1374 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1374', 1);
        }
        if ($par1374) {
            $par1374Resultat = $par1374->getResultat();
            $par1374CodeRemarque = $par1374->getCodeRemarque();
        } else {
            $par1374Resultat = null;
            $par1374CodeRemarque = null;
        }

        $par1335 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1335');
        if (!$par1335) {
            $par1335 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1335', 1);
        }
        if ($par1335) {
            $par1335Resultat = $par1335->getResultat();
            $par1335CodeRemarque = $par1335->getCodeRemarque();
        } else {
            $par1335Resultat = null;
            $par1335CodeRemarque = null;
        }

        $par1372 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1372');
        if (!$par1372) {
            $par1372 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1372', 1);
        }
        if ($par1372) {
            $par1372Resultat = $par1372->getResultat();
            $par1372CodeRemarque = $par1372->getCodeRemarque();
        } else {
            $par1372Resultat = null;
            $par1372CodeRemarque = null;
        }

        $par1367 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1367');
        if (!$par1367) {
            $par1367 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1367', 1);
        }
        if ($par1367) {
            $par1367Resultat = $par1367->getResultat();
            $par1367CodeRemarque = $par1367->getCodeRemarque();
        } else {
            $par1367Resultat = null;
            $par1367CodeRemarque = null;
        }

        $par1375 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1375');
        if (!$par1375) {
            $par1375 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1375', 1);
        }
        if ($par1375) {
            $par1375Resultat = $par1375->getResultat();
            $par1375CodeRemarque = $par1375->getCodeRemarque();
        } else {
            $par1375Resultat = null;
            $par1375CodeRemarque = null;
        }

        $par1433 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1433');
        if (!$par1433) {
            $par1433 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1433', 1);
        }
        if ($par1433) {
            $par1433Resultat = $par1433->getResultat();
            $par1433CodeRemarque = $par1433->getCodeRemarque();
        } else {
            $par1433Resultat = null;
            $par1433CodeRemarque = null;
        }


        $par1340 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1340');
        if (!$par1340) {
            $par1340 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1340', 1);
        }
        if ($par1340) {
            $par1340Resultat = $par1340->getResultat();
            $par1340CodeRemarque = $par1340->getCodeRemarque();
        } else {
            $par1340Resultat = null;
            $par1340CodeRemarque = null;
        }

        $par1338 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1338');
        if (!$par1338) {
            $par1338 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1338', 1);
        }
        if ($par1338) {
            $par1338Resultat = $par1338->getResultat();
            $par1338CodeRemarque = $par1338->getCodeRemarque();
        } else {
            $par1338Resultat = null;
            $par1338CodeRemarque = null;
        }

        $par1337 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1337');
        if (!$par1337) {
            $par1337 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1337', 1);
        }
        if ($par1337) {
            $par1337Resultat = $par1337->getResultat();
            $par1337CodeRemarque = $par1337->getCodeRemarque();
        } else {
            $par1337Resultat = null;
            $par1337CodeRemarque = null;
        }

        $par1327 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1327');
        if (!$par1327) {
            $par1327 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1327', 1);
        }
        if ($par1327) {
            $par1327Resultat = $par1327->getResultat();
            $par1327CodeRemarque = $par1327->getCodeRemarque();
        } else {
            $par1327Resultat = null;
            $par1327CodeRemarque = null;
        }

        $par1339 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1339');
        if (!$par1339) {
            $par1339 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1339', 1);
        }
        if ($par1339) {
            $par1339Resultat = $par1339->getResultat();
            $par1339CodeRemarque = $par1339->getCodeRemarque();
        } else {
            $par1339Resultat = null;
            $par1339CodeRemarque = null;
        }

        $cCationParams = array(1374 => $par1374Resultat,
            1335 => $par1335Resultat,
            1372 => $par1372Resultat,
            1367 => $par1367Resultat,
            1375 => $par1375Resultat
        );

        $codeRqCationParams = array(1374 => $par1374CodeRemarque,
            1335 => $par1335CodeRemarque,
            1372 => $par1372CodeRemarque,
            1367 => $par1367CodeRemarque,
            1375 => $par1375CodeRemarque
        );

        $cAnionParams = array(1433 => $par1433Resultat,
            1340 => $par1340Resultat,
            1338 => $par1338Resultat,
            1337 => $par1337Resultat,
            1327 => $par1327Resultat,
            1339 => $par1339Resultat
        );

        $codeRqAnionParams = array(1433 => $par1433CodeRemarque,
            1340 => $par1340CodeRemarque,
            1338 => $par1338CodeRemarque,
            1337 => $par1337CodeRemarque,
            1327 => $par1327CodeRemarque,
            1339 => $par1339CodeRemarque
        );

        $mConductivite = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1303');
        if (!$mConductivite) {
            $mConductivite = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1303', 1);
        } else {
            $mConductivite = null;
        }


        $result = $controleVraisemblance->balanceIonique($cCationParams, $cAnionParams, $codeRqCationParams, $codeRqAnionParams);

        if (is_bool($result)) {
            $result = $controleVraisemblance->balanceIoniqueTds2($cCationParams, $cAnionParams, $codeRqCationParams, $codeRqAnionParams, $mConductivite);
        }

        if (!is_bool($result)) {
            $tabRetour = $result;
            if ($tabRetour[0] == 'warning') {
                $tabStatut['statut'] = 1;
            } else {
                $tabStatut['statut'] = 2;
                $okBalanceIonique = 1;
            }
            $tabStatut['libelle'] = $tabRetour[1];

            $par1374->setCodeStatut($tabStatut['statut']);
            $par1374->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1374);
            $par1335->setCodeStatut($tabStatut['statut']);
            $par1335->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1335);
            $par1372->setCodeStatut($tabStatut['statut']);
            $par1372->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1372);
            $par1367->setCodeStatut($tabStatut['statut']);
            $par1367->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1367);
            $par1375->setCodeStatut($tabStatut['statut']);
            $par1375->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1375);
            $par1433->setCodeStatut($tabStatut['statut']);
            $par1433->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1433);
            $par1340->setCodeStatut($tabStatut['statut']);
            $par1340->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1340);
            $par1338->setCodeStatut($tabStatut['statut']);
            $par1338->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1338);
            $par1337->setCodeStatut($tabStatut['statut']);
            $par1337->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1337);
            $par1327->setCodeStatut($tabStatut['statut']);
            $par1327->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1327);
            $par1339->setCodeStatut($tabStatut['statut']);
            $par1339->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1339);
            $emSqe->flush();
        }
        return $okBalanceIonique;
    }

// III.10 [PO4] (1433) en P < [P total](1350)
    protected function _ortophosphate($pgCmdPrelev) {

        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', '_ortophosphate');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $controleVraisemblance = $this->get('aeag_sqe.controle_vraisemblance');

        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');

        $tabStatut = array();
        $tabStatut['statut'] = 0;
        $tabStatut['libelle'] = null;
        $okOrtophosphate = 0;

        $mPo4 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1433');
        if (!$mPo4) {
            $mPo4 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1433', 1);
        }
        if ($mPo4) {
            $mPo4Resultat = $mPo4->getResultat();
            $mPo4CodeRemarque = $mPo4->getCodeRemarque();
        } else {
            $mPo4Resultat = null;
            $mPo4CodeRemarque = null;
        }

        $mP = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1350');
        if (!$mP) {
            $mP = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1350', 1);
        }
        if ($mP) {
            $mPResultat = $mP->getResultat();
            $mPCodeRemarque = $mP->getCodeRemarque();
        } else {
            $mPResultat = null;
            $mPCodeRemarque = null;
        }

        $result = $controleVraisemblance->orthophosphate($mPo4Resultat, $mPResultat, $mPo4CodeRemarque, $mPCodeRemarque);

        if (!is_bool($result)) {
            $tabRetour = $result;
            if ($tabRetour[0] == 'warning') {
                $tabStatut['statut'] = 1;
            } else {
                $tabStatut['statut'] = 2;
                $okOrtophosphate = 1;
            }
            $tabStatut['libelle'] = $tabRetour[1];

            $mPo4->setCodeStatut($tabStatut['statut']);
            $mPo4->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($mPo4);
            $mP->setCodeStatut($tabStatut['statut']);
            $mP->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($mP);

            $emSqe->flush();
        }
        return $okOrtophosphate;
    }

// III.11 NH4 (1335) en N < Nkj (1319)
    protected function _ammonium($pgCmdPrelev) {

        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', '_ammonium');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $controleVraisemblance = $this->get('aeag_sqe.controle_vraisemblance');

        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');

        $tabStatut = array();
        $tabStatut['statut'] = 0;
        $tabStatut['libelle'] = null;
        $okAmmonium = 0;

        $mNh4 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1335');
        if (!$mNh4) {
            $mNh4 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1335', 1);
        }
        if ($mNh4) {
            $mNh4Resultat = $mNh4->getResultat();
            $mNh4CodeRemarque = $mNh4->getCodeRemarque();
        } else {
            $mNh4Resultat = null;
            $mNh4CodeRemarque = null;
        }

        $mNkj = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1319');
        if (!$mNkj) {
            $mNkj = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1319', 1);
        }
        if ($mNkj) {
            $mNkjResultat = $mNkj->getResultat();
            $mNkjCodeRemarque = $mNkj->getCodeRemarque();
        } else {
            $mNkjResultat = null;
            $mNkjCodeRemarque = null;
        }

        $result = $controleVraisemblance->ammonium($mNh4Resultat, $mNkjResultat, $mNh4CodeRemarque, $mNkjCodeRemarque);

        if (!is_bool($result)) {
            $tabRetour = $result;
            if ($tabRetour[0] == 'warning') {
                $tabStatut['statut'] = 1;
            } else {
                $tabStatut['statut'] = 2;
                $okAmmonium = 1;
            }
            $tabStatut['libelle'] = $tabRetour[1];

            $mNh4->setCodeStatut($tabStatut['statut']);
            $mNh4->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($mNh4);
            $mNkj->setCodeStatut($tabStatut['statut']);
            $mNkj->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($mNkj);

            $emSqe->flush();
        }

        return $okAmmonium;
    }

// III.12 Valeur de pourcentage hors 1312 oxygène (ex : matière sèche ou granulo) : non compris entre 0 et 100 si code unité = 243 ou 246
    protected function _pourcentageHorsOxygene($pgCmdPrelev) {

        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', '_pourcentageHorsOxygene');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');

        $tabStatut = array();
        $tabStatut['statut'] = 0;
        $tabStatut['libelle'] = null;
        $okPourcentageHorsOxygene = 0;

        $par243 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevCodeUniteParametre($pgCmdPrelev, '243', '1312');
        if (!$par243) {
            $par243 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevCodeUniteParametre($pgCmdPrelev, '243', '1312');
        }

        $par246 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevCodeUniteParametre($pgCmdPrelev, '246', '1312');
        if (!$par246) {
            $par246 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevCodeUniteParametre($pgCmdPrelev, '246', '1312');
        }


        if ($par243) {
            foreach ($par243 as $par) {
                $parResultat = $par->getResultat();
                if (!is_null($parResultat)) {
                    if ($parResultat > 100 || $parResultat < 0) {
                        $okPourcentageHorsOxygene++;
                        $tabStatut['statut'] = 2;
                        $tabStatut['libelle'] = 'Valeur pourcentage : pourcentage n\'est pas entre 0 et 100';
                        $par->setCodeStatut($tabStatut['statut']);
                        $par->setLibelleStatut($tabStatut['libelle']);
                        $emSqe->persist($par);
                    }
                }
            }
        }

        if ($par246) {
            foreach ($par246 as $par) {
                $parResultat = $par->getResultat();
                if (!is_null($parResultat)) {
                    if ($parResultat > 100 || $parResultat < 0) {
                        $okPourcentageHorsOxygene++;
                        $tabStatut['statut'] = 2;
                        $tabStatut['libelle'] = 'Valeur pourcentage : pourcentage n\'est pas entre 0 et 100';
                        $par->setCodeStatut($tabStatut['statut']);
                        $par->setLibelleStatut($tabStatut['libelle']);
                        $emSqe->persist($par);
                    }
                }
            }
        }

        $emSqe->flush();

        return $okPourcentageHorsOxygene;
    }

// III.13 Somme des paramètres distincts (1200+1201+1202+1203=5537; 1178+1179 = 1743; 1144+1146+ 1147+1148 = 7146; 2925 + 1292 =  1780) à  (+/- 20%)
    protected function _sommeParametresDistincts($pgCmdPrelev) {

        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', '_sommeParametresDistincts');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $controleVraisemblance = $this->get('aeag_sqe.controle_vraisemblance');

        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');

        $tabStatut = array();
        $tabStatut['statut'] = 0;
        $tabStatut['libelle'] = null;
        $okSommeParametresDistincts = 0;
        $pars = array();
        $i = 0;

        $par1200 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1200');
        if (!$par1200) {
            $par1200 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1200', 1);
        }
        if ($par1200) {
            $par1200Resultat = $par1200->getResultat();
            $pars[$i] = $par1200;
            $i++;
        } else {
            $par1200Resultat = null;
        }

        $par1201 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1201');
        if (!$par1201) {
            $par1201 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1201', 1);
        }
        if ($par1201) {
            $par1201Resultat = $par1201->getResultat();
            $pars[$i] = $par1201;
            $i++;
        } else {
            $par1201Resultat = null;
        }

        $par1202 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1202');
        if (!$par1202) {
            $par1202 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1202', 1);
        }
        if ($par1202) {
            $par1202Resultat = $par1202->getResultat();
            $pars[$i] = $par1202;
            $i++;
        } else {
            $par1202Resultat = null;
        }

        $par1203 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1203');
        if (!$par1203) {
            $par1203 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1203', 1);
        }
        if ($par1203) {
            $par1203Resultat = $par1203->getResultat();
            $pars[$i] = $par1203;
            $i++;
        } else {
            $par1203Resultat = null;
        }

        $par1178 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1178');
        if (!$par1178) {
            $par1178 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1178', 1);
        }
        if ($par1178) {
            $par1178Resultat = $par1178->getResultat();
            $pars[$i] = $par1178;
            $i++;
        } else {
            $par1178Resultat = null;
        }

        $par1179 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1179');
        if (!$par1179) {
            $par1179 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1179', 1);
        }
        if ($par1179) {
            $par1179Resultat = $par1179->getResultat();
            $pars[$i] = $par1179;
            $i++;
        } else {
            $par1179Resultat = null;
        }

        $par1144 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1144');
        if (!$par1144) {
            $par1144 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1144', 1);
        }
        if ($par1144) {
            $par1144Resultat = $par1144->getResultat();
            $pars[$i] = $par1144;
            $i++;
        } else {
            $par1144Resultat = null;
        }

        $par1146 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1146');
        if (!$par1146) {
            $par1146 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1146', 1);
        }
        if ($par1146) {
            $par1146Resultat = $par1146->getResultat();
            $pars[$i] = $par1146;
            $i++;
        } else {
            $par1146Resultat = null;
        }

        $par1147 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1147');
        if (!$par1147) {
            $par1147 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1147', 1);
        }
        if ($par1147) {
            $par1147Resultat = $par1147->getResultat();
            $pars[$i] = $par1147;
            $i++;
        } else {
            $par1147Resultat = null;
        }

        $par1148 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1148');
        if (!$par1148) {
            $par1148 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1148', 1);
        }
        if ($par1148) {
            $par1148Resultat = $par1148->getResultat();
            $pars[$i] = $par1148;
            $i++;
        } else {
            $par1148Resultat = null;
        }

        $par2925 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '2925');
        if (!$par2925) {
            $par2925 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '2925', 1);
        }
        if ($par2925) {
            $par2925Resultat = $par2925->getResultat();
            $pars[$i] = $par12925;
            $i++;
        } else {
            $par2925Resultat = null;
        }

        $par1292 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1292');
        if (!$par1292) {
            $par1292 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1292', 1);
        }
        if ($par1292) {
            $par1292Resultat = $par1292->getResultat();
            $pars[$i] = $par1292;
            $i++;
        } else {
            $par1292Resultat = null;
        }

        $par5537 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '5537');
        if (!$par5537) {
            $par5537 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '5537', 1);
        }
        if ($par5537) {
            $par5537Resultat = $par5537->getResultat();
            $pars[$i] = $par5537;
            $i++;
        } else {
            $par5537Resultat = null;
        }

        $par1743 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1743');
        if (!$par1743) {
            $par1743 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1743', 1);
        }
        if ($par1743) {
            $par1743Resultat = $par1743->getResultat();
            $pars[$i] = $par1743;
            $i++;
        } else {
            $par1743Resultat = null;
        }

        $par7146 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '7146');
        if (!$par7146) {
            $par7146 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '7146', 1);
        }
        if ($par7146) {
            $par7146Resultat = $par7146->getResultat();
            $pars[$i] = $pa7146;
            $i++;
        } else {
            $par7146Resultat = null;
        }

        $par1780 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1780');
        if (!$par1780) {
            $par1780 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1780', 1);
        }
        if ($par1780) {
            $par1780Resultat = $par1780->getResultat();
            $pars[$i] = $par1780;
            $i++;
        } else {
            $par1780Resultat = null;
        }

        $sommeParams = array(0 => array(1200 => $par1200Resultat,
                1201 => $par1201Resultat,
                1202 => $par1202Resultat,
                1203 => $par1203Resultat
            ),
            1 => array(1178 => $par1178Resultat,
                1179 => $par1179Resultat
            ),
            2 => array(1144 => $par1144Resultat,
                1146 => $par1146Resultat,
                1147 => $par1147Resultat,
                1148 => $par1148Resultat
            ),
            3 => array(2925 => $par2925Resultat,
                1292 => $par1292Resultat
            ),
        );

        $resultParams = array(0 => $par5537Resultat,
            1 => $par1743Resultat,
            2 => $par7146Resultat,
            3 => $par1780Resultat);

        $params = array(5537, 1743, 7146, 1780);

        $result = $controleVraisemblance->sommeParametresDistincts($sommeParams, $resultParams, $params);

        // foreach ($results as $result) {

        if (!is_bool($result)) {
            $tabRetour = $result;
            if ($tabRetour[0] == 'warning') {
                $tabStatut['statut'] = 1;
            } else {
                $tabStatut['statut'] = 2;
                $okSommeParametresDistincts = 1;
            }
            $tabStatut['libelle'] = $tabRetour[1];

            for ($i = 0; $i < count($pars); $i++) {
                $par = $pars[$i];
                $par->setCodeStatut($tabStatut['statut']);
                $par->setLibelleStatut($tabStatut['libelle']);
                $emSqe->persist($par);
            }
            $emSqe->flush();
        }
        //}
        return $okSommeParametresDistincts;
    }

//III.14 Contrôle de vraisemblance par parmètres macropolluants : Résultat d’analyse< Valeur max de la base x 2
    protected function _controleVraisemblanceMacroPolluants($pgCmdPrelev) {

        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', '_controleVraisemblanceMacroPolluants');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgProgUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');

        $tabStatut = array();
        $tabStatut['statut'] = 0;
        $tabStatut['libelle'] = null;
        $okControleVraisemblanceMacroPolluants = 0;

        $pgProgUnitesPossiblesParams = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamWithValeurMax();
        foreach ($pgProgUnitesPossiblesParams as $pgProgUnitesPossiblesParam) {
            $parMesure = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, $pgProgUnitesPossiblesParam->getCodeParametre()->getCodeParametre());
            if (!$parMesure) {
                $parMesure = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, $pgProgUnitesPossiblesParam->getCodeParametre()->getCodeParametre(), 1);
            }
            if ($parMesure) {
                $parMesureResultat = $parMesure->getResultat();
            } else {
                $parMesureResultat = null;
            }
            if (!is_null($parMesureResultat)) {
                if ($parMesureResultat > $pgProgUnitesPossiblesParam->getValMax()) {
                    $tabStatut['statut'] = 1;
                    $tabStatut['libelle'] = 'Controle Vraisemblance Macro Polluants : Le résultat est supérieur à la valeur attendue pour le paramètre (' . $pgProgUnitesPossiblesParam->getValMax() . ')';
                    $parMesure->setCodeStatut($tabStatut['statut']);
                    $parMesure->setLibelleStatut($tabStatut['libelle']);
                    $emSqe->persist($parMesure);
                }
            }
        }
        $emSqe->flush();
        return $okControleVraisemblanceMacroPolluants;
    }

}
