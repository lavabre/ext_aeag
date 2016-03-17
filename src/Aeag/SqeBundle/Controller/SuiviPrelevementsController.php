<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\SqeBundle\Entity\PgCmdSuiviPrel;
use Aeag\SqeBundle\Entity\PgCmdFichiersRps;
use Aeag\SqeBundle\Form\PgCmdSuiviPrelMajType;
use Aeag\SqeBundle\Form\PgCmdSuiviPrelVoirType;
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
                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    $tabParamAns[$nbParamAns]['paramAn'] = $pgProgLotParamAn;
                    if ($pgProgLotParamAn->getCodeUnite()) {
                        $pgSandreUnite = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($pgProgLotParamAn->getCodeUnite());
                        $tabParamAns[$nbParamAns]['unite'] = $pgSandreUnite;
                        $tabParamAns[$nbParamAns]['unites'] = array();
                    } else {
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
                $response = 'La taille (' .   $size    . ' octets' . ') du fichier téléchargé excède la taille de upload_max_filesize dans php.ini.';
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

}
