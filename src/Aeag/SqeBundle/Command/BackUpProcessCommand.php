<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BackUpProcessCommand extends ContainerAwareCommand {

    private $emSqe;
    private $em;
    private $output;
    private $repoPgCmdFichiersRps;
    private $repoPgProgPhases;
    private $repoPgProgWebUsers;
    
    protected function configure() {
        $this
                ->setName('rai:backup_process')
                ->setDescription('Relance des RAIs arretées en cours de traitement')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->emSqe = $this->getContainer()->get('doctrine')->getManager('sqe');

        $this->output = $output;

        $this->repoPgCmdFichiersRps = $this->emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $this->repoPgProgPhases = $this->emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $this->repoPgProgWebUsers = $this->emSqe->getRepository('AeagSqeBundle:PgProgWebUsers');

        // Phase 210
        $this->_phaseR10();

        // Phase 226
        $this->_phaseR26();

        // Phase 236
        $this->_phaseR36();
    }

    protected function _phaseR10() {
        // Repérer les fichier réponses ayant une phase R10 (210)
        $pgProgPhase = $this->repoPgProgPhases->findOneByCodePhase('R10');
        $pgCmdFichiersRps = $this->repoPgCmdFichiersRps->findBy(array('typeFichier' => 'RPS', 'phaseFichier' => $pgProgPhase));
        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            $pathBase = $this->getCheminEchange($pgCmdFichierRps->getDemande(), $pgCmdFichierRps->getId());
            if ($pgCmdFichierRps->getNomFichier()) {
                if ($this->envoiFichierValidationFormat($this->emSqe, $pgCmdFichierRps, $pathBase . '/' . $pgCmdFichierRps->getNomFichier())) {
                    // Changement de la phase de la réponse 
                    $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase('R15');
                    $pgCmdFichierRps->setPhaseFichier($pgProgPhases);
                    $this->emSqe->persist($pgCmdFichierRps);
                    $this->emSqe->flush();

                    // Envoi d'un mail
                    $objetMessage = "RAI " . $pgCmdFichierRps->getId() . " soumise et en cours de validation";
                    $txtMessage = "Votre RAI (id " . $pgCmdFichierRps->getId() . ") concernant la DAI " . $pgCmdFichierRps->getDemande()->getCodeDemandeCmd() . " a été soumise. Le fichier " . $pgCmdFichierRps->getNomFichier() . " est en cours de validation. "
                            . "Vous serez informé lorsque celle-ci sera validée. ";
                    $destinataire = $this->repoPgProgWebUsers->findOneByPrestataire($pgCmdFichierRps->getDemande()->getPrestataire());
                    $this->_envoiMessage($txtMessage, $destinataire, $objetMessage, $pgCmdFichierRps);
                }
            }
        }
    }

    public function envoiFichierValidationFormat($em, $pgCmdFichierRps, $fullFileName) {

        $xmlFileName = $this->extractXmlFile($fullFileName);

        if ($xmlFileName == false) {
            return false;
        }

        $versionEdilabo = $this->getVersionEdilabo($xmlFileName);

        // Données POST
        $params = array(
            "XML" => $xmlFileName,
            "XSD" => $versionEdilabo,
            "NomSI" => "Logiciel version 1",
            "VersionSI" => "4.3",
            "Transformation" => "1",
            "NomIntervenant" => "AGENCE DE L'EAU ADOUR-GARONNE",
            "CdIntervenant" => "18310006400033"
        );

        $r = $this->post("http://sandre.eaufrance.fr/PS/parseurSANDRE", $params);

        if ($r !== '') {
            // Analyse de la réponse 
            // Récupération des valeurs dans la réponse
            $reponseTab = json_decode(json_encode(\simplexml_load_string($r)), true);

            // Stockage des valeurs en base
            if (isset($reponseTab['LienAcquittement']) && isset($reponseTab['LienCertificat'])) {
                $pgCmdFichierRps->setLienAcquitSandre($reponseTab['LienAcquittement']);
                $pgCmdFichierRps->setLienCertifSandre($reponseTab['LienCertificat']);
                $em->persist($pgCmdFichierRps);
                $em->flush();

                return true;
            }
            return false;
        } else {
            return false;
        }
    }

    protected function extractXmlFile($fullFileName) {

        // Récupération de la version d'edilabo
        // Dézippe du fichier
        $zip = new \ZipArchive();
        // Ouvrir l'archive
        if ($zip->open($fullFileName) !== true) {
            return false;
        }
        // Extraire le contenu dans le dossier de destination
        $destination = str_replace(".zip", "", $fullFileName);
        $zip->extractTo($destination);
        // Fermer l'archive
        $zip->close();

        // Lecture du fichier
        $files = scandir($destination);

        if (count($files) != 3) {
            return false;
        }

        $fichierXml = '';
        foreach ($files as $file) {
            if (strpos($file, '.xml') !== false) {
                $fichierXml = $destination . '/' . $file;
            }
        }

        if ($fichierXml == '') {
            return false;
        }

        return $fichierXml;
    }

    protected function getVersionEdilabo($xmlFileName) {
        $raiTab = json_decode(json_encode(\simplexml_load_file($xmlFileName)), true);

        $codeScenario = $raiTab['Scenario']['CodeScenario'];
        $versionScenario = $raiTab['Scenario']['VersionScenario'];

        return $codeScenario . ';' . $versionScenario;
    }
    
    protected function _envoiMessage($txtMessage, $destinataire, $objet, $pgCmdFichierRps, $expediteur = 'automate@eau-adour-garonne.fr') {
        $txtMessage = "<html><head></head><body>" . $txtMessage . "</body></html>";
        try {
            // Récupération du service
            $mailer = $this->getContainer()->get('mailer');
            // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
            $mail = \Swift_Message::newInstance('Wonderful Subject')
                    ->setSubject($objet)
                    ->setFrom($expediteur)
                    ->setTo($destinataire->getMail())
                    ->setBody($txtMessage, 'text/html');

            $mailer->send($mail);

            $this->em->flush();
        } catch (\Swift_TransportException $ex) {
            $this->_addLog('warning', $pgCmdFichierRps->getDemande()->getId(), $pgCmdFichierRps->getId(), "Erreur lors de l\'envoi de mail dans le process de verification des RAIs", null, $ex->getMessage());
        }
    }
    
    protected function getCheminEchange($pgCmdDemande, $reponseId = null) {
        $chemin = $this->getContainer()->getParameter('repertoire_echange');
        $chemin .= $pgCmdDemande->getAnneeProg() . '/' . $pgCmdDemande->getCommanditaire()->getNomCorres() .
                '/' . $pgCmdDemande->getLotan()->getLot()->getId() . '/' . $pgCmdDemande->getLotan()->getId() . '/';
        if (!is_null($reponseId)) {
            $chemin .= $reponseId;
        }

        return $chemin;
    }
    
    protected function post($url, $params) {

        if (isset ($params['XML'])) {
            $params['XML'] = \curl_file_create($params['XML'], 'application/xml', 'data.xml');
        }
        
        // Initialisation de CURL
        $ch = \curl_init($url);
                        
        //\curl_setopt($ch, CURLOPT_URL, $url);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($ch, CURLOPT_POST, 1);
        \curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        // Executer et fermer la session curl
        $result = \curl_exec($ch);
        $result = \urldecode($result);
        \curl_close($ch);
        return $result;
    }

    protected function _phaseR26() {
        return true;
    }

    protected function _phaseR36() {
        return true;
    }

}
