<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Aeag\SqeBundle\Entity\PgProgLotPeriodeAn;
use Aeag\SqeBundle\Entity\PgProgLotPeriodeProg;
use Aeag\SqeBundle\Entity\PgProgSuiviPhases;
use Aeag\AeagBundle\Controller\AeagController;

class ProgrammationBilanController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
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
//                if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE')) {
//                    $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P25');
//                } else {
                if ($tabControle['fictif']['ok']) {
                    $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P20');
                } else {
                    $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P19');
                }
//                }
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

            $pgProgSuiviPhases = new PgProgSuiviPhases();
            $pgProgSuiviPhases->setTypeObjet('LOT');
            $pgProgSuiviPhases->setObjId($pgProgLotAn->getId());
            $pgProgSuiviPhases->setDatePhase(new \DateTime());
            $pgProgSuiviPhases->setPhase($pgProgPhase);
            if ($pgProgWebuser) {
                $pgProgSuiviPhases->setUser($pgProgWebuser);
            }
            $emSqe->persist($pgProgSuiviPhases);

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
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
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
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');

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

        $tabStations = array();
        $i = 0;
        foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
            $tabStations[$i]["station"] = $pgProgLotStationAn;
            if ($pgProgLotStationAn->getRsxId()) {
                $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
            } else {
                $pgRefReseauMesure = null;
            }
            $tabStations[$i]["reseau"] = $pgRefReseauMesure;
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
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
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
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
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

//          \Symfony\Component\VarDumper\VarDumper::dump($tabPrestaDftAll);
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
                                $trouve = false;
                                foreach ($prestataireByGrparan as $presta) {
                                    for ($j = 0; $j < count($tabPrestaDftAll); $j++) {
                                        if ($tabPrestaDftAll[$j]['presta'] == $presta) {
                                            $trouve = true;
                                            break;
                                        }
                                    }
                                    if (!$trouve) {
                                        if ($pgProglotPeriodeProg->getStatut() != 'I') {
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
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
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
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');

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
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
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

            if ($pgProgLot->getDelaiPrel()) {
                $dateFin = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
                $delai = $pgProgLot->getDelaiPrel();
                $dateFin->add(new \DateInterval('P' . $delai . 'D'));
            } else {
                $dateFin = $pgProgLotPeriodeAn->getPeriode()->getDateFin();
            }
            $pgProgLotPeriodeAn->getPeriode()->setDateFin($dateFin);


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
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationBilan');
        $session->set('fonction', 'periodeGroupe');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');

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
                    $tabProgs[$j]['prog'] = $pgProglotPeriodeProg;
                    $pgProgLotStationAn = $pgProglotPeriodeProg->getStationAn();
                    if ($pgProgLotStationAn->getRsxId()) {
                        $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
                    } else {
                        $pgRefReseauMesure = null;
                    }
                    $tabProgs[$j]['reseau'] = $pgRefReseauMesure;
                    $j++;
                } else {
                    $tabProgCompls[$k]['prog'] = $pgProglotPeriodeProg;
                    $pgProgLotStationAn = $pgProglotPeriodeProg->getStationAn();
                    if ($pgProgLotStationAn->getRsxId()) {
                        $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
                    } else {
                        $pgRefReseauMesure = null;
                    }
                    $tabProgCompls[$j]['reseau'] = $pgRefReseauMesure;
                    $k++;
                }
            }
        } else {
            $tabPeriodes["renseigner"] = "N";
        }
        usort($tabProgs, create_function('$a,$b', 'return $a[\'prog\']->getGrparAn()->getGrparRef()->getCodeGrp()-$b[\'prog\']->getGrparAn()->getGrparRef()->getCodeGrp();'));
        usort($tabProgCompls, create_function('$a,$b', 'return $a[\'prog\']->getGrparAn()->getGrparRef()->getCodeGrp()-$b[\'prog\']->getGrparAn()->getGrparRef()->getCodeGrp();'));
        $tabPeriodes["progs"] = $tabProgs;
        $tabPeriodes["progCompls"] = $tabProgCompls;

        return $this->render('AeagSqeBundle:Programmation:Bilan\periodeGroupe.html.twig', array(
                    'periodeAn' => $tabPeriodes));
    }

    public function telechargerAction($lotan = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationBilan');
        $session->set('fonction', 'telecharger');

        //recupération des parametres
        $pgProgLotAnId = $lotan;

        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');

        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');

        $repoPgProgLotPresta = $emSqe->getRepository('AeagSqeBundle:PgProgLotPresta');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');


        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $delai = $pgProgLot->getDelaiPrel();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgPeriodes = $repoPgProgPeriodes->getPgProgPeriodesByAnneeTypePeriode($annee, $pgProgTypeMilieu->getTypePeriode());
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        asort($pgProgLotGrparAns);
        asort($pgProgLotStationAns);
        asort($pgProgLotPeriodeAns);
        $tabStations = array();
        $i = 0;
        foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
            $stationGeo = $pgProgLotStationAn->getStation();
            $tabStations[$i]['station']['ouvFoncId'] = $stationGeo->getOuvfoncId();
            $tabStations[$i]['station']['code'] = $stationGeo->getCode();
            $tabStations[$i]['station']['libelle'] = $stationGeo->getLibelle();
            $tabStations[$i]['station']['codeMasdo'] = $stationGeo->getCodeMasdo();
            $tabStations[$i]['station']['nomCoursEau'] = $stationGeo->getNomCoursEau();
            $tabStations[$i]['commune']['libelle'] = $stationGeo->getNomCommune();
            if ($pgProgLotStationAn->getRsxId()) {
                $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
            } else {
                $pgRefReseauMesure = null;
            }
            $tabStations[$i]["reseau"] = $pgRefReseauMesure;
            $pgProglotPeriodeProgsByStation = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAn);
            $tabProgs = array();
            $l = 0;
            foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
                $tabProgs[$l]['groupe'] = $pgProgLotGrparAn;
                if ($tabProgs[$l]['groupe']->getGrparRef()->getSupport()) {
                    $tabProgs[$l]['support'] = $tabProgs[$l]['groupe']->getGrparRef()->getSupport()->getNomSupport();
                } else {
                    $tabProgs[$l]['support'] = null;
                }
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                asort($pgProgLotParamAns);
                $tabParametres = array();
                $p = 0;
                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    $tabParametres[$p]['parametre'] = $pgProgLotParamAn;
                    if ($pgProgLotParamAn->getCodeFraction()) {
                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                        $tabParametres[$p]['fraction'] = $pgSandreFraction->getNomFraction();
                    } else {
                        $tabParametres[$p]['fraction'] = null;
                    }
                    if ($pgProgLotParamAn->getCodeUnite()) {
                        $pgSandreUnite = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($pgProgLotParamAn->getCodeUnite());
                        $tabParametres[$p]['unite'] = $pgSandreUnite->getNomUnite();
                    } else {
                        $tabParametres[$p]['unite'] = null;
                    }
                    $p++;
                }
                $tabProgs[$l]['parametres'] = $tabParametres;
                $tabProgs[$l]['periode'] = array();
                $tabProgs[$l]['nb'] = 0;
                $l++;
            }
            if (count($pgProglotPeriodeProgsByStation) > 0) {
                $tabStations[$i]["renseigner"] = "O";

                // return new Response ('nb : ' . count($tabProgs));
                foreach ($pgProglotPeriodeProgsByStation as $pgProglotPeriodeProg) {
                    if (!$pgProglotPeriodeProg->getPprogCompl()) {
                        for ($l = 0; $l < count($tabProgs); $l++) {
                            if ($tabProgs[$l]['groupe']->getGrparRef()->getCodeGrp() == $pgProglotPeriodeProg->getGrparAn()->getGrparRef()->getCodeGrp()) {
                                $trouve = false;
                                if (count($tabProgs[$l]['periode'])) {
                                    for ($j = 0; $j < count($tabProgs[$l]['periode']); $j++) {
                                        if ($tabProgs[$l]['periode'][$j]->getNumPeriode() == $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getNumPeriode()) {
                                            $trouve = true;
                                        }
                                    }
                                }
                                if (!$trouve) {
                                    $j = count($tabProgs[$l]['periode']);
                                    if ($pgProgLot->getDelaiPrel()) {
                                        $dateFin = clone($pgProglotPeriodeProg->getPeriodAn()->getPeriode()->getDateDeb());
                                        $delai = $pgProgLot->getDelaiPrel();
                                        $dateFin->add(new \DateInterval('P' . $delai . 'D'));
                                    } else {
                                        $dateFin = $pgProglotPeriodeProg->getPeriodAn()->getPeriode()->getDateFin();
                                    }
                                    $pgProglotPeriodeProg->getPeriodAn()->getPeriode()->setDateFin($dateFin);
                                    $tabProgs[$l]['periode'][$j] = $pgProglotPeriodeProg->getPeriodAn()->getPeriode();
                                    $tabProgs[$l]['nb'] = $tabProgs[$l]['nb'] + 1;
                                }
                            }
                        };
                    } else {
                        if ($pgProglotPeriodeProg->getStatut() == 'C') {
                            for ($l = 0; $l < count($tabProgs); $l++) {
                                if ($tabProgs[$l]['groupe']->getGrparRef()->getCodeGrp() == $pgProglotPeriodeProg->getGrparAn()->getGrparRef()->getCodeGrp()) {
                                    $trouve = false;
                                    if (count($tabProgs[$l]['periode'])) {
                                        for ($j = 0; $j < count($tabProgs[$l]['periode']); $j++) {
                                            if ($tabProgs[$l]['periode'][$j]->getNumPeriode() == $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getNumPeriode()) {
                                                $trouve = true;
                                            }
                                        }
                                    }
                                    if (!$trouve) {
                                        $j = count($tabProgs[$l]['periode']);
                                        if ($pgProgLot->getDelaiPrel()) {
                                            $dateFin = clone($pgProglotPeriodeProg->getPeriodAn()->getPeriode()->getDateDeb());
                                            $delai = $pgProgLot->getDelaiPrel();
                                            $dateFin->add(new \DateInterval('P' . $delai . 'D'));
                                        } else {
                                            $dateFin = $pgProglotPeriodeProg->getPeriodAn()->getPeriode()->getDateFin();
                                        }
                                        $pgProglotPeriodeProg->getPeriodAn()->getPeriode()->setDateFin($dateFin);
                                        $tabProgs[$l]['periode'][$j] = $pgProglotPeriodeProg->getPeriodAn()->getPeriode();
                                        $tabProgs[$l]['nb'] = $tabProgs[$l]['nb'] + 1;
                                    }
                                }
                            }
                        }
                    }
                }
                $tabProgBis = array();
                $k = 0;
                for ($j = 0; $j < count($tabProgs); $j++) {
                    if ($tabProgs[$j]['nb'] > 0) {
                        $tabProgBis[$k] = $tabProgs[$j];
                        $k++;
                    }
                }
                asort($tabProgBis);
                $tabStations[$i]["progs"] = $tabProgBis;
                $i++;
            }
        }


//        \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response('');


        $chemin = '/base/extranet/Transfert/Sqe/csv';
        $fichier = 'bilan-stations-programmation-' . $pgProgLotAn->getAnneeProg() . '-' . $pgProgLotAnId . '.csv';
        $fullFileName1 = $chemin . '/' . $fichier;
        $ext = strtolower(pathinfo($fullFileName1, PATHINFO_EXTENSION));
        if (file_exists($fullFileName1)) {
            unlink($fullFileName1);
        }
        $fichier_csv = fopen($fullFileName1, 'w+');
        // Entete
        $ligne = array('Programmation', 'Version', 'Lot',
            'Station', 'Libellé', 'Commune', 'Masse d\'eau', 'Rivière', 'Réseau',
            'Groupe', 'Libellé', 'Type', 'Support', 'Prestataire',
//            'Parametre', 'libellé', 'Fraction', 'Unité', 'Prestataire',
            'Période', 'Date début', 'Date fin'
        );
        for ($i = 0; $i < count($ligne); $i++) {
            $ligne[$i] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$i]);
        }
        fputcsv($fichier_csv, $ligne, ';');

        for ($i = 0; $i < count($tabStations); $i++) {
            for ($j = 0; $j < count($tabStations[$i]['progs']); $j++) {
//                for ($k = 0; $k < count($tabStations[$i]['progs'][$j]['parametres']); $k++) {
                for ($l = 0; $l < count($tabStations[$i]['progs'][$j]['periode']); $l++) {
                    $ligne = array($pgProgLotAn->getAnneeProg(),
                        $pgProgLotAn->getversion(),
                        $pgProgLotAn->getLot()->getNomLot(),
                        $tabStations[$i]['station']['code'],
                        $tabStations[$i]['station']['libelle'],
                        $tabStations[$i]['commune']['libelle'],
                        $tabStations[$i]['station']['codeMasdo'],
                        $tabStations[$i]['station']['nomCoursEau'],
                        $tabStations[$i]['reseau']->getNomRsx(),
                        $tabStations[$i]['progs'][$j]['groupe']->getGrparRef()->getCodeGrp(),
                        $tabStations[$i]['progs'][$j]['groupe']->getGrparRef()->getLibelleGrp(),
                        $tabStations[$i]['progs'][$j]['groupe']->getGrparRef()->getTypeGrp(),
                        $tabStations[$i]['progs'][$j]['support'],
                        $tabStations[$i]['progs'][$j]['groupe']->getPrestaDft()->getNomCorres(),
//                            $tabStations[$i]['progs'][$j]['parametres'][$k]['parametre']->getCodeParametre()->getCodeParametre(),
//                            $tabStations[$i]['progs'][$j]['parametres'][$k]['parametre']->getCodeParametre()->getNomParametre(),
//                            $tabStations[$i]['progs'][$j]['parametres'][$k]['fraction'],
//                            $tabStations[$i]['progs'][$j]['parametres'][$k]['unite'],
//                            $tabStations[$i]['progs'][$j]['parametres'][$k]['parametre']->getPrestataire()->getNomCorres(),
                        $tabStations[$i]['progs'][$j]['periode'][$l]->getLabelPeriode(),
                        $tabStations[$i]['progs'][$j]['periode'][$l]->getDateDeb()->format('d/m/Y'),
                        $tabStations[$i]['progs'][$j]['periode'][$l]->getDateFin()->format('d/m/Y'),
                    );
                    for ($m = 0; $m < count($ligne); $m++) {
                        $ligne[$m] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$m]);
                    }
                    fputcsv($fichier_csv, $ligne, ';');
                }
//                }
            }
        }

        fclose($fichier_csv);

        $tabPeriodes = array();

        foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
            $trouve = false;
            for ($i = 0; $i < count($tabPeriodes); $i++) {
                if ($tabPeriodes[$i]['periodes']['periode']->getId() == $pgProgLotPeriodeAn->getPeriode()->getId()) {
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                if ($pgProgLot->getDelaiPrel()) {
                    $dateFin = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
                    $delai = $pgProgLot->getDelaiPrel();
                    $dateFin->add(new \DateInterval('P' . $delai . 'D'));
                } else {
                    $dateFin = $pgProgLotPeriodeAn->getPeriode()->getDateFin();
                }
                $pgProgLotPeriodeAn->getPeriode()->setDateFin($dateFin);
                $tabPeriodes[$i]['periodes']['periode'] = $pgProgLotPeriodeAn->getPeriode();
                $tabPeriodes[$i]['periodes']['groupes'] = array();
                $pgProglotPeriodeProgsByPeriode = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
                foreach ($pgProglotPeriodeProgsByPeriode as $pgProglotPeriodeProg) {
                    $trouve = false;
                    for ($j = 0; $j < count($tabPeriodes[$i]['periodes']['groupes']); $j++) {
                        if ($tabPeriodes[$i]['periodes']['groupes'][$j]['groupe']->getGrparRef()->getCodeGrp() == $pgProglotPeriodeProg->getGrparAn()->getGrparRef()->getCodeGrp()) {
                            $trouve = true;
                            break;
                        }
                    }
                    if (!$trouve) {
                        $tabPeriodes[$i]['periodes']['groupes'][$j]['groupe'] = $pgProglotPeriodeProg->getGrparAn();
                        if ($tabPeriodes[$i]['periodes']['groupes'][$j]['groupe']->getGrparRef()->getSupport()) {
                            $tabPeriodes[$i]['periodes']['groupes'][$j]['support'] = $tabPeriodes[$i]['periodes']['groupes'][$j]['groupe']->getGrparRef()->getSupport()->getNomSupport();
                        } else {
                            $tabPeriodes[$i]['periodes']['groupes'][$j]['support'] = null;
                        }
                        $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProglotPeriodeProg->getGrparAn());
                        asort($pgProgLotParamAns);
                        $tabParametres = array();
                        $p = 0;
                        foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                            $tabParametres[$p]['parametre'] = $pgProgLotParamAn;
                            if ($pgProgLotParamAn->getCodeFraction()) {
                                $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                                $tabParametres[$p]['fraction'] = $pgSandreFraction->getNomFraction();
                            } else {
                                $tabParametres[$p]['fraction'] = null;
                            }
                            if ($pgProgLotParamAn->getCodeUnite()) {
                                $pgSandreUnite = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($pgProgLotParamAn->getCodeUnite());
                                $tabParametres[$p]['unite'] = $pgSandreUnite->getNomUnite();
                            } else {
                                $tabParametres[$p]['unite'] = null;
                            }
                            $p++;
                        }
                        $tabPeriodes[$i]['periodes']['groupes'][$j]['parametres'] = $tabParametres;
                        $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'] = array();
                        foreach ($pgProglotPeriodeProgsByPeriode as $pgProglotPeriodeProgStation) {
                            $trouve = false;
                            for ($k = 0; $k < count($tabPeriodes[$i]['periodes']['groupes'][$j]['stations']); $k++) {
                                if ($tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$k]['ouvFoncId'] == $pgProglotPeriodeProgStation->getStationAn()->getStation()->getOuvfoncId()) {
                                    $trouve = true;
                                    break;
                                }
                            };
                            if (!$trouve) {
                                $stationGeo = $pgProglotPeriodeProgStation->getStationAn()->getStation();
                                $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$k]['ouvFoncId'] = $stationGeo->getOuvfoncId();
                                $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$k]['code'] = $stationGeo->getCode();
                                $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$k]['libelle'] = $stationGeo->getLibelle();
                                $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$k]['codeMasdo'] = $stationGeo->getCodeMasdo();
                                $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$k]['nomCoursEau'] = $stationGeo->getNomCoursEau();
                                $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$k]['commune'] = $stationGeo->getNomCommune();
                                if ($pgProgLotStationAn->getRsxId()) {
                                    $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
                                } else {
                                    $pgRefReseauMesure = null;
                                }
                                $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$k]['reseau'] = $pgRefReseauMesure;
                                if (!$pgProglotPeriodeProgStation->getPprogCompl()) {
                                    $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$k]['statut'] = 'O';
                                } else {
                                    $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$k]['statut'] = 'C';
                                }
                            }
                        }
                    }
                }
            }
        }
        // return new Response ('');
        usort($tabPeriodes, create_function('$a,$b', 'return $a[\'periodes\'][\'periode\']->getNumPeriode()-$b[\'periodes\'][\'periode\']->getNumPeriode();'));

//        \Symfony\Component\VarDumper\VarDumper::dump($tabPeriodes);
//        return new Response('');
//         return new Response ('fichier : ' . $chemin . '/' . $fichier . ' ext : ' . $ext. ' size : ' . filesize($chemin . '/' . $fichier));

        $chemin = '/base/extranet/Transfert/Sqe/csv';
        $fichier = 'bilan-periodes-programmation-' . $pgProgLotAn->getAnneeProg() . '-' . $pgProgLotAnId . '.csv';
        $fullFileName2 = $chemin . '/' . $fichier;
        $ext = strtolower(pathinfo($fullFileName2, PATHINFO_EXTENSION));
        if (file_exists($fullFileName2)) {
            unlink($fullFileName2);
        }
        $fichier_csv = fopen($fullFileName2, 'w+');
        // Entete
        $ligne = array('Programmation', 'Version', 'Lot',
            'Période', 'Date début', 'Date fin',
            'Groupe', 'Libellé', 'Type', 'Support', 'Prestataire',
//            'Parametre', 'libellé', 'Fraction', 'Unité', 'Prestataire',
            'Station', 'Libellé', 'Commune', 'Masse d\'eau', 'Rivière', 'Réseau',
        );
        for ($i = 0; $i < count($ligne); $i++) {
            $ligne[$i] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$i]);
        }
        fputcsv($fichier_csv, $ligne, ';');

        for ($i = 0; $i < count($tabPeriodes); $i++) {
            for ($j = 0; $j < count($tabPeriodes[$i]['periodes']['groupes']); $j++) {
//                for ($k = 0; $k < count($tabPeriodes[$i]['periodes']['groupes'][$j]['parametres']); $k++) {
                for ($l = 0; $l < count($tabPeriodes[$i]['periodes']['groupes'][$j]['stations']); $l++) {
                    $ligne = array($pgProgLotAn->getAnneeProg(),
                        $pgProgLotAn->getversion(),
                        $pgProgLotAn->getLot()->getNomLot(),
                        $tabPeriodes[$i]['periodes']['periode']->getLabelPeriode(),
                        $tabPeriodes[$i]['periodes']['periode']->getDateDeb()->format('d/m/Y'),
                        $tabPeriodes[$i]['periodes']['periode']->getDateFin()->format('d/m/Y'),
                        $tabPeriodes[$i]['periodes']['groupes'][$j]['groupe']->getGrparRef()->getCodeGrp(),
                        $tabPeriodes[$i]['periodes']['groupes'][$j]['groupe']->getGrparRef()->getLibelleGrp(),
                        $tabPeriodes[$i]['periodes']['groupes'][$j]['groupe']->getGrparRef()->getTypeGrp(),
                        $tabPeriodes[$i]['periodes']['groupes'][$j]['support'],
                        $tabPeriodes[$i]['periodes']['groupes'][$j]['groupe']->getPrestaDft()->getNomCorres(),
//                            $tabPeriodes[$i]['periodes']['groupes'][$j]['parametres'][$k]['parametre']->getCodeParametre()->getCodeParametre(),
//                            $tabPeriodes[$i]['periodes']['groupes'][$j]['parametres'][$k]['parametre']->getCodeParametre()->getNomParametre(),
//                            $tabPeriodes[$i]['periodes']['groupes'][$j]['parametres'][$k]['fraction'],
//                            $tabPeriodes[$i]['periodes']['groupes'][$j]['parametres'][$k]['unite'],
//                            $tabPeriodes[$i]['periodes']['groupes'][$j]['parametres'][$k]['parametre']->getPrestataire()->getNomCorres(),
                        $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$l]['code'],
                        $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$l]['libelle'],
                        $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$l]['commune'],
                        $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$l]['codeMasdo'],
                        $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$l]['nomCoursEau'],
                        $tabPeriodes[$i]['periodes']['groupes'][$j]['stations'][$l]['reseau']->getNomRsx(),
                    );
                    for ($m = 0; $m < count($ligne); $m++) {
                        $ligne[$m] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$m]);
                    }
                    fputcsv($fichier_csv, $ligne, ';');
                }
//                }
            }
        }

        fclose($fichier_csv);

//        \Symfony\Component\VarDumper\VarDumper::dump($tabPeriodes);
//        return new Response('');
        // par pestataire

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

//           \Symfony\Component\VarDumper\VarDumper::dump($tabPrestas);
//        return new Response ('');


        $tabMessage = array();
        $tabPrestataires = array();
        $i = 0;

        for ($k = 0; $k < count($tabPrestas); $k++) {
            $prestataire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($tabPrestas[$k]["presta"]);
            $pgProgLotPresta = $repoPgProgLotPresta->getPgProgLotPrestaByLotPresta($pgProgLot, $prestataire);
            $tabPrestataires[$i]["prestataire"] = $prestataire;
            $tabPrestataires[$i]["type"] = $tabPrestas[$k]["type"];
            $tabPrestataires[$i]["renseigner"] = "N";
            $tabPrestataires[$i]["groupes"] = null;
            if ($pgProgLotPresta) {
                if (count($pgProgLotPresta) == 1) {
                    $tabPrestataires[$i]["typePresta"] = $pgProgLotPresta[0]->getTypePresta();
                } else {
                    $tabPrestataires[$i]["typePresta"] = "PL";
                }
            } else {
                $tabPrestataires[$i]["typePresta"] = null;
            }
            $i++;
        }

        for ($i = 0; $i < count($tabPrestataires); $i++) {
            $prestataire = $tabPrestataires[$i]["prestataire"];
            $j = 0;
            $tabGroupes = array();
//            foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
//                echo ('$pgProgLotAn : ' . $pgProgLotAn->getId() . ' $prestataire :  ' . $prestataire->getadrCorId() . '<br/>');
            $pgProgLotGrparAnsByPrestataires = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotAnPrestaDft($pgProgLotAn, $prestataire);
            foreach ($pgProgLotGrparAnsByPrestataires as $pgProgLotGrparAnByPrestataire) {
                $tabGroupes[$j]["groupe"] = $pgProgLotGrparAnByPrestataire;
                if ($tabGroupes[$j]["groupe"]->getGrparRef()->getSupport()) {
                    $tabGroupes[$j]['support'] = $tabGroupes[$j]["groupe"]->getGrparRef()->getSupport()->getNomSupport();
                } else {
                    $tabGroupes[$j]['support'] = null;
                }
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAnByPrestataire);
                asort($pgProgLotParamAns);
                $tabParametres = array();
                $p = 0;
                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    $tabParametres[$p]['parametre'] = $pgProgLotParamAn;
                    if ($pgProgLotParamAn->getCodeFraction()) {
                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
                        $tabParametres[$p]['fraction'] = $pgSandreFraction->getNomFraction();
                    } else {
                        $tabParametres[$p]['fraction'] = null;
                    }
                    if ($pgProgLotParamAn->getCodeUnite()) {
                        $pgSandreUnite = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($pgProgLotParamAn->getCodeUnite());
                        $tabParametres[$p]['unite'] = $pgSandreUnite->getNomUnite();
                    } else {
                        $tabParametres[$p]['unite'] = null;
                    }
                    $p++;
                }
                $tabGroupes[$j]['parametres'] = $tabParametres;
                $tabStations = array();
                $delai = $pgProgLot->getDelaiPrel();
                $pgProglotPeriodeProgsByGrparAn = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAn($pgProgLotGrparAnByPrestataire);
                foreach ($pgProglotPeriodeProgsByGrparAn as $pgProglotPeriodeProg) {
                    if ($delai) {
                        $dateFin = clone($pgProglotPeriodeProg->getPeriodan()->getPeriode()->getDateDeb());
                        $dateFin->add(new \DateInterval('P' . $delai . 'D'));
                    } else {
                        $dateFin = $pgProglotPeriodeProg->getPeriodan()->getPeriode()->getDateFin();
                    }
                    $pgProglotPeriodeProg->getPeriodan()->getPeriode()->setDateFin($dateFin);
                    $trouve = false;
                    for ($k = 0; $k < count($tabStations); $k++) {
                        if ($tabStations[$k]["station"]->getOuvFoncId() == $pgProglotPeriodeProg->getStationAn()->getStation()->getOuvFoncId()) {
                            $trouve = true;
                            break;
                        }
                    }
                    if (!$trouve) {
                        $tabStations[$k]["station"] = $pgProglotPeriodeProg->getStationAn()->getStation();
                        if ($pgProglotPeriodeProg->getStationAn()->getRsxId()) {
                            $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProglotPeriodeProg->getStationAn()->getRsxId());
                        } else {
                            $pgRefReseauMesure = null;
                        }
                        $tabStations[$k]['reseau'] = $pgRefReseauMesure;
                        $tabStations[$k]["periodes"] = array();
                        $pgProglotPeriodeProgsByStationGroupe = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnGrparAn($pgProglotPeriodeProg->getStationAn(), $pgProgLotGrparAnByPrestataire);
                        foreach ($pgProglotPeriodeProgsByStationGroupe as $pgProglotPeriodeProgByStationGroupe) {
                            $trouve = false;
                            for ($l = 0; $l < count($tabStations[$k]["periodes"]); $l++) {
                                if ($tabStations[$k]["periodes"][$l] == $pgProglotPeriodeProgByStationGroupe->getPeriodan()->getPeriode()) {
                                    $trouve = true;
                                    break;
                                }
                            }
                            if (!$trouve) {
                                $tabStations[$k]["periodes"][$l] = $pgProglotPeriodeProgByStationGroupe->getPeriodan()->getPeriode();
                            }
                        }
                    }
                }
                $tabGroupes[$j]["stations"] = $tabStations;
                $j++;
            }
//            }
//               \Symfony\Component\VarDumper\VarDumper::dump($tabGroupes);
//        return new Response ('');

            $tabPrestataires[$i]["groupes"] = $tabGroupes;
        }

        $chemin = '/base/extranet/Transfert/Sqe/csv';
        $fichier = 'bilan-prestataires-programmation-' . $pgProgLotAn->getAnneeProg() . '-' . $pgProgLotAnId . '.csv';
        $fullFileName3 = $chemin . '/' . $fichier;
        $ext = strtolower(pathinfo($fullFileName3, PATHINFO_EXTENSION));
        if (file_exists($fullFileName3)) {
            unlink($fullFileName3);
        }
        $fichier_csv = fopen($fullFileName3, 'w+');
        // Entete
        $ligne = array('Programmation', 'Version', 'Lot',
            'Prestataire code', 'Prestataire nom',
            'Groupe', 'Libellé', 'Type', 'Support', 'Prestataire',
//            'Parametre', 'libellé', 'Fraction', 'Unité', 'Prestataire',
            'Station', 'Libellé', 'Commune', 'Masse d\'eau', 'Rivière', 'Réseau',
            'Période', 'Date début', 'Date fin',
        );
        for ($i = 0; $i < count($ligne); $i++) {
            $ligne[$i] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$i]);
        }
        fputcsv($fichier_csv, $ligne, ';');

//        \Symfony\Component\VarDumper\VarDumper::dump($tabPrestataires);
//        return new Response ('');

        for ($i = 0; $i < count($tabPrestataires); $i++) {
            for ($j = 0; $j < count($tabPrestataires[$i]['groupes']); $j++) {
//                for ($k = 0; $k < count($tabPrestataires[$i]['groupes'][$j]['parametres']); $k++) {
                for ($l = 0; $l < count($tabPrestataires[$i]['groupes'][$j]['stations']); $l++) {
                    for ($m = 0; $m < count($tabPrestataires[$i]['groupes'][$j]['stations'][$l]['periodes']); $m++) {
                        $ligne = array($pgProgLotAn->getAnneeProg(),
                            $pgProgLotAn->getversion(),
                            $pgProgLotAn->getLot()->getNomLot(),
                            $tabPrestataires[$i]['prestataire']->getAncnum(),
                            $tabPrestataires[$i]['prestataire']->getNomCorres(),
                            $tabPrestataires[$i]['groupes'][$j]['groupe']->getGrparRef()->getCodeGrp(),
                            $tabPrestataires[$i]['groupes'][$j]['groupe']->getGrparRef()->getLibelleGrp(),
                            $tabPrestataires[$i]['groupes'][$j]['groupe']->getGrparRef()->getTypeGrp(),
                            $tabPrestataires[$i]['groupes'][$j]['support'],
                            $tabPrestataires[$i]['groupes'][$j]['groupe']->getPrestaDft()->getNomCorres(),
//                                $tabPrestataires[$i]['groupes'][$j]['parametres'][$k]['parametre']->getCodeParametre()->getCodeParametre(),
//                                $tabPrestataires[$i]['groupes'][$j]['parametres'][$k]['parametre']->getCodeParametre()->getNomParametre(),
//                                $tabPrestataires[$i]['groupes'][$j]['parametres'][$k]['fraction'],
//                                $tabPrestataires[$i]['groupes'][$j]['parametres'][$k]['unite'],
//                                $tabPrestataires[$i]['groupes'][$j]['parametres'][$k]['parametre']->getPrestataire()->getNomCorres(),
                            $tabPrestataires[$i]['groupes'][$j]['stations'][$l]['station']->getCode(),
                            $tabPrestataires[$i]['groupes'][$j]['stations'][$l]['station']->getLibelle(),
                            $tabPrestataires[$i]['groupes'][$j]['stations'][$l]['station']->getNomCommune(),
                            $tabPrestataires[$i]['groupes'][$j]['stations'][$l]['station']->getCodeMasdo(),
                            $tabPrestataires[$i]['groupes'][$j]['stations'][$l]['station']->getNomCoursEau(),
                            $tabPrestataires[$i]['groupes'][$j]['stations'][$l]['reseau']->getNomRsx(),
                            $tabPrestataires[$i]['groupes'][$j]['stations'][$l]['periodes'][$m]->getLabelPeriode(),
                            $tabPrestataires[$i]['groupes'][$j]['stations'][$l]['periodes'][$m]->getDateDeb()->format('d/m/Y'),
                            $tabPrestataires[$i]['groupes'][$j]['stations'][$l]['periodes'][$m]->getDateFin()->format('d/m/Y'),
                        );
                        for ($m = 0; $m < count($ligne); $m++) {
                            $ligne[$m] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$m]);
                        }
                        fputcsv($fichier_csv, $ligne, ';');
                    }
                }
//                }
            }
        }

        fclose($fichier_csv);


//        \Symfony\Component\VarDumper\VarDumper::dump($tabPrestataires);
//        return new Response('');

        $files = array();
        $zip = new \ZipArchive();
        $zipName = 'bilan-programmation-' . $pgProgLotAn->getAnneeProg() . '-' . $pgProgLotAnId . ".zip";
        array_push($files, $fullFileName1);
        array_push($files, $fullFileName2);
        array_push($files, $fullFileName3);
        $zip->open($chemin . '/' . $zipName, \ZipArchive::CREATE);
        foreach ($files as $f) {
            $zip->addFromString(basename($f), file_get_contents($f));
        }
        $zip->close();
        $fichier = $zipName;

        \header("Cache-Control: no-cahe, must-revalidate");
        \header('Content-Type', 'text/' . $ext);
        \header('Content-disposition: attachment; filename="' . $fichier . '"');
        \header('Expires: 0');
        \header('Content-Length: ' . filesize($chemin . '/' . $fichier));
        readfile($chemin . '/' . $fichier);
        exit();
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
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');


        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotanId);
        $pgRefCorresPrestaFictif = $repoPgRefCorresPresta->getPgRefCorresPrestaByAncnum('00000000A');

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
        $nbFictif = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $nb = $repoPgProgLotPeriodeProg->countPgProgLotPeriodeProgByGrparAn($pgProgLotGrparAn);
            if ($nb == 0) {
                $okGroupe = false;
            } else {
                //controle si prestataire non fictif
                $fictif = false;
                if ($pgRefCorresPrestaFictif) {
                    $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                    foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                        if ($pgProgLotParamAn->getPrestataire()->getAdrCorId() == $pgRefCorresPrestaFictif->getAdrCorId()) {
                            $fictif = true;
                            $nbFictif++;
                            break;
                        }
                    }
                }
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
        if ($nbFictif == 0) {
            $tabControle['fictif']['ok'] = true;
        } else {
            $tabControle['fictif']['ok'] = false;
        }
        return $tabControle;
    }

    private static function tri_periodes($a, $b) {
        if ($a['periode']->getNumPeriode() == $b['periode']->getNumPeriode())
            return 0;
        return ($a['periode']->getNumPeriode() < $b['periode']->getNumPeriode()) ? -1 : 1;
    }

}
