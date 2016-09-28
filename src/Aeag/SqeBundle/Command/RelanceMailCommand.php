<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RelanceMailCommand extends AeagCommand {
    
    private $logs = array();

    protected function configure() {
        $this
                ->setName('rai:relance_mail')
                ->setDescription('Relance des mails')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        parent::execute($input, $output);

        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . ' - Relance mail : Début');

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
        
        $this->sendEmailRecap();

        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . ' - Relance mail : Fin');
    }
    
    protected function sendEmailJ7() {
        $pgCmdDemandes = $this->repoPgCmdDemande->getPgCmdDemandeForRelance7JAvt();
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .' - Début relance analyses (code_milieu like ‘%PC’) à J-7');
        foreach ($pgCmdDemandes as $pgCmdDemande) {
            $this->output->writeln($pgCmdDemande->getPeriode()->getDateDeb());
            $dateLimite = $pgCmdDemande->getPeriode()->getDateDeb();
            $delaiLot = $pgCmdDemande->getLotan()->getLot()->getDelaiLot();
            if (is_null($delaiLot)) {
                $delaiLot = 30;
            }
            $delaiPrel = $pgCmdDemande->getLotan()->getLot()->getDelaiPrel();
            if (is_null($delaiPrel)) {
                $delaiPrel = 7;
            }
            $interval = $delaiLot + $delaiPrel;
            $dateLimite->add(new \DateInterval('P'.$interval.'D'));
            $destinataires = array();
            if (!is_null($pgCmdDemande->getLotan()->getLot()->getTitulaire())) {
                $prestataires = $this->repoPgProgWebUsers->findByPrestataire($pgCmdDemande->getLotan()->getLot()->getTitulaire());
                foreach($prestataires as $prestataire) {
                    $destinataires[$prestataire->getId()] = $prestataire;
                }
            }
            if (!is_null($pgCmdDemande->getPrestataire())) {
                $prestatairesDmd = $this->repoPgProgWebUsers->findByPrestataire($pgCmdDemande->getPrestataire());
                foreach($prestatairesDmd as $prestataireDmd) {
                    $destinataires[$prestataireDmd->getId()] = $prestataireDmd;
                }
            }
            $objetMessage = "Relance SQE - RAI : Dépot de fichier en attente " . $pgCmdDemande->getLotan()->getLot()->getNomLot();
            $txtMessage = "Lot : " . $pgCmdDemande->getLotan()->getLot()->getNomLot() . "<br/>";
            $txtMessage .= "Période : " . $pgCmdDemande->getPeriode()->getLabelPeriode() . "<br/>";
            $txtMessage .= "Vous êtes censé déposer les résultats de la demande " . $pgCmdDemande->getId() . " du lot " . $pgCmdDemande->getLotan()->getLot()->getNomLot() . " avant le ".$dateLimite->format('d-m-Y')."<br/>";
            $txtMessage .= "Il vous reste 7 jours pour déposer vos résultats, cf. Article 6.2 du CCTP. <br/>";
            foreach ($destinataires as $destinataire) {
                if (!is_null($destinataire)) {
                    $this->sendEmail($destinataire, $txtMessage, $objetMessage);
                }
            }
        }
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .' - Fin relance analyses (code_milieu like ‘%PC’) à J-7');
    }
    
    protected function sendEmailJ1() {
        $pgCmdDemandes = $this->repoPgCmdDemande->getPgCmdDemandeForRelance1JAprs();

        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .' - Début relance analyses (code_milieu like ‘%PC’) à J+1');
        foreach ($pgCmdDemandes as $pgCmdDemande) {
            $this->output->writeln($pgCmdDemande->getPeriode()->getDateDeb());
            $destinataires = array();
            if (!is_null($pgCmdDemande->getLotan()->getLot()->getTitulaire())) {
                $prestataires = $this->repoPgProgWebUsers->findByPrestataire($pgCmdDemande->getLotan()->getLot()->getTitulaire());
                foreach($prestataires as $prestataire) {
                    $destinataires[$prestataire->getId()] = $prestataire;
                }
            }
            
            if (!is_null($pgCmdDemande->getPrestataire())) {
                $prestatairesDmd = $this->repoPgProgWebUsers->findByPrestataire($pgCmdDemande->getPrestataire());
                foreach($prestatairesDmd as $prestataireDmd) {
                    $destinataires[$prestataireDmd->getId()] = $prestataireDmd;
                }
            }

            $objetMessage = "Relance SQE - RAI : Dépot de fichier non effectué " . $pgCmdDemande->getLotan()->getLot()->getNomLot();
            $txtMessage = "Lot : " . $pgCmdDemande->getLotan()->getLot()->getNomLot() . "<br/>";
            $txtMessage .= "Période : " . $pgCmdDemande->getPeriode()->getLabelPeriode() . "<br/>";
            $txtMessage .= "Les résultats à la demande ".$pgCmdDemande->getId()." du lot ".$pgCmdDemande->getLotan()->getLot()->getNomLot()." n'ont pas été déposé. Vous encourez des pénalités. (Retard dans la remise des résultats, pénalités encourues cf. CCAP 17.1) <br/>";
            foreach ($destinataires as $destinataire) {
                if (!is_null($destinataire)) {
                    $this->sendEmail($destinataire, $txtMessage, $objetMessage);
                }
            }
        }
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .' - Fin relance analyses (code_milieu like ‘%PC’) à J+1');
    }
    
    protected function sendEmailHbP() {
        $pgProgLots =$this->repoPgCmdSuiviPrel->getLotPByDays(15);
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .' - Début relance suivis hydrobio (code_milieu like ‘%HB’) P');
        foreach($pgProgLots as $pgProgLot) {
            $pgCmdSuiviPrels = $this->repoPgCmdSuiviPrel->getSuiviPrelPByDaysAndLot(15, $pgProgLot);
            
            $destinataires = array();
            if (!is_null($pgProgLot->getTitulaire())) {
                $prestataires = $this->repoPgProgWebUsers->findByPrestataire($pgProgLot->getTitulaire());
                foreach ($prestataires as $prestataire) {
                    $destinataires[$prestataire->getId()] = $prestataire;
                }
            }
            $typMil = $this->repoPgProgTypeMilieu->findByCodeMilieu('RHB');
            $admins = $this->repoPgProgWebuserTypmil->findByTypmil($typMil);
            foreach($admins as $admin) {
                $destinataires[$admin->getWebuser()->getId()] = $admin->getWebuser();
            }
            
            $listeStationLibs = '<ul>';
            $listeStationCodes = array();
            foreach($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
                $listeStationLibs .= '<li>'.$pgCmdSuiviPrel->getPrelev()->getStation()->getCode().' - '.$pgCmdSuiviPrel->getPrelev()->getStation()->getLibelle().'</li>';
                $listeStationCodes[] = $pgCmdSuiviPrel->getPrelev()->getStation()->getCode();
            }
            $listeStationLibs .= '</ul>';

            $objetMessage = "Relance SQE : Lot ".$pgProgLot->getNomLot()." - Stations non réalisée à ce jour";
            $txtMessage = "Lot : " . $pgProgLot->getNomLot() . "<br/>";
            $txtMessage .= "Stations : <br/>" . $listeStationLibs . "<br/>";
            $txtMessage .= "<br/>Vous n'avez pas renseigné le prélèvement de la station comme \"Effectué\" ni déposé les fichiers associés.<br/>";
            foreach ($destinataires as $destinataire) {
                if (!is_null($destinataire)) {
                    $date = new \DateTime();
                    $this->output->writeln($date->format('d/m/Y H:i:s') .' - Send Mail Suivi HB P - Lot '.$pgProgLot->getId().' - Stations '.implode(', ', $listeStationCodes).'- Utilisateur '.$destinataire->getLogin());
                    $this->sendEmail($destinataire, $txtMessage, $objetMessage);
                }
            }
        }
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .' - Fin relance suivis hydrobio (code_milieu like ‘%HB’) P');
    }
        
    protected function sendEmailHbF() {
        $pgProgLots = $this->repoPgCmdSuiviPrel->getLotFWithoutRpsByDays(15);
        
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .' - Début relance suivis hydrobio (code_milieu like ‘%HB’) F');
        foreach($pgProgLots as $pgProgLot) {
            $pgCmdSuiviPrels = $this->repoPgCmdSuiviPrel->getSuiviPrelFWithoutRpsByDaysAndLot(15, $pgProgLot);
            
            $listeStationCodes = array();
            $listePrelevDates = array();

            $destinataires = array();
            if (!is_null($pgProgLot->getTitulaire())) {
                $prestataires = $this->repoPgProgWebUsers->findByPrestataire($pgProgLot->getTitulaire());
                foreach ($prestataires as $prestataire) {
                    $destinataires[$prestataire->getId()] = $prestataire;
                }
            }
            $typMil = $this->repoPgProgTypeMilieu->findByCodeMilieu('RHB');
            $admins = $this->repoPgProgWebuserTypmil->findByTypmil($typMil);
            foreach($admins as $admin) {
                $destinataires[$admin->getWebuser()->getId()] = $admin->getWebuser();
            }
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
            $txtMessage .= "Vous n'avez pas déposé les fichiers associés pour ces stations, le CCTP cf. article 4.1.4 n’est pas respecté à ce jour. <br/>";
            foreach ($destinataires as $destinataire) {
                if (!is_null($destinataire)) {
                    $date = new \DateTime();
                    $this->output->writeln($date->format('d/m/Y H:i:s') .' - Send Mail Suivi HB F - Lot '.$pgProgLot->getId().' - Stations '.implode(', ', $listeStationCodes).' - Utilisateur '.$destinataire->getLogin());
                    $this->sendEmail($destinataire, $txtMessage, $objetMessage);
                }
            }
        }
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .' - Fin relance suivis hydrobio (code_milieu like ‘%HB’) F');
    }
    
    protected function sendEmailRecap() {
        if (count($this->getLogs()) > 0) {
            $typMil = $this->repoPgProgTypeMilieu->findByCodeMilieu('RHB');
            $admins = $this->repoPgProgWebuserTypmil->findByTypmil($typMil);
            $destinataires = array();
            foreach($admins as $admin) {
                $destinataires[$admin->getWebuser()->getId()] = $admin->getWebuser();
            }
            
            $date = new \DateTime();
            $objetMessage = "Relance SQE : Récapitulatif des mises à jour automatique du ".$date->format('d/m/Y H:i:s');
            $txtMessage = "Vous trouverez ci-dessous le récapitulatif des mises à jour automatique effectuées le ".$date->format('d/m/Y H:i:s')."<br/><br/>";
            foreach ($this->getLogs() as $log) {
                $txtMessage .= $log.'<br/>';
            }
            foreach ($destinataires as $destinataire) {
                if (!is_null($destinataire)) {
                    $this->sendEmail($destinataire, $txtMessage, $objetMessage);
                }
            }
        }
    }
    
    protected function sendEmail($destinataire, $txtMessage, $objetMessage) {
        if (!is_null($destinataire)) {
            $mailer = $this->getContainer()->get('mailer');
            if (!$this->getContainer()->get('aeag_sqe.message')->createMail($this->em, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                $this->_addLog('warning', null, null, "Erreur lors de l\'envoi de mail dans le process de verification des RAIs", null, $destinataire);
            } else {
                $date = new \DateTime();
                $this->output->writeln($date->format('d/m/Y H:i:s') . ' - Relance Mail : Un email a été envoyé à ' . $destinataire->getMail());
            }
        } else {
            $date = new \DateTime();
            $this->output->writeln($date->format('d/m/Y H:i:s') . ' - Relance Mail : Le destinataire est null');
        }
    }

    protected function updateHbP() {
        $pgCmdSuiviPrels = $this->repoPgCmdSuiviPrel->getSuiviPrelPByDays(15);
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .' - Début validation auto HB P');
        foreach($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
            $date = new \DateTime();
            $msgLog = $date->format('d/m/Y H:i:s') .' - Mise à jour validation à Accepté HB Prévisionnel - Lot '.$pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getLot()->getNomLot().' - Station '.$pgCmdSuiviPrel->getPrelev()->getStation()->getCode();
            $this->output->writeln($msgLog);
            $this->addLog($msgLog);
            $pgCmdSuiviPrel->setValidation('A');
            $pgCmdSuiviPrel->setValidAuto('O');
            
            $this->emSqe->persist($pgCmdSuiviPrel);
        }
        $this->emSqe->flush();
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .' - Fin validation auto HB P');
    }

    protected function updateHbF() {
        $pgCmdSuiviPrels = $this->repoPgCmdSuiviPrel->getSuiviPrelFWithRpsByDays(21);
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .' - Début validation auto HB F');
        foreach($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
            $date = new \DateTime();
            $msgLog = $date->format('d/m/Y H:i:s') .' - Mise à jour validation à Accepté HB Effectué - Lot '.$pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getLot()->getNomLot().' - Station '.$pgCmdSuiviPrel->getPrelev()->getStation()->getCode();
            $this->output->writeln($msgLog);
            $this->addLog($msgLog);
            $pgCmdSuiviPrel->setValidation('A');
            $pgCmdSuiviPrel->setValidAuto('O');
            
            $pgCmdPrel = $pgCmdSuiviPrel->getPrelev();
            $pgCmdPrel->setDatePrelev($pgCmdSuiviPrel->getDatePrel());
            $pgCmdPrel->setRealise(1);
            
            $this->emSqe->persist($pgCmdSuiviPrel);
            $this->emSqe->persist($pgCmdPrel);
        }
        $this->emSqe->flush();
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .' - Fin validation auto HB F');
    }
    
    public function getLogs() {
        return $this->logs;
    }

    public function setLogs($logs) {
        $this->logs = $logs;
    } 
    
    public function addLog($log) {
        $this->logs[] = $log;
    }

}
