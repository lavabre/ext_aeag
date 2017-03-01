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
                ->addArgument('pgCmdFichierRps_id', InputArgument::REQUIRED, 'id du fichier rps')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        parent::execute($input, $output);

        // Récupération des parametres
        // fichier rps
        $pgCmdFichierRps_id = $this->input->getArgument('pgCmdFichierRps_id');

        $pgCmdFichierRps = $this->repoPgCmdFichiersRps->findOneById($pgCmdFichierRps_id);
        $cptRaisTraitesOk = 0;
        $cptRaisTraitesNok = 0;

        if (!is_null($pgCmdFichierRps)) {

            // On les passe la reponse en phase R26
            $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R26');

            $date = new \DateTime();
            $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process depotHydrobio : Le traitement de la RAI ' . $pgCmdFichierRps->getId() . ' commence');

            // TODO On vérifie que l'on insère pas deux fois la même RAI
            $pgCmdDemande = $pgCmdFichierRps->getDemande();
            $chemin = $this->getContainer()->getParameter('repertoire_depotHydrobio');
            $pathBase = $this->getContainer()->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdDemande, $pgCmdFichierRps->getId());
            $excelObj = $this->excelObj;
            $tabFichiers = $this->getContainer()->get('aeag_sqe.depotHydrobio')->extraireFichier($pgCmdFichierRps->getDemande()->getId(), $this->emSqe, $pgCmdFichierRps, $pathBase, $pgCmdFichierRps->getNomFichier(), $excelObj);


            $erreur = false;
            for ($i = 0; $i < count($tabFichiers); $i++) {
                for ($j = 0; $j < count($tabFichiers[$i]['feuillet']); $j++) {
                    $erreur = $tabFichiers[$i]['feuillet'][$j]['erreur'];
                }
            }

            if (!$erreur) {
                $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R40');
            } else {
                $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R80');
            }

            $this->_updatePhaseDemande($pgCmdDemande);
        }

        // Envoi mail
        $objetMessage = "SQE - RAI : Fichier " . $pgCmdFichierRps->getNomFichier() . " - Récapitulatif";
        //$url = $this->getContainer()->get('router')->generate('AeagSqeBundle_depotHydrobio_demandes', array("lotanId" => $pgCmdFichierRps->getDemande()->getLotan()->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
        $url = $this->getContainer()->get('router')->generate('AeagSqeBundle_depotHydrobio_reponses_telecharger', array("reponseId" => $pgCmdFichierRps->getId(), "typeFichier" => "CR"), UrlGeneratorInterface::ABSOLUTE_URL);
        $txtMessage = "Lot : " . $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getNomLot() . "<br/>";
        $txtMessage .= "Période : " . $pgCmdFichierRps->getDemande()->getPeriode()->getLabelPeriode() . "<br/>";
        $txtMessage .= 'Le traitement de la RAI ' . $pgCmdFichierRps->getNomFichier() . ' est maintenant terminé <br/>';
        $txtMessage .= "L'état final est le suivant : <strong>" . $pgCmdFichierRps->getPhaseFichier()->getLibellePhase() . "</strong><br/>";
        $txtMessage .= 'Vous pouvez lire le récapitulatif dans le fichier disponible à l\'adresse suivante : <a href="' . $url . '">' . $pgCmdFichierRps->getNomFichierCompteRendu() . '</a>';
        $destinataire = $this->repoPgProgWebUsers->findOneByPrestataire($pgCmdFichierRps->getDemande()->getPrestataire());
        $mailer = $this->getContainer()->get('mailer');
        if (!$this->getContainer()->get('aeag_sqe.message')->createMail($this->em, $mailer, $txtMessage, $destinataire, $objetMessage)) {
            $this->_addLog('warning', $pgCmdFichierRps->getDemande()->getId(), $pgCmdFichierRps->getId(), "Erreur lors de l\'envoi de mail dans le process de verification des RAIs", null, $destinataire);
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
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process depotHydrobio : Le traitement de la RAI ' . $pgCmdFichierRps->getId() . ' est terminé');
        //}
        $date = new \DateTime();
        $cptRaisTraitesTot = $cptRaisTraitesOk + $cptRaisTraitesNok;
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process depotHydrobio : ' . $cptRaisTraitesTot . " RAI(s) traitée(s), " . $cptRaisTraitesOk . " OK, " . $cptRaisTraitesNok . " NOK");
    }

}
