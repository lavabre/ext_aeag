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
    protected $repoPgRefCorresPresta;
    protected $repoPgProgLotLqParam;
    protected $repoPgProgUnitesPossiblesParam;
    protected $repoPgSandreFractions;
    protected $repoPgCmdPrelev;
    protected $repoPgCmdPrelevPc;
    protected $repoPgProgWebUsers;
    protected $repoPgSandreParametres;
    protected $repoPgSandreUnites;
    protected $repoPgSandreZoneVerticaleProspectee;
    protected $repoPgProgSuiviPhases;
    
    protected $detectionCodeRemarqueComplet;
    protected $detectionCodeRemarqueMoitie;

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
        
        // Chargement des fichiers csv dans des tableaux 
        $cheminCourant = __DIR__ . '/../../../../';
        $this->detectionCodeRemarqueComplet = $this->_csvToArray($cheminCourant . "/web/tablesCorrespondancesRai/detectionCodeRemarqueComplet.csv");
        $this->detectionCodeRemarqueMoitie = $this->_csvToArray($cheminCourant . "/web/tablesCorrespondancesRai/detectionCodeRemarqueMoitie.csv");
    }

    protected function _updatePhaseFichierRps($pgCmdFichierRps, $phase, $phaseExclu = false) {
        $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase($phase);
        if (!$phaseExclu) {
            $pgCmdFichierRps->setPhaseFichier($pgProgPhases);
            $this->emSqe->persist($pgCmdFichierRps);
        }

        $this->emSqe->flush();

        $this->_addSuiviPhase('RPS', $pgCmdFichierRps->getId(), $pgProgPhases);
    }
    
    protected function _updatePhasePrelevement($pgCmdPrelev, $phase) {
        $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase($phase);
        $pgCmdPrelev->setPhaseDmd($pgProgPhases);
        $this->emSqe->persist($pgCmdPrelev);

        $this->emSqe->flush();
        
        //$this->_addSuiviPhase('PRL', $pgCmdPrelev->getId(), $pgProgPhases);
        
    }
    
    protected function _updatePhaseDemande($pgCmdDemande) {
        // Si la demande < D30, mettre en D30
        if ($this->repoPgCmdDemande->getPhaseDemande() < $this->repoPgProgPhases->findOneByCodePhase('D30') ) {
            $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase('D30');
        } 
        
        // Si tous les prélèvements de la demande sont en M40, passer la demande en D40
        $phase = $this->repoPgProgPhases->findOneByCodePhase('M40');
        if ($this->repoPgCmdPrelev->getCountPgCmdPrelevByPhase($pgCmdDemande, $phase) == $this->repoPgCmdPrelev->getCountAllPgCmdPrelev($pgCmdDemande)) {
            $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase('D40');
            
        }
        
        $pgCmdDemande->setPhaseDemande($pgProgPhases);
        
        $this->emSqe->persist($pgCmdDemande);
        $this->emSqe->flush();
        
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
            $commentaire = $this->_convertMultiArray($commentaire);
        }
        $pgLogValidEdilabo = new \Aeag\SqeBundle\Entity\PgLogValidEdilabo($demandeId, $fichierRpsId, $typeErreur, $message, $dateLog, $codePrelevement, $commentaire);

        $this->emSqe->persist($pgLogValidEdilabo);
        $this->emSqe->flush();
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

}
