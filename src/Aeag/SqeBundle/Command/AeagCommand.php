<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AeagCommand extends ContainerAwareCommand {

    protected $emSqe;
    protected $em;
    protected $output;
    protected $input;
    protected $repoPgCmdFichiersRps;
    protected $repoPgProgPhases;
    protected $repoPgTmpValidEdilabo;
    protected $repoPgLogValidEdilabo;
    protected $repoPgCmdDemande;
    protected $repoPgCmdSuiviPrel;
    protected $repoPgRefCorresPresta;
    protected $repoPgProgLotLqParam;
    protected $repoPgProgUnitesPossiblesParam;
    protected $repoPgSandreFractions;
    protected $repoPgCmdPrelev;
    protected $repoPgCmdPrelevPc;
    protected $repoPgProgWebUsers;
    protected $repoPgSandreParametres;
    protected $repoPgSandreUnites;
    protected $repoPgSandreMethodes;
    protected $repoPgSandreZoneVerticaleProspectee;
    protected $repoPgProgSuiviPhases;
    protected $repoPgCmdMesureEnv;
    protected $repoPgCmdAnalyse;
    protected $repoPgProgZoneGeoRef;
    protected $repoPgProgTypeMilieu;
    protected $repoPgProgWebuserTypmil;
    protected $repoPgProgBornesParams;
    protected $repoPgProgPrestaTypfic;
    protected $detectionCodeRemarqueComplet;
    protected $detectionCodeRemarqueMoitie;
    protected $controleVraisemblaceService;
    protected $excelObj;
    protected $evolution;
    protected $env;

    protected function configure() {
        $this
                ->setName('rai:aeag')
                ->setDescription('Classe parente des autres commandes. A ne pas utiliser directement!!')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

    }

    protected function initialize(InputInterface $input, OutputInterface $output) {

        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->emSqe = $this->getContainer()->get('doctrine')->getManager('sqe');

        $this->output = $output;
        $this->input = $input;

        $this->repoPgCmdFichiersRps = $this->emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $this->repoPgCmdDemande = $this->emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $this->repoPgProgPhases = $this->emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $this->repoPgTmpValidEdilabo = $this->emSqe->getRepository('AeagSqeBundle:PgTmpValidEdilabo');
        $this->repoPgLogValidEdilabo = $this->emSqe->getRepository('AeagSqeBundle:PgLogValidEdilabo');
        $this->repoPgRefCorresPresta = $this->emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $this->repoPgProgLotLqParam = $this->emSqe->getRepository('AeagSqeBundle:PgProgLotLqParam');
        $this->repoPgProgUnitesPossiblesParam = $this->emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $this->repoPgSandreFractions = $this->emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $this->repoPgCmdPrelev = $this->emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $this->repoPgCmdPrelevPc = $this->emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        $this->repoPgProgWebUsers = $this->emSqe->getRepository('AeagSqeBundle:PgProgWebUsers');
        $this->repoPgSandreParametres = $this->emSqe->getRepository('AeagSqeBundle:PgSandreParametres');
        $this->repoPgSandreUnites = $this->emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $this->repoPgSandreZoneVerticaleProspectee = $this->emSqe->getRepository('AeagSqeBundle:PgSandreZoneVerticaleProspectee');
        $this->repoPgProgLotParamAn = $this->emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
        $this->repoPgProgSuiviPhases = $this->emSqe->getRepository('AeagSqeBundle:PgProgSuiviPhases');
        $this->repoPgSandreMethodes = $this->emSqe->getRepository('AeagSqeBundle:PgSandreMethodes');
        $this->repoPgCmdMesureEnv = $this->emSqe->getRepository('AeagSqeBundle:PgCmdMesureEnv');
        $this->repoPgCmdAnalyse = $this->emSqe->getRepository('AeagSqeBundle:PgCmdAnalyse');
        $this->repoPgCmdSuiviPrel = $this->emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $this->repoPgProgZoneGeoRef = $this->emSqe->getRepository('AeagSqeBundle:PgProgZoneGeoRef');
        $this->repoPgProgTypeMilieu = $this->emSqe->getRepository('AeagSqeBundle:PgProgTypeMilieu');
        $this->repoPgProgWebuserTypmil = $this->emSqe->getRepository('AeagSqeBundle:PgProgWebuserTypmil');
        $this->repoPgProgBornesParams = $this->emSqe->getRepository('AeagSqeBundle:PgProgBornesParams');
        $this->repoPgProgPrestaTypfic = $this->emSqe->getRepository('AeagSqeBundle:PgProgPrestaTypfic');
        $this->repoParametre = $this->emSqe->getRepository('AeagSqeBundle:Parametre');

        // Chargement des fichiers csv dans des tableaux
        $cheminCourant = __DIR__ . '/../../../../';
        $this->detectionCodeRemarqueComplet = $this->_csvToArray($cheminCourant . "/web/tablesCorrespondancesRai/detectionCodeRemarqueComplet.csv");
        $this->detectionCodeRemarqueMoitie = $this->_csvToArray($cheminCourant . "/web/tablesCorrespondancesRai/detectionCodeRemarqueMoitie.csv");

        $this->controleVraisemblaceService = $this->getContainer()->get('aeag_sqe.controle_vraisemblance');

        $this->setEvolution($this->repoParametre->getParametreByCode('EVOLUTION'));
        $this->excelObj = $this->getContainer()->get('xls.load_xls5');
        
        $this->env = $input->getOption('env');
    }

    protected function _updatePhaseFichierRps(\Aeag\SqeBundle\Entity\PgCmdFichiersRps $pgCmdFichierRps, $phase, $phase82atteinte = false) {
        $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase($phase);
        $this->_addSuiviPhase('RPS', $pgCmdFichierRps->getId(), $pgProgPhases);

        if ($phase82atteinte) {
            $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase('R82');
        }

        $pgCmdFichierRps->setPhaseFichier($pgProgPhases);
        $this->emSqe->persist($pgCmdFichierRps);
        $this->emSqe->flush();
    }

    protected function _updatePhasePrelevement(\Aeag\SqeBundle\Entity\PgCmdPrelev $pgCmdPrelev, $phase) {
        $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase($phase);
        $pgCmdPrelev->setPhaseDmd($pgProgPhases);
        $this->emSqe->persist($pgCmdPrelev);

        $this->emSqe->flush();

        //$this->_addSuiviPhase('PRL', $pgCmdPrelev->getId(), $pgProgPhases);
    }

    protected function _updatePhaseDemande(\Aeag\SqeBundle\Entity\PgCmdDemande $pgCmdDemande) {
        // Si la demande < D30, mettre en D30
        if ($pgCmdDemande->getPhaseDemande() < $this->repoPgProgPhases->findOneByCodePhase('D30')) {
            $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase('D30');
            $pgCmdDemande->setPhaseDemande($pgProgPhases);

            $this->emSqe->persist($pgCmdDemande);
            $this->emSqe->flush();
        }

        // Si tous les prélèvements de la demande sont en M40, passer la demande en D40
        $phase = $this->repoPgProgPhases->findOneByCodePhase('M40');
        if ($this->repoPgCmdPrelev->getCountPgCmdPrelevByPhase($pgCmdDemande, $phase) == $this->repoPgCmdPrelev->getCountAllPgCmdPrelev($pgCmdDemande)) {
            $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase('D40');
            $pgCmdDemande->setPhaseDemande($pgProgPhases);

            $this->emSqe->persist($pgCmdDemande);
            $this->emSqe->flush();
        }
    }

    protected function _addSuiviPhase($typeObj, $objId, $pgProgPhase) {
        $pgProgSuiviPhases = new \Aeag\SqeBundle\Entity\PgProgSuiviPhases;
        $pgProgSuiviPhases->setTypeObjet($typeObj);
        $pgProgSuiviPhases->setObjId($objId);
        $pgProgSuiviPhases->setDatePhase(new \DateTime());
        $pgProgSuiviPhases->setPhase($pgProgPhase);
        $this->emSqe->persist($pgProgSuiviPhases);

        $this->emSqe->flush();
    }

    protected function _addLog($typeErreur, $demandeId, $fichierRpsId, $message, $codePrelevement = null, $commentaire = null) {
        $dateLog = new \DateTime();
        if (!is_null($commentaire) && is_array($commentaire)) {
            if (count($commentaire) == count($commentaire, COUNT_RECURSIVE)) {
                $commentaire = implode("-", $commentaire);
            } else {
                $commentaire = $this->_convertMultiArray($commentaire);
            }
        }
        $pgLogValidEdilabo = new \Aeag\SqeBundle\Entity\PgLogValidEdilabo($demandeId, $fichierRpsId, $typeErreur, $message, $dateLog, $codePrelevement, $commentaire);

        $this->emSqe->persist($pgLogValidEdilabo);
        $this->emSqe->flush();
    }

    /* protected function _addLog($typeErreur, $demandeId, $fichierRpsId, $message, $codePrelevement = null, $commentaire = null) {
      $pgCmdFichierRps = $this->repoPgCmdFichiersRps->findOneById($fichierRpsId);
      if (!is_null($commentaire) && is_array($commentaire)) {
      $commentaire = $this->_convertMultiArray($commentaire);
      }
      $this->_addLogAlt($typeErreur, $pgCmdFichierRps, $message, $codePrelevement, $commentaire);
      } */

    protected function _addLogAlt($typeErreur, $pgCmdFichierRps, $message, $codePrelevement = null, $commentaire = null) {
        // Récupération du fichier de CR
        $chemin = $this->getContainer()->getParameter('repertoire_echange');
        $pathBase = $this->getContainer()->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdFichierRps->getDemande(), $pgCmdFichierRps->getId());
        $fileName = $pgCmdFichierRps->getNomFichierCompteRendu();
        $fullFileName = $pathBase . '/' . $fileName;
        // Insertion de la ligne à la suite
        $date = new \DateTime();
        //[08-04-2016 21:10:10] - 05025835_3_2016_8_22240001200118 - error - Incoherence RAI/DAI: Paramètre manquant - 1429
        $cr = "[" . $date->format('d-m-Y H:i:s') . "] - " . $codePrelevement . " - " . $typeErreur . " - " . $message . " - " . $commentaire . "\r\n";
        file_put_contents($fullFileName, $cr, FILE_APPEND);
    }

    protected function _insertFichierLog($pgCmdFichierRps, $nomParagraphe) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        $fileName = $pgCmdFichierRps->getNomFichierCompteRendu();
        $logs = $this->repoPgLogValidEdilabo->findBy(array('demandeId' => $demandeId, 'fichierRpsId' => $reponseId));

        // Récupération et ouverture du fichier de log
        $chemin = $this->getContainer()->getParameter('repertoire_echange');
        $pathBase = $this->getContainer()->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdFichierRps->getDemande(), $pgCmdFichierRps->getId());
        $fullFileName = $pathBase . '/' . $fileName;

        $cr = '';
        $cr .= "--- Début " . $nomParagraphe . " \r\n";
        foreach ($logs as $log) {
            $cr .= $log . "\r\n";
        }
        $cr .= "--- Fin " . $nomParagraphe . " \r\n";

        if (file_put_contents($fullFileName, $cr, FILE_APPEND) !== false) {
            //Suppression des logs
            $this->_cleanLogTable($pgCmdFichierRps);
        }
    }

    protected function _convertMultiArray($array) {
        $out = implode(",", array_map(function($a) {
                    return implode("-", $a);
                }, $array));
        return $out;
    }

    protected function _csvToArray($nomFichier) {
        $result = array();
        if (($handle = fopen($nomFichier, "r")) !== FALSE) {
            $row = 0;
            while ((($data = fgetcsv($handle, 1000, ";")) !== FALSE)) {
                if ($row !== 0) {
                    $result[] = $data;
                }
                $row++;
            }
        }

        return $result;
    }

    protected function isAlreadyAdded($pgCmdFichierRps, $pgCmdPrelev, $phase) {
        $pgProgPhase = $this->repoPgProgPhases->findOneByCodePhase($phase);
        $pgCmdPrelevExisting = $this->repoPgCmdPrelev->getPgCmdPrelevByCodePrelevCodeDmdAndPhase($pgCmdPrelev, $pgCmdFichierRps->getDemande(), $pgProgPhase);
        if (count($pgCmdPrelevExisting) > 0) {
            return true;
        }
        return false;
    }

    protected function _cleanLogTable($pgCmdFichierRps) {
        if ($this->getEnv() !== 'preprod') {
            $demandeId = $pgCmdFichierRps->getDemande()->getId();
            $reponseId = $pgCmdFichierRps->getId();
            $pgPgLogValidEdilabos = $this->repoPgLogValidEdilabo->findBy(array('demandeId' => $demandeId, 'fichierRpsId' => $reponseId));
            foreach ($pgPgLogValidEdilabos as $pgPgLogValidEdilabo) {
                $this->emSqe->remove($pgPgLogValidEdilabo);
            }

            $this->emSqe->flush();
        }
    }

    protected function _cleanTmpTable($pgCmdFichierRps) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();

        // Suppression de la RAI
        $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->findBy(array('demandeId' => $demandeId, 'fichierRpsId' => $reponseId));
        foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
            $this->emSqe->remove($pgTmpValidEdilabo);
        }

        // Suppression de la DAI
        // Avant de supprimer la DAI, on vérifie s'il n'existe pas encore des RAIs
        if (count($this->repoPgTmpValidEdilabo->getPgTmpValidEdilaboRps($demandeId)) == 0) {
            $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->getPgTmpValidEdilaboDmd($demandeId);
            foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
                $this->emSqe->remove($pgTmpValidEdilabo);
            }
        }

        $pgProgSuiviPhases = $this->repoPgProgSuiviPhases->findBy(array('typeObjet' => 'RPS', 'objId' => $reponseId));
        foreach ($pgProgSuiviPhases as $pgProgSuiviPhase) {
            $this->emSqe->remove($pgProgSuiviPhase);
        }

        $this->emSqe->flush();
        $this->_cleanLogTable($pgCmdFichierRps);

    }

    // Méthodes permettant la gestion des doublons
    public function getCodeRqByCodeParametre($codeParametre, $demandeId, $reponseId, $codePrelevement, $codeFraction = null) {
        $codeRq = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($codeParametre, $demandeId, $reponseId, $codePrelevement, $codeFraction);
        if ($codeRq == -1) {
            $this->_addLog('error', $demandeId, $reponseId, 'Présence de doublon du code remarque', $codePrelevement, $codeParametre);
            return null;
        }
        return $codeRq;
    }

    public function getMesureByCodeParametre($codeParametre, $demandeId, $reponseId, $codePrelevement, $codeFraction = null) {
        $mesure = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre($codeParametre, $demandeId, $reponseId, $codePrelevement, $codeFraction);
        if ($mesure == -999) {
            $this->_addLog('error', $demandeId, $reponseId, 'Présence de doublon de la mesure', $codePrelevement, $codeParametre);
            return null;
        }
        return $mesure;
    }

    public function getMesuresByCodeParametre($codeParametre, $codePrelevement, $demandeId, $reponseId = null, $codeFraction = null) {
        $mesures = $this->repoPgTmpValidEdilabo->getMesuresByCodeParametre($codeParametre, $codePrelevement, $demandeId, $reponseId, $codeFraction);
        if ($mesures == -1) {
            $this->_addLog('error', $demandeId, $reponseId, 'Présence de doublon des mesures', $codePrelevement, $codeParametre);
            return null;
        }
        return $mesures;
    }

    public function getDatePrelevement($codePrelevement, $demandeId, $reponseId = null) {
        $datePrelev = $this->repoPgTmpValidEdilabo->getDatePrelevement($codePrelevement, $demandeId, $reponseId);
        if ($datePrelev == -1) {
            $this->_addLog('error', $demandeId, $reponseId, 'Présence de doublon de la date de prelevement', $codePrelevement);
            return null;
        }
        return $datePrelev;
    }

    public function getEvolution() {
        return $this->evolution;
    }

    public function setEvolution($evolution) {
        $this->evolution = $evolution;
    }
    
    public function getEnv() {
        return $this->env;
    }

    public function setEnv($env) {
        $this->env = $env;
    }
}
