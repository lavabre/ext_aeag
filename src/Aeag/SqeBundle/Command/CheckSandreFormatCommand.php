<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;

class CheckSandreFormatCommand extends AeagCommand {
    
    protected function configure() {
        $this
                ->setName('rai:check_sandre')
                ->setDescription('Validation Sandre des RAI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        parent::execute($input, $output);

        $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase('R15');
        $pgCmdFichiersRps = $this->repoPgCmdFichiersRps->getReponsesHorsLac($pgProgPhases);
        //$pgCmdFichiersRps = $this->repoPgCmdFichiersRps->findBy(array('phaseFichier' => $pgProgPhases, 'typeFichier' => 'RPS', 'suppr' => 'N'));
        
        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R16');
        }
        
        $cptRaiOk = 0;
        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            
            // recuperation du lien d'acquittement
            $lienAcquit = $pgCmdFichierRps->getLienAcquitSandre();
            // Récupération du fichier xml correspondant
            $reponseTab = json_decode(json_encode(\simplexml_load_file($lienAcquit)), true);
            if (count($reponseTab) > 0) {
                //$output->writeln(var_dump($reponseTab));
                $etatTraitement = $reponseTab['AccuseReception']['Acceptation'];
                $erreurs = array();
                if ($etatTraitement == 1 || $etatTraitement == 2) {
                    $destinataires = array();
                    
                    if ($etatTraitement == 1 && !isset($reponseTab['AccuseReception']['Erreur'])) { // Le traitement est terminé et le fichier est conforme sans erreur
                        $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R20');
                        $validMessage = " conforme";
                        $cptRaiOk++;
                    } else if (($etatTraitement == 1 && isset($reponseTab['AccuseReception']['Erreur'])) || $etatTraitement == 2) {
                        if ($etatTraitement == 1) {
                            $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R21');
                            $validMessage = " conforme avec erreurs";
                            $cptRaiOk++;
                        } else {
                            $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R80');
                            $validMessage = " non conforme";
                        }
                        if (isset($reponseTab['AccuseReception']['Erreur']["DescriptifErreur"])) { // Une seule erreur
                            $erreurs[] = $reponseTab['AccuseReception']['Erreur']["DescriptifErreur"];
                        } else { // Plusieurs erreurs
                            foreach ($reponseTab['AccuseReception']['Erreur'] as $erreurXml) {
                                $erreurs[] = $erreurXml["DescriptifErreur"];
                            }
                        }
                        //$destinataires = $this->repoPgProgWebUsers->findByTypeUser('ADMIN');
                    }
                    
                    // Création du fichier de compte rendu
                    $this->_creationFichierCr($pgCmdFichierRps, $erreurs);
                    
                    $this->emSqe->persist($pgCmdFichierRps);
                    $this->emSqe->flush();
                    
                    // Envoi de mail au prestataire et à l'admin
                    if ($etatTraitement == 2) {
                        $objetMessage = "SQE - DAI " . $pgCmdFichierRps->getDemande()->getCodeDemandeCmd() . " : Fichier " . $pgCmdFichierRps->getId() . $validMessage;
                        $txtMessage = "La RAI " . $pgCmdFichierRps->getNomFichier() . " concernant la DAI " . $pgCmdFichierRps->getDemande()->getCodeDemandeCmd() . " a été analysé. Celui-ci possède un format ".$validMessage.".";
                        // Prevoir plusieurs
                        $destinataires[] = $this->repoPgProgWebUsers->findOneByPrestataire($pgCmdFichierRps->getDemande()->getPrestataire());
                        foreach ($destinataires as $destinataire) {
                            if (!is_null($destinataire)) {
                                $mailer = $this->getContainer()->get('mailer');
                                if (!$this->getContainer()->get('aeag_sqe.message')->createMail($this->em, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                                    $this->_addLog('warning', $pgCmdFichierRps->getDemande()->getId(), $pgCmdFichierRps->getId(), "Erreur lors de l\'envoi de mail dans le process de verification des RAIs", null, $destinataire);
                                }
                            }
                        }
                    }
                } else {
                    $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R15');
                }
            } else {
                $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R15');
            }
        }
        $date = new \DateTime();
        $cptRaiNok = count($pgCmdFichiersRps) - $cptRaiOk;
        $this->output->writeln($date->format('d/m/Y H:i:s').'- Check Sandre : '.count($pgCmdFichiersRps)." RAI(s) traitée(s), ".$cptRaiOk." OK, ".$cptRaiNok." NOK");
    }

    protected function _creationFichierCr($pgCmdFichierRps, $erreurs) {
        // Création du fichier de compte rendu
        $chemin = $this->getContainer()->getParameter('repertoire_echange');
        $pathBase = $this->getContainer()->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdFichierRps->getDemande(), $pgCmdFichierRps->getId());

        $fileName = str_replace('.', '_', $pgCmdFichierRps->getNomFichier() . '_CR').'.txt';
        $fullFileName = $pathBase . '/' . $fileName;
        $cr = 'Fichier : ' . $pgCmdFichierRps->getNomFichier() . "\r\n";
        $cr .= 'Date dépot : ' . $pgCmdFichierRps->getDateDepot()->format('Y-m-d H:i:s') . "\r\n";
        $cr .= 'Demande : ' . $pgCmdFichierRps->getDemande()->getCodeDemandeCmd() . "\r\n";
        $cr .= 'Lot : ' . $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getNomLot() . "\r\n";
        $cr .= date("Y-m-d H:i:s") . ' - ' . $pgCmdFichierRps->getPhaseFichier()->getLibellePhase() . "\r\n";
        if (count($erreurs) > 0) {
            $erreurs = array_unique($erreurs);
            $cr .= 'Erreurs : ' . implode("\r\n", $erreurs);
        }
        file_put_contents($fullFileName, $cr);
        // Enregistrement du fichier CR en base
        $pgCmdFichierRps->setNomFichierCompteRendu($fileName);
    }

}
