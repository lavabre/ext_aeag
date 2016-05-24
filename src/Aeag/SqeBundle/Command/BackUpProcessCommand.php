<?php

namespace Aeag\SqeBundle\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BackUpProcessCommand extends AeagCommand {
    
    protected function configure() {
        $this
                ->setName('rai:backup_process')
                ->setDescription('Relance des RAIs arretées en cours de traitement')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        parent::execute($input, $output);
        
        // Phase 210
        $this->_phaseR10();

        // Phase 226
        $this->_phaseRX6('R26');

        // Phase 236
        $this->_phaseRX6('R36');
    }

    protected function _phaseR10() {
        // Repérer les fichier réponses ayant une phase R10 (210)
        $pgProgPhase = $this->repoPgProgPhases->findOneByCodePhase('R10');
        $pgCmdFichiersRps = $this->repoPgCmdFichiersRps->findBy(array('typeFichier' => 'RPS', 'phaseFichier' => $pgProgPhase));
        $cptPhaseR10Fait = 0;
        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            $chemin = $this->getContainer()->getParameter('repertoire_echange');
            $pathBase = $this->getContainer()->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdFichierRps->getDemande(), $pgCmdFichierRps->getId());
            if ($pgCmdFichierRps->getNomFichier()) {
                if ($this->getContainer()->get('aeag_sqe.process_rai')->envoiFichierValidationFormat($this->emSqe, $pgCmdFichierRps, $pathBase . '/' . $pgCmdFichierRps->getNomFichier())) {
                    // Changement de la phase de la réponse 
                    $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R15');

                    // Envoi d'un mail
                    $objetMessage = "RAI " . $pgCmdFichierRps->getId() . " soumise et en cours de validation";
                    $txtMessage = "Votre RAI (id " . $pgCmdFichierRps->getId() . ") concernant la DAI " . $pgCmdFichierRps->getDemande()->getCodeDemandeCmd() . " a été soumise. Le fichier " . $pgCmdFichierRps->getNomFichier() . " est en cours de validation. "
                            . "Vous serez informé lorsque celle-ci sera validée. ";
                    $destinataire = $this->repoPgProgWebUsers->findOneByPrestataire($pgCmdFichierRps->getDemande()->getPrestataire());
                    $mailer = $this->getContainer()->get('mailer');
                    if (!$this->getContainer()->get('aeag_sqe.message')->createMail($this->em, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                        $this->_addLog('warning', $pgCmdFichierRps->getDemande()->getId(), $pgCmdFichierRps->getId(), "Erreur lors de l\'envoi de mail dans le process de verification des RAIs", null, $destinataire);
                    }
                    
                    $cptPhaseR10Fait++;
                }
            }
        }
        
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s').'- BackUp Process : '.$cptPhaseR10Fait." RAI(s) en phase R10 traitée(s)");
    }
    
    protected function _phaseRX6($phase) {
        $pgProgPhase = $this->repoPgProgPhases->findOneByCodePhase($phase);
        $pgCmdFichiersRps = $this->repoPgCmdFichiersRps->findBy(array('typeFichier' => 'RPS', 'phaseFichier' => $pgProgPhase));
        $this->output->writeln(count($pgCmdFichiersRps));
        $cptPhaseR26Fait = 0;
        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            // Vérifier le nombre de phase R26 déjà présente dans PgProgSuiviPhases
            $pgProgSuiviPhases = $this->repoPgProgSuiviPhases->findBy(array('objId'=> $pgCmdFichierRps->getId(), 'typeObjet' => 'RPS', 'phase' => $pgProgPhase));
            if (count($pgProgSuiviPhases) <= 4) {
                $pgProgSuiviPhases = $this->repoPgProgSuiviPhases->findOneBy(array('objId'=> $pgCmdFichierRps->getId(), 'typeObjet' => 'RPS', 'phase' => $pgProgPhase),array('datePhase' => 'DESC'));
                if (!is_null($pgProgSuiviPhases)) {
                    $datePhase = $pgProgSuiviPhases->getDatePhase();
                    $datePhase->add(new \DateInterval('P1D'));
                    $dateDuJour = new \DateTime();
                    // Date de la phase (suivi phase) + 24h < date du jour
                    if ($datePhase < $dateDuJour) {
                        $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R25');
                        $cptPhaseR26Fait++;
                    }
                }
            } else {
                // Envoi d'un mail aux admins
                $objetMessage = "RAI " . $pgCmdFichierRps->getId() . " est bloquée dans le process de validation";
                $txtMessage = "La RAI (id " . $pgCmdFichierRps->getId() . ") concernant la DAI " . $pgCmdFichierRps->getDemande()->getCodeDemandeCmd() . " est actuellement bloquée.";
                $txtMessage .= " Celle-ci est passée à la phase \"".$pgProgPhase->getLibellePhase()."\" au moins 4 fois sans réussir à terminer le traitement de cette RAI.";
                
                
                $mailer = $this->getContainer()->get('mailer');
                $destinataires = $this->repoPgProgWebUsers->findByTypeUser('ADMIN');
                foreach($destinataires as $destinataire) {
                    if (!$this->getContainer()->get('aeag_sqe.message')->createMail($this->em, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                        $this->_addLog('warning', $pgCmdFichierRps->getDemande()->getId(), $pgCmdFichierRps->getId(), "Erreur lors de l\'envoi de mail dans le process de verification des RAIs", null, $destinataire);
                    }
                }
            }
        }
        
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s').'- BackUp Process : '.$cptPhaseR26Fait." RAI(s) en phase ".$phase." traitée(s)");
        
    }

}
