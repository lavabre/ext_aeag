<?php

namespace Aeag\SqeBundle\Utils;

use Aeag\SqeBundle\Entity\PgCmdSuiviPrel;
use Aeag\SqeBundle\Entity\PgCmdPrelevHbInvert;
use Aeag\SqeBundle\Entity\PgCmdInvertRecouv;
use Aeag\SqeBundle\Entity\PgCmdInvertPrelem;
use Aeag\SqeBundle\Entity\PgCmdInvertListe;
use Aeag\SqeBundle\Entity\PgCmdPrelevHbDiato;
use Aeag\SqeBundle\Entity\PgCmdDiatoListe;

class DepotHydrobio {

    public function extraireFichier($demandeId, $emSqe, $pgCmdFichierRps, $pathBase, $nomFichier, $excelObj) {

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
            if (is_file($pathBase . '/' . $nomFichier)) {
                $pos1 = stripos($nomFichier, '.');
                if ($pos1) {
                    $tabNomFichier = explode('.', $nomFichier);
                    if (strtoupper($tabNomFichier[1]) == 'XLS') {
                        $tabFichier = $this->lireXls($pgCmdFichierRps->getId(), $demandeId, $excelObj, $nomFichier, $pathBase, $rapport, $emSqe);
                        $tabTraitFichiers[$fic] = $tabFichier;
                        $fic++;
                    }
                    if (strtoupper($tabNomFichier[1]) == 'PRN') {
                        $tabFichier = $this->lirePrn($demandeId, $nomFichier, $pathBase, $rapport, $emSqe);
                        $tabTraitFichiers[$fic] = $tabFichier;
                        $fic++;
                    }
                    if (strtoupper($tabNomFichier[1]) == 'PDF') {
                        $tabFichier = $this->lirePdf($pgCmdFichierRps->getId(), $demandeId, $nomFichier, $pathBase, $rapport, $emSqe);
                        $tabTraitFichiers[$fic] = $tabFichier;
                        $fic++;
                    }
                    if (strtoupper($tabNomFichier[1]) == 'JPG') {
                        $tabFichier = $this->lireJpg($pgCmdFichierRps->getId(), $demandeId, $nomFichier, $pathBase, $rapport, $emSqe);
                        $tabTraitFichiers[$fic] = $tabFichier;
                        $fic++;
                    }
                }
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

    protected function lireXls($pgCmdFichierRpsId, $demandeId, $excelObj, $nomFichier, $pathBase, $rapport, $emSqe) {

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
            if ($worksheet->getCell('B21') == 'CODE STATION') {
                $tabFeuillets = $this->lireXlsSupport10($worksheet, $feuil, $tabFeuillets, $rapport, $pgCmdFichierRpsId, $demandeId, $emSqe);
                $feuil++;
            }
            if ($worksheet->getCell('B22') == 'CODE STATION') {
                $tabFeuillets = $this->lireXlsSupport13($worksheet, $feuil, $tabFeuillets, $rapport, $pgCmdFichierRpsId, $demandeId, $emSqe);
                $feuil++;
            }
        }

        $tabFichier['feuillet'] = $tabFeuillets;
        $tabFichier['erreur'] = false;
        for ($feuil = 0; $feuil < count($tabFeuillets); $feuil++) {
            if ($tabFeuillets[$feuil]['erreur']) {
                $tabFichier['erreur'] = true;
                break;
            }
        }

        return $tabFichier;
    }

    protected function lireXlsSupport10($worksheet, $feuil, $tabFeuillets, $rapport, $pgCmdFichierRpsId, $demandeId, $emSqe) {
        // Récupération des programmations
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebUsers');
        $repoPgSandreHbNomemclatures = $emSqe->getRepository('AeagSqeBundle:PgSandreHbNomemclatures');
        $repoPgCmdFichierRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgCmdPrelevHbDiato = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevHbDiato');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgCmdFichierRps = $repoPgCmdFichierRps->findOneById($pgCmdFichierRpsId);
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
        $pgProgWebUser = $repoPgProgWebUsers->findOneByPrestataire($pgCmdDemande->getPrestataire());


        $tabFeuillets[$feuil]['feuillet'] = $worksheet->getTitle();
        $contenu = '          Feuillet : ' . $worksheet->getTitle() . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);

        $avertissement = false;
        $erreur = false;

        // station
        $B22 = $worksheet->getCell('B22');
        $codeStation = $B22->getCalculatedValue();
        $trouve = false;
        foreach ($pgCmdPrelevs as $pgCmdPrelev) {
            if (substr($pgCmdPrelev->getStation()->getCode(), -(strlen($codeStation))) == $codeStation) {
                $trouve = true;
                if ($pgCmdPrelev->getPhaseDmd()->getCodePhase() == 'M40') {
                    $contenu = '                     Avertissement : la station ' . $codeStation . ' ' . $pgCmdPrelev->getStation()->getLibelle() . ' à déjà été traitée.' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
                break;
            }
        }
        if (!$trouve) {
            $erreur = true;
            $contenu = '                     Erreur : la station ' . $codeStation . ' ' . $worksheet->getCell('D22')->getCalculatedValue() . ' ne fait pas partie de cette demande' . CHR(13) . CHR(10);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            fputs($rapport, $contenu);
        } else {

            // Coordonnées : cellules G23, H23 doivent être non vides, au format numérique et avec une valeur > 0
            $G23 = str_replace(',', '.', $worksheet->getCell('G23'));
            $H23 = str_replace(',', '.', $worksheet->getCell('H23'));
            $avertissement = false;
            if (is_null($G23) or is_null($H23)) {
                $avertissement = true;
                $contenu = '                     Avertissement : Coordonnées Lambert 93 incorrectes ou non renseignées. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif (!is_numeric($G23->getCalculatedValue()) or ! is_numeric($H23->getCalculatedValue())) {
                $avertissement = true;
                $contenu = '                     Avertissement : Coordonnées Lambert 93 incorrectes ou non renseignées. ';
                $contenu = $contenu . ' G23 : ' . $G23->getCalculatedValue() . ' H23 : ' . $H23->getCalculatedValue() . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif ($G23->getCalculatedValue() == 0 or $H23->getCalculatedValue() == 0) {
                $avertissement = true;
                $contenu = '                     Avertissementt : Coordonnées Lambert 93 incorrectes ou non renseignées. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }

            if ($avertissement) {
                // Coordonnées : cellules G22, H22 doivent être non vides, au format numérique et avec une valeur > 0
                $G22 = str_replace(',', '.', $worksheet->getCell('G22'));
                $H22 = str_replace(',', '.', $worksheet->getCell('H22'));
                if (is_null($G22) or is_null($H22)) {
                    $erreur = true;
                    $contenu = '                     Erreur : Coordonnées Lambert II incorrectes ou non renseignées. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (!is_numeric($G22->getCalculatedValue()) or ! is_numeric($H22->getCalculatedValue())) {
                    $erreur = true;
                    $contenu = '                     Erreur : Coordonnées Lambert II incorrectes ou non renseignées. ';
                    $contenu = $contenu . ' G22 : ' . $G22->getCalculatedValue() . ' H22 : ' . $H22->getCalculatedValue() . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif ($G22->getCalculatedValue() == 0 or $H223->getCalculatedValue() == 0) {
                    $erreur = true;
                    $contenu = '                     Erreur : Coordonnées Lambert II incorrectes ou non renseignées. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            // Autres valeurs du prélèvement : cellules E40, F40, A53, B53, C53  doivent être non vide, au format numérique
            $E40 = str_replace(',', '.', $worksheet->getCell('E40'));
            $F40 = str_replace(',', '.', $worksheet->getCell('F40'));
            $A53 = str_replace(',', '.', $worksheet->getCell('A53'));
            $B53 = str_replace(',', '.', $worksheet->getCell('B53'));
            $C53 = str_replace(',', '.', $worksheet->getCell('C53'));
            if (is_null($E40)) {
                $erreur = true;
                $contenu = '                     Avertissementt : cellule ' . $E40->getCoordinate() . ' non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif (!strtoupper(substr($E40->getCalculatedValue(), 1)) == 'TIAGE' or ! strtoupper($E40->getCalculatedValue()) == 'CRU') {
                $avertissement = true;
                $contenu = '                      Avertissementt : cellule ' . $E40->getCoordinate() . ' valeur : ' . $E40->getCalculatedValue() . ' incorrecte. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }

            if (is_null($F40)) {
                $erreur = true;
                $contenu = '                      Avertissementt : cellule ' . $F40->getCoordinate() . ' incorrecte ou non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif (!is_numeric(intval($F40->getCalculatedValue()))) {
                $erreur = true;
                $contenu = '                     Avertissementt : cellule ' . $F40->getCoordinate() . 'incorrecte. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif ($F40->getCalculatedValue() == 0) {
                $erreur = true;
                $contenu = '                      Avertissementt : cellule ' . $F40->getCoordinate() . ' valeur : ' . $F40->getCalculatedValue() . ' incorrecte. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }

            if (is_null($A53)) {
                $erreur = true;
                $contenu = '                     Avertissementt : cellule ' . $A53->getCoordinate() . 'incorrecte ou non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } else {
                $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('274', $A53->getCalculatedValue(), '10');
                if (!$pgSandreHnNomemclature) {
                    $erreur = true;
                    $contenu = '                     Erreur : cellule ' . $A53->getCoordinate() . ' :  substrat ' . $A53->getCalculatedValue() . ' impossible. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            if (is_null($B53)) {
                $erreur = true;
                $contenu = '                     Avertissementt : cellule ' . $B53->getCoordinate() . 'incorrecte ou non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } else {
                $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('278', $B53->getCalculatedValue(), '10');
                if (!$pgSandreHnNomemclature) {
                    $erreur = true;
                    $contenu = '                     Erreur : cellule ' . $A53->getCoordinate() . ' :  classe vitesse  ' . $B53->getCalculatedValue() . ' impossible. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            if (is_null($C53)) {
                $erreur = true;
                $contenu = '                     Avertissementt : cellule ' . $C53->getCoordinate() . 'incorrecte ou non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif (!strtoupper($C53->getCalculatedValue()) == 'OUVERT' or ! strtoupper($C53->getCalculatedValue()) == 'SEMI-OUVERT' or ! strtoupper(substr($C53->getCalculatedValue(), 0, 4)) == 'FERM') {
                $erreur = true;
                $contenu = '                      Avertissementt : cellule ' . $C53->getCoordinate() . ' valeur : ' . $C53->getCalculatedValue() . ' incorrecte. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }

            //    Cellules B40, C40, D40  les valeurs doivent être numériques si non vides
            $B40 = str_replace(',', '.', $worksheet->getCell('B40'));
            $C40 = str_replace(',', '.', $worksheet->getCell('C40'));
            $D40 = str_replace(',', '.', $worksheet->getCell('D40'));
            if (!is_null($B40)) {
                if (!is_numeric(intval($B40->getCalculatedValue()))) {
                    $avertissement = true;
                    $contenu = '                      Avertissementt : cellule ' . $B40->getCoordinate() . ' : température ' . $B40->getCalculatedValue() . ' valeur non numérique. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            if (!is_null($C40)) {
                if (!is_numeric(intval($C40->getCalculatedValue()))) {
                    $avertissement = true;
                    $contenu = '                      Avertissementt : cellule ' . $C40->getCoordinate() . ' : ph ' . $C40->getCalculatedValue() . ' valeur non numérique. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            if (!is_null($D40)) {
                if (!is_numeric(intval($D40->getCalculatedValue()))) {
                    $avertissement = true;
                    $contenu = '                      Avertissementt : cellule ' . $D40->getCoordinate() . ' : conductivite ' . $D40->getCalculatedValue() . ' valeur non numérique. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            if (!$erreur) {
                $contenu = '                     Correct ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
                // enregistrement en base
                //
                // table pg_cmd_suivi_prel
                $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevStatutPrel($pgCmdPrelev, 'D');
                if (!$pgCmdSuiviPrel) {
                    $pgCmdSuiviPrel = new PgCmdSuiviPrel();
                    $pgCmdSuiviPrel->setPrelev($pgCmdPrelev);
                    $pgCmdSuiviPrel->setFichierRps($pgCmdFichierRps);
                    $pgCmdSuiviPrel->setUser($pgProgWebUser);
                    $pgCmdSuiviPrel->setDatePrel($pgCmdPrelev->getDatePrelev());
                    $pgCmdSuiviPrel->setStatutPrel('D');
                    $pgCmdSuiviPrel->setCommentaire('Dépôt hydrobio');
                    $pgCmdSuiviPrel->setValidation('E');
                } else {
                    $pgCmdSuiviPrel->setFichierRps($pgCmdFichierRps);
                    $pgCmdSuiviPrel->setCommentaire('Dépôt hydrobio');
                    $pgCmdSuiviPrel->setValidation('E');
                }
                $emSqe->persist($pgCmdSuiviPrel);
                //
                // Table pg_cmd_prelev_hb_ diato
                $pgCmdPrelevHbDiato = $repoPgCmdPrelevHbDiato->getPgCmdPrelevHbDiatoByPrelev($pgCmdPrelev);
                if (!$pgCmdPrelevHbDiato) {
                    $pgCmdPrelevHbDiato = new PgCmdPrelevHbDiato();
                    $pgCmdPrelevHbDiato->setPrelev($pgCmdPrelev);
                }
                if (is_null($G23) or ! is_numeric($G23->getCalculatedValue()) or ( $G23->getCalculatedValue() == 0)) {
                    $pgCmdPrelevHbDiato->setXPrel($G22->getCalculatedValue());
                } else {
                    $pgCmdPrelevHbDiato->setXPrel($G23->getCalculatedValue());
                }
                if (is_null($H23) or ! is_numeric($H23->getCalculatedValue()) or ( $H23->getCalculatedValue() == 0)) {
                    $pgCmdPrelevHbDiato->setYPrel($H22->getCalculatedValue());
                } else {
                    $pgCmdPrelevHbDiato->setYPrel($H23->getCalculatedValue());
                }

                if (!is_null($B40)) {
                    if (is_numeric(intval($B40->getCalculatedValue()))) {
                        $pgCmdPrelevHbDiato->setTempEau($B40->getCalculatedValue());
                    }
                }
                if (!is_null($C40)) {
                    if (is_numeric(intval($C40->getCalculatedValue()))) {
                        $pgCmdPrelevHbDiato->setPh($C40->getCalculatedValue());
                    }
                }
                if (!is_null($D40)) {
                    if (is_numeric(intval($D40->getCalculatedValue()))) {
                        $pgCmdPrelevHbDiato->setConductivite($D40->getCalculatedValue());
                    }
                }

                if (!is_null($F40)) {
                    if (is_numeric(intval($F40->getCalculatedValue()))) {
                        if ($F40->getCalculatedValue() != 0) {
                            $pgCmdPrelevHbDiato->setLargeur($F40->getCalculatedValue());
                        }
                    }
                }

                $pgCmdPrelevHbDiato->setSubstrat($A53->getCalculatedValue());
                $pgCmdPrelevHbDiato->setVitesse($B53->getCalculatedValue());
                $pgCmdPrelevHbDiato->setOmbrage($C53->getCalculatedValue());
                $pgCmdPrelevHbDiato->setConditionsHydro($E40->getCalculatedValue());

                $emSqe->persist($pgCmdPrelevHbDiato);

                //$pgCmdPrelev->setDatePrelev(new \DateTime());
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            }

            $emSqe->flush();


            $contenu = CHR(13) . CHR(10);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            fputs($rapport, $contenu);
        }
        $tabFeuillets[$feuil]['erreur'] = $erreur;
        $feuil++;
        return $tabFeuillets;
    }

    protected function lireXlsSupport13($worksheet, $feuil, $tabFeuillets, $rapport, $pgCmdFichierRpsId, $demandeId, $emSqe) {
        // Récupération des programmations
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebUsers');
        $repoPgSandreHbNomemclatures = $emSqe->getRepository('AeagSqeBundle:PgSandreHbNomemclatures');
        $repoPgSandreAppellationTaxon = $emSqe->getRepository('AeagSqeBundle:PgSandreAppellationTaxon');
        $repoPgCmdFichierRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgCmdPrelevHbInvert = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevHbInvert');
        $repoPgCmdInvertRecouv = $emSqe->getRepository('AeagSqeBundle:PgCmdInvertRecouv');
        $repoPgCmdInvertPrelem = $emSqe->getRepository('AeagSqeBundle:PgCmdInvertPrelem');
        $repoPgCmdInvertListe = $emSqe->getRepository('AeagSqeBundle:PgCmdInvertListe');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');

        $pgCmdFichierRps = $repoPgCmdFichierRps->findOneById($pgCmdFichierRpsId);
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgProgLotAn = $pgCmdDemande->getLotan();
        $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
        $pgProgWebUser = $repoPgProgWebUsers->findOneByPrestataire($pgCmdDemande->getPrestataire());

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
            $pgRefStationMesure = $pgCmdPrelev->getStation();
            if (substr($pgCmdPrelev->getStation()->getCode(), -(strlen($codeStation))) == $codeStation) {
                $trouve = true;
                if ($pgCmdPrelev->getPhaseDmd()->getCodePhase() == 'M40') {
                    //  $avertissement = true;
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
            $K24 = str_replace(',', '.', $worksheet->getCell('K24'));
            $L24 = str_replace(',', '.', $worksheet->getCell('L24'));
            $M24 = str_replace(',', '.', $worksheet->getCell('M24'));
            $N24 = str_replace(',', '.', $worksheet->getCell('N24'));
            $avertissement = false;
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
                $K23 = str_replace(',', '.', $worksheet->getCell('K23'));
                $L23 = str_replace(',', '.', $worksheet->getCell('L23'));
                $M23 = str_replace(',', '.', $worksheet->getCell('M23'));
                $N23 = str_replace(',', '.', $worksheet->getCell('N23'));
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
            $P23 = str_replace(',', '.', $worksheet->getCell('P23'));
            $E39 = str_replace(',', '.', $worksheet->getCell('E39'));
            $O23 = str_replace(',', '.', $worksheet->getCell('O23'));
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
            if (is_null($E39) or $E39->getCalculatedValue() == '') {
                $avertissement = true;
                $contenu = '                      Avertissementt : cellule ' . $E39->getCoordinate() . ' incorrecte ou non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif (!is_numeric(intval($E39->getCalculatedValue()))) {
                $avertissement = true;
                $contenu = '                     Avertissementt : cellule ' . $E39->getCoordinate() . 'incorrecte. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }
            if (is_null($O23)) {
                $avertissement = true;
                $contenu = '                     Avertissementt : cellule ' . $O23->getCoordinate() . 'incorrecte ou non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif (!is_numeric(intval($O23->getCalculatedValue()))) {
                $avertissement = true;
                $contenu = '                      Avertissementt : cellule ' . $O23->getCoordinate() . 'incorrecte. ' . CHR(13) . CHR(10);
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
            for ($i = 39; $i <= 50; $i++) {
                $H[$i] = str_replace(',', '.', $worksheet->getCell('H' . $i));
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
                } else {
                    $trouve = false;
                    for ($j = 39; $j <= 50; $j++) {
                        if ($D[$i]->getCalculatedValue() == $worksheet->getCell('F' . $j)->getCalculatedValue()) {
                            $trouve = true;
                            break;
                        }
                    }
                    if (!$trouve) {
                        $erreur = true;
                        $contenu = '                     Erreur : cellule ' . $D[$i]->getCoordinate() . ' :  substrat ' . $D[$i]->getCalculatedValue() . ' plan d’échantillonnage non identifié dans la mosaïque de substrats de la station (notés dans les colonnes F39 et F50. ' . CHR(13) . CHR(10);
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

            // 	Hauteur d'eau : Cellules G66 àG79, les valeurs doivent être renseignées et différentes de 0
            $tabG = array();
            for ($i = 66; $i < 79; $i++) {
                $G[$i] = $worksheet->getCell('H' . $i);
                if (is_null($G[$i])) {
                    $avertissement = true;
                    $contenu = '                      Avertissementt : cellule ' . $G[$i]->getCoordinate() . ' non renseignée. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (!is_numeric(intval($G[$i]->getCalculatedValue()))) {
                    $avertissement = true;
                    $contenu = '                     Avertissementt : cellule ' . $G[$i]->getCoordinate() . 'incorrecte. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (intval($G[$i]->getCalculatedValue()) == 0) {
                    $avertissement = true;
                    $contenu = '                     Avertissementt : cellule ' . $G[$i]->getCoordinate() . 'doit être > 0. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            // 	Intensite du colmatage : Cellules H66 à H79, les valeurs doivent être renseignées et différentes de 0
            $tabH = array();
            for ($i = 66; $i < 79; $i++) {
                $H[$i] = $worksheet->getCell('H' . $i);
                if (is_null($H[$i])) {
                    $avertissement = true;
                    $contenu = '                      Avertissementt : cellule ' . $H[$i]->getCoordinate() . ' non renseignée. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (!is_numeric(intval($H[$i]->getCalculatedValue()))) {
                    $avertissement = true;
                    $contenu = '                     Avertissementt : cellule ' . $H[$i]->getCoordinate() . 'incorrecte. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (intval($H[$i]->getCalculatedValue()) == 0) {
                    $avertissement = true;
                    $contenu = '                     Avertissementt : cellule ' . $H[$i]->getCoordinate() . 'doit être > 0. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            //  Dénombrements : pour chaque ligne xx à partir de la ligne 88, et jusqu’à rencontrer une cellule vide dans la colonne D
            $tabDenombrements = array();
            for ($i = 88; $i < 1000; $i++) {
                $tabDenombrements[$i] = $worksheet->getCell('D' . $i);
                $celE = $worksheet->getCell('E' . $i);
                $celF = $worksheet->getCell('F' . $i);
                $celG = $worksheet->getCell('G' . $i);
                if ($tabDenombrements[$i]->getCalculatedValue() != '') {
                    $pgSandreAppellationTaxon = $repoPgSandreAppellationTaxon->getPgSandreAppellationTaxonByCodeAppelTaxonCodeSupport($tabDenombrements[$i]->getCalculatedValue(), '13');
                    if (!$pgSandreAppellationTaxon) {
                        $erreur = true;
                        $contenu = '                     Erreur : cellule ' . $tabDenombrements[$i]->getCoordinate() . ' : le code Sandre ' . $tabDenombrements[$i]->getCalculatedValue() . ' ne faire partie de la liste des codes possibles pour le support 13. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } else {
                        if ($celE->getCalculatedValue() == "" and $celF->getCalculatedValue() == "" and $celG->getCalculatedValue() == "") {
                            $erreur = true;
                            $contenu = '                     Erreur : cellule ' . $tabDenombrements[$i]->getCoordinate() . ' : le code Sandre ' . $tabDenombrements[$i]->getCalculatedValue() . ' n’est pas dénombré dans les phases A, B et C . ' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
                        if ($pgProgLotStationAn) {
                            $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
                            if ($pgRefReseauMesure->getCodeAeagRsx == '099') {
                                $celH = $worksheet->getCell('H' . $i);
                                $celI = $worksheet->getCell('I' . $i);
                                $celJ = $worksheet->getCell('J' . $i);
                                $celK = $worksheet->getCell('K' . $i);
                                $celL = $worksheet->getCell('L' . $i);
                                $celM = $worksheet->getCell('M' . $i);
                                $celN = $worksheet->getCell('N' . $i);
                                $celO = $worksheet->getCell('O' . $i);
                                $celP = $worksheet->getCell('P' . $i);
                                $celQ = $worksheet->getCell('Q' . $i);
                                $celR = $worksheet->getCell('R' . $i);
                                $celS = $worksheet->getCell('S' . $i);
                                if ($celH->getCalculatedValue() == "" and $celI->getCalculatedValue() == "" and $celJ->getCalculatedValue() == "" and
                                        $celK->getCalculatedValue() == "" and $celL->getCalculatedValue() == "" and $celM->getCalculatedValue() == "" and
                                        $celN->getCalculatedValue() == "" and $celP->getCalculatedValue() == "" and $celP->getCalculatedValue() == "" and
                                        $celQ->getCalculatedValue() == "" and $celR->getCalculatedValue() == "" and $celS->getCalculatedValue() == "") {
                                    $erreur = true;
                                    $contenu = '                     Erreur : cellule ' . $tabDenombrements[$i]->getCoordinate() . ' : le code Sandre ' . $tabDenombrements[$i]->getCalculatedValue() . ' n’est pas dénombré dans les microprélèvements P1 à P12. ' . CHR(13) . CHR(10);
                                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                    fputs($rapport, $contenu);
                                }
                            }
                        }
                    }
                } else {
                    break;
                }
            }

//            if (!$erreur){
//                //  Règle générale phase A
//                $tabA = array();
//                $tabA['PhA'] = array();
//                $tabA['PhB'] = array();
//                $tabA['PhC'] = array();
//                 for ($i = 66; $i < 79; $i++) {
//                     if ($worksheet->getCell('H' . $i)->getCalculatedValue())
//                $H[$i] = $worksheet->getCell('H' . $i);
//                 }
//            }

            if (!$erreur) {
                $contenu = '                     Correct ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
                // enregistrement en base
                // table pg_cmd_suivi_prel
                $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevStatutPrel($pgCmdPrelev, 'D');
                if (!$pgCmdSuiviPrel) {
                    $pgCmdSuiviPrel = new PgCmdSuiviPrel();
                    $pgCmdSuiviPrel->setPrelev($pgCmdPrelev);
                    $pgCmdSuiviPrel->setFichierRps($pgCmdFichierRps);
                    $pgCmdSuiviPrel->setUser($pgProgWebUser);
                    $pgCmdSuiviPrel->setDatePrel($pgCmdPrelev->getDatePrelev());
                    $pgCmdSuiviPrel->setStatutPrel('D');
                    $pgCmdSuiviPrel->setCommentaire('Dépôt hydrobio');
                    $pgCmdSuiviPrel->setValidation('E');
                } else {
                    $pgCmdSuiviPrel->setFichierRps($pgCmdFichierRps);
                    $pgCmdSuiviPrel->setCommentaire('Dépôt hydrobio');
                    $pgCmdSuiviPrel->setValidation('E');
                }
                $emSqe->persist($pgCmdSuiviPrel);


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
                $pgCmdPrelevHbInvert->setLargeurMoy(str_replace(',', '.', $E39->getCalculatedValue()));
                $pgCmdPrelevHbInvert->setLargeurPb(str_replace(',', '.', $O23->getCalculatedValue()));
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
                            $pgCmdInvertListe->setDenombrement(intval($tabPhase[$jj]['valeur']));
                            $emSqe->persist($pgCmdInvertListe);
                        }
                        for ($kk = 0; $kk < count($tabQE); $kk++) {
                            $pgCmdInvertListe = new PgCmdInvertListe();
                            $pgCmdInvertListe->setPrelev($pgCmdPrelevHbInvert);
                            $pgCmdInvertListe->setPrelem($tabQE[$kk]['nom']);
                            $pgCmdInvertListe->setCodeSandre($ecd->getCalculatedValue());
                            $qec = $worksheet->getCell('C' . $i);
                            $pgCmdInvertListe->setTaxon($qec->getCalculatedValue());
                            $pgCmdInvertListe->setDenombrement(intval($tabQE[$kk]['valeur']));
                            $emSqe->persist($pgCmdInvertListe);
                        }
                    } else {
                        break;
                    }
                }
                // $pgCmdPrelev->setDatePrelev(new \DateTime());
                $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M40');
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
            } else {
                // $pgCmdPrelev->setDatePrelev(new \DateTime());
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
        return $tabFeuillets;
    }

    protected function lirePrn($demandeId, $nomFichier, $pathBase, $rapport, $emSqe) {

        // Récupération des programmations
        $repoPgSandreCodesAlternAppelTaxon = $emSqe->getRepository('AeagSqeBundle:PgSandreCodesAlternAppelTaxon');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdPrelevHbDiato = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelevHbDiato');
        $repoPgCmdDiatoListe = $emSqe->getRepository('AeagSqeBundle:PgCmdDiatoListe');

        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);

        $tabFichier = array();
        $tabFichier['fichier'] = $nomFichier;

        $contenu = '     fichier : ' . $nomFichier . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);
        $contenu = CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);

        $fichier = fopen($pathBase . '/' . $nomFichier, "r");
        $codeStation = null;
        $codeSandre = null;
        $taxon = null;
        $codeAltern = null;
        $denombrement = null;
        $feuil = 0;
        $tabFeuillets = array();
        $nbl = 0;
        while (!feof($fichier)) {
            $ligne = fgets($fichier, 1024);
            $nbl++;
            if (strlen($ligne) > 0) {
                $tab = explode("\t", $ligne);
                if (strlen($tab[0]) > 0) {
                    $tab1 = explode('*', $tab[0]);
                    if (strlen($codeStation) > 0 and $codeStation != $tab1[5]) {
                        if (!$erreur) {
//                            $contenu = '                     Correct ' . CHR(13) . CHR(10);
//                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
//                            fputs($rapport, $contenu);
                            // enregistrement en base
                            //
                            // Table pg_cmd_diato_liste
                            //print_r('station : ' . $codeStation . ' sandre : ' . $codeSandre . ' taxon : ' . $codeAltern . '</br>');
                            $pgCmdPrelevHbDiato = $repoPgCmdPrelevHbDiato->getPgCmdPrelevHbDiatoByPrelev($pgCmdPrelev);
                            if ($pgCmdPrelevHbDiato) {
                                $pgCmdDiatoListe = $repoPgCmdDiatoListe->getPgCmdDiatoListeByPrelevCodeSandreTaxon($pgCmdPrelevHbDiato, $codeSandre, $codeAltern);
                                if (!$pgCmdDiatoListe) {
                                    $pgCmdDiatoListe = new PgCmdDiatoListe();
                                    $pgCmdDiatoListe->setPrelev($pgCmdPrelevHbDiato);
                                }
                                $pgCmdDiatoListe->setCodeSandre($codeSandre);
                                $pgCmdDiatoListe->setTaxon($codeAltern);
                                $pgCmdDiatoListe->setDenombrement($denombrement);
                                $emSqe->persist($pgCmdDiatoListe);
                                $emSqe->flush();
                            }
                        }
//                         else {
//                            $contenu = '                     Incorrect ' . CHR(13) . CHR(10);
//                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
//                            fputs($rapport, $contenu);
//                        }
                    }
                    $codeStation = $tab1[5];
                    $codeSandre = null;
                    $taxon = null;
                    $codeAltern = null;
                    $denombrement = null;
                    $erreur = false;
                    // station
                    $trouve = false;
                    foreach ($pgCmdPrelevs as $pgCmdPrelev) {
                        if (substr($pgCmdPrelev->getStation()->getCode(), -(strlen($codeStation))) == $codeStation) {
                            $trouve = true;
//                            if ($pgCmdPrelev->getPhaseDmd()->getCodePhase() == 'M40') {
                            $contenu = '               Station ' . $codeStation . ' ' . $pgCmdPrelev->getStation()->getLibelle() . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
//                            }
                            break;
                        }
                    }
                    if (!$trouve) {
                        $erreur = true;
                        $contenu = '               Station ' . $codeStation . ' ne fait pas partie de cette demande' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }
                } else {
                    if (!$erreur) {
                        $codeAltern = $tab[1];
                        if (is_null($codeAltern)) {
                            $erreur = true;
                            $contenu = '                     ligne ' . $nbl . ' Erreur : code  incorrect ou non renseignée. ' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        } else {
                            $pgSandreCodesAlternAppelTaxon = $repoPgSandreCodesAlternAppelTaxon->getPgSandreCodesAlternAppelTaxonBycodeAlternOrigineCodeAltern($codeAltern, 'OMNIDIA');
                            if (!$pgSandreCodesAlternAppelTaxon) {
                                $erreur = true;
                                $contenu = '                     ligne ' . $nbl . ' Erreur : code ' . $codeAltern . ' inconnu. ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            } else {
                                $codeSandre = $pgSandreCodesAlternAppelTaxon->getCodeAppelTaxon();
                            }
                        }
                        $denombrement = $tab[2];
                        if (is_null($denombrement)) {
                            $erreur = true;
                            $contenu = '                     ligne ' . $nbl . '  Avertissement : dénombrement  incorrecte ou non renseignée. ' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        } elseif (!is_numeric(intval($denombrement))) {
                            $erreur = true;
                            $contenu = '                     ligne ' . $nbl . '  Avertissement : dénombrement incorrecte. ' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        }
                        if (!$erreur and $pgCmdPrelev) {
//                            $contenu = '                  ligne ' . $nbl . ' correcte ' . CHR(13) . CHR(10);
//                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
//                            fputs($rapport, $contenu);
                            // enregistrement en base
                            //
                            // Table pg_cmd_diato_liste
                            //print_r('station : ' . $codeStation . ' sandre : ' . $codeSandre . ' taxon : ' . $codeAltern . '</br>');
                            $pgCmdPrelevHbDiato = $repoPgCmdPrelevHbDiato->getPgCmdPrelevHbDiatoByPrelev($pgCmdPrelev);
                            if ($pgCmdPrelevHbDiato) {
                                $pgCmdDiatoListe = $repoPgCmdDiatoListe->getPgCmdDiatoListeByPrelevCodeSandreTaxon($pgCmdPrelevHbDiato, $codeSandre, $codeAltern);
                                if (!$pgCmdDiatoListe) {
                                    $pgCmdDiatoListe = new PgCmdDiatoListe();
                                    $pgCmdDiatoListe->setPrelev($pgCmdPrelevHbDiato);
                                }
                                $pgCmdDiatoListe->setCodeSandre($codeSandre);
                                $pgCmdDiatoListe->setTaxon($codeAltern);
                                $pgCmdDiatoListe->setDenombrement($denombrement);
                                $emSqe->persist($pgCmdDiatoListe);
                                $emSqe->flush();
                            }
                        }
                        //                        else {
//                            $contenu = '               ligne ' . $nbl . ' incorrecte ' . CHR(13) . CHR(10);
//                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
//                            fputs($rapport, $contenu);
//                        }
                    }
                }
            }
        }
        if (!$erreur and $pgCmdPrelev) {
//            $contenu = '                   Correct ' . CHR(13) . CHR(10);
//            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
//            fputs($rapport, $contenu);
            // enregistrement en base
            //
                // Table pg_cmd_diato_liste
            $pgCmdPrelevHbDiato = $repoPgCmdPrelevHbDiato->getPgCmdPrelevHbDiatoByPrelev($pgCmdPrelev);
            if ($pgCmdPrelevHbDiato) {
                $pgCmdDiatoListe = $repoPgCmdDiatoListe->getPgCmdDiatoListeByPrelevCodeSandreTaxon($pgCmdPrelevHbDiato, $codeSandre, $codeAltern);
                if (!$pgCmdDiatoListe) {
                    $pgCmdDiatoListe = new PgCmdDiatoListe();
                    $pgCmdDiatoListe->setPrelev($pgCmdPrelevHbDiato);
                }
                $pgCmdDiatoListe->setCodeSandre($codeSandre);
                $pgCmdDiatoListe->setTaxon($codeAltern);
                $pgCmdDiatoListe->setDenombrement($denombrement);
                $emSqe->persist($pgCmdDiatoListe);
                $emSqe->flush();
            }
        }

        $tabFichier['erreur'] = $erreur;
        return $tabFichier;
    }

    protected function lirePdf($pgCmdFichierRpsId, $demandeId, $nomFichier, $pathBase, $rapport, $emSqe) {
        // Récupération des programmations
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebUsers');
        $repoPgCmdFichierRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgCmdFichierRps = $repoPgCmdFichierRps->findOneById($pgCmdFichierRpsId);
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
        $pgProgWebUser = $repoPgProgWebUsers->findOneByPrestataire($pgCmdDemande->getPrestataire());

        $tabFichier = array();
        $tabFichier['fichier'] = $nomFichier;

        $contenu = '          Pdf : ' . $nomFichier . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);

        $avertissement = false;
        $erreur = false;

        // station

        $codeStation = substr($nomFichier, 0, 8);
        $trouve = false;
        foreach ($pgCmdPrelevs as $pgCmdPrelev) {
            if (substr($pgCmdPrelev->getStation()->getCode(), -(strlen($codeStation))) == $codeStation) {
                $trouve = true;
                if ($pgCmdPrelev->getPhaseDmd()->getCodePhase() == 'M40') {
                    //  $avertissement = true;
                    $contenu = '                     Avertissement : la station ' . $codeStation . ' ' . $pgCmdPrelev->getStation()->getLibelle() . ' à déjà étét traitée.' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
                break;
            }
        }
        if (!$trouve) {
            $erreur = true;
            $contenu = '                     Erreur : la station ' . $codeStation . ' ne fait pas partie de cette demande' . CHR(13) . CHR(10);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            fputs($rapport, $contenu);
        } else {

            // enregistrement en base
            // table pg_cmd_suivi_prel
            $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevStatutPrel($pgCmdPrelev, 'DF');
            if (!$pgCmdSuiviPrel) {
                $pgCmdSuiviPrel = new PgCmdSuiviPrel();
                $pgCmdSuiviPrel->setPrelev($pgCmdPrelev);
                $pgCmdSuiviPrel->setFichierRps($pgCmdFichierRps);
                $pgCmdSuiviPrel->setUser($pgProgWebUser);
                $pgCmdSuiviPrel->setDatePrel($pgCmdPrelev->getDatePrelev());
                $pgCmdSuiviPrel->setStatutPrel('DF');
                $pgCmdSuiviPrel->setCommentaire('Dépôt hydrobio Pdf');
                $pgCmdSuiviPrel->setValidation('E');
                $emSqe->persist($pgCmdSuiviPrel);
                $emSqe->flush();
            }


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
        $tabFichier['erreur'] = $erreur;
        return $tabFichier;
    }

    protected function lireJpg($pgCmdFichierRpsId, $demandeId, $nomFichier, $pathBase, $rapport, $emSqe) {
        // Récupération des programmations
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebUsers');
        $repoPgCmdFichierRps = $emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgCmdDemande = $emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $repoPgCmdPrelev = $emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgPhases = $emSqe->getRepository('AeagSqeBundle:PgProgPhases');

        $pgCmdFichierRps = $repoPgCmdFichierRps->findOneById($pgCmdFichierRpsId);
        $pgCmdDemande = $repoPgCmdDemande->findOneById($demandeId);
        $pgCmdPrelevs = $repoPgCmdPrelev->getPgCmdPrelevByDemande($pgCmdDemande);
        $pgProgWebUser = $repoPgProgWebUsers->findOneByPrestataire($pgCmdDemande->getPrestataire());

        $tabFichier = array();
        $tabFichier['fichier'] = $nomFichier;

        $contenu = '          Photo : ' . $nomFichier . CHR(13) . CHR(10);
        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
        fputs($rapport, $contenu);

        $erreur = false;

        // station

        $codeStation = substr($nomFichier, 0, 8);
        $trouve = false;
        foreach ($pgCmdPrelevs as $pgCmdPrelev) {
            if (substr($pgCmdPrelev->getStation()->getCode(), -(strlen($codeStation))) == $codeStation) {
                $trouve = true;
                if ($pgCmdPrelev->getPhaseDmd()->getCodePhase() == 'M40') {
                    //  $avertissement = true;
                    $contenu = '                     Avertissement : la station ' . $codeStation . ' ' . $pgCmdPrelev->getStation()->getLibelle() . ' à déjà étét traitée.' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
                break;
            }
        }
        if (!$trouve) {
            $erreur = true;
            $contenu = '                     Erreur : la station ' . $codeStation . '  ne fait pas partie de cette demande' . CHR(13) . CHR(10);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            fputs($rapport, $contenu);
        } else {

            // enregistrement en base
            // table pg_cmd_suivi_prel
            $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelevStatutPrel($pgCmdPrelev, 'DO');
            if (!$pgCmdSuiviPrel) {
                $pgCmdSuiviPrel = new PgCmdSuiviPrel();
                $pgCmdSuiviPrel->setPrelev($pgCmdPrelev);
                $pgCmdSuiviPrel->setFichierRps($pgCmdFichierRps);
                $pgCmdSuiviPrel->setUser($pgProgWebUser);
                $pgCmdSuiviPrel->setDatePrel($pgCmdPrelev->getDatePrelev());
                $pgCmdSuiviPrel->setStatutPrel('DO');
                $pgCmdSuiviPrel->setCommentaire('Dépôt hydrobio Photo');
                $pgCmdSuiviPrel->setValidation('E');
                $emSqe->persist($pgCmdSuiviPrel);
                $emSqe->flush();
            }


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

        $tabFichier['erreur'] = $erreur;
        return $tabFichier;
    }

}
