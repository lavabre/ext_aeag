<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Aeag\SqeBundle\Entity\PgProgLotPeriodeAn;
use Aeag\SqeBundle\Entity\PgProgLotPeriodeProg;
use Aeag\SqeBundle\Controller\ProgrammationBilanController;

class ProgrammationPeriodeController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
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
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgProgGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');
        $repoPgSandreSupport = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');


        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgPeriodes = $repoPgProgPeriodes->getPgProgPeriodesByAnneeTypePeriode($annee, $pgProgTypeMilieu->getTypePeriode());
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);

        foreach ($pgProgPeriodes as $pgProgPeriode) {
             if ($pgProgLot->getDelaiPrel()) {
                $dateFin = clone($pgProgPeriode->getDateDeb());
                $delai = $pgProgLot->getDelaiPrel();
                $dateFin->add(new \DateInterval('P'. $delai . 'D'));
              } else {
                $dateFin = $pgProgPeriode->getDateFin();
            }
             $pgProgPeriode->setDateFin($dateFin);
        }


        if ($action == 'P' and $maj != 'V') {
            if ($pgProgTypeMilieu->getTypePeriode()->getcodeTypePeriode() == 'SEM') {
                if (!$pgProgLotPeriodeAns) {
                    return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_filtrer_semaines', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
                }
            }
        }
        if (!$pgProgLotPeriodeAns) {
            foreach ($pgProgPeriodes as $selPeriode) {
                $pgProgPeriode = $repoPgProgPeriodes->getPgProgPeriodesById($selPeriode->getId());
                $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnBySLotanPeriode($pgProgLotAn, $pgProgPeriode);
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

        $tabMessage = array();
        $tabStations = array();
        $i = 0;
        foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
            $tabStations[$i]["station"] = $pgProgLotStationAn;
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
                    $tabPeriodes[$j]["nbGroupe"] = count($pgProgLotPeriodeProgs);
//print_r('stationan : ' . $pgProgLotStationAn->getid() . ' peridean : ' . $pgProgLotPeriodeAn->getid() .   'nb pgProgLotPeriodeProgs : ' . count($pgProgLotPeriodeProgs). '    ');
                    $tabGroupes = array();
                    $k = 0;
                    $trouveCompl = false;
                    $trouveStatut = "N";
                    foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                        $tabGroupes[$k] = $pgProgLotPeriodeProg;
                        if ($pgProgLotPeriodeProg->getPprogCompl()) {
                            $trouveCompl = true;
                            $trouveStatut = $pgProgLotPeriodeProg->getStatut();
                        }
                        $k++;
                    }
//                    if (count($tabGroupes) > 0) {
//                        sort($tabGroupes);
//                    }
                    $tabPeriodes[$j]["groupes"] = $tabGroupes;
                    $tabPeriodes[$j]["autreProgrammation"] = 0;
                    $tabPeriodes[$j]["autreStatut"] = $trouveStatut;
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
                    if ($trouveCompl or count($tabGroupes) == 0) {
                        $tabPeriodes[$j]["autreProgrammation"] = count($tabGroupeAutres);
                    }
                    $j++;
                }
            } else {
                foreach ($pgProgPeriodes as $pgProgPeriode) {
                    $tabPeriodes[$j]["ordre"] = $pgProgPeriode->getid();
                    $tabPeriodes[$j]["periode"] = $pgProgPeriode;
                    $tabPeriodes[$j]["nbGroupe"] = 0;
                    $tabPeriodes[$j]["groupes"] = array();
                    $tabPeriodes[$j]["autreProgrammation"] = 0;
                    $tabPeriodes[$j]["statut"] = null;
                    $tabPeriodes[$j]["autreStatut"] = 'N';
                    $tabGroupeAutres = array();
                    $k = 0;
                    $pgProgLotStationAnAutres = $repoPgProgLotStationAn->getPgProgLotStationAnByStation($pgProgLotStationAn->getStation());
                    foreach ($pgProgLotStationAnAutres as $pgProgLotStationAnAutre) {
                        $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAnAutre);
                        foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                            if ($pgProgLotPeriodeProgAutre->getPeriodan()->getCodeStatut()->getCodeStatut() != 'INV') {
                                if ($pgProgLotPeriodeProgAutre->getPeriodan()->getPeriode()->getId() == $pgProgPeriode->getid()) {
                                    if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getid() != $pgProgLotStationAn->getLotan()->getid()) {
                                        if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                            if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getLot()->getId() != $pgProgLotStationAn->getLotan()->getLot()->getId()) {
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
                    $tabPeriodes[$j]["autreProgrammation"] = count($tabGroupeAutres);
                    $j++;
                }
            }
            sort($tabPeriodes);
            $tabStations[$i]["periodes"] = $tabPeriodes;
            $i++;
        }
//return new Response('');
// usort($tabStations, create_function('$a,$b', 'return $a[\'station\']->getStation()->getCode()-$b[\'station\']->getStation()->getCode();'));



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
                $dateFin->add(new \DateInterval('P'. $delai . 'D'));
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
            $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnBySLotanPeriode($pgProgLotAn, $pgProgPeriode);
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
            $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnBySLotanPeriode($pgProgLotAn, $pgProgPeriode);
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

    public function filtrerAction() {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'filtrer');
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
            $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnBySLotanPeriode($pgProgLotAn, $pgProgPeriode);
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
            $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnBySLotanPeriode($pgProgLotAn, $pgProgPeriode);
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

        $tabStations = array();
        $i = 0;
        foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
            $tabStations[$i]["station"] = $pgProgLotStationAn;
            $tabPeriodes = array();
            $j = 0;
            if ($pgProgLotPeriodeAns) {
                foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
                    $tabPeriodes[$j]["ordre"] = $pgProgLotPeriodeAn->getPeriode()->getid();
                    $tabPeriodes[$j]["periode"] = $pgProgLotPeriodeAn->getPeriode();
                    $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAnPeriodeAn($pgProgLotStationAn, $pgProgLotPeriodeAn);
                    $tabPeriodes[$j]["nbGroupe"] = count($pgProgLotPeriodeProgs);
                    $tabGroupes = array();
                    $trouveCompl = false;
                    $trouveStatut = 'N';
                    $k = 0;
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
                    $tabPeriodes[$j]["autreStatut"] = $trouveStatut;
                    $tabPeriodes[$j]["autreProgrammation"] = 0;
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
                    if ($trouveCompl or count($tabGroupes) == 0) {
                        $tabPeriodes[$j]["autreProgrammation"] = count($tabGroupeAutres);
                    }
                    $j++;
                }
            } else {
                foreach ($pgProgPeriodes as $pgProgPeriode) {
                    $tabPeriodes[$j]["ordre"] = $pgProgPeriode->getid();
                    $tabPeriodes[$j]["periode"] = $pgProgPeriode;
                    $tabPeriodes[$j]["nbGroupe"] = 0;
                    $tabPeriodes[$j]["groupes"] = array();
                    $tabPeriodes[$j]["autreStatut"] = 'N';
                    $tabPeriodes[$j]["autreProgrammation"] = 0;
                    $tabGroupeAutres = array();
                    $k = 0;
                    $pgProgLotStationAnAutres = $repoPgProgLotStationAn->getPgProgLotStationAnByStation($pgProgLotStationAn->getStation());
                    foreach ($pgProgLotStationAnAutres as $pgProgLotStationAnAutre) {
                        $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAnAutre);
                        foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                            if ($pgProgLotPeriodeProgAutre->getPeriodan()->getCodeStatut()->getCodeStatut() != 'INV') {
                                if ($pgProgLotPeriodeProgAutre->getPeriodan()->getPeriode()->getId() == $pgProgPeriode->getid()) {
                                    if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getid() != $pgProgLotStationAn->getLotan()->getid()) {
                                        if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                            if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getLot()->getId() != $pgProgLotStationAn->getLotan()->getLot()->getId()) {
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
                    $tabPeriodes[$j]["autreProgrammation"] = count($tabGroupeAutres);
                    $j++;
                }
            }
            sort($tabPeriodes);
            $tabStations[$i]["periodes"] = $tabPeriodes;
            $i++;
        }
// usort($tabStations, create_function('$a,$b', 'return $a[\'station\']->getStation()->getCode()-$b[\'station\']->getStation()->getCode();'));


        return $this->render('AeagSqeBundle:Programmation:Periode\filtrer.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'campagne' => $annee,
                    'lotan' => $pgProgLotAn,
                    'periodes' => $pgProgPeriodes,
                    'stationAns' => $tabStations,
                    'grparAns' => $pgProgLotGrparAns,
        ));
    }

    public function dupliquerStationAction($stationId = null) {

        $user = $this->getUser();
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

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $pgProgLotStationAnADupliquer = $repoPgProgLotStationAn->getPgProgLotStationAnById($stationId);

        $tabSupports = array();
        $i = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            if ($pgProgLotGrparAn->getGrparRef()->getSupport()) {
                $tabSupports[$i] = $pgProgLotGrparAn->getGrparRef()->getSupport();
                $i++;
            }
        }

        $tabStations = array();
        $i = 0;

        $site = 'ko';
        foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
            $pgRefSitePrelevements = $repoPgRefSitePrelevement->getPgRefSitePrelevementByOuvFoncId($pgProgLotStationAn->getStation()->getOuvFoncId());
            for ($j = 0; $j < count($tabSupports); $j++) {
                $site = 'ko';
                if (!$pgRefSitePrelevements) {
                    $site = 'ok';
                } else {
                    foreach ($pgRefSitePrelevements as $pgRefSitePrelevement) {
                        if ($pgRefSitePrelevement->getCodeSupport()) {
                            if ($tabSupports[$j]->getCodeSupport() == $pgRefSitePrelevement->getCodeSupport()->getCodeSupport()) {
                                $site = 'ok';
                                break;
                            }
                        }
                    }    
                }
            }
            if ($site == 'ok') {
                $tabStations[$i]["station"] = $pgProgLotStationAn;
                $tabPeriodes = array();
                $j = 0;
//print_r('nb pgProgLotPeriodeAns : ' . count($pgProgLotPeriodeAns));
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
                        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                            $tabGroupes[$k] = $pgProgLotPeriodeProg;
                            if ($pgProgLotPeriodeProg->getPprogCompl()) {
                                $trouveCompl = true;
                            }
                            $k++;
                        }
                        if (count($tabGroupes) > 0) {
                            sort($tabGroupes);
                        }
                        $tabPeriodes[$j]["groupes"] = $tabGroupes;
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
                        if ($trouveCompl or count($tabGroupes) == 0) {
                            $tabPeriodes[$j]["autreProgrammation"] = count($tabGroupeAutres);
                        }
                        $j++;
                    }
                } else {
                    foreach ($pgProgPeriodes as $pgProgPeriode) {
                        $tabPeriodes[$j]["ordre"] = $pgProgPeriode->getid();
                        $tabPeriodes[$j]["periode"] = $pgProgPeriode;
                        $tabPeriodes[$j]["nbGroupe"] = 0;
                        $tabPeriodes[$j]["groupes"] = array();
                        $tabPeriodes[$j]["autreProgrammation"] = 0;
                        $tabGroupeAutres = array();
                        $k = 0;
                        $pgProgLotStationAnAutres = $repoPgProgLotStationAn->getPgProgLotStationAnByStation($pgProgLotStationAn->getStation());
                        foreach ($pgProgLotStationAnAutres as $pgProgLotStationAnAutre) {
                            $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAnAutre);
                            foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                                if ($pgProgLotPeriodeProgAutre->getPeriodan()->getCodeStatut()->getCodeStatut() != 'INV') {
                                    if ($pgProgLotPeriodeProgAutre->getPeriodan()->getPeriode()->getId() == $pgProgPeriode->getid()) {
                                        if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getid() != $pgProgLotStationAn->getLotan()->getid()) {
                                            if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                                if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getLot()->getId() != $pgProgLotStationAn->getLotan()->getLot()->getId()) {
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
                        $tabPeriodes[$j]["autreProgrammation"] = count($tabGroupeAutres);
                        $j++;
                    }
                }
                sort($tabPeriodes);
                $tabStations[$i]["periodes"] = $tabPeriodes;
                $i++;
            }
        }
//return new Response('');
        usort($tabStations, create_function('$a,$b', 'return $a[\'station\']->getStation()->getCode()-$b[\'station\']->getStation()->getCode();'));

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
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);

        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnById($stationId);

        $pgProgLotStationAnSelectionnee = $repoPgProgLotStationAn->getPgProgLotStationAnById($autreStationId);
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

                if ($trouveCompl or count($tabGroupes) == 0) {
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
                $tabGroupeAutres = array();
                $k = 0;
                $pgProgLotStationAnAutres = $repoPgProgLotStationAn->getPgProgLotStationAnByStation($pgProgLotStationAn->getStation());
                foreach ($pgProgLotStationAnAutres as $pgProgLotStationAnAutre) {
                    $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAnAutre);
                    foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                        if ($pgProgLotPeriodeProgAutre->getPeriodan()->getCodeStatut()->getCodeStatut() != 'INV') {
                            if ($pgProgLotPeriodeProgAutre->getPeriodan()->getPeriode()->getId() == $pgProgPeriode->getid()) {
                                if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getid() != $pgProgLotStationAn->getLotan()->getid()) {
                                    if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                        if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getLot()->getId() != $pgProgLotStationAn->getLotan()->getLot()->getId()) {
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
                if ($trouveCompl or count($tabGroupes) == 0) {
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

//recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $annee = $pgProgLotAn->getAnneeProg();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnById($stationId);
        $pgProgPeriode = $repoPgProgPeriodes->getPgProgPeriodesById($periodeId);
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnBySLotanPeriode($pgProgLotAn, $pgProgPeriode);
        $pgRefSitePrelevements = $repoPgRefSitePrelevement->getPgRefSitePrelevementByOuvFoncId($pgProgLotStationAn->getStation()->getOuvFoncId());

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
                if ($pgProgLotPeriodeProg->getPprogCompl()) {
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
            $pgProgLotGrparAnAutres = $repoPgProgLotGrparAn->getPgProgLotGrparAnByGrpparref($pgProgLotGrparAn->getGrparRef());
            foreach ($pgProgLotGrparAnAutres as $pgProgLotGrparAnAutre) {
                if ($pgProgLotGrparAn->getLotAn()->getid() != $pgProgLotGrparAnAutre->getLotAn()->getid()) {
                    if ($pgProgLotGrparAn->getLotAn()->getAnneeProg() == $pgProgLotGrparAnAutre->getLotAn()->getAnneeProg()) {
                        $pgProgLotPeriodeAnAutres = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByPeriode($pgProgLotPeriodeAn->getPeriode());
                        foreach ($pgProgLotPeriodeAnAutres as $pgProgLotPeriodeAnAutre) {
                            if ($pgProgLotPeriodeAnAutre->getCodeStatut()->getCodeStatut() != 'INV') {
                                if ($pgProgLotPeriodeAn->getLotAn()->getAnneeProg() == $pgProgLotPeriodeAnAutre->getLotAn()->getAnneeProg()) {
                                    if ($pgProgLotPeriodeAnAutre->getLotan()->getid() != $pgProgLotPeriodeAnAutre->getLotan()->getid()) {
                                        if ($pgProgLotPeriodeAnAutre->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                            if ($pgProgLotPeriodeAnAutre->getLotan()->getLot()->getId() != $pgProgLotPeriodeAnAutre->getLotan()->getLot()->getId()) {
                                                $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAnPeriodeAn($pgProgLotGrparAnAutre, $pgProgLotPeriodeAnAutre);
                                                foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                                                    if ($pgProgLotPeriodeProgAutre->getStationAn()->getStation()->getOuvFoncId() == $pgProgLotStationAn->getStation()->getOuvFoncId()) {
                                                        $trouvelot = $pgProgLotPeriodeProgAutre->getStationAn()->getlotAn();
                                                        $tabgroupes[$i]["autre"] = $trouvelot;
                                                        $tabgroupes[$i]["autreGroupe"] = $pgProgLotPeriodeProgAutre->getGrparAn();
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


        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnById($stationId);
        $pgProgPeriode = $repoPgProgPeriodes->getPgProgPeriodesById($periodeId);
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnBySLotanPeriode($pgProgLotAn, $pgProgPeriode);
        
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
        ));
    }

    public function validerAction($stationId = null, $periodeId = null, $optionGroupes = null) {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationPeriode');
        $session->set('fonction', 'valider');
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
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
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
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $pgProgPeriodes = $repoPgProgPeriodes->getPgProgPeriodesByAnneeTypePeriode($annee, $pgProgTypeMilieu->getTypePeriode());
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnById($stationId);
        $pgProgPeriode = $repoPgProgPeriodes->getPgProgPeriodesById($periodeId);

        $selGroupes = Array();
        if (!empty($_POST['checkGroupe'])) {
            $selGroupes = $_POST['checkGroupe'];
            $ok = 'ok';
        } else {
            $tabMessage[$i] = 'Séléctionner au moins un groupe pour la station : ' . $pgProgLotStationAn->getStation()->getNumero() . ' et la période : ' . $pgProgPeriode->getLabelperiode() . '  svp.';
            $i++;
        }

        $selAutrePeriodeAns = Array();
        $i = 0;
        if (!empty($_POST['autrePeriodes'])) {
            $selAutrePeriodeAns = $_POST['autrePeriodes'];
        }

        if (!$optionGroupes) {
            $optionGroupes = 'N';
        }

        $autreProgrammation = 0;
        $tabGroupeAutres = array();
        $k = 0;
        $pgProgLotStationAnAutres = $repoPgProgLotStationAn->getPgProgLotStationAnByStation($pgProgLotStationAn->getStation());
        foreach ($pgProgLotStationAnAutres as $pgProgLotStationAnAutre) {
            $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAnAutre);
            foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                if ($pgProgLotPeriodeProgAutre->getPeriodan()->getCodeStatut()->getCodeStatut() != 'INV') {
                    if ($pgProgLotPeriodeProgAutre->getPeriodan()->getPeriode()->getId() == $pgProgPeriode->getid()) {
                        if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getid() != $pgProgLotStationAn->getLotan()->getid()) {
                            if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                if ($pgProgLotPeriodeProgAutre->getStationAn()->getLotan()->getLot()->getId() != $pgProgLotStationAn->getLotan()->getLot()->getId()) {


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
        $autreProgrammation = count($tabGroupeAutres);
        if ($autreProgrammation == 0) {
            $optionGroupes = 'N';
        }

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnBySLotanPeriode($pgProgLotAn, $pgProgPeriode);
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
                    }
                    $emSqe->flush();
                }
            }
        }

        foreach ($selGroupes as $selGroupe) {
            $pgProgLotGrparAn = $repoPgProgLotGrparAn->getPgProgLotGrparAnById($selGroupe);
            $pgProgLotPeriodeProg = new PgProgLotPeriodeProg();
            $pgProgLotPeriodeProg->setStationAn($pgProgLotStationAn);
            $pgProgLotPeriodeProg->setGrparAn($pgProgLotGrparAn);
            $pgProgLotPeriodeProg->setPeriodAn($pgProgLotPeriodeAn);

            $trouve = false;
            if ($optionGroupes != 'N') {
                $pgProgLotGrparAnAutres = $repoPgProgLotGrparAn->getPgProgLotGrparAnByGrpparref($pgProgLotGrparAn->getGrparRef());
                foreach ($pgProgLotGrparAnAutres as $pgProgLotGrparAnAutre) {
                    if ($pgProgLotGrparAn->getLotAn()->getid() != $pgProgLotGrparAnAutre->getLotAn()->getid()) {
                        if ($pgProgLotGrparAn->getLotAn()->getAnneeProg() == $pgProgLotGrparAnAutre->getLotAn()->getAnneeProg()) {
                            $pgProgLotPeriodeAnAutres = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByPeriode($pgProgPeriode);
                            foreach ($pgProgLotPeriodeAnAutres as $pgProgLotPeriodeAnAutre) {
                                if ($pgProgLotPeriodeAn->getLotAn()->getAnneeProg() == $pgProgLotPeriodeAnAutre->getLotAn()->getAnneeProg()) {
                                    $pgProgLotPeriodeProgAutres = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAnPeriodeAn($pgProgLotGrparAnAutre, $pgProgLotPeriodeAnAutre);
                                    foreach ($pgProgLotPeriodeProgAutres as $pgProgLotPeriodeProgAutre) {
                                        if ($pgProgLotPeriodeProgAutre->getStationAn()->getStation()->getOuvFoncId() == $pgProgLotStationAn->getStation()->getOuvFoncId()) {
                                            $pgProgLotPeriodeProg->setPprogCompl($pgProgLotPeriodeProgAutre);
                                            $pgProgLotPeriodeProg->setStatut($optionGroupes);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $pgProgLotPeriodeProg->setStatut('N');
                $pgProgLotPeriodeProg->setPprogCompl(null);
            }

            if (!$pgProgLotPeriodeProg->getPprogCompl()) {
                $pgProgLotPeriodeProg->setStatut('N');
            }


            $emSqe->persist($pgProgLotPeriodeProg);

            if (count($selAutrePeriodeAns) > 0) {
                for ($i = 0; $i < count($selAutrePeriodeAns); $i++) {
                    if ($selAutrePeriodeAns[$i]) {
                        $autrePeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnById($selAutrePeriodeAns[$i]);
                        $autrePeriodeProg = clone($pgProgLotPeriodeProg);
                        $autrePeriodeProg->setPeriodAn($autrePeriodeAn);
                        $emSqe->persist($autrePeriodeProg);
                    }
                }
            }
        }
// return new Response ('option : ' . $optionGroupes );
        $emSqe->flush();

        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
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
        $emSqe->flush();


// Récupération des support obligatoire
        $tabGrparAns = array();
        $i = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $tabGrparAns[$i]["groupe"] = $pgProgLotGrparAn;
            $i++;
        }

        $tabStations = array();
        $i = 0;
        $tabStations[$i]["station"] = $pgProgLotStationAn;
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
                if ($trouveCompl or count($tabGroupes) == 0) {
                    $tabPeriodes[$j]["autreProgrammation"] = count($tabGroupeAutres);
                }
                $j++;
            }
        }
        sort($tabPeriodes);
        $tabStations[$i]["periodes"] = $tabPeriodes;
        $i++;
        usort($tabStations, create_function('$a,$b', 'return $a[\'station\']->getStation()->getCode()-$b[\'station\']->getStation()->getCode();'));


        return $this->render('AeagSqeBundle:Programmation:Periode\valider.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'lotan' => $pgProgLotAn,
                    'periodes' => $pgProgPeriodes,
                    'stationAns' => $tabStations,
                    'grparAns' => $tabGrparAns,
        ));
    }

    public function autreProgrammationAction($stationId = null, $periodeId = null, $groupeId = null) {

        $user = $this->getUser();
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
        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnBySLotanPeriode($pgProgLotAn, $pgProgPeriode);
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

}
