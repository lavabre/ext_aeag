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
        $session->set('menu', 'donnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'index');
        $emSqe = $this->get('doctrine')->getManager('sqe');

// Récupération des programmations
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        if ($user->hasRole('ROLE_ADMINSQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnSaisieDonneesByAdmin();
        } else if ($user->hasRole('ROLE_PRESTASQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnSaisieDonneesByPresta($user);
        } else if ($user->hasRole('ROLE_PROGSQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnSaisieDonneesByProg($user);
        }

        $tabProgLotAns = array();
        $i = 0;
        $pgProgPhaseM40 = $repoProgPhases->getPgProgPhasesByCodePhase('M40');
        $pgProgPhaseM50 = $repoProgPhases->getPgProgPhasesByCodePhase('M50');
        foreach ($pgProgLotAns as $pgProgLotAn) {
            $tabProgLotAns[$i]['lotan'] = $pgProgLotAn;
            $nbPrelevs = $repoPgCmdPrelev->getCountPgCmdPrelevByLotan($pgProgLotAn);
            $nbPrelevCorrectes40 = $repoPgCmdPrelev->getCountPgCmdPrelevByLotanPhaseBis($pgProgLotAn, $pgProgPhaseM40);
            $nbPrelevCorrectes50 = $repoPgCmdPrelev->getCountPgCmdPrelevByLotanPhaseBis($pgProgLotAn, $pgProgPhaseM50);
            $tabProgLotAns[$i]['nbDemandes'] = $nbPrelevs;
            $tabProgLotAns[$i]['nbDemandeCorrectes'] = $nbPrelevCorrectes40 + $nbPrelevCorrectes50;
//            if ($pgProgLotAn->getid() == 330) {
//                echo('lot : ' . $pgProgLotAn->getLot()->getNomLot() . ' demandes : ' . $tabProgLotAns[$i]['nbDemandes'] . ' correctes : ' . $tabProgLotAns[$i]['nbDemandeCorrectes'] . '</br>');
//            }
            $i++;
        }

        // return new Response(' ');

        return $this->render('AeagSqeBundle:SaisieDonnees:index.html.twig', array('user' => $user,
                    'lotans' => $tabProgLotAns));
    }

    public function lotPeriodesAction($lotanId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotanId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $prestaprel = null;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            if (substr_count($pgProgLotGrparAn->getGrparRef()->getLibelleGrp(), 'situ') > 0 or substr_count($pgProgLotGrparAn->getGrparRef()->getLibelleGrp(), 'environ') > 0) {
                $prestaprel = $pgProgLotGrparAn->getPrestaDft();
            }
        }
        $nbPrelevs = $repoPgCmdPrelev->getCountPgCmdPrelevByLotan($pgProgLotAn);
        $userPrestataire = null;
        if ($pgProgWebUser) {
            if ($pgProgWebUser->getPrestataire()) {
                $userPrestataire = $pgProgWebUser->getPrestataire();
            } else {
                $userPrestataire = null;
            }
        } else {
            $userPrestataire = null;
        }

        $tabPeriodeAns = array();
        $i = 0;
        foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {

            if ($pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'DEL' and $pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'INV') {
                $tabPeriodeAns[$i]['pgProgLotPeriodeAn'] = $pgProgLotPeriodeAn;
                if ($pgProgLot->getDelaiPrel()) {
                    $dateFin = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
                    $delai = $pgProgLot->getDelaiPrel();
                    $dateFin->add(new \DateInterval('P' . $delai . 'D'));
                } else {
                    $dateFin = clone($pgProgLotPeriodeAn->getPeriode()->getDateFin());
                }
                $tabPeriodeAns[$i]['dateFin'] = $dateFin;
                $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
                $tabStations = array();
                $nbStations = 0;
                $nbStationCorrectes = 0;
                $j = 0;
                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                    $prestataire = $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft();
                    if ($user->hasRole('ROLE_ADMINSQE') or ( $userPrestataire == $prestataire)) {
                        $trouve = false;
                        for ($k = 0; $k < count($tabStations); $k++) {
                            if ($tabStations[$k]['station']->getOuvFoncid() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncid()) {
                                $trouve = true;
                                break;
                            }
                        }
                        if (!$trouve) {
                            $tabStations[$k]['station'] = $pgProgLotPeriodeProg->getStationAn()->getStation();
                            $nbStations++;


//                            if (!$user->hasRole('ROLE_ADMINSQE')) {
//                                $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeByLotanPrestatairePeriode($pgProgLotAn, $prestaprel, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
//                                $pgCmdDemandes[0] = $pgCmdDemande;
//                                $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
//                            } else {
//                                $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
//                            }
                            $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                            $nbPrelevs = 0;
                            $nbPrelevCorrects = 0;
                            foreach ($pgCmdDemandes as $pgCmdDemande) {
                                if ($pgCmdDemande) {

                                    if (!$prestaprel) {
                                        $prestaprel = $pgCmdDemande->getPrestataire();
                                    }
                                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestaprel, $pgCmdDemande, $pgProgLotPeriodeProg->getStationAn()->getStation(), $pgProgLotPeriodeAn->getPeriode());

                                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                        // print_r('demande : ' . $pgCmdDemande->getId() . '  prelev : ' . $pgCmdPrelev->getId() . ' phase : ' . $pgCmdPrelev->getPhaseDmd()->getcodePhase() . '</br>');
                                        if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() != 'M60') {
                                            $nbPrelevs++;
                                        }
                                        if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M40' or $pgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M50') {
                                            $nbPrelevCorrects++;
                                        }
                                    }
                                    $tabStations[$k]['nbPrelevs'] = $nbPrelevs;
                                    $tabStations[$k]['nbPrelevCorrects'] = $nbPrelevCorrects;
                                }
                            }
                            if ($nbPrelevs == $nbPrelevCorrects and $nbPrelevs > 0) {
                                $nbStationCorrectes++;
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
//
//        \Symfony\Component\VarDumper\VarDumper::dump($tabPeriodeAns);
//       return new Response('');


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
        $session->set('menu', 'donnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', ' lotPeriodeTelecharger');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();

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

        $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeAn->getPeriode());
        foreach ($pgCmdDemandes as $pgCmdDemande) {
            //          print_r('prestataire : ' . $pgCmdDemande->getPrestataire()->getAdrCorid() . ' demande : ' . $pgCmdDemande->getId() . ' station : ' . $station->getOuvFoncid() . ' periode : ' . $pgProgLotPeriodeAn->getPeriode()->getid() . '</br>');
            $donneesBrutes = $repoPgCmdDemande->getDonneesBrutesAnalyseByDemande($pgCmdDemande);
            foreach ($donneesBrutes as $ligne) {
                foreach ($ligne as $j => $value) {
                    $ligne[$j] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$j]);
                }
                fputcsv($fichier_csv, $ligne, ';');
            }
        }

        fclose($fichier_csv);

//        \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response('');
//        return new Response('fichier : ' . $chemin . '/' . $fichier . ' ext : ' . $ext . ' size : ' . filesize($chemin . '/' . $fichier));

        \header("Cache-Control: no-cahe, must-revalidate");
        \header('Content-Type', 'text/' . $ext);
        \header('Content-disposition: attachment; filename="' . $fichier . '"');
        \header('Expires: 0');
        \header('Content-Length: ' . filesize($chemin . '/' . $fichier));
        readfile($chemin . '/' . $fichier);
        exit();
    }

    public function lotPeriodeStationsAction($periodeAnId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStations');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
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

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());


        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAnOrderByStation($pgProgLotPeriodeAn);

        if ($pgProgLot->getDelaiPrel()) {
            $dateFin = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
            $delai = $pgProgLot->getDelaiPrel();
            $dateFin->add(new \DateInterval('P' . $delai . 'D'));
        } else {
            $dateFin = clone($pgProgLotPeriodeAn->getPeriode()->getDateFin());
        }

        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $prestaprel = null;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            if (substr_count($pgProgLotGrparAn->getGrparRef()->getLibelleGrp(), 'situ') > 0 or substr_count($pgProgLotGrparAn->getGrparRef()->getLibelleGrp(), 'environ') > 0) {
                $prestaprel = $pgProgLotGrparAn->getPrestaDft();
            }
        }

        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');

        $userPrestataire = null;
        $userPrestataireDemande = false;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }



        $tabStations = array();
        $passage = 0;
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
                $tabStations[$is]['passage'] = $passage;
                if ($passage == 0) {
                    $passage = 1;
                } else {
                    $passage = 0;
                }
                $tabStations[$is]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $pgProgLotPeriodeProg->getStationAn()->getStation()->getCode()) . '.pdf';
                $tabStations[$is]['pgCmdDemandes'] = null;
                $tabPgCmdDemandes = array();
                $id = 0;
                $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                foreach ($pgCmdDemandes as $pgCmdDemande) {
                    // if ($pgCmdDemande->getPhaseDemande()->getcodePhase() != 'D50') {
                    if ($userPrestataire) {
                        if ($userPrestataire == $pgCmdDemande->getPrestataire()) {
                            $userPrestataireDemande = true;
                        } else {
                            $userPrestataireDemande = false;
                        }
                    }
                    $tabPgCmdDemandes[$id]['pgCmdDemande'] = $pgCmdDemande;
                    $tabPgCmdDemandes[$id]['prestataire'] = $pgCmdDemande->getPrestataire();
                    $tabPgCmdDemandes[$id]['pgCmdPrelevs'] = null;
                    $tabPgCmdPrelevs = array();
                    $ip = 0;
                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemandeStationPeriode($pgCmdDemande, $station, $periode);
                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                        if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() != 'M60') {
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
                            $tabPgCmdPrelevs[$ip]['nbParametresAnalyse'] = 0;
                            $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyse'] = 0;
                            $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseCorrect'] = 0;
                            $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseIncorrect'] = 0;
                            $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseErreur'] = 0;
                            $NbCmdMesureEnv = 0;
                            $NbCmdMesureEnvCorrect = 0;
                            $NbCmdMesureEnvIncorrect = 0;
                            $NbCmdMesureEnvErreur = 0;
                            $NbCmdAnalyseSitu = 0;
                            $NbCmdAnalyseSituCorrect = 0;
                            $NbCmdAnalyseSituIncorrect = 0;
                            $NbCmdAnalyseSituErreur = 0;
                            $NbCmdAnalyse = 0;
                            $NbCmdAnalyseCorrect = 0;
                            $NbCmdAnalyseIncorrect = 0;
                            $NbCmdAnalyseErreur = 0;
                            if ($userPrestataire) {
                                $pgProgPrestaTypfic = $repoPgProgPrestaTypfic->getPgProgPrestaTypficByCodeMilieuPrestataireFormatFic($pgProgTypeMilieu, $userPrestataire, 'Saisie');
                            } else {
                                $pgProgPrestaTypfic = $repoPgProgPrestaTypfic->getPgProgPrestaTypficByCodeMilieuPrestataireFormatFic($pgProgTypeMilieu, $pgCmdPrelev->getprestaPrel(), 'Saisie');
                            }
                            //$pgProgPrestaTypfic = $repoPgProgPrestaTypfic->getPgProgPrestaTypficByCodeMilieuPrestataireFormatFic($pgProgTypeMilieu, $pgCmdDemande->getPrestataire(), 'Saisie');
                            if ($pgProgPrestaTypfic) {
                                $nbParametresTerrain = 0;
                                $nbParametresAnalyse = 0;
                                foreach ($pgCmdPrelev->getPprog() as $pgProgLotPeriodeProg1) {
                                    if ($station->getOuvFoncId() == $pgProgLotPeriodeProg1->getStationAn()->getStation()->getOuvFoncId()) {
                                        $pgProgLotGrparAn = $pgProgLotPeriodeProg1->getGrparAn();
                                        if ($pgProgLotGrparAn->getvalide() == 'O') {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByPrestataireGrparan($pgCmdDemande->getPrestataire(), $pgProgLotGrparAn);

                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                // if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV') {
                                                    $nbParametresTerrain++;
                                                    //$pgCmdMesureEnvs = $repoPgCmdMesureEnv->getPgCmdMesureEnvsByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                                    $pgCmdMesureEnvs = $repoPgCmdMesureEnv->getPgCmdMesureEnvsByPrelevParametre($pgCmdPrelev, $pgProgLotParamAn->getCodeParametre()->getCodeParametre());
                                                    foreach ($pgCmdMesureEnvs as $pgCmdMesureEnv) {
                                                        $NbCmdMesureEnv++;
                                                        if ($pgCmdMesureEnv->getCodeStatut() == '0') {
                                                            $NbCmdMesureEnvCorrect++;
                                                        }
                                                        if ($pgCmdMesureEnv->getCodeStatut() == '1') {
                                                            $NbCmdMesureEnvIncorrect++;
                                                        }
                                                        if ($pgCmdMesureEnv->getCodeStatut() == '2') {
                                                            $NbCmdMesureEnvErreur++;
                                                        }
                                                    }
                                                }
                                                if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT') {
                                                    $nbParametresTerrain++;
                                                    $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                                    foreach ($pgCmdAnalyses as $pgCmdAnalyse) {
                                                        $NbCmdAnalyseSitu++;
                                                        if ($pgCmdAnalyse->getCodeStatut() == '0') {
                                                            $NbCmdAnalyseSituCorrect++;
                                                        }
                                                        if ($pgCmdAnalyse->getCodeStatut() == '1') {
                                                            $NbCmdAnalyseSituIncorrect++;
                                                        }
                                                        if ($pgCmdAnalyse->getCodeStatut() == '2') {
                                                            $NbCmdAnalyseSituErreur++;
                                                        }
                                                    }
                                                }
                                                if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                                                    $nbParametresAnalyse++;
                                                    $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                                    foreach ($pgCmdAnalyses as $pgCmdAnalyse) {
                                                        $NbCmdAnalyse++;
                                                        if ($pgCmdAnalyse->getCodeStatut() == '0') {
                                                            $NbCmdAnalyseCorrect++;
                                                        }
                                                        if ($pgCmdAnalyse->getCodeStatut() == '1') {
                                                            $NbCmdAnalyseIncorrect++;
                                                        }
                                                        if ($pgCmdAnalyse->getCodeStatut() == '2') {
                                                            $NbCmdAnalyseErreur++;
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        $tabPgCmdPrelevs[$ip]['nbParametresTerrain'] = $nbParametresTerrain;
                                        $tabPgCmdPrelevs[$ip]['nbParametresAnalyse'] = $nbParametresAnalyse;
//                                    $NbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
//                                    $NbCmdMesureEnvCorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '0');
//                                    $NbCmdMesureEnvIncorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '1');
//                                    $NbCmdMesureEnvErreur = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '2');
//                                    $NbCmdAnalyseSitu = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
//                                    $NbCmdAnalyseSituCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '0');
//                                    $NbCmdAnalyseSituIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '1');
//                                    $NbCmdAnalyseSituErreur = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '2');
//                                    $NbCmdAnalyse = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
//                                    $NbCmdAnalyseCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '0');
//                                    $NbCmdAnalyseIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '1');
//                                    $NbCmdAnalyseErreur = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '2');
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrain'] = $NbCmdMesureEnv + $NbCmdAnalyseSitu;
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainCorrect'] = $NbCmdMesureEnvCorrect + $NbCmdAnalyseSituCorrect;
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainIncorrect'] = $NbCmdMesureEnvIncorrect + $NbCmdAnalyseSituIncorrect;
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainErreur'] = $NbCmdMesureEnvErreur + $NbCmdAnalyseSituErreur;
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyse'] = $NbCmdAnalyse;
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseCorrect'] = $NbCmdAnalyseCorrect;
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseIncorrect'] = $NbCmdAnalyseIncorrect;
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseErreur'] = $NbCmdAnalyseErreur;
                                        if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() <= 'M40') {
                                            if ($userPrestataire) {

                                                //if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() == $userPrestataire->getAdrCorId()) {
                                                // if ($pgCmdDemande->getPrestataire()->getAdrCorId() == $userPrestataire->getAdrCorId()) {
                                                if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' || strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_T') {
                                                    $tabPgCmdPrelevs[$ip]['saisieTerrain'] = 'O';
                                                }
                                                if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' || strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_A') {
                                                    $tabPgCmdPrelevs[$ip]['saisieAnalyse'] = 'O';
                                                }
                                                //   }
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
                    }
                    $tabPgCmdDemandes[$id]['pgCmdPrelevs'] = $tabPgCmdPrelevs;
                    if ($ip > 0) {
                        $id++;
                    }
                    //     }
                }
                sort($tabPgCmdDemandes);
                $tabStations[$is]['pgCmdDemandes'] = $tabPgCmdDemandes;
                if ($id > 0) {
                    $is++;
                }
            }
        }

//        \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response('');

        $dateDepot = new \DateTime();
        $chemin = '/base/extranet/Transfert/Sqe/csv';
        $fichier = $user->getId() . '_' . $dateDepot->format('Y-m-d-H') . '_rapport.csv';
        if (file_exists($chemin . '/' . $fichier)) {
            $rapport = $fichier;
        } else {
            $rapport = null;
        }

        return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeStations.html.twig', array(
                    'userPrestataireDemande' => $userPrestataireDemande,
                    'pgProgWebUser' => $pgProgWebUser,
                    'typeMilieu' => $pgProgTypeMilieu,
                    'lotan' => $pgProgLotAn,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'dateFin' => $dateFin,
                    'stations' => $tabStations,
                    'rapport' => $rapport));
    }

    public function lotPeriodeStationsValiderEnvAction($periodeAnId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());


        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAnOrderByStation($pgProgLotPeriodeAn);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');

        $now = date('Ymd');
        $today = new \DateTime($now);

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }



        $tabStations = array();
        $passage = 0;
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
                $tabStations[$is]['passage'] = $passage;
                if ($passage == 0) {
                    $passage = 1;
                } else {
                    $passage = 0;
                }
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
                        //$pgProgPrestaTypfic = $repoPgProgPrestaTypfic->getPgProgPrestaTypficByCodeMilieuPrestataireFormatFic($pgProgTypeMilieu, $pgCmdPrelev->getprestaPrel(), 'Saisie');
                        $pgProgPrestaTypfic = $repoPgProgPrestaTypfic->getPgProgPrestaTypficByCodeMilieuPrestataireFormatFic($pgProgTypeMilieu, $pgCmdDemande->getPrestataire(), 'Saisie');
                        if ($pgProgPrestaTypfic) {
                            $nbParametresTerrain = 0;
                            $nbParametresAnalyse = 0;
                            foreach ($pgCmdPrelev->getPprog() as $pgProgLotPeriodeProg1) {
                                if ($station->getOuvFoncId() == $pgProgLotPeriodeProg1->getStationAn()->getStation()->getOuvFoncId()) {
                                    $pgProgLotGrparAn = $pgProgLotPeriodeProg1->getGrparAn();
                                    if ($pgProgLotGrparAn->getvalide() == 'O') {
                                        $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                                        $tabParamAns = array();
                                        $nbParamAns = 0;
                                        foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                            //if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                            if ($pgCmdDemande->getPrestataire()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT') {
                                                    $nbParametresTerrain++;
                                                }
                                                if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                                                    $nbParametresAnalyse++;
                                                }
                                                if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV') {
                                                    $nbParametresTerrain++;
                                                    $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                                                    $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                                    if (!$pgCmdMesureEnv) {
                                                        $pgCmdMesureEnv = new PgCmdMesureEnv();
                                                        $pgCmdMesureEnv->setDateMes($today);
                                                        $pgCmdMesureEnv->setPrelev($pgCmdPrelev);
                                                        $pgCmdMesureEnv->setParamProg($pgProgLotParamAn);
                                                        $pgCmdMesureEnv->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                                        $pgCmdMesureEnv->setResultat(null);
                                                        $pgCmdMesureEnv->setCodeRemarque(0);
                                                        $pgCmdMesureEnv->setCodeMethode('0');
                                                        $pgCmdMesureEnv->setCodeStatut(1);
                                                        $pgCmdMesureEnv->setLibelleStatut('Valeur absente');
                                                        $unite = $pgProgLotParamAn->getCodeUnite();
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
                                                    }
                                                    $tabParamAns[$nbParamAns]['pgCmdMesureEnv'] = $pgCmdMesureEnv;
                                                }
                                            }
                                            $nbParamAns++;
                                        }
                                        $tabPgCmdPrelevs[$ip]['pgProgLotParamAns'] = $tabParamAns;
                                    }
                                }
                            }
                            $emSqe->flush();


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

                            $nbTotalParametres = $nbParametresTerrain + $nbParametresAnalyse;
                            $nbTotalParametresCorrectes = $NbCmdMesureEnvCorrect + $NbCmdAnalyseSituCorrect + $NbCmdAnalyseCorrect;
                            $nbTotalParametresIncorrectes = $NbCmdMesureEnvIncorrect + $NbCmdAnalyseSituIncorrect + $NbCmdAnalyseIncorrect;
                            $nbTotalParametresErreurs = $NbCmdMesureEnvErreur + $NbCmdAnalyseSituErreur + $NbCmdAnalyseErreur;
                            $nbTotalParametresOk = $nbTotalParametresCorrectes + $nbTotalParametresIncorrectes;

//
//                            echo('station  : ' . $station->getCode() . '<br/>');
//                            echo('NbCmdMesureEnv : ' . $NbCmdMesureEnv . '<br/>');
//                            echo('NbCmdMesureEnvCorrect : ' . $NbCmdMesureEnvCorrect . '<br/>');
//                            echo('NbCmdMesureEnvIncorrect : ' . $NbCmdMesureEnvIncorrect . '<br/>');
//                            echo('NbCmdMesureEnvErreur : ' . $NbCmdMesureEnvErreur . '<br/>');
//                            echo('<br/>');
//                            echo('NbCmdAnalyseSitu : ' . $NbCmdAnalyseSitu . '<br/>');
//                            echo('NbCmdAnalyseSituCorrect : ' . $NbCmdAnalyseSituCorrect . '<br/>');
//                            echo('NbCmdAnalyseSituIncorrect : ' . $NbCmdAnalyseSituIncorrect . '<br/>');
//                            echo('NbCmdAnalyseSituErreur : ' . $NbCmdAnalyseSituErreur . '<br/>');
//                            echo('<br/>');
//                            echo('NbCmdAnalyse : ' . $NbCmdAnalyse . '<br/>');
//                            echo('NbCmdAnalyseCorrect : ' . $NbCmdAnalyseCorrect . '<br/>');
//                            echo('NbCmdAnalyseIncorrect : ' . $NbCmdAnalyseIncorrect . '<br/>');
//                            echo('NbCmdAnalyseErreur : ' . $NbCmdAnalyseErreur . '<br/>');
//                            echo('<br/>');
//                            echo('nbTotalParametresCorrecctes : ' . $nbTotalParametresCorrectes . '<br/>');
//                            echo('nbTotalParametresIncorrectes : ' . $nbTotalParametresIncorrectes . '<br/>');
//                            echo('nbTotalParametresErreurs : ' . $nbTotalParametresErreurs . '<br/>');
//                            echo('<br/>');
//                            echo('nbTotalParametres : ' . $nbTotalParametres . '<br/>');
//                            echo('nbTotalParametresOk : ' . $nbTotalParametresOk . '<br/>');
//                            echo('<br/>');
//                            echo('<br/>');
//                            if ($station->getCode() == '05122850') {
//                                return new Response();
//                            }

                            if ($nbTotalParametres == $nbTotalParametresOk) {
                                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M30');
                                $pgCmdPrelev->setRealise('O');
                                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
                                //  $pgCmdPrelev->setDatePrelev($today);
                            } else {
                                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M20');
                                $pgCmdPrelev->setRealise('N');
                                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
                            }
                            $emSqe->persist($pgCmdPrelev);
                        }
                        $ip++;
                    }
                    $tabPgCmdDemandes[$id]['pgCmdPrelevs'] = $tabPgCmdPrelevs;
                    $id++;
                }
                //    sort($tabPgCmdDemandes);
                $tabStations[$is]['pgCmdDemandes'] = $tabPgCmdDemandes;

                $is++;
            }
        }

        $emSqe->flush();


//        \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response('');


        return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_stations', array('periodeAnId' => $pgProgLotPeriodeAn->getId())));
    }

    public function lotPeriodeStationsValiderStationsAction($periodeAnId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());


        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAnOrderByStation($pgProgLotPeriodeAn);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R45');

        $now = date('Ymd');
        $today = new \DateTime($now);

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }



        $tabStations = array();
        $passage = 0;
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
                $tabStations[$is]['passage'] = $passage;
                if ($passage == 0) {
                    $passage = 1;
                } else {
                    $passage = 0;
                }
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
                        if ($pgCmdPrelev->getPhaseDmd()->getCodePhase() == 'M30') {
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
                            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R45');
                            $pgCmdFichiersRps->setPhaseFichier($pgProgPhases);
                            $emSqe->persist($pgCmdFichiersRps);
                            $pgCmdPrelev->setFichierRps($pgCmdFichiersRps);

                            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');
                            $pgCmdPrelev->setPhaseDmd($pgProgPhases);
                            $emSqe->persist($pgCmdPrelev);
                            $tabPgCmdPrelevs[$ip]['cmdPrelev'] = $pgCmdPrelev;
                            $ip++;
                        }
                    }
                    $tabPgCmdDemandes[$id]['pgCmdPrelevs'] = $tabPgCmdPrelevs;
                    $id++;

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
                    } else {
                        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D30');
                        $pgCmdDemande->setPhaseDemande($pgProgPhases);
                        $emSqe->persist($pgCmdDemande);
                    }
                }
                sort($tabPgCmdDemandes);
                $tabStations[$is]['pgCmdDemandes'] = $tabPgCmdDemandes;

                $is++;
            }
        }

        $emSqe->flush();


//        \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response('');


        return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_stations', array('periodeAnId' => $pgProgLotPeriodeAn->getId())));
    }

    public function lotPeriodeStationSaisirCommentaireAction($periodeAnId = null, $prelevId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationSaisirCommentaire');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
        $pgCmdPrelevPc = $pgCmdPrelevPcs[0];

        if (isset($_POST['textareaCommentaire'])) {
            $commentaire = $_POST['textareaCommentaire'];
        } else {
            $commentaire = null;
        }

        $pgCmdPrelevPc->setCommentaire($commentaire);

        $emSqe->persist($pgCmdPrelevPc);

        $emSqe->flush();

        return new Response(json_encode(''));
    }

    public function lotPeriodeStationSaisirEnvSituAction($prelevId = null, $periodeAnId = null, $stationId = null, $maj = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
        $pgCmdDemande = $pgCmdPrelev->getDemande();
        $prestataire = $pgCmdDemande->getPrestataire();
        $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $pgCmdPrelev->getPprog();

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
            if ($pgProgLotGrparAn->getvalide() == 'O' and ( $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV' or $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT')) {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $tabGroupes[$nbGroupes]['grparAn'] = $pgProgLotGrparAn;
                $tabGroupes[$nbGroupes]['nbDft'] = 0;
                $tabGroupes[$nbGroupes]['nbAutres'] = 0;
                $tabGroupes[$nbGroupes]['correct'] = 0;
                $tabGroupes[$nbGroupes]['warning'] = 0;
                $tabGroupes[$nbGroupes]['erreur'] = 0;
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                $tabParamAns = array();
                $nbParamAns = 0;
                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                    if ($pgProgLotParamAn->getPrestataire() == $prestataire) {
                        $tabGroupes[$nbGroupes]['nbDft'] ++;
                    } else {
                        $tabGroupes[$nbGroupes]['nbAutres'] ++;
                    }
                    $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($pgProgLotParamAn->getCodeUnite());
                    if ($pgSandreUnites) {
                        $tabParamAns[$nbParamAns]['unite'] = $pgSandreUnites;
                    } else {
                        $tabParamAns[$nbParamAns]['unite'] = null;
                    }
                    if ($pgProgGrpParamRef->getTypeGrp() == 'ENV') {
                        $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, $pgProgLotParamAn->getCodeParametre()->getCodeParametre());
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
                        'cmdPrelevPcs' => $pgCmdPrelevPcs,
                        'groupes' => $tabGroupes,
                        'prestataire' => $prestataire,
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
                        'cmdPrelevPcs' => $pgCmdPrelevPcs,
                        'groupes' => $tabGroupes,
                        'prestataire' => $prestataire,
                        'maj' => $maj,));
        }
    }

    public function lotPeriodeStationResultatEnvSituAction($prelevId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
        $pgProgLotPeriodeProgs = $pgCmdPrelev->getPprog();
        $pgProgPeriodes = $pgProgLotPeriodeAn->getPeriode();

        $nbErreurs = 0;
        $okControlesSpecifiques = 0;
        $okControleVraisemblance = 0;
        $nbParametresEnvSit = 0;
        $nbParametresAna = 0;


        if (isset($_POST['datePrel'])) {
            $dateSaisie = str_replace('/', '-', $_POST['datePrel']);
            $datePrel = new \DateTime($dateSaisie);
            $pgCmdPrelev->setDatePrelev($datePrel);
            $emSqe->persist($pgCmdPrelev);
        } else {
            $datePrel = null;
        }


        if ($datePrel) {
            $autresDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgPeriodes);
            foreach ($autresDemandes as $autreDemande) {
                $autrePrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemandeStationPeriode($autreDemande, $pgRefStationMesure, $pgProgPeriodes);
                foreach ($autrePrelevs as $autrePrelev) {
                    if ($autrePrelev->getid() != $pgCmdPrelev->getId()) {
                        $autrePrelev->setDatePrelev($datePrel);
                        $emSqe->persist($autrePrelev);
                    }
                }
            }
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

                            $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, $parametre);
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
                    $emSqe->flush();
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
//                                if ($prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire) {
                                //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                $nbParametresEnvSit++;
                                $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                if ($pgCmdAnalyse) {
                                    $nbSaisieParametresSit ++;
                                }
//                                }
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
            echo ('$nbParametresEnvSit : ' . $nbParametresEnvSit . ' </br>');
            echo (' $nbParametresAna : ' . $nbParametresAna . ' </br>');
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

        if ($datePrel) {
            $pgCmdPrelev->setDatePrelev($datePrel);
            $emSqe->persist($pgCmdPrelev);
            $emSqe->flush();
        }

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
        $session->set('menu', 'donnees');
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
        $pgCmdDemande = $pgCmdPrelev->getDemande();
        $prestataire = $pgCmdDemande->getPrestataire();

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $pgCmdPrelev->getPprog();

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
            //$prestataire = $pgProgLotGrparAn->getPrestaDft();
            if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $tabGroupes[$nbGroupes]['grparAn'] = $pgProgLotGrparAn;
                $tabGroupes[$nbGroupes]['nbDft'] = 0;
                $tabGroupes[$nbGroupes]['nbAutres'] = 0;
                $tabGroupes[$nbGroupes]['correct'] = 0;
                $tabGroupes[$nbGroupes]['warning'] = 0;
                $tabGroupes[$nbGroupes]['erreur'] = 0;
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByPrestataireGrparan($prestataire, $pgProgLotGrparAn);
                $tabParamAns = array();
                $nbParamAns = 0;
                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                    if ($pgProgLotParamAn->getPrestataire() == $prestataire) {
                        $tabGroupes[$nbGroupes]['nbDft'] ++;
                    } else {
                        $tabGroupes[$nbGroupes]['nbAutres'] ++;
                    }
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
                    //print_r (' nb : ' . count($pgCmdAnalyses) . '<br/>');
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

//return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabGroupes));
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
                        'prestataire' => $prestataire,
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
                        'prestataire' => $prestataire,
                        'maj' => $maj,));
        }
    }

    public function lotPeriodeStationResultatAnaAction($prelevId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgProgUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $repoPgSandreUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgSandreUnitesPossiblesParam');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $now = date('Ymd');
        $today = new \DateTime($now);

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdDemande = $pgCmdPrelev->getDemande();
        $prestataire = $pgCmdDemande->getPrestataire();

        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgCmdDemande->getLotan());
        $prestaprel = null;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            if (substr_count($pgProgLotGrparAn->getGrparRef()->getLibelleGrp(), 'situ') > 0 or substr_count($pgProgLotGrparAn->getGrparRef()->getLibelleGrp(), 'environ') > 0) {
                $prestaprel = $pgProgLotGrparAn->getPrestaDft();
            }
        }
        if (!$prestaprel) {
            $prestaprel = $prestataire;
        }

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $pgCmdPrelev->getPprog();
        $nbErreurs = 0;
        $okControlesSpecifiques = 0;
        $okControleVraisemblance = 0;
        $nbParametresEnvSit = 0;
        $nbParametresAna = 0;
        $nbParametresAnaMaj = 0;

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
            //print_r('groupe : ' . $pgProgLotGrparAn->getId() . ' valide : ' . $pgProgLotGrparAn->getvalide() . ' type : ' . $pgProgLotGrparAn->getGrparRef()->getTypeGrp() . '<br/>');
            if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);

                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire)) {
                        $parametre = $pgProgLotParamAn->getCodeParametre()->getCodeparametre();
                        $nbParametresAna++;
                        $valeur = null;
                        if (!empty($_POST['valeur' . $pgProgLotParamAn->getId()])) {
                            $valeur = $_POST['valeur' . $pgProgLotParamAn->getId()];
                        } else {
                            $valeur = null;
                        }
                        if (!empty($_POST['uniteCode' . $pgProgLotParamAn->getId()])) {
                            $unite = $_POST['uniteCode' . $pgProgLotParamAn->getId()];
                            $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($unite);
                        } else {
                            $unite = null;
                            $pgSandreUnites = null;
                            $pgSandreUnitesPossiblesParams = $repoPgSandreUnitesPossiblesParam->getPgSandreUnitesPossiblesParamByCodeParametre($parametre);
                            foreach ($pgSandreUnitesPossiblesParams as $pgSandreUnitesPossiblesParam) {
                                $pgSandreUnites = $pgSandreUnitesPossiblesParam->getCodeUnite();
                                $unite = $pgSandreUnitesPossiblesParam->getCodeUnite()->getCodeUnite();
                                if ($pgSandreUnitesPossiblesParam->getUniteDefaut() == 'O') {
                                    break;
                                }
                            }
                        }
                        $remarque = null;
                        if (!empty($_POST['remarque' . $pgProgLotParamAn->getId()])) {
                            $remarque = $_POST['remarque' . $pgProgLotParamAn->getId()];
                        } else {
                            $remarque = null;
                        }

                        $tabStatut = array();
                        $tabStatut['ko'] = 0;
                        $tabStatut['statut'] = 0;
                        $tabStatut['libelle'] = null;

                        $inSitu = 2;

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
                        if (strlen($valeur) > 0 or $remarque == 0) {
                            $nbParametresAnaMaj++;
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
                            if (strlen($valeur) > 0) {
                                $pgCmdAnalyse->setResultat($valeur);
                            }
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

                //     return new Response('lu : ' . $nbParametresAna . ' maj : ' . $nbParametresAnaMaj);
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

        // return new Response ('valide : ' . $pgProgLotGrparAn->getvalide()  . ' type : ' .  $pgProgLotGrparAn->getGrparRef()->getTypeGrp() );

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
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
                $pgProgPeriodes = $pgProgLotPeriodeProg->getPeriodAn()->getPeriode();
                if ($pgProgLotGrparAn->getvalide() == 'O') {
                    $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                    foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                        if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire)) {

                            if ($pgProgGrpParamRef->getTypeGrp() == 'ENV') {
//                                if ($prestataire == $pgProgLotParamAn->getPrestataire()) {
                                //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                $nbParametresEnvSit++;
                                $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                if ($pgCmdMesureEnv) {
                                    $nbSaisieParametresEnv++;
                                }
//                                }
                            }

                            if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {
//                                if ($prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire) {
                                //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                $nbParametresEnvSit++;
                                $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                if (count($pgCmdAnalyses) > 0) {
                                    $nbSaisieParametresSit++;
                                }
//                                }
                            }



                            if ($pgProgGrpParamRef->getTypeGrp() == 'ANA') {
//                                if ($prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire) {
                                //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                $nbParametresAna++;
                                $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestaprel, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
                                $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                if (count($pgCmdAnalyses) > 0) {
                                    $nbSaisieParametresAna++;
                                }
//                                }
                            }
                        }
                    }
                }
            }


            echo('prestataire  : ' . $prestataire->getAdrcorId() . '<br/>');
            // $nbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
            echo ('$nbCmdMesureEnv : ' . $nbSaisieParametresEnv . ' </br>');
            // $nbCmdSit = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
            echo ('$nbCmdSit : ' . $nbSaisieParametresSit . ' </br>');
            $nbSaisieParametresEnvSit = $nbSaisieParametresEnv + $nbSaisieParametresSit;
            echo ('$nbParametresEnvSit : ' . $nbParametresEnvSit . ' </br>');
            //$nbSaisieParametresAna = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
            echo ('$nbParametresAna : ' . $nbParametresAna . ' </br>');
            $nbParametresTotal = $nbParametresEnvSit + $nbParametresAna;
            echo ('$nbParametresTotal : ' . $nbParametresTotal . ' </br>');
            echo ('$nbSaisieParametresEnvSit : ' . $nbSaisieParametresEnvSit . ' </br>');
            //$nbSaisieParametresAna = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
            echo ('$nbSaisieParametresAna : ' . $nbSaisieParametresAna . ' </br>');
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

        if ($datePrel) {
            $pgCmdPrelev->setDatePrelev($datePrel);
        }
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
        $session->set('menu', 'donnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationValider');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgWebuserTypmil = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserTypmil');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoUsers = $em->getRepository('AeagUserBundle:User');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotan();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgMarche = $pgProgLot->getMarche();
        $producteur = $pgProgMarche->getRespAdrCor();
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdDemande = $pgCmdPrelev->getDemande();

        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $prestaprel = null;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            if (substr_count($pgProgLotGrparAn->getGrparRef()->getLibelleGrp(), 'situ') > 0 or substr_count($pgProgLotGrparAn->getGrparRef()->getLibelleGrp(), 'environ') > 0) {
                $prestaprel = $pgProgLotGrparAn->getPrestaDft();
            }
        }
        if (!$prestaprel) {
            $prestaprel = $pgCmdDemande->getPrestataire();
        }

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

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }
        $tabStations = array();
        $nbStations = 0;
        $nbStationCorrectes = 0;
        $j = 0;
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAnOrderByStation($pgProgLotPeriodeAn);
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $prestataire = $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft();
            if ($user->hasRole('ROLE_ADMINSQE') or ( $userPrestataire == $prestataire)) {
                $trouve = false;
                for ($k = 0; $k < count($tabStations); $k++) {
                    if ($tabStations[$k]->getOuvFoncid() == $pgProgLotPeriodeProg->getStationAn()->getStation()->getOuvFoncid()) {
                        $trouve = true;
                        break;
                    }
                }
                if (!$trouve) {
                    $nbStations++;
                    $pgCmdDemandes = array();
                    if (!$user->hasRole('ROLE_ADMINSQE')) {
                        $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeByLotanPrestatairePeriode($pgProgLotAn, $prestataire, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                        $pgCmdDemandes[0] = $pgCmdDemande;
                    } else {
                        $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                    }
                    foreach ($pgCmdDemandes as $pgCmdDemande) {
                        if ($pgCmdDemande) {
                            $tabStations[$j] = $pgProgLotPeriodeProg->getStationAn()->getStation();
                            $j++;
                            $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestaprel, $pgCmdDemande, $pgProgLotPeriodeProg->getStationAn()->getStation(), $pgProgLotPeriodeAn->getPeriode());
                            $nbPrelevs = count($pgCmdPrelevs);
                            $nbPrelevCorrects = 0;
                            foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M40') {
                                    $nbPrelevCorrects++;
                                }
                            }
                            if ($nbPrelevs == $nbPrelevCorrects and $nbPrelevs > 0) {
                                $nbStationCorrectes++;
                            }
                        }
                    }
                }
            }
        }
        if ($nbStations == $nbStationCorrectes) {
            $tabDestinataires = array();
            $i = 0;
            $userAdmins = $repoUsers->getUsersByRole('ROLE_ADMINSQE');
            foreach ($userAdmins as $userAdmin) {
                $pgProgWebuserTypmils = $repoPgProgWebuserTypmil->getPgProgWebuserTypmilByWebuser($pgProgWebUser);
                $trouve = false;
                foreach ($pgProgWebuserTypmils as $pgProgWebuserTypmil) {
                    if ($pgProgLotAn->getLot()->getCodeMilieu()->getCodeMilieu() == $pgProgWebuserTypmil->getTypmil()->getCodeMilieu()) {
                        $trouve = false;
                        for ($j = 0; $j < count($tabDestinataires); $j++) {
                            if ($tabDestinataires[$j] == $userAdmin->getEmail()) {
                                $trouve = true;
                                break;
                            }
                        }
                        if (!$trouve) {
                            $tabDestinataires[$i] == $userAdmin;
                            $i++;
                        }
                    }
                }
            }

            $pgProgWebUsers = $repoPgProgWebUsers->getNotAdminPgProgWebusersByProducteur($producteur);
            foreach ($pgProgWebUsers as $pgProgWebUser) {
                if ($pgProgWebUser->getExtId()) {
                    $userAdmin = $repoUsers->getUserById($pgProgWebUser->getExtId());
                    $trouve = false;
                    for ($j = 0; $j < count($tabDestinataires); $j++) {
                        if ($tabDestinataires[$j] == $userAdmin->getEmail()) {
                            $trouve = true;
                            break;
                        }
                    }
                    if (!$trouve) {
                        $tabDestinataires[$i] == $userAdmin;
                        $i++;
                    }
                }
            }


            // Récupération des services.
            $mailer = $this->get('mailer');
            $messages = $this->get('aeag.messages');

            for ($i = 0; $i < count($tabDestinataires); $i++) {
                $userAdmin = $tabDestinataires[$i];
                $texte = "Bonjour ," . PHP_EOL;
                $texte = $texte . 'La saisie des données des ' . $nbStations . ' stations de la période du ' . date_format($pgProgLotPeriodeAn->getPeriode()->getDateDeb(), 'd/m/Y') . ' au ' . date_format($pgProgLotPeriodeAn->getPeriode()->getDateFin(), 'd/m/Y');
                $texte = $texte . "de la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL;
                $texte = $texte . "vient d'être validée par  " . $pgProgWebUser->getNom() . " le " . date_format($pgProgLotAn->getDateModif(), 'd/m/Y') . PHP_EOL;
                $texte = $texte . " " . PHP_EOL;
                $texte = $texte . "Cordialement.";
                $messages->createMessage($user, $userAdmin, $em, $session, $texte);

                $notifications = $this->get('aeag.notifications');
                $notifications->createNotification($user, $userAdmin, $em, $session, $texte);

                if ($userAdmin->getEmail()) {
                    // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
                    $mail = \Swift_Message::newInstance('Wonderful Subject')
                            ->setSubject('Saisie des données des ' . $nbStations . ' stations de la période du ' . date_format($pgProgLotPeriodeAn->getPeriode()->getDateDeb(), 'd/m/Y') . ' au ' . date_format($pgProgLotPeriodeAn->getPeriode()->getDateFin(), 'd/m/Y') . '  valider')
                            ->setFrom('automate@eau-adour-garonne.fr')
                            ->setTo($userAdmin->getEmail())
                            ->setBody($this->renderView('AeagSqeBundle:SaisieDonnees:validerParPeriodeEmail.txt.twig', array(
                                'emetteur' => $pgProgWebUser,
                                'lotPeriodeAn' => $pgProgLotPeriodeAn,
                                'lotan' => $pgProgLotAn,
                                'nbStations' => $nbStations
                    )));

// Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
                    $mailer->send($mail);
                }
            }
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
        $session->set('menu', 'donnees');
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

        return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_stations', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                            'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                            'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
    }

    public function lotPeriodeStationTelechargerAction($prelevId = null, $type = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationTelecharger');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');

        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdFichiersRps = $pgCmdPrelev->getFichierRps();

        if (!$pgCmdFichiersRps) {
            $session->getFlashBag()->add('notice-error', 'Le prélèvement  : ' . $prelevId . ' n\'a pasde fichier Rps associé !');
            exit();
        }


        $chemin = '/base/extranet/Transfert/Sqe/csv';

        if ($type == 'TA') {
            $fichier = 'Saisie-des-donnees-prelev-' . $pgCmdPrelev->getId() . '.csv';
            $donneesBrutes = $repoPgCmdPrelev->getDonneesBrutes($pgCmdFichiersRps);
        } elseif ($type == 'T') {
            $fichier = 'Saisie-des-donnees-prelev-Terrain-' . $pgCmdPrelev->getId() . '.csv';
            $donneesBrutes = $repoPgCmdPrelev->getDonneesBrutesMesureEnv($pgCmdFichiersRps);
        } else {
            $fichier = 'Saisie-des-donnees-prelev-analyse-' . $pgCmdPrelev->getId() . '.csv';
            $donneesBrutes = $repoPgCmdPrelev->getDonneesBrutesAnalyse($pgCmdFichiersRps);
        }
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

        foreach ($donneesBrutes as $ligne) {
            foreach ($ligne as $j => $value) {
                $ligne[$j] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$j]);
            }
            fputcsv($fichier_csv, $ligne, ';');
        }
        fclose($fichier_csv);

        //return new Response ('prelev : ' . $prelevId . ' rps : ' . $pgCmdFichiersRps->getId() .  ' fichier : ' . $chemin . '/' . $fichier . ' ext : ' . $ext. ' size : ' . filesize($chemin . '/' . $fichier));

        \header("Cache-Control: no-cahe, must-revalidate");
        \header('Content-Type', 'text/' . $ext);
        \header('Content-disposition: attachment; filename="' . $fichier . '"');
        \header('Content-Length: ' . filesize($chemin . '/' . $fichier));
        readfile($chemin . '/' . $fichier);
        exit();
    }

    public function lotPeriodeStationsIntegrerAction($periodeAnId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationsIntegrer');
        $emSqe = $this->get('doctrine')->getManager('sqe');


        return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeStationsIntegrer.html.twig', array(
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
        $session->set('menu', 'donnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', 'lotPeriodeStationsIntegrerFichier');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdPrelevPc = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgSandreSupports = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgProgUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $repoPgSandreUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgSandreUnitesPossiblesParam');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $repoPgSandreParametres = $emSqe->getRepository('AeagSqeBundle:PgSandreParametres');
        $repoPgSandreZoneVerticaleProspectee = $emSqe->getRepository('AeagSqeBundle:PgSandreZoneVerticaleProspectee');


        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgMarche = $pgProgLot->getMarche();
        $marche = $pgProgMarche->getTypeMarche();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgPeriode = $pgProgLotPeriodeAn->getPeriode();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }

        if ($pgProgLot->getDelaiPrel()) {
            $dateFin = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
            $delai = $pgProgLot->getDelaiPrel();
            $dateFin->add(new \DateInterval('P' . $delai . 'D'));
        }

        if ($pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'DEL' and $pgProgLotPeriodeAn->getCodeStatut()->getCodeStatut() != 'INV') {
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
                    $tabStations[$j]['prestataire'] = $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft();
                    $tabStations[$j]['station'] = $pgProgLotPeriodeProg->getStationAn()->getStation();
                    $nbStations++;
                    $j++;
                }
            }

//            \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//            return new Response('');
            sort($tabStations);
            for ($i = 0; $i < count($tabStations); $i++) {
                //     foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                $prestataire = $tabStations[$i]['prestataire'];
                $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgPeriode);
                $k = 0;
                foreach ($pgCmdDemandes as $pgCmdDemande) {
                    $prestataire = $pgCmdDemande->getPrestataire();
                    //print_r('prestataire : ' . $prestataire->getAdrCorId() . ' demande : ' . $pgCmdDemande->getid() . ' station : ' .  $tabStations[$i]['station']->getOuvFoncId() . ' periode : ' . $pgProgPeriode->getid() . '<br/>');
                    //$pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $tabStations[$i]['station'], $pgProgPeriode);
                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemandeStationPeriode($pgCmdDemande, $tabStations[$i]['station'], $pgProgPeriode);
                    $tabStations[$i]['demande'][$k]['demande'] = $pgCmdDemande;
                    $tabStations[$i]['demande'][$k]['prelevs'] = array();
                    $l = 0;
                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                        $tabStations[$i]['demande'][$k]['prelevs'][$l] = $pgCmdPrelev;
                    }
                    $k++;
                }
                //      }
            }
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
                    //validate file extensions
                    if (in_array($ext, array('exe', 'php', 'bat', 'ini', 'com', 'cmd', 'bin'))) {
                        $valid = false;
                        $response = 'extension du fichier incorrecte.';
                    }
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
//            $fichierIn = fopen($pathBase . '/' . $name, "r");
//            $fichierOut = fopen($pathBase . '/' . 'donnees-' . $user->getId() . '.csv', "w+");
                $rapport = fopen($pathBase . '/' . $user->getId() . '_' . $dateDepot->format('Y-m-d-H') . '_rapport.csv', "w+");
                $contenu = 'rapport d\'intégration du fichier : ' . $name . ' déposé le ' . $dateDepot->format('d/m/Y') . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
                $erreur = 0;
//            $ligne = 0;
//            while (($n = fgets($fichierIn, 1024)) !== false) {
//                $n = str_replace(CHR(10), "", $n);
//                $n = str_replace(CHR(13), "\r\n", $n);
//                fputs($fichierOut, $n);
//            }
//            fclose($fichierIn);
//            fclose($fichierOut);
                $ligne = 0;
                $nbErreurs = 0;
                $okControlesSpecifiques = 0;
                $okControleVraisemblance = 0;
                $nbParametresEnvSit = 0;
                $nbParametresAna = 0;
//            $fichier = fopen($pathBase . '/' . 'donnees-' . $user->getId() . '.csv', "r");
                $fichier = fopen($pathBase . '/' . $name, "r");
                $tab = fgetcsv($fichier, 1024, ';', '\'');
                while (($tab = fgetcsv($fichier, 1024, ';', '\'')) !== false) {
//            while (!feof($fichier)) {
//                $tab = fgetcsv($fichier, 1024, ';');
                    if (count($tab) > 1) {
                        $err = false;
                        $ano = false;
                        $ligne++;
                        $codeStation = $tab[1];
                        $prelevs = array();
                        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByCode($codeStation);
                        if (!$pgRefStationMesure) {
                            $err = true;
                            $contenu = 'ligne  ' . $ligne . '  :  code station inconnu (' . $tab[1] . ')' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        } else {
                            $trouve = false;
                            if (count($tabStations) > 0) {
                                for ($i = 0; $i < count($tabStations); $i++) {
                                    if ($tabStations[$i]['station'] == $pgRefStationMesure) {
                                        $trouve = true;
                                        break;
                                    }
                                }
                            }
                            if (!$trouve) {
                                $err = true;
                                $contenu = 'ligne  ' . $ligne . '  :  code station  (' . $codeStation . ') non référencé dans la liste' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        }

                        $siret_prestataire = $tab[5];
                        $siret_labo = $tab[26];
                        $pgRefCorresPresta = null;
                        if (strlen($siret_labo) > 1) {
                            $pgRefCorresPresta = $repoPgRefCorresPresta->getPgRefCorresPrestaByCodeSiret($siret_labo);
                        } else {
                            $pgRefCorresPresta = $repoPgRefCorresPresta->getPgRefCorresPrestaByCodeSiret($siret_prestataire);
                        }

                        $pgCmdPrelevs = array();
                        if (!$pgRefCorresPresta) {
                            $err = true;
                            $contenu = 'ligne  ' . $ligne . '  :  Siret préleveur inconnu (' . $tab[5] . ')' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        } else {
                            if ($trouve) {
                                for ($j = 0; $j < count($tabStations[$i]['demande']); $j++) {
                                    if ($tabStations[$i]['demande'][$j]['demande']->getPrestataire() == $pgRefCorresPresta) {
                                        $pgCmdDemande = $tabStations[$i]['demande'][$j]['demande'];
                                        $pgCmdPrelevs = array();
                                        for ($k = 0; $k < count($tabStations[$i]['demande'][$j]['prelevs']); $k++) {
                                            $pgCmdPrelevs[$k] = $tabStations[$i]['demande'][$j]['prelevs'][$k];
                                        }
                                        //  $pgCmdPrelev = $tabStations[$i]['demande'][$j]['prelev'];
                                        break;
                                    }
                                }
                            }
                        }

                        $dateActuel = new \DateTime();
                        $dateActuel->add(new \DateInterval('P15D'));
                        $date = $tab[7];
                        $date = str_replace('"', '', $date);
                        $tabDate = explode(' ', $date);
                        if (count($tabDate) != 2) {
                            $err = true;
                            $contenu = 'ligne  ' . $ligne . '  :  date heure incorrecte (' . $date . ')' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        } else {
                            $tabDate1 = explode('-', $date);
                            $tabDate2 = explode(':', $date);
                            if (count($tabDate1) != 3 or count($tabDate2) != 2) {
                                $err = true;
                                $contenu = 'ligne  ' . $ligne . '  :  date heure incorrecte (' . $date . '). Le format attendu est : YYYY-MM-DD HH:MM' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            } else {
                                list( $annee, $mois, $jour, $heure, $min ) = sscanf($date, "%d-%d-%d %d:%d");
                                try {
                                    $datePrel = new \DateTime($annee . '-' . $mois . '-' . $jour . ' ' . $heure . ':' . $min);
                                    if (!$datePrel) {
                                        $err = true;
                                        $contenu = 'ligne  ' . $ligne . '  :  date heure incorrecte (' . $date . '). Le format attendu est : YYYY-MM-DD HH:MM' . CHR(13) . CHR(10);
                                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                        fputs($rapport, $contenu);
                                    }
                                } catch (Exception $e) {
                                    echo $e->getMessage();
                                    $err = true;
                                    $contenu = 'ligne  ' . $ligne . '  :  date heure incorrecte (' . $date . '). Le format attendu est : YYYY-MM-DD HH:MM' . CHR(13) . CHR(10);
                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($rapport, $contenu);
                                }
                            }
                        }

                        $codeParametre = $tab[8];
                        $pgSandreParametre = $repoPgSandreParametres->getPgSandreParametresByCodeParametre($codeParametre);
                        if (!$pgSandreParametre) {
                            $err = true;
                            $contenu = 'ligne  ' . $ligne . '  :  Code du paramètre inconnu (' . $tab[8] . ')' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        } else {
                            $trouve = false;
                            foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
                                if ($pgProgLotGrparAn->getvalide() == 'O') {
                                    $pgProgLotParamAn = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparAnCodeParametre($pgProgLotGrparAn, $pgSandreParametre);
                                    if ($pgProgLotParamAn) {
                                        $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
                                        $trouve = true;
                                        break;
                                    }
                                }
                            }
                            if (!$trouve) {
                                //$err = true;
                                $ano = true;
                                $contenu = 'ligne  ' . $ligne . '  :  Paramètre  (' . $tab[8] . ') non prévu dans le lot' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                $pgProgLotParamAn = null;
                                fputs($rapport, $contenu);
                            }
                        }

                        $codeZoneVerticale = $tab[11];
                        if ($codeZoneVerticale) {
                            $pgSandreZoneVerticaleProspectee = $repoPgSandreZoneVerticaleProspectee->getPgSandreZoneVerticaleProspecteeByCodeZone($codeZoneVerticale);
                            if (!$pgSandreZoneVerticaleProspectee) {
                                $err = true;
                                $contenu = 'ligne  ' . $ligne . '  :  Zone verticale inconnue (' . $tab[11] . ')' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        }

                        $codeSupport = $tab[13];
                        if ($codeSupport) {
                            $pgSandreSupport = $repoPgSandreSupports->getPgSandreSupportsByCodeSupport($codeSupport);
                            if (!$pgSandreSupport) {
                                $err = true;
                                $contenu = 'ligne  ' . $ligne . '  :  Code support inconnu (' . $tab[13] . ')' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        }

                        $codeFraction = $tab[15];
                        if ($codeFraction) {
                            $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($codeFraction);
                            if (!$pgSandreFraction) {
                                $err = true;
                                $contenu = 'ligne  ' . $ligne . '  :  Code fraction inconnu (' . $tab[15] . ')' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        } else {
                            $err = true;
                            $contenu = 'ligne  ' . $ligne . '  :  Code fraction obligatoire' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }

                        $valeur = str_replace(',', '.', $tab[20]);
                        if (!is_numeric($valeur)) {
                            $err = true;
                            $contenu = 'ligne  ' . $ligne . '  :  valeur non numerique  (' . $tab[20] . ')' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }

                        $unite = $tab[22];
                        if ($unite) {
                            $pgSandreUnite = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($unite);
                            if (!$pgSandreUnite) {
                                $err = true;
                                $contenu = 'ligne  ' . $ligne . '  :  Code unité inconnu (' . $tab[22] . ')' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            } else {
                                if ($pgSandreParametre) {
                                    $pgProgUnitesPossiblesParams = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametre($pgSandreParametre->getCodeParametre());
                                    if ($pgProgUnitesPossiblesParams) {
                                        $trouve = false;
                                        foreach ($pgProgUnitesPossiblesParams as $pgProgUnitesPossiblesParam) {
                                            if ($pgProgUnitesPossiblesParam->getCodeUnite()->getCodeUnite() == $pgSandreUnite->getCodeUnite()) {
                                                $trouve = true;
                                                break;
                                            }
                                        }
                                        if (!$trouve) {
                                            $err = true;
                                            $contenu = 'ligne  ' . $ligne . '  :  Code unité (' . $tab[22] . ')  interdit pour le paramètre (' . $tab[8] . ')' . CHR(13) . CHR(10);
                                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                            fputs($rapport, $contenu);
                                        }
                                    }
                                }
                            }
                        } else {
                            if ($pgSandreParametre) {
                                $pgProgUnitesPossiblesParams = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametre($pgSandreParametre->getCodeParametre());
                                if ($pgProgUnitesPossiblesParams) {
                                    $trouve = false;
                                    foreach ($pgProgUnitesPossiblesParams as $pgProgUnitesPossiblesParam) {
                                        if ($pgProgUnitesPossiblesParam->getUniteDefaut() == 'O') {
                                            $unite = $pgProgUnitesPossiblesParam->getCodeUnite()->getCodeUnite();
                                            $trouve = true;
                                            break;
                                        }
                                    }
                                }
                                if (!$trouve) {
                                    $err = true;
                                    $contenu = 'ligne  ' . $ligne . '  :  Code unité obilgatoire pour le paramètre (' . $tab[8] . ')' . CHR(13) . CHR(10);
                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($rapport, $contenu);
                                }
                            }
                        }

                        if (count($pgCmdPrelevs) == 1) {
                            $pgCmdPrelev = $pgCmdPrelevs[0];
                        } else {
                            for ($k = 0; $k < count($pgCmdPrelevs); $k++) {
                                if ($pgCmdPrelevs[$k]->getCodeSupport()->getCodeSupport() == $pgSandreSupport->getCodeSupport()) {
                                    $pgCmdPrelev = $pgCmdPrelevs[$k];
                                }
                            }
                        }
                        //$pgCmdPrelev = $tabStations[$i]['prelev'];
                        if (!$pgCmdPrelev) {
                            $err = true;
                            $contenu = 'ligne  ' . $ligne . '  :  Pas de prélevement pour la demande : ' . $pgCmdDemande->getCodeDemandeCmd() . ' de la station : ' . $tab[1] . ' pour le prestataire : ' . $prestataire->getAncnum() . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }

                        //$contenu = 'ligne  ' . $ligne . '  : paramètre (' . $tab[8] . ')'  . ' prelev : ' . $pgCmdPrelev->getId()  . CHR(13) . CHR(10);
                        //$contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        //fputs($rapport, $contenu);

                        if ($err) {
                            $erreur++;
                        } else {

                            if (!$ano) {

                                $remarque = $tab[19];
                                $valeur = str_replace(',', '.', $tab[20]);
                                $lqM = $tab[25];

                                // $pgCmdPrelev = $tabStations[$i]['prelev'];

                                $okControleVraisemblance = 0;
                                $okControlesSpecifiques = 0;



                                if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {

                                    $AnalyseNumOrdre = 1;
                                    $nbParametresEnvSit++;
                                    $tabStatut = array();
                                    $tabStatut['ko'] = 0;
                                    $tabStatut['statut'] = 0;
                                    $tabStatut['libelle'] = null;
                                    $parametre = $codeParametre;
                                    $inSitu = 1;
                                    if (strlen($valeur) > 0) {
                                        $tabStatut = $this->_controleVraisemblance_fichier($parametre, $valeur, $remarque, $unite, $inSitu, $lqM, $marche, $pgSandreFraction, $tabStatut);
                                        $okControleVraisemblance = $okControleVraisemblance + $tabStatut['ko'];
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

                                        $pgCmdAnalyse->setDateAna($datePrel);
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
                                            $pgCmdAnalyse->setDateAna($datePrel);
                                            $pgCmdAnalyse->setResultat(null);
                                            $pgCmdAnalyse->setCodeRemarque($remarque);
                                            $pgCmdAnalyse->setCodeMethode('0');
                                            $pgCmdAnalyse->setCodeStatut(1);
                                            $pgCmdAnalyse->setLibelleStatut('Valeur absente');
                                            if (strlen($unite) == 0) {
                                                $pgSandreUnitesPossiblesParams = $repoPgSandreUnitesPossiblesParam->getPgSandreUnitesPossiblesParamByCodeParametre($codeParametre);
                                                foreach ($pgSandreUnitesPossiblesParams as $pgSandreUnitesPossiblesParam) {
                                                    if ($pgSandreUnitesPossiblesParam->getUniteDefaut() == 'O') {
                                                        $unite = $pgSandreUnitesPossiblesParam->getCodeUnite()->getCodeUnite();
                                                        break;
                                                    }
                                                }
                                            }
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
                                    $tabStatut = array();
                                    $tabStatut['ko'] = 0;
                                    $tabStatut['statut'] = 0;
                                    $tabStatut['libelle'] = null;
                                    $parametre = $codeParametre;
                                    $inSitu = 1;


                                    if (strlen($valeur) > 0) {
                                        $tabStatut = $this->_controleVraisemblance_fichier($parametre, $valeur, $remarque, $unite, $inSitu, $lqM, $marche, $pgSandreFraction, $tabStatut);
                                        $okControleVraisemblance = $okControleVraisemblance + $tabStatut['ko'];
                                    }

                                    $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                    if (strlen($valeur) > 0) {
                                        if (!$pgCmdMesureEnv) {
                                            $pgCmdMesureEnv = new PgCmdMesureEnv();
                                            $pgCmdMesureEnv->setDateMes($datePrel);
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
                                                $pgCmdMesureEnv->setDateMes($datePrel);
                                                $pgCmdMesureEnv->setPrelev($pgCmdPrelev);
                                                $pgCmdMesureEnv->setParamProg($pgProgLotParamAn);
                                                $pgCmdMesureEnv->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                            }
                                            $pgCmdMesureEnv->setResultat(null);
                                            $pgCmdMesureEnv->setCodeRemarque($remarque);
                                            $pgCmdMesureEnv->setCodeMethode('0');
                                            $pgCmdMesureEnv->setCodeStatut(1);
                                            $pgCmdMesureEnv->setLibelleStatut('Valeur absente');
                                            if (strlen($unite) == 0) {
                                                $pgSandreUnitesPossiblesParams = $repoPgSandreUnitesPossiblesParam->getPgSandreUnitesPossiblesParamByCodeParametre($codeParametre);
                                                foreach ($pgSandreUnitesPossiblesParams as $pgSandreUnitesPossiblesParam) {
                                                    if ($pgSandreUnitesPossiblesParam->getUniteDefaut() == 'O') {
                                                        $unite = $pgSandreUnitesPossiblesParam->getCodeUnite()->getCodeUnite();
                                                        break;
                                                    }
                                                }
                                            }
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

                                    $AnalyseNumOrdre = 1;
                                    $nbParametresAna++;
                                    $tabStatut = array();
                                    $tabStatut['ko'] = 0;
                                    $tabStatut['statut'] = 0;
                                    $tabStatut['libelle'] = null;
                                    $parametre = $codeParametre;
                                    $inSitu = 1;

                                    //$contenu = 'ligne  ' . $ligne . '  :  paramètre ' . $parametre . ', valeur ' . $valeur . '(nb=' . $nbParametresAna . ')' . CHR(13) . CHR(10);
                                    //$contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    //fputs($rapport, $contenu);

                                    if (strlen($valeur) > 0) {
                                        $tabStatut = $this->_controleVraisemblance_fichier($parametre, $valeur, $remarque, $unite, $inSitu, $lqM, $marche, $pgSandreFraction, $tabStatut);
                                        $okControleVraisemblance = $okControleVraisemblance + $tabStatut['ko'];
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
                                            $pgCmdAnalyse->setLieuAna('2');
                                            $pgCmdAnalyse->setPrelevId($pgCmdPrelev->getId());
                                            $pgCmdAnalyse->setNumOrdre($AnalyseNumOrdre);
                                            $pgCmdAnalyse->setParamProg($pgProgLotParamAn);
                                            $pgCmdAnalyse->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                            if ($pgSandreFraction) {
                                                $pgCmdAnalyse->setCodeFraction($pgSandreFraction);
                                            }
                                        }

                                        $pgCmdAnalyse->setDateAna($datePrel);
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
                                                $pgCmdAnalyse->setLieuAna('2');
                                                $pgCmdAnalyse->setPrelevId($pgCmdPrelev->getId());
                                                $pgCmdAnalyse->setNumOrdre($AnalyseNumOrdre);
                                                $pgCmdAnalyse->setParamProg($pgProgLotParamAn);
                                                $pgCmdAnalyse->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                                if ($pgSandreFraction) {
                                                    $pgCmdAnalyse->setCodeFraction($pgSandreFraction);
                                                }
                                            }
                                            $pgCmdAnalyse->setDateAna($datePrel);
                                            $pgCmdAnalyse->setResultat(null);
                                            $pgCmdAnalyse->setCodeRemarque($remarque);
                                            $pgCmdAnalyse->setCodeMethode('0');
                                            $pgCmdAnalyse->setCodeStatut(1);
                                            $pgCmdAnalyse->setLibelleStatut('Valeur absente');
                                            if (strlen($unite) == 0) {
                                                $pgSandreUnitesPossiblesParams = $repoPgSandreUnitesPossiblesParam->getPgSandreUnitesPossiblesParamByCodeParametre($codeParametre);
                                                foreach ($pgSandreUnitesPossiblesParams as $pgSandreUnitesPossiblesParam) {
                                                    if ($pgSandreUnitesPossiblesParam->getUniteDefaut() == 'O') {
                                                        $unite = $pgSandreUnitesPossiblesParam->getCodeUnite()->getCodeUnite();
                                                        break;
                                                    }
                                                }
                                            }

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
                                $emSqe->flush();

                                if ($okControleVraisemblance != 0) {
                                    $nbErreurs++;
                                } else {
                                    $okControlesSpecifiques = $this->_controlesSpecifiques($pgCmdPrelev);
                                }
                                if ($okControlesSpecifiques != 0) {
                                    $nbErreurs++;
                                }

                                $pgCmdPrelev->setDatePrelev($datePrel);
                                $emSqe->persist($pgCmdPrelev);

                                $emSqe->flush();
                            }
                        }
                    }
                }

                for ($i = 0; $i < count($tabStations); $i++) {
                    $nbSaisieParametresEnv = 0;
                    $nbSaisieParametresSit = 0;
                    $nbSaisieParametresAna = 0;
                    $nbParametresEnvSit = 0;
                    $nbParametresAna = 0;

                    for ($j = 0; $j < count($tabStations[$i]['demande']); $j++) {
                        if ($tabStations[$i]['demande'][$j]['demande']->getPrestataire() == $pgRefCorresPresta) {
                            $prestataire = $tabStations[$i]['demande'][$j]['demande']->getPrestataire();
                            $pgCmdDemande = $tabStations[$i]['demande'][$j]['demande'];
                            $pgCmdPrelevs = $tabStations[$i]['demande'][$j]['prelevs'];
                            if (count($pgCmdPrelevs) == 1) {
                                $pgCmdPrelev = $pgCmdPrelevs[0];
                            } else {
                                for ($k = 0; $k < count($pgCmdPrelevs); $k++) {
                                    if ($pgCmdPrelevs[$k]->getCodeSupport()->getCodeSupport() == $pgSandreSupport->getCodeSupport()) {
                                        $pgCmdPrelev = $pgCmdPrelevs[$j];
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    //foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                    $pgProgLotPeriodeProgsPrelev = $pgCmdPrelev->getPprog();
                    foreach ($pgProgLotPeriodeProgsPrelev as $pgProgLotPeriodeProg) {
                        $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
                        $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
                        $pgProgPeriodes = $pgProgLotPeriodeProg->getPeriodAn()->getPeriode();
                        if ($pgProgLotGrparAn->getvalide() == 'O') {
                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire)) {

                                    if ($pgProgGrpParamRef->getTypeGrp() == 'ENV') {
                                        echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                        $nbParametresEnvSit++;
                                        if ($pgCmdPrelev) {
                                            $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                            if ($pgCmdMesureEnv) {
                                                $nbSaisieParametresEnv ++;
                                            }
                                        }
                                    }

                                    if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {
                                        if ($prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire) {
                                            //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                            $nbParametresEnvSit++;
                                            if ($pgCmdPrelev) {
                                                $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                                if ($pgCmdAnalyse) {
                                                    $nbSaisieParametresSit ++;
                                                }
                                            }
                                        }
                                    }


                                    if ($pgProgGrpParamRef->getTypeGrp() == 'ANA') {
                                        //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                        $nbParametresAna++;
                                        if ($pgCmdPrelev) {
                                            $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                            if ($pgCmdAnalyse) {
                                                $nbSaisieParametresAna ++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $nbSaisieParametresEnvSit = $nbSaisieParametresEnv + $nbSaisieParametresSit;
                    $nbParametresTotal = $nbParametresEnvSit + $nbParametresAna;
                    $nbSaisieParametresTotal = $nbSaisieParametresEnvSit + $nbSaisieParametresAna;

                    //$contenu = CHR(13) . CHR(10) . 'prelev  : ' . $pgCmdPrelev->getId() . ', nbEnvSit  : ' . $nbParametresEnvSit . ', nbAna  : ' . $nbParametresAna . ', nbEnvSitSaisis  : ' . $nbSaisieParametresEnvSit . ', nbAnaSaisis  : ' . $nbSaisieParametresAna . CHR(13) . CHR(10);
                    //$contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    //fputs($rapport, $contenu);
//                    print_r('$nbSaisieParametresEnv : ' . $nbSaisieParametresEnv . '<br/>');
//                    print_r('$nbSaisieParametresSit : ' . $nbSaisieParametresSit . '<br/>');
//                    print_r('$nbSaisieParametresEnvSit : ' . $nbSaisieParametresEnvSit . '<br/>');
//                    print_r('$nbSaisieParametresAna : ' . $nbSaisieParametresAna . '<br/>');
//                    print_r('$nbSaisieParametresTotal : ' . $nbSaisieParametresTotal . '<br/>');
//                    return new Response('');

                    if ($pgCmdPrelev) {

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
                        $emSqe->persist($pgCmdPrelev);
                    }
                }

                $emSqe->flush();

                $contenu = CHR(13) . CHR(10) . 'nombre de lignes traitées  : ' . $ligne . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
                $contenu = 'nombre de lignes en erreur  : ' . $erreur . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
                fclose($rapport);
                fclose($fichier);
                //              unlink($pathBase . '/' . $name);
//            unlink($pathBase . '/donnees-' . $user->getId() . '.csv');
                // envoi mail  aux presta connecte
                $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
                if ($pgProgWebUser) {
                    $objetMessage = "fichier de données ";
                    $txtMessage = "Un fichier de données a été déposé sur le lot " . $pgProgLot->getNomLot() . " pour la période du " . $pgProgPeriode->getDateDeb()->format('d/m/Y') . " au " . $dateFin->format('d/m/Y');
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
                    $message = 'un email  vous a été envoyé avec en pièce jointe le fichier rapport du dépôt ';

                    $notifications = $this->get('aeag.notifications');
                    $notifications->createNotification($user, $user, $em, $session, $message);
                }
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
        $session->set('menu', 'donnees');
        $session->set('controller', 'SaisieDonnees');
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

    public function lotPeriodeLacsAction($periodeAnId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAnOrderByStation($pgProgLotPeriodeAn);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');

        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $prestaprel = null;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            if (substr_count($pgProgLotGrparAn->getGrparRef()->getLibelleGrp(), 'situ') > 0 or substr_count($pgProgLotGrparAn->getGrparRef()->getLibelleGrp(), 'environ') > 0) {
                $prestaprel = $pgProgLotGrparAn->getPrestaDft();
            }
        }

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }

        $tabStations = array();
        $passage = 0;
        $is = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $prestataire = $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft();
            $periode = $pgProgLotPeriodeProg->getPeriodan()->getPeriode();
            $trouve = false;
            if (count($tabStations) > 0) {
                for ($k = 0; $k < count($tabStations); $k++) {
//                            \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//                            return new Response('');
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
                $tabStations[$is]['passage'] = $passage;
                if ($passage == 0) {
                    $passage = 1;
                } else {
                    $passage = 0;
                }
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
                        if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() != 'M60') {
                            $tabPgCmdPrelevs[$ip]['cmdPrelev'] = $pgCmdPrelev;
                            $tabCodePrelevCmd = explode("_", $pgCmdPrelev->getCodePrelevCmd());
                            $tabPgCmdPrelevs[$ip]['type'] = $tabCodePrelevCmd[2];
                            $tabPgCmdPrelevs[$ip]['valider'] = 'N';
                            $tabPgCmdPrelevs[$ip]['devalider'] = 'N';
                            $tabPgCmdPrelevs[$ip]['saisieTerrain'] = 'N';
                            $tabPgCmdPrelevs[$ip]['nbParametresTerrain'] = 0;
                            $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrain'] = 0;
                            $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainCorrect'] = 0;
                            $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainIncorrect'] = 0;
                            $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainErreur'] = 0;
                            $tabPgCmdPrelevs[$ip]['saisieAnalyse'] = 'N';
                            $tabPgCmdPrelevs[$ip]['nbParametresAnalyse'] = 0;
                            $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyse'] = 0;
                            $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseCorrect'] = 0;
                            $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseIncorrect'] = 0;
                            $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseErreur'] = 0;
                            $NbCmdMesureEnv = 0;
                            $NbCmdMesureEnvCorrect = 0;
                            $NbCmdMesureEnvIncorrect = 0;
                            $NbCmdMesureEnvErreur = 0;
                            $NbCmdAnalyseSitu = 0;
                            $NbCmdAnalyseSituCorrect = 0;
                            $NbCmdAnalyseSituIncorrect = 0;
                            $NbCmdAnalyseSituErreur = 0;
                            $NbCmdAnalyse = 0;
                            $NbCmdAnalyseCorrect = 0;
                            $NbCmdAnalyseIncorrect = 0;
                            $NbCmdAnalyseErreur = 0;
                            $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                            $pgPrelevPc = $pgCmdPrelevPcs[0];
                            if ($pgCmdPrelevPcs) {
                                $tabPgCmdPrelevs[$ip]['prelevPcs'] = $pgCmdPrelevPcs;
                            } else {
                                $tabPgCmdPrelevs[$ip]['prelevPcs'] = null;
                            }
                            $pgProgPrestaTypfic = $repoPgProgPrestaTypfic->getPgProgPrestaTypficByCodeMilieuPrestataireFormatFic($pgProgTypeMilieu, $pgCmdPrelev->getprestaPrel(), 'Saisie');
                            if ($pgProgPrestaTypfic) {
                                $nbParametresTerrain = 0;
                                $nbParametresAnalyse = 0;
                                foreach ($pgCmdPrelev->getPprog() as $pgProgLotPeriodeProg1) {
                                    if ($station->getOuvFoncId() == $pgProgLotPeriodeProg1->getStationAn()->getStation()->getOuvFoncId()) {
                                        $pgProgLotGrparAn = $pgProgLotPeriodeProg1->getGrparAn();
                                        if ($pgProgLotGrparAn->getvalide() == 'O') {
                                            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);

                                            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                                if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV') {
                                                        $nbParametresTerrain++;
                                                        $pgCmdMesureEnvs = $repoPgCmdMesureEnv->getPgCmdMesureEnvsByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                                        foreach ($pgCmdMesureEnvs as $pgCmdMesureEnv) {
                                                            $NbCmdMesureEnv++;
                                                            if ($pgCmdMesureEnv->getCodeStatut() == '0') {
                                                                $NbCmdMesureEnvCorrect++;
                                                            }
                                                            if ($pgCmdMesureEnv->getCodeStatut() == '1') {
                                                                $NbCmdMesureEnvIncorrect++;
                                                            }
                                                            if ($pgCmdMesureEnv->getCodeStatut() == '2') {
                                                                $NbCmdMesureEnvErreur++;
                                                            }
                                                        }
                                                    }
                                                    if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT') {
                                                        if ($pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1301' or
                                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1302' or
                                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1303' or
                                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1311' or
                                                                $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1312') {
                                                            if (count($pgCmdPrelevPcs) > 0) {
                                                                foreach ($pgCmdPrelevPcs as $pgCmdPrelevPca) {
                                                                    if ($pgCmdPrelevPca->getZoneVerticale()) {
                                                                        if ($pgCmdPrelevPca->getZoneVerticale()->getCodeZone() == '6') {
                                                                            $nbParametresTerrain++;
                                                                        } else {
                                                                            $nbParametresTerrain++;
                                                                        }
                                                                    }
                                                                    $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                                                    foreach ($pgCmdAnalyses as $pgCmdAnalyse) {
                                                                        $NbCmdAnalyseSitu++;
                                                                        if ($pgCmdAnalyse->getCodeStatut() == '0') {
                                                                            $NbCmdAnalyseSituCorrect++;
                                                                        }
                                                                        if ($pgCmdAnalyse->getCodeStatut() == '1') {
                                                                            $NbCmdAnalyseSituIncorrect++;
                                                                        }
                                                                        if ($pgCmdAnalyse->getCodeStatut() == '2') {
                                                                            $NbCmdAnalyseSituErreur++;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        } else {
                                                            $nbParametresTerrain++;
                                                        }
                                                    }
                                                    if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                                                        if ($pgPrelevPc) {
                                                            $nbgProgGrparRefZoneVert = $repoPgProgGrparRefZoneVert->getNbPgProgGrparRefZoneVertByGrparRefPgSandreZoneVerticaleProspectee($pgProgLotGrparAn->getGrparRef(), $pgPrelevPc->getZoneVerticale());
                                                        } else {
                                                            $nbgProgGrparRefZoneVert = 0;
                                                        }
                                                        if ($nbgProgGrparRefZoneVert > 0) {
                                                            $nbParametresAnalyse++;
                                                        }
                                                        $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                                        foreach ($pgCmdAnalyses as $pgCmdAnalyse) {
                                                            $NbCmdAnalyse++;
                                                            if ($pgCmdAnalyse->getCodeStatut() == '0') {
                                                                $NbCmdAnalyseCorrect++;
                                                            }
                                                            if ($pgCmdAnalyse->getCodeStatut() == '1') {
                                                                $NbCmdAnalyseIncorrect++;
                                                            }
                                                            if ($pgCmdAnalyse->getCodeStatut() == '2') {
                                                                $NbCmdAnalyseErreur++;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        if ($tabPgCmdPrelevs[$ip]['type'] != '6') {
                                            $tabPgCmdPrelevs[$ip]['nbParametresTerrain'] = 0;
                                        } else {
                                            $tabPgCmdPrelevs[$ip]['nbParametresTerrain'] = $nbParametresTerrain;
                                        }
                                        $tabPgCmdPrelevs[$ip]['nbParametresTerrain'] = $nbParametresTerrain;
                                        $tabPgCmdPrelevs[$ip]['nbParametresAnalyse'] = $nbParametresAnalyse;
//                                        $NbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
//                                        $NbCmdMesureEnvCorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '0');
//                                        $NbCmdMesureEnvIncorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '1');
//                                        $NbCmdMesureEnvErreur = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '2');
//                                        $NbCmdAnalyseSitu = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
//                                        $NbCmdAnalyseSituCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '0');
//                                        $NbCmdAnalyseSituIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '1');
//                                        $NbCmdAnalyseSituErreur = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '2');
//                                        $NbCmdAnalyse = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
//                                        $NbCmdAnalyseCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '0');
//                                        $NbCmdAnalyseIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '1');
//                                        $NbCmdAnalyseErreur = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '2');
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrain'] = $NbCmdMesureEnv + $NbCmdAnalyseSitu;
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainCorrect'] = $NbCmdMesureEnvCorrect + $NbCmdAnalyseSituCorrect;
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainIncorrect'] = $NbCmdMesureEnvIncorrect + $NbCmdAnalyseSituIncorrect;
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresTerrainErreur'] = $NbCmdMesureEnvErreur + $NbCmdAnalyseSituErreur;
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyse'] = $NbCmdAnalyse;
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseCorrect'] = $NbCmdAnalyseCorrect;
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseIncorrect'] = $NbCmdAnalyseIncorrect;
                                        $tabPgCmdPrelevs[$ip]['nbSaisisParametresAnalyseErreur'] = $NbCmdAnalyseErreur;
                                        if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() <= 'M40') {
                                            if ($userPrestataire) {
//                                                if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() == $userPrestataire->getAdrCorId()) {
                                                if ($pgCmdDemande->getPrestataire()->getAdrCorId() == $userPrestataire->getAdrCorId()) {
                                                    if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_T') {
                                                        $tabPgCmdPrelevs[$ip]['saisieTerrain'] = 'O';
                                                    }
                                                    if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_A') {
                                                        $tabPgCmdPrelevs[$ip]['saisieAnalyse'] = 'O';
                                                    }
                                                }
                                            }
                                            if ($user->hasRole('ROLE_ADMINSQE') and $pgCmdPrelev->getPhaseDmd()->getcodePhase() < 'M50') {
                                                $tabPgCmdPrelevs[$ip]['saisieTerrain'] = 'O';
                                                $tabPgCmdPrelevs[$ip]['saisieAnalyse'] = 'O';
                                            }
                                        }
                                        if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M30') {
                                            $tabPgCmdPrelevs[$ip]['valider'] = 'O';
                                            $tabStations[$is]['valider'] = 'O';
                                        } else {
                                            $tabPgCmdPrelevs[$ip]['valider'] = 'N';
                                            $tabStations[$is]['valider'] = 'N';
                                        }
                                        if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() == 'M40') {
                                            $tabPgCmdPrelevs[$ip]['devalider'] = 'O';
                                            $tabStations[$is]['devalider'] = 'O';
                                            $tabPgCmdPrelevs[$ip]['saisieTerrain'] = 'N';
                                            $tabPgCmdPrelevs[$ip]['saisieAnalyse'] = 'N';
                                            $tabStations[$is]['saisieTerrain'] = 'N';
                                        } else {
                                            $tabPgCmdPrelevs[$ip]['devalider'] = 'N';
                                            $tabStations[$is]['devalider'] = 'N';
                                        }
                                    }
                                }
                            }
                            $ip++;
                        }
                    }
                    $tabPgCmdDemandes[$id]['pgCmdPrelevs'] = $tabPgCmdPrelevs;
                    $id++;
                }
                sort($tabPgCmdDemandes);
                $tabStations[$is]['pgCmdDemandes'] = $tabPgCmdDemandes;
                $is++;
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

    public function lotPeriodeLacSaisirEnvSituAction($prelevId = null, $periodeAnId = null, $stationId = null, $maj = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdDemande = $pgCmdPrelev->getDemande();
        $pgCmdPrelevPcAll = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelevOrderByProfondeur($pgCmdPrelev);
        $pgCmdPrelevPc = $pgCmdPrelevPcAll[0];

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $pgCmdPrelev->getPprog();
        $pgProgPeriodes = $pgProgLotPeriodeAn->getPeriode();

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }

        if ($pgProgLot->getDelaiPrel()) {
            $dateFin = clone($pgProgPeriodes->getDateDeb());
            $delai = $pgProgLot->getDelaiPrel();
            $dateFin->add(new \DateInterval('P' . $delai . 'D'));
        } else {
            $dateFin = $pgProgPeriodes->getDateFin();
        }

        $tabAutreDemandes = array();
        $nbDemande = 0;
        $autresDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgPeriodes);
        foreach ($autresDemandes as $autreDemande) {
            $tabAutreDemandes[$nbDemande]['demande'] = $autreDemande;
            $tabAutrePrelevs = array();
            $nbPrelev = 0;
            $autrePrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemandeStationPeriode($autreDemande, $pgRefStationMesure, $pgProgPeriodes);
            foreach ($autrePrelevs as $autrePrelev) {
                if ($autrePrelev->getId() != $prelevId) {
                    $tabAutrePrelevs[$nbPrelev]['prelev'] = $autrePrelev;
                    $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($autrePrelev);
                    $tabAutrePrelevs[$nbPrelev]['prelevPc'] = $pgCmdPrelevPcs[0];
                    $nbPrelev++;
                }
            }
            $tabAutreDemandes[$nbDemande]['prelevs'] = $tabAutrePrelevs;
//                $nbDemande++;
        }

        $tabGroupes = array();
        $nbGroupes = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {



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
//                sort($tabParamAns);
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
                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    if ($pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1301' or
                            $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1302' or
                            $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1303' or
                            $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1311' or
                            $pgProgLotParamAn->getCodeParametre()->getCodeParametre() == '1312') {
                        if ($pgCmdPrelevPcAll) {
                            foreach ($pgCmdPrelevPcAll as $pgCmdPrelevPc) {
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
                // sort($tabParamAns);
                $tabGroupes[$nbGroupes]['paramAns'] = $tabParamAns;
                $nbGroupes++;
// }
            }
        }

//\Symfony\Component\VarDumper\VarDumper::dump($pgCmdPrelevPcAll);
//return new Response ('');

        if ($maj == 'C') {
            return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeLacVoirEnvSitu.html.twig', array(
                        'user' => $pgProgWebUser,
                        'typeMilieu' => $pgProgTypeMilieu,
                        'lotan' => $pgProgLotAn,
                        'station' => $pgRefStationMesure,
                        'periodeAn' => $pgProgLotPeriodeAn,
                        'dateFin' => $dateFin,
                        'demande' => $pgCmdPrelev->getDemande(),
                        'cmdPrelev' => $pgCmdPrelev,
                        'cmdPrelevPcs' => $pgCmdPrelevPcAll,
                        'autreDemandes' => $tabAutreDemandes,
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
                        'cmdPrelevPcs' => $pgCmdPrelevPcAll,
                        'autreDemandes' => $tabAutreDemandes,
                        'groupes' => $tabGroupes,
                        'maj' => $maj));
        }
    }

    public function lotPeriodeLacResultatEnvSituAction($prelevId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgProgUnitesPossiblesParam = $emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();



        $now = date('Ymd');
        $today = new \DateTime($now);

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
        $pgPrelevPc = $pgPrelevPcs[0];
        $pgCmdDemande = $pgCmdPrelev->getDemande();

        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $prestaprel = null;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            if (substr_count($pgProgLotGrparAn->getGrparRef()->getLibelleGrp(), 'situ') > 0 or substr_count($pgProgLotGrparAn->getGrparRef()->getLibelleGrp(), 'environ') > 0) {
                $prestaprel = $pgProgLotGrparAn->getPrestaDft();
            }
        }
        if (!$prestaprel) {
            $prestaprel = $pgCmdDemande->getPrestataire();
        }

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }

        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $pgCmdPrelev->getPprog();
        $pgProgPeriodes = $pgProgLotPeriodeAn->getPeriode();

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

        if (isset($_POST['profMax'])) {
            $profMax = $_POST['profMax'];
        }

        $pgCmdPrelev->setDatePrelev($datePrel);
        $pgCmdPrelev->setProfMax($profMax);
        $emSqe->persist($pgCmdPrelev);



        $autresDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgPeriodes);
        foreach ($autresDemandes as $autreDemande) {
            $autrePrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemandeStationPeriode($autreDemande, $pgRefStationMesure, $pgProgPeriodes);
            foreach ($autrePrelevs as $autrePrelev) {
                if ($autrePrelev->getid() != $pgCmdPrelev->getId()) {
                    $autrePrelev->setDatePrelev($datePrel);
                    $emSqe->persist($autrePrelev);
                    $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($autrePrelev);
                    foreach ($pgCmdPrelevPcs as $autrePrelevPc) {
                        if (isset($_POST['nonRealise_' . $autrePrelevPc->getZoneVerticale()->getcodeZone() . '_' . $autrePrelev->getCodeSupport()->getCodeSupport()])) {
                            $autrePrelev = $autrePrelevPc->getPrelev();
                            $autrePrelev->setRealise('N');
                            $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('M60');
                            $autrePrelev->setPhaseDmd($pgProgPhase);
                            $emSqe->persist($autrePrelev);
                        } else {
                            $autrePrelev = $autrePrelevPc->getPrelev();
                            $autrePrelev->setRealise('O');
                            $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('M40');
                            $autrePrelev->setPhaseDmd($pgProgPhase);
                            $emSqe->persist($autrePrelev);
                        }
                        if (isset($_POST['profAna_' . $autrePrelevPc->getZoneVerticale()->getcodeZone() . '_' . $autrePrelev->getCodeSupport()->getCodeSupport()])) {
                            $profAna = $_POST['profAna_' . $autrePrelevPc->getZoneVerticale()->getcodeZone() . '_' . $autrePrelev->getCodeSupport()->getCodeSupport()];
                            if (strlen($profAna) > 0) {
                                $autrePrelevPc->setProfondeur($profAna);
                                $emSqe->persist($autrePrelevPc);
                            }
                        }
                    }
                }
            }
        }


        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            //$prestataire = $pgProgLotGrparAn->getPrestaDft();
            $pgProgPeriodes = $pgProgLotPeriodeProg->getPeriodAn()->getPeriode();
            if ($pgProgLotGrparAn->getvalide() == 'O') {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();

// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);

                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
//                    if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire)) {
                    if ($pgCmdPrelev->getPrestaPrel()->getAdrcorId() == $pgProgLotParamAn->getPrestataire()->getAdrcorId()) {

                        if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {
                            $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                            foreach ($pgCmdPrelevPcs as $pgCmdPrelevPc) {
                                if ($pgCmdPrelevPc->getZoneVerticale()->getCodeZone() == '6') {
                                    $trouve = true;
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

                            $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestaprel, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
                            $trouve = false;
                            $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                            foreach ($pgCmdPrelevPcs as $pgCmdPrelevPc) {
                                if ($pgCmdPrelevPc->getZoneVerticale()->getCodeZone() == '1') {
                                    $trouve = true;
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


                            if (strlen($valeur) > 0 and $valeur > 0) {
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
//                        if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire)) {
                        if ($pgCmdPrelev->getPrestaPrel()->getAdrcorId() == $pgProgLotParamAn->getPrestataire()->getAdrcorId()) {

                            if ($pgProgGrpParamRef->getTypeGrp() == 'ENV') {
//                                if ($prestataire == $pgProgLotParamAn->getPrestataire()) {
                                //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                $nbParametresEnvSit++;
//                                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
//                                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                if ($pgCmdMesureEnv) {
                                    $nbSaisieParametreEnv++;
                                }
//                                    }
//                                }
                            }

                            if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {
//                                if ($prestataire == $pgProgLotParamAn->getPrestataire()) {
                                //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                $nbParametresEnvSit++;
//                                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
//                                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                if (count($pgCmdAnalyses) > 0) {
                                    $nbSaisieParametreSit++;
                                }
//                                    }
//                                }
                            }



                            if ($pgProgGrpParamRef->getTypeGrp() == 'ANA') {
//                                if ($prestataire == $pgProgLotParamAn->getPrestataire()) {
                                //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                $nbParametresAna++;
//                                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
//                                    $trouve = false;
//                                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                //echo ('groupe: ' . $pgProgLotGrparAn . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId()  . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                if (count($pgCmdAnalyses) > 0) {
                                    $nbSaisieParametresAna++;
                                }
//                                    }
//                                }
                            }
                        }
                    }
                }
            }


            echo ('$pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' </br>');
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

//            return new Response('');

            $pgCmdPrelev->setDatePrelev($datePrel);
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
            $emSqe->persist($pgCmdPrelev);
        }

//
//        foreach ($pgCmdPrelevs as $pgCmdPrelev) {
//            $pgCmdPrelev->setDatePrelev($datePrel);
//            $emSqe->persist($pgCmdPrelev);
//        }
        $emSqe->flush();




//  return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//return new Response ('');
        if ($nbErreurs == 0) {
            if ($okPhase) {
                return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_lac_saisir_env_situ', array(
                                    'prelevId' => $pgCmdPrelev->getId(),
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
                                'prelevId' => $pgCmdPrelev->getId(),
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
        $session->set('menu', 'donnees');
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
        $pgProgLotPeriodeProgs = $pgCmdPrelev->getPprog();
        $nbErreurs = 0;

        $pgSandreZoneVerticaleProspectee = $repoPgSandreZoneVerticaleProspectee->getPgSandreZoneVerticaleProspecteeByCodeZone(0);
        $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
        $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);

        $profMax = str_replace(',', '.', $profMax);

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

        if ($ecartRet == 1) {
            if ($profFin > 0.5) {
                $prof = 0.5;
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
            }
            if ($profFin > 1.5) {
                $prof = 1.5;
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
            }
            if ($profFin > 2.5) {
                $prof = 2.5;
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
            }
        }

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
                            'prelevId' => $pgCmdPrelev->getId(),
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
        $session->set('menu', 'donnees');
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
        $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
        $pgPrelevPc = $pgCmdPrelevPcs[0];

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $pgCmdPrelev->getPprog();

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
                        if ($pgCmdPrelev->getPrestaPrel() == $pgProgLotParamAn->getPrestataire()) {
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
        $session->set('menu', 'donnees');
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
        $repoPgProgGrparRefZoneVert = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefZoneVert');

        $now = date('Ymd');
        $today = new \DateTime($now);

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
        $pgPrelevPc = $pgPrelevPcs[0];
        $pgCmdDemande = $pgCmdPrelev->getDemande();
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $pgCmdPrelev->getPprog();
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
//            if ($user->hasRole('ROLE_ADMINSQE') or ( $userPrestataire == $prestataire)) {
            if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);

                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    if ($pgCmdPrelev->getPrestaPrel()->getAdrcorId() == $pgProgLotParamAn->getPrestataire()->getAdrcorId()) {
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
                        $inSitu = 2;

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
                            if ($remarque == '0') {
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
                }

                $emSqe->flush();



// }
//                } else {
//                    $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
//                    foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
//                        if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire())) {
//                            $nbParametresEnvSit++;
//                        }
//                    }
//                }
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
//                        if ($user->hasRole('ROLE_ADMINSQE') or ( $prestataire == $pgProgLotParamAn->getPrestataire() and $userPrestataire == $prestataire)) {
                        if ($pgCmdPrelev->getPrestaPrel()->getAdrcorId() == $pgProgLotParamAn->getPrestataire()->getAdrcorId()) {

                            if ($pgProgGrpParamRef->getTypeGrp() == 'ENV') {
//                                if ($prestataire == $pgProgLotParamAn->getPrestataire()) {
                                //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                $nbParametresEnvSit++;
//                                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
//                                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                if ($pgCmdMesureEnv) {
                                    $nbSaisieParametreEnv++;
                                }
//                                    }
//                                }
                            }

                            if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {
//                                if ($prestataire == $pgProgLotParamAn->getPrestataire()) {
                                //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                $nbParametresEnvSit++;
//                                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
//                                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                if (count($pgCmdAnalyses) > 0) {
                                    $nbSaisieParametreSit++;
                                }
//                                    }
//                                }
                            }

                            if ($pgProgGrpParamRef->getTypeGrp() == 'ANA') {
//                                if ($prestataire == $pgProgLotParamAn->getPrestataire()) {
                                //echo ('groupe: ' . $pgProgLotGrparAn->getId() . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId() . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                //$nbParametresAna++;
//                                    $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($prestataire, $pgCmdDemande, $pgRefStationMesure, $pgProgPeriodes);
//                                    $trouve = false;
//                                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                                //echo ('groupe: ' . $pgProgLotGrparAn . ' type : ' . $pgProgGrpParamRef->getTypeGrp() . '  pgCmdPrelev : ' . $pgCmdPrelev->getId()  . ' parametre : ' . $pgProgLotParamAn->getId() . ' </br>');
                                $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                                if (count($pgCmdAnalyses) > 0) {
                                    $nbSaisieParametresAna++;
                                }
                                if ($pgPrelevPc) {
                                    $nbgProgGrparRefZoneVert = $repoPgProgGrparRefZoneVert->getNbPgProgGrparRefZoneVertByGrparRefPgSandreZoneVerticaleProspectee($pgProgLotGrparAn->getGrparRef(), $pgPrelevPc->getZoneVerticale());
                                } else {
                                    $nbgProgGrparRefZoneVert = 0;
                                }
                                if ($nbgProgGrparRefZoneVert > 0) {
                                    $nbParametresAna++;
                                }
//                                    }
//                                }
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
            echo ('$nbParametresEnvSit : ' . $nbParametresEnvSit . ' </br>');
            echo ('$nbParametresAna : ' . $nbParametresAna . ' </br>');
            $nbParametresTotal = $nbParametresEnvSit + $nbParametresAna;
            echo ('$nbParametresTotal : ' . $nbParametresTotal . ' </br>');
            $nbSaisieParametresTotal = $nbSaisieParametresEnvSit + $nbSaisieParametresAna;
            echo ('$nbSaisieParametresTotal : ' . $nbSaisieParametresTotal . ' </br>');

//            return new Response('');

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

    public function lotPeriodeLacValiderAction($prelevId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
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


        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $pgCmdPrelev->getPprog();

        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            $prestataire = $pgProgLotGrparAn->getPrestaDft();
            $pgProgPeriodes = $pgProgLotPeriodeProg->getPeriodAn()->getPeriode();

            $tabDonneesBrutes = array();
            $i = 0;
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

    public function lotPeriodeLacDevaliderAction($prelevId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdDemande = $pgCmdPrelev->getDemande();

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = null;
        }


        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $pgCmdPrelev->getPprog();

        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            $prestataire = $pgProgLotGrparAn->getPrestaDft();
            $pgProgPeriodes = $pgProgLotPeriodeProg->getPeriodAn()->getPeriode();
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
        $session->set('menu', 'donnees');
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
        $session->set('menu', 'donnees');
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

    protected function _controleVraisemblance_fichier($parametre, $valeur, $remarque, $unite, $lqM, $inSitu, $marche, $fraction, $tabStatut) {

        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
            $result = $controleVraisemblance->testsComplementaires($mesure, $codeRq, $inSitu, $lqM, $marche);
        }
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
        //echo ('III.7 : ' . $okControles . ' </br>');
        // III.8  et III.9
        $okControles += $this->_balanceIonique($pgCmdPrelev);
        //echo ('IIII.8 : ' . $okControles . ' </br>');
// III.10
        $okControles += $this->_ortophosphate($pgCmdPrelev);
        //echo ('III.10 : ' . $okControles . ' </br>');
// III.11
        $okControles += $this->_ammonium($pgCmdPrelev);
        //echo ('III.11 : ' . $okControles . ' </br>');
// III.12
        $okControles += $this->_pourcentageHorsOxygene($pgCmdPrelev);
        //echo ('III.12 : ' . $okControles . ' </br>');
        // III.13
        $okControles += $this->_sommeParametresDistincts($pgCmdPrelev);
        //echo ('III.13 : ' . $okControles . ' </br>');
        // III.14
        $okControles += $this->_controleVraisemblanceMacroPolluants($pgCmdPrelev);
        //echo ('III.14 : ' . $okControles . ' </br>');

        return $okControles;
    }

// III.7 modèle  de WEISS : cohérence Teau, % O2, Concentration O2  sauf si Conductivité > 10 000
    protected function _modeleWeiss($pgCmdPrelev) {

        $session = $this->get('session');
        $session->set('menu', 'donnees');
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
        $session->set('menu', 'donnees');
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
        $session->set('menu', 'donnees');
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
        $session->set('menu', 'donnees');
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
        $session->set('menu', 'donnees');
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
        $session->set('menu', 'donnees');
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
        $session->set('menu', 'donnees');
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
