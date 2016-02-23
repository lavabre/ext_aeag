<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;

class CheckProcessRaiCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('rai:check_process')
                ->setDescription('Controle des RAI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $emSqe = $this->getContainer()->get('doctrine')->getManager('sqe');
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        
        // On récupère les RAIs dont les phases sont en R25
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R25');
        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findBy(array('phaseFichier' => $pgProgPhases, 'suppr' => 'N'));
        
        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            if ($this->coherenceRaiDai($output, $emSqe, $pgCmdFichierRps)) {
                $output->writeln('Coherence RAI / DAI');
            } else {
                $output->writeln('Incoherence RAI / DAI');
            }
        }
    }
    
    protected function coherenceRaiDai($output, $emSqe, $pgCmdFichierRps) {
        $repoPgTmpValidEdilabo = $emSqe->getRepository('AeagSqeBundle:PgTmpValidEdilabo');
        
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        
        // Vérif code demande
        if (count($repoPgTmpValidEdilabo->getDiffCodeDemande($demandeId, $reponseId)) > 0) {
            return false;
        }
        
        // Vérif code prélèvement
        if (count($repoPgTmpValidEdilabo->getDiffCodePrelevement($demandeId, $reponseId)) > 0) {
            return false;
        }
        
        // Vérif Date prélèvement, si hors période
        $codePrelevs = $repoPgTmpValidEdilabo->getCodePrelevement($demandeId, $reponseId);
        foreach($codePrelevs as $codePrelev) {
            $datePrelRps = $repoPgTmpValidEdilabo->getDatePrelevement($codePrelev["codePrelevement"], $demandeId, $reponseId);
            $datePrelRps = new \DateTime($datePrelRps["datePrel"]);
            
            $datePrelDmd = $repoPgTmpValidEdilabo->getDatePrelevement($codePrelev["codePrelevement"], $demandeId);
            $datePrelDmdMin = new \DateTime($datePrelDmd["datePrel"]);
            
            $delaiPrel = $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getDelaiPrel();
            
            $datePrelDmdMax = clone $datePrelDmdMin;
            $datePrelDmdMax->add(new \DateInterval('P'.$delaiPrel.'D'));
            
            if ($datePrelDmdMin > $datePrelRps || $datePrelRps > $datePrelDmdMax) {
                return false;
            }
        }
        
        /*foreach($codePrevelementsRpsTab as $codePrevelementRpsTab) {
            $stationsRps = $repoPgTmpValidEdilabo->getStationsByCodePrelevement($codePrevelementRpsTab);
            $stationsDmd = $repoPgTmpValidEdilabo->getStationsByCodePrelevement($codePrevelementRpsTab);
            // Vérif code intervenant
        
            // Vérif STQ : concordance STQ RAI (unique ou multiple) / DAI : stations manquantes

            // Vérif STQ : concordance STQ RAI (unique ou multiple) / DAI : stations rajoutées

            // paramètres/unité : si unité changée

            // paramètres/unité : rajout de paramètres

            // paramètres/unité : paramètre manquant
        }*/
        
        
        return true;
    }
    
    protected function convertTabAssoc($oldTabAssocs, $index) {
        $newTab = array();
        foreach ($oldTabAssocs as $oldTabAssoc) {
            $newTab[] = $oldTabAssoc[$index];
        }
        return $newTab;
    }
    
    

    

}
