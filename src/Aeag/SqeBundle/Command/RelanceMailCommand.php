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
        
        // Envoi de mails lorsque la DAI est déposé
        $this->sendEmailDai();

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
    
    protected function sendEmailDai() {
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .' - Début envoi mails Dais');
        
        // Récupération des DAIS dernièrement générées
        $pgCmdDemandes = $this->repoPgCmdDemande->getLotanAndPrestaByCodePhase('D10');  
        $this->output->writeln($date->format('d/m/Y H:i:s') .' Nb demandes :'.count($pgCmdDemandes));
        foreach ($pgCmdDemandes as $pgCmdDemande) {
            $prestataire = $this->repoPgRefCorresPresta->findOneByAdrCorId($pgCmdDemande['prestaId']);
            $lotan = $this->repoPgProgLotan->findOneById($pgCmdDemande['lotanId']);
            $codeMilieu = $lotan->getLot()->getCodeMilieu()->getCodeMilieu();
            
            $objetMessage = "SQE : DAI générée - " . $lotan->getLot()->getNomLot();
            if ($this->getEnv() !== 'prod') {
                $objetMessage .= " - ".$this->getEnv();
            }
            
            $pgProgPrestaTypfic = $this->repoPgProgPrestaTypfic->findOneBy(array('codeMilieu' => $codeMilieu, 'prestataire' => $prestataire));
            
            if (!is_null($pgProgPrestaTypfic)) {
                if (stripos($codeMilieu,"PC") !== false) {
                    if (stripos($pgProgPrestaTypfic->getFormatFic(), "EDILABO") !== false) {
                        $url = $this->getContainer()->get('router')->generate('AeagSqeBundle_echangefichiers_demandes', array('lotId' => $lotan->getLot()->getId(), 'anneeProg' => $lotan->getAnneeProg()), UrlGeneratorInterface::ABSOLUTE_URL);
                        $txtMessage = 'Les DAI du lot ' . $lotan->getLot()->getNomLot() . ' ont été générées.<br/>';
                        $txtMessage .= 'Vous pouvez les télécharger sur SQE : <a href="' . $url . '">Cliquez ici</a> <br/>';
                    } elseif (stripos($pgProgPrestaTypfic->getFormatFic(), "SAISIE") !== false) {
                        $url = $this->getContainer()->get('router')->generate('AeagSqeBundle_saisieDonnees_lot_periodes', array("lotanId" => $lotan->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
                        $txtMessage = 'Le lot ' . $lotan->getLot()->getNomLot() . ' a été généré. <br/>';
                        $txtMessage .= 'Vous pouvez saisir les données sur SQE : <a href="'. $url .'">Cliquez ici</a> <br/>';
                    } 
                } else if (stripos($codeMilieu,"HB") !== false || stripos($codeMilieu,"HM") !== false) {
                    if (stripos($pgProgPrestaTypfic->getFormatFic(), "Suivi_HB") !== false) {
                        $url = $this->getContainer()->get('router')->generate('AeagSqeBundle_suiviHydrobio_lot_periodes', array("lotanId" => $lotan->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
                        $txtMessage = 'Le lot ' . $lotan->getLot()->getNomLot() . ' a été généré. <br/>';
                        $txtMessage .= 'Vous pouvez renseigner le suivi des prélèvements sur SQE : <a href="'.$url.'">Cliquez ici</a>. <br/>';
                    }
                }
            }
            if (isset($txtMessage)) {
                $utilisateurs = $this->repoPgProgWebUsers->getPgProgWebusersByPrestataireAndTypeMilieu($prestataire, $lotan->getLot()->getCodeMilieu());
                foreach ($utilisateurs as $utilisateur) {
                    $this->sendEmail($utilisateur, $txtMessage, $objetMessage);
                }
            }
            
            //Mise à jour de la phase
            $pgProgPhase = $this->repoPgProgPhases->findOneByCodePhase('D10');
            $pgCmdDemandesMaJ = $this->repoPgCmdDemande->findBy(array('lotan' => $lotan, 'prestataire' => $prestataire, 'phaseDemande' => $pgProgPhase));
            $pgProgPhase = $this->repoPgProgPhases->findOneByCodePhase('D20');
            foreach($pgCmdDemandesMaJ as $pgCmdDemandeMaJ) {
                $this->output->writeln($date->format('d/m/Y H:i:s') .' Demande id :'.$pgCmdDemandeMaJ->getId());
                $pgCmdDemandeMaJ->setPhaseDemande($pgProgPhase);
                $this->emSqe->persist($pgCmdDemandeMaJ);
            }
            $this->emSqe->flush();
        }
        
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') .' - Fin envoi mails Dais');
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
                $prestataires = $this->repoPgProgWebUsers->getPgProgWebusersByPrestataireAndTypeMilieu($pgCmdDemande->getLotan()->getLot()->getTitulaire(), $pgCmdDemande->getLotan()->getLot()->getCodeMilieu());
                foreach($prestataires as $prestataire) {
                    $destinataires[$prestataire->getId()] = $prestataire;
                }
            }
            if (!is_null($pgCmdDemande->getPrestataire())) {
                $prestatairesDmd = $this->repoPgProgWebUsers->getPgProgWebusersByPrestataireAndTypeMilieu($pgCmdDemande->getPrestataire(), $pgCmdDemande->getLotan()->getLot()->getCodeMilieu());
                foreach($prestatairesDmd as $prestataireDmd) {
                    $destinataires[$prestataireDmd->getId()] = $prestataireDmd;
                }
            }
            $objetMessage = "Relance SQE - RAI : Dépot de fichier en attente " . $pgCmdDemande->getLotan()->getLot()->getNomLot();
            if ($this->getEnv() !== 'prod') {
                $objetMessage .= " - ".$this->getEnv();
            }
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
                $prestataires = $this->repoPgProgWebUsers->getPgProgWebusersByPrestataireAndTypeMilieu($pgCmdDemande->getLotan()->getLot()->getTitulaire(), $pgCmdDemande->getLotan()->getLot()->getCodeMilieu());
                foreach($prestataires as $prestataire) {
                    $destinataires[$prestataire->getId()] = $prestataire;
                }
            }
            
            if (!is_null($pgCmdDemande->getPrestataire())) {
                $prestatairesDmd = $this->repoPgProgWebUsers->getPgProgWebusersByPrestataireAndTypeMilieu($pgCmdDemande->getPrestataire(), $pgCmdDemande->getLotan()->getLot()->getCodeMilieu());
                foreach($prestatairesDmd as $prestataireDmd) {
                    $destinataires[$prestataireDmd->getId()] = $prestataireDmd;
                }
            }

            $objetMessage = "Relance SQE - RAI : Dépot de fichier non effectué " . $pgCmdDemande->getLotan()->getLot()->getNomLot();
            if ($this->getEnv() !== 'prod') {
                $objetMessage .= " - " . $this->getEnv();
            }
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
                $prestataires = $this->repoPgProgWebUsers->getPgProgWebusersByPrestataireAndTypeMilieu($pgProgLot->getTitulaire(), $pgProgLot->getCodeMilieu());
                foreach ($prestataires as $prestataire) {
                    $destinataires[$prestataire->getId()] = $prestataire;
                }
            }
            $typMil = $this->repoPgProgTypeMilieu->findByCodeMilieu('RHB');
            $admins = $this->repoPgProgWebuserTypmil->getPgProgWebuserTypmilByTypMilAndTypeUser($typeMil, 'ADMIN');
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

            $objetMessage = "Relance SQE : Lot " . $pgProgLot->getNomLot() . " - Stations non réalisées à ce jour";
            if ($this->getEnv() !== 'prod') {
                $objetMessage .= " - " . $this->getEnv();
            }
            $txtMessage = "Lot : " . $pgProgLot->getNomLot() . "<br/>";
            $txtMessage .= "Stations prévues le ".$pgCmdSuiviPrels[0]->getDatePrel()->format('d/m/Y').":<br/>" . $listeStationLibs . "<br/>";
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
                $prestataires = $this->repoPgProgWebUsers->getPgProgWebusersByPrestataireAndTypeMilieu($pgProgLot->getTitulaire(), $pgProgLot->getCodeMilieu());
                foreach ($prestataires as $prestataire) {
                    $destinataires[$prestataire->getId()] = $prestataire;
                }
            }
            $typMil = $this->repoPgProgTypeMilieu->findByCodeMilieu('RHB');
            $admins = $this->repoPgProgWebuserTypmil->getPgProgWebuserTypmilByTypMilAndTypeUser($typeMil, 'ADMIN');
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

            $objetMessage = "Relance SQE : Lot " . $pgProgLot->getNomLot() . " - Absence de documents de terrain ";
            if ($this->getEnv() !== 'prod') {
                $objetMessage .= " - " . $this->getEnv();
            }
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
            $admins = $this->repoPgProgWebuserTypmil->getPgProgWebuserTypmilByTypMilAndTypeUser($typeMil, 'ADMIN');
            $destinataires = array();
            foreach($admins as $admin) {
                $destinataires[$admin->getWebuser()->getId()] = $admin->getWebuser();
            }
            
            $date = new \DateTime();
            $objetMessage = "Relance SQE : Récapitulatif des mises à jour automatique du " . $date->format('d/m/Y H:i:s');
            if ($this->getEnv() !== 'prod') {
                $objetMessage .= " - " . $this->getEnv();
            }
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
