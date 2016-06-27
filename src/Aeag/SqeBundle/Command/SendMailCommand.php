<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendMailCommand extends AeagCommand {
    
    protected function configure() {
        $this
                ->setName('rai:send_mail')
                ->setDescription('Envoi des mails en phase R45')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {

        parent::execute($input, $output);
        
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Envoi mail : Début');
        
        $pgProgPhase = $this->repoPgProgPhases->findOneByCodePhase('R45');
        $pgCmdFichiersRps = $this->repoPgCmdFichiersRps->findBy(array('typeFichier' => 'RPS', 'phaseFichier' => $pgProgPhase, 'suppr' => 'N'));
        foreach($pgCmdFichiersRps as $pgCmdFichierRps) {
            $destinataires = array();
            $destinataires[] = $this->repoPgProgWebUsers->findOneByPrestataire($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getTitulaire());
            $destinataires[] = $this->repoPgProgWebUsers->findOneByProducteur($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getMarche()->getRespAdrCor());

            $objetMessage = "SQE - RAI : Fichier csv des données brutes disponible pour le lot " . $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getNomLot();
            $urlDb = $this->getContainer()->get('router')->generate('AeagSqeBundle_echangefichiers_reponses_telecharger', array("reponseId" => $pgCmdFichierRps->getId(), "typeFichier" => "DB"), UrlGeneratorInterface::ABSOLUTE_URL);
            $urlCr = $this->getContainer()->get('router')->generate('AeagSqeBundle_echangefichiers_reponses_telecharger', array("reponseId" => $pgCmdFichierRps->getId(), "typeFichier" => "CR"), UrlGeneratorInterface::ABSOLUTE_URL);
            $txtMessage = "Lot : " . $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getNomLot() . "<br/>";
            $txtMessage .= "Période : " . $pgCmdFichierRps->getDemande()->getPeriode()->getLabelPeriode() . "<br/>";
            $txtMessage .= 'Vous pouvez récupérer le fichier csv à l\'adresse suivante: <a href="' . $urlDb . '">' . $pgCmdFichierRps->getNomFichierDonneesBrutes() . '</a><br/>';
            $txtMessage .= 'Le fichier de compte rendu est, quand à lui, disponible ici: <a href="' . $urlCr . '">' . $pgCmdFichierRps->getNomFichierCompteRendu() . '</a>';
            foreach ($destinataires as $destinataire) {
                if (!is_null($destinataire)) {
                    $this->output->writeln($date->format('d/m/Y H:i:s') . '- Integration Données Brutes : Un email a été envoyé à ' . $destinataire->getMail() . ' pour la RAI '.$pgCmdFichierRps->getId());
                    /*$mailer = $this->getContainer()->get('mailer');
                    if (!$this->getContainer()->get('aeag_sqe.message')->createMail($this->em, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                        $this->_addLog('warning', $pgCmdFichierRps->getDemande()->getId(), $pgCmdFichierRps->getId(), "Erreur lors de l\'envoi de mail dans le process de verification des RAIs", null, $destinataire);
                    } else {
                        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Integration Données Brutes : Un email a été envoyé à ' . $destinataire->getMail() . ' pour la RAI '.$pgCmdFichierRps->getId());
                    }*/
                } else {
                    $this->output->writeln($date->format('d/m/Y H:i:s') . '- Integration Données Brutes : Le destinataire est null pour la RAI '.$pgCmdFichierRps->getId());
                }
            }
        }
        
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Envoi mail : Fin');
    }
}
