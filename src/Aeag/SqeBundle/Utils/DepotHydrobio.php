<?php

namespace Aeag\SqeBundle\Utils;

use Aeag\SqeBundle\Entity\PgCmdPrelevHbInvert;
use Aeag\SqeBundle\Entity\PgCmdInvertRecouv;
use Aeag\SqeBundle\Entity\PgCmdInvertPrelem;
use Aeag\SqeBundle\Entity\PgCmdInvertListe;

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
        $repoPgCmdPrelevHbInvert = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevHbInvert');
        $repoPgCmdInvertRecouv = $emSqe->getRepository('AeagSqeBundle:PgCmdInvertRecouv');
        $repoPgCmdInvertPrelem = $emSqe->getRepository('AeagSqeBundle:PgCmdInvertPrelem');
        $repoPgCmdInvertListe = $emSqe->getRepository('AeagSqeBundle:PgCmdInvertListe');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

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
                $codeStation = $B23->getCalculatedValue();
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
                    $contenu = '                     Erreur : la station ' . $codeStation . ' ' . $worksheet->getCell('D23')->getCalculatedValue() . ' ne fait pas partie de cette demande' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } else {
                    // Coordonnées : cellules K24, L24, M24, N24 doivent être non vides, au format numérique et avec une valeur > 0
                    $K24 = $worksheet->getCell('K24');
                    $L24 = $worksheet->getCell('L24');
                    $M24 = $worksheet->getCell('M24');
                    $N24 = $worksheet->getCell('N24');

                    if (is_null($K24) or is_null($L24) or is_null($M24) or is_null($N24)) {
                        $avertissement = true;
                        $contenu = '                     Avertissement : Coordonnées Lambert 93 incorrectes ou non renseignées. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } elseif (!is_numeric($K24->getCalculatedValue()) or ! is_numeric($L24->getCalculatedValue()) or ! is_numeric($M24->getCalculatedValue()) or ! is_numeric($N24->getCalculatedValue())) {
                        $avertissement = true;
                        $contenu = '                     Avertissement : Coordonnées Lambert 93 incorrectes ou non renseignées. ';
                        $contenu = $contenu . ' K24 : ' . $K24->getCalculatedValue() . ' L24 : ' . $L24->getCalculatedValue() . ' M24 : ' . $M24->getCalculatedValue() . ' N24 : ' . $N24->getCalculatedValue() . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } elseif ($K24->getCalculatedValue() == 0 or $L24->getCalculatedValue() == 0 or $M24->getCalculatedValue() == 0 or $N24->getCalculatedValue() == 0) {
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
                        } elseif (!is_numeric($K23->getCalculatedValue()) or ! is_numeric($L23->getCalculatedValue()) or ! is_numeric($M23->getCalculatedValue()) or ! is_numeric($N23->getCalculatedValue())) {
                            $erreur = true;
                            $contenu = '                     Erreur : Coordonnées Lambert II incorrectes ou non renseignées. ';
                            $contenu = $contenu . ' K23 : ' . $K23->getCalculatedValue() . ' L23 : ' . $L23->getCalculatedValue() . ' M23 : ' . $M23->getCalculatedValue() . ' N23 : ' . $N23->getCalculatedValue() . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        } elseif ($K23->getCalculatedValue() == 0 or $L23->getCalculatedValue() == 0 or $M23->getCalculatedValue() == 0 or $N23->getCalculatedValue() == 0) {
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
                    } elseif (!is_numeric(intval($P23->getCalculatedValue()))) {
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
                    } elseif (!is_numeric(intval($E39->getCalculatedValue()))) {
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
                    } elseif (!is_numeric(intval($O23->getCalculatedValue()))) {
                        $avertissement = true;
                        $contenu = '                      Avertissementt : cellule ' . $PO23->getCoordinate() . 'incorrecte. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }

                    //  Substrats
                    $tabG = array();
                    for ($i = 39; $i < 51; $i++) {
                        $G[$i] = $worksheet->getCell('G' . $i);
                        $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('274', $G[$i]->getCalculatedValue(), '13');
                        if (!$pgSandreHnNomemclature) {
                            $erreur = true;
                            $contenu = '                     Erreur : cellule ' . $G[$i]->getCoordinate() . ' : substrat ' . $G[$i]->getCalculatedValue() . ' impossible. ' . CHR(13) . CHR(10);
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
                            if (!is_numeric(intval($H[$i]->getCalculatedValue()))) {
                                $avertissement = true;
                                $contenu = '                      Avertissementt : cellule ' . $H[$i]->getCoordinate() . ' : recouvrement ' . $H[$i]->getCalculatedValue() . ' valeur non numérique. ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            } else {
                                $totRecouvrement = $totRecouvrement + intval($H[$i]->getCalculatedValue());
                            }
                        }
                    }
                    if ($totRecouvrement != 100) {
                        $erreur = true;
                        $contenu = '                     Erreur : la somme des recouvrements ' . $totRecouvrement . ' doit être égale à 100. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }

                    // 	Substrats (encore) : Cellules D66 à D79, les valeurs doivent être dans la liste des substrats possibles 
                    $tabD = array();
                    for ($i = 66; $i < 79; $i++) {
                        $D[$i] = $worksheet->getCell('D' . $i);
                        if ($D[$i]->getCalculatedValue() != '') {
                            $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('274', $D[$i]->getCalculatedValue(), '13');
                            if (!$pgSandreHnNomemclature) {
                                $erreur = true;
                                $contenu = '                     Erreur : cellule ' . $D[$i]->getCoordinate() . ' :  substrat ' . $D[$i]->getCalculatedValue() . ' impossible. ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        }
                    }

                    // 	Vitesses : Cellules E66 à E79, les valeurs doivent être dans la liste des vitesses possibles
                    $tabE = array();
                    for ($i = 66; $i < 79; $i++) {
                        $E[$i] = $worksheet->getCell('E' . $i);
                        if ($E[$i]->getCalculatedValue() != '') {
                            $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('278', $E[$i]->getCalculatedValue(), '13');
                            if (!$pgSandreHnNomemclature) {
                                $erreur = true;
                                $contenu = '                     Erreur : cellule ' . $E[$i]->getCoordinate() . ' :  classe vitesse ' . $E[$i]->getCalculatedValue() . ' impossible. ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        }
                    }

                    // 	Phases : Cellules F66 à F79, les valeurs doivent être dans la liste des phases possibles
                    $tabF = array();
                    for ($i = 66; $i < 79; $i++) {
                        $F[$i] = $worksheet->getCell('F' . $i);
                        if ($F[$i]->getCalculatedValue() != '') {
                            $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('480', $F[$i]->getCalculatedValue(), '13');
                            if (!$pgSandreHnNomemclature) {
                                $erreur = true;
                                $contenu = '                     Erreur : cellule ' . $F[$i]->getCoordinate() . ' :  phase ' . $F[$i]->getCalculatedValue() . ' impossible. ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                        }
                    }

                    //  Dénombrements : pour chaque ligne xx à partir de la ligne 88, et jusqu’à rencontrer une cellule vide dans la colonne D 
                    $tabDenombrements = array();
                    for ($i = 88; $i < 1000; $i++) {
                        $tabDenombrements[$i] = $worksheet->getCell('D' . $i);
                        if ($tabDenombrements[$i]->getCalculatedValue() != '') {
                            $pgSandreAppellationTaxon = $repoPgSandreAppellationTaxon->getPgSandreAppellationTaxonByCodeAppelTaxonCodeSupport($tabDenombrements[$i]->getCalculatedValue(), '13');
                            if (!$pgSandreAppellationTaxon) {
                                $erreur = true;
                                $contenu = '                     Erreur : cellule ' . $tabDenombrements[$i]->getCoordinate() . ' : le code Sandre ' . $tabDenombrements[$i]->getCalculatedValue() . ' ne faire partie de la liste des codes possibles pour le support 13. ' . CHR(13) . CHR(10);
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
                        // enregistrement en base
                        // 
                        // Table pg_cmd_prelev_hb_invert 
                        $pgCmdPrelevHbInvert = $repoPgCmdPrelevHbInvert->getPgCmdPrelevHbInvertByPrelev($pgCmdPrelev);
                        if (!$pgCmdPrelevHbInvert) {
                            $pgCmdPrelevHbInvert = new PgCmdPrelevHbInvert();
                            $pgCmdPrelevHbInvert->setPrelev($pgCmdPrelev);
                        }
                        if (is_null($K24) or ! is_numeric($K24->getCalculatedValue()) or ( $K24->getCalculatedValue() == 0)) {
                            $pgCmdPrelevHbInvert->setXAmont($k23->getCalculatedValue());
                        } else {
                            $pgCmdPrelevHbInvert->setXAmont($K24->getCalculatedValue());
                        }
                        if (is_null($L24) or ! is_numeric($L24->getCalculatedValue()) or ( $L24->getCalculatedValue() == 0)) {
                            $pgCmdPrelevHbInvert->setYAmont($L23->getCalculatedValue());
                        } else {
                            $pgCmdPrelevHbInvert->setYAmont($L24->getCalculatedValue());
                        }
                        if (is_null($M24) or ! is_numeric($M24->getCalculatedValue()) or ( $M24->getCalculatedValue() == 0)) {
                            $pgCmdPrelevHbInvert->setXAvalt($M23->getCalculatedValue());
                        } else {
                            $pgCmdPrelevHbInvert->setXAval($M24->getCalculatedValue());
                        }
                        if (is_null($N24) or ! is_numeric($N24->getCalculatedValue()) or ( $N24->getCalculatedValue() == 0)) {
                            $pgCmdPrelevHbInvert->setYAval($N23->getCalculatedValue());
                        } else {
                            $pgCmdPrelevHbInvert->setYAval($N24->getCalculatedValue());
                        }
                        $pgCmdPrelevHbInvert->setLongueur($P23->getCalculatedValue());
                        $pgCmdPrelevHbInvert->setLargeurMoy($E39->getCalculatedValue());
                        $pgCmdPrelevHbInvert->setLargeurPb($O23->getCalculatedValue());
                        $emSqe->persist($pgCmdPrelevHbInvert);

                        // suppresion des enregistrements
                        $pgCmdInvertRecouvs = $repoPgCmdInvertRecouv->getPgCmdInvertRecouvByPrelev($pgCmdPrelevHbInvert);
                        foreach ($pgCmdInvertRecouvs as $pgCmdInvertRecouv) {
                            $emSqe->remove($pgCmdInvertRecouv);
                        }
                        $pgCmdInvertPrelems = $repoPgCmdInvertPrelem->getPgCmdInvertPrelemByPrelev($pgCmdPrelevHbInvert);
                        foreach ($pgCmdInvertPrelems as $pgCmdInvertPrelem) {
                            $emSqe->remove($pgCmdInvertPrelem);
                        }
                        $pgCmdInvertListes = $repoPgCmdInvertListe->getPgCmdInvertListeByPrelev($pgCmdPrelevHbInvert);
                        foreach ($pgCmdInvertListes as $pgCmdInvertListe) {
                            $emSqe->remove($pgCmdInvertListe);
                        }
                        $emSqe->flush();


                        // Table pg_cmd_invert_recouv : pour chaque ligne xx dans la zone G39 à H50 
                        //  Substrats

                        for ($i = 39; $i < 51; $i++) {
                            if (!is_null($H[$i])) {
                                $substrat = $G[$i]->getCalculatedValue();
                                $pgCmdInvertRecouv = new PgCmdInvertRecouv();
                                $pgCmdInvertRecouv->setPrelev($pgCmdPrelevHbInvert);
                                $pgCmdInvertRecouv->setSubstrat($substrat);
                                if (!is_null($H[$i])) {
                                    $pgCmdInvertRecouv->setRecouvrement($H[$i]->getCalculatedValue());
                                }
                                if (!is_null($H[$i]) and is_numeric($H[$i]->getCalculatedValue())) {
                                    $pgCmdInvertRecouv->setRecouvNum($H[$i]->getCalculatedValue());
                                }
                                $emSqe->persist($pgCmdInvertRecouv);
                            }
                        }

                        //Table pg_cmd_invert_prelem : pour chaque ligne xx dans la zone C66 à K79 

                        for ($i = 66; $i < 79; $i++) {
                            $mpc = $worksheet->getCell('C' . $i);
                            if ($mpc->getCalculatedValue() != '') {
                                $pgCmdInvertPrelem = new PgCmdInvertPrelem();
                                $pgCmdInvertPrelem->setPrelev($pgCmdPrelevHbInvert);
                                $pgCmdInvertPrelem->setPrelem($mpc->getCalculatedValue());
                                $mpd = $worksheet->getCell('D' . $i);
                                if (!is_null($mpd)) {
                                    $pgCmdInvertPrelem->setSubstrat($mpd->getCalculatedValue());
                                }
                                $mpe = $worksheet->getCell('E' . $i);
                                if (!is_null($mpe)) {
                                    $pgCmdInvertPrelem->setVitesse($mpe->getCalculatedValue());
                                }
                                $mpf = $worksheet->getCell('F' . $i);
                                if (!is_null($mpf)) {
                                    $pgCmdInvertPrelem->setPhase($mpf->getCalculatedValue());
                                }
                                $mpg = $worksheet->getCell('G' . $i);
                                if (!is_null($mpg) and is_numeric($mpg->getCalculatedValue())) {
                                    $pgCmdInvertPrelem->setHauteurEau($mpg->getCalculatedValue());
                                }
                                $mph = $worksheet->getCell('H' . $i);
                                if (!is_null($mph) and is_numeric($mph->getCalculatedValue())) {
                                    $pgCmdInvertPrelem->setColmatage($mph->getCalculatedValue());
                                }
                                $mpi = $worksheet->getCell('I' . $i);
                                if (!is_null($mpi)) {
                                    $pgCmdInvertPrelem->setStabilite($mpi->getCalculatedValue());
                                }
                                $mpj = $worksheet->getCell('J' . $i);
                                if (!is_null($mpj)) {
                                    $pgCmdInvertPrelem->setNatureVeget($mpj->getCalculatedValue());
                                }
                                $mpk = $worksheet->getCell('K' . $i);
                                if (!is_null($mpk)) {
                                    $pgCmdInvertPrelem->setAbondVeget($mpk->getCalculatedValue());
                                }
                                $emSqe->persist($pgCmdInvertPrelem);
                            }
                        }

                        // Table pg_cmd_invert_liste : pour chaque ligne xx à partir de la ligne 88, et jusqu’à rencontrer une cellule vide dans la colonne D

                        for ($i = 88; $i < 1000; $i++) {
                            $ecd = $worksheet->getCell('D' . $i);
                            if ($ecd->getCalculatedValue() != '') {
                                $tabPhase = array();
                                $j = 0;
                                if ($worksheet->getCell('E' . $i)->getCalculatedValue() != '') {
                                    $tabPhase[$j]['nom'] = 'PHA';
                                    $tabPhase[$j]['valeur'] = $worksheet->getCell('E' . $i)->getCalculatedValue();
                                    $j++;
                                }
                                if ($worksheet->getCell('F' . $i)->getCalculatedValue() != '') {
                                    $tabPhase[$j]['nom'] = 'PHB';
                                    $tabPhase[$j]['valeur'] = $worksheet->getCell('F' . $i)->getCalculatedValue();
                                    $j++;
                                }
                                if ($worksheet->getCell('G' . $i)->getCalculatedValue() != '') {
                                    $tabPhase[$j]['nom'] = 'PHC';
                                    $tabPhase[$j]['valeur'] = $worksheet->getCell('G' . $i)->getCalculatedValue();
                                    $j++;
                                }
                                $tabQE = array();
                                $k = 0;
                                if ($worksheet->getCell('H' . $i)->getCalculatedValue() != '') {
                                    $tabQE[$k]['nom'] = 'P1';
                                    $tabQE[$k]['valeur'] = $worksheet->getCell('H' . $i)->getCalculatedValue();
                                    $k++;
                                }
                                if ($worksheet->getCell('I' . $i)->getCalculatedValue() != '') {
                                    $tabQE[$k]['nom'] = 'P2';
                                    $tabQE[$k]['valeur'] = $worksheet->getCell('I' . $i)->getCalculatedValue();
                                    $k++;
                                }
                                if ($worksheet->getCell('J' . $i)->getCalculatedValue() != '') {
                                    $tabQE[$k]['nom'] = 'P3';
                                    $tabQE[$k]['valeur'] = $worksheet->getCell('J' . $i)->getCalculatedValue();
                                    $k++;
                                }
                                if ($worksheet->getCell('K' . $i)->getCalculatedValue() != '') {
                                    $tabQE[$k]['nom'] = 'P4';
                                    $tabQE[$k]['valeur'] = $worksheet->getCell('K' . $i)->getCalculatedValue();
                                    $k++;
                                }
                                if ($worksheet->getCell('L' . $i)->getCalculatedValue() != '') {
                                    $tabQE[$k]['nom'] = 'P5';
                                    $tabQE[$k]['valeur'] = $worksheet->getCell('L' . $i)->getCalculatedValue();
                                    $k++;
                                }
                                if ($worksheet->getCell('M' . $i)->getCalculatedValue() != '') {
                                    $tabQE[$k]['nom'] = 'P6';
                                    $tabQE[$k]['valeur'] = $worksheet->getCell('M' . $i)->getCalculatedValue();
                                    $k++;
                                }
                                if ($worksheet->getCell('N' . $i)->getCalculatedValue() != '') {
                                    $tabQE[$k]['nom'] = 'P7';
                                    $tabQE[$k]['valeur'] = $worksheet->getCell('N' . $i)->getCalculatedValue();
                                    $k++;
                                }
                                if ($worksheet->getCell('O' . $i)->getCalculatedValue() != '') {
                                    $tabQE[$k]['nom'] = 'P8';
                                    $tabQE[$k]['valeur'] = $worksheet->getCell('O' . $i)->getCalculatedValue();
                                    $k++;
                                }
                                if ($worksheet->getCell('P' . $i)->getCalculatedValue() != '') {
                                    $tabQE[$k]['nom'] = 'P9';
                                    $tabQE[$k]['valeur'] = $worksheet->getCell('P' . $i)->getCalculatedValue();
                                    $k++;
                                }
                                if ($worksheet->getCell('Q' . $i)->getCalculatedValue() != '') {
                                    $tabQE[$k]['nom'] = 'P10';
                                    $tabQE[$k]['valeur'] = $worksheet->getCell('Q' . $i)->getCalculatedValue();
                                    $k++;
                                }
                                if ($worksheet->getCell('R' . $i)->getCalculatedValue() != '') {
                                    $tabQE[$k]['nom'] = 'P11';
                                    $tabQE[$k]['valeur'] = $worksheet->getCell('R' . $i)->getCalculatedValue();
                                    $k++;
                                }
                                if ($worksheet->getCell('S' . $i)->getCalculatedValue() != '') {
                                    $tabQE[$k]['nom'] = 'P12';
                                    $tabQE[$k]['valeur'] = $worksheet->getCell('S' . $i)->getCalculatedValue();
                                    $k++;
                                }
                                for ($jj = 0; $jj < count($tabPhase); $jj++) {
                                    $pgCmdInvertListe = new PgCmdInvertListe();
                                    $pgCmdInvertListe->setPrelev($pgCmdPrelevHbInvert);
                                    $pgCmdInvertListe->setPhase($tabPhase[$jj]['nom']);
                                    $pgCmdInvertListe->setCodeSandre($ecd->getCalculatedValue());
                                    $qec = $worksheet->getCell('C' . $i);
                                    $pgCmdInvertListe->setTaxon($qec->getCalculatedValue());
                                    $pgCmdInvertListe->setDenombrement($tabPhase[$jj]['valeur']);
                                    $emSqe->persist($pgCmdInvertListe);
                                }
                                for ($kk = 0; $kk < count($tabQE); $kk++) {
                                    $pgCmdInvertListe = new PgCmdInvertListe();
                                    $pgCmdInvertListe->setPrelev($pgCmdPrelevHbInvert);
                                    $pgCmdInvertListe->setPrelem($tabQE[$kk]['nom']);
                                    $pgCmdInvertListe->setCodeSandre($ecd->getCalculatedValue());
                                    $qec = $worksheet->getCell('C' . $i);
                                    $pgCmdInvertListe->setTaxon($qec->getCalculatedValue());
                                    $pgCmdInvertListe->setDenombrement($tabQE[$kk]['valeur']);
                                    $emSqe->persist($pgCmdInvertListe);
                                }
                            } else {
                                break;
                            }
                        }
                        $pgCmdPrelev->setDatePrelev(new \DateTime());
                        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');
                        $pgCmdPrelev->setPhaseDmd($pgProgPhases);
                    } else {
                        $pgCmdPrelev->setDatePrelev(new \DateTime());
                        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R80');
                        $pgCmdPrelev->setPhaseDmd($pgProgPhases);
                    }

                    $emSqe->flush();

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
