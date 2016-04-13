<?php

namespace Aeag\SqeBundle\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BackUpProcessCommand extends AeagCommand {
    
    protected function configure() {
        $this
                ->setName('rai:backup_process')
                ->setDescription('Relance des RAIs arretées en cours de traitement')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        parent::execute($input, $output);
        
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
        $cptPhaseR10Fait = 0;
        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            $pathBase = $this->getCheminEchange($pgCmdFichierRps->getDemande(), $pgCmdFichierRps->getId());
            if ($pgCmdFichierRps->getNomFichier()) {
                if ($this->envoiFichierValidationFormat($this->emSqe, $pgCmdFichierRps, $pathBase . '/' . $pgCmdFichierRps->getNomFichier())) {
                    // Changement de la phase de la réponse 
                    $this->_updatePhase($pgCmdFichierRps, 'R15');

                    // Envoi d'un mail
                    $objetMessage = "RAI " . $pgCmdFichierRps->getId() . " soumise et en cours de validation";
                    $txtMessage = "Votre RAI (id " . $pgCmdFichierRps->getId() . ") concernant la DAI " . $pgCmdFichierRps->getDemande()->getCodeDemandeCmd() . " a été soumise. Le fichier " . $pgCmdFichierRps->getNomFichier() . " est en cours de validation. "
                            . "Vous serez informé lorsque celle-ci sera validée. ";
                    $destinataire = $this->repoPgProgWebUsers->findOneByPrestataire($pgCmdFichierRps->getDemande()->getPrestataire());
                    $this->_envoiMessage($txtMessage, $destinataire, $objetMessage, $pgCmdFichierRps);
                    
                    $cptPhaseR10Fait++;
                }
            }
        }
        
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s').'- BackUp Process : '.$cptPhaseR10Fait." RAI(s) en phase R10 traitée(s)");
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
        
//        $date = new \DateTime();
//        
//        $this->output->writeln($date->format('d/m/Y H:i:s').'- BackUp Process : '.$cptPhaseR10Fait." RAI(s) en phase R10 traitée(s)");
        
        return true;
    }

    protected function _phaseR36() {
        
//        $date = new \DateTime();
//        $this->output->writeln($date->format('d/m/Y H:i:s').'- BackUp Process : '.$cptPhaseR10Fait." RAI(s) en phase R10 traitée(s)");
        return true;
    }

}
