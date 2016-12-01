<?php

namespace Aeag\SqeBundle\Utils;

class DepotHydrobio {

    public function extraireFichier($demandeId, $emSqe, $pgCmdFichierRps, $pathBase, $nomFichier, $session = null, $excelObj) {

        $session->set('fonction', 'extraireFichier');

        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);

        $dateDepot = new \DateTime();
        $tabNomFichier = explode('.', $pgCmdFichierRps->getNomFichier());
        $ficRapport = $tabNomFichier[0] . '_rapport.csv';
        $liste = array();
        $liste = $this->unzip($pathBase . '/' . $nomFichier, $pathBase . '/');
        $rapport = fopen($pathBase . '/' . $ficRapport, "w+");
        $contenu = '                                   Rapport d\'intégration du ' . $dateDepot->format('d/m/Y') . CHR(13) . CHR(10) . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);
        $contenu = '                                   Fichier : ' . $nomFichier . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);
        $contenu = '                                   Demande : ' . $pgCmdDemande->getCodeDemandeCmd() . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);
        $contenu = '                                   Periode : ' . $pgCmdDemande->getPeriode()->getlabelPeriode() . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);
        $contenu = '                                   Programmation : ' . $pgCmdDemande->getLotan()->getLot()->getNomLot() . '  ' . $pgCmdDemande->getLotan()->getAnneeProg() . ' ' . $pgCmdDemande->getLotan()->getVersion() . CHR(13) . CHR(10) . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);
        $contenu = '                                   Nombre de fichiers :  ' . count($liste) . CHR(13) . CHR(10) . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);

        $tabTraitFichiers = array();
        $fic = 0;

        foreach ($liste as $nomFichier) {
            $tabNomFichier = explode('.', $nomFichier);
            if ($tabNomFichier[1] == 'xls') {
                $tabFichier = $this->lireXls($demandeId, $excelObj, $nomFichier, $pathBase, $rapport, $session, $emSqe);
                $tabTraitFichiers[$fic] = $tabFichier;
                $fic++;
            }
        }



        fclose($rapport);

        // Enregistrement des valeurs en base
        $pgCmdFichierRps->setNomFichierCompteRendu($ficRapport);
        $emSqe->persist($pgCmdFichierRps);
        $emSqe->flush();


        return $tabTraitFichiers;
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

    protected function lireXls($demandeId, $excelObj, $nomFichier, $pathBase, $rapport, $session, $emSqe) {

        $session->set('fonction', 'lireXls');

        // Récupération des programmations
        $repoPgSandreHbNomemclatures = $emSqe->getRepository('AeagSqeBundle:PgSandreHbNomemclatures');
        $repoPgSandreAppellationTaxon = $emSqe->getRepository('AeagSqeBundle:PgSandreAppellationTaxon');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');

        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);


        $tabFichier = array();
        $tabFichier['fichier'] = $nomFichier;

        $contenu = '     fichier : ' . $nomFichier . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);

        // $fileExcel = \PHPExcel_IOFactory::load($pathBase . '/' . $nomFichier);
        $fileExcel = $excelObj->load($pathBase . '/' . $nomFichier);

        $feuil = 0;
        $tabFeuillets = array();
        foreach ($fileExcel->getWorksheetIterator() as $worksheet) {
            if ($worksheet->getCell('B22') == 'CODE STATION') {
                $tabFeuillets[$feuil]['feuillet'] = $worksheet->getTitle();
                $contenu = '          Feuillet : ' . $worksheet->getTitle() . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);

                $avertissement = false;
                $erreur = false;

                // station
                $B23 = $worksheet->getCell('B23');
                $codeStation = $B23->getValue();
                $trouve = false;
                foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                     if (substr($pgCmdPrelev->getStation()->getCode(), -(strlen($codeStation))) == $codeStation) {
                        $trouve = true;
                        if ($pgCmdPrelev->getPhaseDmd()->getCodePhase() == 'M40') {
                            $avertissement = true;
                            $contenu = '                     Avertissement : la station ' . $codeStation . ' ' . $pgCmdPrelev->getStation()->getLibelle() . ' à déjà étét traitée.' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                        break;
                    }
                }
                if (!$trouve) {
                    $erreur = true;
                    $contenu = '                     Erreur : la station ' . $codeStation . ' ' . $worksheet->getCell('D23')->getValue() . ' ne fait pas partie de cette demande' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } else {
                    // Coordonnées : cellules K24, L24, M24, N24 doivent être non vides, au format numérique et avec une valeur > 0
                    $K24 = $worksheet->getCell('K24')->getValue();
                    $L24 = $worksheet->getCell('L24')->getValue();
                    $M24 = $worksheet->getCell('M24')->getValue();
                    $N24 = $worksheet->getCell('N24')->getValue();

                    if (is_null($K24) or is_null($L24) or is_null($M24) or is_null($N24)) {
                        $avertissement = true;
                        $contenu = '                     Avertissement : Coordonnées Lambert 93 incorrectes ou non renseignées. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } elseif (!is_numeric($K24) or ! is_numeric($L24) or ! is_numeric($M24) or ! is_numeric($N24)) {
                        $avertissement = true;
                        $contenu = '                     Avertissement : Coordonnées Lambert 93 incorrectes ou non renseignées. ';
                        $contenu = $contenu . ' K24 : ' . $K24 . ' L24 : ' . $L24 . ' M24 : ' . $M24 . ' N24 : ' . $N24 . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } elseif ($K24 == 0 or $L24 == 0 or $M24 == 0 or $N24 == 0) {
                        $avertissement = true;
                        $contenu = '                     Avertissementt : Coordonnées Lambert 93 incorrectes ou non renseignées. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }

                    if ($avertissement) {
                        // Coordonnées : cellules K23, L23, M23, N23 doivent être non vides, au format numérique et avec une valeur > 0
                        $K23 = $worksheet->getCell('K23');
                        $L23 = $worksheet->getCell('L23');
                        $M23 = $worksheet->getCell('M23');
                        $N23 = $worksheet->getCell('N23');
                        if (is_null($K23) or is_null($L23) or is_null($M23) or is_null($N23)) {
                            $erreur = true;
                            $contenu = '                     Erreur : Coordonnées Lambert II incorrectes ou non renseignées. ' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        } elseif (!is_numeric($K23->getValue()) or ! is_numeric($L23->getValue()) or ! is_numeric($M23->getValue()) or ! is_numeric($N23->getValue())) {
                            $erreur = true;
                            $contenu = '                     Erreur : Coordonnées Lambert II incorrectes ou non renseignées. ';
                            $contenu = $contenu . ' K23 : ' . $K23->getValue() . ' L23 : ' . $L23->getValue() . ' M23 : ' . $M23->getValue() . ' N23 : ' . $N23->getValue() . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        } elseif ($K23->getValue() == 0 or $L23->getValue() == 0 or $M23->getValue() == 0 or $N23->getValue() == 0) {
                            $erreur = true;
                            $contenu = '                     Erreur : Coordonnées Lambert II incorrectes ou non renseignées. ' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                    }


                    //	Autres valeurs du prélèvement : cellules P23, E39, O23 doivent être non vide, au format numérique
                    $P23 = $worksheet->getCell('P23');
                    $E39 = $worksheet->getCell('E39');
                    $O23 = $worksheet->getCell('O23');
                    if (is_null($P23)) {
                        $avertissement = true;
                        $contenu = '                     Avertissementt : cellule ' . $P23->getCoordinate() . ' non renseignée. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } elseif (!is_numeric(intval($P23->getValue()))) {
                        $avertissement = true;
                        $contenu = '                      Avertissementt : cellule ' . $P23->getCoordinate() . 'incorrecte. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }
                    if (is_null($E39)) {
                        $avertissement = true;
                        $contenu = '                      Avertissementt : cellule ' . $E39->getCoordinate() . ' incorrecte ou non renseignée. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } elseif (!is_numeric(intval($E39->getValue()))) {
                        $avertissement = true;
                        $contenu = '                     Avertissementt : cellule ' . $PE39->getCoordinate() . 'incorrecte. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }
                    if (is_null($O23)) {
                        $avertissement = true;
                        $contenu = '                     Avertissementt : cellule ' . $PO23->getCoordinate() . 'incorrecte ou non renseignée. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } elseif (!is_numeric(intval($O23->getValue()))) {
                        $avertissement = true;
                        $contenu = '                      Avertissementt : cellule ' . $PO23->getCoordinate() . 'incorrecte. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }

                    //  Substrats
                    $tabG = array();
                    for ($i = 39; $i < 51; $i++) {
                        $G[$i] = $worksheet->getCell('G' . $i);
                        $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('274', $G[$i]->getValue(), '13');
                        if (!$pgSandreHnNomemclature) {
                            $erreur = true;
                            $contenu = '                     Erreur : cellule ' . $G[$i]->getCoordinate() . ' : substrat ' . $G[$i]->getValue() . ' impossible. ' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                    }

                    //  Recouvrements :  Cellules H39 à H50, les valeurs doivent être numériques si non vides
                    $tabH = array();
                    $totRecouvrement = 0;
                    for ($i = 39; $i < 51; $i++) {
                        $H[$i] = $worksheet->getCell('H' . $i);
                        if (!is_null($H[$i])) {
                            if (!is_numeric(intval($H[$i]->getValue()))) {
                                $avertissement = true;
                                $contenu = '                      Avertissementt : cellule ' . $H[$i]->getCoordinate() . ' : recouvrement ' . $H[$i]->getValue() . ' valeur non numérique. ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            } else {
                                $totRecouvrement = $totRecouvrement + intval($H[$i]->getCalculatedValue());
                            }
                        }
                    }
                    if ($totRecouvrement != 100) {
                        $erreur = true;
                        $contenu = '                     Erreur : la somme des recouvrements doit être égale à 100%. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }

                    // 	Substrats (encore) : Cellules D66 à D79, les valeurs doivent être dans la liste des substrats possibles 
                    $tabD = array();
                    for ($i = 66; $i < 80; $i++) {
                        $D[$i] = $worksheet->getCell('D' . $i);
                        if ($D[$i]->getValue() != '') {
                            $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('274', $D[$i]->getValue(), '13');
                            if (!$pgSandreHnNomemclature) {
                                $erreur = true;
                                $contenu = '                     Erreur : cellule ' . $D[$i]->getCoordinate() . ' :  substrat ' . $D[$i]->getValue() . ' impossible. ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        }
                    }

                    // 	Vitesses : Cellules E66 à E79, les valeurs doivent être dans la liste des vitesses possibles
                    $tabE = array();
                    for ($i = 66; $i < 80; $i++) {
                        $E[$i] = $worksheet->getCell('E' . $i);
                        if ($E[$i]->getValue() != '') {
                            $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('278', $E[$i]->getValue(), '13');
                            if (!$pgSandreHnNomemclature) {
                                $erreur = true;
                                $contenu = '                     Erreur : cellule ' . $E[$i]->getCoordinate() . ' :  classe vitesse ' . $E[$i]->getValue() . ' impossible. ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        }
                    }

                    // 	Phases : Cellules F66 à F79, les valeurs doivent être dans la liste des phases possibles
                    $tabF = array();
                    for ($i = 66; $i < 80; $i++) {
                        $F[$i] = $worksheet->getCell('F' . $i);
                        if ($F[$i]->getValue() != '') {
                            $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('480', $F[$i]->getValue(), '13');
                            if (!$pgSandreHnNomemclature) {
                                $erreur = true;
                                $contenu = '                     Erreur : cellule ' . $F[$i]->getCoordinate() . ' :  phase ' . $F[$i]->getValue() . ' impossible. ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        }
                    }

                    //  Dénombrements : pour chaque ligne xx à partir de la ligne 88, et jusqu’à rencontrer une cellule vide dans la colonne D 
                    $tabDenombrements = array();
                    for ($i = 88; $i < 1000; $i++) {
                        $tabDenombrements[$i] = $worksheet->getCell('D' . $i);
                        if ($tabDenombrements[$i]->getValue() != '') {
                            $pgSandreAppellationTaxon = $repoPgSandreAppellationTaxon->getPgSandreAppellationTaxonByCodeAppelTaxonCodeSupport($tabDenombrements[$i]->getValue(), '13');
                            if (!$pgSandreAppellationTaxon) {
                                $erreur = true;
                                $contenu = '                     Erreur : cellule ' . $tabDenombrements[$i]->getCoordinate() . ' : le code Sandre ' . $tabDenombrements[$i]->getValue() . ' ne faire partie de la liste des codes possibles pour le support 13. ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        } else {
                            break;
                        }
                    }

                    if (!$erreur) {
                        $contenu = '                     Correct ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }

                    $contenu = CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);

//                foreach ($worksheet->getRowIterator() as $row) {
//                    $contenu = '   Ligne n° - ' . $row->getRowIndex() . CHR(13) . CHR(10) . CHR(13) . CHR(10);
//                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
//                    fputs($rapport, $contenu);
//
//                    $cellIterator = $row->getCellIterator();
//                    $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
//                    foreach ($cellIterator as $cell) {
//                        if (!is_null($cell)) {
//                            $contenu = '        Cellule - ' . $cell->getCoordinate() . ' - ' . $cell->getCalculatedValue() . CHR(13) . CHR(10);
//                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
//                            fputs($rapport, $contenu);
//                        }
//                    }
//                }
                }
                $tabFeuillets[$feuil]['erreur'] = $erreur;
                $feuil++;
            }
        }

        $tabFichier['feuillet'] = $tabFeuillets;


        return $tabFichier;
    }

}
