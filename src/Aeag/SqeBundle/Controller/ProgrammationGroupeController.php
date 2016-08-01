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
use Aeag\SqeBundle\Entity\PgProgLotParamAn;
use Aeag\SqeBundle\Entity\PgProgSuiviPhases;
use Aeag\AeagBundle\Controller\AeagController;
use Aeag\SqeBundle\Controller\ProgrammationBilanController;

class ProgrammationGroupeController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationGroupe');
        $session->set('fonction', 'index');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

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

        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgWebuserZgeoref = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserZgeoref');
        $repoPgProgZgeorefTypmil = $emSqe->getRepository('AeagSqeBundle:PgProgZgeorefTypmil');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $repoPgProgLotGrparRef = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparRef');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotPresta = $emSqe->getRepository('AeagSqeBundle:PgProgLotPresta');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProgGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');
        $repoPgSandreSupports = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgProgGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgMarche = $pgProgLot->getMarche();
        $pgProgZoneGeoRef = $pgProgLot->getZgeoRef();
        $typeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgGrpParamRefs = $repoPgProgGrpParamRef->getPgProgGrpParamRefByCodeMilieu($pgProgTypeMilieu);
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);


        if (is_object($user)) {
            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);
        }

        if ($session->has('choixParametre')) {
            $session->remove('choixParametre');
        }

        if ($session->has('selectionGroupes')) {
            $session->remove('selectionGroupes');
        }



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


// récuperation des préléveurs
        $tabPreleveurs = array();
        $pgProgLotPrestas = $repoPgProgLotPresta->getPgProgLotPrestaByLotTypePresta($pgProgLotAn->getLot(), 'P');
        if (count($pgProgLotPrestas) > 0) {
            foreach ($pgProgLotPrestas as $pgProgLotPresta) {
                $trouve = false;
                $size = count($tabPreleveurs);
                for ($i = 0; $i < $size; $i++) {
                    if ($tabPreleveurs[$i] == $pgProgLotPresta->getPresta()) {
                        $trouve = true;
                        $i = $size + 1;
                    }
                }
                if ($trouve == false) {
                    $i = $size;
                    $tabPreleveurs[$i] = $pgProgLotPresta->getPresta();
                }
            }
        } else {
            $i = 0;
            $pgRefCorresPrestas = $repoPgRefCorresPresta->getPgRefCorresPrestas();
            foreach ($pgRefCorresPrestas as $pgRefCorresPresta) {
                $tabPreleveurs[$i] = $pgRefCorresPresta;
                $i++;
            }
        }

// récuperation des laboratoires
        $tabLaboratoires = array();
        $pgProgLotPrestas = $repoPgProgLotPresta->getPgProgLotPrestaByLotTypePresta($pgProgLotAn->getLot(), 'L');
        if (count($pgProgLotPrestas) > 0) {
            foreach ($pgProgLotPrestas as $pgProgLotPresta) {
                $trouve = false;
                $size = count($tabLaboratoires);
                for ($i = 0; $i < $size; $i++) {
                    if ($tabLaboratoires[$i] == $pgProgLotPresta->getPresta()) {
                        $trouve = true;
                        $i = $size + 1;
                    }
                }
                if ($trouve == false) {
                    $i = $size;
                    $tabLaboratoires[$i] = $pgProgLotPresta->getPresta();
                }
            }
        } else {
            $i = 0;
            $pgRefCorresPrestas = $repoPgRefCorresPresta->getPgRefCorresPrestas();
            foreach ($pgRefCorresPrestas as $pgRefCorresPresta) {
                $tabLaboratoires[$i] = $pgRefCorresPresta;
                $i++;
            }
        }


        // récuperation des groupes de parametres liés au lot et type de milieu
        $tabGroupes = array();
        $i = 0;
        $pgProgLotGrparRefs = $repoPgProgLotGrparRef->getPgProgLotGrparRefByLot($pgProgLot);
        foreach ($pgProgLotGrparRefs as $pgProgLotGrparRef) {
            $pgProgGrpParamRef = $pgProgLotGrparRef->getGrpparref();
            if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $pgProgGrpParamRef = $pgProgLotGrparRef->getGrpparref();
                if ($action == 'P' and $maj != 'V') {
                    if ($pgProgGrpParamRef->getValide() == 'O') {
                        $tabGroupes[$i]['groupe'] = $pgProgGrpParamRef;
                        $tabGroupes[$i]['origine'] = 'R';
                        $tabGroupes[$i]['valide'] = 'N';
                        $tabGroupes[$i]['prestataire'] = null;
                        $i++;
                    }
                } else {
                    if ($pgProgGrpParamRef->getValide() == 'O') {
                        $tabGroupes[$i]['groupe'] = $pgProgGrpParamRef;
                        $tabGroupes[$i]['origine'] = 'R';
                        $tabGroupes[$i]['valide'] = 'N';
                        $tabGroupes[$i]['prestataire'] = null;
                        $i++;
                    }
                }
            }
        }
        $size = count($tabGroupes);
        if ($size > 0) {
            $l = 0;
            $j = 0;
            for ($i = 0; $i < $size; $i++) {
                // recuperation des groupes de parametres obligatoires lies au meme support et type de milieu
                $support = $tabGroupes[$i]['groupe']->getSupport();
                if ($support) {
                    $pgProgGrparObligSupports = $repoPgProgGrparObligSupport->getPgProgGrparObligSupportByCodeSupport($support->getCodeSupport());
                    foreach ($pgProgGrparObligSupports as $pgProgGrparObligSupport) {
                        $pgProgGrpParamRefCompl = $repoPgProgGrpParamRef->getPgProgGrpParamRefById($pgProgGrparObligSupport->getGrparRefId());
                        if ($pgProgGrpParamRefCompl->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                            $trouve = false;
                            $size = count($tabGroupes);
                            for ($j = 0; $j < $size; $j++) {
                                if ($tabGroupes[$j]['groupe']->getId() == $pgProgGrpParamRefCompl->getId()) {
                                    $tabGroupes[$j]['prestataire'] = $tabGroupes[$i]['prestataire'];
                                    $trouve = true;
                                    $j = count($tabGroupes) + 1;
                                }
                            }
                            if ($trouve == false and $tabGroupes[$i]['origine'] == 'R') {
                                if ($action == 'P' and $maj != 'V') {
                                    if ($pgProgGrpParamRefCompl->getValide() == 'O') {
                                        $j = $size;
                                        $tabGroupes[$j]['groupe'] = $pgProgGrpParamRefCompl;
                                        $tabGroupes[$j]['prestataire'] = $tabGroupes[$i]['prestataire'];
                                        $tabGroupes[$j]['origine'] = 'RC';
                                        $tabGroupes[$j]['valide'] = 'N';
                                    }
                                } else {
                                    if ($pgProgGrpParamRefCompl->getValide() == 'O') {
                                        $j = $size;
                                        $tabGroupes[$j]['groupe'] = $pgProgGrpParamRefCompl;
                                        $tabGroupes[$j]['prestataire'] = $tabGroupes[$i]['prestataire'];
                                        $tabGroupes[$j]['origine'] = 'RC';
                                        $tabGroupes[$j]['valide'] = 'N';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }



        // recuperation des groupes de parametres programmés pour le lot
        $size = count($tabGroupes);
        $l = 0;
        $support = null;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            if ($session->get('browser') == 'Internet Explorer') {
                if (!$session->has('selectionPreleveur_ie') or ! $session->get('selectionPreleveur_ie')) {
                    if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() != 'ANA') {
                        if ($pgProgLotGrparAn->getPrestaDft()) {
                            $session->set('selectionPreleveur_ie', $pgProgLotGrparAn->getPrestaDft()->getAdrCorid());
                        }
                    }
                }
                if (!$session->has('selectionLaboratoire_ie') or ! $session->get('selectionLaboratoire_ie')) {
                    if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                        if ($pgProgLotGrparAn->getPrestaDft()) {
                            $session->set('selectionLaboratoire_ie', $pgProgLotGrparAn->getPrestaDft()->getAdrCorid());
                            // return new Response('browser : ' . $session->get('browser') . ' laboratoire :  ' . $session->get('selectionLaboratoire_ie'));
                        }
                    }
                }
            } else {
                if (!$session->has('selectionPreleveur') or ! $session->get('selectionPreleveur')) {
                    if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() != 'ANA') {
                        if ($pgProgLotGrparAn->getPrestaDft()) {
                            $session->set('selectionPreleveur', $pgProgLotGrparAn->getPrestaDft()->getAncnumNomCorres());
                        }
                    }
                }
                if (!$session->has('selectionLaboratoire') or ! $session->get('selectionLaboratoire')) {
                    if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                        if ($pgProgLotGrparAn->getPrestaDft()) {
                            $session->set('selectionLaboratoire', $pgProgLotGrparAn->getPrestaDft()->getAncnumNomCorres());
                        }
                    }
                }
            }
            $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
            if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                $trouve = false;
                for ($i = 0; $i < $size; $i++) {
                    if ($tabGroupes[$i]['groupe']->getId() == $pgProgGrpParamRef->getId()) {
                        $tabGroupes[$i]['prestataire'] = $pgProgLotGrparAn->getPrestaDft();
                        $tabGroupes[$i]['valide'] = $pgProgLotGrparAn->getValide();
                        $trouve = true;
                        $i = $size + 1;
                    }
                }
                if ($trouve == false) {
                    if ($action == 'P' and $maj != 'V') {
                        if ($pgProgGrpParamRef->getValide() == 'O') {
                            $i = $size;
                            $tabGroupes[$i]['groupe'] = $pgProgGrpParamRef;
                            $tabGroupes[$i]['origine'] = $pgProgLotGrparAn->getOrigine();
                            $tabGroupes[$i]['prestataire'] = $pgProgLotGrparAn->getPrestaDft();
                            $tabGroupes[$i]['valide'] = $pgProgLotGrparAn->getValide();
                            $support = $tabGroupes[$i]['groupe']->getSupport();
                            $size = count($tabGroupes);
                            if ($support) {
                                $pgProgGrparObligSupports = $repoPgProgGrparObligSupport->getPgProgGrparObligSupportByCodeSupport($support->getCodeSupport());
                                foreach ($pgProgGrparObligSupports as $pgProgGrparObligSupport) {
                                    $pgProgGrpParamRefCompl = $repoPgProgGrpParamRef->getPgProgGrpParamRefById($pgProgGrparObligSupport->getGrparRefId());
                                    if ($pgProgGrpParamRefCompl->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                                        $trouve = false;
                                        $size = count($tabGroupes);
                                        for ($j = 0; $j < $size; $j++) {
                                            if ($tabGroupes[$j]['groupe']->getId() == $pgProgGrpParamRefCompl->getId()) {
                                                $trouve = true;
                                                $k = $j;
                                                $j = $size + 1;
                                            }
                                        }
                                        if ($trouve == false and $tabGroupes[$i]['origine'] == 'R') {
                                            if ($action == 'P' and $maj != 'V') {
                                                if ($pgProgGrpParamRefCompl->getValide() == 'O') {
                                                    $j = $size;
                                                    $tabGroupes[$j]['groupe'] = $pgProgGrpParamRefCompl;
                                                    $tabGroupes[$j]['prestataire'] = $pgProgLotGrparAn->getPrestaDft();
                                                    $tabGroupes[$i]['valide'] = $pgProgLotGrparAn->getValide();
                                                    $tabGroupes[$j]['origine'] = 'RC';
                                                }
                                            } else {
                                                $j = $size;
                                                $tabGroupes[$j]['groupe'] = $pgProgGrpParamRefCompl;
                                                $tabGroupes[$j]['prestataire'] = $pgProgLotGrparAn->getPrestaDft();
                                                $tabGroupes[$i]['valide'] = $pgProgLotGrparAn->getValide();
                                                $tabGroupes[$j]['origine'] = 'RC';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        if ($pgProgGrpParamRef->getValide() == 'O') {
                            $i = $size;
                            $tabGroupes[$i]['groupe'] = $pgProgGrpParamRef;
                            $tabGroupes[$i]['origine'] = $pgProgLotGrparAn->getOrigine();
                            $tabGroupes[$i]['prestataire'] = $pgProgLotGrparAn->getPrestaDft();
                            $tabGroupes[$i]['valide'] = $pgProgLotGrparAn->getValide();
                            $support = $tabGroupes[$i]['groupe']->getSupport();
                            $size = count($tabGroupes);
                            if ($support) {
                                $pgProgGrparObligSupports = $repoPgProgGrparObligSupport->getPgProgGrparObligSupportByCodeSupport($support->getCodeSupport());
                                foreach ($pgProgGrparObligSupports as $pgProgGrparObligSupport) {
                                    $pgProgGrpParamRefCompl = $repoPgProgGrpParamRef->getPgProgGrpParamRefById($pgProgGrparObligSupport->getGrparRefId());
                                    if ($pgProgGrpParamRefCompl->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                                        $trouve = false;
                                        $size = count($tabGroupes);
                                        for ($j = 0; $j < $size; $j++) {
                                            if ($tabGroupes[$j]['groupe']->getId() == $pgProgGrpParamRefCompl->getId()) {
                                                $trouve = true;
                                                $k = $j;
                                                $j = $size + 1;
                                            }
                                        }
                                        if ($trouve == false and $tabGroupes[$i]['origine'] == 'R') {
                                            $j = $size;
                                            $tabGroupes[$j]['groupe'] = $pgProgGrpParamRefCompl;
                                            $tabGroupes[$j]['prestataire'] = $pgProgLotGrparAn->getPrestaDft();
                                            $tabGroupes[$i]['valide'] = $pgProgLotGrparAn->getValide();
                                            $tabGroupes[$j]['origine'] = 'RC';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        //asort($tabGroupes);
        $tabGroupesAeagCles = array();
        $size = count($tabGroupes);

        for ($i = 0; $i < $size; ++$i) {


            $pgProgLotGrparAn = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotAnGrpparref($pgProgLotAn, $tabGroupes[$i]['groupe']);
            if (!$pgProgLotGrparAn and $maj != 'V') {
                $pgProgLotGrparAn = new PgProgLotGrparAn();
                $pgProgLotGrparAn->setLotAn($pgProgLotAn);
                $pgProgLotGrparAn->setGrparRef($tabGroupes[$i]['groupe']);
                $pgProgLotGrparAn->setValide('N');
                $pgProgLotGrparAn->setOrigine($tabGroupes[$i]['origine']);
//                if ($pgProgGrpParamRef->getTypeGrp() == 'ANA') {
//                    $pgProgLotGrparAn->setPrestaDft($tabLaboratoires[0]);
//                } else {
//                    $pgProgLotGrparAn->setPrestaDft($tabPreleveurs[0]);
//                }
                $emSqe->persist($pgProgLotGrparAn);
                $emSqe->flush();
            }


            if ($action == 'P' and $maj != 'V') {
                $nbPgProgGrparRefLstParams = $repoPgProgGrparRefLstParam->getNbPgProgGrparRefLstParamValideByGrparRef($tabGroupes[$i]['groupe']);
            } else {
                $nbPgProgGrparRefLstParams = $repoPgProgGrparRefLstParam->getNbPgProgGrparRefLstParamByGrparRef($tabGroupes[$i]['groupe']);
            }
            $j = 0;
            $tabParametres = array();
            $tabParametres['nbPgProgGrparRefLstParam'] = $nbPgProgGrparRefLstParams;
            $tabParametres['nbPgProgLotParamAns'] = 0;
            if ($tabGroupes[$i]['valide'] == 'O') {
                $pgProgLotGrparAn = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotAnGrpparref($pgProgLotAn, $tabGroupes[$i]['groupe']);
                if ($pgProgLotGrparAn) {
                    $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                    $tabParametres['nbPgProgLotParamAns'] = count($pgProgLotParamAns);
                }
            }
            $tabGroupes[$i]['parametres'] = $tabParametres;
            $tabGroupesAeagCles[$i] = $tabGroupes[$i]['groupe']->getId();
        }

        $session->set('groupesAeag', serialize($tabGroupesAeagCles));
        // recuperation des autres groupes lies au type de milieu
        $tabGroupesMilieu = array();
        $i = 0;
        foreach ($pgProgGrpParamRefs as $groupeMilieu) {
            $trouve = false;
            $size = count($tabGroupesMilieu);
            for ($i = 0; $i < $size; $i++) {
                if ($tabGroupesMilieu[$i]['groupe']->getId() == $groupeMilieu->getid()) {
                    $trouve = true;
                    $i = $size + 1;
                }
            }
            if ($trouve == false) {
                $size = count($tabGroupes);
                for ($i = 0; $i < $size; $i++) {
                    if ($tabGroupes[$i]['groupe']->getId() == $groupeMilieu->getid()) {
                        $trouve = true;
                        $i = $size + 1;
                    }
                }
            }
            if ($trouve == false) {
                if ($groupeMilieu->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                    if ($action == 'P' and $maj != 'V') {
                        if ($groupeMilieu->getValide() == 'O') {
                            $i = count($tabGroupesMilieu);
                            $tabGroupesMilieu[$i]['groupe'] = $groupeMilieu;
                            $tabGroupesMilieu[$i]['origine'] = 'C';
                            $tabGroupesMilieu[$i]['cocher'] = 'N';
                            $tabGroupesMilieu[$i]['prestataire'] = null;
                        }
                    } else {
                        if ($groupeMilieu->getValide() == 'O') {
                            $i = count($tabGroupesMilieu);
                            $tabGroupesMilieu[$i]['groupe'] = $groupeMilieu;
                            $tabGroupesMilieu[$i]['origine'] = 'C';
                            $tabGroupesMilieu[$i]['cocher'] = 'N';
                            $tabGroupesMilieu[$i]['prestataire'] = null;
                        }
                    }
                }
            }
        }
        //asort($tabGroupesMilieu);

        $tabGroupeMilieuCles = array();
        $size = count($tabGroupesMilieu);
        for ($i = 0; $i < $size; ++$i) {
            $tabGroupesMilieuCles[$i] = $tabGroupesMilieu[$i]['groupe']->getId();
        }
        $session->set('groupesMilieu', serialize($tabGroupesMilieuCles));
        // controle des groupes sélectionnés
        if ($session->has('selectionGroupes')) {
            $choixGroupes = $session->get('selectionGroupes');
            foreach ($choixGroupes as $groupeSelectionne) {
                $size = count($tabGroupesMilieu);
                for ($i = 0; $i < $size; $i++) {
                    if ($tabGroupesMilieu[$i]['groupe']->getId() == $groupeSelectionne) {
                        if ($tabGroupesMilieu[$i]['origine'] == 'C') {
                            $tabGroupesMilieu[$i]['cocher'] = 'O';
                        }
                    }
                }
            }
        }


        // recupérations des parametres liés au type de milieu
        $tabSandreParametres = array();
        $j = 0;
        foreach ($pgProgGrpParamRefs as $pgProgGrpParamRef) {
            $pgProgGrparRefLstParams = $repoPgProgGrparRefLstParam->getPgProgGrparRefLstParamByGrparRef($pgProgGrpParamRef);
            foreach ($pgProgGrparRefLstParams as $pgProgGrparRefLstParam) {
                $trouve = false;
                $size = count($tabSandreParametres);
                for ($i = 0; $i < $size; $i++) {
                    if ($tabSandreParametres[$i]->getCodeParametre() == $pgProgGrparRefLstParam->getCodeParametre()->getCodeParametre()) {
                        $trouve = true;
                        $i = $size + 1;
                    }
                }
                if ($trouve == false) {
                    $i = $size;
                    $tabSandreParametres[$i] = $pgProgGrparRefLstParam->getCodeParametre();
                }
            }
        }
        //asort($tabSandreParametres);
//$session->set('niveau1', $this->generateUrl('AeagSqeBundle_programmation', array('action' => $action)));
        $session->set('niveau1', $this->generateUrl('AeagSqeBundle_programmation_groupes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));

        $tabControle = ProgrammationBilanController::controleProgrammationAction($pgProgLotAnId, $emSqe, $session);

        return $this->render('AeagSqeBundle:Programmation:Groupe\index.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'campagne' => $session->get('critAnnee'),
                    'lotan' => $pgProgLotAn,
                    'controle' => $tabControle,
                    'typeMilieu' => $pgProgTypeMilieu,
                    'preleveurs' => $tabPreleveurs,
                    'laboratoires' => $tabLaboratoires,
                    'groupes' => $tabGroupes,
                    'groupesMilieu' => $tabGroupesMilieu,
                    'parametres' => $tabSandreParametres,
                    'messages' => $tabMessages));
    }

    public function groupeParametresAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationGroupe');
        $session->set('fonction', 'groupeParametres');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProgGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgLotPresta = $emSqe->getRepository('AeagSqeBundle:PgProgLotPresta');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();

        $idGroupe = $request->get('groupe');
        if ($session->get('browser') == 'Internet Explorer') {
            $prestataireAdrCorId = $request->get('prestataire');
        } else {
            $prestataireAncnumComplet = explode(" ", $request->get('prestataire'));
            $prestataireAncnum = $prestataireAncnumComplet[0];
            $prestataire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAncnum($prestataireAncnum);
            $prestataireAdrCorId = $prestataire->getAdrCorId();
        }

        $pgProgGrpParamRef = $repoPgProgGrpParamRef->getPgProgGrpParamRefById($idGroupe);
        $prestataire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($prestataireAdrCorId);

        $tabGroupes = array();
        $tabGroupes['groupe'] = $pgProgGrpParamRef;
        $tabGroupes['prestataire'] = null;
        if ($prestataire) {
            $tabGroupes['prestataire'] = $prestataire;
        }
        $pgProgGrparRefLstParams = $repoPgProgGrparRefLstParam->getPgProgGrparRefLstParamByGrparRef($pgProgGrpParamRef);
        $tabParametres = array();
        $j = 0;
        foreach ($pgProgGrparRefLstParams as $pgProgGrparRefLstParam) {
            if ($action == 'P' and $maj != 'V') {
                if ($pgProgGrparRefLstParam->getValide() == 'O') {
                    $tabParametres[$j]['pgProgGrparRefLstParam'] = $pgProgGrparRefLstParam;
                    $pgSandreParametre = $pgProgGrparRefLstParam->getCodeParametre();
                    if ($pgProgGrparRefLstParam->getCodeFraction()) {
                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgGrparRefLstParam->getCodeFraction()->getCodeFraction());
                    } else {
                        $pgSandreFraction = null;
                    }
                    $pgSandreUnite = $pgProgGrparRefLstParam->getUniteDefaut();
                    $tabParametres[$j]['pgSandreParametre'] = $pgSandreParametre;
                    $tabParametres[$j]['pgSandreFraction'] = $pgSandreFraction;
                    $tabParametres[$j]['pgSandreUnite'] = $pgSandreUnite;
                    $tabParametres[$j]['prestataire'] = $prestataire;
                    $tabParametres[$j]['cocher'] = $pgProgGrparRefLstParam->getParamDefaut();
                    $j++;
                }
            } else {
                $tabParametres[$j]['pgProgGrparRefLstParam'] = $pgProgGrparRefLstParam;
                $pgSandreParametre = $pgProgGrparRefLstParam->getCodeParametre();
                if ($pgProgGrparRefLstParam->getCodeFraction()) {
                    $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgGrparRefLstParam->getCodeFraction()->getCodeFraction());
                } else {
                    $pgSandreFraction = null;
                }
                $pgSandreUnite = $pgProgGrparRefLstParam->getUniteDefaut();
                $tabParametres[$j]['pgSandreParametre'] = $pgSandreParametre;
                $tabParametres[$j]['pgSandreFraction'] = $pgSandreFraction;
                $tabParametres[$j]['pgSandreUnite'] = $pgSandreUnite;
                $tabParametres[$j]['prestataire'] = $prestataire;
                $tabParametres[$j]['cocher'] = $pgProgGrparRefLstParam->getParamDefaut();
                $j++;
            }
        }

        $pgProgLotGrparAn = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotAnGrpparref($pgProgLotAn, $tabGroupes['groupe']);
        if ($pgProgLotGrparAn) {
            $valide = $pgProgLotGrparAn->getValide();
            //$tabGroupes['prestataire'] = $pgProgLotGrparAn->getPrestaDft();
            $sizeTab = count($tabParametres);
            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
            if (count($pgProgLotParamAns)) {
                for ($j = 0; $j < $sizeTab; ++$j) {
                    $tabParametres[$j]['cocher'] = 'N';
                }
            }
            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                if (!$pgProgLotGrparAn->getPrestaDft()) {
                    $emSqe->remove($pgProgLotParamAn);
                } else {
                    for ($j = 0; $j < $sizeTab; ++$j) {
                        if ($tabParametres[$j]['pgSandreParametre']->getCodeParametre() == $pgProgLotParamAn->getCodeParametre()->getCodeParametre()) {
                            $tabParametres[$j]['prestataire'] = $pgProgLotParamAn->getPrestataire();
                            $tabParametres[$j]['cocher'] = 'O';
                            $j = $sizeTab + 1;
                        }
                    }
                }
            }
        } else {
            $valide = 'N';
        }
        $tabGroupes['parametres'] = $tabParametres;


        // récuperation des laboratoires
        $tabLaboratoires = array();
        $pgProgLotPrestas = $repoPgProgLotPresta->getPgProgLotPrestaByLotTypePresta($pgProgLotAn->getLot(), 'L');
        if ($pgProgLotPrestas) {
            foreach ($pgProgLotPrestas as $pgProgLotPresta) {
                $trouve = false;
                $size = count($tabLaboratoires);
                for ($i = 0; $i < $size; $i++) {
                    if ($tabLaboratoires[$i] == $pgProgLotPresta->getPresta()) {
                        $trouve = true;
                        $i = $size + 1;
                    }
                }
                if ($trouve == false) {
                    $i = $size;
                    $tabLaboratoires[$i] = $pgProgLotPresta->getPresta();
                }
            }
        } else {
            $i = 0;
            $pgRefCorresPrestas = $repoPgRefCorresPresta->getPgRefCorresPrestas();
            foreach ($pgRefCorresPrestas as $pgRefCorresPresta) {
                $tabLaboratoires[$i] = $pgRefCorresPresta;
                $i++;
            }
        }


        return $this->render('AeagSqeBundle:Programmation:Groupe/groupeParametres.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'lotan' => $pgProgLotAn,
                    'laboratoires' => $tabLaboratoires,
                    'valide' => $valide,
                    'groupe' => $tabGroupes));
    }

    public function groupeParametrePrestatairesAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationGroupe');
        $session->set('fonction', 'groupeParametres');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProgGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgLotPresta = $emSqe->getRepository('AeagSqeBundle:PgProgLotPresta');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();

        $idGroupe = $request->get('groupe');
        if ($session->get('browser') == 'Internet Explorer') {
            $prestataireAdrCorId = $request->get('prestataire');
        } else {
            $prestataireAncnumComplet = explode(" ", $request->get('prestataire'));
            $prestataireAncnum = $prestataireAncnumComplet[0];
            $prestataire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAncnum($prestataireAncnum);
            $prestataireAdrCorId = $prestataire->getAdrCorId();
        }

        $pgProgGrpParamRef = $repoPgProgGrpParamRef->getPgProgGrpParamRefById($idGroupe);
        $prestataire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($prestataireAdrCorId);

        $pgProgLotGrparAn = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotAnGrpparref($pgProgLotAn, $pgProgGrpParamRef);
        if ($pgProgLotGrparAn) {
            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                $pgProgLotParamAn->setPrestataire($prestataire);
                $emSqe->persist($pgProgLotParamAn);
            }
            $emSqe->flush();
        }

        return new Response('ok');
    }

    public function milieuGroupeParametresAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationGroupe');
        $session->set('fonction', 'milieuGroupeParametres');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProgGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');


        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);

        $idGroupe = $request->get('groupe');
        $tabGroupes = array();
        $pgProgGrpParamRef = $repoPgProgGrpParamRef->getPgProgGrpParamRefById($idGroupe);
        $tabGroupes['groupe'] = $pgProgGrpParamRef;
        $pgProgGrparRefLstParams = $repoPgProgGrparRefLstParam->getPgProgGrparRefLstParamByGrparRef($pgProgGrpParamRef);
        $tabParametres = array();
        $j = 0;
        foreach ($pgProgGrparRefLstParams as $pgProgGrparRefLstParam) {
            $tabParametres[$j]['pgProgGrparRefLstParam'] = $pgProgGrparRefLstParam;
            $pgSandreParametre = $pgProgGrparRefLstParam->getCodeParametre();
            if ($pgProgGrparRefLstParam->getCodeFraction()) {
                $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgGrparRefLstParam->getCodeFraction()->getCodeFraction());
            } else {
                $pgSandreFraction = null;
            }
            $pgSandreUnite = $pgProgGrparRefLstParam->getUniteDefaut();
            $tabParametres[$j]['pgSandreParametre'] = $pgSandreParametre;
            $tabParametres[$j]['pgSandreFraction'] = $pgSandreFraction;
            $tabParametres[$j]['pgSandreUnite'] = $pgSandreUnite;
            $j++;
        }
        $tabGroupes['parametres'] = $tabParametres;
        return $this->render('AeagSqeBundle:Programmation:Groupe/milieuGroupeParametres.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'lotan' => $pgProgLotAn,
                    'groupe' => $tabGroupes));
    }

    public function selectionnerAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationGroupe');
        $session->set('fonction', 'selectionner');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProgGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');


        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();

        $tabGroupes = array();
        $i = 0;

        $session->set('messageErreur', null);

        $request = $this->container->get('request');

        $groupe = $request->get('groupe');
        $coche = $request->get('coche');

        $i = 0;
        $pgProgGrpParamRef = $repoPgProgGrpParamRef->getPgProgGrpParamRefById($groupe);
        $tabGroupes[$i]['groupe'] = $pgProgGrpParamRef;
        if ($coche == 'O') {
            $tabGroupes[$i]['cocher'] = 'N';
        } else {
            $tabGroupes[$i]['cocher'] = 'O';
        }

        for ($i = 0; $i < count($tabGroupes); $i++) {
            $pgProgGrparRefLstParams = $repoPgProgGrparRefLstParam->getPgProgGrparRefLstParamByGrparRef($tabGroupes[$i]['groupe']);
            $j = 0;
            $tabParametres = array();
            foreach ($pgProgGrparRefLstParams as $pgProgGrparRefLstParam) {
                $tabParametres[$j]['pgProgGrparRefLstParam'] = $pgProgGrparRefLstParam;
                $pgSandreParametre = $pgProgGrparRefLstParam->getCodeParametre();
                if ($pgProgGrparRefLstParam->getCodeFraction()) {
                    $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgGrparRefLstParam->getCodeFraction()->getCodeFraction());
                } else {
                    $pgSandreFraction = null;
                } $pgSandreUnite = $pgProgGrparRefLstParam->getUniteDefaut();
                $tabParametres[$j]['pgSandreParametre'] = $pgSandreParametre;
                $tabParametres[$j]['pgSandreFraction'] = $pgSandreFraction;
                $tabParametres[$j]['pgSandreUnite'] = $pgSandreUnite;
                $j++;
            }
            $tabGroupes[$i]['parametres'] = $tabParametres;
        }



        return $this->render('AeagSqeBundle:Programmation:Groupe/selectionner.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'lotan' => $pgProgLotAn,
                    'lot' => $pgProgLot,
                    'typeMilieu' => $pgProgTypeMilieu,
                    'groupes' => $tabGroupes));
    }

    public function ajouterAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationGroupe');
        $session->set('fonction', 'Ajouter');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProgGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $repoPgProgGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();


        $tabMessage = array();
        $tabgroupes = array();
        $i = 0;

        if ($session->has('choixGroupes')) {
            $session->remove('choixGroupes');
        }
        if ($session->has('ajouterGroupes')) {
            $session->remove('ajouterGroupes');
        }

        if ($session->has('selectionPreleveur_ie')) {
            $session->remove('selectionPreleveur_ie');
        }

        if ($session->has('selectionLaboratoire_ie')) {
            $session->remove('selectionLaboratoire_ie');
        }

        if ($session->has('selectionPreleveur')) {
            $session->remove('selectionPreleveur');
        }

        if ($session->has('selectionLaboratoire')) {
            $session->remove('selectionLaboratoire');
        }

        $session->set('messageErreur', null);
        $i = 0;

        if (!empty($_POST['check'])) {
            $groupesMilieu = $_POST['check'];
            $preleveurCorId = null;
            if ($session->get('browser') == 'Internet Explorer') {
                if (isset($_POST['preleveur_ie'])) {
                    if ($_POST['preleveur_ie'] != '') {
                        $preleveurCorId = $_POST['preleveur_ie'];
                        $preleveur = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($preleveurCorId);
                    } else {
                        $tabMessage[$i] = 'Renseigner d\'abord le préleveur avant d\'ajouter un groupe';
                        $i++;
                    }
                } else {
                    $tabMessage[$i] = 'Renseigner d\'abord le préleveur avant d\'ajouter un groupe';
                    $i++;
                }
                $laboratoireCorId = null;
                if (isset($_POST['laboratoire_ie'])) {
                    if ($_POST['laboratoire_ie'] != '') {
                        $laboratoireCorId = $_POST['laboratoire_ie'];
                        $laboratoire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($laboratoireCorId);
                    } else {
                        $tabMessage[$i] = 'Renseigner d\'abord le laboratoire avant d\'ajouter un groupe';
                        $i++;
                    }
                } else {
                    $tabMessage[$i] = 'Renseigner d\'abord le laboratoire avant d\'ajouter un groupe';
                    $i++;
                }
                $session->set('selectionPreleveur_ie', $preleveurCorId);
                $session->set('selectionLaboratoire_ie', $laboratoireCorId);
            } else {
                if (isset($_POST['preleveur'])) {
                    if ($_POST['preleveur'] != '') {
                        $preleveurAncnumComplet = explode(" ", $_POST['preleveur']);
                        $preleveurAncnum = $preleveurAncnumComplet[0];
                        $preleveur = $repoPgRefCorresPresta->getPgRefCorresPrestaByAncnum($preleveurAncnum);
                        $session->set('selectionPreleveur', $preleveur->getAncnumNomCorres());
                        $preleveurCorId = $preleveur->getAdrCorid();
                    } else {
                        $tabMessage[$i] = 'Renseigner d\'abord le préleveur avant d\'ajouter un groupe';
                        $i++;
                    }
                } else {
                    $tabMessage[$i] = 'Renseigner d\'abord le préleveur avant d\'ajouter un groupe';
                    $i++;
                }
                $laboratoireCorId = null;
                if (isset($_POST['laboratoire'])) {
                    if ($_POST['laboratoire'] != '') {
                        $laboratoireAncnumComplet = explode(" ", $_POST['laboratoire']);
                        $laboratoireAncnum = $laboratoireAncnumComplet[0];
                        $laboratoire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAncnum($laboratoireAncnum);
                        $session->set('selectionLaboratoire', $laboratoire->getAncnumNomCorres());
                        $laboratoireCorId = $laboratoire->getAdrCorId();
                    } else {
                        $tabMessage[$i] = 'Renseigner d\'abord le laboratoire avant d\'ajouter un groupe';
                        $i++;
                    }
                } else {
                    $tabMessage[$i] = 'Renseigner d\'abord le laboratoire avant d\'ajouter un groupe';
                    $i++;
                }
            }


            if (count($tabMessage) == 0) {
                for ($i = 0; $i < count($groupesMilieu); $i++) {
                    $pgProgGrpParamRefSel = $repoPgProgGrpParamRef->getPgProgGrpParamRefById($groupesMilieu[$i]);
                    $nbProgGrparRefLstParam = $repoPgProgGrparRefLstParam->getNbPgProgGrparRefLstParamByGrparRef($pgProgGrpParamRefSel);
                    $pgProgLotGrparAn = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotAnGrpparref($pgProgLotAn, $pgProgGrpParamRefSel);
                    if (!$pgProgLotGrparAn) {
                        $pgProgLotGrparAn = new PgProgLotGrparAn();
                        $pgProgLotGrparAn->setLotAn($pgProgLotAn);
                        $pgProgLotGrparAn->setGrparRef($pgProgGrpParamRefSel);
                        $pgProgLotGrparAn->setValide('N');
                        if ($nbProgGrparRefLstParam == 0) {
                            $pgProgLotGrparAn->setValide('O');
                        }
                        $pgProgLotGrparAn->setOrigine('A');
                        if ($pgProgGrpParamRefSel->getTypeGrp() == 'ANA' and $laboratoireCorId) {
                            $pgProgLotGrparAn->setPrestaDft($laboratoire);
                        }
                        if ($pgProgGrpParamRefSel->getTypeGrp() != 'ANA' and $preleveurCorId) {
                            $pgProgLotGrparAn->setPrestaDft($preleveur);
                        }
                        $emSqe->persist($pgProgLotGrparAn);
                        $emSqe->flush();
                        $session->getFlashBag()->add('notice-success', 'Le groupe  : ' . $pgProgLotGrparAn->getGrparRef()->getLibelleGrp() . ' a été ajouté à la programmation : ' . $pgProgLotGrparAn->getLotan()->getAnneeProg() . ' version :  ' . $pgProgLotGrparAn->getLotan()->getVersion() . ' du lot : ' . $pgProgLotGrparAn->getLotan()->getLot()->getNomLot() . ' !');
                        $support = $pgProgLotGrparAn->getGrparRef()->getSupport();
                        if ($support) {
                            $pgProgGrparObligSupports = $repoPgProgGrparObligSupport->getPgProgGrparObligSupportByCodeSupport($support->getCodeSupport());
                            foreach ($pgProgGrparObligSupports as $pgProgGrparObligSupport) {
                                $pgProgGrpParamRef = $repoPgProgGrpParamRef->getPgProgGrpParamRefById($pgProgGrparObligSupport->getGrparRefId());
                                if ($pgProgGrpParamRefSel->getId() != $pgProgGrpParamRef->getId()) {
                                    if ($pgProgGrpParamRef->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                                        $pgProgLotGrparAnCompl = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotAnGrpparref($pgProgLotAn, $pgProgGrpParamRef);
                                        if (!$pgProgLotGrparAnCompl) {
                                            $pgProgLotGrparAnCompl = new PgProgLotGrparAn();
                                            $pgProgLotGrparAnCompl->setLotAn($pgProgLotAn);
                                            $pgProgLotGrparAnCompl->setGrparRef($pgProgGrpParamRef);
                                            $pgProgLotGrparAnCompl->setValide('N');
                                            $pgProgLotGrparAnCompl->setOrigine('AC');
                                            if ($pgProgGrpParamRef->getTypeGrp() == 'ANA' and $laboratoireCorId) {
                                                $pgProgLotGrparAnCompl->setPrestaDft($laboratoire);
                                            }
                                            if ($pgProgGrpParamRef->getTypeGrp() != 'ANA' and $preleveurCorId) {
                                                $pgProgLotGrparAnCompl->setPrestaDft($preleveur);
                                            }
                                            $emSqe->persist($pgProgLotGrparAnCompl);
                                            $emSqe->flush();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $tabMessage[$i] = "Sélectionner au moins un groupe.";
        }

        $session->set('messageErreur', $tabMessage);
        return new Response('');
    }

    public function supprimerAction($groupeId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'Programmationgroupe');
        $session->set('fonction', 'supprimer');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');


        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);


        $tabMessage = array();
        $session->set('messageErreur', null);
        $i = 0;

        $pgProgGrpParamRef = $repoPgProgGrpParamRef->getPgProgGrpParamRefById($groupeId);
        $pgProgLotGrparAnSup = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotAnGrpparref($pgProgLotAn, $pgProgGrpParamRef);

        if ($pgProgLotGrparAnSup->getGrparRef()->getSupport()) {
            $trouve = false;
            foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
                if ($pgProgLotGrparAn->getid() != $pgProgLotGrparAnSup->getId()) {
                    if ($pgProgLotGrparAn->getOrigine() == 'R' or $pgProgLotGrparAn->getOrigine() == 'A') {
                        if ($pgProgLotGrparAn->getGrparRef()->getSupport()) {
                            if ($pgProgLotGrparAn->getGrparRef()->getSupport()->getCodesupport() == $pgProgLotGrparAnSup->getGrparRef()->getSupport()->getCodesupport()) {
                                $trouve = true;
                                break;
                            }
                        }
                    }
                }
            }
            if (!$trouve) {
                $support = $pgProgLotGrparAnSup->getGrparRef()->getSupport();
                if ($support) {
                    $pgProgGrparObligSupports = $repoPgProgGrparObligSupport->getPgProgGrparObligSupportByCodeSupport($support->getCodeSupport());
                    foreach ($pgProgGrparObligSupports as $pgProgGrparObligSupport) {
                        $pgProgGrpParamRefCompl = $repoPgProgGrpParamRef->getPgProgGrpParamRefById($pgProgGrparObligSupport->getGrparRefId());
                        if ($pgProgGrpParamRef->getId() != $pgProgGrpParamRefCompl->getId()) {
                            if ($pgProgGrpParamRefCompl->getCodeMilieu()->getCodeMilieu() == $pgProgTypeMilieu->getCodeMilieu()) {
                                $pgProgLotGrparAnCompl = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotAnGrpparref($pgProgLotAn, $pgProgGrpParamRefCompl);
                                if ($pgProgLotGrparAnCompl) {
                                    $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAn($pgProgLotGrparAnCompl);
                                    foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                                        $emSqe->remove($pgProgLotPeriodeProg);
                                    }
                                    $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAnCompl);
                                    foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                                        $emSqe->remove($pgProgLotParamAn);
                                    }
                                    $emSqe->remove($pgProgLotGrparAnCompl);
                                }
                            }
                        }
                    }
                }
            }
        }
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAn($pgProgLotGrparAnSup);
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
            $emSqe->remove($pgProgLotPeriodeProg);
        }
        $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAnSup);
        foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
            $emSqe->remove($pgProgLotParamAn);
        }
        $emSqe->remove($pgProgLotGrparAnSup);
        $emSqe->flush();
        $session->getFlashBag()->add('notice-success', 'Le groupe  : ' . $pgProgLotGrparAnSup->getGrparRef()->getLibelleGrp() . ' a été supprimé de  la programmation : ' . $pgProgLotGrparAnSup->getLotan()->getAnneeProg() . ' version :  ' . $pgProgLotGrparAnSup->getLotan()->getVersion() . ' du lot : ' . $pgProgLotGrparAnSup->getLotan()->getLot()->getNomLot() . ' !');
        return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_groupes', array(
                            'pgProgLotId' => round($pgProgLot->getId()),
                            'maj' => $maj,
                            'action' => $action,
                            'lotan' => $pgProgLotAnId)));
    }

    public function filtrerAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationGroupe');
        $session->set('fonction', 'Filtrer');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgSandreParametres = $emSqe->getRepository('AeagSqeBundle:PgSandreParametres');
        $repoPgProgGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgGrpParamRefs = $repoPgProGrpParamRef->getPgProgGrpParamRefByCodeMilieu($pgProgTypeMilieu);
        $tabSandreParametres = array();
        $tabGroupes = array();
        $tabMessages = array();
        $choixGroupes = array();
        $i = 0;
        $j = 0;

        $session->set('messageErreur', null);
        if ($session->has('choixParametre')) {
            $session->remove('choixParametre');
        }

        if (isset($_GET['reseau'])) {
            $session->set('selectionReseau', $_GET['reseau']);
        }

        if (isset($_GET['preleveur_ie'])) {
            $session->set('selectionPreleveur_ie', $_GET['preleveur_ie']);
        }

        if (isset($_GET['preleveur'])) {
            $session->set('selectionPreleveur', $_GET['preleveur']);
        }

        if (isset($_GET['laboratoire_ie'])) {
            $session->set('selectionLaboratoire_ie', $_GET['laboratoire']);
        }

        if (isset($_GET['laboratoire'])) {
            $session->set('selectionLaboratoire_ie', $_GET['laboratoire']);
        }

        if (!empty($_GET['check'])) {
            $choixGroupes = $_GET['check'];
            $session->set('selectionGroupes', $choixGroupes);
        }


        if (!empty($_GET['parametre'])) {
            $choixParametreComplet = explode(" ", $_GET['parametre']);
            $choixParametre = $choixParametreComplet[0];
        } else {
            $choixParametre = null;
        }
        $session->set('choixParametre', $choixParametre);

        $tabGroupesAeag = unserialize($session->get('groupesAeag'));
        $tabGroupesMilieu = unserialize($session->get('groupesMilieu'));

        $pgSandreParametre = $repoPgSandreParametres->getPgSandreParametresByCodeParametre($choixParametre);
        if (!$pgSandreParametre) {
            $tabMessages[$i] = 'parametre : ' . $choixParametre . ' inconnu';
            $pgProgGrparRefLstParams = null;
        } else {
            $pgProgGrparRefLstParams = $repoPgProgGrparRefLstParam->getPgProgGrparRefLstParamByCodeParametre($pgSandreParametre->getCodeParametre());
        }


        // recuperation des groupes lies au parametres
        if ($pgProgGrparRefLstParams) {
            foreach ($pgProgGrparRefLstParams as $pgProgGrparRefLstParam) {
                $pgProgGrpParamRef = $pgProgGrparRefLstParam->getGrparRef();
                $trouve = false;
                for ($j = 0; $j < count($tabGroupes); $j++) {
                    if ($tabGroupes[$j]['groupe']->getId() == $pgProgGrpParamRef->getId()) {
                        $trouve = true;
                        $j = count($tabGroupes) + 1;
                    }
                }
                if ($trouve == false) {
                    for ($j = 0; $j < count($tabGroupesAeag); $j++) {
                        if ($tabGroupesAeag[$j] == $pgProgGrpParamRef->getId()) {
                            $trouve = true;
                            $j = count($tabGroupesAeag) + 1;
                        }
                    }
                }
                if ($trouve == false) {
                    for ($j = 0; $j < count($tabGroupesMilieu); $j++) {
                        if ($tabGroupesMilieu[$j] == $pgProgGrpParamRef->getId()) {
                            $trouve = true;
                            $j = count($tabGroupesMilieu) + 1;
                        }
                    }
                }
                if ($trouve == true) {
                    $tabGroupes[$i]['groupe'] = $pgProgGrpParamRef;
                    $tabGroupes[$i]['statut'] = 'N';
                    $tabGroupes[$i]['cocher'] = 'N';
                    $i++;
                }
            }
        } else {
            // recuperation des groupes lies au type de milieu
            if (count($tabGroupesMilieu) > 0) {
                for ($i = 0; $i < count($tabGroupesMilieu); $i++) {
                    $pgProgGrpParamRef = $repoPgProGrpParamRef->getPgProgGrpParamRefById($tabGroupesMilieu[$i]);
                    $tabGroupes[$i]['groupe'] = $pgProgGrpParamRef;
                    $tabGroupes[$i]['statut'] = 'N';
                    $tabGroupes[$i]['cocher'] = 'N';
                }
            }
        }


        if ($session->has('selectionGroupes')) {
            $choixGroupes = $session->get('selectionGroupes');
            foreach ($choixGroupes as $choixGroupe) {
                for ($j = 0; $j < count($tabGroupes); $j++) {
                    if ($tabGroupes[$j]['groupe']->getId() == $choixGroupe) {
                        $tabGroupes[$j]['cocher'] = 'O';
                    }
                }
            }
        }
        asort($tabGroupes);

        for ($i = 0; $i < count($tabGroupes); $i++) {
            $pgProgGrparRefLstParams = $repoPgProgGrparRefLstParam->getPgProgGrparRefLstParamByGrparRef($tabGroupes[$i]['groupe']);
            $j = 0;
            foreach ($pgProgGrparRefLstParams as $pgProgGrparRefLstParam) {
                $tabParametres[$j]['pgProgGrparRefLstParam'] = $pgProgGrparRefLstParam;
                $pgSandreParametre = $pgProgGrparRefLstParam->getCodeParametre();
                if ($pgProgGrparRefLstParam->getCodeFraction()) {
                    $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgGrparRefLstParam->getCodeFraction()->getCodeFraction());
                } else {
                    $pgSandreFraction = null;
                } $pgSandreUnite = $pgProgGrparRefLstParam->getUniteDefaut();
                $tabParametres[$j]['pgSandreParametre'] = $pgSandreParametre;
                $tabParametres[$j]['pgSandreFraction'] = $pgSandreFraction;
                $tabParametres[$j]['pgSandreUnite'] = $pgSandreUnite;
                $j++;
            }
            $tabGroupes[$i]['parametres'] = $tabParametres;
        }

        // recupérations des parametres liés au type de milieu
        $tabSandreParametres = array();
        $j = 0;
        foreach ($pgProgGrpParamRefs as $pgProgGrpParamRef) {
            $pgProgGrparRefLstParams = $repoPgProgGrparRefLstParam->getPgProgGrparRefLstParamByGrparRef($pgProgGrpParamRef);
            foreach ($pgProgGrparRefLstParams as $pgProgGrparRefLstParam) {
                $trouve = false;
                for ($i = 0; $i < count($tabSandreParametres); $i++) {
                    if ($tabSandreParametres[$i]->getCodeParametre() == $pgProgGrparRefLstParam->getCodeParametre()->getCodeParametre()) {
                        $trouve = true;
                        $i = count($tabSandreParametres) + 1;
                    }
                }
                if ($trouve == false) {
                    $tabSandreParametres[$j] = $pgProgGrparRefLstParam->getCodeParametre();
                    $j++;
                }
            }
        }
        asort($tabSandreParametres);

        return $this->render('AeagSqeBundle:Programmation:Groupe/ajouter.html.twig', array(
                    'action' => $action,
                    'maj' => $maj,
                    'lotan' => $pgProgLotAn,
                    'groupes' => $tabGroupes,
                    'parametres' => $tabSandreParametres));
    }

    public function validerAction($groupeId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationGroupe');
        $session->set('fonction', 'valider');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');
        $repoPgSandreParametre = $emSqe->getRepository('AeagSqeBundle:PgSandreParametres');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');

        //recupération des parametres
        $request = $this->container->get('request');
        $pgProgLotAnId = $request->get('lotan');
        $action = $request->get('action');
        $maj = $request->get('maj');

        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgGrpParamRef = $repoPgProgGrpParamRef->getPgProgGrpParamRefById($groupeId);
        $pgProgGrparRefLstParams = $repoPgProgGrparRefLstParam->getPgProgGrparRefLstParamByGrparRef($pgProgGrpParamRef);
        $nbProgGrparRefLstParam = $repoPgProgGrparRefLstParam->getNbPgProgGrparRefLstParamByGrparRef($pgProgGrpParamRef);
        $pgProgLotGrparAn = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotAnGrpparref($pgProgLotAn, $pgProgGrpParamRef);
        $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
        $i = 0;
        $ok = 'ok';
        $tabMessage = array();
        $session->set('messageErreur', null);

        $selParametres = array();
        if (!empty($_POST['check'])) {
            $selParametres = $_POST['check'];
            $ok = 'ok';
        } else {
            //$tabMessage[$i] = 'Séléctionner au moins un parametre  svp.';
            //$i++;
            $ok = 'ok';
            //return new Response(json_encode($tabMessage));
        }

        foreach ($selParametres as $codeParametre) {
            $pgSandreParametre = $repoPgSandreParametre->getPgSandreParametresByCodeParametre($codeParametre);
            $pgProgGrparRefLstParam = $repoPgProgGrparRefLstParam->getPgProgGrparRefLstParamByGrparRefCodeParametre($pgProgGrpParamRef, $codeParametre);
            if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                if (!$_POST['prestataire_' . $codeParametre]) {
                    $tabMessage[$i] = "Le prestataire doit être renseigné pour le parametre : " . $codeParametre;
                    $i++;
                    $ok = 'ko';
                }
            } else {
                if (!$_POST['idPrestataire_' . $codeParametre]) {
                    $tabMessage[$i] = "Le prestataire doit être renseigné pour le parametre : " . $codeParametre;
                    $i++;
                    $ok = 'ko';
                }
            }
        }


        if ($ok == 'ko') {
            return new Response(json_encode($tabMessage));
        }

        foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
            $emSqe->remove($pgProgLotParamAn);
        }
        $pgProgLotGrparAn->setvalide('N');
        if ($nbProgGrparRefLstParam == 0) {
            $pgProgLotGrparAn->setValide('O');
        }
        $emSqe->persist($pgProgLotGrparAn);
        $emSqe->flush();

        foreach ($selParametres as $codeParametre) {
            $pgSandreParametre = $repoPgSandreParametre->getPgSandreParametresByCodeParametre($codeParametre);
            $pgProgGrparRefLstParam = $repoPgProgGrparRefLstParam->getPgProgGrparRefLstParamByGrparRefCodeParametre($pgProgGrpParamRef, $codeParametre);
            if ($pgProgLotGrparAn->getGrparRef()->getTypeGrp() == 'ANA') {
                $adrCorId = $_POST['prestataire_' . $codeParametre];
                $pgRefCorresPresta = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($adrCorId);
            } else {
                $adrCorId = $_POST['idPrestataire_' . $codeParametre];
                $pgRefCorresPresta = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($adrCorId);
            }
            $pgProgLotParamAn = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparanCodeParametre($pgProgLotGrparAn, $pgSandreParametre);
            if (!$pgProgLotParamAn) {
                $pgProgLotParamAn = new PgProgLotParamAn();
                $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('CRE');
            } else {
                $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('UPD');
            }
            $pgProgLotParamAn->setGrparan($pgProgLotGrparAn);
            $pgProgLotParamAn->setCodeParametre($pgSandreParametre);
            if ($pgProgGrparRefLstParam->getCodeFraction()) {
                $pgProgLotParamAn->setCodeFraction($pgProgGrparRefLstParam->getCodeFraction()->getCodeFraction());
            }
            if ($pgProgGrparRefLstParam->getUniteDefaut()) {
                $pgProgLotParamAn->setCodeUnite($pgProgGrparRefLstParam->getUniteDefaut()->getCodeUnite());
            }
            $pgProgLotParamAn->setPrestataire($pgRefCorresPresta);
            $pgProgLotParamAn->setCodeStatut($pgProgStatut);
            $emSqe->persist($pgProgLotParamAn);
            $pgProgLotGrparAn->setvalide('O');
            $emSqe->persist($pgProgLotGrparAn);
        }


        $emSqe->flush();
        return new Response(json_encode($tabMessage));
    }

    public function ResultatAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationGroupe');
        $session->set('fonction', 'Resultat');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');
        $repoPgSandreParametre = $emSqe->getRepository('AeagSqeBundle:PgSandreParametres');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

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


        $annee = $session->get('critAnnee');
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($pgProgLotAnId);
        $pgProgLot = $pgProgLotAn->getLot();
        $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotAn($pgProgLotAn);
//        $pgProgGrpParamRef = $repoPgProgGrpParamRef->getPgProgGrpParamRefById($groupeId);
//        $pgProgGrparRefLstParams = $repoPgProgGrparRefLstParam->getPgProgGrparRefLstParamByGrparRef($pgProgGrpParamRef);
//        $pgProgLotGrparAn = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotAnGrpparref($pgProgLotAn, $pgProgGrpParamRef);
//        $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);



        $tabMessage = array();
        $tabgroupes = array();
        $i = 0;


        $session->set('messageErreur', null);

        $preleveurCorId = null;
        if ($session->get('browser') == 'Internet Explorer') {
            if (isset($_POST['preleveur_ie'])) {
                if ($_POST['preleveur_ie'] != '') {
                    $preleveurCorId = $_POST['preleveur_ie'];
                    $preleveur = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($preleveurCorId);
                } else {
                    $tabMessage[$i] = 'Renseigner d\'abord le préleveur avant d\'ajouter un groupe';
                    $i++;
                }
            } else {
                $tabMessage[$i] = 'Renseigner d\'abord le préleveur avant d\'ajouter un groupe';
                $i++;
            }
            $laboratoireCorId = null;
            if (isset($_POST['laboratoire_ie'])) {
                if ($_POST['laboratoire_ie'] != '') {
                    $laboratoireCorId = $_POST['laboratoire_ie'];
                    $laboratoire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($laboratoireCorId);
                } else {
                    $tabMessage[$i] = 'Renseigner d\'abord le laboratoire avant d\'ajouter un groupe';
                    $i++;
                }
            } else {
                $tabMessage[$i] = 'Renseigner d\'abord le laboratoire avant d\'ajouter un groupe';
                $i++;
            }
            $session->set('selectionPreleveur_ie', $preleveurCorId);
            $session->set('selectionLaboratoire_ie', $laboratoireCorId);
        } else {
            if (isset($_POST['preleveur'])) {
                if ($_POST['preleveur'] != '') {
                    $preleveurAncnumComplet = explode(" ", $_POST['preleveur']);
                    $preleveurAncnum = $preleveurAncnumComplet[0];
                    $preleveur = $repoPgRefCorresPresta->getPgRefCorresPrestaByAncnum($preleveurAncnum);
                    $session->set('selectionPreleveur', $preleveur->getAncnumNomCorres());
                } else {
                    $tabMessage[$i] = 'Renseigner d\'abord le préleveur avant d\'ajouter un groupe';
                    $i++;
                }
            } else {
                $tabMessage[$i] = 'Renseigner d\'abord le préleveur avant d\'ajouter un groupe';
                $i++;
            }
            $laboratoireCorId = null;
            if (isset($_POST['laboratoire'])) {
                if ($_POST['laboratoire'] != '') {
                    $laboratoireAncnumComplet = explode(" ", $_POST['laboratoire']);
                    $laboratoireAncnum = $laboratoireAncnumComplet[0];
                    $laboratoire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAncnum($laboratoireAncnum);
                    $session->set('selectionLaboratoire', $laboratoire->getAncnumNomCorres());
                } else {
                    $tabMessage[$i] = 'Renseigner d\'abord le laboratoire avant d\'ajouter un groupe';
                    $i++;
                }
            } else {
                $tabMessage[$i] = 'Renseigner d\'abord le laboratoire avant d\'ajouter un groupe';
                $i++;
            }
        }



        if (count($tabMessage) > 0) {
            $session->set('messageErreur', $tabMessage);
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_groupes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        }

        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $pgProgGrpParamRef = $pgProgLotGrparAn->getGrparRef();
            if ($pgProgGrpParamRef->getValide() != 'N') {
                $prestataire = null;
                if ($pgProgGrpParamRef->getTypeGrp() == 'ANA') {
                    if (isset($_POST['idPrestataire_' . $pgProgGrpParamRef->getId()])) {
                        if ($session->get('browser') == 'Internet Explorer') {
                            $prestataireCorId = $_POST['idPrestataire_' . $pgProgGrpParamRef->getId()];
                        } else {
                            $prestataireAncnumComplet = explode(" ", $_POST['prestataire_' . $pgProgGrpParamRef->getId()]);
                            $prestataireAncnum = $prestataireAncnumComplet[0];
                            $prestataire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAncnum($prestataireAncnum);
                            $prestataireCorId = $prestataire->getAdrCorId();
                        }
                        $prestataire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($prestataireCorId);
                        $pgProgLotGrparAn->setPrestaDft($prestataire);
                    } else {
                        $pgProgLotGrparAn->setPrestaDft($laboratoire);
                    }
                }
                if ($pgProgGrpParamRef->getTypeGrp() != 'ANA') {
                    $pgProgLotGrparAn->setPrestaDft($preleveur);
                    $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                    foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                        $pgProgLotParamAn->setPrestataire($preleveur);
                        $emSqe->persist($pgProgLotParamAn);
                    }
                }
                $emSqe->persist($pgProgLotGrparAn);
            } else {
                $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
                foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                    $emSqe->remove($pgProgLotParamAn);
                }
                $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByGrparAn($pgProgLotGrparAn);
                foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                    $pgProgLotPeriodeProgCompls = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPprogCompl($pgProgLotPeriodeProg);
                    foreach ($pgProgLotPeriodeProgCompls as $pgProgLotPeriodeProgCompl) {
                        $pgProgLotPeriodeProgCompl->setPprogCompl(null);
                        $emSqe->persist($pgProgLotPeriodeProgCompl);
                    }
                    $emSqe->remove($pgProgLotPeriodeProg);
                }
                $emSqe->remove($pgProgLotGrparAn);
            }
        }
        $emSqe->flush();



        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotAn($pgProgLotAn);
        $nbPeriodes = 0;
        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $nbPgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->countPgProgLotPeriodeProgByGrparAn($pgProgLotGrparAn);
            $nbPeriodes = $nbPeriodes + $nbPgProgLotPeriodeProgs;
        }
        if ($nbPeriodes == 0) {
            $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P10');
            $pgProgLotAn->setPhase($pgProgPhase);
            $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('UPD');
            $pgProgLotAn->setCodeStatut($pgProgStatut);
            $now = date('Y-m-d');
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

        if ($suivant == 'station') {
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_stations', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        } elseif ($suivant == 'groupe') {
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_groupes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        } elseif ($suivant == 'periode') {
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_periodes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        } elseif ($suivant == 'bilan') {
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_bilan', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        } else {
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_periodes', array('action' => $action, 'maj' => $maj, 'lotan' => $pgProgLotAnId)));
        }
    }

    public static function wd_remove_accents($str, $charset = 'utf-8') {


        $str = utf8_encode($str);


        $str = nl2br(strtr(
                        $str, array(
            '\'' => '\&\#039',
                ))
        );
    }

}
