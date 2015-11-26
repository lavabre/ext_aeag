<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Aeag\SqeBundle\Entity\PgProgLotPeriodeAn;
use Aeag\SqeBundle\Entity\PgProgLotPeriodeProg;
use Aeag\AeagBundle\Controller\AeagController;

class ProgrammationBilanController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationBilan');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');
        $annee = $session->get('critAnnee');

        if (!empty($_POST['suivant'])) {
            $suivant = $_POST['suivant'];
        } else {
            $suivant = 'bilan';
        }

        if (is_object($user)) {

            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);

            if ($action == 'V' && !$this->get('security.authorization_checker')->isGranted('ROLE_SQE')) {
                return $this->render('AeagSqeBundle:Default:interdit.html.twig');
            }
            if ($action == 'P' && !$this->get('security.authorization_checker')->isGranted('ROLE_PROGSQE')) {
                return $this->render('AeagSqeBundle:Default:interdit.html.twig');
            }
            if ($action != 'P' && $action != 'V') {
                return $this->render('AeagSqeBundle:Default:interdit.html.twig');
            }
        }

        if ($suivant == 'station') {
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_stations', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        } elseif ($suivant == 'groupe') {
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_groupes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        } elseif ($suivant == 'periode') {
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_periodes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        }

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');


        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);

        $tabControle = ProgrammationBilanController::controleProgrammationAction($pgProgLotAnId, $emSqe, $session);

        if ($action == 'P' and $maj != 'V') {
            if ($tabControle['station']['ok'] and $tabControle['groupe']['ok'] and $tabControle['periode']['ok']) {
                if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE')) {
                    $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P25');
                } else {
                    $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P20');
                }
            } else {
                if ($tabControle['station']['ok'] or $tabControle['groupe']['ok'] or $tabControle['periode']['ok']) {
                    $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P15');
                } else {
                    $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P10');
                }
            }
            $pgProgLotAn->setPhase($pgProgPhase);
            $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('UPD');
            $pgProgLotAn->setCodeStatut($pgProgStatut);
            $now = date('Y-m-d H:i');
            $now = new \DateTime($now);
            $pgProgLotAn->setDateModif($now);
            $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByLoginPassword($user->getUsername(), $user->getPassword());
            if ($pgProgWebuser) {
                $pgProgLotAn->setUtilModif($pgProgWebuser);
            }
            $emSqe->persist($pgProgLotAn);
            $emSqe->flush();
        }

        $tabMessage = array();


        return $this->render('AeagSqeBundle:Programmation:Bilan\index.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'campagne' => $annee,
                    'lotan' => $pgProgLotAn,
                    'controle' => $tabControle,
                    'messages' => $tabMessage));
    }

    public function stationAction() {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationBilan');
        $session->set('fonction', 'station');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);

        $tabStations = array();
        $i = 0;
        foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
            $tabStations[$i]["station"] = $pgProgLotStationAn;
            $pgProglotPeriodeProgsByStation = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAn);
            $tabProgs = array();
            $tabProgCompls = array();
            $tabProgIgnores = array();
            if (count($pgProglotPeriodeProgsByStation) > 0) {
                $tabStations[$i]["renseigner"] = "O";
                $l = 0;
                foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
                    $tabProgs[$l]['groupe'] = $pgProgLotGrparAn->getGrparRef()->getCodeGrp();
                    $tabProgs[$l]['periode'] = array();
                    $tabProgs[$l]['nb'] = 0;
                    $tabProgCompls[$l]['groupe'] = $pgProgLotGrparAn->getGrparRef()->getCodeGrp();
                    $tabProgCompls[$l]['periode'] = array();
                    $tabProgCompls[$l]['nb'] = 0;
                    $tabProgIgnores[$l]['groupe'] = $pgProgLotGrparAn->getGrparRef()->getCodeGrp();
                    $tabProgIgnores[$l]['periode'] = array();
                    $tabProgIgnores[$l]['nb'] = 0;
                    $l++;
                }
                // return new Response ('nb : ' . count($tabProgs));
                foreach ($pgProglotPeriodeProgsByStation as $pgProglotPeriodeProg) {
                    if ($pgProgLot->getDelaiPrel()) {
                        $dateFin = clone($pgProglotPeriodeProg->getPeriodan()->getPeriode()->getDateDeb());
                        $delai = $pgProgLot->getDelaiPrel();
                        $dateFin->add(new \DateInterval('P' . $delai . 'D'));
                    } else {
                        $dateFin = $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getDateFin();
                    }
                    $pgProglotPeriodeProg->getPeriodan()->getPeriode()->setDateFin($dateFin);
                    if (!$pgProglotPeriodeProg->getPprogCompl()) {
                        for ($l = 0; $l < count($tabProgs); $l++) {
                            if ($tabProgs[$l]['groupe'] == $pgProglotPeriodeProg->getGrparAn()->getGrparRef()->getCodeGrp()) {
                                $trouve = false;
                                if (count($tabProgs[$l]['periode'])) {
                                    for ($j = 0; $j < count($tabProgs[$l]['periode']); $j++) {
                                        if ($tabProgs[$l]['periode'][$j]->getPeriodan()->getPeriode()->getNumPeriode() == $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getNumPeriode()) {
                                            $trouve = true;
                                        }
                                    }
                                }
                                if (!$trouve) {
                                    $j = count($tabProgs[$l]['periode']);
                                    $tabProgs[$l]['periode'][$j] = $pgProglotPeriodeProg;
                                    $tabProgs[$l]['nb'] = $tabProgs[$l]['nb'] + 1;
                                }
                            }
                        };
                    } else {
                        if ($pgProglotPeriodeProg->getStatut() == 'C') {
                            for ($l = 0; $l < count($tabProgCompls); $l++) {
                                if ($tabProgCompls[$l]['groupe'] == $pgProglotPeriodeProg->getGrparAn()->getGrparRef()->getCodeGrp()) {
                                    $trouve = false;
                                    if (count($tabProgCompls[$l]['periode'])) {
                                        for ($j = 0; $j < count($tabProgCompls[$l]['periode']); $j++) {
                                            if ($tabProgCompls[$l]['periode'][$j]->getPeriodan()->getPeriode()->getNumPeriode() == $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getNumPeriode()) {
                                                $trouve = true;
                                            }
                                        }
                                    }
                                    if (!$trouve) {
                                        $j = count($tabProgCompls[$l]['periode']);
                                        $tabProgCompls[$l]['periode'][$j] = $pgProglotPeriodeProg;
                                        $tabProgCompls[$l]['nb'] = $tabProgCompls[$l]['nb'] + 1;
                                    }
                                }
                            }
                        } else {
                            for ($l = 0; $l < count($tabProgIgnores); $l++) {
                                if ($tabProgIgnores[$l]['groupe'] == $pgProglotPeriodeProg->getGrparAn()->getGrparRef()->getCodeGrp()) {
                                    $trouve = false;
                                    if (count($tabProgIgnores[$l]['periode'])) {
                                        for ($j = 0; $j < count($tabProgIgnores[$l]['periode']); $j++) {
                                            if ($tabProgIgnores[$l]['periode'][$j]->getPeriodan()->getPeriode()->getNumPeriode() == $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getNumPeriode()) {
                                                $trouve = true;
                                            }
                                        }
                                    }
                                    if (!$trouve) {
                                        $j = count($tabProgIgnores[$l]['periode']);
                                         $tabProgIgnores[$l]['periode'][$j] = $pgProglotPeriodeProg;
                                        $tabProgIgnores[$l]['nb'] = $tabProgIgnores[$l]['nb'] + 1;
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $tabStations[$i]["renseigner"] = "N";
            }
            $tabStations[$i]["progs"] = $tabProgs;
            $tabStations[$i]["progCompls"] = $tabProgCompls;
            $tabStations[$i]["progIgnores"] = $tabProgIgnores;
            $i++;
        }
        usort($tabStations, create_function('$a,$b', 'return $a[\'station\']->getStation()->getCode()-$b[\'station\']->getStation()->getCode();'));
        usort($pgProgLotGrparAns, create_function('$a,$b', 'return $a->getGrparRef()->getCodeGrp()-$b->getGrparRef()->getCodeGrp();'));

        return $this->render('AeagSqeBundle:Programmation:Bilan\station.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'campagne' => $annee,
                    'lotan' => $pgProgLotAn,
                    'grparAns' => $pgProgLotGrparAns,
                    'stationAns' => $tabStations));
    }

    public function stationGroupeAction($stationAnId = null, $grparAnId = null) {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationBilan');
        $session->set('fonction', 'stationGroupe');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($_GET['lotan']);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgLotGrparAn = $repoPgProgLotGrparAn->getPgProgLotGrparAnById($grparAnId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnById($stationAnId);

        $tabPeriodes = array();
        $tabPeriodes["station"] = $pgProgLotStationAn;
        $tabPeriodes["groupe"] = $pgProgLotGrparAn;

        $tabZonevertes = array();
        $i = 0;
        foreach ($pgProgLotGrparAn->getgrparRef()->getCodeZoneVert() as $pgSandreZoneVerticaleProspectee) {
            $tabZonevertes[$i] = $pgSandreZoneVerticaleProspectee;
            $i++;
        }
        $tabPeriodes["zoneVertes"] = $tabZonevertes;

        $pgProglotPeriodeProgsByStationGroupe = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnGrparAn($pgProgLotStationAn, $pgProgLotGrparAn);
        $tabProgs = array();
        $tabProgCompls = array();
        if (count($pgProglotPeriodeProgsByStationGroupe) > 0) {
            $tabPeriodes["renseigner"] = "O";
            $j = 0;
            $k = 0;
            $delai = $pgProgLot->getDelaiPrel();
            foreach ($pgProglotPeriodeProgsByStationGroupe as $pgProglotPeriodeProg) {
                if ($delai) {
                    $dateFin = clone($pgProglotPeriodeProg->getPeriodan()->getPeriode()->getDateDeb());
                     $dateFin->add(new \DateInterval('P' . $delai . 'D'));
                } else {
                    $dateFin = $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getDateFin();
                }
                $pgProglotPeriodeProg->getPeriodan()->getPeriode()->setDateFin($dateFin);
                if (!$pgProglotPeriodeProg->getPprogCompl()) {
                    $trouve = false;
                    for ($l = 0; $l < count($tabProgs); $l++) {
                        if ($tabProgs[$l]['periode']) {
                            if ($tabProgs[$l]['periode']->getNumPeriode() == $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getNumPeriode()) {
                                $trouve = true;
                            }
                        }
                    }
                    if (!$trouve) {
                        $tabProgs[$j]['periode'] = $pgProglotPeriodeProg->getPeriodan()->getPeriode();
                        $tabProgs[$j]['statut'] = $pgProglotPeriodeProg->getPeriodan()->getcodeStatut()->getCodeStatut();
                        $j++;
                    }
                } else {
                    $trouve = false;
                    for ($l = 0; $l < count($tabProgCompls); $l++) {
                        if ($tabProgCompls[$l]['periode']) {
                            if ($tabProgCompls[$l]['periode']->getNumPeriode() == $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getNumPeriode()) {
                                $trouve = true;
                            }
                        }
                    }
                    if (!$trouve) {
                        $tabProgCompls[$k]['periode'] = $pgProglotPeriodeProg->getPeriodan()->getPeriode();
                        $tabProgCompls[$k]['statut'] = $pgProglotPeriodeProg->getPeriodan()->getcodeStatut()->getCodeStatut();
                        $k++;
                    }
                }
            }
        } else {
            $tabPeriodes["renseigner"] = "N";
        }
        if (count($tabProgs)) {
            usort($tabProgs, create_function('$a,$b', 'return $a[\'periode\']->getNumPeriode()-$b[\'periode\']->getNumPeriode();'));
        }
        if (count($tabProgCompls)) {
            usort($tabProgCompls, create_function('$a,$b', 'return $a[\'periode\']->getNumPeriode()-$b[\'periode\']->getNumPeriode();'));
        }
        $tabPeriodes["progs"] = $tabProgs;
        $tabPeriodes["progCompls"] = $tabProgCompls;

        return $this->render('AeagSqeBundle:Programmation:Bilan\stationGroupe.html.twig', array(
                    'periodeAn' => $tabPeriodes));
    }

    public function prestataireAction() {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationBilan');
        $session->set('fonction', 'prestataire');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotPresta = $emSqe->getRepository('AeagSqeBundle:PgProgLotPresta');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $tabPrestaDftAll = array();
        $i = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $prestatairesByGrparan = $repoPgProgLotParamAn->getPrestatairesByGrparan($pgProgLotGrparAn);
            // prestataire programmé
            foreach ($prestatairesByGrparan as $prestataireByGrparan) {

                foreach ($prestataireByGrparan as $presta) {
                    $tabPrestaDftAll[$i]['type'] = 'N';
                    $tabPrestaDftAll[$i]['presta'] = $presta;
                    $i++;
                }
            }



            // prestataire complémentaire
            $pgProglotPeriodeProgsByGrparAn = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAn($pgProgLotGrparAn);
            foreach ($pgProglotPeriodeProgsByGrparAn as $pgProglotPeriodeProg) {
                if ($pgProglotPeriodeProg->getpprogCompl()) {
                    $pgProgLotGrparAnAutre = $pgProglotPeriodeProg->getpprogCompl()->getGrparAn();
                    $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAn($pgProgLotGrparAnAutre);
                    foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                        if ($pgProgLotPeriodeProgAutre->getGrparAn()->getGrparRef()->getId() == $pgProgLotGrparAn->getGrparRef()->getId()) {
                            $prestatairesByGrparan = $repoPgProgLotParamAn->getPrestatairesByGrparan($pgProgLotGrparAnAutre);
                            foreach ($prestatairesByGrparan as $prestataireByGrparan) {

                                foreach ($prestataireByGrparan as $presta) {
                                    $tabPrestaDftAll[$i]['type'] = $pgProglotPeriodeProg->getStatut();
                                    $tabPrestaDftAll[$i]['presta'] = $presta;
                                    $i++;
                                }
                            }
                        }
                    }
                }
            }
        }

        $tabPrestas = array();
        $k = 0;
        for ($i = 0; $i < count($tabPrestaDftAll); $i++) {
            $trouve = false;
            for ($j = 0; $j < count($tabPrestas); $j++) {
                if ($tabPrestaDftAll[$i]['presta'] == $tabPrestas[$j]['presta']) {
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                $tabPrestas[$k]['presta'] = $tabPrestaDftAll[$i]['presta'];
                $tabPrestas[$k]['type'] = $tabPrestaDftAll[$i]['type'];
                $k++;
            }
        }

        $tabMessage = array();
        $tabPrestataires = array();
        $i = 0;

        for ($k = 0; $k < count($tabPrestas); $k++) {
            $prestataire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($tabPrestas[$k]["presta"]);
            $pgProgLotPresta = $repoPgProgLotPresta->getPgProgLotPrestaByLotPresta($pgProgLot, $prestataire);
            $tabPrestataires[$i]["prestataire"] = $prestataire;
            $tabPrestataires[$i]["type"] = $tabPrestas[$k]["type"];
            $tabPrestataires[$i]["renseigner"] = "N";
            if ($pgProgLotPresta) {
                if (count($pgProgLotPresta) == 1) {
                    $tabPrestataires[$i]["typePresta"] = $pgProgLotPresta[0]->getTypePresta();
                } else {
                    $tabPrestataires[$i]["typePresta"] = "PL";
                }
            } else {
                $tabPrestataires[$i]["typePresta"] = null;
            }
            $j = 0;
            $tabGroupes = array();
            foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
                $tabGroupes[$j]["groupe"] = $pgProgLotGrparAn;
                if ($pgProgLotGrparAn->getPrestaDft()) {
                    $pgProgLotPresta = $repoPgProgLotPresta->getPgProgLotPrestaByLotPresta($pgProgLot, $pgProgLotGrparAn->getPrestaDft());
                    if ($pgProgLotPresta) {
                        if (count($pgProgLotPresta) == 1) {
                            $tabGroupes[$j]["typePresta"] = $pgProgLotPresta[0]->getTypePresta();
                        } else {
                            $tabGroupes[$j]["typePresta"] = "PL";
                        }
                    } else {
                        $tabGroupes[$j]["typePresta"] = null;
                    }
                } else {
                    $tabGroupes[$j]["typePresta"] = null;
                }
                $tabGroupes[$j]["renseigner"] = "N";
                $pgProglotparamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                $ok = false;
                foreach ($pgProglotparamAns as $pgProglotparamAn) {
                    if ($pgProglotparamAn->getPrestataire()->getAdrCorId() == $tabPrestas[$k]["presta"]) {
                        $ok = true;
                        break;
                    }
                }
                if ($ok) {
                    $tabPrestataires[$i]["renseigner"] = "O";
                    $tabGroupes[$j]["renseigner"] = "O";
                    $is = 0;
                    $ip = 0;
                    $ipc = 0;
                    $ipi = 0;
                    $tabStations = array();
                    $tabPeriodes = array();
                    $tabPeriodeCompls = array();
                    $tabPeriodeIgnores = array();
                    $pgProglotPeriodeProgsByGrparAn = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAn($pgProgLotGrparAn);
                    foreach ($pgProglotPeriodeProgsByGrparAn as $pgProglotPeriodeProg) {
                        if ($pgProglotPeriodeProg->getPeriodan()->getCodeStatut()->getCodeStatut() != 'INV') {
                            if (!$pgProglotPeriodeProg->getPprogCompl()) {
//                            $trouve = false;
//                            for ($l = 0; $l < count($tabStations); $l++) {
//                                if ($tabStations[$l] == $pgProglotPeriodeProg->getStationAn()->getStation()->getouvFoncId()) {
//                                    $trouve = true;
//                                    break;
//                                }
//                            }
//                            if (!$trouve) {
//                                if ($tabPrestataires[$i]["type"] == 'C') {
//                                    $trouve = false;
//                                    foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
//                                        if ($pgProgLotStationAn->getStation()->getOuvFoncid() == $pgProglotPeriodeProg->getStationAn()->getStation()->getOuvFoncid()) {
//                                            $trouve = true;
//                                            break;
//                                        }
//                                    }
//                                } else {
//                                    $trouve = true;
//                                }
//                                if ($trouve) {
//                                    $tabStations[$is] = $pgProglotPeriodeProg->getStationAn()->getStation()->getouvFoncId();
//                                    $is++;
//                                }
//                            }
                                $trouve = false;
//                                for ($l = 0; $l < count($tabPeriodes); $l++) {
//                                    if ($tabPeriodes[$l] == $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getId()) {
//                                        $trouve = true;
//                                        break;
//                                    }
//                                }
                                if (!$trouve) {
                                    $tabPeriodes[$ip] = $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getId();
                                    $ip++;
                                }
                            } else {
                                if ($pgProglotPeriodeProg->getStatut() == 'C') {
                                    $trouve = false;
//                                    for ($l = 0; $l < count($tabPeriodeCompls); $l++) {
//                                        if ($tabPeriodeCompls[$l] == $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getId()) {
//                                            $trouve = true;
//                                            break;
//                                        }
//                                    }
                                    if (!$trouve) {
                                        $tabPeriodeCompls[$ipc] = $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getId();
                                        $ipc++;
                                    }
                                } else {
                                    $trouve = false;
//                                    for ($l = 0; $l < count($tabPeriodeIgnores); $l++) {
//                                        if ($tabPeriodeIgnores[$l] == $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getId()) {
//                                            $trouve = true;
//                                            break;
//                                        }
//                                    }
                                    if (!$trouve) {
                                        $tabPeriodeIgnores[$ipi] = $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getId();
                                        $ipi++;
                                    }
                                }
                            }
                        }
                    }
//                    $tabGroupes[$j]["stations"] = count($tabStations);
                    $tabGroupes[$j]["periodes"] = count($tabPeriodes);
                    $tabGroupes[$j]["periodeCompls"] = count($tabPeriodeCompls);
                    $tabGroupes[$j]["periodeIgnores"] = count($tabPeriodeIgnores);
                    // print_r ('station  : ' . $tabProgs[$l]["stations"]->getStation()->getNumero() . ' nb  : ' . $j );
                } else {
                    $pgProglotPeriodeProgsByGrparAn = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAn($pgProgLotGrparAn);
                    foreach ($pgProglotPeriodeProgsByGrparAn as $pgProglotPeriodeProg) {
                        if ($pgProglotPeriodeProg->getPeriodan()->getCodeStatut()->getCodeStatut() != 'INV') {
                            if ($pgProglotPeriodeProg->getpprogCompl()) {
                                $pgProgLotGrparAnAutre = $pgProglotPeriodeProg->getpprogCompl()->getGrparAn();
                                $tabGroupes[$j]["groupe"] = $pgProgLotGrparAnAutre;
                                $pgProgLotPresta = $repoPgProgLotPresta->getPgProgLotPrestaByLotPresta($pgProgLot, $pgProgLotGrparAnAutre->getPrestaDft());
                                if ($pgProgLotPresta) {
                                    if (count($pgProgLotPresta) == 1) {
                                        $tabGroupes[$j]["typePresta"] = $pgProgLotPresta[0]->getTypePresta();
                                    } else {
                                        $tabGroupes[$j]["typePresta"] = "PL";
                                    }
                                } else {
                                    $tabGroupes[$j]["typePresta"] = null;
                                }
                                $tabGroupes[$j]["renseigner"] = "N";
                                $pgProglotparamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAnAutre);
                                foreach ($pgProglotparamAns as $pgProglotparamAn) {
                                    $ok = false;
                                    if ($pgProglotparamAn->getPrestataire()->getAdrCorId() == $tabPrestas[$k]["presta"]) {
                                        $ok = true;
                                        break;
                                    }
                                }
                                if ($ok) {
                                    $tabPrestataires[$i]["renseigner"] = "O";
                                    $tabGroupes[$j]["renseigner"] = "O";
                                    $is = 0;
                                    $ip = 0;
                                    $ipc = 0;
                                    $ipi = 0;
                                    $tabStations = array();
                                    $tabPeriodes = array();
                                    $tabPeriodeCompls = array();
                                    $tabPeriodeIgnores = array();
                                    $tabStations = array();
                                    $tabPeriodes = array();
                                    $pgProglotPeriodeProgsByGrparAn = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAn($pgProgLotGrparAn);
                                    foreach ($pgProglotPeriodeProgsByGrparAn as $pgProglotPeriodeProgByGrparAn) {
                                        if ($pgProglotPeriodeProgByGrparAn->getPeriodan()->getCodeStatut()->getCodeStatut() != 'INV') {
                                            if ($pgProglotPeriodeProgByGrparAn->getPprogCompl()) {
                                                //                                        $trouve = false;
                                                //                                        for ($l = 0; $l < count($tabStations); $l++) {
                                                //                                            if ($tabStations[$l] == $pgProglotPeriodeProgByGrparAn->getStationAn()->getStation()->getouvFoncId()) {
                                                //                                                $trouve = true;
                                                //                                                break;
                                                //                                            }
                                                //                                        }
                                                //                                        if (!$trouve) {
                                                //                                            if ($tabPrestataires[$i]["type"] == 'C') {
                                                //                                                $trouve = false;
                                                //                                                foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
                                                //                                                    if ($pgProgLotStationAn->getStation()->getOuvFoncId() == $pgProglotPeriodeProgByGrparAn->getStationAn()->getStation()->getOuvFoncId()) {
                                                //                                                        $trouve = true;
                                                //                                                        break;
                                                //                                                    }
                                                //                                                }
                                                //                                            } else {
                                                //                                                $trouve = true;
                                                //                                            }
                                                //                                            if ($trouve) {
                                                //                                                $tabStations[$is] = $pgProglotPeriodeProgByGrparAn->getStationAn()->getStation()->getouvFoncId();
                                                //                                                $is++;
                                                //                                            }
                                                //                                        }
                                                $trouve = false;
                                                for ($l = 0; $l < count($tabPeriodes); $l++) {
                                                    if ($tabPeriodes[$l] == $pgProglotPeriodeProgByGrparAn->getPeriodan()->getPeriode()->getId()) {
                                                        $trouve = true;
                                                        break;
                                                    }
                                                }
                                                if (!$trouve) {
                                                    $tabPeriodes[$ip] = $pgProglotPeriodeProgByGrparAn->getPeriodan()->getPeriode()->getId();
                                                    $ip++;
                                                }
                                            } else {
                                                if ($pgProglotPeriodeProgByGrparAn->getStatut() == 'C') {
                                                    $trouve = false;
                                                    for ($l = 0; $l < count($tabPeriodeCompls); $l++) {
                                                        if ($tabPeriodeCompls[$l] == $pgProglotPeriodeProgByGrparAn->getPeriodan()->getPeriode()->getId()) {
                                                            $trouve = true;
                                                            break;
                                                        }
                                                    }
                                                    if (!$trouve) {
                                                        $tabPeriodeCompls[$ipc] = $pgProglotPeriodeProgByGrparAn->getPeriodan()->getPeriode()->getId();
                                                        $ipc++;
                                                    }
                                                } else {
                                                    $trouve = false;
                                                    for ($l = 0; $l < count($tabPeriodeIgnores); $l++) {
                                                        if ($tabPeriodeIgnores[$l] == $pgProglotPeriodeProgByGrparAn->getPeriodan()->getPeriode()->getId()) {
                                                            $trouve = true;
                                                            break;
                                                        }
                                                    }
                                                    if (!$trouve) {
                                                        $tabPeriodeIgnores[$ipi] = $pgProglotPeriodeProgByGrparAn->getPeriodan()->getPeriode()->getId();
                                                        $ipi++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    //array_unique($tabProgs);
//                                $tabGroupes[$j]["stations"] = count($tabStations);
                                    $tabGroupes[$j]["periodes"] = count($tabPeriodes);
                                    $tabGroupes[$j]["periodeCompls"] = count($tabPeriodeCompls);
                                    $tabGroupes[$j]["periodeIgnores"] = count($tabPeriodeIgnores);
                                    // print_r ('station  : ' . $tabProgs[$l]["stations"]->getStation()->getNumero() . ' nb  : ' . $j );
                                }
                            }
                        }
                    }
                }
                $j++;
            }
//            usort($tabGroupes, create_function('$a,$b', 'return $a[\'groupe\']->getGrparRef()->getCodeGrp()-$b[\'groupe\']->getGrparRef()->getCodeGrp();'));
            $tabPrestataires[$i]["groupes"] = $tabGroupes;
            $i++;
        }


//        usort($tabPrestataires, create_function('$a,$b', 'return $a[\'prestataire\']->getAncnum()-$b[\'prestataire\']->getAncnum();'));
//        usort($pgProgLotGrparAns, create_function('$a,$b', 'return $a->getGrparRef()->getCodeGrp()-$b->getGrparRef()->getCodeGrp();'));

        return $this->render('AeagSqeBundle:Programmation:Bilan\prestataire.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'campagne' => $annee,
                    'lotan' => $pgProgLotAn,
                    'grparAns' => $pgProgLotGrparAns,
                    'prestataires' => $tabPrestataires));
    }

    public function prestataireGroupeAction($prestataireId = null, $grparAnId = null, $type = null) {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationBilan');
        $session->set('fonction', 'prestataireGroupe');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgLotGrparAn = $repoPgProgLotGrparAn->getPgProgLotGrparAnById($grparAnId);
        $pgRefCorresPresta = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($prestataireId);

        $tabPeriodes = array();
        $tabPeriodes["prestataire"] = $pgRefCorresPresta;
        $tabPeriodes["groupe"] = $pgProgLotGrparAn;
        $pgProgLotParamAnsByPrestataireGroupe = $repoPgProgLotParamAn->getPgProgLotParamAnByPrestataireGrparan($pgRefCorresPresta, $pgProgLotGrparAn);

        $tabStations = array();
        if (count($pgProgLotParamAnsByPrestataireGroupe) > 0) {
            $tabPeriodes["renseigner"] = "O";
            $j = 0;
            $k = 0;
            $delai = $pgProgLot->getDelaiPrel();
            $pgProglotPeriodeProgsByGrparAn = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAn($pgProgLotGrparAn);
            foreach ($pgProglotPeriodeProgsByGrparAn as $pgProglotPeriodeProg) {
                $trouve = false;
                if ($delai) {
                    $dateFin = clone($pgProglotPeriodeProg->getPeriodan()->getPeriode()->getDateDeb());
                     $dateFin->add(new \DateInterval('P' . $delai . 'D'));
                } else {
                    $dateFin = $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getDateFin();
                }
                $pgProglotPeriodeProg->getPeriodan()->getPeriode()->setDateFin($dateFin);
                for ($l = 0; $l < count($tabStations); $l++) {
                    if ($tabStations[$l]["station"]->getid() == $pgProglotPeriodeProg->getStationAn()->getId()) {
                        if ($pgProglotPeriodeProg->getStatut() == $type) {
                            $tabStations[$l]["periode"][$k] = $pgProglotPeriodeProg->getPeriodan();
                        } $k++;
                        $trouve = true;
                    }
                }
                if (!$trouve) {
                    $tabStations[$j]["station"] = $pgProglotPeriodeProg->getStationAn();
                    $k = 0;
                    $tabStations[$j]["periode"] = array();
                    if ($pgProglotPeriodeProg->getStatut() == $type) {
                        $tabStations[$j]["periode"][$k] = $pgProglotPeriodeProg->getPeriodan();
                    }
                    $k++;
                    $j++;
                }
            }
        } else {
            $tabPeriodes["renseigner"] = "N";
        }

        $tabProgs = array();
        $i = 0;
        for ($j = 0; $j < count($tabStations); $j++) {
            $tabProgs[$i]["station"] = $tabStations[$j]["station"];
            usort($tabStations[$j]["periode"], create_function('$a,$b', 'return $a->getPeriode()->getNumPeriode()-$b->getPeriode()->getNumPeriode();'));
            $tabProgs[$i]["periodes"] = $tabStations[$j]["periode"];
            $i++;
        }

        $tabPeriodes["progs"] = $tabProgs;

        return $this->render('AeagSqeBundle:Programmation:Bilan\prestataireGroupe.html.twig', array(
                    'periodeAn' => $tabPeriodes));
    }

    public function periodeAction() {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationBilan');
        $session->set('fonction', 'periode');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);

        $tabPeriodes = array();
        $i = 0;
        foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
            $tabPeriodes[$i]["periode"] = $pgProgLotPeriodeAn;
            $pgProglotPeriodeProgsByPeriode = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
            $tabProgs = array();
            $tabProgCompls = array();
            $tabProgIgnores = array();
            if (count($pgProglotPeriodeProgsByPeriode) > 0) {
                $tabPeriodes[$i]["renseigner"] = "O";
                $l = 0;
                foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
                    $tabProgs[$l]['groupe'] = $pgProgLotGrparAn->getGrparRef()->getCodeGrp();
                    $tabProgCompls[$l]['groupe'] = $pgProgLotGrparAn->getGrparRef()->getCodeGrp();
                    $tabProgIgnores[$l]['groupe'] = $pgProgLotGrparAn->getGrparRef()->getCodeGrp();
                    $tabProgs[$l]['nb'] = 0;
                    $tabProgCompls[$l]['nb'] = 0;
                    $tabProgIgnores[$l]['nb'] = 0;
                    $l++;
                }
                foreach ($pgProglotPeriodeProgsByPeriode as $pgProglotPeriodeProg) {
                    foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
                        if ($pgProgLotGrparAn->getGrparRef()->getCodeGrp() == $pgProglotPeriodeProg->getGrparAn()->getGrparRef()->getCodeGrp()) {
                            if (!$pgProglotPeriodeProg->getPprogCompl()) {
                                for ($l = 0; $l < count($tabProgs); $l++) {
                                    if ($tabProgs[$l]['groupe'] == $pgProgLotGrparAn->getGrparRef()->getCodeGrp()) {
                                        $tabProgs[$l]['nb'] = $tabProgs[$l]['nb'] + 1;
                                    }
                                };
                            } else {
                                if ($pgProglotPeriodeProg->getStatut() == 'C') {
                                    for ($l = 0; $l < count($tabProgCompls); $l++) {
                                        if ($tabProgCompls[$l]['groupe'] == $pgProgLotGrparAn->getGrparRef()->getCodeGrp()) {
                                            $tabProgCompls[$l]['nb'] = $tabProgCompls[$l]['nb'] + 1;
                                        }
                                    }
                                } else {
                                    for ($l = 0; $l < count($tabProgIgnores); $l++) {
                                        if ($tabProgIgnores[$l]['groupe'] == $pgProgLotGrparAn->getGrparRef()->getCodeGrp()) {
                                            $tabProgIgnores[$l]['nb'] = $tabProgIgnores[$l]['nb'] + 1;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $tabPeriodes[$i]["renseigner"] = "N";
            }
            $tabPeriodes[$i]["progs"] = $tabProgs;
            $tabPeriodes[$i]["progCompls"] = $tabProgCompls;
            $tabPeriodes[$i]["progIgnores"] = $tabProgIgnores;
            //print_r('i : ' . $i . ' prog : '. count($tabProgs) . ' compl : ' . count($tabProgCompls) . ' ignores : ' . count($tabProgIgnores));
            $i++;
        }
        // return new Response ('');
        usort($tabPeriodes, create_function('$a,$b', 'return $a[\'periode\']->getPeriode()->getNumPeriode()-$b[\'periode\']->getPeriode()->getNumPeriode();'));
        usort($pgProgLotGrparAns, create_function('$a,$b', 'return $a->getGrparRef()->getCodeGrp()-$b->getGrparRef()->getCodeGrp();'));

        return $this->render('AeagSqeBundle:Programmation:Bilan\periode.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'campagne' => $annee,
                    'lotan' => $pgProgLotAn,
                    'grparAns' => $pgProgLotGrparAns,
                    'periodeAns' => $tabPeriodes));
    }

    public function periodeGroupeAction($periodeAnId = null, $grparAnId = null) {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationBilan');
        $session->set('fonction', 'periodeGroupe');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');

        $pgProgLotGrparAn = $repoPgProgLotGrparAn->getPgProgLotGrparAnById($grparAnId);
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($periodeAnId);

        $tabPeriodes = array();
        $tabPeriodes["periode"] = $pgProgLotPeriodeAn;
        $tabPeriodes["groupe"] = $pgProgLotGrparAn;
        $pgProglotPeriodeProgsByPeriode = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAnPeriodeAn($pgProgLotGrparAn, $pgProgLotPeriodeAn);
        $tabProgs = array();
        $tabProgCompls = array();
        if (count($pgProglotPeriodeProgsByPeriode) > 0) {
            $tabPeriodes["renseigner"] = "O";
            $j = 0;
            $k = 0;
            foreach ($pgProglotPeriodeProgsByPeriode as $pgProglotPeriodeProg) {
                if (!$pgProglotPeriodeProg->getPprogCompl()) {
                    $tabProgs[$j] = $pgProglotPeriodeProg;
                    $j++;
                } else {
                    $tabProgCompls[$k] = $pgProglotPeriodeProg;
                    $k++;
                }
            }
        } else {
            $tabPeriodes["renseigner"] = "N";
        }
        usort($tabProgs, create_function('$a,$b', 'return $a->getGrparAn()->getGrparRef()->getCodeGrp()-$b->getGrparAn()->getGrparRef()->getCodeGrp();'));
        usort($tabProgCompls, create_function('$a,$b', 'return $a->getGrparAn()->getGrparRef()->getCodeGrp()-$b->getGrparAn()->getGrparRef()->getCodeGrp();'));
        $tabPeriodes["progs"] = $tabProgs;
        $tabPeriodes["progCompls"] = $tabProgCompls;

        return $this->render('AeagSqeBundle:Programmation:Bilan\periodeGroupe.html.twig', array(
                    'periodeAn' => $tabPeriodes));
    }

    public static function controleProgrammationAction($lotanId = nul, $emSqe, $session) {

        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationBilan');
        $session->set('fonction', 'controleProgrammation');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');


        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotanId);

        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);

        $nbPgProgLotStationAn = count($pgProgLotStationAns);
        $nbPgProgLotGrparAn = count($pgProgLotGrparAns);
        $nbPgProgLotGrparAnValide = $repoPgProgLotGrparAn->countPgProgLotGrparAnByValide($pgProgLotAn);
        $nbPgProgLotGrparAnNonValide = $repoPgProgLotGrparAn->countPgProgLotGrparAnByNonValide($pgProgLotAn);
        $nbPgProgLotPeriodeAn = count($pgProgLotPeriodeAns);

        $okStation = true;
        $nbStation = 0;
        foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
            $nb = $repoPgProgLotPeriodeProg->countPgProgLotPeriodeProgByStationAn($pgProgLotStationAn);
            if ($nb == 0) {
                $okStation = false;
            } else {
                $nbStation++;
            }
        }

        if ($nbPgProgLotStationAn > 0) {
            $okStation = true;
        }

        $okGroupe = true;
        $nbGroupe = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $nb = $repoPgProgLotPeriodeProg->countPgProgLotPeriodeProgByGrparAn($pgProgLotGrparAn);
            if ($nb == 0) {
                $okGroupe = false;
            } else {
                $nbGroupe++;
            }
        }

        if ($nbPgProgLotGrparAn > 0) {
            if ($nbPgProgLotGrparAn == $nbPgProgLotGrparAnValide) {
                $okGroupe = true;
            } else {
                $okGroupe = false;
            }
        } else {
            $okGroupe = false;
        }

        $okPeriode = true;
        $nbPeriode = 0;
        foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
            $nb = $repoPgProgLotPeriodeProg->countPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
            if ($nb == 0) {
                $okPeriode = false;
            } else {
                $nbPeriode++;
            }
        }

        if ($nbPgProgLotGrparAn > 0) {
            if ($nbPgProgLotGrparAn == $nbPgProgLotGrparAnValide) {
                $okGroupe = true;
            } else {
                $okGroupe = false;
            }
        } else {
            $okGroupe = false;
        }


        $tabControle = array();
        $tabControle['station']['ok'] = $okStation;
        $tabControle['station']['nb'] = $nbPgProgLotStationAn;
        $tabControle['station']['nbOk'] = $nbStation;
        $tabControle['groupe']['ok'] = $okGroupe;
        $tabControle['groupe']['nb'] = $nbPgProgLotGrparAn;
        $tabControle['groupe']['nbOk'] = $nbGroupe;
        $tabControle['groupe']['nbValide'] = $nbPgProgLotGrparAnValide;
        $tabControle['groupe']['nbNonValide'] = $nbPgProgLotGrparAnNonValide;
        $tabControle['periode']['ok'] = $okPeriode;
        $tabControle['periode']['nb'] = $nbPgProgLotPeriodeAn;
        $tabControle['periode']['nbOk'] = $nbPeriode;

        return $tabControle;
    }

    static function tri_periodes($a, $b) {
        if ($a['periode']->getNumPeriode() == $b['periode']->getNumPeriode())
            return 0;
        return ($a['periode']->getNumPeriode() < $b['periode']->getNumPeriode()) ? -1 : 1;
    }

}
