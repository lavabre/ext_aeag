<?php

namespace Aeag\SqeBundle\Utils;

use Symfony\Component\HttpFoundation\Response;

class ProcessRai {

    public function envoiFichierValidationFormat($em, $pgCmdFichierRps, $fullFileName, $session = null) {

        $xmlFileName = $this->extractXmlFile($fullFileName, $session);

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
            if (!is_null($session)) {
                $session->getFlashBag()->add('notice-error', 'Le webservice retourne un fichier vide');
            }
            return false;
        }
    }

    public function extractXmlFile($fullFileName, $session = null) {

        // Récupération de la version d'edilabo
        // Dézippe du fichier
        $zip = new \ZipArchive();
        // Ouvrir l'archive
        if ($zip->open($fullFileName) !== true) {
            if (!is_null($session)) {
                $session->getFlashBag()->add('notice-error', 'Fichier zip erroné');
            }
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
            if (!is_null($session)) {
                $session->getFlashBag()->add('notice-error', 'L\'archive zip contient trop de fichier (un seul requis)');
            }
            return false;
        }

        $fichierXml = '';
        foreach ($files as $file) {
            if (strpos($file, '.xml') !== false) {
                $fichierXml = $destination . '/' . $file;
            }
        }

        if ($fichierXml == '') {
            if (!is_null($session)) {
                $session->getFlashBag()->add('notice-error', 'Le fichier RAI n\'est pas au format XML ' . strpos($file, '.xml') . ' || ' . $destination . '/' . $file);
            }
            return false;
        }

        return $fichierXml;
    }

    public function getVersionEdilabo($xmlFileName) {
        $raiTab = json_decode(json_encode(\simplexml_load_file($xmlFileName)), true);

        $codeScenario = $raiTab['Scenario']['CodeScenario'];
        $versionScenario = $raiTab['Scenario']['VersionScenario'];

        return $codeScenario . ';' . $versionScenario;
    }

    public function getCheminEchange($chemin, $pgCmdDemande, $reponseId = null) {
        $chemin .= $pgCmdDemande->getAnneeProg() . '/' . $pgCmdDemande->getCommanditaire()->getNomCorres() .
                '/' . $pgCmdDemande->getLotan()->getLot()->getId() . '/' . $pgCmdDemande->getLotan()->getId() . '/';
        if (!is_null($reponseId)) {
            $chemin .= $reponseId;
        }

        return $chemin;
    }

    public function post($url, $params) {

        if (isset($params['XML'])) {
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

    public function exportCsvDonneesBrutes($em, $chemin, $pgCmdFichierRps, $donneesBrutes) {

        // Fichier CSV :
        // Récupérer le nom du fichier déposé
        // Supprimer l'extension, rajouter csv
        if (strpos($pgCmdFichierRps->getNomFichier(), '.zip') !== false) {
            $nomFichierRps = str_replace('zip', 'csv', $pgCmdFichierRps->getNomFichier());
        } else {
            $nomFichierRps = $pgCmdFichierRps->getNomFichier() . '.csv';
        }

        $pathBase = $this->getCheminEchange($chemin, $pgCmdFichierRps->getDemande(), $pgCmdFichierRps->getId());
        $fullFileName = $pathBase . '/' . $nomFichierRps;

        $fichier_csv = fopen($fullFileName, 'w+');

        // Chaque ligne du tableau correspond a une ligne du fichier csv
        $lignes = array();
        // Entete
        $lignes[] = array('Année', 'Code station', 'Nom station', 'Code masse d\'eau', 'Code du prélèvement',
            'Siret préleveur', 'Nom préleveur', 'Date-heure du prélèvement', 'Code du paramètre',
            'Libellé court paramètre', 'Nom paramètre', 'Zone verticale', 'Profondeur', 'Code support',
            'Nom support', 'Code fraction', 'Nom fraction', 'Code méthode', 'Nom méthode', 'Code remarque',
            'Résultat', 'Valeur textuelle', 'Code unité', 'libellé unité', 'symbole unité', 'LQ', 'Siret labo',
            'Nom labo', 'Code réseau', 'Nom réseau', 'Siret prod', 'Nom prod', 'Commentaire');

        // Requete de récupération des différents champs
        //$donneesBrutes = $this->repoPgCmdPrelev->getDonneesBrutes($pgCmdFichierRps);
        $lignes = array_merge($lignes, $donneesBrutes);
        foreach ($lignes as $ligne) {
            fputcsv($fichier_csv, $ligne, ';');
        }

        fclose($fichier_csv);

        // Mettre à jour la table pgCmdFichierRps avec le lien vers le fichier des données brutes
        $pgCmdFichierRps->setNomFichierDonneesBrutes($nomFichierRps);
        $em->persist($pgCmdFichierRps);
        $em->flush();
    }

    public function exportCsvDonneesBrutesSaisies($em, $chemin, $pgCmdFichierRps, $donneesBrutes) {

        // Fichier CSV :
        // Récupérer le nom du fichier déposé
        // Supprimer l'extension, rajouter csv
        if (strpos($pgCmdFichierRps->getNomFichier(), '.zip') !== false) {
            $nomFichierRps = str_replace('zip', 'csv', $pgCmdFichierRps->getNomFichier());
        } else {
            $nomFichierRps = $pgCmdFichierRps->getNomFichier() . '.csv';
        }

        $pathBase = $chemin;
        $fullFileName = $pathBase . '/' . $nomFichierRps;

        $fichier_csv = fopen($fullFileName, 'w+');

        // Chaque ligne du tableau correspond a une ligne du fichier csv
        $lignes = array();
        // Entete
        $lignes[] = array('Année', 'Code station', 'Nom station', 'Code masse d\'eau', 'Code du prélèvement',
            'Siret préleveur', 'Nom préleveur', 'Date-heure du prélèvement', 'Code du paramètre',
            'Libellé court paramètre', 'Nom paramètre', 'Zone verticale', 'Profondeur', 'Code support',
            'Nom support', 'Code fraction', 'Nom fraction', 'Code méthode', 'Nom méthode', 'Code remarque',
            'Résultat', 'Valeur textuelle', 'Code unité', 'libellé unité', 'symbole unité', 'LQ', 'Siret labo',
            'Nom labo', 'Code réseau', 'Nom réseau', 'Siret prod', 'Nom prod', 'Commentaire');

        // Requete de récupération des différents champs
        //$donneesBrutes = $this->repoPgCmdPrelev->getDonneesBrutes($pgCmdFichierRps);
        $lignes = array_merge($lignes, $donneesBrutes);
        foreach ($lignes as $ligne) {
            fputcsv($fichier_csv, $ligne, ';');
        }

        fclose($fichier_csv);

        // Mettre à jour la table pgCmdFichierRps avec le lien vers le fichier des données brutes
        $pgCmdFichierRps->setNomFichierDonneesBrutes($nomFichierRps);
        $em->persist($pgCmdFichierRps);
        $em->flush();
    }

    public function exportCsvDonneeslacsSaisies($em, $chemin, $pgProgLotStationAn, $tabDonneesBrutes) {

        // Fichier CSV :
        $nomFichier = 'SAISIE_LAC_' . $pgProgLotStationAn->getId() . '.csv';

        $pathBase = $chemin;
        $fullFileName = $pathBase . '/' . $nomFichier;

        $fichier_csv = fopen($fullFileName, 'w+');

        // Chaque ligne du tableau correspond a une ligne du fichier csv
        $lignes = array();
        // Entete
        $lignes[] = array('Année', 'Code station', 'Nom station', 'Code masse d\'eau', 'Code du prélèvement',
            'Siret préleveur', 'Nom préleveur', 'Date-heure du prélèvement', 'Code du paramètre',
            'Libellé court paramètre', 'Nom paramètre', 'Zone verticale', 'Profondeur', 'Code support',
            'Nom support', 'Code fraction', 'Nom fraction', 'Code méthode', 'Nom méthode', 'Code remarque',
            'Résultat', 'Valeur textuelle', 'Code unité', 'libellé unité', 'symbole unité', 'LQ', 'Siret labo',
            'Nom labo', 'Code réseau', 'Nom réseau', 'Siret prod', 'Nom prod', 'Commentaire');

        // Requete de récupération des différents champs
        //$donneesBrutes = $this->repoPgCmdPrelev->getDonneesBrutes($pgCmdFichierRps);

        $donneesBrutes =  array();
        for ($i = 0; $i < count($tabDonneesBrutes); $i++) {
            $donneesBrutes = array_merge($donneesBrutes, $tabDonneesBrutes[$i]);
        }

        $lignes = array_merge($lignes, $donneesBrutes);
        foreach ($lignes as $ligne) {
            fputcsv($fichier_csv, $ligne, ';');
        }
        fclose($fichier_csv);

        // Mettre à jour la table pgCmdFichierRps avec le lien vers le fichier des données brutes
//        $pgCmdFichierRps->setNomFichierDonneesBrutes($nomFichierRps);
//        $em->persist($pgCmdFichierRps);
//        $em->flush();
    }

}
