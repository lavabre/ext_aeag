<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Aeag\SqeBundle\Entity\PgProgWebUsers;
use Aeag\SqeBundle\Entity\PgProgMarcheUser;
use Aeag\SqeBundle\Entity\PgProgMarche;
use Aeag\SqeBundle\Entity\PgProgLot;
use Aeag\SqeBundle\Entity\PgProgLotAn;
use Aeag\SqeBundle\Entity\PgProgLotGrparAn;
use Aeag\SqeBundle\Entity\PgProgLotStationAn;
use Aeag\SqeBundle\Entity\PgProgLotPeriodeProg;
use Aeag\SqeBundle\Entity\PgProgSuiviPhases;
use Aeag\SqeBundle\Controller\ProgrammationBilanController;

class ProgrammationStationController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationStation');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotId = $request->get('pgProgLotId');
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

        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');
        $repoPgProgZgeorefStation = $emSqe->getRepository('AeagSqeBundle:PgProgZgeorefStation');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgWebuserRsx = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserRsx');
        $repoPgProgWebuserZgeoref = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserZgeoref');
        $repoPgProgZgeorefTypmil = $emSqe->getRepository('AeagSqeBundle:PgProgZgeorefTypmil');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');



        if ($pgProgLotAnId) {
            $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
            $pgProgLot = $pgProgLotAn->getLot();
        } else {
            $pgProgLot = $repoPgProgLot->getPgProgLotById($pgProgLotId);
        }

//        if (!$pgProgLotId) {
//            $pgProgLotId = $session->get('progLot');
//            $pgProgLotAnId = $session->get('progLotAn');
//            $maj = $maj;
//        }


        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgZoneGeoRef = $pgProgLot->getZgeoRef();
        if (!$annee) {
            $annee = $pgProgLot->getMarche()->getAnneeDeb();
            $session->set('critAnnee', $annee);
        }

        if ($session->get('critWebuser')) {
            $pgProgWebusers = $repoPgProgWebusers->getPgProgWebusersByid($session->get('critWebuser'));
        } else {
//            if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE')) {
//                $PgProgZgeorefTypmils = $repoPgProgZgeorefTypmil->getpgProgZgeorefTypmilByTypmil($pgProgTypeMilieu);
//                foreach ($PgProgZgeorefTypmils as $PgProgZgeorefTypmil) {
//                    if ($PgProgZgeorefTypmil->getZgeoRef()->getId() == $pgProgZoneGeoRef->getid()) {
//                        $pgProgWebuserZgeorefs = $repoPgProgWebuserZgeoref->getPgProgWebuserZgeorefByZgeoref($PgProgZgeorefTypmil->getZgeoRef());
//                        $pgProgWebusers = array();
//                        $i = 0;
//                        foreach ($pgProgWebuserZgeorefs as $pgProgWebuserZgeoref) {
//                            $trouve = false;
//                            for ($j = 0; $j < count($pgProgWebusers); $j++) {
//                                if ($pgProgWebusers[$j]->getId() == $pgProgWebuserZgeoref->getWebuser()->getId()) {
//                                    $trouve = true;
//                                    $j = count($pgProgWebusers) + 1;
//                                }
//                            }
//                            if ($trouve == false) {
//                                $pgProgWebusers[$i] = $pgProgWebuserZgeoref->getWebuser();
//                                $i++;
//                            }
//                        }
//                    }
//                }
//            } else {
            $pgProgWebusers = $repoPgProgWebusers->getPgProgWebusersByLoginPassword($user->getUsername(), $user->getPassword());
//            }
        }

        if (count($pgProgWebusers) == 1) {
            $pgProgWebuser = $pgProgWebusers;
        } else {
            $pgProgWebuser = null;
        }

        /* sauvegarde de l'annee et du lot dans la table PgProgLotAn */

        if (!$pgProgLotAnId) {
            $version = $repoPgProgLotAn->getMaxVersionByAnneeLot($annee, $pgProgLot);
            if (!$version) {
                $version = 1;
            } else {
                $version++;
            }
            $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnByAnneeLotVersion($annee, $pgProgLot, $version);

            if (!$pgProgLotAn) {
                $pgProgLotAn = new PgProgLotAn();
                $pgProgLotAn->setAnneeProg($annee);
                $pgProgLotAn->setVersion($version);
                $pgProgLotAn->setLot($pgProgLot);
                $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P10');
                $pgProgLotAn->setPhase($pgProgPhase);
                $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('CRE');
                $pgProgLotAn->setCodeStatut($pgProgStatut);
                $now = date('Y-m-d H:i');
                $now = new \DateTime($now);
                $pgProgLotAn->setDateModif($now);
                if ($pgProgWebuser) {
                    $pgProgLotAn->setUtilModif($pgProgWebuser);
                } else {
                    $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByLoginPassword($user->getUsername(), $user->getPassword());
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
        } else {
            $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
            if ($maj != 'V') {
                $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('UPD');
                $pgProgLotAn->setCodeStatut($pgProgStatut);
                $now = date('Y-m-d H:i');
                $now = new \DateTime($now);
                if ($pgProgWebuser) {
                    $pgProgLotAn->setDateModif($now);
                    $pgProgLotAn->setUtilModif($pgProgWebuser);
                    $emSqe->persist($pgProgLotAn);
                    $emSqe->flush();
                }
            }
        }


        $typeMilieu = $pgProgLot->getCodeMilieu();

//        $session->set('maj', $maj);
//        $session->set('progLotAn', $pgProgLotAn->getId());
//        $session->set('progLot', $pgProgLot->getId());
//        $session->set('progZoneGeoRef', $pgProgZoneGeoRef->getId());
//        $session->set('progTypeMilieu', $typeMilieu->getCodeMilieu());


        if ($pgProgLotAn->getPhase()->getcodePhase() == 'P15' and $session->get('niveau2') == '') {
            $session->set('niveau2', $this->generateUrl('AeagSqeBundle_programmation_stations', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_periodes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        }


        // recuperatiion des reseaux de l'utilisateurs
        $tabReseauxUsers = array();
        $i = 0;
        if ($pgProgWebuser and ( !$this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE'))) {
            $pgProgWebuserRsx = $repoPgProgWebuserRsx->getPgProgWebuserRsxByWebuser($pgProgWebuser);
        } else {
            $pgProgWebuserRsx = $repoPgProgWebuserRsx->getPgProgWebuserRsx();
        }
        foreach ($pgProgWebuserRsx as $pgProgWebuserRs) {
            $trouve = false;
            for ($j = 0; $j < count($tabReseauxUsers); $j++) {
                if ($tabReseauxUsers[$j]['reseau'] == $pgProgWebuserRs->getReseauMesure()) {
                    $trouve = true;
                    break;
                }
            }
            if ($trouve == false) {
                $reseauMesure = $pgProgWebuserRs->getReseauMesure();
                // print_r( 'categorie : ' . $reseauMesure->getCategorieMilieu() . ' pour le reseau : ' . $reseauMesure->getcodeAeagRsx() . ' ');
                if ($reseauMesure->getCategorieMilieu() == $pgProgLot->getCodeMilieu()->getCategorieMilieu()) {
                    $tabReseauxUsers[$i]['reseau'] = $reseauMesure;
                    $tabReseauxUsers[$i]['defaut'] = $pgProgWebuserRs->getDefaut();
                    $i++;
                }
            }
        }



        if (count($tabReseauxUsers) == 1) {
            $session->set('selectionReseau', $tabReseauxUsers[0]['reseau']->getGroupementId());
        } else {
            $session->set('selectionReseau', null);
        }

        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        if ($pgProgLotStationAns) {
            //asort($pgProgLotStationAns);
            $tabSelReseaux = array();
            $i = 0;
            foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
                $trouve = false;
                for ($j = 0; $j < count($tabSelReseaux); $j++) {
                    if ($tabSelReseaux[$j]['reseau']->getgroupementId() == $pgProgLotStationAn->getRsxId()) {
                        $trouve = true;
                        $tabSelReseaux[$j]['nb'] = $tabSelReseaux[$j]['nb'] + 1;
                        break;
                    }
                }
                if ($trouve == false) {
                    $reseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
                    if ($reseauMesure) {
                        if ($reseauMesure->getCategorieMilieu() == $pgProgLot->getCodeMilieu()->getCategorieMilieu()) {
                            $tabSelReseaux[$i]['reseau'] = $reseauMesure;
                            $tabSelReseaux[$i]['nb'] = 1;
                            $i++;
                        }
                    }
                }
            }
            $nb = 0;
            foreach ($tabSelReseaux as $tabSelReseau) {
                if ($tabSelReseau['nb'] > $nb) {
                    $nb = $tabSelReseau['nb'];
                    $session->set('selectionReseau', $tabSelReseau['reseau']->getGroupementId());
                    if ($action != 'P') {
                        $tabReseauxUsers = array();
                        $tabReseauxUsers[0]['reseau'] = $tabSelReseau['reseau'];
                    }
                }
            }
        }

        // usort($tabReseauxUsers, create_function('$a,$b', 'return $a[\'reseau\']->getNomRsx()-$b[\'reseau\']->getNomRsx();'));

        $tabMessages = array();
        $i = 0;
        if (!($session->has('messageErreur'))) {
            $session->set('messageErreur', null);
        } else {
            $messages = $session->get('messageErreur');
            if ($messages) {
                foreach ($messages as $message) {
                    $tabMessages[$i] = $message;
                    $i++;
                }
            }
        }


// recuperation des stations a partir de la zone geographique du lot
        $pgProgZgeorefStations = $repoPgProgZgeorefStation->getpgProgZgeorefStationByZgeoref($pgProgZoneGeoRef);
        asort($pgProgZgeorefStations);
        $tabStations = array();
        $i = 0;
        if (count($pgProgLotStationAns) > 0) {
            foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
                $stationGeo = $pgProgLotStationAn->getStation();
                $tabStations[$i]['station']['ouvFoncId'] = $stationGeo->getOuvfoncId();
                $tabStations[$i]['station']['code'] = $stationGeo->getCode();
                $tabStations[$i]['station']['libelle'] = $stationGeo->getLibelle();
                $tabStations[$i]['station']['codeMasdo'] = $stationGeo->getCodeMasdo();
                $tabStations[$i]['station']['nomCoursEau'] = $stationGeo->getNomCoursEau();
                $tabStations[$i]['lotStationAn'] = null;
//            $inseeCommune = sprintf("%05d", $stationGeo->getInseeCommune());
//            $commune = $repoCommune->getCommuneByCommune($inseeCommune);
//            $tabStations[$i]['commune']['libelle'] = $commune->getlibelle();
                $tabStations[$i]['commune']['libelle'] = $stationGeo->getNomCommune();
                $tabStations[$i]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $stationGeo->getCode()) . '.pdf';
//                if ($stationGeo->getType() == 'STQ') {
//                    $tabStations[$i]['lien'] = 'http://adour-garonne.eaufrance.fr/station/' . $stationGeo->getCode() . '/print';
//                }
//                if ($stationGeo->getType() == 'STQL') {
//                    $tabStations[$i]['lien'] = 'http://adour-garonne.eaufrance.fr/lac/' . $stationGeo->getCode() . '/print';
//                }
//                if ($stationGeo->getType() == 'QZ') {
//                    $tabStations[$i]['lien'] = 'http://www.ades.eaufrance.fr/FichePtEau.aspx?code=' . $stationGeo->getCode();
//                }
                $tabStations[$i]['cocher'] = 'O';
                $cocherDecocher = "O";
                $tabReseaux = array();
                $tabStations[$i]['reseauSelectionne'] = null;
                $k = 0;
                if (count($tabReseauxUsers) > 0) {
                    for ($l = 0; $l < count($tabReseauxUsers); $l++) {
                        $tabReseaux[$k]['reseau'] = $tabReseauxUsers[$l];
                        $tabReseaux[$k]['cocher'] = 'N';
                        if ($tabReseaux[$k]['reseau']['reseau']->getGroupementId() == $pgProgLotStationAn->getRsxId()) {
                            $tabReseaux[$k]['cocher'] = 'O';
                            $tabStations[$i]['reseauSelectionne'] = $tabReseaux[$k]['reseau']['reseau'];
                            $k++;
                        }
                    }
                }
                $pgProgLotStationAnAutres = $repoPgProgLotStationAn->getPgProgLotStationAnByStation($stationGeo);
                asort($pgProgLotStationAnAutres);
                $tabStations[$i]['autreLots'] = null;
                $j = 0;
                $tabLots = array();
                foreach ($pgProgLotStationAnAutres as $pgProgLotStationAnAutre) {
                    if ($pgProgLotStationAnAutre->getLotan()->getid() != $pgProgLotAn->getid()) {
                        if ($pgProgLotStationAnAutre->getLotan()->getAnneeProg() == $pgProgLotAn->getAnneeProg()) {
                            if ($pgProgLotStationAnAutre->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                if ($pgProgLotStationAnAutre->getLotan()->getLot()->getId() != $pgProgLotAn->getLot()->getId()) {
                                    $tabLots[$j]['lot'] = $pgProgLotStationAnAutre->getLotan();
                                    $tabLots[$j]['lien'] = 'http://ext.eau-adour-garonne.fr/sqe/programmation/bilan/?action=V&maj=V&lotan=' . $pgProgLotStationAnAutre->getLotan()->getid();
                                    $j++;
                                }
                            }
                        }
                    }
                }
                $tabStations[$i]['autreLots'] = $tabLots;
                $i++;
            }
        } else {
            foreach ($pgProgZgeorefStations as $pgProgZgeorefStation) {
                $stationGeo = $pgProgZgeorefStation->getStationMesure();
                $tabStations[$i]['station']['ouvFoncId'] = $stationGeo->getOuvfoncId();
                $tabStations[$i]['station']['code'] = $stationGeo->getCode();
                $tabStations[$i]['station']['libelle'] = $stationGeo->getLibelle();
                $tabStations[$i]['station']['codeMasdo'] = $stationGeo->getCodeMasdo();
                $tabStations[$i]['station']['nomCoursEau'] = $stationGeo->getNomCoursEau();
                $tabStations[$i]['lotStationAn'] = null;
//            $inseeCommune = sprintf("%05d", $stationGeo->getInseeCommune());
//            $commune = $repoCommune->getCommuneByCommune($inseeCommune);
//            $tabStations[$i]['commune']['libelle'] = $commune->getlibelle();
                $tabStations[$i]['commune']['libelle'] = $stationGeo->getNomCommune();
                $tabStations[$i]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $stationGeo->getCode()) . '.pdf';
//                if ($stationGeo->getType() == 'STQ') {
//                    $tabStations[$i]['lien'] = 'http://adour-garonne.eaufrance.fr/station/' . $stationGeo->getCode() . '/print';
//                }
//                if ($stationGeo->getType() == 'STQL') {
//                    $tabStations[$i]['lien'] = 'http://adour-garonne.eaufrance.fr/lac/' . $stationGeo->getCode() . '/print';
//                }
//                if ($stationGeo->getType() == 'QZ') {
//                    $tabStations[$i]['lien'] = 'http://www.ades.eaufrance.fr/FichePtEau.aspx?code=' . $stationGeo->getCode();
//                }
                $tabStations[$i]['cocher'] = 'N';
                $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $stationGeo);
                $cocherDecocher = "N";
                if ($pgProgLotStationAn) {
                    //$tabStations[$i]['lotStationAn'] = $pgProgLotStationAn;
                    $tabStations[$i]['cocher'] = 'O';
                    $cocherDecocher = "O";
                }
                $tabReseaux = array();
                $tabStations[$i]['reseauSelectionne'] = null;
                $k = 0;
                if (count($tabReseauxUsers) > 0) {
                    for ($l = 0; $l < count($tabReseauxUsers); $l++) {
                        $tabReseaux[$k]['reseau'] = $tabReseauxUsers[$l];
                        $tabReseaux[$k]['cocher'] = 'N';
                        if ($pgProgLotStationAn) {
                            if ($pgProgLotStationAn->getStation()->getOuvFoncId() == $stationGeo->getOuvFoncId()) {
                                if ($tabReseaux[$k]['reseau']->getGroupementId() == $pgProgLotStationAn->getRsxId()) {
                                    $tabReseaux[$k]['cocher'] = 'O';
                                    $tabStations[$i]['reseauSelectionne'] = $tabReseaux[$k]['reseau'];
                                    $k++;
                                }
                            }
                        }
                    }
                }
                //$tabStations[$i]['reseaux'] = $tabReseaux;
                // autres lots
                $pgProgLotStationAnAutres = $repoPgProgLotStationAn->getPgProgLotStationAnByStation($stationGeo);
                asort($pgProgLotStationAnAutres);
                $tabStations[$i]['autreLots'] = null;
                $j = 0;
                $tabLots = array();
                foreach ($pgProgLotStationAnAutres as $pgProgLotStationAnAutre) {
                    if ($pgProgLotStationAnAutre->getLotan()->getid() != $pgProgLotAn->getid()) {
                        if ($pgProgLotStationAnAutre->getLotan()->getAnneeProg() == $pgProgLotAn->getAnneeProg()) {
                            if ($pgProgLotStationAnAutre->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                                if ($pgProgLotStationAnAutre->getLotan()->getLot()->getId() != $pgProgLotAn->getLot()->getId()) {
                                    if ($pgProgLotStationAnAutre->getLotan()->getLot()->getCodeMilieu() == $pgProgLotAn->getLot()->getCodeMilieu()) {
                                        $tabLots[$j]['lot'] = $pgProgLotStationAnAutre->getLotan();
                                        $tabLots[$j]['lien'] = 'http://ext.eau-adour-garonne.fr/sqe/programmation/bilan/?action=V&maj=V&lotan=' . $pgProgLotStationAnAutre->getLotan()->getid();
                                        $j++;
                                    }
                                }
                            }
                        }
                    }
                }
                $tabStations[$i]['autreLots'] = $tabLots;
                $i++;
            }
        }

        $tabControle = ProgrammationBilanController::controleProgrammationAction($pgProgLotAn->getId(), $emSqe, $session);

        return $this->render('AeagSqeBundle:Programmation:Station\index.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'campagne' => $annee,
                    'lotan' => $pgProgLotAn,
                    'controle' => $tabControle,
                    'reseaux' => $tabReseauxUsers,
                    'stations' => $tabStations,
                    'cocherTout' => $cocherDecocher,
                    'messages' => $tabMessages));
    }

    public function allStationsAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationStation');
        $session->set('fonction', 'allStations');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');
        $repoPgProgZgeorefStation = $emSqe->getRepository('AeagSqeBundle:PgProgZgeorefStation');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgWebuserRsx = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserRsx');
        $repoPgProgWebuserZgeoref = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserZgeoref');
        $repoPgProgZgeorefTypmil = $emSqe->getRepository('AeagSqeBundle:PgProgZgeorefTypmil');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');


//        if (!$pgProgLotId) {
//            $pgProgLotId = $session->get('progLot');
//            $pgProgLotAnId = $session->get('progLotAn');
//            $maj = $maj;
//        }


        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgZoneGeoRef = $pgProgLot->getZgeoRef();
        if (!$annee) {
            $annee = $pgProgLot->getMarche()->getAnneeDeb();
            $session->set('critAnnee', $annee);
        }

        if ($session->get('critWebuser')) {
            $pgProgWebusers = $repoPgProgWebusers->getPgProgWebusersByid($session->get('critWebuser'));
        } else {
//            if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE')) {
//                $PgProgZgeorefTypmils = $repoPgProgZgeorefTypmil->getpgProgZgeorefTypmilByTypmil($pgProgTypeMilieu);
//                foreach ($PgProgZgeorefTypmils as $PgProgZgeorefTypmil) {
//                    if ($PgProgZgeorefTypmil->getZgeoRef()->getId() == $pgProgZoneGeoRef->getid()) {
//                        $pgProgWebuserZgeorefs = $repoPgProgWebuserZgeoref->getPgProgWebuserZgeorefByZgeoref($PgProgZgeorefTypmil->getZgeoRef());
//                        $pgProgWebusers = array();
//                        $i = 0;
//                        foreach ($pgProgWebuserZgeorefs as $pgProgWebuserZgeoref) {
//                            $trouve = false;
//                            for ($j = 0; $j < count($pgProgWebusers); $j++) {
//                                if ($pgProgWebusers[$j]->getId() == $pgProgWebuserZgeoref->getWebuser()->getId()) {
//                                    $trouve = true;
//                                    $j = count($pgProgWebusers) + 1;
//                                }
//                            }
//                            if ($trouve == false) {
//                                $pgProgWebusers[$i] = $pgProgWebuserZgeoref->getWebuser();
//                                $i++;
//                            }
//                        }
//                    }
//                }
//            } else {
            $pgProgWebusers = $repoPgProgWebusers->getPgProgWebusersByLoginPassword($user->getUsername(), $user->getPassword());
//            }
        }

        if (count($pgProgWebusers) == 1) {
            $pgProgWebuser = $pgProgWebusers;
        } else {
            $pgProgWebuser = null;
        }

        /* sauvegarde de l'annee et du lot dans la table PgProgLotAn */

        if (!$pgProgLotAnId) {
            $version = $repoPgProgLotAn->getMaxVersionByAnneeLot($annee, $pgProgLot);
            if (!$version) {
                $version = 1;
            } else {
                $version++;
            }
            $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnByAnneeLotVersion($annee, $pgProgLot, $version);

            if (!$pgProgLotAn) {
                $pgProgLotAn = new PgProgLotAn();
                $pgProgLotAn->setAnneeProg($annee);
                $pgProgLotAn->setVersion($version);
                $pgProgLotAn->setLot($pgProgLot);
                $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P10');
                $pgProgLotAn->setPhase($pgProgPhase);
                $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('CRE');
                $pgProgLotAn->setCodeStatut($pgProgStatut);
                $now = date('Y-m-d H:i');
                $now = new \DateTime($now);
                $pgProgLotAn->setDateModif($now);
                if ($pgProgWebuser) {
                    $pgProgLotAn->setUtilModif($pgProgWebuser);
                } else {
                    $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByLoginPassword($user->getUsername(), $user->getPassword());
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
        } else {
            $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
            if ($maj != 'V') {
                $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('UPD');
                $pgProgLotAn->setCodeStatut($pgProgStatut);
                $now = date('Y-m-d H:i');
                $now = new \DateTime($now);
                if ($pgProgWebuser) {
                    $pgProgLotAn->setDateModif($now);
                    $pgProgLotAn->setUtilModif($pgProgWebuser);
                    $emSqe->persist($pgProgLotAn);
                    $emSqe->flush();
                }
            }
        }


        $typeMilieu = $pgProgLot->getCodeMilieu();

//        $session->set('maj', $maj);
//        $session->set('progLotAn', $pgProgLotAn->getId());
//        $session->set('progLot', $pgProgLot->getId());
//        $session->set('progZoneGeoRef', $pgProgZoneGeoRef->getId());
//        $session->set('progTypeMilieu', $typeMilieu->getCodeMilieu());
        // recuperatiion des reseaux de l'utilisateurs
        $tabReseauxUsers = array();
        $i = 0;
        if ($pgProgWebuser and ( !$this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE'))) {
            $pgProgWebuserRsx = $repoPgProgWebuserRsx->getPgProgWebuserRsxByWebuser($pgProgWebuser);
        } else {
            $pgProgWebuserRsx = $repoPgProgWebuserRsx->getPgProgWebuserRsx();
        }
        foreach ($pgProgWebuserRsx as $pgProgWebuserRs) {
            $trouve = false;
            for ($j = 0; $j < count($tabReseauxUsers); $j++) {
                if ($tabReseauxUsers[$j] == $pgProgWebuserRs->getReseauMesure()) {
                    $trouve = true;
                    $j = count($tabReseauxUsers) + 1;
                }
            }
            if ($trouve == false) {
                $reseauMesure = $pgProgWebuserRs->getReseauMesure();
                // print_r( 'categorie : ' . $reseauMesure->getCategorieMilieu() . ' pour le reseau : ' . $reseauMesure->getcodeAeagRsx() . ' ');
                if ($reseauMesure->getCategorieMilieu() == $pgProgLot->getCodeMilieu()->getCategorieMilieu()) {
                    $tabReseauxUsers[$i] = $reseauMesure;
                    $i++;
                }
            }
        }



        if (count($tabReseauxUsers) == 1) {
            $session->set('selectionReseau', $tabReseauxUsers[0]->getGroupementId());
        } else {
            $session->set('selectionReseau', null);
        }

        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        if ($pgProgLotStationAns) {
            $tabSelReseaux = array();
            $i = 0;
            foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
                $trouve = false;
                for ($j = 0; $j < count($tabSelReseaux); $j++) {
                    if ($tabSelReseaux[$j]['reseau']->getgroupementId() == $pgProgLotStationAn->getRsxId()) {
                        $trouve = true;
                        $tabSelReseaux[$j]['nb'] = $tabSelReseaux[$j]['nb'] + 1;
                        break;
                    }
                }
                if ($trouve == false) {
                    $reseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
                    if ($reseauMesure) {
                        if ($reseauMesure->getCategorieMilieu() == $pgProgLot->getCodeMilieu()->getCategorieMilieu()) {
                            $tabSelReseaux[$i]['reseau'] = $reseauMesure;
                            $tabSelReseaux[$i]['nb'] = 1;
                            $i++;
                        }
                    }
                }
            }
            $nb = 0;
            foreach ($tabSelReseaux as $tabSelReseau) {
                if ($tabSelReseau['nb'] > $nb) {
                    $nb = $tabSelReseau['nb'];
                    $session->set('selectionReseau', $tabSelReseau['reseau']->getGroupementId());
                    if ($action != 'P') {
                        $tabReseauxUsers = array();
                        $tabReseauxUsers[0] = $tabSelReseau['reseau'];
                    }
                }
            }
        }

        usort($tabReseauxUsers, create_function('$a,$b', 'return $a->getNomRsx()-$b->getNomRsx();'));

        $tabMessages = array();
        $i = 0;
        if (!($session->has('messageErreur'))) {
            $session->set('messageErreur', null);
        } else {
            $messages = $session->get('messageErreur');
            if ($messages) {
                foreach ($messages as $message) {
                    $tabMessages[$i] = $message;
                    $i++;
                }
            }
        }


// recuperation des stations a partir de la zone geographique du lot
        $pgProgZgeorefStations = $repoPgProgZgeorefStation->getpgProgZgeorefStationByZgeoref($pgProgZoneGeoRef);
        $tabStations = array();
        $i = 0;
        foreach ($pgProgZgeorefStations as $pgProgZgeorefStation) {
            $stationGeo = $pgProgZgeorefStation->getStationMesure();
            $tabStations[$i]['station']['ouvFoncId'] = $stationGeo->getOuvfoncId();
            $tabStations[$i]['station']['code'] = $stationGeo->getCode();
            $tabStations[$i]['station']['libelle'] = $stationGeo->getLibelle();
            $tabStations[$i]['station']['codeMasdo'] = $stationGeo->getCodeMasdo();
            $tabStations[$i]['station']['nomCoursEau'] = $stationGeo->getNomCoursEau();
            $tabStations[$i]['lotStationAn'] = null;
//            $inseeCommune = sprintf("%05d", $stationGeo->getInseeCommune());
//            $commune = $repoCommune->getCommuneByCommune($inseeCommune);
//            $tabStations[$i]['commune']['libelle'] = $commune->getlibelle();
            $tabStations[$i]['commune']['libelle'] = $stationGeo->getNomCommune();
            $tabStations[$i]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $stationGeo->getCode()) . '.pdf';
//            if ($stationGeo->getType() == 'STQ') {
//                $tabStations[$i]['lien'] = 'http://adour-garonne.eaufrance.fr/station/' . $stationGeo->getCode() . '/print';
//            }
//            if ($stationGeo->getType() == 'STQL') {
//                $tabStations[$i]['lien'] = 'http://adour-garonne.eaufrance.fr/lac/' . $stationGeo->getCode() . '/print';
//            }
//            if ($stationGeo->getType() == 'QZ') {
//                $tabStations[$i]['lien'] = 'http://www.ades.eaufrance.fr/FichePtEau.aspx?code=' . $stationGeo->getCode();
//            }
            $tabStations[$i]['cocher'] = 'N';
            $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $stationGeo);
            $cocherDecocher = "N";
            if ($pgProgLotStationAn) {
                //$tabStations[$i]['lotStationAn'] = $pgProgLotStationAn;
                $tabStations[$i]['cocher'] = 'O';
                $cocherDecocher = "O";
            }
            $tabReseaux = array();
            $tabStations[$i]['reseauSelectionne'] = null;
            $k = 0;
            if (count($tabReseauxUsers) > 0) {
                for ($l = 0; $l < count($tabReseauxUsers); $l++) {
                    $tabReseaux[$k]['reseau'] = $tabReseauxUsers[$l];
                    $tabReseaux[$k]['cocher'] = 'N';
                    if ($pgProgLotStationAn) {
                        if ($pgProgLotStationAn->getStation()->getOuvFoncId() == $stationGeo->getOuvFoncId()) {
                            if ($tabReseaux[$k]['reseau']->getGroupementId() == $pgProgLotStationAn->getRsxId()) {
                                $tabReseaux[$k]['cocher'] = 'O';
                                $tabStations[$i]['reseauSelectionne'] = $tabReseaux[$k]['reseau'];
                                $k++;
                            }
                        }
                    }
                }
            }
            //$tabStations[$i]['reseaux'] = $tabReseaux;
            // autres lots
            $pgProgLotStationAnAutres = $repoPgProgLotStationAn->getPgProgLotStationAnByStation($stationGeo);
            $tabStations[$i]['autreLot'] = null;
            $tabStations[$i]['lienAutreLot'] = null;
            foreach ($pgProgLotStationAnAutres as $pgProgLotStationAnAutre) {
                if ($pgProgLotStationAnAutre->getLotan()->getid() != $pgProgLotAn->getid()) {
                    if ($pgProgLotStationAnAutre->getLotan()->getAnneeProg() == $pgProgLotAn->getAnneeProg()) {
                        if ($pgProgLotStationAnAutre->getLotan()->getPhase()->getCodePhase() >= 'P25') {
                            if ($pgProgLotStationAnAutre->getLotan()->getLot()->getId() != $pgProgLotAn->getLot()->getId()) {
                                if ($pgProgLotStationAnAutre->getLotan()->getLot()->getCodeMilieu() == $pgProgLotAn->getLot()->getCodeMilieu()) {
                                    $tabStations[$i]['autreLot'] = $pgProgLotStationAnAutre->getLotan();
                                    $tabStations[$i]['lienAutreLot'] = 'http://ext.eau-adour-garonne.fr/sqe/programmation/bilan/?action=V&maj=V&lotan=' . $pgProgLotStationAnAutre->getLotan()->getid();
                                }
                            }
                        }
                    }
                }
            }
            $i++;
        }

        $session->set('niveau2', $this->generateUrl('AeagSqeBundle_programmation_stations', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));

        $tabControle = ProgrammationBilanController::controleProgrammationAction($pgProgLotAnId, $emSqe, $session);

        return $this->render('AeagSqeBundle:Programmation:Station\allStations.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'campagne' => $annee,
                    'lotan' => $pgProgLotAn,
                    'controle' => $tabControle,
                    'reseaux' => $tabReseauxUsers,
                    'stations' => $tabStations,
                    'cocherTout' => $cocherDecocher,
                    'messages' => $tabMessages));
    }

    public function resultatAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationStation');
        $session->set('fonction', 'Resultat');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        if (!empty($_POST['suivant'])) {
            $suivant = $_POST['suivant'];
        } else {
            $suivant = 'groupe';
        }


        if ($action != 'P' or $maj == 'V') {
            if ($suivant == 'station') {
                return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_stations', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
            } elseif ($suivant == 'groupe') {
                return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_groupes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
            } elseif ($suivant == 'periode') {
                return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_periodes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
            } elseif ($suivant == 'bilan') {
                return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_bilan', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
            } else {
                return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_groupes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
            }
        }

        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgPeriodes = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');
        $repoPgProgZgeorefStation = $emSqe->getRepository('AeagSqeBundle:PgProgZgeorefStation');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');



        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgTypePeriode = $pgProgTypeMilieu->getTypePeriode();
        $pgProgZoneGeoRef = $pgProgLot->getZgeoRef();
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);

        $tabMessage = array();
        $i = 0;


        // recuperation des stations a partir de la zone geographique du lot
        // $pgProgZgeorefStations = $repoPgProgZgeorefStation->getpgProgZgeorefStationByZgeoref($pgProgZoneGeoRef);

        $tabSelStations = array();
        $i = 0;
        $tabSelResaux = array();
        $j = 0;
        foreach ($_POST as $post => $val) {
            if (substr($post, 0, 11) == 'idStationOk') {
                $tabSelStations[$i] = $val;
                $i++;
            } else {
                $tabSelResaux[$j]['station'] = substr($post, 18);
                $tabSelResaux[$j]['reseau'] = $val;
                 $j++;
            }
        }
       
        $tabStations = array();
        $i = 0;
        $j = 0;
        $nbStations = 0;
        $erreur = false;
        for ($nb = 0; $nb < count($tabSelStations); $nb++) {
            $stationGeo = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($tabSelStations[$nb]);
            $tabStations[$i]['station'] = $stationGeo;
            $tabStations[$i]['cocher'] = 'O';
            for ($nbres = 0; $nbres < count($tabSelResaux); $nbres++) {
                $trouver = false;
                if ($tabSelResaux[$nbres]['station'] == $tabSelStations[$nb]) {
                    $trouver = true;
                    break;
                }
            }
            if (!$trouver) {
                $erreur = true;
                $tabMessage[$j] = 'Sélectionner un réseau pour la station ' . $stationGeo->getCode() . ' ' . $stationGeo->getLibelle();
                $j++;
            } else {
                $tabStations[$i]['reseau'] = $tabSelResaux[$nbres]['reseau'];
            }
            $i++;
            $nbStations++;
        }

        if ($nbStations == 0) {
            $tabMessage[$j] = 'Séléctionner au moins une station svp.';
            $erreur = true;
            $j++;
        }

        if ($erreur) {
            $session->set('messageErreur', $tabMessage);
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_stations', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        }

        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAn($pgProgLotAn);
        foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
            $trouver = false;
            foreach ($tabStations as $station) {
                if ($pgProgLotStationAn->getStation()->getOuvFoncId() == $station['station']->getOuvFoncId()) {
                    $trouver = true;
                    break;
                }
            }
            if (!$trouver) {
                $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAn);
                if (count($pgProgLotPeriodeProgs) > 0) {
                    foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                        $pgProgLotPeriodeProgCompls = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPprogCompl($pgProgLotPeriodeProg);
                        foreach ($pgProgLotPeriodeProgCompls as $pgProgLotPeriodeProgCompl) {
                            $pgProgLotPeriodeProgCompl->setPprogCompl(null);
                            $emSqe->persist($pgProgLotPeriodeProgCompl);
                        }
                        $emSqe->remove($pgProgLotPeriodeProg);
                    }
                }
                $emSqe->remove($pgProgLotStationAn);
            }
        }
         $emSqe->flush();

        foreach ($tabStations as $station) {
            $pgRefStationMesure = $station['station'];
            $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($station['reseau']);
            $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
            if (!$pgProgLotStationAn) {
                $pgProgLotStationAn = new PgProgLotStationAn();
            }
            $pgProgLotStationAn->setLotAn($pgProgLotAn);
            $pgProgLotStationAn->setStation($pgRefStationMesure);
            $pgProgLotStationAn->setRsxId($pgRefReseauMesure->getGroupementId());
//                $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('CRE');
//                $pgProgLotStationAn->setCodeStatut($pgProgStatut);
//                $now = date('Y-m-d');
//                $now = new \DateTime($now);
//                $pgProgLotStationAn->setDateModif($now);
//                $pgProgLotStationAn->setUtilModif($pgProgWebuser);
            $emSqe->persist($pgProgLotStationAn);
           // print_r('station : ' . $pgRefStationMesure->getCode() . '<br/>');
        }
         $emSqe->flush();
         

        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $nbPeriodes = 0;
        foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
            $nbPgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->countPgProgLotPeriodeProgByStationAn($pgProgLotStationAn);
            $nbPeriodes = $nbPeriodes + $nbPgProgLotPeriodeProgs;
        }
        if ($nbPeriodes == 0) {
            $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P10');
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

        $session->set('messageErreur', $tabMessage);
        if ($suivant == 'station') {
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_stations', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        } elseif ($suivant == 'groupe') {
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_groupes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        } elseif ($suivant == 'periode') {
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_periodes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        } elseif ($suivant == 'bilan') {
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_bilan', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        } else {
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_groupes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        }
    }

    public function telechargerAction($lotan = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationStation');
        $session->set('fonction', 'telecharger');

        //recupération des parametres
        $pgProgLotAnId = $lotan;

        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        asort($pgProgLotStationAns);
        $tabStations = array();
        $i = 0;
        foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
            $stationGeo = $pgProgLotStationAn->getStation();
            $tabStations[$i]['station']['ouvFoncId'] = $stationGeo->getOuvfoncId();
            $tabStations[$i]['station']['code'] = $stationGeo->getCode();
            $tabStations[$i]['station']['libelle'] = $stationGeo->getLibelle();
            $tabStations[$i]['station']['codeMasdo'] = $stationGeo->getCodeMasdo();
            $tabStations[$i]['station']['nomCoursEau'] = $stationGeo->getNomCoursEau();
            $tabStations[$i]['lotStationAn'] = null;
            $tabStations[$i]['commune']['libelle'] = $stationGeo->getNomCommune();
            $tabStations[$i]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $stationGeo->getCode()) . '.pdf';
            $reseau = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
            $tabStations[$i]['reseau'] = $reseau;
            $pgProgLotStationAnAutres = $repoPgProgLotStationAn->getPgProgLotStationAnByStation($stationGeo);
            $tabStations[$i]['autreLot'] = null;
            $i++;
        }

        $chemin = '/base/extranet/Transfert/Sqe/csv';
        $fichier = 'Stations-programmation-' . $pgProgLotAn->getAnneeProg() . '-' . $pgProgLotAnId . '.csv';
        $fullFileName = $chemin . '/' . $fichier;
        $ext = strtolower(pathinfo($fullFileName, PATHINFO_EXTENSION));
        if (file_exists($fullFileName)) {
            unlink($fullFileName);
        }
        $fichier_csv = fopen($fullFileName, 'w+');
        // Entete
        $ligne = array('Programmation', 'Version', 'Lot', 'Station', 'Libellé', 'Commune', 'Masse d\'eau', 'Rivière', 'Réseau');
        for ($i = 0; $i < count($ligne); $i++) {
            $ligne[$i] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$i]);
        }
        fputcsv($fichier_csv, $ligne, ';');

        for ($i = 0; $i < count($tabStations); $i++) {
            $ligne = array($pgProgLotAn->getAnneeProg(),
                $pgProgLotAn->getversion(),
                $pgProgLotAn->getLot()->getNomLot(),
                $tabStations[$i]['station']['code'],
                $tabStations[$i]['station']['libelle'],
                $tabStations[$i]['commune']['libelle'],
                $tabStations[$i]['station']['codeMasdo'],
                $tabStations[$i]['station']['nomCoursEau'],
                $tabStations[$i]['reseau']->getNomRsx()
            );
            for ($j = 0; $j < count($ligne); $j++) {
                $ligne[$j] = \iconv("UTF-8", "Windows-1252//TRANSLIT", $ligne[$j]);
            }
            fputcsv($fichier_csv, $ligne, ';');
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
//
//        \Symfony\Component\VarDumper\VarDumper::dump($tabStations);
//        return new Response('');
    }

}
