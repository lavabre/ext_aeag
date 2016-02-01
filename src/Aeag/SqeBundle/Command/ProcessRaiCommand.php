<?php

namespace Aeag\SqeBundle\Command;

//use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;

class ProcessRaiCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('rai:process')
                ->setDescription('Validation des RAI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $emSqe = $this->getContainer()->get('doctrine')->getManager('sqe');
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        // Récupération des programmations
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R15');
        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findBy(array('phaseFichier' => $pgProgPhases, 'suppr' => 'N'));

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
                        $pgCmdFichierRps->setPhaseFichier($repoPgProgPhases->findOneByCodePhase('R20'));
                        $validMessage = " conforme";
                    } else if (($etatTraitement == 1 && isset($reponseTab['AccuseReception']['Erreur'])) || $etatTraitement == 2) {
                        if ($etatTraitement == 1) {
                            $pgCmdFichierRps->setPhaseFichier($repoPgProgPhases->findOneByCodePhase('R21'));
                            $validMessage = " conforme avec erreurs";
                        } else {
                            $pgCmdFichierRps->setPhaseFichier($repoPgProgPhases->findOneByCodePhase('R80'));
                            $validMessage = " non conforme";
                        }
                        if (isset($reponseTab['AccuseReception']['Erreur']["DescriptifErreur"])) { // Une seule erreur
                            $erreurs[] = $reponseTab['AccuseReception']['Erreur']["DescriptifErreur"];
                        } else { // Plusieurs erreurs
                            foreach ($reponseTab['AccuseReception']['Erreur'] as $erreurXml) {
                                $erreurs[] = $erreurXml["DescriptifErreur"];
                            }
                        }
                        $destinataires = $repoPgProgWebUsers->findByTypeUser('ADMIN');
                    } 
                    
                    // Création du fichier de compte rendu
                    $this->_creationFichier($pgCmdFichierRps, $erreurs);
                    
                    // Envoi de mail au prestataire et à l'admin
                    $objetMessage = "SQE - DAI " . $pgCmdFichierRps->getDemande()->getCodeDemandeCmd() . " : Fichier " . $pgCmdFichierRps->getId() . $validMessage;
                    $txtMessage = "La RAI " . $pgCmdFichierRps->getNomFichier() . " concernant la DAI " . $pgCmdFichierRps->getDemande()->getCodeDemandeCmd() . " a été analysé. Celui-ci possède un format ".$validMessage.".";
                    $destinataires[] = $repoPgProgWebUsers->findOneByPrestataire($pgCmdFichierRps->getDemande()->getPrestataire());
                    foreach ($destinataires as $destinataire) {
                        $this->_envoiMessage($em, $txtMessage, $destinataire, $objetMessage);
                    }

                    $emSqe->persist($pgCmdFichierRps);
                    $emSqe->flush();
                    
                }
                
            }
        }
    }

    protected function _creationFichier($pgCmdFichierRps, $erreurs) {
        // Création du fichier de compte rendu
        $pathBase = $this->getContainer()->getParameter('repertoire_echange');
        $pathBase .= $pgCmdFichierRps->getDemande()->getAnneeProg() . '/' . $pgCmdFichierRps->getDemande()->getCommanditaire()->getNomCorres() .
                '/' . $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getId() . '/' . $pgCmdFichierRps->getDemande()->getLotan()->getId() . '/' . $pgCmdFichierRps->getId();

        $fileName = str_replace('.', '_', $pgCmdFichierRps->getNomFichier() . '_CR').'.txt';
        $fullFileName = $pathBase . '/' . $fileName;
        $cr = 'Fichier : ' . $pgCmdFichierRps->getNomFichier() . PHP_EOL;
        $cr .= 'Date dépot : ' . $pgCmdFichierRps->getDateDepot()->format('Y-m-d H:i:s') . PHP_EOL;
        $cr .= 'Demande : ' . $pgCmdFichierRps->getDemande()->getCodeDemandeCmd() . PHP_EOL;
        $cr .= 'Lot : ' . $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getNomLot() . PHP_EOL;
        $cr .= date("Y-m-d H:i:s") . ' - ' . $pgCmdFichierRps->getPhaseFichier()->getLibellePhase() . PHP_EOL;
        $cr .= 'Erreurs : ' . implode(PHP_EOL, $erreurs);
        file_put_contents($fullFileName, $cr);
        // Enregistrement du fichier CR en base
        $pgCmdFichierRps->setNomFichierCompteRendu($fileName);
    }

    protected function _envoiMessage($em, $txtMessage, $destinataire, $objet, $expediteur = 'automate@eau-adour-garonne.fr') {

        $message = new Message();
        $message->setRecepteur($destinataire->getId());
        $message->setEmetteur($destinataire->getId());
        $message->setNouveau(true);
        $message->setIteration(2);
        $texte = "Bonjour ," . PHP_EOL;
        $texte .= " " . PHP_EOL;
        $texte .= $txtMessage;
        $texte .= " " . PHP_EOL;
        $texte .= "Cordialement.";
        $message->setMessage($texte);
        $em->persist($message);

        $notification = new Notification();
        $notification->setRecepteur($destinataire->getId());
        $notification->setEmetteur($destinataire->getId());
        $notification->setNouveau(true);
        $notification->setIteration(2);
        $notification->setMessage($txtMessage);
        $em->persist($notification);

        // Récupération du service
        $mailer = $this->getContainer()->get('mailer');
        // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
        $mail = \Swift_Message::newInstance('Wonderful Subject')
                ->setSubject($objet)
                ->setFrom($expediteur)
                ->setTo($destinataire->getMail())
                ->setBody($this->getContainer()->get('templating')->render('AeagSqeBundle:EchangeFichiers:reponseEmail.txt.twig', array('message' => $txtMessage)));

        $mailer->send($mail);

        $em->flush();
    }

}
