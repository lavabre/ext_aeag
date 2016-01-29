<?php

namespace Aeag\SqeBundle\Command;

//use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessRaiCommand extends ContainerAwareCommand  {

    protected function configure() {
        $this
                ->setName('rai:process')
                ->setDescription('Validation des RAI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $emSqe = $this->getContainer()->get('doctrine')->getManager('sqe');
        // Récupération des programmations
        $repoPgCmdFichiersRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R15');
        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findBy(array('phaseFichier' => $pgProgPhases, 'suppr' => 'N'));
        
        foreach($pgCmdFichiersRps as $pgCmdFichierRps) {
            // recuperation du lien d'acquittement
            $lienAcquit = $pgCmdFichierRps->getLienAcquitSandre();
            // Récupération du fichier xml correspondant
            $reponseTab = json_decode(json_encode(\simplexml_load_file($lienAcquit)),true);
            if (count($reponseTab) > 0) {
                //$output->writeln(var_dump($reponseTab));
                $etatTraitement = $reponseTab['AccuseReception']['Acceptation'];
                $erreurs = array();
                switch ($etatTraitement) {
                    case '0' : // Le traitement est en cours
                            // On ne fait rien
                            break;
                    case '1' : // Le traitement est terminé et le fichier est conforme
                            if (!isset($reponseTab['AccuseReception']['Erreur'])) {
                                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R20');
                                $pgCmdFichierRps->setPhaseFichier($pgProgPhases);
                            } else {
                                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R21');
                                $pgCmdFichierRps->setPhaseFichier($pgProgPhases);
                                if (isset($reponseTab['AccuseReception']['Erreur']["DescriptifErreur"])) {
                                    $erreurs[] = $reponseTab['AccuseReception']['Erreur']["DescriptifErreur"];
                                } else {
                                    foreach ($reponseTab['AccuseReception']['Erreur'] as $erreurXml) {
                                        $erreurs[] = $erreurXml["DescriptifErreur"];
                                    }
                                }
                            }
                            break;
                    case '2' : // Le traitement est terminé et le fichier est non conforme
                            $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R80');
                            $pgCmdFichierRps->setPhaseFichier($pgProgPhases);
                            if (isset($reponseTab['AccuseReception']['Erreur']["DescriptifErreur"])) {
                                $erreurs[] = $reponseTab['AccuseReception']['Erreur']["DescriptifErreur"];
                            } else {
                                foreach ($reponseTab['AccuseReception']['Erreur'] as $erreurXml) {
                                    $erreurs[] = $erreurXml["DescriptifErreur"];
                                }
                            }
                            break;
                    default : 
                            break;
                }
                // Création du fichier de compte rendu
                $pathBase = "/base/extranet/Transfert/Sqe/Echanges/";
                $pathBase .= $pgCmdFichierRps->getDemande()->getAnneeProg().'/'.$pgCmdFichierRps->getDemande()->getCommanditaire()->getNomCorres().
                        '/'.$pgCmdFichierRps->getDemande()->getLotan()->getLot()->getId().'/'.$pgCmdFichierRps->getDemande()->getLotan()->getId().'/'.$pgCmdFichierRps->getId();
                
                $fileName = str_replace('.','_',$pgCmdFichierRps->getNomFichier().'_CR');
                $fullFileName = $pathBase.'/'.$fileName;
                $cr = 'Fichier : '.$pgCmdFichierRps->getNomFichier().PHP_EOL;
                $cr .= 'Date dépot : '.$pgCmdFichierRps->getDateDepot()->format('Y-m-d H:i:s').PHP_EOL;
                $cr .= 'Demande : '.$pgCmdFichierRps->getDemande()->getCodeDemandeCmd().PHP_EOL;
                $cr .= 'Lot : '.$pgCmdFichierRps->getDemande()->getLotan()->getLot()->getNomLot().PHP_EOL;
                $cr .= date("Y-m-d H:i:s").' - '.$pgCmdFichierRps->getPhaseFichier()->getLibellePhase().PHP_EOL;
                $cr .= 'Erreurs : '.  implode(PHP_EOL, $erreurs);
                file_put_contents($fullFileName, $cr);
                // Enregistrement du fichier CR en base
                $pgCmdFichierRps->setNomFichierCompteRendu($fileName);
                
                // Envoi de mail au prestataire et à l'admin
                
                
                $emSqe->persist($pgCmdFichierRps);
                $emSqe->flush();
            }
        }
        
    }
}
