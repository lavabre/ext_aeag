<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\AeagBundle\Controller\AeagController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\PhpProcess;
use Symfony\Component\Process\ProcessBuilder;

class DepotHydrobioController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'index');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        // Récupération des programmations
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');

        if ($user->hasRole('ROLE_ADMINSQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAdmin();
        } else if ($user->hasRole('ROLE_PRESTASQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByPresta($user);
        } else if ($user->hasRole('ROLE_PROGSQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByProg($user);
        } else if ($user->hasRole('ROLE_SQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAdmin();
        }

        $tabProglotAns = array();
        $i = 0;
        foreach ($pgProgLotAns as $pgProgLotAn) {
            $pgProgLot = $pgProgLotAn->getLot();
            $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
            $trouve = false;
            if ($pgProgTypeMilieu->getCodeMilieu() == 'RHB') {
                $pgProgLotPeriodeAns = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotan($pgProgLotAn);
                if (count($pgProgLotPeriodeAns) > 0) {
                    foreach ($pgProgLotPeriodeAns as $pgProgLotPeriodeAn) {
                        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByPeriodeAn($pgProgLotPeriodeAn);
                        if (count($pgProgLotPeriodeProgs) > 0) {
                            $trouve = true;
                            break;
                        }
                    }
                    if ($trouve) {
                        $tabProglotAns[$i] = $pgProgLotAn;
                        $i++;
                    }
                }
            }
        }

        //     \Symfony\Component\VarDumper\VarDumper::dump($tabProglotAns);
        //   return new response ('nb lotan : ' . count($tabProglotAns) );


        if ($user->hasRole('ROLE_ADMINSQE')) {
            return $this->render('AeagSqeBundle:DepotHydrobio:index.html.twig', array('user' => $user,
                        'lotans' => $tabProglotAns));
        } else {
            return $this->render('AeagSqeBundle:Default:maintenanceFonctionnalite.html.twig');
        }
    }

    public function demandesAction($lotanId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'demandes');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgProgLotAn = $repoPgProgLotAn->findOneById($lotanId);
        $pgCmdDemandes = $repoPgCmdDemande->getPgCmdDemandeByLotan($pgProgLotAn);

        $reponses = array();
        $reponsesMax = array();
        foreach ($pgCmdDemandes as $pgCmdDemande) {
            $reponses[$pgCmdDemande->getId()] = $repoPgCmdFichiersRps->getReponsesExcelByDemande($pgCmdDemande->getId());
        }
        return $this->render('AeagSqeBundle:DepotHydrobio:demandes.html.twig', array('user' => $pgProgWebUser,
                    'demandes' => $pgCmdDemandes,
                    'lotan' => $pgProgLotAn,
                    'reponses' => $reponses));
    }

    public function prelevementsAction($demandeId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'prelevements');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgProgLotAn = $pgCmdDemande->getLotan();
        $pgProgLot = $pgProgLotAn->getLot();

        if ($pgCmdDemande) {
            $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotanPeriode($pgCmdDemande->getLotan(), $pgCmdDemande->getPeriode());
            if ($pgProgLot->getDelaiPrel()) {
                $dateFin = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
                $delai = $pgProgLot->getDelaiPrel();
                $dateFin->add(new \DateInterval('P' . $delai . 'D'));
            } else {
                $dateFin = $pgProgLotPeriodeAn->getPeriode()->getDateFin();
            }
            $tabCmdPrelevs = array();
            $nbCmdPrelevs = 0;
            $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
            foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                $tabCmdPrelevs[$nbCmdPrelevs]['cmdPrelev'] = $pgCmdPrelev;
                $tabCmdPrelevs[$nbCmdPrelevs]['lien'] = '/sqe_fiches_stations/' . str_replace('/', '-', $pgCmdPrelev->getStation()->getCode()) . '.pdf';
                $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgCmdPrelev->getStation());
                if ($pgProgLotStationAn) {
                    $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
                    if ($pgRefReseauMesure) {
                        $tabCmdPrelevs[$nbCmdPrelevs]['reseau'] = $pgRefReseauMesure;
                    } else {
                        $tabCmdPrelevs[$nbCmdPrelevs]['reseau'] = null;
                    }
                } else {
                    $tabCmdPrelevs[$nbCmdPrelevs]['reseau'] = null;
                }
                $tabCmdPrelevs[$nbCmdPrelevs]['maj'] = 'N';
                $tabCmdPrelevs[$nbCmdPrelevs]['commentaire'] = null;
                $pgCmdSuiviPrels = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevOrderId($pgCmdPrelev);
                $tabSuiviPrels = array();
                $nbSuiviPrels = 0;
                if (count($pgCmdSuiviPrels) == 0) {
                    $tabSuiviPrels[$nbSuiviPrels]['suiviPrel'] = array();
                    $tabSuiviPrels[$nbSuiviPrels]['maj'] = 'O';
                    $tabCmdPrelevs[$nbCmdPrelevs]['maj'] = 'O';
                } else {
                    foreach ($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
                        $tabSuiviPrels[$nbSuiviPrels]['suiviPrel'] = $pgCmdSuiviPrel;
                        $tabSuiviPrels[$nbSuiviPrels]['maj'] = 'N';
                        $tabSuiviPrels[$nbSuiviPrels]['avisSaisie'] = 'N';
                        if ($pgCmdSuiviPrel->getCommentaire()) {
                            if ($tabCmdPrelevs[$nbCmdPrelevs]['commentaire'] == null) {
                                $tabCmdPrelevs[$nbCmdPrelevs]['commentaire'] = $pgCmdSuiviPrel->getCommentaire();
                            }
                        }
                        if ($user->hasRole('ROLE_ADMINSQE') or ( $pgCmdPrelev->getPrestaPrel() == $pgCmdDemande->getPrestataire())) {
                            if ($pgCmdSuiviPrel->getStatutPrel() != 'F') {
                                $tabSuiviPrels[$nbSuiviPrels]['maj'] = 'O';
                                $tabCmdPrelevs[$nbCmdPrelevs]['maj'] = 'O';
                            } else {
                                if (!$pgCmdSuiviPrel->getFichierRps()) {
                                    if ($pgCmdSuiviPrel->getValidation() != 'A') {
                                        $tabSuiviPrels[$nbSuiviPrels]['maj'] = 'O';
                                        $tabCmdPrelevs[$nbCmdPrelevs]['maj'] = 'O';
                                    }
                                }
                            }
                        }
                        $commentaire = $pgCmdSuiviPrel->getCommentaire();
                        $tabCommentaires = explode(CHR(13) . CHR(10), $commentaire);
                        for ($nbLignes = 0; $nbLignes < count($tabCommentaires); $nbLignes++) {
                            $pos = explode(' ', $tabCommentaires[$nbLignes]);
                            //echo ('ligne : ' . $nbLignes . '  pos : ' . $pos[0] .  ' ligne : ' . $tabCommentaires[$nbLignes] . '</br>');
                            if ($pos[0] == 'Déposé' and $pos[1] = 'le' and $pos[3] == 'à' and $pos[5] == 'par' and $pos[7] == ':') {
                                $commentaireBis = null;
                                for ($nbLignesBis = 0; $nbLignesBis < $nbLignes; $nbLignesBis++) {
                                    $commentaireBis .= $tabCommentaires[$nbLignesBis] . CHR(13) . CHR(10);
                                }
                                if (!$user->hasRole('ROLE_ADMINSQE')) {
                                    $tabSuiviPrels[$nbSuiviPrels]['suiviPrel']->setCommentaire($commentaireBis);
                                    break;
                                }
                            }
                        }
                        $nbSuiviPrels++;
                        break;
                    }
                }
                if (count($tabSuiviPrels) > 0) {
                    $tabCmdPrelevs[$nbCmdPrelevs]['suiviPrels'] = $tabSuiviPrels;
                } else {
                    $tabCmdPrelevs[$nbCmdPrelevs]['suiviPrels'] = null;
                }
                //print_r('prelev : ' . $pgCmdPrelev->getid() . '</br>');
                $tabAutrePrelevs = array();
                $tabAutrePrelevs = $repoPgCmdPrelev->getAutrePrelevs($pgCmdPrelev);
                if (count($tabAutrePrelevs) > 0) {
                    $tabCmdPrelevs[$nbCmdPrelevs]['autrePrelevs'] = $tabAutrePrelevs;
                } else {
                    $tabCmdPrelevs[$nbCmdPrelevs]['autrePrelevs'] = null;
                }
//                         if ($pgCmdPrelev->getStation()->getOuvFoncId() == 557655){
//                             for($j = 0 ; $j < count($tabCmdPrelevs[$nbCmdPrelevs]['autrePrelevs']); $j++){
//                                 echo('j : ' . $j . ' date : ' . $tabCmdPrelevs[$nbCmdPrelevs]['autrePrelevs'][$j]['datePrel'] . ' support : ' . $tabCmdPrelevs[$nbCmdPrelevs]['autrePrelevs'][$j]['support'] . '</br>');
//                             }
//                             echo('nb: ' . count($tabCmdPrelevs[$nbCmdPrelevs]['autrePrelevs']));
//               \Symfony\Component\VarDumper\VarDumper::dump($tabCmdPrelevs);
//               return new Response('');
//                        }
                $nbCmdPrelevs++;
            }
        }

//        \Symfony\Component\VarDumper\VarDumper::dump($tabCmdPrelevs);
//        return new Response('');
        return $this->render('AeagSqeBundle:DepotHydrobio:prelevements.html.twig', array(
                    'user' => $pgProgWebUser,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'dateFin' => $dateFin,
                    'demande' => $pgCmdDemande,
                    'prelevs' => $tabCmdPrelevs
        ));
    }

    public function prelevementDetailAction($prelevId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'prelevementDetail');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotPeriodeAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeAn');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdPrelevHbInvert = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevHbInvert');
        $repoPgCmdInvertRecouv = $emSqe->getRepository('AeagSqeBundle:PgCmdInvertRecouv');
        $repoPgCmdInvertPrelem = $emSqe->getRepository('AeagSqeBundle:PgCmdInvertPrelem');
        $repoPgCmdInvertListe = $emSqe->getRepository('AeagSqeBundle:PgCmdInvertListe');
        $repoPgSandreHbNomemclatures = $emSqe->getRepository('AeagSqeBundle:PgSandreHbNomemclatures');
        $repoPgSandreAppellationTaxon = $emSqe->getRepository('AeagSqeBundle:PgSandreAppellationTaxon');
        $repoPgCmdPrelevHbDiato = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevHbDiato');
        $repoPgCmdDiatoListe = $emSqe->getRepository('AeagSqeBundle:PgCmdDiatoListe');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdPrelev = $repoPgCmdPrelev->getPgCmdPrelevById($prelevId);
        $pgCmdDemande = $pgCmdPrelev->getdemande();
        $pgProgLotAn = $pgCmdDemande->getLotan();
        $pgProgLot = $pgProgLotAn->getlot();
        $periode = $pgCmdDemande->getPeriode();

        $pgProgLotPeriodeAn = $repoPgProgLotPeriodeAn->getPgProgLotPeriodeAnByLotanPeriode($pgProgLotAn, $periode);
        if ($pgProgLot->getDelaiPrel()) {
            $dateFin = clone($pgProgLotPeriodeAn->getPeriode()->getDateDeb());
            $delai = $pgProgLot->getDelaiPrel();
            $dateFin->add(new \DateInterval('P' . $delai . 'D'));
        } else {
            $dateFin = $pgProgLotPeriodeAn->getPeriode()->getDateFin();
        }

        $tabRecouvs = array();
        $tabPrelems = array();
        $tabListes = array();
        $tabDiatomes = array();

        $pgCmdPrelevHbInvert = $repoPgCmdPrelevHbInvert->getPgCmdPrelevHbInvertByPrelev($pgCmdPrelev);
        if ($pgCmdPrelevHbInvert) {
            $pgCmdInvertRecouvs = $repoPgCmdInvertRecouv->getPgCmdInvertRecouvByPrelev($pgCmdPrelevHbInvert);
            $i = 0;
            foreach ($pgCmdInvertRecouvs as $pgCmdInvertRecouv) {
                $tabRecouvs[$i]['recouv'] = $pgCmdInvertRecouv;
                $pgSandreHbNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElement(274, $pgCmdInvertRecouv->getSubstrat());
                $tabRecouvs[$i]['nomenclature'] = $pgSandreHbNomemclature;
                $i++;
            }
            $pgCmdInvertPrelems = $repoPgCmdInvertPrelem->getPgCmdInvertPrelemByPrelev($pgCmdPrelevHbInvert);
            //        \Symfony\Component\VarDumper\VarDumper::dump($pgCmdInvertPrelems);
            //        return new response ('nb recouv : ' . count($pgCmdInvertRecouvs) . ' nb prelem :  ' . count($pgCmdInvertPrelems));
            $i = 0;
            foreach ($pgCmdInvertPrelems as $pgCmdInvertPrelem) {
                $tabPrelems[$i]['prelem'] = $pgCmdInvertPrelem;
                $pgSandreHbNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElement(274, $pgCmdInvertPrelem->getSubstrat());
                $tabPrelems[$i]['nomenclature'] = $pgSandreHbNomemclature;
                $i++;
            }
            $pgCmdInvertListes = $repoPgCmdInvertListe->getPgCmdInvertListeByPrelev($pgCmdPrelevHbInvert);
            $i = 0;
            $j = 0;
            $codeSandre = null;
            $taxon = null;
            $tabSandre = array();
            for ($j = 0; $j < 15; $j++) {
                $tabSandre[$j] = null;
            }
            foreach ($pgCmdInvertListes as $pgCmdInvertListe) {
                if (!$codeSandre) {
                    $codeSandre = $pgCmdInvertListe->getCodeSandre();
                    $taxon = $pgCmdInvertListe->getTaxon();
                }
                if ($codeSandre != $pgCmdInvertListe->getCodeSandre()) {
                    $tabListes[$i]['taxon'] = $taxon;
                    $tabListes[$i]['codeSandre'] = $codeSandre;
                    $tabListes[$i]['liste'] = $tabSandre;
                    $i++;
                    $codeSandre = $pgCmdInvertListe->getCodeSandre();
                    $taxon = $pgCmdInvertListe->getTaxon();
                    $tabSandre = array();
                    for ($j = 0; $j < 15; $j++) {
                        $tabSandre[$j] = null;
                    }
                }
                if ($pgCmdInvertListe->getPhase() == 'PHA') {
                    $tabSandre[0] = $pgCmdInvertListe;
                }
                if ($pgCmdInvertListe->getPhase() == 'PHB') {
                    $tabSandre[1] = $pgCmdInvertListe;
                }
                if ($pgCmdInvertListe->getPhase() == 'PHC') {
                    $tabSandre[2] = $pgCmdInvertListe;
                }
                if ($pgCmdInvertListe->getPrelem() == 'P1') {
                    $tabSandre[3] = $pgCmdInvertListe;
                }
                if ($pgCmdInvertListe->getPrelem() == 'P2') {
                    $tabSandre[4] = $pgCmdInvertListe;
                }
                if ($pgCmdInvertListe->getPrelem() == 'P3') {
                    $tabSandre[5] = $pgCmdInvertListe;
                }
                if ($pgCmdInvertListe->getPrelem() == 'P4') {
                    $tabSandre[6] = $pgCmdInvertListe;
                }
                if ($pgCmdInvertListe->getPrelem() == 'P5') {
                    $tabSandre[7] = $pgCmdInvertListe;
                }
                if ($pgCmdInvertListe->getPrelem() == 'P6') {
                    $tabSandre[8] = $pgCmdInvertListe;
                }
                if ($pgCmdInvertListe->getPrelem() == 'P7') {
                    $tabSandre[9] = $pgCmdInvertListe;
                }
                if ($pgCmdInvertListe->getPrelem() == 'P8') {
                    $tabSandre[10] = $pgCmdInvertListe;
                }
                if ($pgCmdInvertListe->getPrelem() == 'P9') {
                    $tabSandre[11] = $pgCmdInvertListe;
                }
                if ($pgCmdInvertListe->getPrelem() == 'P10') {
                    $tabSandre[12] = $pgCmdInvertListe;
                }
                if ($pgCmdInvertListe->getPrelem() == 'P11') {
                    $tabSandre[13] = $pgCmdInvertListe;
                }
                if ($pgCmdInvertListe->getPrelem() == 'P12') {
                    $tabSandre[14] = $pgCmdInvertListe;
                }
            }
            $tabListes[$i]['taxon'] = $taxon;
            $tabListes[$i]['codeSandre'] = $codeSandre;
            $tabListes[$i]['liste'] = $tabSandre;
        }

        $pgCmdPrelevHbDiato = $repoPgCmdPrelevHbDiato->getPgCmdPrelevHbDiatoByPrelev($pgCmdPrelev);
        if ($pgCmdPrelevHbDiato) {
            $tabDiatomes['diatome'] = $pgCmdPrelevHbDiato;
            $tabDiatomeListes = array();
            $i = 0;
            $pgCmdDiatoListes = $repoPgCmdDiatoListe->getPgCmdDiatoListesByPrelev($pgCmdPrelevHbDiato);
            foreach ($pgCmdDiatoListes as $pgCmdDiatoListe) {
                $tabDiatomeListes[$i]['liste'] = $pgCmdDiatoListe;
                $pgSandreAppellationTaxon = $repoPgSandreAppellationTaxon->getPgSandreAppellationTaxonByCodeAppelTaxonCodeSupport($pgCmdDiatoListe->getCodeSandre(), '10');
                $tabDiatomeListes[$i]['taxon'] = $pgSandreAppellationTaxon;
                $i++;
            }
            $tabDiatomes['liste'] = $tabDiatomeListes;
        }

//        \Symfony\Component\VarDumper\VarDumper::dump($tabListes);
//        return new response('nb diatomes : ' . count($tabListes));

        return $this->render('AeagSqeBundle:DepotHydrobio:prelevementDetail.html.twig', array(
                    'user' => $pgProgWebUser,
                    'periodeAn' => $pgProgLotPeriodeAn,
                    'dateFin' => $dateFin,
                    'demande' => $pgCmdDemande,
                    'prelev' => $pgCmdPrelev,
                    'pgCmdPrelevHbInvert' => $pgCmdPrelevHbInvert,
                    'pgCmdInvertRecouvs' => $tabRecouvs,
                    'pgCmdInvertPrelems' => $tabPrelems,
                    'pgCmdInvertListes' => $tabListes,
                    'pgCmdPrelevHbDiato' => $tabDiatomes));
    }

    public function telechargerAction($demandeId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'telecharger');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        if ($pgCmdDemande) {
            // Récupération du fichier
            $zipName = str_replace('xml', 'zip', $pgCmdDemande->getNomFichier());
            $chemin = $this->getParameter('repertoire_depotHydrobio');
            $pathBase = $this->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdDemande);

            // Changement de la phase s'il est téléchargé par un presta pour la première fois
            if ($user->hasRole('ROLE_PRESTASQE') && substr($pgCmdDemande->getPhaseDemande()->getCodePhase(), 1) < '25') {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D25');
                if (count($pgProgPhases) > 0) {
                    $pgCmdDemande->setPhaseDemande($pgProgPhases);
                    $emSqe->persist($pgCmdDemande);
                    $emSqe->flush();
                }
            }

            // On log le téléchargement
            $log = new \Aeag\SqeBundle\Entity\PgCmdDwnldUsrDmd();
            $log->setUser($pgProgWebUser);
            $log->setDemande($pgCmdDemande);
            $log->setDate(new \DateTime());
            $emSqe->persist($log);
            $emSqe->flush();

            header('Content-Type', 'application/zip');
            header('Content-disposition: attachment; filename="' . $zipName . '"');
            header('Content-Length: ' . filesize($pathBase . $zipName));
            readfile($pathBase . $zipName);
            exit();
        }
    }

    public function telechargerFichierAction($demandeId = null, $nomFichier = null) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'telecharger');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        if ($pgCmdDemande) {
            // Récupération du fichier
            $chemin = $this->getParameter('repertoire_depotHydrobio');
            $pathBase = $this->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdDemande);

            // Changement de la phase s'il est téléchargé par un presta pour la première fois
            if ($user->hasRole('ROLE_PRESTASQE') && substr($pgCmdDemande->getPhaseDemande()->getCodePhase(), 1) < '25') {
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D25');
                if (count($pgProgPhases) > 0) {
                    $pgCmdDemande->setPhaseDemande($pgProgPhases);
                    $emSqe->persist($pgCmdDemande);
                    $emSqe->flush();
                }
            }

            $type = substr($nomFichier, -3);

            // On log le téléchargement
            $log = new \Aeag\SqeBundle\Entity\PgCmdDwnldUsrDmd();
            $log->setUser($pgProgWebUser);
            $log->setDemande($pgCmdDemande);
            $log->setDate(new \DateTime());
            $emSqe->persist($log);
            $emSqe->flush();



            header('Content-Type', "'application/" . $type . "'");
            header('Content-disposition: attachment; filename="' . $nomFichier . '"');
            header('Content-Length: ' . filesize($pathBase . $nomFichier));
            readfile($pathBase . $nomFichier);
            exit();
        }
    }

    public function reponsesAction($demandeId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'reponses');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgCmdFichiersRps = $repoPgCmdFichiersRps->getReponseByDemandeType($demandeId, 'DHY');
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());

        return $this->render('AeagSqeBundle:DepotHydrobio:reponses.html.twig', array(
                    'reponses' => $pgCmdFichiersRps,
                    'demande' => $pgCmdDemande,
                    'user' => $pgProgWebUser));
    }

    public function selectionnerReponseAction($demandeId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'reponses');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');

        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);

        return $this->render('AeagSqeBundle:DepotHydrobio:selectionnerReponse.html.twig', array('demande' => $pgCmdDemande));
    }

    public function deposerReponseAction($demandeId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'deposerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R10');

        // Récupération des valeurs du fichier
        $nomFichier = $_FILES["fichier"]["name"];
        if (substr($nomFichier, -3) != "zip") {
            $session->getFlashBag()->add('notice-error', 'Le fichier déposé n\'est pas un fichier zip');
            return $this->redirect($this->generateUrl('AeagSqeBundle_depotHydrobio_demandes', array('lotanId' => $pgCmdDemande->getLotan()->getId())));
        }

        // Enregistrement des valeurs en base
        $reponse = new \Aeag\SqeBundle\Entity\PgCmdFichiersRps();
        $reponse->setDemande($pgCmdDemande);
        $reponse->setNomFichier($nomFichier);
        $reponse->setDateDepot(new \DateTime());
        $reponse->setTypeFichier('DHY');
        $reponse->setPhaseFichier($pgProgPhases);
        $reponse->setUser($pgProgWebUser);
        $reponse->setSuppr('N');

        $emSqe->persist($reponse);


        // Enregistrement du fichier sur le serveur
        $chemin = $this->getParameter('repertoire_depotHydrobio');
        $pathBase = $this->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdDemande, $reponse->getId());

        if (!mkdir($pathBase, 0755, true)) {
            $session->getFlashBag()->add('notice-error', 'Le répertoire n\'a pas pu être créé');
        }

        $emSqe->flush();

        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $pathBase . '/' . $nomFichier)) {

            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R15');
            $reponse->setPhaseFichier($pgProgPhases);
            $emSqe->persist($reponse);

//            $commande = $this->get('commande');
//            $commande->runCommand('rai:depotHydrobio', array('pgCmdFichierRps_id' => $reponse->getId()));
//
//            $process = new PhpProcess('php app/console rai:depotHydrobio ' . $reponse->getId());
//            $process->run();
//            $builder = new ProcessBuilder(array('cd /base/extranet/dev/Joel/ext_aeag', 'php app/console rai:depotHydrobio ' . $reponse->getId()));
//            $builder->getProcess()->run();
//            $builder = new ProcessBuilder();
//            $builder
//                    ->setPrefix('/base/extranet/dev/Joel/ext_aeag')
//                    ->setArguments(array('php app/console rai:depotHydrobio ' . $reponse->getId()))
//                    ->getProcess()
//                    ->run();
//            $cmd = '/base/extranet/dev/Joel/ext_aeag/app/console  rai:depotHydrobio ' . $reponse->getId();
//            pclose(popen("start /B " . $cmd, "r"));

            $session->getFlashBag()->add('notice-success', 'fichier ' . $nomFichier . ' en cours de traitement');
        } else {
            $emSqe->remove($reponse);
            $session->getFlashBag()->add('notice-error', 'Erreur lors du téléchargement du fichier ' . $nomFichier);
        }
        $emSqe->flush();

        return $this->redirect($this->generateUrl('AeagSqeBundle_depotHydrobio_demandes', array('lotanId' => $pgCmdDemande->getLotan()->getId())));
    }

    public function telechargerReponseAction($reponseId, $typeFichier) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'telechargerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');

        $pgProgWebUser = $repoPgProgWebUsers->findOneByExtId($user->getId());
        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findOneById($reponseId);

        // Récupération du fichier
        $chemin = $this->getParameter('repertoire_depotHydrobio');
        $pathBase = $this->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdFichiersRps->getDemande(), $reponseId);
        switch ($typeFichier) {
            case "DHY" :
                $contentType = "application/zip";
                $fileName = $pgCmdFichiersRps->getNomFichier();
                break;
            case "CR" :
                $contentType = "application/octet-stream";
                $fileName = $pgCmdFichiersRps->getNomFichierCompteRendu();
                break;
        }
        // On log le téléchargement
        $log = new \Aeag\SqeBundle\Entity\PgCmdDwnldUsrRps();
        $log->setUser($pgProgWebUser);
        $log->setFichierReponse($pgCmdFichiersRps);
        $log->setDate(new \DateTime());
        $log->setTypeFichier($typeFichier);
        $emSqe->persist($log);
        $emSqe->flush();

        header('Content-Type', $contentType);
        header('Content-disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($pathBase . '/' . $fileName));
        readfile($pathBase . '/' . $fileName);
        exit();
    }

    public function supprimerReponseAction($reponseId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'DepotHydrobio');
        $session->set('fonction', 'deposerReponse');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findOneById($reponseId);
        $pgCmdDemande = $pgCmdFichiersRps->getDemande();
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');

        // Suppression physique des fichiers
        $chemin = $this->getParameter('repertoire_depotHydrobio');
        $pathBase = $this->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdFichiersRps->getDemande(), $reponseId);
        if (file_exists($pathBase)) {
            if ($this->_rmdirRecursive($pathBase)) {
                // Suppression en base
                $pgCmdFichiersRps->setSuppr('O');
                $emSqe->persist($pgCmdFichiersRps);
                $emSqe->flush();
            }
        } else {
            $pgCmdFichiersRps->setSuppr('O');
            $emSqe->persist($pgCmdFichiersRps);
            $emSqe->flush();
        }

        $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M10');
        foreach ($pgCmdPrelevs as $pgCmdPrelev) {
            $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            $emSqe->persist($pgCmdPrelev);
        }
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('D30');
        $pgCmdDemande->setPhaseDemande($pgProgPhases);
        $emSqe->persist($pgCmdDemande);

        $emSqe->flush();

        return $this->redirect($this->generateUrl('AeagSqeBundle_depotHydrobio_demandes', array('lotanId' => $pgCmdFichiersRps->getDemande()->getLotan()->getId())));
    }

    private function _rmdirRecursive($dir) {
        //Liste le contenu du répertoire dans un tableau
        $dir_content = scandir($dir);
        //Est-ce bien un répertoire?
        if ($dir_content !== FALSE) {
            //Pour chaque entrée du répertoire
            foreach ($dir_content as $entry) {
                //Raccourcis symboliques sous Unix, on passe
                if (!in_array($entry, array('.', '..'))) {
                    //On retrouve le chemin par rapport au début
                    $entry = $dir . '/' . $entry;
                    //Cette entrée n'est pas un dossier: on l'efface
                    if (!is_dir($entry)) {
                        unlink($entry);
                    }
                    //Cette entrée est un dossier, on recommence sur ce dossier
                    else {
                        $this->_rmdirRecursive($entry);
                    }
                }
            }
        }
        //On a bien effacé toutes les entrées du dossier, on peut à présent l'effacer
        return rmdir($dir);
    }

    protected function unzip($file, $path = '', $effacer_zip = false) {/* Méthode qui permet de décompresser un fichier zip $file dans un répertoire de destination $path
      et qui retourne un tableau contenant la liste des fichiers extraits
      Si $effacer_zip est égal à true, on efface le fichier zip d'origine $file */

        $tab_liste_fichiers = array(); //Initialisation

        $zip = zip_open($file);

        if ($zip) {
            while ($zip_entry = zip_read($zip)) { //Pour chaque fichier contenu dans le fichier zip
                if (zip_entry_filesize($zip_entry) >= 0) {
                    $complete_path = $path . dirname(zip_entry_name($zip_entry));

                    /* On supprime les éventuels caractères spéciaux et majuscules */
                    $nom_fichier = zip_entry_name($zip_entry);
                    $nom_fichier = strtr($nom_fichier, "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ", "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn");
                    $nom_fichier = strtolower($nom_fichier);
                    $nom_fichier = ereg_replace('[^a-zA-Z0-9.]', '-', $nom_fichier);

                    /* On ajoute le nom du fichier dans le tableau */
                    array_push($tab_liste_fichiers, $nom_fichier);

                    $complete_name = $path . $nom_fichier; //Nom et chemin de destination

                    if (!file_exists($complete_path)) {
                        $tmp = '';
                        foreach (explode('/', $complete_path) AS $k) {
                            $tmp .= $k . '/';

                            if (!file_exists($tmp)) {
                                mkdir($tmp, 0755);
                            }
                        }
                    }

                    /* On extrait le fichier */
                    if (zip_entry_open($zip, $zip_entry, "r")) {
                        $fd = fopen($complete_name, 'w');

                        fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));

                        fclose($fd);
                        zip_entry_close($zip_entry);
                    }
                }
            }

            zip_close($zip);

            /* On efface éventuellement le fichier zip d'origine */
            if ($effacer_zip === true)
                unlink($file);
        }

        return $tab_liste_fichiers;
    }

}
