<?php

namespace Aeag\SqeBundle\Utils;

class DepotHydrobio {

    public function extraireFichier($emSqe, $pgCmdFichierRps, $pathBase, $nomFichier, $session = null) {

         $session->set('fonction', 'extraireFichier');
    
        $dateDepot = new \DateTime();
        $tabNomFichier = explode('.', $pgCmdFichierRps->getNomFichier());
        $ficRapport = $tabNomFichier[0] . '_rapport.csv';
        $liste = array();
        $liste = $this->unzip($pathBase . '/' . $nomFichier, $pathBase . '/');
        $rapport = fopen($pathBase . '/' . $ficRapport, "w+");
        $contenu = '                                  Rapport d\'intégration du fichier : ' . $nomFichier . ' déposé le ' . $dateDepot->format('d/m/Y') . CHR(13) . CHR(10) . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);
        $contenu = 'Le fichier zip contient  ' . count($liste) . ' fichier(s)' . CHR(13) . CHR(10) . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);

        foreach ($liste as $nomFichier) {
            $tabNomFichier = explode('.', $nomFichier);
            if ($tabNomFichier[1] == 'xls') {
                $traitXls = $this->lireXls($nomFichier, $pathBase, $rapport, $session, $emSqe);
            }
        }



        fclose($rapport);

        // Enregistrement des valeurs en base
        $pgCmdFichierRps->setNomFichierCompteRendu($ficRapport);
        $emSqe->persist($pgCmdFichierRps);
        $emSqe->flush();


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

    protected function lireXls($nomFichier, $pathBase, $rapport, $session, $emSqe) {

        $session->set('fonction', 'lireXls');

        // Récupération des programmations
        $repoPgSandreHbNomemclatures = $emSqe->getRepository('AeagSqeBundle:PgSandreHbNomemclatures');


        $contenu = 'Traitement du fichier excel   ' . $nomFichier . CHR(13) . CHR(10) . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);

        $fileExcel = \PHPExcel_IOFactory::load($pathBase . '/' . $nomFichier);

        foreach ($fileExcel->getWorksheetIterator() as $worksheet) {
            if ($worksheet->getCell('B22') == 'CODE STATION') {
                $contenu = 'Feuillet - ' . $worksheet->getTitle() . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);

                // Coordonnées : cellules K24, L24, M24, N24 doivent être non vides, au format numérique et avec une valeur > 0
                $K24 = $worksheet->getCell('K24')->getValue();
                $L24 = $worksheet->getCell('L24')->getValue();
                $M24 = $worksheet->getCell('M24')->getValue();
                $N24 = $worksheet->getCell('N24')->getValue();
                $avertissement = false;
                if (is_null($K24) or is_null($L24) or is_null($M24) or is_null($N24)) {
                    $avertissement = true;
                    $contenu = '    Avertissement  - Coordonnées Lambert 93 incorrectes ou non renseignées. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (!is_numeric($K24) or !is_numeric($L24) or !is_numeric($M24) or !is_numeric($N24)) {
                    $avertissement = true;
                    $contenu = '    Avertissement  - Coordonnées Lambert 93 incorrectes ou non renseignées. ' ;
                    $contenu = $contenu .  ' K24 : ' .  $K24 . ' L24 : ' .  $L24 . ' M24 : ' . $M24 . ' N24 : ' . $N24 . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif ($K24 == 0 or $L24 == 0 or $M24 == 0 or $N24 == 0) {
                    $avertissement = true;
                    $contenu = '    Avertissement  - Coordonnées Lambert 93 incorrectes ou non renseignées. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu); 
                }

              //  if ($avertissement) {
                    // Coordonnées : cellules K23, L23, M23, N23 doivent être non vides, au format numérique et avec une valeur > 0
                    $K23 = $worksheet->getCell('K23');
                    $L23 = $worksheet->getCell('L23');
                    $M23 = $worksheet->getCell('M23');
                    $N23 = $worksheet->getCell('N23');
                    $avertissement = false;
                    if (is_null($K23) or is_null($L23) or is_null($M23) or is_null($N23)) {
                        $avertissement = true;
                        $contenu = '    Avertissement  - Coordonnées Lambert II incorrectes ou non renseignées. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } elseif (!is_numeric($K23->getValue()) or !is_numeric($L23->getValue()) or !is_numeric($M23->getValue()) or !is_numeric($N23->getValue())) {
                        $avertissement = true;
                        $contenu = '    Avertissement  - Coordonnées Lambert II incorrectes ou non renseignées. ' ;
                         $contenu = $contenu .  ' K23 : ' .  $K23->getValue() . ' L23 : ' .  $L23->getValue() . ' M23 : ' . $M23->getValue() . ' N23 : ' . $N23->getValue() . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } elseif ($K23->getValue() == 0 or $L23->getValue() == 0 or $M23->getValue() == 0 or $N23->getValue() == 0) {
                        $avertissement = true;
                        $contenu = '    Avertissement  - Coordonnées Lambert II incorrectes ou non renseignées. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }
             //   }

                //	Autres valeurs du prélèvement : cellules P23, E39, O23 doivent être non vide, au format numérique
                $P23 = $worksheet->getCell('P23');
                $E39 = $worksheet->getCell('E39');
                $O23 = $worksheet->getCell('O23');
                $avertissement = false;
                if (is_null($P23)) {
                    $avertissement = true;
                    $contenu = '    Avertissement  - cellule P23 non renseignée. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (!is_numeric(intval($P23->getValue()))) {
                    $avertissement = true;
                    $contenu = '    Avertissement  - cellule P23 incorrecte. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
                if (is_null($E39)) {
                    $avertissement = true;
                    $contenu = '    Avertissement  - cellule E39  incorrecte ou non renseignée. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (!is_numeric(intval($E39->getValue()))) {
                    $avertissement = true;
                    $contenu = '    Avertissement  - cellule E39 incorrecte. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
                if (is_null($O23)) {
                    $avertissement = true;
                    $contenu = '    Avertissement  - cellule  O23  incorrecte ou non renseignée. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (!is_numeric(intval($O23->getValue()))) {
                    $avertissement = true;
                    $contenu = '    Avertissement  - cellule O23 incorrecte. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }

                //  Substrats
                $avertissement = false;
                $tabG = array();
                for ($i = 39; $i < 51; $i++) {
                    $G[$i] = $worksheet->getCell('G' . $i);
                    $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('274', $G[$i], '13');
                    if (!$pgSandreHnNomemclature) {
                        $avertissement = true;
                        $contenu = '    Avertissement  - substrat ' . $G[$i] . ' impossible. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }
                }
                
                 //  Recouvrements
                $avertissement = false;
                $tabH = array();
                $totRecouvrement = 0;
                for ($i = 39; $i < 51; $i++) {
                    $H[$i] = $worksheet->getCell('H' . $i);
                     if (!is_null($H[$i])) {
                         if (!is_numeric(intval($H[$i]->getValue()))){
                              $avertissement = true;
                                $contenu = '    Avertissement  - cellule recouvrement H' . $i . ' valeur non numérique. ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                         }else{
                             $totRecouvrement = $totRecouvrement + intval($H[$i]->getCalculatedValue());
                         }
                      }
                }
                if ($totRecouvrement != 100){
                    $avertissement = true;
                                $contenu = '    Avertissement  - la somme des recouvrements doit être égale à 100%. ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                }

                $contenu = CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);

                foreach ($worksheet->getRowIterator() as $row) {
                    $contenu = '   Ligne n° - ' . $row->getRowIndex() . CHR(13) . CHR(10) . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
                    foreach ($cellIterator as $cell) {
                        if (!is_null($cell)) {
                            $contenu = '        Cellule - ' . $cell->getCoordinate() . ' - ' . $cell->getCalculatedValue() . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                    }
                }
            }
        }


        return 'ok';
    }

}
