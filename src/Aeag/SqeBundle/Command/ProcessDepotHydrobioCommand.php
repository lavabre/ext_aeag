<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProcessDepotHydrobioCommand extends AeagCommand {

    private $phase82atteinte = false;

    protected function configure() {
        $this
                ->setName('rai:depotHydrobio')
                ->setDescription('Dépot hydrobio')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        parent::execute($input, $output);

        // On récupère les RAIs dont les phases sont en R15
        $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase('R15');
        //$pgCmdFichiersRps = $this->repoPgCmdFichiersRps->findBy(array('phaseFichier' => $pgProgPhases, 'typeFichier' => 'RPS', 'suppr' => 'N'), array('id' => 'ASC'));
        $pgCmdFichiersRps = $this->repoPgCmdFichiersRps->findBy(array('phaseFichier' => $pgProgPhases, 'typeFichier' => 'DHY', 'suppr' => 'N'), array('id' => 'ASC'));
        $cptRaisTraitesOk = 0;
        $cptRaisTraitesNok = 0;

        if (!is_null($pgCmdFichiersRps)) {


            if (count($pgCmdFichiersRps) > 0) {
                $date = new \DateTime();
                $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI DepotHydrobio : ' . count($pgCmdFichiersRps) . " RAI(s) vont être traitées ");
            }


            foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {

                $date = new \DateTime();
                $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI DepotHydrobio : Le traitement du depot Hydrobio ' . $pgCmdFichierRps->getId() . ' commence');
                $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R16');

                // TODO On vérifie que l'on insère pas deux fois la même RAI
                $pgCmdDemande = $pgCmdFichierRps->getDemande();
                $chemin = $this->getContainer()->getParameter('repertoire_depotHydrobio');
                $pathBase = $this->getContainer()->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdDemande, $pgCmdFichierRps->getId());
                //    $this->output->writeln($date->format('d/m/Y H:i:s') . '- chemin : ' . $pathBase);
                $excelObj = $this->excelObj;
                $tabFichiers = $this->getContainer()->get('aeag_sqe.depotHydrobio')->extraireFichier($pgCmdFichierRps->getDemande()->getId(), $this->emSqe, $pgCmdFichierRps, $pathBase, $pgCmdFichierRps->getNomFichier(), $excelObj);


                $erreur = false;
                for ($i = 0; $i < count($tabFichiers); $i++) {
                    if ($tabFichiers[$i]['erreur']) {
                        $erreur = true;
                        break;
                    }
                }

                if (!$erreur) {
                    $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R40');
                } else {
                    $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R80');
                }

                $this->_updatePhaseDemande($pgCmdDemande);


                // Envoi mail
                $objetMessage = "SQE - RAI DepotHydrobio  : Fichier " . $pgCmdFichierRps->getNomFichier() . " - Récapitulatif";
                if ($this->getEnv() !== 'prod') {
                    $objetMessage .= " - " . $this->getEnv();
                }
                //$url = $this->getContainer()->get('router')->generate('AeagSqeBundle_depotHydrobio_demandes', array("lotanId" => $pgCmdFichierRps->getDemande()->getLotan()->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
                $url = $this->getContainer()->get('router')->generate('AeagSqeBundle_depotHydrobio_reponses_telecharger', array("reponseId" => $pgCmdFichierRps->getId(), "typeFichier" => "CR"), UrlGeneratorInterface::ABSOLUTE_URL);
                $txtMessage = "Lot : " . $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getNomLot() . "<br/>";
                $txtMessage .= "Période : " . $pgCmdFichierRps->getDemande()->getPeriode()->getLabelPeriode() . "<br/>";
                $txtMessage .= 'Le traitement de la RAI ' . $pgCmdFichierRps->getNomFichier() . ' est maintenant terminé <br/>';
                $txtMessage .= "L'état final est le suivant : <strong>" . $pgCmdFichierRps->getPhaseFichier()->getLibellePhase() . "</strong><br/>";
                $txtMessage .= 'Vous pouvez lire le récapitulatif dans le fichier disponible à l\'adresse suivante : <a href="' . $url . '">' . $pgCmdFichierRps->getNomFichierCompteRendu() . '</a>';
                $destinataires = $this->repoPgProgWebUsers->findByPrestataire($pgCmdFichierRps->getDemande()->getPrestataire());
                foreach ($destinataires as $destinataire) {
                    $mailer = $this->getContainer()->get('mailer');
                    if (!$this->getContainer()->get('aeag_sqe.message')->createMail($this->em, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                        $this->_addLog('warning', $pgCmdFichierRps->getDemande()->getId(), $pgCmdFichierRps->getId(), "Erreur lors de l\'envoi de mail dans le process de dépot hydrobio", null, $destinataire);
                    }
                }

                // Insertion données brutes
                if (($pgCmdFichierRps->getPhaseFichier()->getCodePhase() == 'R41') || ($pgCmdFichierRps->getPhaseFichier()->getCodePhase() == 'R40')) {
                    $cptRaisTraitesOk++;
                } else {
                    // Vider les tables
                    $this->_cleanTmpTable($pgCmdFichierRps);
                    $cptRaisTraitesNok++;
                }

                $date = new \DateTime();
                $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI DepotHydrobio  : Le traitement du depot hydrobio ' . $pgCmdFichierRps->getId() . ' est terminé');
            }
        }
        $date = new \DateTime();
        $cptRaisTraitesTot = $cptRaisTraitesOk + $cptRaisTraitesNok;
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI DepotHydrobio  : ' . $cptRaisTraitesTot . " Depot hydrobio traitée(s), " . $cptRaisTraitesOk . " OK, " . $cptRaisTraitesNok . " NOK");
    }

}
