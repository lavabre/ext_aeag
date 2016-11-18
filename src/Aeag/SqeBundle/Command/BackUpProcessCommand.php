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
        // Sandre
        $this->_phaseR10();
        
        // Phase 215
        // Cohérence RAI/DAI
        $this->_phaseR15();
        
        // Phase 216
        // Cohérence RAI/DAI
        $this->_phaseRX('R16', 'R15');

        // Phase 226
        // Cohérence RAI/DAI
        $this->_phaseRX('R26', 'R25');
        
        // Phase 236
        // Controle Vraisemblance
        $this->_phaseRX('R36', 'R25');
        
        // Phase 242
        // Intégration données brutes
        $this->_phaseRX('R42', 'R40');
    }

    protected function _phaseR10() {
        // Repérer les fichier réponses ayant une phase R10 (210)
        $pgProgPhase = $this->repoPgProgPhases->findOneByCodePhase('R10');
        $pgCmdFichiersRps = $this->repoPgCmdFichiersRps->findBy(array('typeFichier' => 'RPS', 'phaseFichier' => $pgProgPhase, 'suppr' => 'N'));
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
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- BackUp Process : ' . $cptPhaseR10Fait . " RAI(s) en phase R10 traitée(s)");
    }

    protected function _phaseR15() {
        $pgProgPhase = $this->repoPgProgPhases->findOneByCodePhase('R15');
        //$pgCmdFichiersRps = $this->repoPgCmdFichiersRps->findBy(array('typeFichier' => 'RPS', 'suppr'=> 'N', 'phaseFichier' => $pgProgPhase), array('id' => 'ASC'));
        //$pgCmdFichiersRps = $this->repoPgCmdFichiersRps->getReponsesHorsLacBackup($pgProgPhase);
        $pgCmdFichiersRps = $this->repoPgCmdFichiersRps->getReponsesBackup($pgProgPhase);
        $cptPhaseR15Fait = 0;
        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            $chemin = $this->getContainer()->getParameter('repertoire_echange');
            $pathBase = $this->getContainer()->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdFichierRps->getDemande(), $pgCmdFichierRps->getId());
            if ($this->getContainer()->get('aeag_sqe.process_rai')->envoiFichierValidationFormat($this->emSqe, $pgCmdFichierRps, $pathBase . '/' . $pgCmdFichierRps->getNomFichier())) {
                // Changement de la phase de la réponse 
                $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R15');
                $cptPhaseR15Fait++;
            }
        }

        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- BackUp Process : ' . $cptPhaseR15Fait . " RAI(s) en phase R15 traitée(s)");
    }

    protected function _phaseRX($phaseOrig, $phaseCible) {
        $pgProgPhase = $this->repoPgProgPhases->findOneByCodePhase($phaseOrig);
        $pgCmdFichiersRps = $this->repoPgCmdFichiersRps->findBy(array('typeFichier' => 'RPS', 'phaseFichier' => $pgProgPhase, 'suppr' => 'N'));
        $cptPhaseRXFait = 0;
        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            // Vérifier le nombre de phase R26 déjà présente dans PgProgSuiviPhases
            $pgProgSuiviPhases = $this->repoPgProgSuiviPhases->findBy(array('objId' => $pgCmdFichierRps->getId(), 'typeObjet' => 'RPS', 'phase' => $pgProgPhase));
            if (count($pgProgSuiviPhases) <= 4) {
                $pgProgSuiviPhases = $this->repoPgProgSuiviPhases->findOneBy(array('objId' => $pgCmdFichierRps->getId(), 'typeObjet' => 'RPS', 'phase' => $pgProgPhase), array('datePhase' => 'DESC'));
                if (!is_null($pgProgSuiviPhases)) {
                    $datePhase = $pgProgSuiviPhases->getDatePhase();
                    $datePhase->add(new \DateInterval('P1D'));
                    $dateDuJour = new \DateTime();
                    // Date de la phase (suivi phase) + 24h < date du jour
                    if ($datePhase < $dateDuJour) {
                        $this->_updatePhaseFichierRps($pgCmdFichierRps, $phaseCible);
                        $cptPhaseRXFait++;
                    }
                }
            } else {
                // Envoi d'un mail aux admins
                /* $objetMessage = "RAI " . $pgCmdFichierRps->getId() . " est bloquée dans le process de validation";
                  $txtMessage = "La RAI (id " . $pgCmdFichierRps->getId() . ") concernant la DAI " . $pgCmdFichierRps->getDemande()->getCodeDemandeCmd() . " est actuellement bloquée.";
                  $txtMessage .= " Celle-ci est passée à la phase \"".$pgProgPhase->getLibellePhase()."\" au moins 4 fois sans réussir à terminer le traitement de cette RAI.";


                  $mailer = $this->getContainer()->get('mailer');
                  $destinataires = $this->repoPgProgWebUsers->findByTypeUser('ADMIN');
                  foreach($destinataires as $destinataire) {
                  if (!$this->getContainer()->get('aeag_sqe.message')->createMail($this->em, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                  $this->_addLog('warning', $pgCmdFichierRps->getDemande()->getId(), $pgCmdFichierRps->getId(), "Erreur lors de l\'envoi de mail dans le process de verification des RAIs", null, $destinataire);
                  }
                  } */
            }
        }

        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- BackUp Process : ' . $cptPhaseRXFait . " RAI(s) en phase " . $phaseOrig . " traitée(s)");
    }

}
