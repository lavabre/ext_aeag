<?php

namespace Aeag\SqeBundle\Utils;

class DepotHydrobio {

    public function extraireFichier($em, $pgCmdFichierRps, $pathBase, $nomFichier, $session = null) {
        
    
        $dateDepot = new \DateTime();
        $ficRapport =  $pgCmdFichierRps->getId() .  '_rapport.csv';
        $liste = array();
        $liste = $this->unzip($pathBase . '/' . $nomFichier, $pathBase  . '/');
        $rapport = fopen($pathBase . '/' . $ficRapport , "w+");
        $contenu = '                                  Rapport d\'intégration du fichier : ' . $nomFichier . ' déposé le ' . $dateDepot->format('d/m/Y') . CHR(13) . CHR(10) . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);
        $contenu = 'Le fichier zip contient  ' . count($liste) . ' fichier(s)' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);
        fclose($rapport);
        
        // Enregistrement des valeurs en base
        $reponse = new \Aeag\SqeBundle\Entity\PgCmdFichiersRps();
        $reponse->setDemande($pgCmdFichierRps->getDemande());
        $reponse->setNomFichier($ficRapport);
        $reponse->setDateDepot(new \DateTime());
        $reponse->setTypeFichier('CR');
        $reponse->setPhaseFichier($pgCmdFichierRps->getPhaseFichier());
        $reponse->setUser($pgCmdFichierRps->getUser());
        $reponse->setSuppr('N');

        $em->persist($reponse);
        $em->flush();
        
        
        return true;
    }

    protected function unzip($file, $path = '', $effacer_zip = false) {/* Méthode qui permet de décompresser un fichier zip $file dans un répertoire de destination $path 
      et qui retourne un tableau contenant la liste des fichiers extraits
      Si $effacer_zip est égal à true, on efface le fichier zip d'origine $file */

        $tab_liste_fichiers = array(); //Initialisation 

        $zip = zip_open($file);

        if ($zip) {
            while ($zip_entry = zip_read($zip)) { //Pour chaque fichier contenu dans le fichier zip 
                if (zip_entry_filesize($zip_entry) >= 0) {
                    $complete_path = $path . dirname(zip_entry_name($zip_entry));

                    /* On supprime les éventuels caractères spéciaux et majuscules */
                    $nom_fichier = zip_entry_name($zip_entry);
                    $nom_fichier = strtr($nom_fichier, "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ", "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn");
                    $nom_fichier = strtolower($nom_fichier);
                    $nom_fichier = ereg_replace('[^a-zA-Z0-9.]', '-', $nom_fichier);

                    /* On ajoute le nom du fichier dans le tableau */
                    array_push($tab_liste_fichiers, $nom_fichier);

                    $complete_name = $path . $nom_fichier; //Nom et chemin de destination 

                    if (!file_exists($complete_path)) {
                        $tmp = '';
                        foreach (explode('/', $complete_path) AS $k) {
                            $tmp .= $k . '/';

                            if (!file_exists($tmp)) {
                                mkdir($tmp, 0755);
                            }
                        }
                    }

                    /* On extrait le fichier */
                    if (zip_entry_open($zip, $zip_entry, "r")) {
                        $fd = fopen($complete_name, 'w');

                        fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));

                        fclose($fd);
                        zip_entry_close($zip_entry);
                    }
                }
            }

            zip_close($zip);

            /* On efface éventuellement le fichier zip d'origine */
            if ($effacer_zip === true)
                unlink($file);
        }

        return $tab_liste_fichiers;
    }

}
