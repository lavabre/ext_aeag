<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Aeag\SqeBundle\Entity\Form\Programmation\Criteres;
use Aeag\SqeBundle\Form\Programmation\CriteresType;
use Aeag\SqeBundle\Entity\PgProgWebUsers;
use Aeag\SqeBundle\Entity\PgProgMarcheUser;
use Aeag\SqeBundle\Entity\PgProgMarche;
use Aeag\SqeBundle\Entity\PgProgLot;
use Aeag\SqeBundle\Entity\PgProgSuiviPhases;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\AeagBundle\Controller\AeagController;

class ProgrammationLotController extends Controller {

    public function indexAction($action = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }

        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'programmationLot');
        $session->set('fonction', 'index');

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
        $repoParametre = $emSqe->getRepository('AeagSqeBundle:Parametre');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');
        $repoPgProgMarcheUser = $emSqe->getRepository('AeagSqeBundle:PgProgMarcheUser');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $repoPgProgZoneGeoRef = $emSqe->getRepository('AeagSqeBundle:PgProgZoneGeoRef');
        $repoPgProgTypeMilieu = $emSqe->getRepository('AeagSqeBundle:PgProgTypeMilieu');
        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $annee = $repoParametre->getParametreByCode('ANNEE');


        $tabWebusers = array();
        $tabMarches = array();
        $tabTitulaires = array();
        $tabZoneGeoRefs = array();
        $tabCatMilieux = array();
        $tabTypeMilieux = array();
        $tabLots = array();
        $tabPhases = array();

        $nbWebusers = 0;
        $nbMarches = 0;
        $nbTitulaires = 0;
        $nbZoneGeoRefs = 0;
        $nbCatMilieux = 0;
        $nbTypeMilieux = 0;
        $nbLots = 0;
        $nbPhases = 0;


        $webusers = $repoPgProgWebusers->getPgProgWebusers();
        foreach ($webusers as $webuser) {
            $tabWebusers[$nbWebusers] = $webuser;
            $nbWebusers++;
        }

        $marches = $repoPgProgMarche->getPgProgMarches();
        foreach ($marches as $marche) {
            $tabMarches[$nbMarches] = $marche;
            $nbMarches++;
        }

        $titulaires = $repoPgRefCorresPresta->getPgRefCorresPrestas();
        foreach ($titulaires as $titulaire) {
            $tabTitulaires[$nbTitulaires] = $titulaire;
            $nbTitulaires++;
        }

        $zoneGeoRefs = $repoPgProgZoneGeoRef->getPgProgZoneGeoRefs();
        foreach ($zoneGeoRefs as $zoneGeoRef) {
            $tabZoneGeoRefs[$nbZoneGeoRefs] = $zoneGeoRef;
            $nbZoneGeoRefs++;
        }

        $tabMilieux = $repoPgProgTypeMilieu->getPgProgCatMilieux();

        $typeMilieux = $repoPgProgTypeMilieu->getPgProgTypeMilieux();
        foreach ($typeMilieux as $typeMilieu) {
            $tabTypeMilieux[$nbTypeMilieux] = $typeMilieu;
            $nbTypeMilieux++;
        }

        $lots = $repoPgProgLot->getPgProgLots();
        foreach ($lots as $lot) {
            $tabLots[$nbLots] = $lot;
            $nbLots++;
        }

        $phases = $repoPgProgPhases->getPgProgPhases();
        foreach ($phases as $phase) {
            $taPhases[$nbPhases] = $phase;
            $nbPhases++;
        }




        $criteres = new Criteres();

        if (!($session->has('critAnnee'))) {
            $session->set('critAnnee', $annee->getLibelle());
        }
        if ($action == 'P') {
            if (($session->has('critWebuser'))) {
                $session->remove('critWebuser');
            }
            if (($session->has('critMarche'))) {
                $session->remove('critMarche');
            }
            if (($session->has('critTitulaire'))) {
                $session->remove('critTitulaire');
            }
            if (($session->has('critZoneGeoRef'))) {
                $session->remove('critZoneGeoRef');
            }
            if (($session->has('critCatMilieu'))) {
                $session->remove('critCatMilieu');
            }
            if (($session->has('critTypeMilieu'))) {
                $session->remove('critTypeMilieu');
            }
            if (($session->has('critLot'))) {
                $session->remove('critLot');
            }
            if (($session->has('critPhase'))) {
                $session->remove('critPhase');
            }
            if ($session->has('choixGroupes')) {
                $session->remove('choixGroupes');
            }
            if ($session->has('ajouterGroupes')) {
                $session->remove('ajouterGroupes');
            }
            if ($session->has('selectionReseau')) {
                $session->remove('selectionReseau');
            }
            if ($session->has('selectionPreleveur')) {
                $session->remove('selectionPreleveur');
            }
            if ($session->has('selectionLaboratoire')) {
                $session->remove('selectionLaboratoire');
            }
            if ($session->has('selectionGroupes')) {
                $session->remove('selectionGroupes');
            }
        }

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE')) {
            var_dump($user->getUsername(), $user->getPassword());
            $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByLoginPassword($user->getUsername(), $user->getPassword());
            $critWebuser = $pgProgWebuser->getId();
            $session->set('critWebuser', $critWebuser);
        } else {
            $critWebuser = null;
        }


        // les marche de l'utilisateur sélectionnée
        if ($critWebuser) {
            $tabMarcheBis = array();
            $i = 0;
            foreach ($tabMarches as $marche) {
                $tabMarcheBis[$i] = $marche;
                $i++;
            }
            $tabMarches = array();
            $i = 0;
            if (count($tabMarcheBis) > 0) {
                foreach ($tabMarcheBis as $marche) {
                    $pgprogmarcheUsers = $repoPgProgMarcheUser->getPgProgMarcheUserByMarche($marche);
                    foreach ($pgprogmarcheUsers as $pgprogmarcheUser) {
                        if ($pgprogmarcheUser->getWebuser()->getId() == $critWebuser) {
                            $tabMarches[$i] = $marche;
                            $i++;
                        }
                    }
                }
            }
        }

        //return new Response ('user : ' . $critWebuser . ' marche : ' . count($tabMarches));
        // les lots séléctionées suivant les marches
        $tabLotBis = array();
        $i = 0;
        foreach ($tabLots as $lot) {
            $tabLotBis[$i] = $lot;
            $i++;
        }
        $tabLots = array();
        $i = 0;
        foreach ($tabLotBis as $lot) {
            foreach ($tabMarches as $marche) {
                if ($lot->getMarche()->getid() == $marche->getid()) {
                    $tabLots[$i] = $lot;
                    $i++;
                }
            }
        }

        $criteres->setAnnee($session->get('critAnnee'));

        $form = $this->createForm(new CriteresType(), $criteres);

        return $this->render('AeagSqeBundle:Programmation:Lot/index.html.twig', array(
                    'form' => $form->createView(),
                    'action' => $action,
                    'annee' => $annee->getlibelle(),
                    'webusers' => $tabWebusers,
                    'marches' => $tabMarches,
                    'titulaires' => $tabTitulaires,
                    'zoneGeoRefs' => $tabZoneGeoRefs,
                    'catMilieux' => $tabCatMilieux,
                    'typeMilieux' => $tabTypeMilieux,
                    'lots' => $tabLots,
                    'phases' => $tabPhases
        ));
    }

    public function filtresAction($action = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'programmationLot');
        $session->set('fonction', 'filtres');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');


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

        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');
        $repoPgProgMarcheUser = $emSqe->getRepository('AeagSqeBundle:PgProgMarcheUser');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $repoPgProgZoneGeoRef = $emSqe->getRepository('AeagSqeBundle:PgProgZoneGeoRef');
        $repoPgProgTypeMilieu = $emSqe->getRepository('AeagSqeBundle:PgProgTypeMilieu');
        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');

        $tabWebusers = array();
        $tabMarches = array();
        $tabTitulaires = array();
        $tabZoneGeoRefs = array();
        $tabCatMilieux = array();
        $tabTypeMilieux = array();
        $tabLots = array();
        $tabPhases = array();

        $nbWebusers = 0;
        $nbMarches = 0;
        $nbTitulaires = 0;
        $nbZoneGeoRefs = 0;
        $nbCatMilieux = 0;
        $nbTypeMilieux = 0;
        $nbLots = 0;
        $nbPhases = 0;


        $webusers = $repoPgProgWebusers->getPgProgWebusers();
        foreach ($webusers as $webuser) {
            $tabWebusers[$nbWebusers] = $webuser;
            $nbWebusers++;
        }

        $marches = $repoPgProgMarche->getPgProgMarches();
        foreach ($marches as $marche) {
            $tabMarches[$nbMarches] = $marche;
            $nbMarches++;
        }

        $titulaires = $repoPgRefCorresPresta->getPgRefCorresPrestas();
        foreach ($titulaires as $titulaire) {
            $tabTitulaires[$nbTitulaires] = $titulaire;
            $nbTitulaires++;
        }

        $zoneGeoRefs = $repoPgProgZoneGeoRef->getPgProgZoneGeoRefs();
        foreach ($zoneGeoRefs as $zoneGeoRef) {
            $tabZoneGeoRefs[$nbZoneGeoRefs] = $zoneGeoRef;
            $nbZoneGeoRefs++;
        }

        $tabCatMilieux = $repoPgProgTypeMilieu->getPgProgCatMilieux();

        $typeMilieux = $repoPgProgTypeMilieu->getPgProgTypeMilieux();
        foreach ($typeMilieux as $typeMilieu) {
            $tabTypeMilieux[$nbTypeMilieux] = $typeMilieu;
            $nbTypeMilieux++;
        }

        $lots = $repoPgProgLot->getPgProgLots();
        foreach ($lots as $lot) {
            $tabLots[$nbLots] = $lot;
            $nbLots++;
        }

        $phases = $repoPgProgPhases->getPgProgPhases();
        foreach ($phases as $phase) {
            $tabPhases[$nbPhases] = $phase;
            $nbPhases++;
        }


        $request = $this->container->get('request');
        $action = $request->get('action');
        $critAnnee = $request->get('annee');
        if ($action == 'P') {
            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE')) {
                $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByLoginPassword($user->getUsername(), $user->getPassword());
                $critWebuser = $pgProgWebuser->getId();
            } else {
                $critWebuser = $request->get('webuser');
            }
        } else {
            $critWebuser = $request->get('webuser');
        }



        $critMarche = $request->get('marche');
        $critTitulaire = $request->get('titulaire');
        $critZoneGeoRef = $request->get('zoneGeoRef');
        $critCatMilieu = $request->get('catMilieu');
        $critTypeMilieu = $request->get('typeMilieu');
        $critLot = $request->get('lot');
        $critPhase = $request->get('phase');

        if ($critAnnee) {
            $session->set('critAnnee', $critAnnee);
        } else {
            $critAnnee = null;
            $session->remove('critAnnee');
        }
        if ($critWebuser) {
            $session->set('critWebuser', $critWebuser);
        } else {
            if ($session->get('critWebuser')) {
                $critWebuser = $session->get('critWebuser');
            } else {
                $critWebuser = null;
                $session->remove('critWebuser');
            }
        }
        if ($critMarche) {
            $session->set('critMarche', $critMarche);
        } else {
            $critMarche = null;
            $session->remove('critMarche');
        }
        if ($critTitulaire) {
            $session->set('critTitulaire', $critTitulaire);
        } else {
            $critTitulaire = null;
            $session->remove('critTitulaire');
        }
        if ($critZoneGeoRef) {
            $session->set('critZoneGeoRef', $critZoneGeoRef);
        } else {
            $critZoneGeoRef = null;
            $session->remove('critZoneGeoRef');
        }

        if ($critCatMilieu) {
            $session->set('critCatMilieu', $critCatMilieu);
        } else {
            $critCatMilieu = null;
            $session->remove('critCatMilieu');
        }
        if ($critTypeMilieu) {
            $session->set('critTypeMilieu', $critTypeMilieu);
        } else {
            $critTypeMilieu = null;
            $session->remove('critTypeMilieu');
        }
        if ($critLot) {
            $session->set('critLot', $critLot);
        } else {
            $critLot = null;
            $session->remove('critLot');
        }
        if ($critPhase) {
            $session->set('critPhase', $critPhase);
        } else {
            $critPhase = null;
            $session->remove('critPhase');
        }



        // les marche de l'année sélectionnée
        if ($critAnnee) {
            $tabMarcheBis = array();
            $i = 0;
            foreach ($tabMarches as $marche) {
                $tabMarcheBis[$i] = $marche;
                $i++;
            }
            $tabMarches = array();
            $i = 0;
            if (count($tabMarcheBis) > 0) {
                foreach ($tabMarcheBis as $marche) {
                    $marchAnnee = $marche->getAnneeDeb();
                    $marcheDuree = $marche->getDuree() - 1;
                    if ($marchAnnee == $critAnnee) {
                        $tabMarches[$i] = $marche;
                        $i++;
                    } else {
                        for ($j = 1; $j <= $marcheDuree; $j++) {
                            if ($marchAnnee + $j == $critAnnee) {
                                $tabMarches[$i] = $marche;
                                $i++;
                                $j = $marcheDuree + 1;
                            }
                        }
                    }
                }
            }
        }


        // les marche de l'utilisateur sélectionnée
        if ($critWebuser) {
            $tabMarcheBis = array();
            $i = 0;
            foreach ($tabMarches as $marche) {
                $tabMarcheBis[$i] = $marche;
                $i++;
            }
            $tabMarches = array();
            $i = 0;
            if (count($tabMarcheBis) > 0) {
                foreach ($tabMarcheBis as $marche) {
                    $pgprogmarcheUsers = $repoPgProgMarcheUser->getPgProgMarcheUserByMarche($marche);
                    foreach ($pgprogmarcheUsers as $pgprogmarcheUser) {
                        if ($pgprogmarcheUser->getWebuser()->getId() == $critWebuser) {
                            $tabMarches[$i] = $marche;
                            $i++;
                        }
                    }
                }
            }
        }


        //  le marché séléctionne

        if ($critMarche) {
            $tabMarcheBis = array();
            $i = 0;
            foreach ($tabMarches as $marche) {
                $tabMarcheBis[$i] = $marche;
                $i++;
            }
            $tabMarches = array();
            $i = 0;
            foreach ($tabMarcheBis as $marche) {
                if ($marche->getId() == $critMarche) {
                    $tabMarches[$i] = $marche;
                    $i++;
                }
            }
        }
        //  les lots séléctionées suivant les marches
        $tabLotBis = array();
        $i = 0;
        foreach ($tabLots as $lot) {
            $tabLotBis[$i] = $lot;
            $i++;
        }
        $tabLots = array();
        $i = 0;
        foreach ($tabLotBis as $lot) {
            foreach ($tabMarches as $marche) {
                if ($lot->getMarche()->getid() == $marche->getid()) {
                    $tabLots[$i] = $lot;
                    $i++;
                }
            }
        }
        $marches = $repoPgProgMarche->getPgProgMarches();
        if (count($tabMarches) == 1) {
            $session->set('critMarche', $tabMarches[0]->getId());
        }
        $tabMarches = array();
        foreach ($marches as $marche) {
            $tabMarches[$nbMarches] = $marche;
            $nbMarches++;
        }


        // les lots séléctionnées suivant la phase
        if ($critPhase) {
            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }
            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByLot($lot);
                if (count($pgProgLotAns > 0)) {
                    $trouve = false;
                    foreach ($pgProgLotAns as $pgProgLotAn) {
                        if ($pgProgLotAn->getPhase()->getId() == intval($critPhase)) {
                            $tabLots[$i] = $lot;
                            $i++;
                        }
                    }
                } else {
                    $tabLots[$i] = $lot;
                    $i++;
                }
            }
        }




        //  les lots séléctionées suivant les titulaires
        if ($critTitulaire) {
            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }
            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                if ($lot->getTitulaire()) {
                    if ($lot->getTitulaire()->getAdrCorId() == $critTitulaire) {
                        $tabLots[$i] = $lot;
                        $i++;
                    }
                }
            }
        }
        //    les lots séléctionées suivant les zones géographiques
        if ($critZoneGeoRef) {
            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }
            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                if ($lot->getZgeoRef()->getId() == $critZoneGeoRef) {
                    $tabLots[$i] = $lot;
                    $i++;
                }
            }
        }
        // les lots séléctionées suivant les categories de milieu
        if ($critCatMilieu) {
            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }
            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                $typeMilieu = $lot->getcodeMilieu();
                if ($typeMilieu->getCategorieMilieu() == $critCatMilieu) {
                    $tabLots[$i] = $lot;
                    $i++;
                }
            }

            $tabTypeMilieux = array();
            $nbTypeMilieux = 0;
            $typeMilieux = $repoPgProgTypeMilieu->getPgProgTypeMilieuByCategorieMilieu($critCatMilieu);
            foreach ($typeMilieux as $typeMilieu) {
                $tabTypeMilieux[$nbTypeMilieux] = $typeMilieu;
                $nbTypeMilieux++;
            }
        }

        // les lots séléctionées suivant les types de milieu
        if ($critTypeMilieu) {
            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }
            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                if ($lot->getcodeMilieu()->getCodeMilieu() == $critTypeMilieu) {
                    $tabLots[$i] = $lot;
                    $i++;
                }
            }
        }


        if (count($tabLots) == 1) {
            $session->set('critMarche', $tabLots[0]->getMarche()->getId());
            $session->set('critMarche', $tabLots[0]->getMarche()->getId());
            if ($tabLots[0]->getTitulaire()) {
                $session->set('critTitulaire', $tabLots[0]->getTitulaire()->getAdrCorId());
            }
            $session->set('critZoneGeoRef', $tabLots[0]->getZGeoRef()->getId());
            $typeMilieu = $tabLots[0]->getCodeMilieu();
            $session->set('critCatMilieu', $typeMilieu->getCategorieMilieu());
            $session->set('critTypeMilieu', $tabLots[0]->getCodeMilieu()->getCodeMilieu());
            $session->set('critLot', $tabLots[0]->getId());
        }

        if (count($tabTypeMilieux) == 1) {
            $session->set('critTypeMilieu', $tabTypeMilieux[0]->getCodeMilieu());
        }


        return $this->render('AeagSqeBundle:Programmation:Lot/filtres.html.twig', array(
                    'annee' => $critAnnee,
                    'action' => $action,
                    'webusers' => $tabWebusers,
                    'marches' => $tabMarches,
                    'titulaires' => $tabTitulaires,
                    'zoneGeoRefs' => $tabZoneGeoRefs,
                    'catMilieux' => $tabCatMilieux,
                    'typeMilieux' => $tabTypeMilieux,
                    'lots' => $tabLots,
                    'phases' => $tabPhases,
        ));
    }

    public function consulterAction($action = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'programmationLot');
        $session->set('fonction', 'resultats');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        if (is_object($user)) {

            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);

            if ($action == 'V' && !$this->get('security.authorization_checker')->isGranted('ROLE_SQE')) {
                return $this->render('AeagSqeBundle:Default:interdit.html.twig');
            }
            if ($action != 'V') {
                return $this->render('AeagSqeBundle:Default:interdit.html.twig');
            }
        }

        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgWebuserZgeoref = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserZgeoref');
        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');
        $repoPgProgMarcheUser = $emSqe->getRepository('AeagSqeBundle:PgProgMarcheUser');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $repoPgProgZoneGeoRef = $emSqe->getRepository('AeagSqeBundle:PgProgZoneGeoRef');
        $repoPgProgTypeMilieu = $emSqe->getRepository('AeagSqeBundle:PgProgTypeMilieu');
        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');

        $tabWebusers = array();
        $tabMarches = array();
        $tabTitulaires = array();
        $tabZoneGeoRefs = array();
        $tabTypeMilieux = array();
        $tabLots = array();
        $tabPhases = array();

        $nbWebusers = 0;
        $nbMarches = 0;
        $nbTitulaires = 0;
        $nbZoneGeoRefs = 0;
        $nbTypeMilieux = 0;
        $nbLots = 0;
        $nbPhases = 0;

        $pgProgWebuserConnecte = $repoPgProgWebusers->getPgProgWebusersByExtid($user->getId());
        $pgProgWebuserZgeorefConnectes = $repoPgProgWebuserZgeoref->getPgProgWebuserZgeorefByWebuser($pgProgWebuserConnecte);

        $webusers = $repoPgProgWebusers->getPgProgWebusers();
        foreach ($webusers as $webuser) {
            $tabWebusers[$nbWebusers] = $webuser;
            $nbWebusers++;
        }

        $marches = $repoPgProgMarche->getPgProgMarches();
        foreach ($marches as $marche) {
            $tabMarches[$nbMarches] = $marche;
            $nbMarches++;
        }

        $titulaires = $repoPgRefCorresPresta->getPgRefCorresPrestas();
        foreach ($titulaires as $titulaire) {
            $tabTitulaires[$nbTitulaires] = $titulaire;
            $nbTitulaires++;
        }

        $zoneGeoRefs = $repoPgProgZoneGeoRef->getPgProgZoneGeoRefs();
        foreach ($zoneGeoRefs as $zoneGeoRef) {
            $tabZoneGeoRefs[$nbZoneGeoRefs] = $zoneGeoRef;
            $nbZoneGeoRefs++;
        }

        $typeMilieux = $repoPgProgTypeMilieu->getPgProgTypeMilieux();
        foreach ($typeMilieux as $typeMilieu) {
            $tabTypeMilieux[$nbTypeMilieux] = $typeMilieu;
            $nbTypeMilieux++;
        }

        $lots = $repoPgProgLot->getPgProgLots();
        foreach ($lots as $lot) {
            $tabLots[$nbLots] = $lot;
            $nbLots++;
        }

        $phases = $repoPgProgPhases->getPgProgPhases();
        foreach ($phases as $phase) {
            $tabPhases[$nbPhases] = $phase;
            $nbPhases++;
        }

        $criteres = new Criteres();

        $form = $this->createForm(new CriteresType(), $criteres);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $critAnnee = $criteres->getAnnee();
            $specialUser = null;
            $critWebuser = null;
            if ($action == 'P') {
                if (!$this->get('security.authorization_checker')->isGranted('ROLE_PROGSQE')) {
                    $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByLoginPassword($user->getUsername(), $user->getPassword());
                    if ($pgProgWebuser) {
                        $critWebuser = $pgProgWebuser->getId();
                    } else {
                        $critWebuser = null;
                    }
                } else {
                    $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByLoginPassword($user->getUsername(), $user->getPassword());
                    if ($pgProgWebuser) {
                        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE')) {
                            $specialUser = $pgProgWebuser;
                        }
                        if (!$criteres->getWebuser()) {
                            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE')) {
                                $critWebuser = $pgProgWebuser->getid();
                            } else {
                                $critWebuser = null;
                            }
                        } else {
                            $critWebuser = $criteres->getWebuser();
                        }
                    }
                }
                if ($critWebuser) {
                    $selWebuser = $repoPgProgWebusers->getPgProgWebusersById($critWebuser);
                } else {
                    $selWebuser = null;
                }
            } else {
                $critWebuser = null;
                $selWebuser = null;
            }
            $session->set('specialUser', $specialUser);
            $critMarche = $criteres->getMarche();
            if ($critMarche) {
                $selMarche = $repoPgProgMarche->getPgProgMarcheById($critMarche);
            } else {
                $selMarche = null;
            }
            $critTitulaire = $criteres->getTitulaire();
            if ($critTitulaire) {
                $selTitulaire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($critTitulaire);
            } else {
                $selTitulaire = null;
            }
            $critZoneGeoRef = $criteres->getZoneGeoRef();
            if ($critZoneGeoRef) {
                $selZoneGeoRef = $repoPgProgZoneGeoRef->getPgProgZoneGeoRefById($critZoneGeoRef);
            } else {
                $selZoneGeoRef = null;
            }
            $critTypeMilieu = $criteres->getTypeMilieu();
            if ($critTypeMilieu) {
                $selTypeMilieu = $repoPgProgTypeMilieu->getPgProgTypeMilieuByCodeMilieu($critTypeMilieu);
            } else {
                $selTypeMilieu = null;
            }
            $critLot = $criteres->getLot();
            if ($critLot) {
                $selLot = $repoPgProgLot->getPgProgLotById($critLot);
            } else {
                $selLot = null;
            }
            $critPhase = $criteres->getPhase();
            if ($critPhase) {
                $selPhase = $repoPgProgPhases->getPgProgPhasesById($critPhase);
            } else {
                $selPhase = null;
            }



            if ($critAnnee) {
                $session->set('critAnnee', $critAnnee);
            } else {
                $critAnnee = null;
                $session->remove('critAnnee');
            }


            if ($critWebuser) {
                $session->set('critWebuser', $critWebuser);
            } else {
                if ($session->get('critWebuser')) {
                    $critWebuser = $session->get('critWebuser');
                    $selWebuser = $repoPgProgWebusers->getPgProgWebusersById($critWebuser);
                } else {
                    $critWebuser = null;
                    $session->remove('critWebuser');
                }
            }
            if ($critMarche) {
                $session->set('critMarche', $critMarche);
            } else {
                $critMarche = null;
                $session->remove('critMarche');
            }
            if ($critTitulaire) {
                $session->set('critTitulaire', $critTitulaire);
            } else {
                $critTitulaire = null;
                $session->remove('critTitulaire');
            }
            if ($critZoneGeoRef) {
                $session->set('critZoneGeoRef', $critZoneGeoRef);
            } else {
                $critZoneGeoRef = null;
                $session->remove('critZoneGeoRef');
            }
            if ($critTypeMilieu) {
                $session->set('critTypeMilieu', $critTypeMilieu);
            } else {
                $critTypeMilieu = null;
                $session->remove('critTypeMilieu');
            }
            if ($critLot) {
                $session->set('critLot', $critLot);
            } else {
                $critLot = null;
                $session->remove('critLot');
            }
            if ($critPhase) {
                $session->set('critPhase', $critPhase);
            } else {
                $critPhase = null;
                $session->remove('critPhase');
            }


            // les marche de l'année sélectionnée
            if ($critAnnee) {
                $tabMarcheBis = array();
                $i = 0;
                foreach ($tabMarches as $marche) {
                    $tabMarcheBis[$i] = $marche;
                    $i++;
                }
                $tabMarches = array();
                $i = 0;
                if (count($tabMarcheBis) > 0) {
                    foreach ($tabMarcheBis as $marche) {
                        $marchAnnee = $marche->getAnneeDeb();
                        $marcheDuree = $marche->getDuree() - 1;
                        if ($marchAnnee == $critAnnee) {
                            $tabMarches[$i] = $marche;
                            $i++;
                        } else {
                            for ($j = 1; $j <= $marcheDuree; $j++) {
                                if ($marchAnnee + $j == $critAnnee) {
                                    $tabMarches[$i] = $marche;
                                    $i++;
                                    $J = $marcheDuree + 1;
                                }
                            }
                        }
                    }
                }
            }


            // les marche de l'utilisateur sélectionnée
            if ($critWebuser) {
                $tabMarcheBis = array();
                $i = 0;
                foreach ($tabMarches as $marche) {
                    $tabMarcheBis[$i] = $marche;
                    $i++;
                }
                $tabMarches = array();
                $i = 0;
                if (count($tabMarcheBis) > 0) {
                    foreach ($tabMarcheBis as $marche) {
                        $pgprogmarcheUsers = $repoPgProgMarcheUser->getPgProgMarcheUserByMarche($marche);
                        foreach ($pgprogmarcheUsers as $pgprogmarcheUser) {
                            if ($pgprogmarcheUser->getWebuser()->getId() == $critWebuser) {
                                $tabMarches[$i] = $marche;
                                $i++;
                            }
                        }
                    }
                }
            }

            // le marché séléctionne
            if ($critMarche) {
                $tabMarcheBis = array();
                $i = 0;
                foreach ($tabMarches as $marche) {
                    $tabMarcheBis[$i] = $marche;
                    $i++;
                }
                $tabMarches = array();
                $i = 0;
                foreach ($tabMarcheBis as $marche) {
                    if ($marche->getId() == $critMarche) {
                        $tabMarches[$i] = $marche;
                        $i++;
                    }
                }
            }


            // les lots séléctionnées suivant les marches
            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }
            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                foreach ($tabMarches as $marche) {
                    if ($lot->getMarche()->getid() == $marche->getId()) {
                        $tabLots[$i] = $lot;
                        $i++;
                    }
                }
            }


            // les lots séléctionnées suivant la phase
            if ($critPhase) {
                $tabLotBis = array();
                $i = 0;
                foreach ($tabLots as $lot) {
                    $tabLotBis[$i] = $lot;
                    $i++;
                }
                $tabLots = array();
                $i = 0;
                foreach ($tabLotBis as $lot) {
                    $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByLot($lot);
                    if (count($pgProgLotAns > 0)) {
                        $trouve = false;
                        foreach ($pgProgLotAns as $pgProgLotAn) {
                            if ($pgProgLotAn->getPhase()->getId() == intval($critPhase)) {
                                $tabLots[$i] = $lot;
                                $i++;
                            }
                        }
                    } else {
                        $tabLots[$i] = $lot;
                        $i++;
                    }
                }
            }


            // les lots séléctionées suivant les titulaires

            if ($critTitulaire) {
                $tabLotBis = array();
                $i = 0;
                foreach ($tabLots as $lot) {
                    $tabLotBis[$i] = $lot;
                    $i++;
                }
                $tabLots = array();
                $i = 0;
                foreach ($tabLotBis as $lot) {
                    if ($lot->getTitulaire()) {
                        if ($lot->getTitulaire()->getAdrCorId() == $critTitulaire) {
                            $tabLots[$i] = $lot;
                            $i++;
                        }
                    }
                }
            }


            // les lots séléctionées suivant les zones géographiques
            if ($critZoneGeoRef) {
                $tabLotBis = array();
                $i = 0;
                foreach ($tabLots as $lot) {
                    $tabLotBis[$i] = $lot;
                    $i++;
                }
                $tabLots = array();
                $i = 0;
                foreach ($tabLotBis as $lot) {
                    if ($lot->getZgeoRef()) {
                        if ($lot->getZgeoRef()->getId() == $critZoneGeoRef) {
                            $tabLots[$i] = $lot;
                            $i++;
                        }
                    }
                }
            }


            // les lots séléctionées suivant les types de milieu
            if ($critTypeMilieu) {
                $tabLotBis = array();
                $i = 0;
                foreach ($tabLots as $lot) {
                    $tabLotBis[$i] = $lot;
                    $i++;
                }
                $tabLots = array();
                $i = 0;
                foreach ($tabLotBis as $lot) {
                    if ($lot->getCodeMilieu()->getCodeMilieu() == $critTypeMilieu) {
                        $tabLots[$i] = $lot;
                        $i++;
                    }
                }
            }


            //  lot séléctioné
            if ($critLot) {
                $tabLotBis = array();
                $i = 0;
                foreach ($tabLots as $lot) {
                    $tabLotBis[$i] = $lot;
                    $i++;
                }
                $tabLots = array();
                $i = 0;
                foreach ($tabLotBis as $lot) {
                    if ($lot->getId() == $critLot) {
                        $tabLots[$i] = $lot;
                        $i++;
                    }
                }
            }


            // les lots séléctionées suivant l'année selectionnée
            if ($critAnnee) {
                $tabLotBis = array();
                $i = 0;
                foreach ($tabLots as $lot) {
                    $tabLotBis[$i] = $lot;
                    $i++;
                }
                $tabLots = array();
                $i = 0;
                foreach ($tabLotBis as $lot) {
                    $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAnneeLot($critAnnee, $lot);
                    foreach ($pgProgLotAns as $pgProgLotAn) {
                        if ($pgProgLotAn) {
                            $trouve = false;
                            for ($j = 0; $j < count($tabLots); $j++) {
                                if ($tabLots[$j]->getId() == $lot->getId()) {
                                    $trouve = true;
                                }
                            }
                            if (!$trouve) {
                                $tabLots[$i] = $lot;
                                $i++;
                            }
                        }
                    }
                }
            }

            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }

            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAnneeLot($critAnnee, $lot);
                foreach ($pgProgLotAns as $pgProgLotAn) {
                    if ($pgProgLotAn) {
                        $tabLots[$i]['lot'] = $lot;
                        $typeMilieu = $lot->getCodeMilieu();
                        $tabLots[$i]['typeMilieu'] = $typeMilieu;
                        $tabLots[$i]['lotAn'] = $pgProgLotAn;
                        $tabLots[$i]['tri'] = $lot->getId() . '000' . $pgProgLotAn->getVersion();
                        $i++;
                    } else {
                        $tabLots[$i]['lot'] = $lot;
                        $typeMilieu = $lot->getCodeMilieu();
                        $tabLots[$i]['typeMilieu'] = $typeMilieu;
                        $tabLots[$i]['lotAn'] = null;
                        $tabLots[$i]['tri'] = $lot->getId() . '0000';
                        $i++;
                    }
                }
            }


            $session->set('niveau1', $this->generateUrl('Aeag_sqe_programmation_lots', array('action' => $action)));
            $session->set('niveau2', '');

            if (count($tabLots) == 0) {
                $session->getFlashBag()->add('notice-warning', 'aucune programmation de prévue pour cette sélection');
                return $this->redirect($this->generateUrl('Aeag_sqe_programmation_lots', array('action' => $action)));
            }

            if ($session->has('messageErreur')) {
                $session->remove('messageErreur');
            }

            if (count($tabLots) == 1) {
                $session->set('critMarche', $tabLots[0]['lot']->getMarche()->getId());
                $session->set('critMarche', $tabLots[0]['lot']->getMarche()->getId());
                if ($tabLots[0]['lot']->getTitulaire()) {
                    $session->set('critTitulaire', $tabLots[0]['lot']->getTitulaire()->getAdrCorId());
                }
                $session->set('critZoneGeoRef', $tabLots[0]['lot']->getZGeoRef()->getId());
                $session->set('critTypeMilieu', $tabLots[0]['lot']->getCodeMilieu()->getCodeMilieu());
                $session->set('critLot', $tabLots[0]['lot']->getId());
                //return $this->forward('AeagSqeBundle:Programmation:criteresResultatUnique');
                return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_lot_resultat_unique', array('action' => $action)));
            } else {
                usort($tabLots, create_function('$a,$b', 'return $a[\'tri\']-$b[\'tri\'];'));
                return $this->render('AeagSqeBundle:Programmation:Lot/resultats.html.twig', array(
                            'specialUser' => $specialUser,
                            'action' => $action,
                            'entities' => $tabLots,
                            'critAnnee' => $critAnnee,
                            'critPhase' => $selPhase,
                            'critWebuser' => $selWebuser,
                            'critMarche' => $selMarche,
                            'critTitulaire' => $selTitulaire,
                            'critZoneGeoRef' => $selZoneGeoRef,
                            'critTypeMilieu' => $selTypeMilieu,
                            'critLot' => $selLot));
            }
        }



        return $this->render('AeagSqeBundle:Programmation:Lot/index.html.twig', array(
                    'form' => $form->createView(),
                    'action' => $action,
                    'marches' => $tabMarches,
                    'titulaires' => $tabTitulaires,
                    'zoneGeoRefs' => $tabZoneGeoRefs,
                    'typeMilieux' => $tabTypeMilieux,
                    'lots' => $tabLots,
                    'phases' => $tabPhases
        ));
    }

    public function resultatsAction($action = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'programmationLot');
        $session->set('fonction', 'resultats');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

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

        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');
        $repoPgProgMarcheUser = $emSqe->getRepository('AeagSqeBundle:PgProgMarcheUser');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $repoPgProgZoneGeoRef = $emSqe->getRepository('AeagSqeBundle:PgProgZoneGeoRef');
        $repoPgProgTypeMilieu = $emSqe->getRepository('AeagSqeBundle:PgProgTypeMilieu');
        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');

        $tabWebusers = array();
        $tabMarches = array();
        $tabTitulaires = array();
        $tabZoneGeoRefs = array();
        $tabTypeMilieux = array();
        $tabLots = array();
        $tabPhases = array();

        $nbWebusers = 0;
        $nbMarches = 0;
        $nbTitulaires = 0;
        $nbZoneGeoRefs = 0;
        $nbTypeMilieux = 0;
        $nbLots = 0;
        $nbPhases = 0;


        $webusers = $repoPgProgWebusers->getPgProgWebusers();
        foreach ($webusers as $webuser) {
            $tabWebusers[$nbWebusers] = $webuser;
            $nbWebusers++;
        }

        $marches = $repoPgProgMarche->getPgProgMarches();
        foreach ($marches as $marche) {
            $tabMarches[$nbMarches] = $marche;
            $nbMarches++;
        }

        $titulaires = $repoPgRefCorresPresta->getPgRefCorresPrestas();
        foreach ($titulaires as $titulaire) {
            $tabTitulaires[$nbTitulaires] = $titulaire;
            $nbTitulaires++;
        }

        $zoneGeoRefs = $repoPgProgZoneGeoRef->getPgProgZoneGeoRefs();
        foreach ($zoneGeoRefs as $zoneGeoRef) {
            $tabZoneGeoRefs[$nbZoneGeoRefs] = $zoneGeoRef;
            $nbZoneGeoRefs++;
        }

        $typeMilieux = $repoPgProgTypeMilieu->getPgProgTypeMilieux();
        foreach ($typeMilieux as $typeMilieu) {
            $tabTypeMilieux[$nbTypeMilieux] = $typeMilieu;
            $nbTypeMilieux++;
        }

        $lots = $repoPgProgLot->getPgProgLots();
        foreach ($lots as $lot) {
            $tabLots[$nbLots] = $lot;
            $nbLots++;
        }

        $phases = $repoPgProgPhases->getPgProgPhases();
        foreach ($phases as $phase) {
            $tabPhases[$nbPhases] = $phase;
            $nbPhases++;
        }

        $criteres = new Criteres();

        $selMilieu = null;

        $form = $this->createForm(new CriteresType(), $criteres);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $critAnnee = $criteres->getAnnee();
            $specialUser = null;
            $critWebuser = null;
            if ($action == 'P') {
                if (!$this->get('security.authorization_checker')->isGranted('ROLE_PROGSQE')) {
                    $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByLoginPassword($user->getUsername(), $user->getPassword());
                    if ($pgProgWebuser) {
                        $critWebuser = $pgProgWebuser->getId();
                    } else {
                        $critWebuser = null;
                    }
                } else {
                    $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByLoginPassword($user->getUsername(), $user->getPassword());
                    if ($pgProgWebuser) {
                        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE')) {
                            $specialUser = $pgProgWebuser;
                        }
                        if (!$criteres->getWebuser()) {
                            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE')) {
                                $critWebuser = $pgProgWebuser->getid();
                            } else {
                                $critWebuser = null;
                            }
                        } else {
                            $critWebuser = $criteres->getWebuser();
                        }
                    }
                }
                if ($critWebuser) {
                    $selWebuser = $repoPgProgWebusers->getPgProgWebusersById($critWebuser);
                } else {
                    $selWebuser = null;
                }
            } else {
                $critWebuser = null;
                $selWebuser = null;
            }
            $session->set('specialUser', $specialUser);
            $critMarche = $criteres->getMarche();
            if ($critMarche) {
                $selMarche = $repoPgProgMarche->getPgProgMarcheById($critMarche);
            } else {
                $selMarche = null;
            }
            $critTitulaire = $criteres->getTitulaire();
            if ($critTitulaire) {
                $selTitulaire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($critTitulaire);
            } else {
                $selTitulaire = null;
            }
            $critZoneGeoRef = $criteres->getZoneGeoRef();
            if ($critZoneGeoRef) {
                $selZoneGeoRef = $repoPgProgZoneGeoRef->getPgProgZoneGeoRefById($critZoneGeoRef);
            } else {
                $selZoneGeoRef = null;
            }

            if ($criteres->getCatMilieu()) {
                $selMilieu = $criteres->getCatMilieu();
            } else {
                $selMilieu = null;
            }
            if ($selMilieu) {
                $session->set('selMilieu', $selMilieu);
            } else {
                $session->remove('selMilieu');
            }

            if ($criteres->getCatMilieu() and ! $criteres->getTypeMilieu()) {
                $critTypeMilieu = $criteres->getCatMilieu();
            } else {
                $critTypeMilieu = $criteres->getTypeMilieu();
            }
            if ($critTypeMilieu) {
                $selTypeMilieu = $repoPgProgTypeMilieu->getPgProgTypeMilieuByCodeMilieu($critTypeMilieu);
            } else {
                $selTypeMilieu = null;
            }
            $critLot = $criteres->getLot();
            if ($critLot) {
                $selLot = $repoPgProgLot->getPgProgLotById($critLot);
            } else {
                $selLot = null;
            }
            $critPhase = $criteres->getPhase();
            if ($critPhase) {
                $selPhase = $repoPgProgPhases->getPgProgPhasesById($critPhase);
            } else {
                $selPhase = null;
            }



            if ($critAnnee) {
                $session->set('critAnnee', $critAnnee);
            } else {
                $critAnnee = null;
                $session->remove('critAnnee');
            }


            if ($critWebuser) {
                $session->set('critWebuser', $critWebuser);
            } else {
                if ($session->get('critWebuser')) {
                    $critWebuser = $session->get('critWebuser');
                    $selWebuser = $repoPgProgWebusers->getPgProgWebusersById($critWebuser);
                } else {
                    $critWebuser = null;
                    $session->remove('critWebuser');
                }
            }
            if ($critMarche) {
                $session->set('critMarche', $critMarche);
            } else {
                $critMarche = null;
                $session->remove('critMarche');
            }
            if ($critTitulaire) {
                $session->set('critTitulaire', $critTitulaire);
            } else {
                $critTitulaire = null;
                $session->remove('critTitulaire');
            }
            if ($critZoneGeoRef) {
                $session->set('critZoneGeoRef', $critZoneGeoRef);
            } else {
                $critZoneGeoRef = null;
                $session->remove('critZoneGeoRef');
            }
            if ($critTypeMilieu) {
                $session->set('critTypeMilieu', $critTypeMilieu);
            } else {
                $critTypeMilieu = null;
                $session->remove('critTypeMilieu');
            }
            if ($critLot) {
                $session->set('critLot', $critLot);
            } else {
                $critLot = null;
                $session->remove('critLot');
            }
            if ($critPhase) {
                $session->set('critPhase', $critPhase);
            } else {
                $critPhase = null;
                $session->remove('critPhase');
            }


            // les marche de l'année sélectionnée
            if ($critAnnee) {
                $tabMarcheBis = array();
                $i = 0;
                foreach ($tabMarches as $marche) {
                    $tabMarcheBis[$i] = $marche;
                    $i++;
                }
                $tabMarches = array();
                $i = 0;
                if (count($tabMarcheBis) > 0) {
                    foreach ($tabMarcheBis as $marche) {
                        $marchAnnee = $marche->getAnneeDeb();
                        $marcheDuree = $marche->getDuree() - 1;
                        if ($marchAnnee == $critAnnee) {
                            $tabMarches[$i] = $marche;
                            $i++;
                        } else {
                            for ($j = 1; $j <= $marcheDuree; $j++) {
                                if ($marchAnnee + $j == $critAnnee) {
                                    $tabMarches[$i] = $marche;
                                    $i++;
                                    $J = $marcheDuree + 1;
                                }
                            }
                        }
                    }
                }
            }


            // les marche de l'utilisateur sélectionnée
            if ($critWebuser) {
                $tabMarcheBis = array();
                $i = 0;
                foreach ($tabMarches as $marche) {
                    $tabMarcheBis[$i] = $marche;
                    $i++;
                }
                $tabMarches = array();
                $i = 0;
                if (count($tabMarcheBis) > 0) {
                    foreach ($tabMarcheBis as $marche) {
                        $pgprogmarcheUsers = $repoPgProgMarcheUser->getPgProgMarcheUserByMarche($marche);
                        foreach ($pgprogmarcheUsers as $pgprogmarcheUser) {
                            if ($pgprogmarcheUser->getWebuser()->getId() == $critWebuser) {
                                $tabMarches[$i] = $marche;
                                $i++;
                            }
                        }
                    }
                }
            }

            // le marché séléctionne
            if ($critMarche) {
                $tabMarcheBis = array();
                $i = 0;
                foreach ($tabMarches as $marche) {
                    $tabMarcheBis[$i] = $marche;
                    $i++;
                }
                $tabMarches = array();
                $i = 0;
                foreach ($tabMarcheBis as $marche) {
                    if ($marche->getId() == $critMarche) {
                        $tabMarches[$i] = $marche;
                        $i++;
                    }
                }
            }


            // les lots séléctionnées suivant les marches
            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }
            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                foreach ($tabMarches as $marche) {
                    if ($lot->getMarche()->getid() == $marche->getId()) {
                        $tabLots[$i] = $lot;
                        $i++;
                    }
                }
            }


            // les lots séléctionnées suivant la phase
            if ($critPhase) {
                $tabLotBis = array();
                $i = 0;
                foreach ($tabLots as $lot) {
                    $tabLotBis[$i] = $lot;
                    $i++;
                }
                $tabLots = array();
                $i = 0;
                foreach ($tabLotBis as $lot) {
                    $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAnneeLot($critAnnee, $lot);
                    if (count($pgProgLotAns > 0)) {
                        $trouve = false;
                        foreach ($pgProgLotAns as $pgProgLotAn) {
                            if ($pgProgLotAn->getPhase()->getId() == intval($critPhase)) {
                                $tabLots[$i] = $lot;
                                $i++;
                            }
                        }
                    } else {
                        $tabLots[$i] = $lot;
                        $i++;
                    }
                }
            }


            // les lots séléctionées suivant les titulaires

            if ($critTitulaire) {
                $tabLotBis = array();
                $i = 0;
                foreach ($tabLots as $lot) {
                    $tabLotBis[$i] = $lot;
                    $i++;
                }
                $tabLots = array();
                $i = 0;
                foreach ($tabLotBis as $lot) {
                    if ($lot->getTitulaire()) {
                        if ($lot->getTitulaire()->getAdrCorId() == $critTitulaire) {
                            $tabLots[$i] = $lot;
                            $i++;
                        }
                    }
                }
            }


            // les lots séléctionées suivant les zones géographiques
            if ($critZoneGeoRef) {
                $tabLotBis = array();
                $i = 0;
                foreach ($tabLots as $lot) {
                    $tabLotBis[$i] = $lot;
                    $i++;
                }
                $tabLots = array();
                $i = 0;
                foreach ($tabLotBis as $lot) {
                    if ($lot->getZgeoRef()) {
                        if ($lot->getZgeoRef()->getId() == $critZoneGeoRef) {
                            $tabLots[$i] = $lot;
                            $i++;
                        }
                    }
                }
            }


            // les lots séléctionées suivant les types de milieu
            if ($critTypeMilieu) {
                $tabLotBis = array();
                $i = 0;
                foreach ($tabLots as $lot) {
                    $tabLotBis[$i] = $lot;
                    $i++;
                }
                $tabLots = array();
                $i = 0;
                foreach ($tabLotBis as $lot) {
                    if (strlen($critTypeMilieu) == 1) {
                        //   echo('lot : ' . $lot->getCodeMilieu()->getCodeMilieu() . ' -> ' . substr($lot->getCodeMilieu()->getCodeMilieu(),0,1) . '</br>');
                        if (substr($lot->getCodeMilieu()->getCodeMilieu(), 0, 1) == $critTypeMilieu) {
                            $tabLots[$i] = $lot;
                            $i++;
                        }
                    } else {
                        if ($lot->getCodeMilieu()->getCodeMilieu() == $critTypeMilieu) {
                            $tabLots[$i] = $lot;
                            $i++;
                        }
                    }
                }
            }

            // return new Response('type milieu : ' . $critTypeMilieu . ' nb lot : ' . count($tabLots));
            //  lot séléctioné
            if ($critLot) {
                $tabLotBis = array();
                $i = 0;
                foreach ($tabLots as $lot) {
                    $tabLotBis[$i] = $lot;
                    $i++;
                }
                $tabLots = array();
                $i = 0;
                foreach ($tabLotBis as $lot) {
                    if ($lot->getId() == $critLot) {
                        $tabLots[$i] = $lot;
                        $i++;
                    }
                }
            }


            // les lots séléctionées suivant l'année selectionnée

            if ($critAnnee) {
                $tabLotBis = array();
                $i = 0;
                foreach ($tabLots as $lot) {
                    $tabLotBis[$i] = $lot;
                    $i++;
                }
                $tabLots = array();
                $i = 0;
                foreach ($tabLotBis as $lot) {
                    $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAnneeLot($critAnnee, $lot);
                    if (count($pgProgLotAns) > 0) {
                        foreach ($pgProgLotAns as $pgProgLotAn) {
                            if ($pgProgLotAn) {
                                $trouve = false;
                                for ($j = 0; $j < count($tabLots); $j++) {
                                    if ($tabLots[$j]->getId() == $lot->getId()) {
                                        $trouve = true;
                                    }
                                }
                                if (!$trouve) {
                                    $tabLots[$i] = $lot;
                                    $i++;
                                }
                            }
                        }
                    } else {
                        $tabLots[$i] = $lot;
                        $i++;
                    }
                }
            }

            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }

            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAnneeLot($critAnnee, $lot);
                foreach ($pgProgLotAns as $pgProgLotAn) {
                    $tabLots[$i]['lot'] = $lot;
                    $typeMilieu = $lot->getCodeMilieu();
                    $tabLots[$i]['typeMilieu'] = $typeMilieu;
                    $tabLots[$i]['lotAn'] = $pgProgLotAn;
                    $tabLots[$i]['tri'] = $lot->getId() . '000' . $pgProgLotAn->getVersion();
                    $i++;
                }
                if (count($pgProgLotAns) == 0) {
                    $tabLots[$i]['lot'] = $lot;
                    $typeMilieu = $lot->getCodeMilieu();
                    $tabLots[$i]['typeMilieu'] = $typeMilieu;
                    $tabLots[$i]['lotAn'] = null;
                    $tabLots[$i]['tri'] = $lot->getId() . '0000';
                    $i++;
                }
            }

            $session->set('niveau1', $this->generateUrl('Aeag_sqe_programmation_lots', array('action' => $action)));
            $session->set('niveau2', '');

            if (count($tabLots) == 0) {
                $session->getFlashBag()->add('notice-warning', 'aucune programmation de prévue pour cette sélection');
                return $this->redirect($this->generateUrl('Aeag_sqe_programmation_lots', array('action' => $action)));
            }

            if ($session->has('messageErreur')) {
                $session->remove('messageErreur');
            }

            if (count($tabLots) == 1) {
                $session->set('critMarche', $tabLots[0]['lot']->getMarche()->getId());
                $session->set('critMarche', $tabLots[0]['lot']->getMarche()->getId());
                if ($tabLots[0]['lot']->getTitulaire()) {
                    $session->set('critTitulaire', $tabLots[0]['lot']->getTitulaire()->getAdrCorId());
                }
                $session->set('critZoneGeoRef', $tabLots[0]['lot']->getZGeoRef()->getId());
                $session->set('critTypeMilieu', $tabLots[0]['lot']->getCodeMilieu()->getCodeMilieu());
                $session->set('critLot', $tabLots[0]['lot']->getId());
                //return $this->forward('AeagSqeBundle:Programmation:criteresResultatUnique');
                return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_lot_resultat_unique', array('action' => $action)));
            } else {
                usort($tabLots, create_function('$a,$b', 'return $a[\'tri\']-$b[\'tri\'];'));
                return $this->render('AeagSqeBundle:Programmation:Lot/resultats.html.twig', array(
                            'specialUser' => $specialUser,
                            'action' => $action,
                            'entities' => $tabLots,
                            'critAnnee' => $critAnnee,
                            'critPhase' => $selPhase,
                            'critWebuser' => $selWebuser,
                            'critMarche' => $selMarche,
                            'critTitulaire' => $selTitulaire,
                            'critZoneGeoRef' => $selZoneGeoRef,
                            'critMilieu' => $selMilieu,
                            'critTypeMilieu' => $selTypeMilieu,
                            'critLot' => $selLot));
            }
        }



        return $this->render('AeagSqeBundle:Programmation:Lot/index.html.twig', array(
                    'form' => $form->createView(),
                    'action' => $action,
                    'marches' => $tabMarches,
                    'titulaires' => $tabTitulaires,
                    'zoneGeoRefs' => $tabZoneGeoRefs,
                    'Milieu' => $selMilieu,
                    'typeMilieux' => $tabTypeMilieux,
                    'lots' => $tabLots,
                    'phases' => $tabPhases
        ));
    }

    public function resultatUniqueAction($action = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'programmationLot');
        $session->set('fonction', 'resultatUnique');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        if (is_object($user)) {
            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);
        }

        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');
        $repoPgProgTypeMilieu = $emSqe->getRepository('AeagSqeBundle:PgProgTypeMilieu');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');

        $annee = $session->get('critAnnee');
        $lot = $repoPgProgLot->getPgProgLotById($session->get('critLot'));
        $typeMilieu = $lot->getCodeMilieu();
        $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAnneeLot($annee, $lot);

        $tabLots = array();
        $i = 0;
        $tabLots[$i]['lot'] = $lot;
        $tabLots[$i]['typeMilieu'] = $typeMilieu;
        foreach ($pgProgLotAns as $pgProgLotAn) {
            if ($pgProgLotAn) {
                $tabLots[$i]['lotAn'] = $pgProgLotAn;
                $critPhase = $pgProgLotAn->getPhase();
            } else {
                $tabLots[$i]['lotAn'] = null;
                $critPhase = null;
            }
        }

        if ($session->has('selMilieu')) {
            $selMilieu = $session->get('selMilieu');
        } else {
            $selMilieu = null;
        }

        $session->set('niveau2', '');

        return $this->render('AeagSqeBundle:Programmation:Lot/resultatUnique.html.twig', array(
                    'specialUser' => $session->get('specialUser'),
                    'action' => $action,
                    'entities' => $tabLots,
                    'critAnnee' => $session->get('critAnnee'),
                    'critPhase' => $critPhase,
                    'critWebuser' => null,
                    'critMarche' => $lot->getMarche(),
                    'critTitulaire' => $lot->getTitulaire(),
                    'critZoneGeoRef' => $lot->getZgeoRef(),
                    'critTypeMilieu' => $typeMilieu,
                    'critMilieu' => $selMilieu,
                    'critLot' => $lot));
    }

    public function retourAction($action = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'programmationLot');
        $session->set('fonction', 'retour');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        if (is_object($user)) {
            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);
        }

        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');
        $repoPgProgMarcheUser = $emSqe->getRepository('AeagSqeBundle:PgProgMarcheUser');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $repoPgProgZoneGeoRef = $emSqe->getRepository('AeagSqeBundle:PgProgZoneGeoRef');
        $repoPgProgTypeMilieu = $emSqe->getRepository('AeagSqeBundle:PgProgTypeMilieu');
        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotGrparAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $tabWebusers = array();
        $tabMarches = array();
        $tabTitulaires = array();
        $tabZoneGeoRefs = array();
        $tabTypeMilieux = array();
        $tabLots = array();
        $tabPhases = array();

        $nbWebusers = 0;
        $nbMarches = 0;
        $nbTitulaires = 0;
        $nbZoneGeoRefs = 0;
        $nbTypeMilieux = 0;
        $nbLots = 0;
        $nbPhases = 0;


        $webusers = $repoPgProgWebusers->getPgProgWebusers();
        foreach ($webusers as $webuser) {
            $tabWebusers[$nbWebusers] = $webuser;
            $nbWebusers++;
        }

        $marches = $repoPgProgMarche->getPgProgMarches();
        foreach ($marches as $marche) {
            $tabMarches[$nbMarches] = $marche;
            $nbMarches++;
        }

        $titulaires = $repoPgRefCorresPresta->getPgRefCorresPrestas();
        foreach ($titulaires as $titulaire) {
            $tabTitulaires[$nbTitulaires] = $titulaire;
            $nbTitulaires++;
        }

        $zoneGeoRefs = $repoPgProgZoneGeoRef->getPgProgZoneGeoRefs();
        foreach ($zoneGeoRefs as $zoneGeoRef) {
            $tabZoneGeoRefs[$nbZoneGeoRefs] = $zoneGeoRef;
            $nbZoneGeoRefs++;
        }

        $typeMilieux = $repoPgProgTypeMilieu->getPgProgTypeMilieux();
        foreach ($typeMilieux as $typeMilieu) {
            $tabTypeMilieux[$nbTypeMilieux] = $typeMilieu;
            $nbTypeMilieux++;
        }

        $lots = $repoPgProgLot->getPgProgLots();
        foreach ($lots as $lot) {
            $tabLots[$nbLots] = $lot;
            $nbLots++;
        }

        $phases = $repoPgProgPhases->getPgProgPhases();
        foreach ($phases as $phase) {
            $tabPhases[$nbPhases] = $phase;
            $nbPhases++;
        }

        if ($session->has('critAnnee')) {
            $critAnnee = $session->get('critAnnee');
        } else {
            $critAnnee = null;
        }

        if ($session->has('critWebuser')) {
            $critWebuser = $repoPgProgWebusers->getPgProgWebusersByid($session->get('critWebuser'));
        } else {
            $critWebuser = null;
        }


        if ($session->has('critMarche')) {
            $critMarche = $repoPgProgMarche->getPgProgMarcheByid($session->get('critMarche'));
        } else {
            $critMarche = null;
        }

        if ($session->has('critTitulaire')) {
            $critTitulaire = $repoPgRefCorresPresta->getPgRefCorresPrestaByAdrCorId($session->get('critTitulaire'));
        } else {
            $critTitulaire = null;
        }

        if ($session->has('critZoneGeoRef')) {
            $critZoneGeoRef = $repoPgProgZoneGeoRef->getPgProgZoneGeoRefById($session->get('critZoneGeoRef'));
        } else {
            $critZoneGeoRef = null;
        }

        if ($session->has('critTypeMilieu')) {
            $critTypeMilieu = $repoPgProgTypeMilieu->getPgProgTypeMilieuByCodeMilieu($session->get('critTypeMilieu'));
        } else {
            $critTypeMilieu = null;
        }


        if ($session->has('selMilieu')) {
            $selMilieu = $session->get('selMilieu');
        } else {
            $selMilieu = null;
        }


        if ($session->has('critLot')) {
            $critLot = $repoPgProgLot->getPgProgLotById($session->get('critLot'));
        } else {
            $critLot = null;
        }

        if ($session->has('critPhase')) {
            $critPhase = $repoPgProgPhases->getPgProgPhasesById($session->get('critPhase'));
        } else {
            $critPhase = null;
        }

        // les marche de l'année sélectionnée
        if ($critAnnee) {
            $tabMarcheBis = array();
            $i = 0;
            foreach ($tabMarches as $marche) {
                $tabMarcheBis[$i] = $marche;
                $i++;
            }
            $tabMarches = array();
            $i = 0;
            if (count($tabMarcheBis) > 0) {
                foreach ($tabMarcheBis as $marche) {
                    $marchAnnee = $marche->getAnneeDeb();
                    $marcheDuree = $marche->getDuree() - 1;
                    if ($marchAnnee == $critAnnee) {
                        $tabMarches[$i] = $marche;
                        $i++;
                    } else {
                        for ($j = 1; $j <= $marcheDuree; $j++) {
                            if ($marchAnnee + $j == $critAnnee) {
                                $tabMarches[$i] = $marche;
                                $i++;
                                $J = $marcheDuree + 1;
                            }
                        }
                    }
                }
            }
        }

        // les marche de l'utilisateur sélectionnée
        if ($critWebuser) {
            $tabMarcheBis = array();
            $i = 0;
            foreach ($tabMarches as $marche) {
                $tabMarcheBis[$i] = $marche;
                $i++;
            }
            $tabMarches = array();
            $i = 0;
            if (count($tabMarcheBis) > 0) {
                foreach ($tabMarcheBis as $marche) {
                    $pgprogmarcheUsers = $repoPgProgMarcheUser->getPgProgMarcheUserByMarche($marche);
                    foreach ($pgprogmarcheUsers as $pgprogmarcheUser) {
                        if ($pgprogmarcheUser->getWebuser()->getId() == $critWebuser->getId()) {
                            $tabMarches[$i] = $marche;
                            $i++;
                        }
                    }
                }
            }
        }

        // le marché séléctionne
        if ($critMarche) {
            $tabMarcheBis = array();
            $i = 0;
            foreach ($tabMarches as $marche) {
                $tabMarcheBis[$i] = $marche;
                $i++;
            }
            $tabMarches = array();
            $i = 0;
            foreach ($tabMarcheBis as $marche) {
                if ($marche->getId() == $critMarche->getId()) {
                    $tabMarches[$i] = $marche;
                    $i++;
                }
            }
        }

        // les lots séléctionées suivant les marches
        $tabLotBis = array();
        $i = 0;
        foreach ($tabLots as $lot) {
            $tabLotBis[$i] = $lot;
            $i++;
        }
        $tabLots = array();
        $i = 0;
        foreach ($tabLotBis as $lot) {
            foreach ($tabMarches as $marche) {
                if ($lot->getMarche()->getid() == $marche->getid()) {
                    $tabLots[$i] = $lot;
                    $i++;
                }
            }
        }

        // les lots séléctionnées suivant la phase
        if ($critPhase) {
            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }
            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByLot($lot);
                if (count($pgProgLotAns > 0)) {
                    $trouve = false;
                    foreach ($pgProgLotAns as $pgProgLotAn) {
                        if ($pgProgLotAn->getPhase()->getId() == $critPhase->getId()) {
                            $tabLots[$i] = $lot;
                            $i++;
                        }
                    }
                } else {
                    $tabLots[$i] = $lot;
                    $i++;
                }
            }
        }

        // les lots séléctionées suivant les titulaires
        if ($critTitulaire) {
            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }
            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                if ($lot->getTitulaire()) {
                    if ($lot->getTitulaire()->getAdrCorId() == $critTitulaire->getAdrCorId()) {
                        $tabLots[$i] = $lot;
                        $i++;
                    }
                }
            }
        }


        // les lots séléctionées suivant les zones géographiques
        if ($critZoneGeoRef) {
            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }
            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                if ($lot->getZgeoRef()) {
                    if ($lot->getZgeoRef()->getId() == $critZoneGeoRef->getId()) {
                        $tabLots[$i] = $lot;
                        $i++;
                    }
                }
            }
        }


        // les lots séléctionées suivant les types de milieu
        if ($critTypeMilieu) {
            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }
            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                if ($lot->getCodeMilieu()->getCodeMilieu() == $critTypeMilieu->getCodeMilieu()) {
                    $tabLots[$i] = $lot;
                    $i++;
                }
            }
        }


        // les lots séléctionées suivant l'année selectionnée

        if ($critAnnee) {
            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }
            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAnneeLot($critAnnee, $lot);
                if (count($pgProgLotAns) > 0) {
                    foreach ($pgProgLotAns as $pgProgLotAn) {
                        if ($pgProgLotAn) {
                            $trouve = false;
                            for ($j = 0; $j < count($tabLots); $j++) {
                                if ($tabLots[$j]->getId() == $lot->getId()) {
                                    $trouve = true;
                                }
                            }
                            if (!$trouve) {
                                $tabLots[$i] = $lot;
                                $i++;
                            }
                        }
                    }
                } else {
                    $tabLots[$i] = $lot;
                    $i++;
                }
            }
        }


        //  lot séléctioné
        if ($critLot) {
            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }
            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                if ($lot->getId() == $critLot->getId()) {
                    $tabLots[$i] = $lot;
                    $i++;
                }
            }
        }


        if ($critAnnee) {
            $tabLotBis = array();
            $i = 0;
            foreach ($tabLots as $lot) {
                $tabLotBis[$i] = $lot;
                $i++;
            }

            $tabLots = array();
            $i = 0;
            foreach ($tabLotBis as $lot) {
                $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAnneeLot($critAnnee, $lot);
                if (count($pgProgLotAns) > 0) {
                    foreach ($pgProgLotAns as $pgProgLotAn) {
                        $tabLots[$i]['lot'] = $lot;
                        $typeMilieu = $lot->getCodeMilieu();
                        $tabLots[$i]['typeMilieu'] = $typeMilieu;
                        $tabLots[$i]['lotAn'] = $pgProgLotAn;
                        $i++;
                    }
                } else {
                    $tabLots[$i]['lot'] = $lot;
                    $typeMilieu = $lot->getCodeMilieu();
                    $tabLots[$i]['typeMilieu'] = $typeMilieu;
                    $tabLots[$i]['lotAn'] = null;
                    $i++;
                }
            }
        }


        $session->set('niveau1', $this->generateUrl('Aeag_sqe_programmation_lots', array('action' => $action)));
        $session->set('niveau2', '');
        $session->set('niveau3', '');
        $session->set('niveau4', '');
        $session->set('niveau5', '');
        $session->set('niveau6', '');

        if (count($tabLots) == 0) {
            $session->getFlashBag()->add('notice-warning', 'aucune programmation de prévue pour cette sélection');
            return $this->redirect($this->generateUrl('Aeag_sqe_programmation_lots', array('action' => $action)));
        }

        if (count($tabLots) == 1) {
            $session->set('critMarche', $tabLots[0]['lot']->getMarche()->getId());
            $session->set('critMarche', $tabLots[0]['lot']->getMarche()->getId());
            if ($tabLots[0]['lot']->getTitulaire()) {
                $session->set('critTitulaire', $tabLots[0]['lot']->getTitulaire()->getAdrCorId());
            } else {
                $session->set('critTitulaire', null);
            }
            $session->set('critZoneGeoRef', $tabLots[0]['lot']->getZGeoRef()->getId());
            $session->set('critTypeMilieu', $tabLots[0]['lot']->getCodeMilieu()->getCodeMilieu());
            $session->set('critLot', $tabLots[0]['lot']->getId());
            //return $this->forward('AeagSqeBundle:Programmation:criteresResultatUnique');
            return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_lot_resultat_unique', array('action' => $action)));
        } else {

            return $this->render('AeagSqeBundle:Programmation:Lot/resultats.html.twig', array(
                        'specialUser' => $session->get('specialUser'),
                        'action' => $action,
                        'entities' => $tabLots,
                        'critAnnee' => $critAnnee,
                        'critPhase' => $critPhase,
                        'critWebuser' => $critWebuser,
                        'critMarche' => $critMarche,
                        'critTitulaire' => $critTitulaire,
                        'critZoneGeoRef' => $critZoneGeoRef,
                        'critTypeMilieu' => $critTypeMilieu,
                        'critMilieu' => $selMilieu,
                        'critLot' => $critLot));
        }
    }

    public function soumettreAction($lotAnId = null, $action = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationLot');
        $session->set('fonction', 'soumettre');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgWebuserTypmil = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserTypmil');
        $repoUsers = $em->getRepository('AeagUserBundle:User');


        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotAnId);

        if ($pgProgLotAn->getPhase()->getCodePhase() == 'P19') {
            $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P24');
        } else {
            $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P25');
        }

        $pgProgLotAn->setPhase($pgProgPhase);
        $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('UPD');
        $pgProgLotAn->setCodeStatut($pgProgStatut);
        $now = date('Y-m-d H:i');
        $now = new \DateTime($now);
        $pgProgLotAn->setDateModif($now);
        $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByExtid($user->getId());
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

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE') and $this->get('security.authorization_checker')->isGranted('ROLE_PROGSQE')) {
            $notification = new Notification();
            $notification->setRecepteur($user->getId());
            $notification->setEmetteur($user->getId());
            $notification->setNouveau(true);
            $notification->setIteration(2);
            $notification->setMessage("la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été soumise à la validation par " . $pgProgWebuser->getNom() . " le " . date_format($pgProgLotAn->getDateModif(), 'd/m/Y') . PHP_EOL);
            $em->persist($notification);
        }

        $userAdmins = $repoUsers->getUsersByRole('ROLE_ADMINSQE');
        $emetteur = $user;
        foreach ($userAdmins as $userAdmin) {
            $pgProgWebuserAdmin = $repoPgProgWebusers->getPgProgWebusersByExtid($userAdmin->getId());
            if ($pgProgWebuserAdmin) {
                $pgProgWebuserTypmils = $repoPgProgWebuserTypmil->getPgProgWebuserTypmilByWebuser($pgProgWebuserAdmin);
                $trouve = false;
                foreach ($pgProgWebuserTypmils as $pgProgWebuserTypmil) {
                    if ($pgProgLotAn->getLot()->getCodeMilieu()->getCodeMilieu() == $pgProgWebuserTypmil->getTypmil()->getCodeMilieu()) {
                        $trouve = true;
                        break;
                    }
                }
                if ($trouve) {
                    $message = new Message();
                    $message->setRecepteur($userAdmin->getId());
                    $message->setEmetteur($user->getid());
                    $message->setNouveau(true);
                    $message->setIteration(2);
                    $texte = "Bonjour ," . PHP_EOL;
                    $texte = $texte . "La programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL;
                    $texte = $texte . " a été soumise à la validation par " . $pgProgWebuser->getNom() . " le " . date_format($pgProgLotAn->getDateModif(), 'd/m/Y') . PHP_EOL;
                    $texte = $texte . " " . PHP_EOL;
                     if ($pgProgLotAn->getPhase()->getCodePhase() == 'P24') {
                     $texte = $texte . "ATTENTION : Il y a des prestataires fictifs dans cette programmation " . PHP_EOL;
                      $texte = $texte . " " . PHP_EOL;
                     }
                    $texte = $texte . "Cordialement.";
                    $message->setMessage($texte);
                    $em->persist($message);

                    $notification = new Notification();
                    $notification->setRecepteur($userAdmin->getId());
                    $notification->setEmetteur($user->getId());
                    $notification->setNouveau(true);
                    $notification->setIteration(2);
                    $notification->setMessage("la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été soumise à la validation par " . $pgProgWebuser->getNom() . " le " . date_format($pgProgLotAn->getDateModif(), 'd/m/Y'));
                    $em->persist($notification);
                    // Récupération du service.
                    $mailer = $this->get('mailer');
                    $dest = $userAdmin->getEmail() . ";";
                    $destinataires = explode(";", $dest);
// Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
                    $mail = \Swift_Message::newInstance('Wonderful Subject')
                            ->setSubject('Programmation ' . $pgProgLotAn->getAnneeProg() . ' version ' . $pgProgLotAn->getVersion() . ' du lot  ' . $pgProgLotAn->getLot()->getNomLot() . '  soumise à validation')
                            ->setFrom('automate@eau-adour-garonne.fr')
                            ->setTo($userAdmin->getEmail())
                            ->setBody($this->renderView('AeagSqeBundle:Programmation:Lot/prevaliderEmail.txt.twig', array(
                                'emetteur' => $pgProgWebuser,
                                'lotan' => $pgProgLotAn,
                    )));

// Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
                    $mailer->send($mail);
                }
            }
        }

        $em->flush();
        $emSqe->flush();

        if ($pgProgWebuser) {
            $this->get('session')->getFlashBag()->add('notice-success', "la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été soumise à la validation  par " . $pgProgWebuser->getNom() . " le " . date_format($pgProgLotAn->getDateModif(), 'd/m/Y'));
        } else {
            $this->get('session')->getFlashBag()->add('notice-success', "la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été soumise à la validation  par " . $user->getUserName() . " le " . date_format($pgProgLotAn->getDateModif(), 'd/m/Y'));
        }

        // $session->set('critPhase', $pgProgPhase->getId());


        return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_lot_retour', array('action' => $action)));
    }

    public function debloquerAction($lotAnId = null, $action = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationLot');
        $session->set('fonction', 'debloquer');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotAnId);

        $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P15');
        $pgProgLotAn->setPhase($pgProgPhase);
        $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('UPD');
        $pgProgLotAn->setCodeStatut($pgProgStatut);
        $now = date('Y-m-d H:i');
        $now = new \DateTime($now);
        $pgProgLotAn->setDateModif($now);
        $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByExtid($user->getId());
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

        // $session->set('critPhase', $pgProgPhase->getId());

        if ($pgProgWebuser) {
            $this->get('session')->getFlashBag()->add('notice-success', "la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été débloquée par " . $pgProgWebuser->getNom());
        } else {
            $this->get('session')->getFlashBag()->add('notice-success', "la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été débloquée par " . $user->getUserName());
        }

        return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_lot_retour', array('action' => $action)));
    }

    public function validerAction($lotAnId = null, $action = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationLot');
        $session->set('fonction', 'valider');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoUsers = $em->getRepository('AeagUserBundle:User');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotAnId);

        $pgProgWebuser = $pgProgLotAn->getUtilModif();
        $recepteur = $repoUsers->getUserByUsernamePassword($pgProgWebuser->getLogin(), $pgProgWebuser->getPwd());

        $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P30');
        $pgProgLotAn->setPhase($pgProgPhase);
        $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('UPD');
        $pgProgLotAn->setCodeStatut($pgProgStatut);
        $now = date('Y-m-d H:i');
        $now = new \DateTime($now);
        $pgProgLotAn->setDateModif($now);
        $emetteur = $repoPgProgWebusers->getPgProgWebusersByExtid($user->getId());
        if ($emetteur) {
            $pgProgLotAn->setUtilModif($emetteur);
        }
        $emSqe->persist($pgProgLotAn);

        $pgProgSuiviPhases = new PgProgSuiviPhases();
        $pgProgSuiviPhases->setTypeObjet('LOT');
        $pgProgSuiviPhases->setObjId($pgProgLotAn->getId());
        $pgProgSuiviPhases->setDatePhase(new \DateTime());
        $pgProgSuiviPhases->setPhase($pgProgPhase);
        if ($emetteur) {
            $pgProgSuiviPhases->setUser($emetteur);
        }
        $emSqe->persist($pgProgSuiviPhases);

        $emSqe->flush();

        $message = new Message();
        $message->setRecepteur($recepteur->getId());
        $message->setEmetteur($user->getid());
        $message->setNouveau(true);
        $message->setIteration(2);
        $texte = "Bonjour ," . PHP_EOL;
        $texte = $texte . " " . PHP_EOL;
        $texte = $texte . "La programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL;
        $texte = $texte . " a été validée par l'administrateur " . $emetteur->getNom() . " le " . date_format($pgProgLotAn->getDateModif(), 'd/m/Y') . PHP_EOL;
        $texte = $texte . " " . PHP_EOL;
        $texte = $texte . "Cordialement.";
        $message->setMessage($texte);
        $em->persist($message);

        $notification = new Notification();
        $notification->setRecepteur($recepteur->getId());
        $notification->setEmetteur($user->getId());
        $notification->setNouveau(true);
        $notification->setIteration(2);
        $notification->setMessage("la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été validé par l'administrateur " . $emetteur->getNom());
        $em->persist($notification);
        // Récupération du service.
        $mailer = $this->get('mailer');
        // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
        $mail = \Swift_Message::newInstance('Wonderful Subject')
                ->setSubject('Programmation ' . $pgProgLotAn->getAnneeProg() . ' version ' . $pgProgLotAn->getVersion() . ' du lot  ' . $pgProgLotAn->getLot()->getNomLot() . '  validée')
                ->setFrom('automate@eau-adour-garonne.fr')
                ->setTo($recepteur->getEmail())
                ->setBody($this->renderView('AeagSqeBundle:Programmation:Lot/validerEmail.txt.twig', array(
                    'emetteur' => $emetteur,
                    'lotan' => $pgProgLotAn,
        )));

// Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
        $mailer->send($mail);

        $em->flush();

        if ($emetteur) {
            $this->get('session')->getFlashBag()->add('notice-success', "la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été validée par " . $emetteur->getNom());
        } else {
            $this->get('session')->getFlashBag()->add('notice-success', "la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été validée par " . $user->getUserName());
        }

        // $session->set('critPhase', $pgProgPhase->getId());

        return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_lot_retour', array('action' => $action)));
    }

    public function refuserAction($lotAnId = null, $action = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationLot');
        $session->set('fonction', 'refuser');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        if (isset($_POST["idRefusMotif-" . $lotAnId])) {
            $motifRefus = $_POST["idRefusMotif-" . $lotAnId];
        } else {
            $motifRefus = null;
        }

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoUsers = $em->getRepository('AeagUserBundle:User');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotAnId);

        $pgProgWebuser = $pgProgLotAn->getUtilModif();
        $recepteur = $repoUsers->getUserByUsernamePassword($pgProgWebuser->getLogin(), $pgProgWebuser->getPwd());

        $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P15');
        $pgProgLotAn->setPhase($pgProgPhase);
        $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('UPD');
        $pgProgLotAn->setCodeStatut($pgProgStatut);
        $now = date('Y-m-d H:i');
        $now = new \DateTime($now);
        $pgProgLotAn->setDateModif($now);
        $emetteur = $repoPgProgWebusers->getPgProgWebusersByExtid($user->getId());
        if ($emetteur) {
            $pgProgLotAn->setUtilModif($emetteur);
        }
        $emSqe->persist($pgProgLotAn);

        $pgProgSuiviPhases = new PgProgSuiviPhases();
        $pgProgSuiviPhases->setTypeObjet('LOT');
        $pgProgSuiviPhases->setObjId($pgProgLotAn->getId());
        $pgProgSuiviPhases->setDatePhase(new \DateTime());
        $pgProgSuiviPhases->setPhase($pgProgPhase);
        if ($emetteur) {
            $pgProgSuiviPhases->setUser($emetteur);
        }
        $emSqe->persist($pgProgSuiviPhases);

        $emSqe->flush();

        $message = new Message();
        $message->setRecepteur($recepteur->getId());
        $message->setEmetteur($user->getid());
        $message->setNouveau(true);
        $message->setIteration(2);
        $texte = "Bonjour ," . PHP_EOL;
        $texte = $texte . " " . PHP_EOL;
        $texte = $texte . "La programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL;
        if ($motifRefus) {
            $texte = $texte . " a été refusée par l'administrateur " . $emetteur->getNom() . " le " . date_format($pgProgLotAn->getDateModif(), 'd/m/Y') . " pour la raision suivante : " . PHP_EOL;
            $texte = $texte . " " . PHP_EOL;
            $texte = $texte . $motifRefus . PHP_EOL;
        } else {
            $texte = $texte . " a été refusée par l'administrateur " . $emetteur->getNom() . " le " . date_format($pgProgLotAn->getDateModif(), 'd/m/Y') . PHP_EOL;
            $texte = $texte . " " . PHP_EOL;
        }
        $texte = $texte . " " . PHP_EOL;
        $texte = $texte . "Vous devez la modifier et la soumettre à nouveau à la validation." . PHP_EOL;
        $texte = $texte . " " . PHP_EOL;
        $texte = $texte . "Cordialement.";
        $message->setMessage($texte);
        $em->persist($message);
        $messageEmetteur = clone($message);
        $messageEmetteur->setRecepteur($user->getid());
        $em->persist($messageEmetteur);

        $notification = new Notification();
        $notification->setRecepteur($recepteur->getId());
        $notification->setEmetteur($user->getId());
        $notification->setNouveau(true);
        $notification->setIteration(2);
        $notification->setMessage("la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été refusée par l'administrateur " . $emetteur->getNom() . " le " . date_format($pgProgLotAn->getDateModif(), 'd/m/Y'));
        $em->persist($notification);
        $notificationEmetteur = clone($notification);
        $notificationEmetteur->setRecepteur($user->getid());
        $em->persist($notificationEmetteur);
        
        // Récupération du service.
        $mailer = $this->get('mailer');
        // Envoi au recepteur
        $mail = \Swift_Message::newInstance('Wonderful Subject')
                ->setSubject('Programmation ' . $pgProgLotAn->getAnneeProg() . ' version ' . $pgProgLotAn->getVersion() . ' du lot  ' . $pgProgLotAn->getLot()->getNomLot() . '  refusée')
                ->setFrom('automate@eau-adour-garonne.fr')
                ->setTo($recepteur->getEmail())
                ->setBody($this->renderView('AeagSqeBundle:Programmation:Lot/refuserEmail.txt.twig', array(
                    'emetteur' => $emetteur,
                    'lotan' => $pgProgLotAn,
                    'motifRefus' => $motifRefus,
        )));
        $mailer->send($mail);
        
        // Envoi a l'emetteur
        $mail = \Swift_Message::newInstance('Wonderful Subject')
                ->setSubject('Programmation ' . $pgProgLotAn->getAnneeProg() . ' version ' . $pgProgLotAn->getVersion() . ' du lot  ' . $pgProgLotAn->getLot()->getNomLot() . '  refusée')
                ->setFrom('automate@eau-adour-garonne.fr')
                ->setTo($user->getEmail())
                ->setBody($this->renderView('AeagSqeBundle:Programmation:Lot/refuserEmail.txt.twig', array(
                    'emetteur' => $emetteur,
                    'lotan' => $pgProgLotAn,
                    'motifRefus' => $motifRefus,
        )));
        $mailer->send($mail);

        $em->flush();

        if ($emetteur) {
            $this->get('session')->getFlashBag()->add('notice-error', "la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été refusée par " . $emetteur->getNom());
        } else {
            $this->get('session')->getFlashBag()->add('notice-error', "la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été refusée par " . $user->getUserName());
        }


        // $session->set('critPhase', $pgProgPhase->getId());

        return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_lot_retour', array('action' => $action)));
    }

    public function devaliderAction($lotAnId = null, $action = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationLot');
        $session->set('fonction', 'devalider');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotAnId);

        $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P25');
        $pgProgLotAn->setPhase($pgProgPhase);
        $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('UPD');
        $pgProgLotAn->setCodeStatut($pgProgStatut);
        $now = date('Y-m-d H:i');
        $now = new \DateTime($now);
        $pgProgLotAn->setDateModif($now);
        $emetteur = $repoPgProgWebusers->getPgProgWebusersByExtid($user->getId());
        if ($emetteur) {
            $pgProgLotAn->setUtilModif($emetteur);
        }
        $emSqe->persist($pgProgLotAn);

        $pgProgSuiviPhases = new PgProgSuiviPhases();
        $pgProgSuiviPhases->setTypeObjet('LOT');
        $pgProgSuiviPhases->setObjId($pgProgLotAn->getId());
        $pgProgSuiviPhases->setDatePhase(new \DateTime());
        $pgProgSuiviPhases->setPhase($pgProgPhase);
        if ($emetteur) {
            $pgProgSuiviPhases->setUser($emetteur);
        }
        $emSqe->persist($pgProgSuiviPhases);

        $emSqe->flush();

        // $session->set('critPhase', $pgProgPhase->getId());

        if ($emetteur) {
            $this->get('session')->getFlashBag()->add('notice-success', "la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été dévalidée par " . $emetteur->getNom());
        } else {
            $this->get('session')->getFlashBag()->add('notice-success', "la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été dévalidée par " . $user->getUserName());
        }

        return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_lot_retour', array('action' => $action)));
    }

    public function supprimerAction($lotAnId = null, $action = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationLot');
        $session->set('fonction', 'supprimer');
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
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotAnId);

        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);

        foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
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
            // $emSqe->flush();
            $emSqe->remove($pgProgLotPeriodeAn);
        }



        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                $emSqe->remove($pgProgLotParamAn);
            }
            $emSqe->remove($pgProgLotGrparAn);
        }



        foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
            $emSqe->remove($pgProgLotStationAn);
        }



        $emSqe->flush();
        $this->get('session')->getFlashBag()->add('notice-error', "la programmation " . $pgProgLotAn->getAnneeProg() . " version " . $pgProgLotAn->getVersion() . " du lot " . $pgProgLotAn->getLot()->getNomLot() . PHP_EOL . " a été supprimée.");

        if ($pgProgLotAn->getVersion() > 1) {
            $pgProgLotAnPrec = $repoPgProgLotAn->getPgProgLotAnByAnneeLotVersion($pgProgLotAn->getAnneeProg(), $pgProgLotAn->getLot(), $pgProgLotAn->getVersion() - 1);
            if ($pgProgLotAnPrec) {
                $pgProgLotPeriodeAnPrecs = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAnPrec);
                $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('UPD');
                foreach ($pgProgLotPeriodeAnPrecs as $pgProgLotPeriodeAnPrec) {
                    $pgProgLotPeriodeAnPrec->setCodeStatut($pgProgStatut);
                    $emSqe->persist($pgProgLotPeriodeAnPrec);
                }
                $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P40');
                $pgProgLotAnPrec->setPhase($pgProgPhase);
                $pgProgLotAnPrec->setCodeStatut($pgProgStatut);
                $now = date('Y-m-d H:i');
                $now = new \DateTime($now);
                $pgProgLotAnPrec->setDateModif($now);
                $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByExtid($user->getId());
                $pgProgLotAnPrec->setUtilModif($pgProgWebuser);
                $emSqe->persist($pgProgLotAnPrec);
                // $session->set('critPhase', $pgProgPhase->getId());
            }
        }

        $emSqe->remove($pgProgLotAn);
        $emSqe->flush();

        return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_lot_retour', array('action' => $action)));
    }

    public function periodesDisponiblesAction($lotAnId = null, $action = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationLot');
        $session->set('fonction', 'periodesDisponibles');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $i = 0;
        $ok = 'ko';
        $tabMessage = array();
        $tabMessage[$i] = null;
        $i++;

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotAnId);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
        $pgProgPhases = $repoPgProgPhases->getPgProgPhasesByCodePhase('D30');

        $now = date('Y-m-d H:i');
        $now = new \DateTime($now);
        $tabPeriodes = array();
        $i = 0;

        foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
            $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandesByLotanPeriode($pgProgLotAn, $pgProgLotPeriodeAn->getPeriode());
            $ok = true;
            foreach ($pgCmdDemandes as $pgCmdDemande) {
                if ($pgCmdDemande->getPhaseDemande()->getCodePhase() >= $pgProgPhases->getCodePhase()) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                $tabPeriodes[$i] = $pgProgLotPeriodeAn->getPeriode();
                $i++;
            } else {
                $tabPeriodes = array();
                $i = 0;
            }
        }

        return $this->render('AeagSqeBundle:Programmation:Lot/periodesDisponibles.html.twig', array(
                    'lotan' => $pgProgLotAn,
                    'periodes' => $tabPeriodes,
                    'action' => $action));
    }

    public function dupliquerAction($lotAnId = null, $action = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationLot');
        $session->set('fonction', 'dupliquer');
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
        $repoPgProgLotParamAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgStatut = $emSqe->getRepository('AeagSqeBundle:PgProgStatut');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotAnId);
        $pgProgLotGrparAns = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAn);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);

        $pgProgLotAnBis = clone($pgProgLotAn);
        $pgProgLotAnBis->setVersion($pgProgLotAn->getVersion() + 1);
        $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P10');
        $pgProgLotAnBis->setPhase($pgProgPhase);
        $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('CRE');
        $pgProgLotAnBis->setCodeStatut($pgProgStatut);
        $now = date('Y-m-d H:i');
        $now = new \DateTime($now);
        $pgProgLotAnBis->setDateModif($now);
        $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByLoginPassword($user->getUsername(), $user->getPassword());
        $pgProgLotAnBis->setUtilModif($pgProgWebuser);
        $emSqe->persist($pgProgLotAnBis);
        //$emSqe->flush();

        foreach ($pgProgLotGrparAns as $pgProgLotGrparAn) {
            $pgProgLotGrparAnBis = clone($pgProgLotGrparAn);
            $pgProgLotGrparAnBis->setLotan($pgProgLotAnBis);
            $pgProgLotParamAns = $repoPgProgLotParamAn->getPgProgLotParamAnByGrparan($pgProgLotGrparAn);
            foreach ($pgProgLotParamAns as $pgProgLotParamAn) {
                $pgProgLotParamAnBis = clone($pgProgLotParamAn);
                $pgProgLotParamAnBis->setGrparan($pgProgLotGrparAnBis);
                $emSqe->persist($pgProgLotParamAnBis);
            }
            $emSqe->persist($pgProgLotGrparAnBis);
        }

        foreach ($pgProgLotStationAns as $pgProgLotStationAn) {
            $pgProgLotStationAnBis = clone($pgProgLotStationAn);
            $pgProgLotStationAnBis->setLotan($pgProgLotAnBis);
            $emSqe->persist($pgProgLotStationAnBis);
        }

        $emSqe->flush();

        if (isset($_POST['optPeriode'])) {
            $debPeriode = $repoPgProgPeriodes->getPgProgPeriodesById($_POST['optPeriode']);
            foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
                // if ($now->format('ymd') <= $pgProgLotPeriodeAn->getPeriode()->getDateFin()->format('ymd')) {
                //print_r(  $now->format('ymd')  . ' > ' . $pgProgLotPeriodeAn->getPeriode()->getDateDeb()->format('ymd') . ' < ' . $pgProgLotPeriodeAn->getPeriode()->getDateFin()->format('ymd') . '     ');
                if ($pgProgLotPeriodeAn->getPeriode()->getNumPeriode() >= $debPeriode->getNumPeriode()) {
                    $pgProgLotPeriodeAnBis = clone($pgProgLotPeriodeAn);
                    $pgProgLotPeriodeAnBis->setLotan($pgProgLotAnBis);
                    $emSqe->persist($pgProgLotPeriodeAnBis);

                    $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
                    foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg) {
                        $pgProgLotPeriodeProgBis = clone($pgProgLotPeriodeProg);
                        $pgProgLotPeriodeProgBis->setPeriodan($pgProgLotPeriodeAnBis);
                        $pgProgLotGrparAnsBis = $repoPgProgLotGrparAn->getPgProgLotGrparAnByLotan($pgProgLotAnBis);
                        foreach ($pgProgLotGrparAnsBis as $pgProgLotGrparAnBis) {
                            if ($pgProgLotGrparAnBis->getGrparRef()->getId() == $pgProgLotPeriodeProgBis->getGrparAn()->getGrparRef()->getId()) {
                                $pgProgLotPeriodeProgBis->setGrparAn($pgProgLotGrparAnBis);
                                break;
                            }
                        }
                        $pgProgLotStationAnsBis = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAnBis);
                        foreach ($pgProgLotStationAnsBis as $pgProgLotStationAnBis) {
                            if ($pgProgLotStationAnBis->getStation()->getOuvFoncid() == $pgProgLotPeriodeProgBis->getStationAn()->getStation()->getOuvFoncid()) {
                                $pgProgLotPeriodeProgBis->setStationAn($pgProgLotStationAnBis);
                                break;
                            }
                        }
                        $emSqe->persist($pgProgLotPeriodeProgBis);
                    }
                    $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('INV');
                    $pgProgLotPeriodeAn->setCodeStatut($pgProgStatut);
                    $emSqe->persist($pgProgLotPeriodeAn);
                }
            }
        }

        $pgProgPhase = $repoPgProgPhases->getPgProgPhasesByCodePhase('P45');
        $pgProgLotAn->setPhase($pgProgPhase);
        $pgProgStatut = $repoPgProgStatut->getPgProgStatutByCodeStatut('UPD');
        $pgProgLotAn->setCodeStatut($pgProgStatut);
        $now = date('Y-m-d H:i');
        $now = new \DateTime($now);
        $pgProgLotAn->setDateModif($now);
        $pgProgLotAn->setUtilModif($pgProgWebuser);
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
        $this->get('session')->getFlashBag()->add('notice-error', "la programmation " . $pgProgLotAnBis->getAnneeProg() . " version " . $pgProgLotAnBis->getVersion() . " du lot " . $pgProgLotAnBis->getLot()->getNomLot() . PHP_EOL . " a été créée.");

        return $this->redirect($this->generateUrl('AeagSqeBundle_programmation_lot_retour', array('action' => $action)));
    }

}
