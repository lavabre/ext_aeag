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
        $this->sendEmailJ7();
        
        // Envoi des mails a J+1
        $this->sendEmailJ1();
        
        // HYDROBIO
        $this->sendEmailHbP();
        
        $this->sendEmailHbF();
        
        // Mise a jour auto
        $this->updateHbP();
        
        $this->updateHbF();

        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Relance mail : Fin');
    }
    
    protected function sendEmailJ7() {
        $pgCmdDemandes = $this->repoPgCmdDemande->getPgCmdDemandeForRelance7JAvt();
        $this->output->writeln(count($pgCmdDemandes));
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .'Début sendEmailJ7');
        foreach ($pgCmdDemandes as $pgCmdDemande) {
            $this->output->writeln($pgCmdDemande->getPeriode()->getDateDeb());
            $destinataires = array();
            $destinataires[] = $this->repoPgProgWebUsers->findOneByPrestataire($pgCmdDemande->getLotan()->getLot()->getTitulaire());

            $objetMessage = "Relance SQE - RAI : Dépot de fichier en attente " . $pgCmdDemande->getLotan()->getLot()->getNomLot();
            $txtMessage = "Lot : " . $pgCmdDemande->getLotan()->getLot()->getNomLot() . "<br/>";
            $txtMessage .= "Période : " . $pgCmdDemande->getPeriode()->getLabelPeriode() . "<br/>";
            $txtMessage .= "Vous êtes censé déposer les résultats de la demande " . $pgCmdDemande->getId() . " du lot " . $pgCmdDemande->getLotan()->getLot()->getNomLot() . " avant <br/>";
            foreach ($destinataires as $destinataire) {
                $this->sendEmail($destinataire, $txtMessage, $objetMessage);
            }
        }
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .'Fin sendEmailJ7');
    }
    
    protected function sendEmailJ1() {
        $pgCmdDemandes = $this->repoPgCmdDemande->getPgCmdDemandeForRelance1JAprs();

        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .'Début sendEmailJ1');
        foreach ($pgCmdDemandes as $pgCmdDemande) {
            $this->output->writeln($pgCmdDemande->getPeriode()->getDateDeb());
            $destinataires = array();
            $destinataires[] = $this->repoPgProgWebUsers->findOneByPrestataire($pgCmdDemande->getLotan()->getLot()->getTitulaire());

            $objetMessage = "Relance SQE - RAI : Dépot de fichier non effectué " . $pgCmdDemande->getLotan()->getLot()->getNomLot();
            $txtMessage = "Lot : " . $pgCmdDemande->getLotan()->getLot()->getNomLot() . "<br/>";
            $txtMessage .= "Période : " . $pgCmdDemande->getPeriode()->getLabelPeriode() . "<br/>";
            $txtMessage .= "Les résultats à la demande ".$pgCmdDemande->getId()." du lot ".$pgCmdDemande->getLotan()->getLot()->getNomLot()." n'ont pas été déposé. Vous encourez des pénalités. <br/>";
            foreach ($destinataires as $destinataire) {
                $this->sendEmail($destinataire, $txtMessage, $objetMessage);
            }
        }
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .'Fin sendEmailJ1');
    }
    
    protected function sendEmailHbP() {
        $pgProgLots =$this->repoPgCmdSuiviPrel->getLotPByDays(15);
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .'Début sendEmailHbP');
        foreach($pgProgLots as $pgProgLot) {
            $pgCmdSuiviPrels = $this->repoPgCmdSuiviPrel->getSuiviPrelPByDaysAndLot(15, $pgProgLot);
            
            $destinataires = array();
            $destinataires[] = $this->repoPgProgWebUsers->findOneByPrestataire($pgProgLot->getTitulaire());
            
            $listeStationLibs = '<ul>';
            $listeStationCodes = array();
            foreach($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
                $listeStationLibs .= '<li>'.$pgCmdSuiviPrel->getPrelev()->getStation()->getLibelle().'</li>';
                $listeStationCodes[] = $pgCmdSuiviPrel->getPrelev()->getStation()->getCode();
            }
            $listeStationLibs .= '</ul>';

            $objetMessage = "Relance SQE : Lot ".$pgProgLot->getNomLot()." - Stations non réalisée à ce jour";
            $txtMessage = "Lot : " . $pgProgLot->getNomLot() . "<br/>";
            $txtMessage .= "Stations : <br/>" . $listeStationLibs . "<br/>";
            $txtMessage .= "<br/>Vous n'avez pas renseigné le prélèvement Effectué de la station ni déposé les fichiers associés.<br/>";
            foreach ($destinataires as $destinataire) {
                $date = new \DateTime();
                $this->output->writeln($date->format('d/m/Y H:i:s') .'Send Mail Suivi - Lot '.$pgProgLot->getId().' - Stations '.implode(', ', $listeStationCodes));
                $this->sendEmail($destinataire, $txtMessage, $objetMessage);
            }
        }
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .'Fin sendEmailHbP');
    }
        
    protected function sendEmailHbF() {
        $pgProgLots = $this->repoPgCmdSuiviPrel->getLotFWithoutRpsByDays(15);
        
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .'Début sendEmailHbF');
        foreach($pgProgLots as $pgProgLot) {
            $pgCmdSuiviPrels = $this->repoPgCmdSuiviPrel->getSuiviPrelFWithoutRpsByDaysAndLot(15, $pgProgLot);
            
            $destinataires = array();
            $listeStationCodes = array();
            $listePrelevDates = array();
            
            $destinataires[] = $this->repoPgProgWebUsers->findOneByPrestataire($pgProgLot->getTitulaire());
            $listeStationLibs = '<ul>';
            foreach($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
                $listeStationLibs .= '<li>'.$pgCmdSuiviPrel->getPrelev()->getStation()->getCode().' - '.$pgCmdSuiviPrel->getPrelev()->getStation()->getLibelle().' - '.$pgCmdSuiviPrel->getPrelev()->getDemande()->getPeriode()->getLabelPeriode().'</li>';
                $listeStationCodes[] = $pgCmdSuiviPrel->getPrelev()->getStation()->getCode();
                $listePrelevDates[] = $pgCmdSuiviPrel->getPrelev()->getDemande()->getPeriode()->getLabelPeriode();
            }
            $listeStationLibs .= '</ul>';

            $objetMessage = "Relance SQE : Lot ".$pgProgLot->getNomLot()." - Absence de documents de terrain ";
            $txtMessage = "Lot : " . $pgProgLot->getNomLot() . "<br/>";
            $txtMessage .= "Stations : </br>" . $listeStationLibs . "<br/>";
            $txtMessage .= "Vous n'avez pas déposé les fichiers associés pour ces stations.<br/>";
            foreach ($destinataires as $destinataire) {
                $date = new \DateTime();
                $this->output->writeln($date->format('d/m/Y H:i:s') .'Send Mail Suivi - Lot '.$pgProgLot->getId().' - Stations '.implode(', ', $listeStationCodes));
                $this->sendEmail($destinataire, $txtMessage, $objetMessage);
            }
        }
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .'Fin sendEmailHbF');
    }
    
    protected function sendEmail($destinataire, $txtMessage, $objetMessage) {
        if (!is_null($destinataire)) {
            $mailer = $this->getContainer()->get('mailer');
            if (!$this->getContainer()->get('aeag_sqe.message')->createMail($this->em, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                $this->_addLog('warning', null, null, "Erreur lors de l\'envoi de mail dans le process de verification des RAIs", null, $destinataire);
            } else {
                $date = new \DateTime();
                $this->output->writeln($date->format('d/m/Y H:i:s') . '- Relance Mail : Un email a été envoyé à ' . $destinataire->getMail());
            }
        } else {
            $date = new \DateTime();
            $this->output->writeln($date->format('d/m/Y H:i:s') . '- Relance Mail : Le destinataire est null');
        }
    }

    protected function updateHbP() {
        $pgCmdSuiviPrels = $this->repoPgCmdSuiviPrel->getSuiviPrelPByDays(15);
        foreach($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
            $date = new \DateTime();
            $this->output->writeln($date->format('d/m/Y H:i:s') .'- '. $pgCmdSuiviPrel->getId().' - HBP - Mise à jour validation a A');
            $pgCmdSuiviPrel->setValidation('A');
            $pgCmdSuiviPrel->setValidAuto('O');
            $this->emSqe->persist($pgCmdSuiviPrel);
        }
        $this->emSqe->flush();
    }

    protected function updateHbF() {
        $pgCmdSuiviPrels = $this->repoPgCmdSuiviPrel->getSuiviPrelFWithRpsByDays(21);
        foreach($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
            $date = new \DateTime();
            $this->output->writeln($date->format('d/m/Y H:i:s') .'- '. $pgCmdSuiviPrel->getId().' - HBF - Mise à jour validation a A');
            $pgCmdSuiviPrel->setValidation('A');
            $pgCmdSuiviPrel->setValidAuto('O');
            $this->emSqe->persist($pgCmdSuiviPrel);
        }
        $this->emSqe->flush();
    }

}
