<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\SqeBundle\Entity\PgCmdSuiviPrel;
use Aeag\SqeBundle\Form\PgCmdSuiviPrelType;
use Aeag\AeagBundle\Controller\AeagController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class SuiviPrelevementsController extends Controller {

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
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->getPgCmdDemandeById($cmdDemandeId);
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);
        $pgProgPeriode = $pgProgLotPeriodeAn->getPeriode();

        $tabDemande = array();

        if ($pgCmdDemande) {
            $pgProgLotAn = $pgCmdDemande->getLotan();
            $tabDemande['cmdDemande'] = $pgCmdDemande;
            $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
            $tabCmdPrelevs = array();
            if ($pgCmdPrelevs) {
                $i = 0;
                foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                    if ($pgCmdPrelev->getStation()->getOuvFoncId() == $stationId and $pgCmdPrelev->getPeriode()->getId() == $pgProgPeriode->getId()) {
                        $tabCmdPrelevs[$i]['cmdPrelev'] = $pgCmdPrelev;
                        $tabCmdPrelevs[$i]['maj'] = 'N';
                        $tabCmdPrelevs[$i]['saisie'] = 'N';
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
                                if ($pgProgWebUser->getPrestataire()) {
                                    if ($pgProgWebUser->getPrestataire()->getAdrCorId() == $pgCmdSuiviPrel->getUser()->getPrestataire()->getAdrCorId()) {
                                        $tabSuiviPrels[$j]['maj'] = 'O';
                                        $tabCmdPrelevs[$i]['maj'] = 'O';
                                        $tabDemande[$i]['maj'] = 'O';
                                         if ($pgCmdSuiviPrel->getStatutPrel() == 'F') {
                                            $tabCmdPrelevs[$i]['saisie'] = 'O';
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



//        \Symfony\Component\VarDumper\VarDumper::dump($tabDemande);
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
        $form = $this->createForm(new PgCmdSuiviPrelType(), $pgCmdSuiviPrel);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $pgCmdSuiviPrel->setPrelev($pgCmdPrelev);
            $pgCmdSuiviPrel->setUser($pgProgWebUser);
            $datePrel = $pgCmdSuiviPrel->getDatePrel();
            $emSqe->persist($pgCmdSuiviPrel);
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
        $session->set('fonction', 'lotPeriodeStationDemandeSuiviNew');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $form = $this->createForm(new PgCmdSuiviPrelType(), $pgCmdSuiviPrel);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $datePrel = $pgCmdSuiviPrel->getDatePrel();
            $emSqe->persist($pgCmdSuiviPrel);
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
        $emSqe->remove($pgCmdSuiviPrel);
        $emSqe->flush();

        $session->getFlashBag()->add('notice-success', 'le suivi du prélèvement du   : ' . $datePrel->format('d/m/Y') . ' a été supprimé !');

        return $this->redirect($this->generateUrl('AeagSqeBundle_suiviPrelevements_lot_periode_station_demande', array('stationId' => $pgCmdPrelev->getStation()->getOuvFoncId(),
                            'periodeAnId' => $periodeAnId,
                            'cmdDemandeId' => $pgCmdPrelev->getDemande()->getId())));


//          \Symfony\Component\VarDumper\VarDumper::dump($tabDemande);
//        return new Response ('');
    }

    public function lotPeriodeStationDemandeSuiviSaisirAction($prelevId = null, $periodeAnId = null, $stationId = null, Request $request) {

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
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');

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
            if ($pgProgLotGrparAn->getvalide() == 'O') {
                $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
                // if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $tabGroupes[$nbGroupes]['grparAn'] = $pgProgLotGrparAn;
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                $tabParamAns = array();
                $nbParamAns = 0;
                foreach( $pgProgLotParamAns as  $pgProgLotParamAn){
                    $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                    if ($pgProgLotParamAn->getCodeUnite()){
                        $pgSandreUnite = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($pgProgLotParamAn->getCodeUnite());
                        $tabParamAns[$nbParamAns]['unite'] = $pgSandreUnite;
                        $tabParamAns[$nbParamAns]['unites'] = array();
                    }else{
                        $tabParamAns[$nbParamAns]['unite'] = null;
                        $tabParamAns[$nbParamAns]['unites'] = $pgProgLotParamAn->getCodeParametre()->getCodeUnite();
                    }
                    $nbParamAns++;
                }
                $tabGroupes[$nbGroupes]['paramAns'] = $tabParamAns;
                $nbGroupes++;
                // }
            }
        }

        return $this->render('AeagSqeBundle:SuiviPrelevements:lotPeriodeStationDemandeSuiviSaisir.html.twig', array(
                    'user' => $pgProgWebUser,
                    'lotan' => $pgProgLotAn,
                    'station' => $pgRefStationMesure,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'demande' => $pgCmdPrelev->getDemande(),
                    'cmdPrelev' => $pgCmdPrelev,
                    'groupes' => $tabGroupes));


//          \Symfony\Component\VarDumper\VarDumper::dump($tabGroupes);
//        return new Response ('');
    }

}
