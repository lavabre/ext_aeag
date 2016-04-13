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
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotanId);
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


        return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodes.html.twig', array(
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

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        }

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
            $userPrestataire = $pgProgLot->getTitulaire();
        }


        $tabStations = array();
        $i = 0;
        $j = 0;
        $k = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
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
                $tabStations[$i]['stationAn'] = $pgProgLotStationAn;
                $tabStations[$i]['station'] = $pgProgLotStationAn->getStation();
                $tabStations[$i]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $pgProgLotPeriodeProg->getStationAn()->getStation()->getCode()) . '.pdf';
                $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeByLotanPrestatairePeriode($pgProgLotAn, $userPrestataire, $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                if ($pgCmdDemande) {
                    $tabStations[$i]['cmdDemande'] = $pgCmdDemande;
                }
                $tabStations[$i]['cmdPrelev'] = null;

                $i++;
            }
        }
        for ($i = 0; $i < count($tabStations); $i++) {
            $pgProgLotStationAn = $tabStations[$i]['stationAn'];
            $station = $tabStations[$i]['station'];
            $tabSuiviPrels = array();
            $j = 0;
            if ($pgCmdDemande) {
                $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($pgCmdDemande->getPrestataire(), $pgCmdDemande, $station, $pgProgLotPeriodeAn->getPeriode());
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
                                                if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() == $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    $NbProgLotParamAn++;
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
                                                if ($pgCmdPrelev->getprestaPrel()->getAdrCorId() != $pgProgLotParamAn->getPrestataire()->getAdrCorId()) {
                                                    $NbProgLotParamAn++;
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
                            if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() != 'M40') {
                                if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_T') {
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
                            if ($pgCmdPrelev->getPhaseDmd()->getcodePhase() != 'M40') {
                                if (strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_TA' or strtoupper($pgProgPrestaTypfic->getFormatFic()) == 'SAISIE_A') {
                                    $tabStations[$i]['cmdPrelev'][$j]['saisieAnalyse'] = 'O';
                                }
                            }
                        }
                    }
                    $j++;
                }
            }
            $tabStations[$i]['suiviPrels'] = $tabSuiviPrels;
        }

//        \Symfony\Component\VarDumper\VarDumper::dump($tabDates);
//        return new Response ('');   

        return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeStations.html.twig', array(
                    'user' => $pgProgWebUser,
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
            $userPrestataire = $pgProgLot->getTitulaire();
        }

        $tabGroupes = array();
        $nbGroupes = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
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
                    }
                }
                $tabGroupes[$nbGroupes]['paramAns'] = $tabParamAns;
                $nbGroupes++;
// }
            }
        }

//\Symfony\Component\VarDumper\VarDumper::dump($tabParamAns);
//return new Response ('');

        return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeStationSaisirEnvSitu.html.twig', array(
                    'user' => $pgProgWebUser,
                    'typeMilieu' => $pgProgTypeMilieu,
                    'lotan' => $pgProgLotAn,
                    'station' => $pgRefStationMesure,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'demande' => $pgCmdPrelev->getDemande(),
                    'cmdPrelev' => $pgCmdPrelev,
                    'groupes' => $tabGroupes,
                    'maj' => $maj));
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

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = $pgProgLot->getTitulaire();
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

        if (isset($_POST['profMax'])) {
            $profMax = $_POST['profMax'];
        } else {
            $profMax = null;
        }

        if (isset($_POST['ecart'])) {
            $ecart = $_POST['ecart'];
        } else {
            $ecart = null;
        }

        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            if ($pgProgLotGrparAn->getvalide() == 'O' and ( $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV' or $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT')) {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);


                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    if ($user->hasRole('ROLE_ADMINSQE') or ( $userPrestataire == $pgProgLotParamAn->getPrestataire())) {

                        if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {

                            $tabSaisies = array();
                            $i = 0;

                            if ($pgProgLotParamAn->getCodeParametre()->getCodeparametre() == '1301' or
                                    $pgProgLotParamAn->getCodeParametre()->getCodeparametre() == '1302' or
                                    $pgProgLotParamAn->getCodeParametre()->getCodeparametre() == '1303' or
                                    $pgProgLotParamAn->getCodeParametre()->getCodeparametre() == '1311' or
                                    $pgProgLotParamAn->getCodeParametre()->getCodeparametre() == '1312') {
                                $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);

                                foreach ($pgCmdPrelevPcs as $pgCmdPrelevPc) {
                                    if (isset($_POST['valeur' . $pgProgLotParamAn->getId() . '-' . $pgCmdPrelevPc->getNumOrdre()])) {
                                        $valeur = $_POST['valeur' . $pgProgLotParamAn->getId() . '-' . $pgCmdPrelevPc->getNumOrdre()];
                                    } else {
                                        $valeur = null;
                                    }
                                    if (isset($_POST['uniteCode' . $pgProgLotParamAn->getId() . '-' . $pgCmdPrelevPc->getNumOrdre()])) {
                                        $unite = $_POST['uniteCode' . $pgProgLotParamAn->getId() . '-' . $pgCmdPrelevPc->getNumOrdre()];
                                    } else {
                                        $unite = null;
                                    }
                                    if (isset($_POST['remarque' . $pgProgLotParamAn->getId() . '-' . $pgCmdPrelevPc->getNumOrdre()])) {
                                        $remarque = $_POST['remarque' . $pgProgLotParamAn->getId() . '-' . $pgCmdPrelevPc->getNumOrdre()];
                                    } else {
                                        $remarque = null;
                                    }

                                    if (isset($_POST['numOrdre-' . $pgProgLotParamAn->getId() . '-' . $pgCmdPrelevPc->getNumOrdre()])) {
                                        $numOrdre = $_POST['numOrdre-' . $pgProgLotParamAn->getId() . '-' . $pgCmdPrelevPc->getNumOrdre()];
                                    } else {
                                        $numOrdre = null;
                                    }

                                    $tabSaisies[$i]['valeur'] = $valeur;
                                    $tabSaisies[$i]['unite'] = $unite;
                                    $tabSaisies[$i]['remarque'] = $remarque;
                                    $tabSaisies[$i]['numOrdre'] = $numOrdre;
                                    $i++;
                                }
                            } else {
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

                                $tabSaisies[$i]['valeur'] = $valeur;
                                $tabSaisies[$i]['unite'] = $unite;
                                $tabSaisies[$i]['remarque'] = $remarque;
                                $tabSaisies[$i]['numOrdre'] = 1;
                                $i++;
                            }


                            for ($i = 0; $i < count($tabSaisies); $i++) {

                                $valeur = $tabSaisies[$i]['valeur'];
                                $unite = $tabSaisies[$i]['unite'];
                                $remarque = $tabSaisies[$i]['remarque'];
                                $AnalyseNumOrdre = $tabSaisies[$i]['numOrdre'];


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
                                    $pgCmdAnalyse = new PgCmdAnalyse();
                                    $pgCmdAnalyse->setLieuAna('1');
                                    $pgCmdAnalyse->setPrelevId($pgCmdPrelev->getId());
                                    $pgCmdAnalyse->setNumOrdre($AnalyseNumOrdre);
                                    $pgCmdAnalyse->setParamProg($pgProgLotParamAn);
                                    $pgCmdAnalyse->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                    $pgCmdAnalyse->setCodeFraction($pgSandreFraction);
                                    $pgCmdAnalyse->setDateAna($today);
                                    $pgCmdAnalyse->setResultat($valeur);
                                    $pgCmdAnalyse->setCodeRemarque($remarque);
                                    $pgCmdAnalyse->setCodeStatut($tabStatut['statut']);
                                    $pgCmdAnalyse->setLibelleStatut($tabStatut['libelle']);
                                    if ($unite) {
                                        $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($unite);
                                        if ($pgSandreUnites) {
                                            $pgCmdAnalyse->setCodeUnite($pgSandreUnites);
                                        } else {
                                            $pgCmdAnalyse->setCodeUnite(null);
                                        }
                                    } else {
                                        $pgCmdAnalyse->setCodeUnite(null);
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
                                        $pgCmdAnalyse = new PgCmdAnalyse();
                                        $pgCmdAnalyse->setNumOrdre($AnalyseNumOrdre);
                                        $pgCmdAnalyse->setLieuAna('1');
                                        $pgCmdAnalyse->setPrelevId($pgCmdPrelev->getId());
                                        $pgCmdAnalyse->setParamProg($pgProgLotParamAn);
                                        $pgCmdAnalyse->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                        $pgCmdAnalyse->setCodeFraction($pgSandreFraction);
                                        $pgCmdAnalyse->setDateAna($today);
                                        $pgCmdAnalyse->setResultat(null);
                                        $pgCmdAnalyse->setCodeRemarque($remarque);
                                        $pgCmdAnalyse->setCodeStatut(1);
                                        $pgCmdAnalyse->setLibelleStatut('Valeur absente');
                                        if ($unite) {
                                            $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($unite);
                                            if ($pgSandreUnites) {
                                                $pgCmdAnalyse->setCodeUnite($pgSandreUnites);
                                            } else {
                                                $pgCmdAnalyse->setCodeUnite(null);
                                            }
                                        } else {
                                            $pgCmdAnalyse->setCodeUnite(null);
                                        }
                                        $emSqe->persist($pgCmdAnalyse);
                                    }
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
        if ($okControlesSpecifiques != 0) {
            $nbErreurs++;
        }

        //return new Response ('$okControleVraisemblance : ' . $okControleVraisemblance . '  $okControlesSpecifiques :  ' . $okControlesSpecifiques . '   $nbErreurs : ' . $nbErreurs);
        $ok = false;
        if ($nbErreurs == 0) {
            $nbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
            $nbCmdSit = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
            $nbSaisieParametresEnvSit = $nbCmdMesureEnv + $nbCmdSit;
            $nbSaisieParametresAna = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
            $nbParametresTotal = $nbParametresEnvSit + $nbParametresAna;
            $nbSaisieParametresTotal = $nbSaisieParametresEnvSit + $nbSaisieParametresAna;
            if ($nbParametresTotal == $nbSaisieParametresTotal) {
                $ok = true;
            }
        }
        if ($ok) {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R45');
        } else {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R10');
        }
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

        if ($ok) {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');
            $pgCmdPrelev->setRealise('O');
        } else {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M20');
            $pgCmdPrelev->setRealise('N');
        }
        $pgCmdPrelev->setDatePrelev($datePrel);
        $pgCmdPrelev->setProfMax($profMax);
        $pgCmdPrelev->setPhaseDmd($pgProgPhases);
        $emSqe->persist($pgCmdPrelev);

        $emSqe->flush();




//  return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//return new Response ('');
        if ($ok == 0) {
            return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_stations', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
        } else {
            return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_station_saisir_env_situ', array(
                                'prelevId' => $pgCmdPrelev->getId(),
                                'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                'stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
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
            $userPrestataire = $pgProgLot->getTitulaire();
        }

        $tabGroupes = array();
        $nbGroupes = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
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

//return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//return new Response ('');

        return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeStationSaisirAna.html.twig', array(
                    'user' => $pgProgWebUser,
                    'lotan' => $pgProgLotAn,
                    'station' => $pgRefStationMesure,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'demande' => $pgCmdPrelev->getDemande(),
                    'cmdPrelev' => $pgCmdPrelev,
                    'groupes' => $tabGroupes,
                    'maj' => $maj));
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

        $userPrestataire = null;
        if ($pgProgWebUser->getPrestataire()) {
            $userPrestataire = $pgProgWebUser->getPrestataire();
        } else {
            $userPrestataire = $pgProgLot->getTitulaire();
        }

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

        if (isset($_POST['datePrel'])) {
            $dateSaisie = str_replace('/', '-', $_POST['datePrel']);
            $datePrel = new \DateTime($dateSaisie);
        } else {
            $datePrel = null;
        }

        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
// if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);

                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    if ($user->hasRole('ROLE_ADMINSQE') or ( $userPrestataire == $pgProgLotParamAn->getPrestataire())) {
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
                                $pgCmdAnalyse->setCodeFraction($pgSandreFraction);
                            }
                            $pgCmdAnalyse->setDateAna($today);
                            $pgCmdAnalyse->setResultat($valeur);
                            $pgCmdAnalyse->setCodeRemarque($remarque);
                            $pgCmdAnalyse->setCodeStatut($tabStatut['statut']);
                            $pgCmdAnalyse->setlibelleStatut($tabStatut['libelle']);
                            if ($pgSandreUnites) {
                                $pgCmdAnalyse->setCodeUnite($pgSandreUnites);
                            } else {
                                $pgCmdAnalyse->setCodeUnite(null);
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
                    if ($user->hasRole('ROLE_ADMINSQE') or ( $userPrestataire == $pgProgLotParamAn->getPrestataire())) {
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

        // return new Response ('$okControleVraisemblance : ' . $okControleVraisemblance . '  $okControlesSpecifiques :  ' . $okControlesSpecifiques . '   $nbErreurs : ' . $nbErreurs);

        $ok = false;
        if ($nbErreurs == 0) {
            $nbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
            $nbCmdSit = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
            $nbSaisieParametresEnvSit = $nbCmdMesureEnv + $nbCmdSit;
            $nbSaisieParametresAna = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
            $nbParametresTotal = $nbParametresEnvSit + $nbParametresAna;
            $nbSaisieParametresTotal = $nbSaisieParametresEnvSit + $nbSaisieParametresAna;
            if ($nbParametresTotal == $nbSaisieParametresTotal) {
                $ok = true;
            }
        }

        //return new Response('$nbParametresTotal  : ' . $nbParametresTotal . ' $nbParametresEnvSit : ' . $nbParametresEnvSit . '  $nbParametresAna : ' . $nbParametresAna . '  $nbSaisieParametresTotal :  ' . $nbSaisieParametresTotal);

        if ($ok) {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R45');
        } else {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R10');
        }

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

        if ($ok) {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');
            $pgCmdPrelev->setRealise('O');
        } else {
            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M20');
            $pgCmdPrelev->setRealise('N');
        }
        $pgCmdPrelev->setDatePrelev($datePrel);
        $pgCmdPrelev->setPhaseDmd($pgProgPhases);
        $emSqe->persist($pgCmdPrelev);

        $emSqe->flush();

//  return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
//return new Response ('');

        if ($nbErreurs == 0) {
            return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_stations', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
        } else {
            return $this->redirect($this->generateUrl('AeagSqeBundle_saisieDonnees_lot_periode_station_saisir_ana', array(
                                'prelevId' => $pgCmdPrelev->getId(),
                                'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                'stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
            )));
        }
    }

    public function lotPeriodeStationGenererEnvSituAction($periodeAnId = null, $prelevId = null, $groupeId = null, $profMax = null, $ecart = null, Request $request) {

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

        $tabGroupe = array();
        $tabGroupe['grparAn'] = $pgProgLotGrparAn;
        $tabGroupe['correct'] = 0;
        $tabGroupe['warning'] = 0;
        $tabGroupe['erreur'] = 0;
        $tabParamAns = array();
        $nbParamAns = 0;
        foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
            $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
            $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($pgProgLotParamAn->getCodeUnite());
            if ($pgSandreUnites) {
                $tabParamAns[$nbParamAns]['unite'] = $pgSandreUnites;
            }
            if ($pgProgGrpParamRef->getTypeGrp() == 'ENV') {
                $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                if ($pgCmdMesureEnv) {
                    if ($pgCmdMesureEnv->getCodeStatut() == '0') {
                        $tabGroupe['correct'] = $tabGroupe['correct'] + 1;
                    } elseif ($pgCmdMesureEnv->getCodeStatut() == '1') {
                        $tabGroupe['warning'] = $tabGroupe['warning'] + 1;
                    } elseif ($pgCmdMesureEnv->getCodeStatut() == '2') {
                        $tabGroupe['erreur'] = $tabGroupe['erreur'] + 1;
                    }
                    $tabParamAns[$nbParamAns]['pgCmdMesureEnv'] = $pgCmdMesureEnv;
                    $tabParamAns[$nbParamAns]['unite'] = $pgCmdMesureEnv->getCodeUnite();
                } else {
                    $tabParamAns[$nbParamAns]['pgCmdMesureEnv'] = null;
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
            }
            if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {
                $pgCmdAnalyses = $repoPgCmdAnalyse->getPgCmdAnalysesByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                if (count($pgCmdAnalyses) > 0) {
                    foreach ($pgCmdAnalyses as $pgCmdAnalyse) {
                        if ($pgCmdAnalyse->getCodeStatut() == '0') {
                            $tabGroupe['correct'] = $tabGroupe['correct'] + 1;
                        } elseif ($pgCmdAnalyse->getCodeStatut() == '1') {
                            $tabGroupe['warning'] = $tabGroupe['warning'] + 1;
                        } elseif ($pgCmdAnalyse->getCodeStatut() == '2') {
                            $tabGroupe['erreur'] = $tabGroupe['erreur'] + 1;
                        }
                        $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                        $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = $pgCmdAnalyse;
                        $pgCmdPrelevPc = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelevNumOrdre($pgCmdPrelev, $pgCmdAnalyse->getNumOrdre());
                        $tabParamAns[$nbParamAns]['pgCmdPrelevPcs'][0] = $pgCmdPrelevPc;
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
                    $pgCmdPrelevPcs = $repoPgCmdPrelevPc->getPgCmdPrelevPcByPrelev($pgCmdPrelev);
                    if (count($pgCmdPrelevPcs) > 0) {
                        $tabParamAns[$nbParamAns]['pgCmdPrelevPcs'] = $pgCmdPrelevPcs;
                    } else {
                        $tabParamAns[$nbParamAns]['pgCmdPrelevPcs'] = null;
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
                }
            }

            $nbParamAns++;
        }
        $tabGroupe['paramAns'] = $tabParamAns;

        //  \Symfony\Component\VarDumper\VarDumper::dump($tabGroupe);
//return new Response ('');
        return $this->render('AeagSqeBundle:SaisieDonnees:lotPeriodeStationGenererEnvSitu.html.twig', array(
                    'user' => $pgProgWebUser,
                    'typeMilieu' => $pgProgTypeMilieu,
                    'lotan' => $pgProgLotAn,
                    'cmdPrelev' => $pgCmdPrelev,
                    'groupe' => $tabGroupe,
        ));
    }

    protected function getCheminEchange($pgCmdSuiviPrel) {
        $chemin = $this->container->getParameter('repertoire_echange');
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

// Contrôles sur toutes les valeurs insérées
        $mesure = $valeur;
        $codeRq = $remarque;
        $codeParametre = $parametre;


// III.1 Champs non renseignés (valeurs et code remarque) ou valeurs non numériques ou valeurs impossibles params env / code remarque peut ne pas être renseigné pour cette liste (car réponse en edilabo 1.0) => avertissement
        if (is_null($mesure)) {
            if ($codeRq != 0 || is_null($codeRq)) { // III.2 Si valeur "vide" avec code remarque "0" hors lecture échelle (1429),,,,,,,'ABSENT' / on doit avoir un code remarque = 0 pour les valeurs vides, sinon avertissement, sauf pour le 1429 (cote échelle) => Avertissement
                if ($codeParametre == 1429 || $inSitu == 1) {
                    $tabStatut['statut'] = 1;
                    $tabStatut['libelle'] = 'Valeur non renseignée et code remarque différent de 0';
                } else {
                    $tabStatut['ko'] = $tabStatut['ko'] + 1;
                    $tabStatut['statut'] = 2;
                    $tabStatut['libelle'] = 'Valeur non renseignée et code remarque différent de 0';
                }
            }
        }

        if (is_null($codeRq) && !is_null($mesure)) {
            if ($codeParametre == 1429 || $inSitu == 1) {
                $tabStatut['statut'] = 1;
                $tabStatut['libelle'] = 'Valeur renseignée et code remarque vide';
            } else {
                $tabStatut['ko'] = $tabStatut['ko'] + 1;
                $tabStatut['statut'] = 2;
                $tabStatut['libelle'] = 'Valeur renseignée et code remarque vide';
            }
        }

        if (!is_null($codeRq) && !is_null($mesure)) {
            if (!is_numeric($mesure) || !is_numeric($codeRq)) {
                $tabStatut['ko'] = $tabStatut['ko'] + 1;
                $tabStatut['statut'] = 2;
                $tabStatut['libelle'] = 'La valeur n\'est pas un nombre';
            }
        }

// Codes Params Env
// TODO Utiliser champ in_situ == 0
        $codeParamsEnv = array(
            1015, 1018, 1408, 1409, 1410, 1411, 1412, 1413, 1415, 1416,
            1420, 1422, 1423, 1424, 1425, 1427, 1428, 1429, 1434, 1726,
            1799, 1841, 1947, 1948, 5915, 6565, 6566, 6567, 7036
        );

// III.3 Valeurs =0 (hors TH (1345), TA (1346), TAC (1347), Temp(1301)) hors codes observations environnementales / résultat = 0 possible pour les paramètres de cette liste (et pour 1345, 1346, 1347 et 1301) => Erreur
        if ($mesure == 0) {
            if ($codeParametre !== 1345 && $codeParametre !== 1346 && $codeParametre !== 1347 && $codeParametre !== 1301 && $inSitu != 1) {
                $tabStatut['ko'] = $tabStatut['ko'] + 1;
                $tabStatut['statut'] = 2;
                $tabStatut['libelle'] = 'Valeur = 0 impossible pour ce paramètre';
            }
        }

// III.4 Valeurs < 0 (hors température de air 1409, potentiel REDOX 1330)
        if ($mesure < 0) {
            if ($codeParametre != 1409 && $codeParametre != 1330 && $codeParametre != 1420 && $codeParametre != 1429) {
                $tabStatut['ko'] = $tabStatut['ko'] + 1;
                $tabStatut['statut'] = 2;
                $tabStatut['libelle'] = 'Valeur < 0 impossible pour ce paramètre';
            }
        }

// III.5 Valeurs avec code remarque '> (3)' hors bactério (1147,1448,1449,1451,5479,6455); code remarque ="Trace (7)"
        $codeParamsBacterio = array(
            1447, 1448, 1449, 1451, 5479, 6455
        );
        if (($codeRq == 3 && !in_array($codeParametre, $codeParamsBacterio)) || $codeRq == 7) {
            $tabStatut['ko'] = $tabStatut['ko'] + 1;
            $tabStatut['statut'] = 2;
            $tabStatut['libelle'] = 'Code Remarque > 3 ou == 7 impossible pour ce paramètre';
        }

// III.6 1 < pH(1302) < 14
        if ($codeParametre == '1302') {
            $mPh = $mesure;
            if (!is_null($mPh)) {
                if ($mPh < 1 || $mPh > 14) {
                    $tabStatut['ko'] = $tabStatut['ko'] + 1;
                    $tabStatut['statut'] = 2;
                    $tabStatut['libelle'] = 'Le pH n\'est pas entre 1 et 14';
                }
            }
        }

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

        return $tabStatut;
    }

    protected function _controlesSpecifiques($pgCmdPrelev) {
// Contrôles spécifiques  

        $okControles = 0;

// III.7
        $okControles += $this->_modeleWeiss($pgCmdPrelev);

// III.8 
        $okControles += $this->_balanceIonique($pgCmdPrelev);

// III.9
        $okControles += $this->_balanceIoniqueTds2($pgCmdPrelev);

// III.10
        $okControles += $this->_ortophosphate($pgCmdPrelev);

// III.11
        $okControles += $this->_ammonium($pgCmdPrelev);

// III.12
        $okControles += $this->_pourcentageHorsOxygene($pgCmdPrelev);

        // III.13
        $okControles += $this->_sommeParametresDistincts($pgCmdPrelev);

        // III.14
        $okControles += $this->_controleVraisemblanceMacroPolluants($pgCmdPrelev);

        return $okControles;
    }

// III.7 modèle  de WEISS : cohérence Teau, % O2, Concentration O2  sauf si Conductivité > 10 000
    protected function _modeleWeiss($pgCmdPrelev) {

        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', '_modeleWeiss');
        $emSqe = $this->get('doctrine')->getManager('sqe');
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
        $mOxDis = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1311');
        if (!$mOxDis) {
            $mOxDiss = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1311', 1);
        }
        $mTEau = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1301');
        if (!$mTEau) {
            $mTEau = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1301', 1);
        }
        $mConductivite = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1303');
        if (!$mConductivite) {
            $mConductivite = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1303', 1);
        }


        if ($mTxSatOx && $mOxDiss && $mTEau && $mConductivite) {
            if ($mConductivite->getResultat() < 10000) {
                $mTEauK = $mTEau->getResultat() + 273.15;
                $argExp = -173.4292 + 249.6339 * 100 / $mTEauK + 143.3483 * log($mTEauK / 100) - 21.8492 * $mTEauK / 100;
                $txSatModel = 1.4276 * exp($argExp);
                $indVraiWess = $txSatModel - $mTxSatOx->getResultat();

                if (abs($indVraiWess) > 25) {
//error
                    $okWeiss++;
                    $tabStatut['statut'] = 2;
                    $tabStatut['libelle'] = 'Modele de Weiss : valeurs non conformes (' . abs($indVraiWess) . ')';
                } else if (10 < abs($indVraiWess) && abs($indVraiWess) <= 25) {
// Avertissement
                    $tabStatut['statut'] = 1;
                    $tabStatut['libelle'] = 'Modele de Weiss : valeur réservée (' . abs($indVraiWess) . ')';
                }
            } else {
//error ou avertissement ?
                $tabStatut['statut'] = 1;
                $tabStatut['libelle'] = 'Modele de Weiss : Conductivité supérieur à 10000  (' . abs($indVraiWess) . ')';
            }

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
            return $okWeiss;
        }
    }

// III.8 Balance ionique (meq) sauf si tous les résultats < LQ
    protected function _balanceIonique($pgCmdPrelev) {

        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', '_balanceIonique');
        $emSqe = $this->get('doctrine')->getManager('sqe');

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
        } else {
            $par1374Resultat = null;
        }

        $par1335 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1335');
        if (!$par1335) {
            $par1335 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1335', 1);
        }
        if ($par1335) {
            $par1335Resultat = $par1335->getResultat();
        } else {
            $par1335Resultat = null;
        }

        $par1372 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1372');
        if (!$par1372) {
            $par1372 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1372', 1);
        }
        if ($par1372) {
            $par1372Resultat = $par1372->getResultat();
        } else {
            $par1372Resultat = null;
        }

        $par1367 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1367');
        if (!$par1367) {
            $par1367 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1367', 1);
        }
        if ($par1367) {
            $par1367Resultat = $par1367->getResultat();
        } else {
            $par1367Resultat = null;
        }

        $par1375 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1375');
        if (!$par1375) {
            $par1375 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1375', 1);
        }
        if ($par1375) {
            $par1375Resultat = $par1375->getResultat();
        } else {
            $par1375Resultat = null;
        }

        $par1433 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1433');
        if (!$par1433) {
            $par1433 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1433', 1);
        }
        if ($par1433) {
            $par1433Resultat = $par1433->getResultat();
        } else {
            $par1433Resultat = null;
        }


        $par1340 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1340');
        if (!$par1340) {
            $par1340 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1340', 1);
        }
        if ($par1340) {
            $par1340Resultat = $par1340->getResultat();
        } else {
            $par1340Resultat = null;
        }

        $par1338 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1338');
        if (!$par1338) {
            $par1338 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1338', 1);
        }
        if ($par1338) {
            $par1338Resultat = $par1338->getResultat();
        } else {
            $par1338Resultat = null;
        }

        $par1337 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1337');
        if (!$par1337) {
            $par1337 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1337', 1);
        }
        if ($par1337) {
            $par1337Resultat = $par1337->getResultat();
        } else {
            $par1337Resultat = null;
        }

        $par1327 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1327');
        if (!$par1327) {
            $par1327 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1327', 1);
        }
        if ($par1327) {
            $par1327Resultat = $par1327->getResultat();
        } else {
            $par1327Resultat = null;
        }

        $par1339 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1339');
        if (!$par1339) {
            $par1339 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1339', 1);
        }
        if ($par1339) {
            $par1339Resultat = $par1339->getResultat();
        } else {
            $par1339Resultat = null;
        }

        $cCationParams = array(1374 => $par1374Resultat,
            1335 => $par1335Resultat,
            1372 => $par1372Resultat,
            1367 => $par1367Resultat,
            1375 => $par1375Resultat
        );

        $cAnionParams = array(1433 => $par1433Resultat,
            1340 => $par1340Resultat,
            1338 => $par1338Resultat,
            1337 => $par1337Resultat,
            1327 => $par1327Resultat,
            1339 => $par1339Resultat
        );

// Vérification de l'existence des paramètres
        $valid = true;
        $cpt = 0;
        $keys = array_keys($cCationParams);
        while ($cpt < count($keys) && $valid) {
            if (is_null($cCationParams[$keys[$cpt]])) {
                $valid = false;
            }
            $cpt++;
        }


        if ($valid) {
            $cpt = 0;
            $keys = array_keys($cAnionParams);
            while ($cpt < count($keys) && $valid) {
                if (is_null($cAnionParams[$keys[$cpt]])) {
                    $valid = false;
                }
                $cpt++;
            }
        }

        if ($valid) {
            $countLq = 0;
            foreach ($cCationParams as $idx => $cCationParam) {
                $parIdx = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, $idx);
                if (!$parIdx) {
                    $parIdx = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, $idx, 1);
                }
                if ($parIdx) {
                    $parIdxResultat = $parIdx->getResultat();
                } else {
                    $parIdxResultat = null;
                }
                if ($parIdxResultat == 10) {
                    $countLq++;
                }
            }

            foreach ($cAnionParams as $idx => $cAnionParam) {
                $parIdx = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, $idx);
                if (!$parIdx) {
                    $parIdx = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, $idx, 1);
                }
                if ($parIdx) {
                    $parIdxResultat = $parIdx->getResultat();
                } else {
                    $parIdxResultat = null;
                }
                if ($parIdxResultat == 10) {
                    $countLq++;
                }
            }

            if ($countLq >= (count($cAnionParams) + count($cCationParams))) {
                $okBalanceIonique++;
                $tabStatut['statut'] = 2;
                $tabStatut['libelle'] = 'Balance Ionique : Tous les dosages sont en LQ';
            } else {
                $vCationParams = array(1374 => 20.039,
                    1335 => 18.03846,
                    1372 => 12.1525,
                    1367 => 39.0983,
                    1375 => 22.98977
                );

                $vAnionParams = array(1433 => 47.48568,
                    1340 => 63.0049,
                    1338 => 48.0313,
                    1337 => 35.453,
                    1327 => 61.01684,
                    1339 => 46.0055
                );


                $cCation = 0;
                foreach ($cCationParams as $idx => $cCationParam) {
                    $cCation += $cCationParam / $vCationParams[$idx];
                }

                $cAnion = 0;
                foreach ($cAnionParams as $idx => $cAnionParam) {
                    $cAnion += $cAnionParam / $vAnionParams[$idx];
                }

                $indVraiBion = ($cCation - $cAnion);

                if (0.5 < $indVraiBion && $indVraiBion <= 1.25) {
                    $tabStatut['statut'] = 1;
                    $tabStatut['libelle'] = 'Balance Ionique : Valeur réservée';
                } else if ($indVraiBion > 1.25) {
                    $okBalanceIonique++;
                    $tabStatut['statut'] = 2;
                    $tabStatut['libelle'] = 'Balance Ionique : Valeur non conforme';
                }
            }


            $par1374->setCodeStatut($tabStatut['statut']);
            $par1374->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1374);
            $par1335->setCodeStatut($tabStatut['statut']);
            $par1335->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1335);
            $par13721->setCodeStatut($tabStatut['statut']);
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

// III.9 Comparaison Balance ionique / conductivité (Feret)
    protected function _balanceIoniqueTds2($pgCmdPrelev) {

        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', '_balanceIoniqueTds2');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdMesureEnv = $emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $repoPgCmdAnalyse = $emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');

        $tabStatut = array();
        $tabStatut['statut'] = 0;
        $tabStatut['libelle'] = null;
        $okBalanceIoniqueTds2 = 0;

        $par1374 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1374');
        if (!$par1374) {
            $par1374 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1374', 1);
        }
        if ($par1374) {
            $par1374Resultat = $par1374->getResultat();
        } else {
            $par1374Resultat = null;
        }

        $par1335 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1335');
        if (!$par1335) {
            $par1335 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1335', 1);
        }
        if ($par1335) {
            $par1335Resultat = $par1335->getResultat();
        } else {
            $par1335Resultat = null;
        }

        $par1372 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1372');
        if (!$par1372) {
            $par1372 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1372', 1);
        }
        if ($par1372) {
            $par1372Resultat = $par1372->getResultat();
        } else {
            $par1372Resultat = null;
        }

        $par1367 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1367');
        if (!$par1367) {
            $par1367 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1367', 1);
        }
        if ($par1367) {
            $par1367Resultat = $par1367->getResultat();
        } else {
            $par1367Resultat = null;
        }

        $par1375 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1375');
        if (!$par1375) {
            $par1375 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1375', 1);
        }
        if ($par1375) {
            $par1375Resultat = $par1375->getResultat();
        } else {
            $par1375Resultat = null;
        }

        $par1433 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1433');
        if (!$par1433) {
            $par1433 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1433', 1);
        }
        if ($par1433) {
            $par1433Resultat = $par1433->getResultat();
        } else {
            $par1433Resultat = null;
        }


        $par1340 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1340');
        if (!$par1340) {
            $par1340 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1340', 1);
        }
        if ($par1340) {
            $par1340Resultat = $par1340->getResultat();
        } else {
            $par1340Resultat = null;
        }

        $par1338 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1338');
        if (!$par1338) {
            $par1338 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1338', 1);
        }
        if ($par1338) {
            $par1338Resultat = $par1338->getResultat();
        } else {
            $par1338Resultat = null;
        }

        $par1337 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1337');
        if (!$par1337) {
            $par1337 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1337', 1);
        }
        if ($par1337) {
            $par1337Resultat = $par1337->getResultat();
        } else {
            $par1337Resultat = null;
        }

        $par1327 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1327');
        if (!$par1327) {
            $par1327 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1327', 1);
        }
        if ($par1327) {
            $par1327Resultat = $par1327->getResultat();
        } else {
            $par1327Resultat = null;
        }

        $par1339 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1339');
        if (!$par1339) {
            $par1339 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1339', 1);
        }
        if ($par1339) {
            $par1339Resultat = $par1339->getResultat();
        } else {
            $par1339Resultat = null;
        }

        $cCationParams = array(1374 => $par1374Resultat,
            1335 => $par1335Resultat,
            1372 => $par1372Resultat,
            1367 => $par1367Resultat,
            1375 => $par1375Resultat
        );

        $cAnionParams = array(1433 => $par1433Resultat,
            1340 => $par1340Resultat,
            1338 => $par1338Resultat,
            1337 => $par1337Resultat,
            1327 => $par1327Resultat,
            1339 => $par1339Resultat
        );

        $mConductivite = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1303');
        if (!$mConductivite) {
            $mConductivite = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1303', 1);
        }
        if ($mConductivite) {
            $mConductiviteResultat = $mConductivite->getResultat();
        } else {
            $mConductiviteResultat = null;
        }


// Vérification de l'existence des paramètres
        $valid = true;
        $cpt = 0;
        $keys = array_keys($cCationParams);
        while ($cpt < count($keys) && $valid) {
            if (is_null($cCationParams[$keys[$cpt]])) {
                $valid = false;
            }
            $cpt++;
        }

        if ($valid) {
            $cpt = 0;
            $keys = array_keys($cAnionParams);
            while ($cpt < count($keys) && $valid) {
                if (is_null($cAnionParams[$keys[$cpt]])) {
                    $valid = false;
                }
                $cpt++;
            }
        }

        if ($valid) {
            if (is_null($mConductivite)) {
                $valid = false;
            }
        }

        if ($valid) {
            $countLq = 0;
            foreach ($cCationParams as $idx => $cCationParam) {
                $parIdx = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, $idx);
                if (!$parIdx) {
                    $parIdx = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, $idx, 1);
                }
                if ($parIdx) {
                    $parIdxResultat = $parIdx->getResultat();
                } else {
                    $parIdxResultat = null;
                }
                if ($parIdxResultat == 10) {
                    $countLq++;
                }
            }

            foreach ($cAnionParams as $idx => $cAnionParam) {
                $parIdx = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, $idx);
                if (!$parIdx) {
                    $parIdx = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, $idx, 1);
                }
                if ($parIdx) {
                    $parIdxResultat = $parIdx->getResultat();
                } else {
                    $parIdxResultat = null;
                }
                if ($parIdxResultat == 10) {
                    $countLq++;
                }
            }

            if ($countLq >= (count($cAnionParams) + count($cCationParams))) {
                $okBalanceIoniqueTds2++;
                $tabStatut['statut'] = 2;
                $tabStatut['libelle'] = 'Balance Ionique TDS 2 : Tous les dosages sont en LQ';
            } else {
                $cCation = 0;
                foreach ($cCationParams as $idx => $cCationParam) {
                    $cCation += $cCationParam;
                }

                $cAnion = 0;
                foreach ($cAnionParams as $idx => $cAnionParam) {
                    $cAnion += $cAnionParam;
                }

                $tdsEstime = $cCation + $cAnion;
                $tdsModele = 35.18 + 0.68 * $mConductivite;

                $indVraiTds = $tdsEstime - $tdsModele;

                if (175 <= abs($indVraiTds) && abs($indVraiTds) < 280) {
                    $tabStatut['statut'] = 1;
                    $tabStatut['libelle'] = 'Balance Ionique TDS 2 : Réserve';
                } else if (abs($indVraiTds) >= 280) {
                    $okBalanceIoniqueTds2++;
                    $tabStatut['statut'] = 2;
                    $tabStatut['libelle'] = 'Balance Ionique TDS 2 : Valeur non conforme';
                }
            }

            $par1374->setCodeStatut($tabStatut['statut']);
            $par1374->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1374);
            $par1335->setCodeStatut($tabStatut['statut']);
            $par1335->setLibelleStatut($tabStatut['libelle']);
            $emSqe->persist($par1335);
            $par13721->setCodeStatut($tabStatut['statut']);
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

        return $okBalanceIoniqueTds2;
    }

// III.10 [PO4] (1433) en P < [P total](1350) 
    protected function _ortophosphate($pgCmdPrelev) {

        $session = $this->get('session');
        $session->set('menu', 'saisieDonnees');
        $session->set('controller', 'SaisieDonnees');
        $session->set('fonction', '_ortophosphate');
        $emSqe = $this->get('doctrine')->getManager('sqe');

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
        } else {
            $mPo4Resultat = null;
        }

        $mP = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1350');
        if (!$mP) {
            $mP = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1350', 1);
        }
        if ($mP) {
            $mPResultat = $mP->getResultat();
        } else {
            $mPResultat = null;
        }

        if (!is_null($mPo4Resultat) && !is_null($mPResultat)) {
            if ($mPo4Resultat == 10 && $mPResultat == 10) {
                $okOrtophosphate++;
                $tabStatut['statut'] = 2;
                $tabStatut['libelle'] = 'Ortophosphate : Tous les dosages sont en LQ';
            } else {
                $indP = $mPo4Resultat / $mPResultat;
                if (1 < $indP && $indP <= 1.25) {
                    $tabStatut['statut'] = 1;
                    $tabStatut['libelle'] = 'Ortophosphate : Réserve';
                } else if ($indP > 1.25) {
                    $okOrtophosphate++;
                    $tabStatut['statut'] = 2;
                    $tabStatut['libelle'] = 'Ortophosphate : Valeur non conforme';
                }
            }

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
        } else {
            $mNh4Resultat = null;
        }

        $mNkj = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParametre($pgCmdPrelev, '1319');
        if (!$mNkj) {
            $mNkj = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParametreNumOrdre($pgCmdPrelev, '1319', 1);
        }
        if ($mNkj) {
            $mNkjResultat = $mNkj->getResultat();
        } else {
            $mNkjResultat = null;
        }


        if (!is_null($mNh4Resultat) && !is_null($mNkjResultat)) {
            if ($mNh4Resultat == 10 && $mNkjResultat == 10) {
                $okAmmonium++;
                $tabStatut['statut'] = 2;
                $tabStatut['libelle'] = 'Ammonium : tous les dosages sont en LQ';
            } else {
                $indP = $mNh4Resultat / $mNkjResultat;
                if (1 < $indP && $indP <= 1.25) {
                    $tabStatut['statut'] = 1;
                    $tabStatut['libelle'] = 'Ammonium : Réserve';
                } else if ($indP > 1.25) {
                    $okAmmonium++;
                    $tabStatut['statut'] = 2;
                    $tabStatut['libelle'] = 'Ammonium : Valeur non conforme';
                }
            }

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

        $par246 = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevCodeUniteParametre($pgCmdPrelev, '246', '1312');
        if (!$par246) {
            $par246 = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevCodeUniteParametre($pgCmdPrelev, '246', '1312');
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
            $pars[$i] = $par1746;
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

// Test de validité
        $valid = true;
        $i = 0;
        while ($i < count($sommeParams) && $valid) {
            $j = 0;
            $keys = array_keys($sommeParams[$i]);
            while ($j < count($keys) && $valid) {
                if (is_null($sommeParams[$i][$keys[$j]])) {
                    $valid = false;
                }
                $j++;
            }
            $i++;
        }

        if ($valid) {
            $i = 0;
            while ($i < count($resultParams) && $valid) {
                $j = 0;
                $keys = array_keys($sommeParams[$i]);
                while ($j < count($keys) && $valid) {
                    if (is_null($resultParams[$i][$keys[$j]])) {
                        $valid = false;
                    }
                    $j++;
                }
                $i++;
            }
        }

        if ($valid) {
            foreach ($sommeParams as $idx => $sommeParam) {
                $somme = 0;
                foreach ($sommeParam as $key => $param) {
                    $somme += $param;
                }

                $percent = ((20 / 100) * $resultParams[$idx]);
                $resultParamMin = $resultParams[$idx] - $percent;
                $resultParamMax = $resultParams[$idx] + $percent;
                if (($resultParamMin > $somme) || ($somme > $resultParamMax)) {
                    $okSommeParametresDistincts++;
                    $tabStatut['statut'] = 2;
                    $tabStatut['libelle'] = 'Somme Parametres Distincts : La somme des paramètres ne correspond pas au paramètre global ';

                    for ($i = 0; $i < count($pars); $i++) {
                        $par = $pars[$i];
                        $par->setCodeStatut($tabStatut['statut']);
                        $par->setLibelleStatut($tabStatut['libelle']);
                        $emSqe->persist($par);
                    }
                    $emSqe->flush();
                }
            }
        }
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
        return $okControleVraisemblanceMacroPolluants;
    }

}
