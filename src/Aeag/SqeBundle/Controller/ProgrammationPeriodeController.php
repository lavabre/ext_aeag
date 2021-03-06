<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Aeag\SqeBundle\Entity\PgProgLotPeriodeAn;
use Aeag\SqeBundle\Entity\PgProgLotPeriodeProg;
use Aeag\SqeBundle\Entity\PgProgSuiviPhases;
use Aeag\SqeBundle\Controller\ProgrammationBilanController;

class ProgrammationPeriodeController extends Controller {

    public function indexAction() {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');
        $annee = $session->get('critAnnee');

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

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');


        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgPeriodes = $repoPgProgPeriodes->getPgProgPeriodesByAnneeTypePeriode($annee, $pgProgTypeMilieu->getTypePeriode());
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);



        foreach ($pgProgPeriodes as $pgProgPeriode) {
            if ($pgProgLot->getDelaiPrel()) {
                $dateFin = clone($pgProgPeriode->getDateDeb());
                $delai = $pgProgLot->getDelaiPrel();
                $dateFin->add(new \DateInterval('P' . $delai . 'D'));
            } else {
                $dateFin = $pgProgPeriode->getDateFin();
            }
            $pgProgPeriode->setDateFin($dateFin);
        }


        if ($action == 'P' && $maj != 'V') {
            if ($pgProgTypeMilieu->getTypePeriode()->getcodeTypePeriode() == 'SEM') {
                if (!$pgProgLotPeriodeAns) {
                    return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_filtrer_semaines', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
                }
            }
        }
        if (!$pgProgLotPeriodeAns) {
            foreach ($pgProgPeriodes as $selPeriode) {
                $pgProgPeriode = $repoPgProgPeriodes->getPgProgPeriodesById($selPeriode->getId());
                $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotanPeriode($pgProgLotAn, $pgProgPeriode);
                if (!$pgProgLotPeriodeAn) {
                    $pgProgLotPeriodeAn = new PgProgLotPeriodeAn();
                    $pgProgLotPeriodeAn->setLotan($pgProgLotAn);
                    $pgProgLotPeriodeAn->setPeriode($pgProgPeriode);
                    $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('CRE');
                    $pgProgLotPeriodeAn->setCodeStatut($pgProgStatut);
                    $emSqe->persist($pgProgLotPeriodeAn);
                }
            }
            $emSqe->flush();
        }

// Récupération des support obligatoire
        $tabGrparAns = array();
        $i = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $tabGrparAns[$i]["groupe"] = $pgProgLotGrparAn;
            $i++;
        }

        $tabEnregs = $repoPgProgLotAn->getProgrammationPeriodes($pgProgLotAn->getId());

        $stationLu = null;
        $tabMessage = array();
        $tabStations = array();
        $tabPeriodes = array();
        $i = 0;
        $j = 0;
        foreach ($tabEnregs as $tabEnreg) {
            if (!$stationLu) {
                $stationLu = $tabEnreg['station_id'];
            }
            if ($stationLu != $tabEnreg['station_id']) {
                $tabStations[$i]["periodes"] = $tabPeriodes;
                $stationLu = $tabEnreg['station_id'];
                $tabPeriodes = array();
                $j = 0;
                $i++;
            }
            $tabStations[$i]["station"]["id"] = $tabEnreg['station_id'];
            $tabStations[$i]["station"]["code"] = $tabEnreg['code_station'];
            $tabStations[$i]["station"]["libelle"] = $tabEnreg['lib_station'];
            $tabStations[$i]["station"]["reseau"] = $tabEnreg['nom_rsx'];
            $tabPeriodes[$j]["ordre"] = $tabEnreg['periode_id'];
            $tabPeriodes[$j]["periode"] = $tabEnreg['label_periode'];
            $tabPeriodes[$j]["statut"] = $tabEnreg['statut_periode'];
            $tabPeriodes[$j]["nbGroupe"] = $tabEnreg['nb_pprog'];
            $tabPeriodes[$j]["autreStatut"] = $tabEnreg['statut_autres'];
            $tabPeriodes[$j]["autreProgrammation"] = $tabEnreg['nb_pprog_autres'];
            $j++;
        }
        $tabStations[$i]["periodes"] = $tabPeriodes;

//          \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//           return new Response ('');

        $session->set('niveau6', $this->generateUrl('AeagSqeBundle_programmation_periodes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));

        $tabControle = ProgrammationBilanController::controleProgrammationAction($pgProgLotAnId, $emSqe, $session);

        return $this->render('AeagSqeBundle:Programmation:Periode\index.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'campagne' => $annee,
                    'lotan' => $pgProgLotAn,
                    'controle' => $tabControle,
                    'milieu' => $pgProgTypeMilieu,
                    'grparAns' => $tabGrparAns,
                    'stationAns' => $tabStations,
                    'typePeriode' => $pgProgTypeMilieu->getTypePeriode(),
                    'periodes' => $pgProgPeriodes,
                    'cocherTout' => 'N',
                    'messages' => $tabMessage));
    }

    public function filtrerSemainesAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'filtrerSemaines');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $pgProgPeriodes = $repoPgProgPeriodes->getPgProgPeriodesByAnneeTypePeriode($annee, $pgProgTypeMilieu->getTypePeriode());

        foreach ($pgProgPeriodes as $pgProgPeriode) {
            if ($pgProgLot->getDelaiPrel()) {
                $dateFin = clone($pgProgPeriode->getDateDeb());
                $delai = $pgProgLot->getDelaiPrel();
                $dateFin->add(new \DateInterval('P' . $delai . 'D'));
            } else {
                $dateFin = $pgProgPeriode->getDateFin();
            }
            $pgProgPeriode->setDateFin($dateFin);
        }

        $tabMessage = array();
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
            $tabPeriodes = array();
            $j = 0;
            if (count($pgProgLotPeriodeAns) > 0) {
                foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
                    $tabPeriodes[$j]["ordre"] = $pgProgLotPeriodeAn->getPeriode()->getid();
                    $tabPeriodes[$j]["periode"] = $pgProgLotPeriodeAn->getPeriode();
                    $tabPeriodes[$j]["statut"] = $pgProgLotPeriodeAn->getCodeStatut();
                    $j++;
                }
            }
            $tabStations[$i]["periodes"] = $tabPeriodes;
            break;
        };

        return $this->render('AeagSqeBundle:Programmation:Periode\filtrerSemaines.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'campagne' => $annee,
                    'lotan' => $pgProgLotAn,
                    'milieu' => $pgProgTypeMilieu,
                    'typePeriode' => $pgProgTypeMilieu->getTypePeriode(),
                    'stationAns' => $tabStations,
                    'periodes' => $pgProgPeriodes,
                    'cocherTout' => 'N',
                    'messages' => $tabMessage));
    }

    public function semainesSelectionneesAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'semainesSelectionnees');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgPeriodes = $repoPgProgPeriodes->getPgProgPeriodesByAnneeTypePeriode($annee, $pgProgTypeMilieu->getTypePeriode());

        $selPeriodes = Array();
        if (isset($_POST['checkPeriode'])) {
            $selPeriodes = $_POST['checkPeriode'];
        }

        foreach ($pgProgPeriodes as $pgProgPeriode) {
            $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotanPeriode($pgProgLotAn, $pgProgPeriode);
            if ($pgProgLotPeriodeAn) {
                $trouve = 0;
                foreach ($selPeriodes as $selPeriode) {
                    $pgProgPeriode = $repoPgProgPeriodes->getPgProgPeriodesById($selPeriode);
                    if ($pgProgLotPeriodeAn->getPeriode()->getId() == $pgProgPeriode->getId()) {
                        $trouve = 1;
                        break;
                    }
                }
                if ($trouve == 0) {
                    $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
                    foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                        $pgProgLotPeriodeProgCompls = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPprogCompl($pgProgLotPeriodeProg);
                        foreach ($pgProgLotPeriodeProgCompls as $pgProgLotPeriodeProgCompl) {
                            $pgProgLotPeriodeProgCompl->setPprogCompl(null);
                            $emSqe->persist($pgProgLotPeriodeProgCompl);
                        }
                        $emSqe->remove($pgProgLotPeriodeProg);
                    }
                    $emSqe->remove($pgProgLotPeriodeAn);
                }
            }
        }
        $emSqe->flush();

        foreach ($selPeriodes as $selPeriode) {
            $pgProgPeriode = $repoPgProgPeriodes->getPgProgPeriodesById($selPeriode);
            $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotanPeriode($pgProgLotAn, $pgProgPeriode);
            if (!$pgProgLotPeriodeAn) {
                $pgProgLotPeriodeAn = new PgProgLotPeriodeAn();
                $pgProgLotPeriodeAn->setLotan($pgProgLotAn);
                $pgProgLotPeriodeAn->setPeriode($pgProgPeriode);
                $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('CRE');
                $pgProgLotPeriodeAn->setCodeStatut($pgProgStatut);
                $emSqe->persist($pgProgLotPeriodeAn);
            }
        }
        $emSqe->flush();

        $this->get('session')->getFlashBag()->add('notice-succes', "les semaines sélectionnées ont été enregistrées pour la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL);

        return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_periodes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
    }

    public function filtrerPeriodesAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'validerSelectionPeriode');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgPeriodes = $repoPgProgPeriodes->getPgProgPeriodesByAnneeTypePeriode($annee, $pgProgTypeMilieu->getTypePeriode());

        foreach ($pgProgPeriodes as $pgProgPeriode) {
            if ($pgProgLot->getDelaiPrel()) {
                $dateFin = clone($pgProgPeriode->getDateDeb());
                $delai = $pgProgLot->getDelaiPrel();
                $dateFin->add(new \DateInterval('P' . $delai . 'D'));
            } else {
                $dateFin = $pgProgPeriode->getDateFin();
            }
            $pgProgPeriode->setDateFin($dateFin);
        }

        $tabProgPeriodes = array();
        $i = 0;

        foreach ($pgProgPeriodes as $pgProgPeriode) {
            $tabProgPeriodes[$i]['periode'] = $pgProgPeriode;
            $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotanPeriode($pgProgLotAn, $pgProgPeriode);
            if ($pgProgLotPeriodeAn) {
                $tabProgPeriodes[$i]['sel'] = 'O';
            } else {
                $tabProgPeriodes[$i]['sel'] = 'N';
            }
            $i++;
        }

        //  usort($tabStations, create_function('$a,$b', 'return $a[\'station\']->getStation()->getCode()-$b[\'station\']->getStation()->getCode();'));

        $tabControle = ProgrammationBilanController::controleProgrammationAction($pgProgLotAnId, $emSqe, $session);

        return $this->render('AeagSqeBundle:Programmation:Periode\filtrerPeriodes.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'campagne' => $annee,
                    'lotan' => $pgProgLotAn,
                    'periodes' => $tabProgPeriodes,
        ));
    }

    public function validerFiltrerPeriodesAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'validerSelectionPeriode');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgPeriodes = $repoPgProgPeriodes->getPgProgPeriodesByAnneeTypePeriode($annee, $pgProgTypeMilieu->getTypePeriode());

        $selPeriodes = Array();
        if (isset($_POST['checkPeriode'])) {
            $selPeriodes = $_POST['checkPeriode'];
        }

        foreach ($pgProgPeriodes as $pgProgPeriode) {
            $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotanPeriode($pgProgLotAn, $pgProgPeriode);
            if ($pgProgLotPeriodeAn) {
                $trouve = 0;
                foreach ($selPeriodes as $selPeriode) {
                    $pgProgPeriode = $repoPgProgPeriodes->getPgProgPeriodesById($selPeriode);
                    if ($pgProgLotPeriodeAn->getPeriode()->getId() == $pgProgPeriode->getId()) {
                        $trouve = 1;
                        break;
                    }
                }
                if ($trouve == 0) {
                    $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
                    foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                        $pgProgLotPeriodeProgCompls = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPprogCompl($pgProgLotPeriodeProg);
                        foreach ($pgProgLotPeriodeProgCompls as $pgProgLotPeriodeProgCompl) {
                            $pgProgLotPeriodeProgCompl->setPprogCompl(null);
                            $emSqe->persist($pgProgLotPeriodeProgCompl);
                        }
                        $emSqe->remove($pgProgLotPeriodeProg);
                    }
                    $emSqe->remove($pgProgLotPeriodeAn);
                }
            }
        }
        $emSqe->flush();

        foreach ($selPeriodes as $selPeriode) {
            $pgProgPeriode = $repoPgProgPeriodes->getPgProgPeriodesById($selPeriode);
            $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotanPeriode($pgProgLotAn, $pgProgPeriode);
            if (!$pgProgLotPeriodeAn) {
                $pgProgLotPeriodeAn = new PgProgLotPeriodeAn();
                $pgProgLotPeriodeAn->setLotan($pgProgLotAn);
                $pgProgLotPeriodeAn->setPeriode($pgProgPeriode);
                $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('CRE');
                $pgProgLotPeriodeAn->setCodeStatut($pgProgStatut);
                $emSqe->persist($pgProgLotPeriodeAn);
            }
        }
        $emSqe->flush();

        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);

        $tabGrparAns = array();
        $i = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $tabGrparAns[$i]["groupe"] = $pgProgLotGrparAn;
            $i++;
        }

        $tabEnregs = $repoPgProgLotAn->getProgrammationPeriodes($pgProgLotAn->getId());

        $stationLu = null;
        $tabMessage = array();
        $tabStations = array();
        $tabPeriodes = array();
        $i = 0;
        $j = 0;
        foreach ($tabEnregs as $tabEnreg) {
            if (!$stationLu) {
                $stationLu = $tabEnreg['station_id'];
            }
            if ($stationLu != $tabEnreg['station_id']) {
                $tabStations[$i]["periodes"] = $tabPeriodes;
                $stationLu = $tabEnreg['station_id'];
                $tabPeriodes = array();
                $j = 0;
                $i++;
            }
            $tabStations[$i]["station"]["id"] = $tabEnreg['station_id'];
            $tabStations[$i]["station"]["code"] = $tabEnreg['code_station'];
            $tabStations[$i]["station"]["libelle"] = $tabEnreg['lib_station'];
            $tabStations[$i]["station"]["reseau"] = $tabEnreg['nom_rsx'];
            $tabPeriodes[$j]["ordre"] = $tabEnreg['periode_id'];
            $tabPeriodes[$j]["periode"] = $tabEnreg['label_periode'];
            $tabPeriodes[$j]["statut"] = $tabEnreg['statut_periode'];
            $tabPeriodes[$j]["nbGroupe"] = $tabEnreg['nb_pprog'];
            $tabPeriodes[$j]["autreStatut"] = $tabEnreg['statut_autres'];
            $tabPeriodes[$j]["autreProgrammation"] = $tabEnreg['nb_pprog_autres'];
            $j++;
        }
        $tabStations[$i]["periodes"] = $tabPeriodes;

        $tabControle = ProgrammationBilanController::controleProgrammationAction($pgProgLotAnId, $emSqe, $session);

        return $this->render('AeagSqeBundle:Programmation:Periode\validerFiltrerPeriodes.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'campagne' => $annee,
                    'lotan' => $pgProgLotAn,
                    'periodes' => $pgProgPeriodes,
                    'stationAns' => $tabStations,
                    'grparAns' => $tabGrparAns,
                    'controle' => $tabControle,
        ));
    }

    public function gererPeriodeAction($lotan = null) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'gererPeriode');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgRefSitePrelevement = $emSqe->getRepository('AeagSqeBundle:PgRefSitePrelevement');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);


        $tabPeriodeAns = array();
        $i = 0;
        foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
            if ($pgProgLot->getDelaiPrel()) {
                $dateFin = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
                $delai = $pgProgLot->getDelaiPrel();
                $dateFin->add(new \DateInterval('P' . $delai . 'D'));
            } else {
                $dateFin = $pgProgLotPeriodeAn->getPeriode()->getDateFin();
            }
            $pgProgLotPeriodeAn->getPeriode()->setDateFin($dateFin);
            $tabPeriodeAns[$i] = $pgProgLotPeriodeAn;
            $i++;
        }
//return new Response('');
        usort($tabPeriodeAns, create_function('$a,$b', 'return $a->getPeriode()->getNumPeriode()-$b->getPeriode()->getNumPeriode();'));

        //   \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
        //  return new Response('');

        $tabControle = ProgrammationBilanController::controleProgrammationAction($pgProgLotAnId, $emSqe, $session);

        return $this->render('AeagSqeBundle:Programmation:Periode\gererPeriode.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'lotan' => $pgProgLotAn,
                    'periodeAns' => $tabPeriodeAns,
                    'controle' => $tabControle,
        ));
    }

    public function dupliquerPeriodeAction($periodeId = null) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'dupliquerPeriode');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgRefSitePrelevement = $emSqe->getRepository('AeagSqeBundle:PgRefSitePrelevement');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $pgProgPeriodeADupliquer = $repoPgProgPeriodes->getPgProgPeriodesById($periodeId);

        $tabPeriodeAns = array();
        $i = 0;
        foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
            if ($pgProgLotPeriodeAn->getPeriode()->getId() != $periodeId) {
                $tabPeriodeAns[$i] = $pgProgLotPeriodeAn;
                $i++;
            }
        }
//return new Response('');
        usort($tabPeriodeAns, create_function('$a,$b', 'return $a->getPeriode()->getNumPeriode()-$b->getPeriode()->getNumPeriode();'));

        //   \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
        //  return new Response('');

        return $this->render('AeagSqeBundle:Programmation:Periode\dupliquerPeriode.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'lotan' => $pgProgLotAn,
                    'periode' => $pgProgPeriodeADupliquer,
                    'periodeAns' => $tabPeriodeAns,
        ));
    }

    public function validerDupliquerPeriodeAction($periodeId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'validerDupliquerPeriode');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgProgGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');
        $repoPgSandreSupport = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $pgProgPeriodeADupliquer = $repoPgProgPeriodes->getPgProgPeriodesById($periodeId);

        foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
            if ($pgProgLotPeriodeAn->getPeriode()->getId() == $periodeId) {
                $pgProgLotPeriodeAnADupliquer = $pgProgLotPeriodeAn;
                $pgProgLotPeriodeProgsADupliquer = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
                break;
            }
        }

        $selPeriodes = Array();
        if (isset($_POST['checkDupliquerPeriode'])) {
            $selPeriodes = $_POST['checkDupliquerPeriode'];
        }

        foreach ($selPeriodes as $selPeriode) {
            foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
                if ($pgProgLotPeriodeAn->getPeriode()->getId() == $selPeriode) {
                    $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
                    foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                        $pgProgLotPeriodeProgCompls = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPprogCompl($pgProgLotPeriodeProg);
                        foreach ($pgProgLotPeriodeProgCompls as $pgProgLotPeriodeProgCompl) {
                            $pgProgLotPeriodeProgCompl->setPprogCompl(null);
                            $pgProgLotPeriodeProgCompl->setStatut('N');
                            $emSqe->persist($pgProgLotPeriodeProgCompl);
                        }
                        $emSqe->remove($pgProgLotPeriodeProg);
                        $emSqe->flush();
                    }
                    foreach ($pgProgLotPeriodeProgsADupliquer as $pgProgLotPeriodeProgADupliquer) {
                        $pgProgLotPeriodeProg = clone($pgProgLotPeriodeProgADupliquer);
                        $pgProgLotPeriodeProg->setPeriodan($pgProgLotPeriodeAn);
                        $emSqe->persist($pgProgLotPeriodeProg);
                    }
                }
            }
        }
        $emSqe->flush();

        return new Response('periode dupliqué');
    }

    public function supprimerPeriodeAction($periodeId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'supprimerPeriode');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgProgGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');
        $repoPgSandreSupport = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $pgProgPeriodeASupprimer = $repoPgProgPeriodes->getPgProgPeriodesById($periodeId);

        foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
            if ($pgProgLotPeriodeAn->getPeriode()->getId() == $periodeId) {
                $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                    $pgProgLotPeriodeProgCompls = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPprogCompl($pgProgLotPeriodeProg);
                    foreach ($pgProgLotPeriodeProgCompls as $pgProgLotPeriodeProgCompl) {
                        $pgProgLotPeriodeProgCompl->setPprogCompl(null);
                        $pgProgLotPeriodeProgCompl->setStatut('N');
                        $emSqe->persist($pgProgLotPeriodeProgCompl);
                    }
                    $emSqe->remove($pgProgLotPeriodeProg);
                }
                $emSqe->remove($pgProgLotPeriodeAn);
            }
        }
        $emSqe->flush();

        return new Response('periode supprimée');
    }

    public function dupliquerStationAction($stationId = null) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'dupliquerStation');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgRefSitePrelevement = $emSqe->getRepository('AeagSqeBundle:PgRefSitePrelevement');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAnADupliquer = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);

        $tabSupports = array();
        $i = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            if ($pgProgLotGrparAn->getGrparRef()->getSupport()) {
                $tabSupports[$i] = $pgProgLotGrparAn->getGrparRef()->getSupport();
                $i++;
            }
        }

        $tabEnregs = $repoPgProgLotAn->getProgrammationPeriodes($pgProgLotAn->getId());

        $stationLu = null;
        $tabMessage = array();
        $tabStations = array();
        $tabPeriodes = array();
        $i = 0;
        $j = 0;
        foreach ($tabEnregs as $tabEnreg) {
            if (!$stationLu) {
                $stationLu = $tabEnreg['station_id'];
            }
            if ($stationLu != $tabEnreg['station_id']) {
                $tabStations[$i]["periodes"] = $tabPeriodes;
                $stationLu = $tabEnreg['station_id'];
                $tabPeriodes = array();
                $j = 0;
                $i++;
            }
            $tabStations[$i]["station"]["id"] = $tabEnreg['station_id'];
            $tabStations[$i]["station"]["code"] = $tabEnreg['code_station'];
            $tabStations[$i]["station"]["libelle"] = $tabEnreg['lib_station'];
            $tabStations[$i]["station"]["reseau"] = $tabEnreg['nom_rsx'];
            $tabPeriodes[$j]["ordre"] = $tabEnreg['periode_id'];
            $tabPeriodes[$j]["periode"] = $tabEnreg['label_periode'];
            $tabPeriodes[$j]["statut"] = $tabEnreg['statut_periode'];
            $tabPeriodes[$j]["nbGroupe"] = $tabEnreg['nb_pprog'];
            $tabPeriodes[$j]["autreStatut"] = $tabEnreg['statut_autres'];
            $tabPeriodes[$j]["autreProgrammation"] = $tabEnreg['nb_pprog_autres'];
            $j++;
        }
        $tabStations[$i]["periodes"] = $tabPeriodes;

        //   \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
        //  return new Response('');

        return $this->render('AeagSqeBundle:Programmation:Periode\dupliquerStation.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'lotan' => $pgProgLotAn,
                    'stationAn' => $pgProgLotStationAnADupliquer,
                    'stationAns' => $tabStations,
        ));
    }

    public function validerDupliquerStationAction($stationId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'validerDupliquerStation');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgProgGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');
        $repoPgSandreSupport = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnById($stationId);
        $pgProgPeriodes = $repoPgProgPeriodes->getPgProgPeriodesByAnneeTypePeriode($annee, $pgProgTypeMilieu->getTypePeriode());

// Récupération des groupes
        $tabGrparAns = array();
        $i = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $tabGrparAns[$i]["groupe"] = $pgProgLotGrparAn;
            $i++;
        }

        $selStations = Array();
        if (isset($_POST['checkDupliquerStation'])) {
            $selStations = $_POST['checkDupliquerStation'];
        }

        foreach ($selStations as $selStation) {
            $pgProgLotStationAnSelectionnee = $repoPgProgLotStationAn->getPgProgLotStationAnById($selStation);
            $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAnSelectionnee);
            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                $pgProgLotPeriodeProgCompls = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPprogCompl($pgProgLotPeriodeProg);
                foreach ($pgProgLotPeriodeProgCompls as $pgProgLotPeriodeProgCompl) {
                    $pgProgLotPeriodeProgCompl->setPprogCompl(null);
                    $pgProgLotPeriodeProgCompl->setStatut('N');
                    $emSqe->persist($pgProgLotPeriodeProgCompl);
                }
                $emSqe->remove($pgProgLotPeriodeProg);
            }
            $emSqe->flush();
            $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAn);
            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                $pgProgLotPeriodeProgSelectionne = clone($pgProgLotPeriodeProg);
                $pgProgLotPeriodeProgSelectionne->setStationAn($pgProgLotStationAnSelectionnee);
                $pgProgLotPeriodeProgSelectionne->setPprogCompl(null);
                $pgProgLotPeriodeProgSelectionne->setStatut('N');
                $pgProgLotStationAnSelectionne = $pgProgLotPeriodeProgSelectionne->getStationAn();
                $pgProgLotGrparAnSelectionne = $pgProgLotPeriodeProgSelectionne->getGrparAn();
                $pgProgLotPeriodeAnSelectionne = $pgProgLotPeriodeProgSelectionne->getPeriodan();
                $pgProgPeriodeSelectionne = $pgProgLotPeriodeProgSelectionne->getPeriodan()->getPeriode();
                $pgProgLotPeriodeAnAutres = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByPeriode($pgProgPeriodeSelectionne);
                $pgProgLotGrparAnAutres = $repoPgProgLotGrparAn->getPgProgLotGrparAnByGrpparref($pgProgLotGrparAnSelectionne->getGrparRef());
                foreach ($pgProgLotGrparAnAutres as $pgProgLotGrparAnAutre) {
                    if ($pgProgLotGrparAnSelectionne->getLotAn()->getid() != $pgProgLotGrparAnAutre->getLotAn()->getid()) {
                        if ($pgProgLotGrparAnSelectionne->getLotAn()->getAnneeProg() == $pgProgLotGrparAnAutre->getLotAn()->getAnneeProg()) {
                            foreach ($pgProgLotPeriodeAnAutres as $pgProgLotPeriodeAnAutre) {
                                if ($pgProgLotPeriodeAnAutre->getCodeStatut()->getCodeStatut() != 'INV') {
                                    if ($pgProgLotPeriodeAnAutre->getLotan()->getid() != $pgProgLotGrparAnSelectionne->getLotan()->getid()) {
                                        if ($pgProgLotPeriodeAnAutre->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                            if ($pgProgLotPeriodeAnAutre->getLotan()->getLot()->getId() != $pgProgLotGrparAnSelectionne->getLotan()->getLot()->getId()) {
                                                $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAnPeriodeAn($pgProgLotGrparAnAutre, $pgProgLotPeriodeAnAutre);
                                                foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                                                    if ($pgProgLotPeriodeProgAutre->getStationAn()->getStation()->getOuvFoncId() == $pgProgLotStationAnSelectionne->getStation()->getOuvFoncId()) {
                                                        $pgProgLotPeriodeProgSelectionne->setPprogCompl($pgProgLotPeriodeProgAutre);
                                                        $pgProgLotPeriodeProgSelectionne->setStatut('I');
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $emSqe->persist($pgProgLotPeriodeProgSelectionne);
//$emSqe->flush();
            }
        }
        $emSqe->flush();

        return new Response(json_encode(''));
    }

    public function validerDupliquerStationSurAutreStationAction($stationId = null, $autreStationId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'validerDupliquerStationSurAutreStation');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);

        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);

        $pgRefStationMesureSelectionnee = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($autreStationId);
        $pgProgLotStationAnSelectionnee = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesureSelectionnee);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAnSelectionnee);
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotPeriodeProgCompls = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPprogCompl($pgProgLotPeriodeProg);
            foreach ($pgProgLotPeriodeProgCompls as $pgProgLotPeriodeProgCompl) {
                $pgProgLotPeriodeProgCompl->setPprogCompl(null);
                $pgProgLotPeriodeProgCompl->setStatut('N');
                $emSqe->persist($pgProgLotPeriodeProgCompl);
            }
            $emSqe->remove($pgProgLotPeriodeProg);
        }
        $emSqe->flush();
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAn);
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotPeriodeProgSelectionne = clone($pgProgLotPeriodeProg);
            $pgProgLotPeriodeProgSelectionne->setStationAn($pgProgLotStationAnSelectionnee);
            $pgProgLotPeriodeProgSelectionne->setPprogCompl(null);
            $pgProgLotPeriodeProgSelectionne->setStatut('N');
            $pgProgLotStationAnSelectionne = $pgProgLotPeriodeProgSelectionne->getStationAn();
            $pgProgLotGrparAnSelectionne = $pgProgLotPeriodeProgSelectionne->getGrparAn();
            $pgProgLotPeriodeAnSelectionne = $pgProgLotPeriodeProgSelectionne->getPeriodan();
            $pgProgPeriodeSelectionne = $pgProgLotPeriodeProgSelectionne->getPeriodan()->getPeriode();

            $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgAutres($pgProgLotGrparAnSelectionne, $pgProgPeriodeSelectionne, $pgProgLotStationAnSelectionne, $pgProgLotAn, 3);

            foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                $pgProgLotPeriodeProgSelectionne->setPprogCompl($pgProgLotPeriodeProgAutre);
                $pgProgLotGrparAnSelectionne = $pgProgLotPeriodeProgSelectionne->getGrparAn();
                $prestatSelectionne = null;
                if ($pgProgLotGrparAnSelectionne->getGrparRef()->getTypeGrp() == 'ENV' || $pgProgLotGrparAnSelectionne->getGrparRef()->getTypeGrp() == 'SIT') {
                    $pgProgLotParamAnSelectionnes = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAnSelectionne);
                    foreach ($pgProgLotParamAnSelectionnes as $pgProgLotParamAnSelectionne) {
                        $prestaSelectionne = $pgProgLotParamAnSelectionne->getPrestataire();
                        break;
                    } $prestaAutre = $prestatSelectionne;
                    $pgProgLotGrparAnAutre = $pgProgLotPeriodeProgAutre->getGrparAn();
                    if ($pgProgLotGrparAnAutre->getGrparRef()->gettypeGrp() == 'ENV' || $pgProgLotGrparAnAutre->getGrparRef()->getTypeGrp() == 'SIT') {
                        $pgProgLotParamAnAutres = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAnAutre);
                        foreach ($pgProgLotParamAnAutres as $pgProgLotParamAnAutre) {
                            $prestaAutre = $pgProgLotParamAnAutre->getPrestataire();
                            break;
                        }
                    }
                    if ($prestaSelectionne->getAdrCorId() != $prestaAutre->getAdrCorId()) {
                        $pgProgLotPeriodeProgSelectionne->setStatut('I');
                    } else {
                        $pgProgLotPeriodeProgSelectionne->setStatut('C');
                    }
                } else {
                    $pgProgLotPeriodeProgSelectionne->setStatut('C');
                }
            }
            $emSqe->persist($pgProgLotPeriodeProgSelectionne);
        }
        $emSqe->flush();

        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $tabPeriodes = array();
        $j = 0;
        if ($pgProgLotPeriodeAns) {
            foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
                $tabPeriodes[$j]["ordre"] = $pgProgLotPeriodeAn->getPeriode()->getid();
                $tabPeriodes[$j]["periode"] = $pgProgLotPeriodeAn->getPeriode();
                $tabPeriodes[$j]["statut"] = $pgProgLotPeriodeAn->getCodeStatut();
                $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAnSelectionnee, $pgProgLotPeriodeAn);
                $tabPeriodes[$j]["nbGroupe"] = count($pgProgLotPeriodeProgs);
                $tabGroupes = array();
                $trouveCompl = false;
                $trouveStatut = 'N';
                $k = 0;
                //$pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAnSelectionnee);
                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                    $tabGroupes[$k] = $pgProgLotPeriodeProg;
                    if ($pgProgLotPeriodeProg->getPprogCompl()) {
                        $trouveCompl = true;
                        $trouveStatut = $pgProgLotPeriodeProg->getStatut();
                    }
                    $k++;
                }
                if (count($tabGroupes) > 0) {
                    sort($tabGroupes);
                }
                $tabPeriodes[$j]["groupes"] = $tabGroupes;
                $tabPeriodes[$j]["autreProgrammation"] = 0;
                $tabPeriodes[$j]["autreStatut"] = $trouveStatut;
                $tabGroupeAutres = array();
                $k = 0;
                $pgProgLotStationAnAutres = $repoPgProgLotStationAn->getPgProgLotStationAnByStation($pgProgLotStationAn->getStation());
                foreach ($pgProgLotStationAnAutres as $pgProgLotStationAnAutre) {
                    $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAnAutre);
                    foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                        if ($pgProgLotPeriodeProgAutre->getPeriodan()->getCodeStatut()->getCodeStatut() != 'INV') {
                            if ($pgProgLotPeriodeProgAutre->getPeriodan()->getPeriode()->getId() == $pgProgLotPeriodeAn->getPeriode()->getId()) {
                                if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getid() != $pgProgLotPeriodeAn->getLotan()->getid()) {
                                    if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                        if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getLot()->getId() != $pgProgLotPeriodeAn->getLotan()->getLot()->getId()) {
                                            foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
                                                if ($pgProgLotPeriodeProgAutre->getGrparAn()->getGrparRef()->getId() == $pgProgLotGrparAn->getGrparRef()->getId()) {
                                                    $trouve = false;
                                                    for ($l = 0; $l < count($tabGroupeAutres); $l++) {
                                                        if ($pgProgLotGrparAn->getGrparRef()->getId() == $tabGroupeAutres[$l]) {
                                                            $trouve = true;
                                                            $l = count($tabGroupeAutres) + 1;
                                                        }
                                                    }
                                                    if (!$trouve) {
                                                        $tabGroupeAutres[$k] = $pgProgLotGrparAn->getGrparRef()->getId();
                                                        $k++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if ($trouveCompl || count($tabGroupes) == 0) {
                    $tabPeriodes[$j]["autreProgrammation"] = count($tabGroupeAutres);
                }
                $j++;
            }
        } else {
            foreach ($pgProgPeriodes as $pgProgPeriode) {
                $tabPeriodes[$j]["ordre"] = $pgProgPeriode->getid();
                $tabPeriodes[$j]["periode"] = $pgProgPeriode;
                $tabPeriodes[$j]["statut"] = null;
                $tabPeriodes[$j]["nbGroupe"] = 0;
                $tabPeriodes[$j]["groupes"] = array();
                $tabPeriodes[$j]["autreProgrammation"] = 0;
                $tabPeriodes[$j]["autreStatut"] = 'N';
                $tabGroupeAutres = $this->getAutreProgrammation($repoPgProgLotPeriodeProg, $pgProgLotStationAn, $pgProgPeriode, $pgProgLotGrparAns);
                $tabPeriodes[$j]["autreProgrammation"] = count($tabGroupeAutres);
                $j++;
            }
        }
        sort($tabPeriodes);

//return new Response(json_encode($tabPeriodes));

        return $this->render('AeagSqeBundle:Programmation:Periode\validerDupliquerAutreStation.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'lotan' => $pgProgLotAn,
                    'station' => $pgProgLotStationAnSelectionnee,
                    'periodes' => $tabPeriodes,
                    'grparAns' => $pgProgLotGrparAns,));
    }

    public function initialiserAction($stationId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'initialiser');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgProgGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');
        $repoPgSandreSupport = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgPeriodes = $repoPgProgPeriodes->getPgProgPeriodesByAnneeTypePeriode($annee, $pgProgTypeMilieu->getTypePeriode());

        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAn);
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $pgProgLotPeriodeProgCompls = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPprogCompl($pgProgLotPeriodeProg);
            foreach ($pgProgLotPeriodeProgCompls as $pgProgLotPeriodeProgCompl) {
                $pgProgLotPeriodeProgCompl->setPprogCompl(null);
                $pgProgLotPeriodeProgCompl->setStatut('N');
                $emSqe->persist($pgProgLotPeriodeProgCompl);
            }
            $emSqe->remove($pgProgLotPeriodeProg);
        }
        $emSqe->flush();


        $tabPeriodes = array();
        $j = 0;
        if (count($pgProgLotPeriodeAns) > 0) {
            foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
//print_r('j : ' . $j);
                $tabPeriodes[$j]["ordre"] = $pgProgLotPeriodeAn->getPeriode()->getid();
                $tabPeriodes[$j]["periode"] = $pgProgLotPeriodeAn->getPeriode();
                $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);
                $tabPeriodes[$j]["nbGroupe"] = count($pgProgLotPeriodeProgs);
//print_r( 'nb pgProgLotPeriodeProgs : ' . count($pgProgLotPeriodeProgs));
                $tabGroupes = array();
                $k = 0;
                $trouveCompl = false;
                $trouveStatut = 'N';
                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                    $tabGroupes[$k] = $pgProgLotPeriodeProg;
                    $trouveStatut = $pgProgLotPeriodeProg->getStatut();
                    if ($pgProgLotPeriodeProg->getPprogCompl()) {
                        $trouveCompl = true;
                    }
                    $k++;
                }
//                    if (count($tabGroupes) > 0) {
//                        sort($tabGroupes);
//                    }
                $tabPeriodes[$j]["groupes"] = $tabGroupes;
                $tabPeriodes[$j]["statut"] = $trouveStatut;
                $tabPeriodes[$j]["autreProgrammation"] = 0;
                $tabGroupeAutres = array();
                $k = 0;
                $pgProgLotStationAnAutres = $repoPgProgLotStationAn->getPgProgLotStationAnByStation($pgProgLotStationAn->getStation());
                foreach ($pgProgLotStationAnAutres as $pgProgLotStationAnAutre) {
                    $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAnAutre);
                    $tabPeriodes[$j]["autreProgrammation"] = 0;
                    foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                        if ($pgProgLotPeriodeProgAutre->getPeriodan()->getCodeStatut()->getCodeStatut() != 'INV') {
                            if ($pgProgLotPeriodeProgAutre->getPeriodan()->getPeriode()->getId() == $pgProgLotPeriodeAn->getPeriode()->getId()) {
                                if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getid() != $pgProgLotPeriodeAn->getLotan()->getid()) {
                                    if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                        if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getLot()->getId() != $pgProgLotPeriodeAn->getLotan()->getLot()->getId()) {
                                            foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
                                                if ($pgProgLotPeriodeProgAutre->getGrparAn()->getGrparRef()->getId() == $pgProgLotGrparAn->getGrparRef()->getId()) {
                                                    $trouve = false;
                                                    for ($l = 0; $l < count($tabGroupeAutres); $l++) {
                                                        if ($pgProgLotGrparAn->getGrparRef()->getId() == $tabGroupeAutres[$l]) {
                                                            $trouve = true;
                                                            $l = count($tabGroupeAutres) + 1;
                                                        }
                                                    }
                                                    if (!$trouve) {
                                                        $tabGroupeAutres[$k] = $pgProgLotGrparAn->getGrparRef()->getId();
                                                        $k++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if ($trouveCompl || count($tabGroupes) == 0) {
                    $tabPeriodes[$j]["autreProgrammation"] = count($tabGroupeAutres);
                }
                $j++;
            }
        }
        sort($tabPeriodes);

        $tabGrparAns = array();
        $i = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $tabGrparAns[$i]["groupe"] = $pgProgLotGrparAn;
            $i++;
        }


        return $this->render('AeagSqeBundle:Programmation:Periode\validerInitialiserStation.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'lotan' => $pgProgLotAn,
                    'station' => $stationId,
                    'periodes' => $tabPeriodes,
                    'grparAns' => $tabGrparAns,
        ));
    }

    public function programmerAction($stationId = null, $periodeId = null) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'programmer');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgProgGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');
        $repoPgSandreSupport = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgRefSitePrelevement = $emSqe->getRepository('AeagSqeBundle:PgRefSitePrelevement');
        $repoPgProgGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgWebuserRsx = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserRsx');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgZoneGeoRef = $pgProgLot->getZgeoRef();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgPeriode = $repoPgProgPeriodes->getPgProgPeriodesById($periodeId);
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotanPeriode($pgProgLotAn, $pgProgPeriode);
        $pgRefSitePrelevements = $repoPgRefSitePrelevement->getPgRefSitePrelevementByOuvFoncId($pgProgLotStationAn->getStation()->getOuvFoncId());

        if ($session->get('critWebuser')) {
            $pgProgWebusers = $repoPgProgWebusers->getPgProgWebusersByid($session->get('critWebuser'));
        } else {
            $pgProgWebusers = $repoPgProgWebusers->getPgProgWebusersByLoginPassword($user->getUsername(), $user->getPassword());
        }

        if (count($pgProgWebusers) == 1) {
            $pgProgWebuser = $pgProgWebusers;
        } else {
            $pgProgWebuser = null;
        }

        $tabStationPeriode = array();
        $tabStationPeriode["station"] = $pgProgLotStationAn;
        $tabStationPeriode["periode"] = $pgProgLotPeriodeAn;
        $tabStationPeriode["sitePrelevemnts"] = $pgRefSitePrelevements;

        $tabgroupes = array();
        $i = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $tabgroupes[$i]["groupe"] = $pgProgLotGrparAn;
            $tabgroupes[$i]["renseigne"] = 'N';
            $tabgroupes[$i]["existe"] = 'N';
            $nbProgGrparRefLstParam = $repoPgProgGrparRefLstParam->getNbPgProgGrparRefLstParamByGrparRef($pgProgLotGrparAn->getGrparRef());
            if ($nbProgGrparRefLstParam == 0) {
                $tabgroupes[$i]["renseigne"] = 'O';
            }

            $tabgroupes[$i]["nbParametres"] = $nbProgGrparRefLstParam;
            $tabgroupes[$i]["Compl"] = 'N';
            $pgProgLotPeriodeProg = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnGrparAnPeriodeAn($pgProgLotStationAn, $pgProgLotGrparAn, $pgProgLotPeriodeAn);
            if ($pgProgLotPeriodeProg) {
                $tabgroupes[$i]["renseigne"] = 'O';
                $tabgroupes[$i]["existe"] = 'O';
                /* if ($pgProgLotPeriodeProg->getPprogCompl()) {
                  $tabgroupes[$i]["Compl"] = 'O';
                  } */

                if ($pgProgLotPeriodeProg->getStatut() == 'C') {
                    $tabgroupes[$i]["Compl"] = 'O';
                }

                $pgProgLotPeriodeProgCompls = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPprogCompl($pgProgLotPeriodeProg);
                if (count($pgProgLotPeriodeProgCompls) > 0) {
                    $tabgroupes[$i]["EstCompl"] = 'O';
                } else {
                    $tabgroupes[$i]["EstCompl"] = 'N';
                }
            } else {
                $tabgroupes[$i]["Compl"] = null;
                $tabgroupes[$i]["EstCompl"] = null;
            }
            $tabgroupes[$i]["autre"] = null;
            $tabgroupes[$i]["autreGroupe"] = null;

            $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgAutres($pgProgLotGrparAn, $pgProgLotPeriodeAn->getPeriode(), $pgProgLotStationAn, $pgProgLotAn, 3);
            foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                $trouvelot = $pgProgLotPeriodeProgAutre->getStationAn()->getlotAn();
                $tabgroupes[$i]["autre"] = $trouvelot;
                $tabgroupes[$i]["autreGroupe"] = $pgProgLotPeriodeProgAutre->getGrparAn();
            }

            $tabGrparAnSupports = array();
            $tabGrparAnSupports[0]["support"] = $pgProgLotGrparAn->getGrparRef()->getSupport();
            $tabGrparAnSupports[0]["obli"] = "N";
            $j = 1;
            $pgProgGrparObligSupports = $repoPgProgGrparObligSupport->getPgProgGrparObligSupportByGrparRefId($pgProgLotGrparAn->getGrparRef()->getId());
            foreach ($pgProgGrparObligSupports as $pgProgGrparObligSupport) {
                $pgSandreSupport = $repoPgSandreSupport->getPgSandreSupportsByCodeSupport($pgProgGrparObligSupport->getCodeSupport());
                if ($pgProgLotGrparAn->getGrparRef()->getSupport()) {
                    if ($pgSandreSupport->getCodeSupport() != $pgProgLotGrparAn->getGrparRef()->getSupport()->getCodeSupport()) {
                        $tabGrparAnSupports[$j]["support"] = $pgSandreSupport;
                        $tabGrparAnSupports[$j]["obli"] = "O";
                        $j++;
                    } else {
                        $tabGrparAnSupports[0]["obli"] = "O";
                    }
                } else {
                    $tabGrparAnSupports[$j]["support"] = $pgSandreSupport;
                    $tabGrparAnSupports[$j]["obli"] = "O";
                    $j++;
                }
            }
            for ($j = 0; $j < count($tabGrparAnSupports); $j++) {
                if ($tabGrparAnSupports[$j]["support"]) {
                    $tabGrparAnSupports[$j]["site"] = "ko";
                    foreach ($pgRefSitePrelevements as $pgRefSitePrelevement) {
                        if ($tabGrparAnSupports[$j]["support"]->getCodeSupport() == $pgRefSitePrelevement->getCodeSupport()->getCodeSupport()) {
                            $tabGrparAnSupports[$j]["site"] = "ok";
                            break;
                        }
                    }
                }
            }
            sort($tabGrparAnSupports);
            $tabgroupes[$i]["obliSupport"] = $tabGrparAnSupports;
            $i++;
        }
        sort($tabgroupes);
        usort($tabgroupes, create_function('$a,$b', 'return $a[\'groupe\']->getGrparRef()->getCodeGrp()-$b[\'groupe\']->getGrparRef()->getCodeGrp();'));
        $tabStationPeriode["groupes"] = $tabgroupes;

        // récuperation des supports
        $tabSupports = array();
        $i = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $trouve = false;
            for ($j = 0; $j < count($tabSupports); $j++) {
                if ($pgProgLotGrparAn->getGrparRef()->getSupport()) {
                    if ($tabSupports[$j]->getcodeSupport() == $pgProgLotGrparAn->getGrparRef()->getSupport()->getcodeSupport()) {
                        $trouve = true;
                        break;
                    }
                }
            }
            if (!$trouve) {
                if ($pgProgLotGrparAn->getGrparRef()->getSupport()) {
                    $tabSupports[$i] = $pgProgLotGrparAn->getGrparRef()->getSupport();
                    $i++;
                }
            }
        }

        //$pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnById($stationId);
        $pgProgPeriode = $repoPgProgPeriodes->getPgProgPeriodesById($periodeId);
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotanPeriode($pgProgLotAn, $pgProgPeriode);
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);


        // recuperatiion des reseaux de l'utilisateurs
        $tabReseauxUsers = array();
        $i = 0;
        if ($pgProgWebuser && (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE'))) {
            $pgProgWebuserRsx = $repoPgProgWebuserRsx->getPgProgWebuserRsxByWebuser($pgProgWebuser);
        } else {
            $pgProgWebuserRsx = $repoPgProgWebuserRsx->getPgProgWebuserRsx();
        }
        foreach ($pgProgWebuserRsx as $pgProgWebuserRs) {
            $trouve = false;
            for ($j = 0; $j < count($tabReseauxUsers); $j++) {
                if ($tabReseauxUsers[$j]['reseau'] == $pgProgWebuserRs->getReseauMesure()) {
                    $trouve = true;
                    $j = count($tabReseauxUsers) + 1;
                }
            }
            if ($trouve == false) {
                $reseauMesure = $pgProgWebuserRs->getReseauMesure();
                // print_r( 'categorie : ' . $reseauMesure->getCategorieMilieu() . ' pour le reseau : ' . $reseauMesure->getcodeAeagRsx() . ' ');
                if ($reseauMesure->getCategorieMilieu() == $pgProgLot->getCodeMilieu()->getCategorieMilieu()) {
                    $tabReseauxUsers[$i]['reseau'] = $reseauMesure;
                    if ($pgProgLotStationAn->getRsxId() == $reseauMesure->getGroupementId()) {
                        $tabReseauxUsers[$i]['cocher'] = 'O';
                    } else {
                        $tabReseauxUsers[$i]['cocher'] = 'N';
                    }
                    $i++;
                }
            }
        }

        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $trouve = false;
            for ($i = 0; $i < count($tabReseauxUsers); $i++) {
                if ($pgProgLotPeriodeProg->getRsxId() == $tabReseauxUsers[$i]['reseau']->getGroupementId()) {
                    $tabReseauxUsers[$i]['cocher'] = 'O';
                    $trouve = true;
                } else {
                    $tabReseauxUsers[$i]['cocher'] = 'N';
                }
            }
            if ($trouve) {
                break;
            }
        }


        //var_dump($tabgroupes);

        return $this->render('AeagSqeBundle:Programmation:Periode\programmer.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'lotan' => $pgProgLotAn,
                    'autrePeriodes' => $pgProgLotPeriodeAns,
                    'periode' => $pgProgLotPeriodeAn,
                    'station' => $pgProgLotStationAn,
                    'groupes' => $tabgroupes,
                    'supports' => $tabSupports,
                    'reseaux' => $tabReseauxUsers,
        ));
    }

    public function validerAction($stationId = null, $periodeId = null, $optionGroupes = null) {
        $logger = $this->get('logger');

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'valider');
        //$em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgProgGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');
        $repoPgSandreSupport = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');
        //$annee = $session->get('critAnnee');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $pgProgPeriodes = $repoPgProgPeriodes->getPgProgPeriodesByAnneeTypePeriode($annee, $pgProgTypeMilieu->getTypePeriode());
        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($stationId);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
        $pgProgPeriode = $repoPgProgPeriodes->getPgProgPeriodesById($periodeId);

        $selGroupes = Array();
        if (!empty($_POST['checkGroupe'])) {
            $selGroupes = $_POST['checkGroupe'];
            $ok = 'ok';
        } else {
            $tabMessage[$i] = 'Sélectionner au moins un groupe pour la station : ' . $pgProgLotStationAn->getStation()->getNumero() . ' et la période : ' . $pgProgPeriode->getLabelperiode() . '  svp.';
            $i++;
        }

        $selReseau = null;
        if (!empty($_POST['reseau'])) {
            $selReseau = $_POST['reseau'];
            $ok = 'ok';
        } else {
            $selReseau = $pgProgLotStationAn->getRsxId();
        }

        $selAutrePeriodeAns = Array();
        $i = 0;
        if (!empty($_POST['autrePeriodes'])) {
            $selAutrePeriodeAns = $_POST['autrePeriodes'];
        }

        $tabStations = Array();
        $tabGrparAns = Array();

        $this->createProgrammation($emSqe, $optionGroupes, $repoPgProgLotAn, $repoPgProgLotStationAn, $repoPgRefReseauMesure, $repoPgProgLotPeriodeProg, $repoPgProgLotPeriodeAn, $repoPgProgLotGrparAn, $pgProgLotStationAn, $pgProgPeriode, $pgProgLotGrparAns, $pgProgLotAn, $pgProgLotPeriodeAns, $selAutrePeriodeAns, $selGroupes, $selReseau, $tabStations, $tabGrparAns, $logger);


        // Update PgProgLotAn
        $nbPeriodes = 0;
        foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
            $nbPgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->countPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
            $nbPeriodes = $nbPeriodes + $nbPgProgLotPeriodeProgs;
        }
        if ($nbPeriodes == 0) {
            $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P10');
        } else {
            $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P15');
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
        return $this->render('AeagSqeBundle:Programmation:Periode\valider.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'lotan' => $pgProgLotAn,
                    'periodes' => $pgProgPeriodes,
                    'stationAns' => $tabStations,
                    'grparAns' => $tabGrparAns,
        ));
    }

    private function createProgrammation($emSqe, $optionGroupes, $repoPgProgLotAn, $repoPgProgLotStationAn, $repoPgRefReseauMesure, $repoPgProgLotPeriodeProg, $repoPgProgLotPeriodeAn, $repoPgProgLotGrparAn, $pgProgLotStationAn, $pgProgPeriode, $pgProgLotGrparAns, $pgProgLotAn, $pgProgLotPeriodeAns, $selAutrePeriodeAns, $selGroupes, $selReseau, &$tabStations, &$tabGrparAns, $logger) {

        if (!$optionGroupes) {
            $optionGroupes = 'N';
        }

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotanPeriode($pgProgLotAn, $pgProgPeriode);
        if ($pgProgLotPeriodeAn) {
            $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);
            foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                $pgProgLotPeriodeProgCompls = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPprogCompl($pgProgLotPeriodeProg);
                foreach ($pgProgLotPeriodeProgCompls as $pgProgLotPeriodeProgCompl) {
                    $pgProgLotPeriodeProgCompl->setPprogCompl(null);
                    $pgProgLotPeriodeProgCompl->setStatut('N');
                    $emSqe->persist($pgProgLotPeriodeProgCompl);
                }
                $emSqe->remove($pgProgLotPeriodeProg);
            }
            $emSqe->flush();
        }

        if (count($selAutrePeriodeAns) > 0) {
            for ($i = 0; $i < count($selAutrePeriodeAns); $i++) {
                if ($selAutrePeriodeAns[$i]) {
                    $autrePeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($selAutrePeriodeAns[$i]);
                    if ($autrePeriodeAn) {
                        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $autrePeriodeAn);
                        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                            $pgProgLotPeriodeProgCompls = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPprogCompl($pgProgLotPeriodeProg);
                            foreach ($pgProgLotPeriodeProgCompls as $pgProgLotPeriodeProgCompl) {
                                $pgProgLotPeriodeProgCompl->setPprogCompl(null);
                                $pgProgLotPeriodeProgCompl->setStatut('N');
                                $emSqe->persist($pgProgLotPeriodeProgCompl);
                            }
                            $emSqe->remove($pgProgLotPeriodeProg);
                        }
                        $emSqe->flush();
                    }
                }
            }
        }

        foreach ($selGroupes as $selGroupe) {
            $pgProgLotGrparAn = $repoPgProgLotGrparAn->getPgProgLotGrparAnById($selGroupe);
            $pgProgLotPeriodeProg = new PgProgLotPeriodeProg();
            $pgProgLotPeriodeProg->setStationAn($pgProgLotStationAn);
            $pgProgLotPeriodeProg->setGrparAn($pgProgLotGrparAn);
            $pgProgLotPeriodeProg->setPeriodAn($pgProgLotPeriodeAn);
            if ($selReseau) {
                $pgProgLotPeriodeProg->setRsxId($selReseau);
            }

            $trouve = false;
            if ($optionGroupes != 'N') {
                $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgAutres($pgProgLotGrparAn, $pgProgLotPeriodeAn->getPeriode(), $pgProgLotStationAn, $pgProgLotAn, 3);
                foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                    $pgProgLotPeriodeProg->setPprogCompl($pgProgLotPeriodeProgAutre);
                    $pgProgLotPeriodeProg->setStatut($optionGroupes);
                }
            } else {
                $pgProgLotPeriodeProg->setStatut('N');
                $pgProgLotPeriodeProg->setPprogCompl(null);
            }

            if (!$pgProgLotPeriodeProg->getPprogCompl()) {
                $pgProgLotPeriodeProg->setStatut('N');
            }

            $emSqe->persist($pgProgLotPeriodeProg);

            // VGU : Pour les autres périodes
            if (count($selAutrePeriodeAns) > 0) {
                for ($i = 0; $i < count($selAutrePeriodeAns); $i++) {

                    if ($selAutrePeriodeAns[$i]) {

                        $autrePeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($selAutrePeriodeAns[$i]);
                        $autrePeriodeAns = array($autrePeriodeAn);
                        $autreTabStations = array();
                        $autreTabGrparAns = array();
                        $this->createProgrammation($emSqe, $optionGroupes, $repoPgProgLotAn, $repoPgProgLotStationAn, $repoPgRefReseauMesure, $repoPgProgLotPeriodeProg, $repoPgProgLotPeriodeAn, $repoPgProgLotGrparAn, $pgProgLotStationAn, $autrePeriodeAn->getPeriode(), $pgProgLotGrparAns, $pgProgLotAn, $autrePeriodeAns, array(), $selGroupes, $selReseau, $autreTabStations, $autreTabGrparAns, $logger);
                    }
                }
            }
        }
        // return new Response ('option : ' . $optionGroupes );
        $emSqe->flush();

        // Récupération des support obligatoire
        //$tabGrparAns = array();
        $i = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $tabGrparAns[$i]["groupe"] = $pgProgLotGrparAn;
            $i++;
        }


        //$tabStations = array();
        $i = 0;
        $tabStations[$i]["station"] = $pgProgLotStationAn;
        if ($pgProgLotStationAn->getRsxId()) {
            $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
        } else {
            $pgRefReseauMesure = null;
        }
        $tabStations[$i]["reseau"] = $pgRefReseauMesure;
        $tabPeriodes = array();
        $j = 0;

        if (count($pgProgLotPeriodeAns) > 0) {
            foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
                //print_r('j : ' . $j);
                $tabPeriodes[$j]["ordre"] = $pgProgLotPeriodeAn->getPeriode()->getid();
                $tabPeriodes[$j]["periode"] = $pgProgLotPeriodeAn->getPeriode();
                $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);
                $tabPeriodes[$j]["nbGroupe"] = count($pgProgLotPeriodeProgs);
                //print_r( 'nb pgProgLotPeriodeProgs : ' . count($pgProgLotPeriodeProgs));
                $tabGroupes = array();
                $k = 0;
                $trouveCompl = false;
                $trouveStatut = 'N';
                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                    $tabGroupes[$k] = $pgProgLotPeriodeProg;
                    $trouveStatut = $pgProgLotPeriodeProg->getStatut();
                    if ($pgProgLotPeriodeProg->getPprogCompl()) {
                        $trouveCompl = true;
                    }
                    $k++;
                }
                //                    if (count($tabGroupes) > 0) {
                //                        sort($tabGroupes);
                //                    }
                $tabPeriodes[$j]["groupes"] = $tabGroupes;
                $tabPeriodes[$j]["statut"] = $trouveStatut;
                $tabPeriodes[$j]["autreProgrammation"] = 0;
                $tabGroupeAutres = array();
                $k = 0;
                $pgProgLotStationAnAutres = $repoPgProgLotStationAn->getPgProgLotStationAnByStation($pgProgLotStationAn->getStation());
                foreach ($pgProgLotStationAnAutres as $pgProgLotStationAnAutre) {
                    $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAnAutre);
                    $tabPeriodes[$j]["autreProgrammation"] = 0;
                    foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                        if ($pgProgLotPeriodeProgAutre->getPeriodan()->getCodeStatut()->getCodeStatut() != 'INV') {
                            if ($pgProgLotPeriodeProgAutre->getPeriodan()->getPeriode()->getId() == $pgProgLotPeriodeAn->getPeriode()->getId()) {
                                if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getid() != $pgProgLotPeriodeAn->getLotan()->getid()) {
                                    if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                        if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getLot()->getId() != $pgProgLotPeriodeAn->getLotan()->getLot()->getId()) {
                                            foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
                                                if ($pgProgLotPeriodeProgAutre->getGrparAn()->getGrparRef()->getId() == $pgProgLotGrparAn->getGrparRef()->getId()) {
                                                    $trouve = false;
                                                    for ($l = 0; $l < count($tabGroupeAutres); $l++) {
                                                        if ($pgProgLotGrparAn->getGrparRef()->getId() == $tabGroupeAutres[$l]) {
                                                            $trouve = true;
                                                            $l = count($tabGroupeAutres) + 1;
                                                        }
                                                    }
                                                    if (!$trouve) {
                                                        $tabGroupeAutres[$k] = $pgProgLotGrparAn->getGrparRef()->getId();
                                                        $k++;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if ($trouveCompl || count($tabGroupes) == 0) {
                    $tabPeriodes[$j]["autreProgrammation"] = count($tabGroupeAutres);
                }
                $j++;
            }
        }
        sort($tabPeriodes);
        $tabStations[$i]["periodes"] = $tabPeriodes;
        $i++;
        usort($tabStations, create_function('$a,$b', 'return $a[\'station\']->getStation()->getCode()-$b[\'station\']->getStation()->getCode();'));
    }

    private function getAutreProgrammation($repoPgProgLotPeriodeProg, $pgProgLotStationAn, $pgProgPeriode, $pgProgLotGrparAns) {
        $tabGroupeAutres = array();
        $k = 0;
        $pgProgLotPeriodeProgAutresGroupesRef = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgAutresGroupesRef($pgProgLotStationAn, $pgProgPeriode, $pgProgLotGrparAns, 3);
        foreach ($pgProgLotPeriodeProgAutresGroupesRef as $pgProgLotPeriodeProgAutreGroupeRef) {
            $tabGroupeAutres[$k] = $pgProgLotPeriodeProgAutreGroupeRef->getId();
            $k++;
        }
        return $tabGroupeAutres;
    }

    public function autreProgrammationAction($stationId = null, $periodeId = null, $groupeId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'autreProgrammation');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnById($stationId);
        $pgProgPeriode = $repoPgProgPeriodes->getPgProgPeriodesById($periodeId);
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotanPeriode($pgProgLotAn, $pgProgPeriode);
        $pgProgLotGrparAn = $repoPgProgLotGrparAn->getPgProgLotGrparAnById($groupeId);
        $trouve = false;
        $pgProgLotGrparAnAutres = $repoPgProgLotGrparAn->getPgProgLotGrparAnByGrpparref($pgProgLotGrparAn->getGrparRef());
        foreach ($pgProgLotGrparAnAutres as $pgProgLotGrparAnAutre) {
            if ($pgProgLotGrparAn->getLotAn()->getid() != $pgProgLotGrparAnAutre->getLotAn()->getid()) {
                if ($pgProgLotGrparAn->getLotAn()->getAnneeProg() == $pgProgLotGrparAnAutre->getLotAn()->getAnneeProg()) {
                    $pgProgLotPeriodeAnAutres = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByPeriode($pgProgPeriode);
                    foreach ($pgProgLotPeriodeAnAutres as $pgProgLotPeriodeAnAutre) {
                        if ($pgProgLotPeriodeAnAutre->getCodeStatut()->getCodeStatut() != 'INV') {
                            if ($pgProgLotPeriodeAnAutre->getPeriode()->getId() == $pgProgLotPeriodeAn->getPeriode()->getId()) {
                                if ($pgProgLotPeriodeAnAutre->getLotan()->getid() != $pgProgLotPeriodeAn->getLotan()->getid()) {
                                    if ($pgProgLotPeriodeAnAutre->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                        if ($pgProgLotPeriodeAnAutre->getLotan()->getLot()->getId() != $pgProgLotPeriodeAn->getLotan()->getLot()->getId()) {
                                            $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAnPeriodeAn($pgProgLotGrparAnAutre, $pgProgLotPeriodeAnAutre);
                                            foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                                                if ($pgProgLotPeriodeProgAutre->getStationAn()->getStation()->getOuvFoncId() == $pgProgLotStationAn->getStation()->getOuvFoncId()) {
                                                    $trouve = true;
                                                    $trouvelot = $pgProgLotPeriodeProgAutre->getStationAn()->getlotAn();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }



        $tabMessage = array();
        $html = $pgProgLotGrparAn->getGrparRef()->getCodeGrp() . '  ' . $pgProgLotGrparAn->getGrparRef()->getLibelleGrp();
        if ($trouve) {
            //$html = $html . 'inclus dans la programmation ' . $trouvelot->getanneeProg() . ' du lot ' . $trouvelot->getLot()->getNomLot();
            $tabMessage[0] = 'ko';
            $tabMessage[1] = $html;
            $tabMessage[2] = $trouvelot;
        } else {
            $tabMessage[0] = 'ok';
            $tabMessage[1] = $html;
            $tabMessage[2] = null;
        }
        return new Response(json_encode($tabMessage));
    }

    public function bilanAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'bilan');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgProgGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');
        $repoPgSandreSupport = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgPeriodes = $repoPgProgPeriodes->getPgProgPeriodesByAnneeTypePeriode($annee, $pgProgTypeMilieu->getTypePeriode());
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);


        $tabCtrlPeriodes = array();
        $i = 0;
        foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
            $tabCtrlPeriodes[$i]["ordre"] = $pgProgLotPeriodeAn->getPeriode()->getid();
            $tabCtrlPeriodes[$i]["periode"] = $pgProgLotPeriodeAn->getPeriode();
            $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
            if (count($pgProgLotPeriodeProgs)) {
                $tabCtrlPeriodes[$i]["renseigner"] = "O";
            } else {
                $tabCtrlPeriodes[$i]["renseigner"] = "N";
            }
            $i++;
        }
        sort($tabCtrlPeriodes);

        $tabCtrlStations = array();
        $i = 0;
        foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
            $tabCtrlStations[$i]["ordre"] = $pgProgLotStationAn->getStation()->getCode();
            $tabCtrlStations[$i]["station"] = $pgProgLotStationAn->getStation();
            if ($pgProgLotStationAn->getRsxId()) {
                $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
            } else {
                $pgRefReseauMesure = null;
            }
            $tabCtrlStations[$i]["reseau"] = $pgRefReseauMesure;
            $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAn);
            if (count($pgProgLotPeriodeProgs)) {
                $tabCtrlStations[$i]["renseigner"] = "O";
            } else {
                $tabCtrlStations[$i]["renseigner"] = "N";
            }
            $i++;
        }
        sort($tabCtrlStations);

        $tabCtrlGroupes = array();
        $i = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $tabCtrlGroupes[$i]["ordre"] = $pgProgLotGrparAn->getGrparRef()->getCodeGrp();
            $tabCtrlGroupes[$i]["groupe"] = $pgProgLotGrparAn->getGrparRef();
            $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAn($pgProgLotGrparAn);
            if (count($pgProgLotPeriodeProgs)) {
                $tabCtrlGroupes[$i]["renseigner"] = "O";
            } else {
                $tabCtrlGroupes[$i]["renseigner"] = "N";
            }
            $i++;
        }
        sort($tabCtrlGroupes);



        $session->set('niveau6', $this->generateUrl('AeagSqeBundle_programmation_periodes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));

        return $this->render('AeagSqeBundle:Programmation:Periode\bilan.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'lotan' => $pgProgLotAn,
                    'ctrlPeriodes' => $tabCtrlPeriodes,
                    'ctrlStations' => $tabCtrlStations,
                    'ctrlGroupes' => $tabCtrlGroupes,
        ));
    }

    public function telechargerAction($lotan = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
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



        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgPeriodes = $repoPgProgPeriodes->getPgProgPeriodesByAnneeTypePeriode($annee, $pgProgTypeMilieu->getTypePeriode());
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
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
            $tabPeriodes = array();
            $j = 0;
//print_r('nb pgProgLotPeriodeAns : ' . count($pgProgLotPeriodeAns));
            if (count($pgProgLotPeriodeAns) > 0) {
                foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
//print_r('j : ' . $j);
                    $tabPeriodes[$j]["ordre"] = $pgProgLotPeriodeAn->getPeriode()->getid();
                    $tabPeriodes[$j]["periode"] = $pgProgLotPeriodeAn->getPeriode();
                    $tabPeriodes[$j]["statut"] = $pgProgLotPeriodeAn->getCodeStatut();
                    $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);
                    asort($pgProgLotPeriodeProgs);
                    $tabPeriodes[$j]["nbGroupe"] = count($pgProgLotPeriodeProgs);
//print_r('stationan : ' . $pgProgLotStationAn->getid() . ' peridean : ' . $pgProgLotPeriodeAn->getid() .   'nb pgProgLotPeriodeProgs : ' . count($pgProgLotPeriodeProgs). '    ');
                    $tabGroupes = array();
                    $k = 0;
                    foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                        $tabGroupes[$k]['groupe'] = $pgProgLotPeriodeProg->getGrparAn();
                        $tabGroupes[$k]['statut'] = 'I';
                        if ($tabGroupes[$k]['groupe']->getGrparRef()->getSupport()) {
                            $tabGroupes[$k]['support'] = $tabGroupes[$k]['groupe']->getGrparRef()->getSupport()->getNomSupport();
                        } else {
                            $tabGroupes[$k]['support'] = null;
                        }
//                        $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotPeriodeProg->getGrparAn());
//                        asort($pgProgLotParamAns);
//                        $tabParametres = array();
//                        $p = 0;
//                        foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
//                            $tabParametres[$p]['parametre'] = $pgProgLotParamAn;
//                            if ($pgProgLotParamAn->getCodeFraction()) {
//                                $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
//                                $tabParametres[$p]['fraction'] = $pgSandreFraction->getNomFraction();
//                            } else {
//                                $tabParametres[$p]['fraction'] = null;
//                            }
//                            if ($pgProgLotParamAn->getCodeUnite()) {
//                                $pgSandreUnite = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($pgProgLotParamAn->getCodeUnite());
//                                $tabParametres[$p]['unite'] = $pgSandreUnite->getNomUnite();
//                            } else {
//                                $tabParametres[$p]['unite'] = null;
//                            }
//                            $p++;
//                        }
//                        $tabGroupes[$k]['parametres'] = $tabParametres;
                        $k++;
                    }
                    $pgProgLotStationAnAutres = $repoPgProgLotStationAn->getPgProgLotStationAnByStation($pgProgLotStationAn->getStation());
                    foreach ($pgProgLotStationAnAutres as $pgProgLotStationAnAutre) {
                        $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAnAutre);
                        foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                            if ($pgProgLotPeriodeProgAutre->getPeriodan()->getCodeStatut()->getCodeStatut() != 'INV') {
                                if ($pgProgLotPeriodeProgAutre->getPeriodan()->getPeriode()->getId() == $pgProgLotPeriodeAn->getPeriode()->getId()) {
                                    if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getid() != $pgProgLotPeriodeAn->getLotan()->getid()) {
                                        if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                            if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getLot()->getId() != $pgProgLotPeriodeAn->getLotan()->getLot()->getId()) {
                                                if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getLot()->getCodeMilieu() == $pgProgLotPeriodeAn->getLotan()->getLot()->getCodeMilieu()) {
                                                    foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
                                                        if ($pgProgLotPeriodeProgAutre->getGrparAn()->getGrparRef()->getId() == $pgProgLotGrparAn->getGrparRef()->getId()) {
                                                            $trouve = false;
                                                            for ($l = 0; $l < count($tabGroupes); $l++) {
                                                                if ($pgProgLotGrparAn->getGrparRef()->getId() == $tabGroupes[$l]['groupe']->getGrparRef()->getId()) {
                                                                    $trouve = true;
                                                                    break;
                                                                }
                                                            }
                                                            if (!$trouve) {
                                                                $tabGroupes[$k]['groupe'] = $pgProgLotGrparAn;
                                                                $tabGroupes[$k]['statut'] = 'C';
                                                                if ($tabGroupes[$k]['groupe']->getGrparRef()->getSupport()) {
                                                                    $tabGroupes[$k]['support'] = $tabGroupes[$k]['groupe']->getGrparRef()->getSupport()->getNomSupport();
                                                                } else {
                                                                    $tabGroupes[$k]['support'] = null;
                                                                }
//                                                                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
//                                                                asort($pgProgLotParamAns);
//                                                                $tabParametres = array();
//                                                                $p = 0;
//                                                                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
//                                                                    $tabParametres[$p]['parametre'] = $pgProgLotParamAn;
//                                                                    if ($pgProgLotParamAn->getCodeFraction()) {
//                                                                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
//                                                                        $tabParametres[$p]['fraction'] = $pgSandreFraction->getNomFraction();
//                                                                    } else {
//                                                                        $tabParametres[$p]['fraction'] = null;
//                                                                    }
//                                                                    if ($pgProgLotParamAn->getCodeUnite()) {
//                                                                        $pgSandreUnite = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($pgProgLotParamAn->getCodeUnite());
//                                                                        $tabParametres[$p]['unite'] = $pgSandreUnite->getNomUnite();
//                                                                    } else {
//                                                                        $tabParametres[$p]['unite'] = null;
//                                                                    }
//                                                                    $p++;
//                                                                }
//                                                                $tabGroupes[$k]['parametres'] = $tabParametres;
                                                                $k++;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    asort($tabGroupes);
                    $tabPeriodes[$j]["groupes"] = $tabGroupes;
                    $j++;
                }
            } else {
                foreach ($pgProgPeriodes as $pgProgPeriode) {
                    $tabPeriodes[$j]["ordre"] = $pgProgPeriode->getid();
                    $tabPeriodes[$j]["periode"] = $pgProgPeriode;
                    $tabPeriodes[$j]["nbGroupe"] = 0;
                    $tabPeriodes[$j]["groupes"] = array();
                    $tabPeriodes[$j]["statut"] = null;
                    $tabGroupes = array();
                    $k = 0;
                    $pgProgLotStationAnAutres = $repoPgProgLotStationAn->getPgProgLotStationAnByStation($pgProgLotStationAn->getStation());
                    asort($pgProgLotStationAnAutres);
                    foreach ($pgProgLotStationAnAutres as $pgProgLotStationAnAutre) {
                        $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAnAutre);
                        asort($pgProgLotPeriodeProgAutres);
                        foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                            if ($pgProgLotPeriodeProgAutre->getPeriodan()->getCodeStatut()->getCodeStatut() != 'INV') {
                                if ($pgProgLotPeriodeProgAutre->getPeriodan()->getPeriode()->getId() == $pgProgPeriode->getid()) {
                                    if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getid() != $pgProgLotStationAn->getLotan()->getid()) {
                                        if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                            if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getLot()->getId() != $pgProgLotStationAn->getLotan()->getLot()->getId()) {
                                                if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getLot()->getCodeMilieu() == $pgProgLotPeriodeAn->getLotan()->getLot()->getCodeMilieu()) {
                                                    foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
                                                        if ($pgProgLotPeriodeProgAutre->getGrparAn()->getGrparRef()->getId() == $pgProgLotGrparAn->getGrparRef()->getId()) {
                                                            $trouve = false;
                                                            for ($l = 0; $l < count($tabGroupes); $l++) {
                                                                if ($pgProgLotGrparAn->getGrparRef()->getId() == $tabGroupeAutres[$l]['groupe']->getGrparRef()->getId()) {
                                                                    $trouve = true;
                                                                    break;
                                                                }
                                                            }
                                                            if (!$trouve) {
                                                                $tabGroupeAutres[$k]['groupe'] = $pgProgLotGrparAn;
                                                                $tabGroupes[$k]['statut'] = 'C';
                                                                if ($tabGroupes[$k]['groupe']->getGrparRef()->getSupport()) {
                                                                    $tabGroupes[$k]['support'] = $tabGroupes[$k]['groupe']->getGrparRef()->getSupport()->getNomSupport();
                                                                } else {
                                                                    $tabGroupes[$k]['support'] = null;
                                                                }
//                                                                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
//                                                                asort($pgProgLotParamAns);
//                                                                $tabParametres = array();
//                                                                $p = 0;
//                                                                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
//                                                                    $tabParametres[$p]['parametre'] = $pgProgLotParamAn;
//                                                                    if ($pgProgLotParamAn->getCodeFraction()) {
//                                                                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgLotParamAn->getCodeFraction());
//                                                                        $tabParametres[$p]['fraction'] = $pgSandreFraction->getNomFraction();
//                                                                    } else {
//                                                                        $tabParametres[$p]['fraction'] = null;
//                                                                    }
//                                                                    if ($pgProgLotParamAn->getCodeUnite()) {
//                                                                        $pgSandreUnite = $repoPgSandreUnites->getPgSandreUnitesByCodeUnite($pgProgLotParamAn->getCodeUnite());
//                                                                        $tabParametres[$p]['unite'] = $pgSandreUnite->getNomUnite();
//                                                                    } else {
//                                                                        $tabParametres[$p]['unite'] = null;
//                                                                    }
//                                                                    $p++;
//                                                                }
//                                                                $tabGroupes[$k]['parametres'] = $tabParametres;
                                                                $k++;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    asort($tabGroupes);
                    $tabPeriodes[$j]["groupes"] = $tabGroupes;
                    $j++;
                }
            }
            usort($tabPeriodes, create_function('$a,$b', 'return $a[\'ordre\']-$b[\'ordre\'];'));
            $tabStations[$i]["periodes"] = $tabPeriodes;
            $i++;
        }


//         \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//         return new Response ('');


        $chemin = '/base/extranet/Transfert/Sqe/csv';
        $fichier = 'periodes-programmation-' . $pgProgLotAn->getAnneeProg() . '-' . $pgProgLotAnId . '.csv';
        $fullFileName = $chemin . '/' . $fichier;
        $ext = strtolower(pathinfo($fullFileName, PATHINFO_EXTENSION));
        if (file_exists($fullFileName)) {
            unlink($fullFileName);
        }
        $fichier_csv = fopen($fullFileName, 'w+');
        // Entete
        $ligne = array('Programmation', 'Version', 'Lot',
            'Station', 'Libellé', 'Commune', 'Masse d\'eau', 'Rivière', 'Réseau',
            'Période', 'Date début', 'Date fin',
            'Groupe', 'Libellé', 'Type', 'Support', 'Prestataire');
//            'Parametre', 'libellé', 'Fraction', 'Unité', 'Prestataire');
        for ($i = 0; $i < count($ligne); $i++) {
            $ligne[$i] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$i]);
        }
        fputcsv($fichier_csv, $ligne, ';');

        for ($i = 0; $i < count($tabStations); $i++) {
            for ($j = 0; $j < count($tabStations[$i]['periodes']); $j++) {
                for ($k = 0; $k < count($tabStations[$i]['periodes'][$j]['groupes']); $k++) {
//                    for ($l = 0; $l < count($tabStations[$i]['periodes'][$j]['groupes'][$k]['parametres']); $l++) {
                    if ($tabStations[$i]['periodes'][$j]['groupes'][$k]['groupe']->getPrestaDft()) {
                        $ligne = array($pgProgLotAn->getAnneeProg(),
                            $pgProgLotAn->getversion(),
                            $pgProgLotAn->getLot()->getNomLot(),
                            $tabStations[$i]['station']['code'],
                            $tabStations[$i]['station']['libelle'],
                            $tabStations[$i]['commune']['libelle'],
                            $tabStations[$i]['station']['codeMasdo'],
                            $tabStations[$i]['station']['nomCoursEau'],
                            $tabStations[$i]['reseau']->getNomRsx(),
                            $tabStations[$i]['periodes'][$j]['periode']->getLabelPeriode(),
                            $tabStations[$i]['periodes'][$j]['periode']->getDateDeb()->format('d/m/Y'),
                            $tabStations[$i]['periodes'][$j]['periode']->getDateFin()->format('d/m/Y'),
                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['groupe']->getGrparRef()->getCodeGrp(),
                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['groupe']->getGrparRef()->getLibelleGrp(),
                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['groupe']->getGrparRef()->getTypeGrp(),
                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['support'],
                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['groupe']->getPrestaDft()->getNomCorres(),
//                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['parametres'][$l]['parametre']->getCodeParametre()->getCodeParametre(),
//                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['parametres'][$l]['parametre']->getCodeParametre()->getNomParametre(),
//                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['parametres'][$l]['fraction'],
//                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['parametres'][$l]['unite'],
//                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['parametres'][$l]['parametre']->getPrestataire()->getNomCorres(),
                        );
                    } else {
                        $ligne = array($pgProgLotAn->getAnneeProg(),
                            $pgProgLotAn->getversion(),
                            $pgProgLotAn->getLot()->getNomLot(),
                            $tabStations[$i]['station']['code'],
                            $tabStations[$i]['station']['libelle'],
                            $tabStations[$i]['commune']['libelle'],
                            $tabStations[$i]['station']['codeMasdo'],
                            $tabStations[$i]['station']['nomCoursEau'],
                            $tabStations[$i]['reseau']->getNomRsx(),
                            $tabStations[$i]['periodes'][$j]['periode']->getLabelPeriode(),
                            $tabStations[$i]['periodes'][$j]['periode']->getDateDeb()->format('d/m/Y'),
                            $tabStations[$i]['periodes'][$j]['periode']->getDateFin()->format('d/m/Y'),
                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['groupe']->getGrparRef()->getCodeGrp(),
                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['groupe']->getGrparRef()->getLibelleGrp(),
                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['groupe']->getGrparRef()->getTypeGrp(),
                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['support'],
//                       $tabStations[$i]['periodes'][$j]['groupes'][$k]['groupe']->getPrestaDft()->getNomCorres(),
//                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['parametres'][$l]['parametre']->getCodeParametre()->getCodeParametre(),
//                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['parametres'][$l]['parametre']->getCodeParametre()->getNomParametre(),
//                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['parametres'][$l]['fraction'],
//                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['parametres'][$l]['unite'],
//                            $tabStations[$i]['periodes'][$j]['groupes'][$k]['parametres'][$l]['parametre']->getPrestataire()->getNomCorres(),
                        );
                    }
                    for ($m = 0; $m < count($ligne); $m++) {
                        $ligne[$m] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$m]);
                    }
                    fputcsv($fichier_csv, $ligne, ';');
//                    }
                }
            }
        }

        fclose($fichier_csv);

//        \Symfony\Component\VarDumper\VarDumper::dump($tabGroupes);
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

}
