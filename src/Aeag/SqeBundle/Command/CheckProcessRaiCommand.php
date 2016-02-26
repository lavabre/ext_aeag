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

    private $emSqe;
    
    private $em;
    
    private $output;
    
    protected function configure() {
        $this
                ->setName('rai:check_process')
                ->setDescription('Controle des RAI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->emSqe = $this->getContainer()->get('doctrine')->getManager('sqe');
        
        $this->output = $output;
        
        $repoPgCmdFichiersRps = $this->emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgProgPhases = $this->emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgTmpValidEdilabo = $this->emSqe->getRepository('AeagSqeBundle:PgTmpValidEdilabo');
        
        // On récupère les RAIs dont les phases sont en R25
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R25');
        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findBy(array('phaseFichier' => $pgProgPhases, 'suppr' => 'N'));
        
        //foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            
            //$this->coherenceRaiDai(86, $repoPgTmpValidEdilabo);
            $this->coherenceRaiDai($pgCmdFichiersRps, $repoPgTmpValidEdilabo);
            
            /*if ($this->controleVraisemblance($output, $pgCmdFichierRps, $repoPgTmpValidEdilabo)) {
                $output->writeln('Controle vraisemblance OK');
            } else {
                $output->writeln('Controle vraisemblance NOK');
            }*/
            
            // TODO Changement de la phase en fonction des retours
        //}
    }
    
    protected function coherenceRaiDai($pgCmdFichierRps, $repoPgTmpValidEdilabo) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        //$demandeId = 14;
        $reponseId = $pgCmdFichierRps->getId();
        //$reponseId = 86;
        // Vérif code demande
        if (count($diff = $repoPgTmpValidEdilabo->getDiffCodeDemande($demandeId, $reponseId)) > 0) {
            $this->addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: code demande", null, $diff);
        }
        
        // Vérif code prélèvement
        if (count($diff = $repoPgTmpValidEdilabo->getDiffCodePrelevementAdd($demandeId, $reponseId)) > 0) {
            $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: codes prélèvement RAI en trop", null, $diff);
        }
        
        if (count($diff = $repoPgTmpValidEdilabo->getDiffCodePrelevementMissing($demandeId, $reponseId)) > 0) {
            $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: code prélèvement RAI manquant", null, $diff);
        }
        
        // Vérif Date prélèvement, si hors période
        $codePrelevs = $repoPgTmpValidEdilabo->getCodePrelevement($demandeId, $reponseId);
        foreach($codePrelevs as $codePrelev) {
            // Vérification de la date de prélèvement
            $datePrelRps = $repoPgTmpValidEdilabo->getDatePrelevement($codePrelev["codePrelevement"], $demandeId, $reponseId);
            $datePrelRps = new \DateTime($datePrelRps["datePrel"]);
            
            $datePrelDmd = $repoPgTmpValidEdilabo->getDatePrelevement($codePrelev["codePrelevement"], $demandeId);
            $datePrelDmdMin = new \DateTime($datePrelDmd["datePrel"]);
            
            $delaiPrel = $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getDelaiPrel();
            //$delaiPrel = 7;
            if (is_null($delaiPrel) || $delaiPrel == 0) {
                $delaiPrel = 7;
            }
            
            $datePrelDmdMax = clone $datePrelDmdMin;
            $datePrelDmdMax->add(new \DateInterval('P'.$delaiPrel.'D'));
            
            if ($datePrelDmdMin > $datePrelRps || $datePrelRps > $datePrelDmdMax) {
                $this->addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Date Prelevement hors période", $codePrelev["codePrelevement"]);
            }
            
            // Vérif code intervenant
            if (count($diff = $repoPgTmpValidEdilabo->getDiffPreleveur($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                if ($this->existePresta($diff)) {
                    $this->addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Preleveur", $codePrelev["codePrelevement"], $diff);
                } else {
                    $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Preleveur", $codePrelev["codePrelevement"], $diff);
                }
            }
            
            if (count($diff = $repoPgTmpValidEdilabo->getDiffLabo($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                if ($this->existePresta($diff)) {
                    $this->addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Laboratoire",$codePrelev["codePrelevement"], $diff);
                } else {
                    $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Laboratoire",$codePrelev["codePrelevement"], $diff);
                }
            }
            
            // Vérif STQ : concordance STQ RAI (unique ou multiple) / DAI : stations rajoutées => Erreur
            if (count($diff = $repoPgTmpValidEdilabo->getDiffCodeStation($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Date Prelevement",$codePrelev["codePrelevement"], $diff);
            }
            
            // paramètres/unité : si unité changée => erreur
            $mesuresRps = $repoPgTmpValidEdilabo->getMesures($codePrelev["codePrelevement"], $demandeId, $reponseId);
            $mesuresDmd = $repoPgTmpValidEdilabo->getMesures($codePrelev["codePrelevement"], $demandeId);
            foreach($mesuresRps as $idx => $mesureRps) {
                if (($mesureRps['codeParametre'] == $mesuresDmd[$idx]['codeParametre']) 
                        && ($mesureRps['codeUnite'] != $mesuresDmd[$idx]['codeUnite'])) {
                    $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Unité changée", $codePrelev["codePrelevement"]);
                }
            }
            
            // paramètres/unité : rajout de paramètres => avertissement
            if (count($diff = $repoPgTmpValidEdilabo->getDiffCodeParametreAdd($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                //Avertissement
                $this->addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Rajout de paramètre",$codePrelev["codePrelevement"], $diff);
            }
            
            // paramètres/unité : paramètre manquant => erreur
            if (count($diff = $repoPgTmpValidEdilabo->getDiffCodeParametreMissing($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Paramètre manquant",$codePrelev["codePrelevement"], $diff);
            }
            
        }
    }
    
    protected function existePresta($codeIntervenants) {
        foreach($codeIntervenants as $codeIntervenant) {
            $repoPgRefCorresPresta = $this->emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
            $pgRefCorresPrestas = $repoPgRefCorresPresta->findByCodeSiret($codeIntervenant);
            if (count($pgRefCorresPrestas) == 0) {
                $pgRefCorresPrestas = $repoPgRefCorresPresta->findByCodeSandre($codeIntervenant);
                if (count($pgRefCorresPrestas) == 0) {
                    return false;
                }
            }
            return true;
        }
    }
    
    protected function addLog($typeErreur, $demandeId, $fichierRpsId, $message, $codePrelevement = null) {
        
        $pgLogValidEdilabo = new \Aeag\SqeBundle\Entity\PgLogValidEdilabo($demandeId, $fichierRpsId, $typeErreur, $message, $codePrelevement);
        
        $this->emSqe->persist($pgLogValidEdilabo);
        $this->emSqe->flush();
    }
    
//    protected function controleVraisemblance($output, $pgCmdFichierRps, $repoPgTmpValidEdilabo) {
//        $demandeId = $pgCmdFichierRps->getDemande()->getId();
//        $reponseId = $pgCmdFichierRps->getId();
//        $pgTmpValidEdilabos = $repoPgTmpValidEdilabo->findBy(array('fichierRpsId' => $reponseId, 'demandeId' => $demandeId));
//        
//        foreach($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
//            $mesure = $pgTmpValidEdilabo->getResM();
//            $codeRq = $pgTmpValidEdilabo->getCodeRqM();
//            
//            if ($codeRq == '' || !is_numeric($codeRq)) {
//                $output->writeln('----> '.$codeRq);
//                return false;
//            } else {
//                if ($codeRq == 0) {
//                
//                } else {
//
//                }
//            }
//            
//            if ($mesure == '' || !is_numeric($mesure)) {
//                $output->writeln('----> '.$pgTmpValidEdilabo->getId());
//                return false;
//            }
//            
//            if ($codeRq == '' || !is_numeric($codeRq)) {
//                $output->writeln('----> '.$codeRq);
//                return false;
//            }
//            
//            if ($codeRq == 0 && $mesure == "") {
//                //avertissement
//            }
//            
//            
//        }
//        return true;
//    }

}
