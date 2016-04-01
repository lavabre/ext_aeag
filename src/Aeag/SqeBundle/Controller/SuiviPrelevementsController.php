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

class SuiviPrelevementsController extends Controller {

    private $phase82atteinte = false;

    public function indexAction() {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }

        $session = $this->get('session');
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
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

        return $this->render('AeagSqeBundle:SuiviPrelevements:index.html.twig', array('user' => $user,
                    'lotans' => $pgProgLotAns));
    }

    public function lotPeriodesAction($lotanId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
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


        return $this->render('AeagSqeBundle:SuiviPrelevements:lotPeriodes.html.twig', array(
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
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
        $session->set('fonction', 'lotPeriodeStations');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
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
                $tabStations[$i]['station'] = $pgProgLotPeriodeProg->getStationAn()->getStation();
                $tabStations[$i]['lien'] = '/sqe_fiches_stations/' . $pgProgLotPeriodeProg->getStationAn()->getStation()->getCode() . '.pdf';
                $tabStations[$i]['cmdPrelev'] = null;
                $tabSuiviPrels = array();
                $j = 0;
                $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeByLotanPrestatairePeriode($pgProgLotAn, $pgProgLotPeriodeProg->getGrparAn()->getPrestaDft(), $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                if ($pgCmdDemande) {
                    $tabStations[$i]['cmdDemande'] = $pgCmdDemande;
                    $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevByPrestaPrelDemandeStationPeriode($pgProgLotPeriodeProg->getGrparAn()->getPrestaDft(), $pgCmdDemande, $pgProgLotPeriodeProg->getStationAn()->getStation(), $pgProgLotPeriodeProg->getPeriodan()->getPeriode());
                    if ($pgCmdPrelev) {
                        $tabStations[$i]['cmdPrelev'] = $pgCmdPrelev;
                        $pgCmdSuiviPrels = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelev($pgCmdPrelev);
                        foreach ($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
                            $tabSuiviPrels[$j] = $pgCmdSuiviPrel;
                            $j++;
                        }
                    }
                }
                $tabStations[$i]['suiviPrels'] = $tabSuiviPrels;
                $i++;
            }
        }

//        \Symfony\Component\VarDumper\VarDumper::dump($tabDates);
//        return new Response ('');   

        return $this->render('AeagSqeBundle:SuiviPrelevements:lotPeriodeStations.html.twig', array(
                    'user' => $pgProgWebUser,
                    'lotan' => $pgProgLotAn,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'stations' => $tabStations));
    }

    public function lotPeriodeStationDemandeAction($stationId = null, $periodeAnId = null, $cmdDemandeId = null) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
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
                        $tabCmdPrelevs[$i]['saisieTerrain'] = 'N';
                        $tabCmdPrelevs[$i]['nbParametresTerrain'] = 0;
                        $tabCmdPrelevs[$i]['nbSaisisParametresTerrain'] = 0;
                        $tabCmdPrelevs[$i]['nbSaisisParametresTerrainCorrect'] = 0;
                        $tabCmdPrelevs[$i]['nbSaisisParametresTerrainIncorrect'] = 0;
                        $tabCmdPrelevs[$i]['nbSaisisParametresTerrainErreur'] = 0;
                        $tabCmdPrelevs[$i]['saisieAnalyse'] = 'N';
                        $tabCmdPrelevs[$i]['nbParametresAnalyse'] = 0;
                        $tabCmdPrelevs[$i]['nbSaisisParametresAnalyse'] = 0;
                        $tabCmdPrelevs[$i]['nbSaisisParametresAnalyseCorrect'] = 0;
                        $tabCmdPrelevs[$i]['nbSaisisParametresAnalyseIncorrect'] = 0;
                        $tabCmdPrelevs[$i]['nbSaisisParametresAnalyseErreur'] = 0;
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
                                if ($pgProgWebUser->getPrestataire() and $pgCmdSuiviPrel->getUser()->getPrestataire()) {
                                    if ($pgProgWebUser->getPrestataire()->getAdrCorId() == $pgCmdSuiviPrel->getUser()->getPrestataire()->getAdrCorId()) {
                                        $tabSuiviPrels[$j]['maj'] = 'O';
                                        $tabCmdPrelevs[$i]['maj'] = 'O';
                                        $tabDemande[$i]['maj'] = 'O';
                                        if ($pgCmdSuiviPrel->getStatutPrel() == 'F') {
                                            $pgProgPrestaTypfic = $repoPgProgPrestaTypfic->getPgProgPrestaTypficByCodeMilieu($pgProgTypeMilieu, $pgProgWebUser->getPrestataire());
                                            if ($pgProgPrestaTypfic) {
                                                $NbProgLotParamAn = $repoPgProgLotParamAn->getNbProgLotParamAnEnvSituByStationAnPeriodeAnPrestataire($pgProgLotStationAn, $pgProgLotPeriodeAn, $pgProgWebUser->getPrestataire());
                                                $tabCmdPrelevs[$i]['nbParametresTerrain'] = $NbProgLotParamAn;
                                                $NbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
                                                $NbCmdMesureEnvCorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '0');
                                                $NbCmdMesureEnvIncorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '1');
                                                $NbCmdMesureEnvErreur = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '2');
                                                $NbCmdAnalyse = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
                                                $NbCmdAnalyseCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '0');
                                                $NbCmdAnalyseIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '1');
                                                $NbCmdAnalyseErreur = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '2');
                                                $tabCmdPrelevs[$i]['nbSaisisParametresTerrain'] = $NbCmdMesureEnv + $NbCmdAnalyse;
                                                $tabCmdPrelevs[$i]['nbSaisisParametresTerrainCorrect'] = $NbCmdMesureEnvCorrect + $NbCmdAnalyseCorrect;
                                                $tabCmdPrelevs[$i]['nbSaisisParametresTerrainIncorrect'] = $NbCmdMesureEnvIncorrect + $NbCmdAnalyseIncorrect;
                                                $tabCmdPrelevs[$i]['nbSaisisParametresTerrainErreur'] = $NbCmdMesureEnvErreur + $NbCmdAnalyseErreur;
                                                $tabCmdPrelevs[$i]['saisieTerrain'] = 'O';
                                            }
                                        }
                                        if ($pgCmdSuiviPrel->getStatutPrel() == 'A') {
                                            $pgProgPrestaTypfic = $repoPgProgPrestaTypfic->getPgProgPrestaTypficByCodeMilieu($pgProgTypeMilieu, $pgProgWebUser->getPrestataire());
                                            if ($pgProgPrestaTypfic) {
                                                $NbProgLotParamAn = $repoPgProgLotParamAn->getNbProgLotParamAnAnaByStationAnPeriodeAnPrestataire($pgProgLotStationAn, $pgProgLotPeriodeAn, $pgProgWebUser->getPrestataire());
                                                $tabCmdPrelevs[$i]['nbParametresAnalyse'] = $NbProgLotParamAn;
                                                $NbCmdAnalyse = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
                                                $NbCmdAnalyseCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '0');
                                                $NbCmdAnalyseIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '1');
                                                $NbCmdAnalyseErreur = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '2');
                                                $tabCmdPrelevs[$i]['nbSaisisParametresAnalyse'] = $NbCmdAnalyse;
                                                $tabCmdPrelevs[$i]['nbSaisisParametresAnalyseCorrect'] = $NbCmdAnalyseCorrect;
                                                $tabCmdPrelevs[$i]['nbSaisisParametresAnalyseIncorrect'] = $NbCmdAnalyseIncorrect;
                                                $tabCmdPrelevs[$i]['nbSaisisParametresAnalyseErreur'] = $NbCmdAnalyseErreur;
                                                $tabCmdPrelevs[$i]['saisieAnalyse'] = 'O';
                                            }
                                        }
                                    } else {
                                        $tabSuiviPrels[$j]['maj'] = 'N';
                                    }
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

        return $this->render('AeagSqeBundle:SuiviPrelevements:lotPeriodeStationDemande.html.twig', array(
                    'user' => $pgProgWebUser,
                    'lotan' => $pgProgLotAn,
                    'station' => $pgRefStationMesure,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'demande' => $tabDemande));
    }

    public function lotPeriodeStationDemandeSuiviNewAction($prelevId = null, $periodeAnId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviNew');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdSuiviPrel = new PgCmdSuiviPrel();
        $form = $this->createForm(new PgCmdSuiviPrelMajType($user), $pgCmdSuiviPrel);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $pgCmdSuiviPrel->setPrelev($pgCmdPrelev);
            $pgCmdSuiviPrel->setUser($pgProgWebUser);
            $datePrel = $pgCmdSuiviPrel->getDatePrel();
            $emSqe->persist($pgCmdSuiviPrel);
            if ($pgCmdSuiviPrel->getStatutPrel() == 'F') {
                $pgCmdPrelev->setDatePrelev($datePrel);
                $pgCmdPrelev->setRealise('O');
            } elseif ($pgCmdSuiviPrel->getStatutPrel() == 'N') {
                $pgCmdPrelev->setDatePrelev($datePrel);
                $pgCmdPrelev->setRealise('N');
            } else {
                $pgCmdPrelev->setDatePrelev(null);
                $pgCmdPrelev->setRealise(null);
            }
            $emSqe->persist($pgCmdPrelev);
            $emSqe->flush();
            $session->getFlashBag()->add('notice-success', 'le suivi du ' . $datePrel->format('d/m/Y') . ' a été créé !');

            return $this->redirect($this->generateUrl('AeagSqeBundle_suiviPrelevements_lot_periode_station_demande', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                'periodeAnId' => $periodeAnId,
                                'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
        }

        return $this->render('AeagSqeBundle:SuiviPrelevements:lotPeriodeStationDemandeSuiviNew.html.twig', array(
                    'prelev' => $pgCmdPrelev,
                    'periodeAnId' => $periodeAnId,
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
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviMaj');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $form = $this->createForm(new PgCmdSuiviPrelMajType($user), $pgCmdSuiviPrel);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $datePrel = $pgCmdSuiviPrel->getDatePrel();
            $emSqe->persist($pgCmdSuiviPrel);
            if ($pgCmdSuiviPrel->getStatutPrel() == 'F') {
                $pgCmdPrelev->setDatePrelev($datePrel);
                $pgCmdPrelev->setRealise('O');
            } elseif ($pgCmdSuiviPrel->getStatutPrel() == 'N') {
                $pgCmdPrelev->setDatePrelev($datePrel);
                $pgCmdPrelev->setRealise('N');
            } else {
                $pgCmdPrelev->setDatePrelev(null);
                $pgCmdPrelev->setRealise(null);
            }
            $emSqe->persist($pgCmdPrelev);
            $emSqe->flush();
            $session->getFlashBag()->add('notice-success', 'le suivi du ' . $datePrel->format('d/m/Y') . ' a été modifié !');

            return $this->redirect($this->generateUrl('AeagSqeBundle_suiviPrelevements_lot_periode_station_demande', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                'periodeAnId' => $periodeAnId,
                                'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
        }

        return $this->render('AeagSqeBundle:SuiviPrelevements:lotPeriodeStationDemandeSuiviMaj.html.twig', array(
                    'prelev' => $pgCmdPrelev,
                    'periodeAnId' => $periodeAnId,
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
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
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

        return $this->render('AeagSqeBundle:SuiviPrelevements:lotPeriodeStationDemandeSuiviVoir.html.twig', array(
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
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviDeposer');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();


        return $this->render('AeagSqeBundle:SuiviPrelevements:lotPeriodeStationDemandeSuiviDeposer.html.twig', array(
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
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
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

        return $this->redirect($this->generateUrl('AeagSqeBundle_suiviPrelevements_lot_periode_station_demande', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                            'periodeAnId' => $periodeAnId,
                            'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));


//          \Symfony\Component\VarDumper\VarDumper::dump($tabDemande);
//        return new Response ('');
    }

    public function lotPeriodeStationDemandeSuiviSaisirEnvSituAction($prelevId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviNew');
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

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);
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
                            } else {
                                $tabGroupes[$nbGroupes]['erreur'] = $tabGroupes[$nbGroupes]['erreur'] + 1;
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
                        $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                        if ($pgCmdAnalyse) {
                            if ($pgCmdAnalyse->getCodeStatut() == '0') {
                                $tabGroupes[$nbGroupes]['correct'] = $tabGroupes[$nbGroupes]['correct'] + 1;
                            } elseif ($pgCmdAnalyse->getCodeStatut() == '1') {
                                $tabGroupes[$nbGroupes]['warning'] = $tabGroupes[$nbGroupes]['warning'] + 1;
                            } else {
                                $tabGroupes[$nbGroupes]['erreur'] = $tabGroupes[$nbGroupes]['erreur'] + 1;
                            }
                            $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = $pgCmdAnalyse;
                            $tabParamAns[$nbParamAns]['unite'] = $pgCmdAnalyse->getCodeUnite();
                        } else {
                            $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = null;
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

                    if ($pgProgLotParamAn->getCodeFraction()) {
                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                        $tabParamAns[$nbParamAns]['fraction'] = $pgSandreFraction;
                    } else {
                        $tabParamAns[$nbParamAns]['fraction'] = null;
                    }
                    $nbParamAns++;
                }
                $tabGroupes[$nbGroupes]['paramAns'] = $tabParamAns;
                $nbGroupes++;
                // }
            }
        }

        //\Symfony\Component\VarDumper\VarDumper::dump($tabParamAns);
        //return new Response ('');

        return $this->render('AeagSqeBundle:SuiviPrelevements:lotPeriodeStationDemandeSuiviSaisirEnvSitu.html.twig', array(
                    'user' => $pgProgWebUser,
                    'lotan' => $pgProgLotAn,
                    'station' => $pgRefStationMesure,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'demande' => $pgCmdPrelev->getDemande(),
                    'cmdPrelev' => $pgCmdPrelev,
                    'groupes' => $tabGroupes));
    }

    public function lotPeriodeStationDemandeSuiviResultatEnvSituAction($prelevId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviNew');
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


        $now = date('Ymd');
        $today = new \DateTime($now);

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);
        $nbErreurs = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            if ($pgProgLotGrparAn->getvalide() == 'O' and ( $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ENV' or $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'SIT')) {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
                // if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
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
                    $tabStatut['statut'] = 0;
                    $tabStatut['libelle'] = null;
                    $parametre = $pgProgLotParamAn->getCodeParametre()->getCodeparametre();
                    $inSitu = 1;
                    $ok = 0;
                    if (strlen($valeur) > 0) {

                        $tabStatut = $this->_controleVraisemblance($parametre, $valeur, $remarque, $unite, $inSitu, $tabStatut);

                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                        $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametreCodeUniteNatureFraction($pgProgLotParamAn->getCodeParametre()->getCodeParametre(), $unite, $pgSandreFraction);
                        if ($pgProgUnitesPossiblesParam) {
                            if (strlen($pgProgUnitesPossiblesParam->getValMax()) > 0) {
                                if ($valeur > $pgProgUnitesPossiblesParam->getValMax()) {
                                    $tabStatut['statut'] = 2;
                                    $tabStatut['libelle'] = 'Valeur doit être inferieure à ' . $pgProgUnitesPossiblesParam->getValMax();
                                }
                            }
                        }
                        $ok = $tabStatut['statut'];
                    }

                    $pgCmdMesureEnv = $repoPgCmdMesureEnv->getPgCmdMesureEnvByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                    if ($pgProgGrpParamRef->getTypeGrp() == 'ENV') {
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
                    if ($pgProgGrpParamRef->getTypeGrp() == 'SIT') {
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
                                $pgCmdAnalyse->setLieuAna('1');
                                $pgCmdAnalyse->setPrelevId($pgCmdPrelev->getId());
                                $pgCmdAnalyse->setParamProg($pgProgLotParamAn);
                                $pgCmdAnalyse->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                $pgCmdAnalyse->setCodeFraction($pgSandreFraction);
                            }
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
                                    $pgCmdAnalyse->setLieuAna('1');
                                    $pgCmdAnalyse->setPrelevId($pgCmdPrelev->getId());
                                    $pgCmdAnalyse->setParamProg($pgProgLotParamAn);
                                    $pgCmdAnalyse->setCodeParametre($pgProgLotParamAn->getCodeParametre());
                                    $pgCmdAnalyse->setCodeFraction($pgSandreFraction);
                                }
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
                            } else {
                                if ($pgCmdAnalyse) {
                                    $emSqe->remove($pgCmdAnalyse);
                                }
                            }
                        }
                    }
                }
                $emSqe->flush();

                $NbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
                $NbCmdMesureEnvCorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '0');
                $NbCmdMesureEnvIncorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '1');
                $NbCmdAnalyseSitu = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
                $NbCmdAnalyseSituCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '0');
                $NbCmdAnalyseSituIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '1');
                $NbCmdAnalyseAna = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
                $NbCmdAnalyseAnaCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '0');
                $NbCmdAnalyseAnaIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '1');
                $NbCmdTot = $NbCmdMesureEnv + $NbCmdAnalyseSitu + $NbCmdAnalyseAna;
                $NbCmdTotSaisis = $NbCmdMesureEnvCorrect + $NbCmdMesureEnvIncorrect + $NbCmdAnalyseSituCorrect + $NbCmdAnalyseSituIncorrect + $NbCmdAnalyseAnaCorrect + $NbCmdAnalyseAnaIncorrect;
                if ($NbCmdTot == $NbCmdTotSaisis) {
                    $ok = $this->_controlesSpecifiques($pgCmdPrelev);
                } else {
                    $ok = 1;
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
                if ($ok == 0) {
                    $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R45');
                } else {
                    $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R10');
                }
                $pgCmdFichiersRps->setPhaseFichier($pgProgPhases);
                $emSqe->persist($pgCmdFichiersRps);
                $pgCmdPrelev->setFichierRps($pgCmdFichiersRps);

                if ($ok == 0) {
                    $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');
                } else {
                    $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M20');
                }
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
                $emSqe->persist($pgCmdPrelev);

                $emSqe->flush();
                // }
            }
        }

        //  return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
        //return new Response ('');

        if ($ok == 0) {
            return $this->redirect($this->generateUrl('AeagSqeBundle_suiviPrelevements_lot_periode_station_demande', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
        } else {
            return $this->redirect($this->generateUrl('AeagSqeBundle_suiviPrelevements_lot_periode_station_demande_suivi_saisir_env_situ', array(
                                'prelevId' => $pgCmdPrelev->getId(),
                                'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                'stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
            )));
        }
    }

    public function lotPeriodeStationDemandeSuiviSaisirAnaAction($prelevId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviNew');
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
        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevStatutPrel($pgCmdPrelev, 'A');

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);
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

                    $pgCmdAnalyse = $repoPgCmdAnalyse->getPgCmdAnalyseByPrelevParamProg($pgCmdPrelev, $pgProgLotParamAn);
                    if ($pgCmdAnalyse) {
                        if ($pgCmdAnalyse->getCodeStatut() == '0') {
                            $tabGroupes[$nbGroupes]['correct'] = $tabGroupes[$nbGroupes]['correct'] + 1;
                        } elseif ($pgCmdAnalyse->getCodeStatut() == '1') {
                            $tabGroupes[$nbGroupes]['warning'] = $tabGroupes[$nbGroupes]['warning'] + 1;
                        } else {
                            $tabGroupes[$nbGroupes]['erreur'] = $tabGroupes[$nbGroupes]['erreur'] + 1;
                        }
                        $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = $pgCmdAnalyse;
                        $tabParamAns[$nbParamAns]['unite'] = $pgCmdAnalyse->getCodeUnite();
                    } else {
                        $tabParamAns[$nbParamAns]['pgCmdAnalyse'] = null;
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

        return $this->render('AeagSqeBundle:SuiviPrelevements:lotPeriodeStationDemandeSuiviSaisirAna.html.twig', array(
                    'user' => $pgProgWebUser,
                    'lotan' => $pgProgLotAn,
                    'station' => $pgRefStationMesure,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'demande' => $pgCmdPrelev->getDemande(),
                    'cmdPrelev' => $pgCmdPrelev,
                    'cmdSuiviPrel' => $pgCmdSuiviPrel,
                    'groupes' => $tabGroupes));
    }

    public function lotPeriodeStationDemandeSuiviResultatAnaAction($prelevId = null, $periodeAnId = null, $stationId = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviNew');
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

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgLotAn = $pgProgLotPeriodeAn->getLotAn();
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);
        $nbErreurs = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotGrparAn = $pgProgLotPeriodeProg->getGrparAn();
            if ($pgProgLotGrparAn->getvalide() == 'O' and $pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
                // if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
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
                    $tabStatut['statut'] = 0;
                    $tabStatut['libelle'] = null;
                    $parametre = $pgProgLotParamAn->getCodeParametre()->getCodeparametre();
                    $inSitu = 2;
                    $ok = 0;
                    if (strlen($valeur) > 0) {

                        $tabStatut = $this->_controleVraisemblance($parametre, $valeur, $remarque, $unite, $inSitu, $tabStatut);

                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                        $pgProgUnitesPossiblesParam = $repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamByCodeParametreCodeUniteNatureFraction($pgProgLotParamAn->getCodeParametre()->getCodeParametre(), $unite, $pgSandreFraction);
                        if ($pgProgUnitesPossiblesParam) {
                            if (strlen($pgProgUnitesPossiblesParam->getValMax()) > 0) {
                                if ($valeur > $pgProgUnitesPossiblesParam->getValMax()) {
                                    $tabStatut['statut'] = 2;
                                    $tabStatut['libelle'] = 'Valeur doit être inferieure à ' . $pgProgUnitesPossiblesParam->getValMax();
                                }
                            }
                        }
                        $ok = $tabStatut['statut'];
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

                $emSqe->flush();

                $NbCmdMesureEnv = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelev($pgCmdPrelev);
                $NbCmdMesureEnvCorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '0');
                $NbCmdMesureEnvIncorrect = $repoPgCmdMesureEnv->getNbCmdMesureEnvByPrelevStatut($pgCmdPrelev, '1');
                $NbCmdAnalyseSitu = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelev($pgCmdPrelev);
                $NbCmdAnalyseSituCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '0');
                $NbCmdAnalyseSituIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseSituByPrelevStatut($pgCmdPrelev, '1');
                $NbCmdAnalyseAna = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelev($pgCmdPrelev);
                $NbCmdAnalyseAnaCorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '0');
                $NbCmdAnalyseAnaIncorrect = $repoPgCmdAnalyse->getNbCmdAnalyseAnaByPrelevStatut($pgCmdPrelev, '1');
                $NbCmdTot = $NbCmdMesureEnv + $NbCmdAnalyseSitu + $NbCmdAnalyseAna;
                $NbCmdTotSaisis = $NbCmdMesureEnvCorrect + $NbCmdMesureEnvIncorrect + $NbCmdAnalyseSituCorrect + $NbCmdAnalyseSituIncorrect + $NbCmdAnalyseAnaCorrect + $NbCmdAnalyseAnaIncorrect;
                if ($NbCmdTot == $NbCmdTotSaisis) {
                    $ok = $this->_controlesSpecifiques($pgCmdPrelev);
                } else {
                    $ok = 1;
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
                if ($ok == 0) {
                    $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R45');
                } else {
                    $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R10');
                }
                $pgCmdFichiersRps->setPhaseFichier($pgProgPhases);
                $emSqe->persist($pgCmdFichiersRps);
                $pgCmdPrelev->setFichierRps($pgCmdFichiersRps);

                if ($ok == 0) {
                    $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');
                } else {
                    $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M20');
                }
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
                $emSqe->persist($pgCmdPrelev);

                $emSqe->flush();
                // }
            }
        }

        //  return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabParamAns));
        //return new Response ('');

        if ($ok == 0) {
            return $this->redirect($this->generateUrl('AeagSqeBundle_suiviPrelevements_lot_periode_station_demande', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                                'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));
        } else {
            return $this->redirect($this->generateUrl('AeagSqeBundle_suiviPrelevements_lot_periode_station_demande_suivi_saisir_ana', array(
                                'prelevId' => $pgCmdPrelev->getId(),
                                'periodeAnId' => $pgProgLotPeriodeAn->getId(),
                                'stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
            )));
        }
    }

    public function lotPeriodeStationDemandeSuiviFichierDeposerAction($suiviPrelId = null, $periodeAnId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
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
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
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
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
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

    protected function _controleVraisemblance($parametre, $valeur, $remarque, $unite, $inSitu, $tabStatut) {


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
                $tabStatut['statut'] = 2;
                $tabStatut['libelle'] = 'Valeur renseignée et code remarque vide';
            }
        }

        if (!is_null($codeRq) && !is_null($mesure)) {
            if (!is_numeric($mesure) || !is_numeric($codeRq)) {
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
                $tabStatut['statut'] = 2;
                $tabStatut['libelle'] = 'Valeur = 0 impossible pour ce paramètre';
            }
        }

        // III.4 Valeurs < 0 (hors température de air 1409, potentiel REDOX 1330)
        if ($mesure < 0) {
            if ($codeParametre != 1409 && $codeParametre != 1330 && $codeParametre != 1420 && $codeParametre != 1429) {
                $tabStatut['statut'] = 2;
                $tabStatut['libelle'] = 'Valeur < 0 impossible pour ce paramètre';
            }
        }

        // III.5 Valeurs avec code remarque '> (3)' hors bactério (1147,1448,1449,1451,5479,6455); code remarque ="Trace (7)"
        $codeParamsBacterio = array(
            1447, 1448, 1449, 1451, 5479, 6455
        );
        if (($codeRq == 3 && !in_array($codeParametre, $codeParamsBacterio)) || $codeRq == 7) {
            $tabStatut['statut'] = 2;
            $tabStatut['libelle'] = 'Code Remarque > 3 ou == 7 impossible pour ce paramètre';
        }

        // III.6 1 < pH(1302) < 14
        if ($codeParametre == '1302') {
            $mPh = $mesure;
            if (!is_null($mPh)) {
                if ($mPh < 1 || $mPh > 14) {
                    $tabStatut['statut'] = 2;
                    $tabStatut['libelle'] = 'Le pH n\'est pas entre 1 et 14';
                }
            }
        }

        // III.12 Valeur de pourcentage hors 1312 oxygène (ex : matière sèche ou granulo) : non compris entre 0 et 100 si code unité = 243 ou 246
        if ($unite == '243' or $unite == '246') {
            if ($codeParametre != '1312') {
                if ($mesure < 0 or $mesure > 100) {
                    $tabStatut['statut'] = 2;
                    $tabStatut['libelle'] = 'Valeur pourcentage : pourcentage n\'est pas entre 0 et 100';
                }
            }
        }

        return $tabStatut;
    }

    protected function _controlesSpecifiques($pgCmdPrelev) {
        // Contrôles spécifiques  

        $okControles = 0;

        // III.7
        $okControles = $this->_modeleWeiss($pgCmdPrelev);

        // III.8 
        // $this->_balanceIonique($pgCmdPrelev);
//
//            // III.9
//            $this->_balanceIoniqueTds2($demandeId, $reponseId, $codePrelevement);
//
//            // III.10
//            $this->_ortophosphate($demandeId, $reponseId, $codePrelevement);
//
//            // III.11
//            $this->_ammonium($demandeId, $reponseId, $codePrelevement);
//
//            // III.12
//            $this->_pourcentageHorsOxygene($demandeId, $reponseId, $codePrelevement);
//
//            // III.13
//            $this->_sommeParametresDistincts($demandeId, $reponseId, $codePrelevement);
//
//            // III.14
//            $this->_controleVraisemblanceMacroPolluants($demandeId, $reponseId, $codePrelevement);
//
//
//
//
//
//            // III.15
//            $this->_detectionCodeRemarqueLot7($demandeId, $reponseId, $codePrelevement);
//
//            // III.16
//            $this->_detectionCodeRemarqueLot8($demandeId, $reponseId, $codePrelevement);
//
//            // III.17
//            $this->_controleLqAeag($pgCmdFichierRps, $codePrelevement);
//            
//    
        return $okControles;
    }

    // III.7 modèle  de WEISS : cohérence Teau, % O2, Concentration O2  sauf si Conductivité > 10 000
    protected function _modeleWeiss($pgCmdPrelev) {

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
                    $okWeiss = 1;
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
            1340 => $par1380Resultat,
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
                if ($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement) == 10) {
                    $countLq++;
                }
            }

            foreach ($cAnionParams as $idx => $cAnionParam) {
                if ($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement) == 10) {
                    $countLq++;
                }
            }

            if ($countLq >= (count($cAnionParams) + count($cCationParams))) {
                $okBalanceIonique = 2;
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
                    $okBalanceIonique = 2;
                    $tabStatut['statut'] = 2;
                    $tabStatut['libelle'] = 'Balance Ionique : Valeur non conforme';
                }
            }
        }
        return $okBalanceIonique;
    }

    // III.9 Comparaison Balance ionique / conductivité (Feret)
    protected function _balanceIoniqueTds2($demandeId, $reponseId, $codePrelevement) {
        $cCationParams = array(1374 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1374, $demandeId, $reponseId, $codePrelevement),
            1335 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement),
            1372 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1372, $demandeId, $reponseId, $codePrelevement),
            1367 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1367, $demandeId, $reponseId, $codePrelevement),
            1375 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1375, $demandeId, $reponseId, $codePrelevement)
        );

        $cAnionParams = array(1433 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement),
            1340 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1340, $demandeId, $reponseId, $codePrelevement),
            1338 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1338, $demandeId, $reponseId, $codePrelevement),
            1337 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1337, $demandeId, $reponseId, $codePrelevement),
            1327 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1327, $demandeId, $reponseId, $codePrelevement),
            1339 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1339, $demandeId, $reponseId, $codePrelevement)
        );

        $mConductivite = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1303, $demandeId, $reponseId, $codePrelevement);

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
                if ($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement) == 10) {
                    $countLq++;
                }
            }

            foreach ($cAnionParams as $idx => $cAnionParam) {
                if ($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement) == 10) {
                    $countLq++;
                }
            }

            if ($countLq >= (count($cAnionParams) + count($cCationParams))) {
                $this->_addLog('error', $demandeId, $reponseId, "Balance Ionique TDS 2 : Tous les dosages sont en LQ", $codePrelevement);
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
                    $this->_addLog('warning', $demandeId, $reponseId, "Balance Ionique TDS 2 : Réserve", $codePrelevement);
                } else if (abs($indVraiTds) >= 280) {
                    $this->_addLog('warning', $demandeId, $reponseId, "Balance Ionique TDS 2 : Valeur non conforme", $codePrelevement);
                }
            }
        }
    }

    // III.10 [PO4] (1433) en P < [P total](1350) 
    protected function _ortophosphate($demandeId, $reponseId, $codePrelevement) {
        $mPo4 = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement);
        $mP = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1350, $demandeId, $reponseId, $codePrelevement);

        if (!is_null($mPo4) && !is_null($mP)) {
            if (($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement) == 10) &&
                    ($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1350, $demandeId, $reponseId, $codePrelevement) == 10)) {
                $this->_addLog('error', $demandeId, $reponseId, "Ortophosphate : Tous les dosages sont en LQ", $codePrelevement);
            } else {
                $indP = $mPo4 / $mP;
                if (1 < $indP && $indP <= 1.25) {
                    $this->_addLog('warning', $demandeId, $reponseId, "Ortophosphate : Réserve", $codePrelevement);
                } else if ($indP > 1.25) {
                    $this->_addLog('error', $demandeId, $reponseId, "Ortophosphate : Valeur non conforme", $codePrelevement);
                }
            }
        }
    }

    // III.11 NH4 (1335) en N < Nkj (1319)
    protected function _ammonium($demandeId, $reponseId, $codePrelevement) {
        $mNh4 = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement);
        $mNkj = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1319, $demandeId, $reponseId, $codePrelevement);

        if (!is_null($mNh4) && !is_null($mNkj)) {
            if (($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement) == 10) &&
                    ($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1319, $demandeId, $reponseId, $codePrelevement) == 10)) {
                $this->_addLog('error', $demandeId, $reponseId, "Ammonium : tous les dosages sont en LQ", $codePrelevement);
            } else {
                $indP = $mNh4 / $mNkj;
                if (1 < $indP && $indP <= 1.25) {
                    $this->_addLog('warning', $demandeId, $reponseId, "Ammonium : Réserve", $codePrelevement);
                } else if ($indP > 1.25) {
                    $this->_addLog('error', $demandeId, $reponseId, "Ammonium : Valeur non conforme", $codePrelevement);
                }
            }
        }
    }

    // III.12 Valeur de pourcentage hors 1312 oxygène (ex : matière sèche ou granulo) : non compris entre 0 et 100 si code unité = 243 ou 246
    protected function _pourcentageHorsOxygene($demandeId, $reponseId, $codePrelevement) {
        $tabMesures = array(243 => $this->repoPgTmpValidEdilabo->getMesureByCodeUnite(243, $demandeId, $reponseId, $codePrelevement, 1312),
            246 => $this->repoPgTmpValidEdilabo->getMesureByCodeUnite(246, $demandeId, $reponseId, $codePrelevement, 1312));

        foreach ($tabMesures as $tabMesure) {
            if (!is_null($tabMesure) && count($tabMesure) > 0) {
                foreach ($tabMesure as $mesure) {
                    if ($mesure > 100 || $mesure < 0) {
                        $this->_addLog('error', $demandeId, $reponseId, "Valeur pourcentage : pourcentage n'est pas entre 0 et 100", $mesure);
                    }
                }
            }
        }
    }

    // III.13 Somme des paramètres distincts (1200+1201+1202+1203=5537; 1178+1179 = 1743; 1144+1146+ 1147+1148 = 7146; 2925 + 1292 =  1780) à  (+/- 20%)
    protected function _sommeParametresDistincts($demandeId, $reponseId, $codePrelevement) {
        $sommeParams = array(0 => array(1200 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1200, $demandeId, $reponseId, $codePrelevement),
                1201 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1201, $demandeId, $reponseId, $codePrelevement),
                1202 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1202, $demandeId, $reponseId, $codePrelevement),
                1203 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1203, $demandeId, $reponseId, $codePrelevement)
            ),
            1 => array(1178 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1178, $demandeId, $reponseId, $codePrelevement),
                1179 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1179, $demandeId, $reponseId, $codePrelevement)
            ),
            2 => array(1144 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1144, $demandeId, $reponseId, $codePrelevement),
                1146 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1146, $demandeId, $reponseId, $codePrelevement),
                1147 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1147, $demandeId, $reponseId, $codePrelevement),
                1148 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1148, $demandeId, $reponseId, $codePrelevement)
            ),
            3 => array(2925 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(2925, $demandeId, $reponseId, $codePrelevement),
                1292 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1292, $demandeId, $reponseId, $codePrelevement)
            ),
        );

        $resultParams = array(0 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(5537, $demandeId, $reponseId, $codePrelevement),
            1 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1743, $demandeId, $reponseId, $codePrelevement),
            2 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(7146, $demandeId, $reponseId, $codePrelevement),
            3 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1780, $demandeId, $reponseId, $codePrelevement));

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
                    $this->_addLog('error', $demandeId, $reponseId, "Somme Parametres Distincts : La somme des paramètres ne correspond pas au paramètre global " . $params[$idx], $codePrelevement, $somme);
                }
            }
        }
    }

    //III.14 Contrôle de vraisemblance par parmètres macropolluants : Résultat d’analyse< Valeur max de la base x 2 
    protected function _controleVraisemblanceMacroPolluants($demandeId, $reponseId, $codePrelevement) {
        $codeSandreMacroPolluants = $this->codeSandreMacroPolluants;
        foreach ($codeSandreMacroPolluants as $codeSandreMacroPolluant) {
            $mesure = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre($codeSandreMacroPolluant[0], $demandeId, $reponseId, $codePrelevement);
            if (!is_null($mesure)) {
                if ($mesure > $codeSandreMacroPolluant[1]) {
                    $this->_addLog('warning', $demandeId, $reponseId, "Controle Vraisemblance Macro Polluants : Le résultat est supérieur à la valeur attendue pour le paramètre " . $codeSandreMacroPolluant[0], $codePrelevement, $mesure);
                }
            }
        }
    }

    //III.15 Détection Code remarque Lot 7 (Etat chimique, Substance pertinentes, Complément AEAG, PSEE) :  % de détection différent de 100 (= recherche d'absence de code remarque) suivant liste ref-doc
    protected function _detectionCodeRemarqueLot7($demandeId, $reponseId, $codePrelevement) {

        // Vérification marché Demande = marché Aeag
        $demandeAeag = $this->repoPgCmdDemande->isPgCmdDemandesMarcheAeag($demandeId);
        if (count($demandeAeag) > 0) {
            // Récupération des codes Parametre de la RAI
            $codesParams = $this->repoPgTmpValidEdilabo->getCodesParametres($demandeId, $reponseId, $codePrelevement);
            $nbCodeRqTot = 0;
            $nbCodeRq10 = 0;
            foreach ($codesParams as $codeParam) {
                if (in_array($codeParam, $this->detectionCodeRemarqueComplet)) {
                    $codeRq = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($codeParam, $demandeId, $reponseId, $codePrelevement);
                    if ($codeRq == 10) {
                        $nbCodeRq10++;
                    }
                    if ($codeRq != 0) {
                        $nbCodeRqTot++;
                    }
                }
            }

            if ($nbCodeRqTot <= $nbCodeRq10) {
                $this->_addLog('error', $demandeId, $reponseId, "Detection Code Remarque Lot 7 : Tous les codes remarques sont à 10", $codePrelevement, $nomFichier);
            }
        }
    }

    //III.16
    protected function _detectionCodeRemarqueLot8($demandeId, $reponseId, $codePrelevement) {

        // Vérification marché Demande = marché Aeag
        $demandeAeag = $this->repoPgCmdDemande->isPgCmdDemandesMarcheAeag($demandeId);

        if (count($demandeAeag) > 0) {
            // Récupération des codes Parametre de la RAI
            $codesParams = $this->repoPgTmpValidEdilabo->getCodesParametres($demandeId, $reponseId, $codePrelevement);
            $nbTotalCodeRq = 0;
            $nbCodeRq10 = 0;
            foreach ($codesParams as $codeParam) {
                if (in_array($codeParam, $this->detectionCodeRemarqueMoitie)) {
                    $codeRq = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($codeParam, $demandeId, $reponseId, $codePrelevement);
                    if ($codeRq == 10) {
                        $nbCodeRq10++;
                    }
                    if ($codeRq !== 0) {
                        $nbTotalCodeRq++;
                    }
                }
            }

            if ($nbCodeRq10 < ($nbTotalCodeRq / 2)) {
                $this->_addLog('warning', $demandeId, $reponseId, "Detection Code Remarque Lot 8 : La majorité des codes remarque est à 1", $codePrelevement, $nomFichier);
            }
        }
    }

    // III.17
    protected function _controleLqAeag($pgCmdFichierRps, $codePrelevement) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->findBy(array('demandeId' => $demandeId, 'fichierRpsId' => $reponseId, 'codePrelevement' => $codePrelevement));
        if ($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getMarche()->getTypeMarche() == 'MOA') {
            foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
                if (!is_null($pgTmpValidEdilabo->getLqM())) {
                    $lq = $this->repoPgProgLotLqParam->isValidLq($pgCmdFichierRps->getDemande()->getLotan()->getLot(), $pgTmpValidEdilabo->getCodeParametre(), $pgTmpValidEdilabo->getCodeFraction(), $pgTmpValidEdilabo->getLqM());
                    if (count($lq) == 0) {
                        $this->_addLog('warning', $demandeId, $reponseId, "Controle Lq AEAG : Lq supérieure à la valeur prévue", $codePrelevement, $pgTmpValidEdilabo->getLqM());
                    }
                }
            }
        }
    }

    protected function _updatePhase($pgCmdFichierRps, $phase, $phaseExclu = false) {

        $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase($phase);
        if (!$phaseExclu) {
            $pgCmdFichierRps->setPhaseFichier($pgProgPhases);
            $this->emSqe->persist($pgCmdFichierRps);
        }

        $pgProgSuiviPhases = new \Aeag\SqeBundle\Entity\PgProgSuiviPhases;
        $pgProgSuiviPhases->setTypeObjet('RPS');
        $pgProgSuiviPhases->setObjId($pgCmdFichierRps->getId());
        $pgProgSuiviPhases->setDatePhase(new \DateTime());
        $pgProgSuiviPhases->setPhase($pgProgPhases);
        $this->emSqe->persist($pgProgSuiviPhases);

        $this->emSqe->flush();
    }

}
