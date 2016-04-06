<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;

class CheckSandreFormatCommand extends ContainerAwareCommand {

    private $emSqe;
    private $em;
    private $output;
    private $repoPgCmdFichiersRps;
    private $repoPgProgPhases;
    private $repoPgProgWebUsers;
    
    protected function configure() {
        $this
                ->setName('rai:check_sandre')
                ->setDescription('Validation Sandre des RAI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->emSqe = $this->getContainer()->get('doctrine')->getManager('sqe');
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->output = $output;
        
        // Récupération des programmations
        $this->repoPgCmdFichiersRps = $this->emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $this->repoPgProgPhases = $this->emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $this->repoPgProgWebUsers = $this->emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase('R15');
        $pgCmdFichiersRps = $this->repoPgCmdFichiersRps->findBy(array('phaseFichier' => $pgProgPhases, 'typeFichier' => 'RPS', 'suppr' => 'N'));

        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            $this->_updatePhase($pgCmdFichierRps, 'R16');
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
                        $this->_updatePhase($pgCmdFichierRps, 'R20');
                        $validMessage = " conforme";
                    } else if (($etatTraitement == 1 && isset($reponseTab['AccuseReception']['Erreur'])) || $etatTraitement == 2) {
                        if ($etatTraitement == 1) {
                            $this->_updatePhase($pgCmdFichierRps, 'R21');
                            $validMessage = " conforme avec erreurs";
                        } else {
                            $this->_updatePhase($pgCmdFichierRps, 'R80');
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
                                $this->_envoiMessage($txtMessage, $destinataire, $objetMessage);
                            }
                        }
                    }
                }
                
            }
        }
        if (count($pgCmdFichiersRps) > 0) {
            $date = new \DateTime();
            $output->writeln($date->format('d/m/Y H:i:s').': '.count($pgCmdFichiersRps)." RAI(s) traitée(s)");
        }
    }

    protected function _creationFichierCr($pgCmdFichierRps, $erreurs) {
        // Création du fichier de compte rendu
        $pathBase = $this->getContainer()->getParameter('repertoire_echange');
        $pathBase .= $pgCmdFichierRps->getDemande()->getAnneeProg() . '/' . $pgCmdFichierRps->getDemande()->getCommanditaire()->getNomCorres() .
                '/' . $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getId() . '/' . $pgCmdFichierRps->getDemande()->getLotan()->getId() . '/' . $pgCmdFichierRps->getId();

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

    protected function _envoiMessage($txtMessage, $destinataire, $objet, $expediteur = 'automate@eau-adour-garonne.fr') {
        // Récupération du service
        $mailer = $this->getContainer()->get('mailer');
        // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
        $mail = \Swift_Message::newInstance('Wonderful Subject')
                ->setSubject($objet)
                ->setFrom($expediteur)
                ->setTo($destinataire->getMail())
                ->setBody($this->getContainer()->get('templating')->render('AeagSqeBundle:EchangeFichiers:reponseEmail.txt.twig', array('message' => $txtMessage)));

        $mailer->send($mail);

        $this->em->flush();
    }
    
    protected function _updatePhase($pgCmdFichierRps, $phase) {
        $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase($phase);
        $pgCmdFichierRps->setPhaseFichier($pgProgPhases);
        $this->emSqe->persist($pgCmdFichierRps);
        
        $pgProgSuiviPhases = new \Aeag\SqeBundle\Entity\PgProgSuiviPhases;
        $pgProgSuiviPhases->setTypeObjet('RPS');
        $pgProgSuiviPhases->setObjId($pgCmdFichierRps->getId());
        $pgProgSuiviPhases->setDatePhase(new \DateTime());
        $pgProgSuiviPhases->setPhase($pgProgPhases);
        $this->emSqe->persist($pgProgSuiviPhases);
        
        $this->emSqe->flush();
    }

}
