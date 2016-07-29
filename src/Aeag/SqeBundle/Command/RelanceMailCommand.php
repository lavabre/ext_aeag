<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RelanceMailCommand extends AeagCommand {

    protected function configure() {
        $this
                ->setName('rai:relance_mail')
                ->setDescription('Relance des mails')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        parent::execute($input, $output);

        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Relance mail : Début');

        // ANALYSE
        // Envoi des mails a J-7
        //$this->sendEmailJ7();
        
        // Envoi des mails a J+1
        //$this->sendEmailJ1();
        
        // HYDROBIO
        // TODO A terminer
        //$this->sendEmailHbP();
        
        // TODO A terminer
        //$this->sendEmailHbF();
        
        $this->updateHbP();
        
        // TODO A terminer
        //$this->updateHbF();

        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Relance mail : Fin');
    }
    
    protected function sendEmailJ7() {
        $pgCmdDemandes = $this->repoPgCmdDemande->getPgCmdDemandeForRelance7JAvt();
        $this->output->writeln(count($pgCmdDemandes));
        foreach ($pgCmdDemandes as $pgCmdDemande) {
            $this->output->writeln($pgCmdDemande->getPeriode()->getDateDeb());
            $destinataires = array();
            $destinataires[] = $this->repoPgProgWebUsers->findOneByPrestataire($pgCmdDemande->getLotan()->getLot()->getTitulaire());

            $objetMessage = "Relance SQE - RAI : Dépot de fichier en attente " . $pgCmdDemande->getLotan()->getLot()->getNomLot();
            $txtMessage = "Lot : " . $pgCmdDemande->getLotan()->getLot()->getNomLot() . "<br/>";
            $txtMessage .= "Période : " . $pgCmdDemande->getPeriode()->getLabelPeriode() . "<br/>";
            $txtMessage .= "Vous êtes censé déposer les résultats de la demande " . $pgCmdDemande->getId() . " du lot " . $pgCmdDemande->getLotan()->getLot()->getNomLot() . " avant <br/>";
            foreach ($destinataires as $destinataire) {
                $this->sendEmail($pgCmdDemande, $destinataire, $txtMessage, $objetMessage);
            }
        }
    }
    
    protected function sendEmailJ1() {
        $pgCmdDemandes = $this->repoPgCmdDemande->getPgCmdDemandeForRelance1JAprs();
        $this->output->writeln(count($pgCmdDemandes));
        foreach ($pgCmdDemandes as $pgCmdDemande) {
            $this->output->writeln($pgCmdDemande->getPeriode()->getDateDeb());
            $destinataires = array();
            $destinataires[] = $this->repoPgProgWebUsers->findOneByPrestataire($pgCmdDemande->getLotan()->getLot()->getTitulaire());

            $objetMessage = "Relance SQE - RAI : Dépot de fichier non effectué " . $pgCmdDemande->getLotan()->getLot()->getNomLot();
            $txtMessage = "Lot : " . $pgCmdDemande->getLotan()->getLot()->getNomLot() . "<br/>";
            $txtMessage .= "Période : " . $pgCmdDemande->getPeriode()->getLabelPeriode() . "<br/>";
            $txtMessage .= "Les résultats à la demande ".$pgCmdDemande->getId()." du lot ".$pgCmdDemande->getLotan()->getLot()->getNomLot()." n'ont pas été déposé. Vous encourez des pénalités. <br/>";
            foreach ($destinataires as $destinataire) {
                $this->sendEmail($pgCmdDemande, $destinataire, $txtMessage, $objetMessage);
            }
        }
    }

    protected function sendEmail($pgCmdDemande, $destinataire, $txtMessage, $objetMessage) {
        if (!is_null($destinataire)) {
            $mailer = $this->getContainer()->get('mailer');
            if (!$this->getContainer()->get('aeag_sqe.message')->createMail($this->em, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                $this->_addLog('warning', $pgCmdDemande->getId(), null, "Erreur lors de l\'envoi de mail dans le process de verification des RAIs", null, $destinataire);
            } else {
                $date = new \DateTime();
                $this->output->writeln($date->format('d/m/Y H:i:s') . '- Relance Mail : Un email a été envoyé à ' . $destinataire->getMail() . ' pour la DAI ' . $pgCmdDemande->getId());
            }
        } else {
            $date = new \DateTime();
            $this->output->writeln($date->format('d/m/Y H:i:s') . '- Relance Mail : Le destinataire est null pour la DAI ' . $pgCmdDemande->getId());
        }
    }
    
    protected function sendEmailHbP() {
        $pgCmdSuiviPrels = $this->repoPgCmdSuiviPrel->getSuiviPrelP(15);
        foreach($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
            $destinataires = array();
            $destinataires[] = $this->repoPgProgWebUsers->findOneByPrestataire($pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getLot()->getTitulaire());

            $objetMessage = "Relance SQE - RAI : Dépot de fichier non effectué " . $pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getLot()->getNomLot();
            $txtMessage = "Lot : " . $pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getLot()->getNomLot() . "<br/>";
            $txtMessage .= "Période : " . $pgCmdSuiviPrel->getPrelev()->getDemande()->getPeriode()->getLabelPeriode() . "<br/>";
            $txtMessage .= "Les résultats à la demande ".$pgCmdSuiviPrel->getPrelev()->getDemande()->getId()." du lot ".$pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getLot()->getNomLot()." n'ont pas été déposé. Vous encourez des pénalités. <br/>";
            foreach ($destinataires as $destinataire) {
                $this->sendEmail($pgCmdSuiviPrel->getPrelev()->getDemande(), $destinataire, $txtMessage, $objetMessage);
            }
        }
    }
        
        
    protected function sendEmailHbF() {
        // TODO Modifier la requete
        $pgCmdDemandes = $this->repoPgCmdDemande->getPgCmdDemandeForRelance1JAprs();
        $this->output->writeln(count($pgCmdDemandes));
        foreach ($pgCmdDemandes as $pgCmdDemande) {
            $this->output->writeln($pgCmdDemande->getPeriode()->getDateDeb());
            $destinataires = array();
            $destinataires[] = $this->repoPgProgWebUsers->findOneByPrestataire($pgCmdDemande->getLotan()->getLot()->getTitulaire());

            $objetMessage = "Relance SQE - RAI : Dépot de fichier non effectué " . $pgCmdDemande->getLotan()->getLot()->getNomLot();
            $txtMessage = "Lot : " . $pgCmdDemande->getLotan()->getLot()->getNomLot() . "<br/>";
            $txtMessage .= "Période : " . $pgCmdDemande->getPeriode()->getLabelPeriode() . "<br/>";
            $txtMessage .= "Les résultats à la demande ".$pgCmdDemande->getId()." du lot ".$pgCmdDemande->getLotan()->getLot()->getNomLot()." n'ont pas été déposé. Vous encourez des pénalités. <br/>";
            foreach ($destinataires as $destinataire) {
                $this->sendEmail($pgCmdDemande, $destinataire, $txtMessage, $objetMessage);
            }
        }
    }

    protected function updateHbP() {
        $pgCmdSuiviPrels = $this->repoPgCmdSuiviPrel->getSuiviPrelP(15);
        foreach($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
            $this->output->writeln($pgCmdSuiviPrel->getDatePrel());
            $pgCmdSuiviPrel->setValidation('A');
            $this->emSqe->persist($pgCmdSuiviPrel);
        }
        $this->emSqe->flush();
    }

    protected function updateHbF() {
        // TODO Modifier la requete
        $pgCmdSuiviPrels = $this->repoPgCmdSuiviPrel;
        foreach($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
            $pgCmdSuiviPrel->setValidation('A');
            $this->emSqe->persist($pgCmdSuiviPrel);
        }
        $this->emSqe->flush();
    }

}
