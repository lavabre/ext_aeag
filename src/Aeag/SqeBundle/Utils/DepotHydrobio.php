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
        //$contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
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
                    $nom_fichier = preg_replace('[^a-zA-Z0-9.]', '-', $nom_fichier);

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
                        if (is_file($complete_name)) {
                            $fd = fopen($complete_name, 'w');

                            fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));

                            fclose($fd);
                            chmod($complete_name, 0755);
                            zip_entry_close($zip_entry);
                        }
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
        //$contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
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
        $B22 = $worksheet->getCell('B22')->getCalculatedValue();
        $codeStation = $B22;
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
            $G23 = $worksheet->getCell('G23')->getCalculatedValue();
            //str_replace(',', '.', $G23->getCalculatedValue());
            $H23 = $worksheet->getCell('H23')->getCalculatedValue();
            //str_replace(',', '.', $H23->getCalculatedValue());
            $avertissement = false;
            if ($G23 == '' && $H23 == '') {
                $avertissement = true;
                $contenu = '                     Avertissement : Coordonnées Lambert 93 non renseignées. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif (!is_numeric(floatval($G23)) || !is_numeric(floatval($H23))) {
                $avertissement = true;
                $contenu = '                     Avertissement : Coordonnées Lambert 93 incorrectes. ';
                $contenu = $contenu . ' G23 : ' . $G23 . ' H23 : ' . $H23 . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif ($G23 == 0 || $H23 == 0) {
                $avertissement = true;
                $contenu = '                     Avertissement : Coordonnées Lambert 93 non renseignées. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }

            if ($avertissement) {
                // Coordonnées : cellules G22, H22 doivent être non vides, au format numérique et avec une valeur > 0
                $G22 = $worksheet->getCell('G22')->getCalculatedValue();
                //str_replace(',', '.', $G22->getCalculatedValue());
                $H22 = $worksheet->getCell('H22')->getCalculatedValue();
                //str_replace(',', '.', $H22->getCalculatedValue());
                if ($G22 == '' && $H22 == '') {
                    $erreur = true;
                    $contenu = '                     Erreur : Coordonnées Lambert II non renseignées. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (!is_numeric(floatval($G22)) || !is_numeric(floatval($H22))) {
                    $erreur = true;
                    $contenu = '                     Erreur : Coordonnées Lambert II incorrectes. ';
                    $contenu = $contenu . ' G22 : ' . $G22 . ' H22 : ' . $H22 . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif ($G22 == 0 || $H223 == 0) {
                    $erreur = true;
                    $contenu = '                     Erreur : Coordonnées Lambert II non renseignées. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            // Autres valeurs du prélèvement : cellules E40, F40, A53, B53, C53  doivent être non vide, au format numérique
            $E40 = $worksheet->getCell('E40')->getCalculatedValue();
            //str_replace(',', '.', $E40->getCalculatedValue());
            $F40 = $worksheet->getCell('F40')->getCalculatedValue();
            //str_replace(',', '.', $F40->getCalculatedValue());
            $A53 = $worksheet->getCell('A53')->getCalculatedValue();
            //str_replace(',', '.', $A53->getCalculatedValue());
            $B53 = $worksheet->getCell('B53')->getCalculatedValue();
            //str_replace(',', '.', $B53->getCalculatedValue());
            $C53 = $worksheet->getCell('C53')->getCalculatedValue();
            //str_replace(',', '.', $C53->getCalculatedValue());
            if (is_null($E40)) {
                $erreur = true;
                $contenu = '                     Avertissement : cellule E40 non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif (!strtoupper(substr($E40, 1)) == 'TIAGE' || !strtoupper($E40) == 'CRU') {
                $avertissement = true;
                $contenu = '                      Avertissement : cellule E40  valeur : ' . $E40 . ' incorrecte. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }

            if (is_null($F40)) {
                $erreur = true;
                $contenu = '                      Avertissement : cellule F40 non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif (!is_numeric(intval($F40))) {
                $erreur = true;
                $contenu = '                     Avertissement : cellule F40 incorrecte. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif ($F40 == 0) {
                $erreur = true;
                $contenu = '                      Avertissement : cellule F40  valeur : ' . $F40 . ' incorrecte. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }

            if (is_null($A53)) {
                $erreur = true;
                $contenu = '                        Erreur : cellule A53  support  incorrecte ou non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } else {
                $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('274', $A53, '10');
                if (!$pgSandreHnNomemclature) {
                    $erreur = true;
                    $contenu = '                     Erreur : cellule A53  support ' . $A53 . ' impossible. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            if (is_null($B53)) {
                $erreur = true;
                $contenu = '                       Erreur : cellule B53   classe vitesse incorrecte ou non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } else {
                $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('278', $B53, '10');
                if (!$pgSandreHnNomemclature) {
                    $erreur = true;
                    $contenu = '                   Erreur : cellule B53   classe vitesse  ' . $B53 . ' impossible. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            if (is_null($C53)) {
                $erreur = true;
                $contenu = '                      Erreur : cellule C53  ombrage  incorrecte ou non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif (!strtoupper($C53) == 'OUVERT' || !strtoupper($C53) == 'SEMI-OUVERT' || !strtoupper(substr($C53, 0, 4)) == 'FERM') {
                $erreur = true;
                $contenu = '                      Erreur : cellule C53  ombrage  ' . $C53 . ' incorrecte. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }

            //    Cellules B40, C40, D40  les valeurs doivent être numériques si non vides
            $B40 = $worksheet->getCell('B40')->getCalculatedValue();
            //str_replace(',', '.', $B40->getCalculatedValue());
            $C40 = $worksheet->getCell('C40')->getCalculatedValue();
            //str_replace(',', '.', $C40->getCalculatedValue());
            $D40 = $worksheet->getCell('D40')->getCalculatedValue();
            //str_replace(',', '.', $D40->getCalculatedValue());
            if (!is_null($B40)) {
                if (!is_numeric(intval($B40))) {
                    $avertissement = true;
                    $contenu = '                      Avertissement : cellule B40  température ' . $B40 . ' valeur non numérique. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            if (!is_null($C40)) {
                if (!is_numeric(intval($C40))) {
                    $avertissement = true;
                    $contenu = '                      Avertissement : cellule C40  ph ' . $C40 . ' valeur non numérique. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            if (!is_null($D40)) {
                if (!is_numeric(intval($D40))) {
                    $avertissement = true;
                    $contenu = '                      Avertissement : cellule D40  conductivite ' . $D40 . ' valeur non numérique. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            if (!$erreur) {
                $contenu = '                       Correct ' . CHR(13) . CHR(10);
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
                if (is_null($G23) || !is_numeric(intval($G23)) || ( $G23 == 0)) {
                    $pgCmdPrelevHbDiato->setXPrel(str_replace(',', '.', $G22));
                } else {
                    $pgCmdPrelevHbDiato->setXPrel(str_replace(',', '.', $G23));
                }
                if (is_null($H23) || !is_numeric(intval($H23)) || ( $H23 == 0)) {
                    $pgCmdPrelevHbDiato->setYPrel(str_replace(',', '.', $H22));
                } else {
                    $pgCmdPrelevHbDiato->setYPrel(str_replace(',', '.', $H23));
                }

                if (!is_null($B40)) {
                    if (is_numeric(intval($B40))) {
                        $pgCmdPrelevHbDiato->setTempEau(str_replace(',', '.', $B40));
                    }
                }
                if (!is_null($C40)) {
                    if (is_numeric(intval($C40))) {
                        $pgCmdPrelevHbDiato->setPh(str_replace(',', '.', $C40));
                    }
                }
                if (!is_null($D40)) {
                    if (is_numeric(intval($D40))) {
                        $pgCmdPrelevHbDiato->setConductivite(str_replace(',', '.', $D40));
                    }
                }

                if (!is_null($F40)) {
                    if (is_numeric(intval($F40))) {
                        if ($F40 != 0) {
                            $pgCmdPrelevHbDiato->setLargeur(str_replace(',', '.', $F40));
                        }
                    }
                }

                $pgCmdPrelevHbDiato->setSubstrat($A53);
                $pgCmdPrelevHbDiato->setVitesse($B53);
                $pgCmdPrelevHbDiato->setOmbrage($C53);
                $pgCmdPrelevHbDiato->setConditionsHydro($E40);

                $emSqe->persist($pgCmdPrelevHbDiato);

                $emSqe->flush();

                //$pgCmdPrelev->setDatePrelev(new \DateTime());
                $pgCmdSuiviPrels = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelev($pgCmdPrelev);
                $pgCmdDiatoListes = $repoPgCmdDiatoListe->getPgCmdDiatoListesByPrelev($pgCmdPrelevHbDiato);
                $nbOk = 0;
                foreach ($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
                    if ($pgCmdSuiviPrel->getStatutPrel() == 'D' || $pgCmdSuiviPrel->getStatutPrel() == 'DF') {
                        if (count($pgCmdDiatoListes) > 0) {
                            $nbOk++;
                        }
                    }
                }
                if ($nbOk < 2) {
                    $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M20');
                } else {
                    $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M30');
                }
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
                $emSqe->persist($pgCmdPrelev);
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
        $B23 = $worksheet->getCell('B23')->getCalculatedValue();
        $codeStation = $B23;
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

            // diren
            $A23 = $worksheet->getCell('A23')->getCalculatedValue();
            if ($A23 == '') {
                $avertissement = true;
                $contenu = '                     Avertissement : Cellule A23 diren   non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }

            // cours d'eau
            $C23 = $worksheet->getCell('C23')->getCalculatedValue();
            if ($C23 == '') {
                $avertissement = true;
                $contenu = '                     Avertissement : Cellule C23 cours d\'eau   non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }

            // Coordonnées : cellules K24, L24, M24, N24 doivent être non vides, au format numérique et avec une valeur > 0
            $K24 = $worksheet->getCell('K24')->getCalculatedValue();
            // str_replace(',', '.', $K24->getCalculatedValue());
            $L24 = $worksheet->getCell('L24')->getCalculatedValue();
            //  str_replace(',', '.', $L24->getCalculatedValue());
            $M24 = $worksheet->getCell('M24')->getCalculatedValue();
            //  str_replace(',', '.', $M24->getCalculatedValue());
            $N24 = $worksheet->getCell('N24')->getCalculatedValue();
            //  str_replace(',', '.', $N24->getCalculatedValue());
            $avertissement = false;
            if ($K24 == '' && $L24 == '' && $M24 == '' && $N24 == '') {
                $avertissement = true;
                $contenu = '                     Avertissement : Coordonnées Lambert 93 non renseignées. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif (!is_numeric(floatval($K24)) || !is_numeric(floatval($L24)) || !is_numeric(floatval($M24)) || !is_numeric(floatval($N24))) {
                $avertissement = true;
                $contenu = '                     Avertissement : Coordonnées Lambert 93 incorrectes. ';
                $contenu = $contenu . ' K24 : ' . $K24 . ' L24 : ' . $L24 . ' M24 : ' . $M24 . ' N24 : ' . $N24 . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif ($K24 == 0 && $L24 == 0 && $M24 == 0 && $N24 == 0) {
                $avertissement = true;
                $contenu = '                     Avertissement : Coordonnées Lambert 93 non renseignées. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }

            if ($avertissement) {
                // Coordonnées : cellules K23, L23, M23, N23 doivent être non vides, au format numérique et avec une valeur > 0
                $K23 = $worksheet->getCell('K23')->getCalculatedValue();
                //str_replace(',', '.', $K23->getCalculatedValue());
                $L23 = $worksheet->getCell('L23')->getCalculatedValue();
                //str_replace(',', '.', $L23->getCalculatedValue());
                $M23 = $worksheet->getCell('M23')->getCalculatedValue();
                //str_replace(',', '.', $M23->getCalculatedValue());
                $N23 = $worksheet->getCell('N23')->getCalculatedValue();
                //str_replace(',', '.', $N23->getCalculatedValue());
                if ($K23 == '' && $L23 == '' && $M23 == '' && $N23 == '') {
                    $erreur = true;
                    $contenu = '                     Erreur : Coordonnées Lambert II non renseignées. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (!is_numeric(floatval($K23)) || !is_numeric(floatval($L23)) || !is_numeric(floatval($M23)) || !is_numeric(floatval($N23))) {
                    $erreur = true;
                    $contenu = '                     Erreur : Coordonnées Lambert II incorrectes. ';
                    $contenu = $contenu . ' K23 : ' . $K23 . ' L23 : ' . $L23 . ' M23 : ' . $M23 . ' N23 : ' . $N23 . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif ($K23 == 0 && $L23 == 0 && $M23 == 0 && $N23 == 0) {
                    $erreur = true;
                    $contenu = '                     Erreur : Coordonnées Lambert II  non renseignées. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }


            //	Autres valeurs du prélèvement : cellules P23, E39, O23 doivent être non vide, au format numérique
            $P23 = $worksheet->getCell('P23')->getCalculatedValue();
            //str_replace(',', '.', $P23->getCalculatedValue());
            $E39 = $worksheet->getCell('E39')->getCalculatedValue();
            // str_replace(',', '.', $E39->getCalculatedValue());
            $O23 = $worksheet->getCell('O23')->getCalculatedValue();
            // str_replace(',', '.', $O23->getCalculatedValue());
            if (is_null($P23)) {
                $avertissement = true;
                $contenu = '                     Avertissement : cellule P23 non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif (!is_numeric(intval($P23))) {
                $avertissement = true;
                $contenu = '                      Avertissement : cellule P23  incorrecte. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }
            if (is_null($E39) || $E39 == '') {
                $avertissement = true;
                $contenu = '                      Avertissement : cellule E39   non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif (!is_numeric(intval($E39))) {
                $avertissement = true;
                $contenu = '                     Avertissement : cellule E39  incorrecte. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }
            if (is_null($O23)) {
                $avertissement = true;
                $contenu = '                     Avertissement : cellule O23   non renseignée. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            } elseif (!is_numeric(intval($O23))) {
                $avertissement = true;
                $contenu = '                      Avertissement : cellule O23  incorrecte. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }

            //  Substrats
            $tabG = array();
            for ($i = 39; $i < 51; $i++) {
                $G[$i] = $worksheet->getCell('G' . $i)->getCalculatedValue();
                $celG = $worksheet->getCell('G' . $i)->getCalculatedValue();
                $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('274', $celG, '13');
                if (!$pgSandreHnNomemclature) {
                    $erreur = true;
                    $contenu = '                     Erreur : cellule G' . $i . '  substrat ' . $celG . ' impossible. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            //  Recouvrements :  Cellules H39 à H50, les valeurs doivent être numériques si non vides
            $tabH = array();
            $totRecouvrement = 0;
            for ($i = 39; $i <= 50; $i++) {
                $H[$i] = $worksheet->getCell('H' . $i)->getCalculatedValue();
                $celH = $worksheet->getCell('H' . $i)->getCalculatedValue();
                // str_replace(',', '.', $celH->getCalculatedValue());
                if (!is_null($celH)) {
                    if (!is_numeric(floatval($celH))) {
                        $avertissement = true;
                        $contenu = '                      Avertissement : cellule H' . $i . '   recouvrement ' . $celH . ' valeur non numérique. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } else {
                        $totRecouvrement = $totRecouvrement + floatval($celH);
                    }
                }
            }
            if (round($totRecouvrement, 0) != 100) {
                $erreur = true;
                $contenu = '                     Erreur : la somme des recouvrements ' . $totRecouvrement . ' doit être égale à 100. ' . CHR(13) . CHR(10);
                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                fputs($rapport, $contenu);
            }

            // 	Substrats (encore) : Cellules D66 à D78, les valeurs doivent être dans la liste des substrats possibles
            $tabD = array();
            for ($i = 66; $i < 78; $i++) {
                $D[$i] = $worksheet->getCell('D' . $i)->getCalculatedValue();
                $celD = $worksheet->getCell('D' . $i)->getCalculatedValue();
                if ($celD != '') {
                    $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('274', $celD, '13');
                    if (!$pgSandreHnNomemclature) {
                        $erreur = true;
                        $contenu = '                     Erreur : cellule D' . $i . '   substrat ' . $celD . ' impossible. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }
                } else {
                    $trouve = false;
                    for ($j = 39; $j <= 50; $j++) {
                        $celF = $worksheet->getCell('F' . $j)->getCalculatedValue();
                        if ($celD == $celF) {
                            $trouve = true;
                            break;
                        }
                    }
                    if (!$trouve) {
                        $erreur = true;
                        $contenu = '                     Erreur : cellule D' . $i . '   substrat ' . $celD . ' plan d’échantillonnage non identifié dans la mosaïque de substrats de la station (notés dans les colonnes F39 et F50). ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }
                }
            }

            // 	Vitesses : Cellules E66 à E78, les valeurs doivent être dans la liste des vitesses possibles
            $tabE = array();
            for ($i = 66; $i < 78; $i++) {
                $E[$i] = $worksheet->getCell('E' . $i)->getCalculatedValue();
                $celE = $worksheet->getCell('E' . $i)->getCalculatedValue();
                if ($celE != '') {
                    $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('278', $celE, '13');
                    if (!$pgSandreHnNomemclature) {
                        $erreur = true;
                        $contenu = '                     Erreur : cellule E' . $i . '   classe vitesse ' . $celE . ' impossible. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }
                }
            }

            // 	Phases : Cellules F66 à F78, les valeurs doivent être dans la liste des phases possibles
            $tabF = array();
            for ($i = 66; $i < 78; $i++) {
                $F[$i] = $worksheet->getCell('F' . $i)->getCalculatedValue();
                $celF = $worksheet->getCell('F' . $i)->getCalculatedValue();
                if ($celF != '') {
                    $pgSandreHnNomemclature = $repoPgSandreHbNomemclatures->getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport('480', $celF, '13');
                    if (!$pgSandreHnNomemclature) {
                        $erreur = true;
                        $contenu = '                     Erreur : cellule F' . $i . '   phase ' . $celF . ' impossible. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    }
                }
            }

            // 	Hauteur d'eau : Cellules G66 àG78, les valeurs doivent être renseignées et différentes de 0
            $tabG = array();
            for ($i = 66; $i < 78; $i++) {
                $G[$i] = $worksheet->getCell('G' . $i)->getCalculatedValue();
                $celG = $worksheet->getCell('G' . $i)->getCalculatedValue();
                if (is_null($celG)) {
                    $avertissement = true;
                    $contenu = '                      Avertissement : cellule G' . $i . '  non renseignée. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (!is_numeric(intval($celG))) {
                    $avertissement = true;
                    $contenu = '                     Avertissement : cellule G' . $i . '  incorrecte. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (intval($celG) == 0) {
                    $avertissement = true;
                    $contenu = '                     Avertissement : cellule G' . $i . '   = 0 à vérifier. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            // 	Intensite du colmatage : Cellules H66 à H78, les valeurs doivent être renseignées et différentes de 0
            $tabH = array();
            for ($i = 66; $i < 78; $i++) {
                $H[$i] = $worksheet->getCell('H' . $i)->getCalculatedValue();
                $celH = $worksheet->getCell('H' . $i)->getCalculatedValue();
                if (is_null($celH)) {
                    $avertissement = true;
                    $contenu = '                      Avertissement : cellule H' . $i . '  non renseignée. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (!is_numeric(intval($celH))) {
                    $avertissement = true;
                    $contenu = '                     Avertissement : cellule H' . $i . '  incorrecte. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                } elseif (intval($celH) == 0) {
                    $avertissement = true;
                    $contenu = '                     Avertissement : cellule H' . $i . '  = 0 à vérifier. ' . CHR(13) . CHR(10);
                    $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                    fputs($rapport, $contenu);
                }
            }

            //  Dénombrements : pour chaque ligne xx à partir de la ligne 88, et jusqu’à rencontrer une cellule vide dans la colonne D
            for ($i = 88; $i < 1000; $i++) {
                $celC = $worksheet->getCell('C' . $i)->getCalculatedValue();
                $celD = $worksheet->getCell('D' . $i)->getCalculatedValue();
                $celE = $worksheet->getCell('E' . $i)->getCalculatedValue();
                $celF = $worksheet->getCell('F' . $i)->getCalculatedValue();
                $celG = $worksheet->getCell('G' . $i)->getCalculatedValue();
                if ($celD != '' || $celC != '') {
                    if ($celD == '') {
                        $erreur = true;
                        $contenu = '                     Erreur : cellule D' . $i . '  le code Sandre doit être renseigné. ' . CHR(13) . CHR(10);
                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                        fputs($rapport, $contenu);
                    } else {
                        if ($celD == '#N/A') {
                            $cell = $worksheet->getCell('D' . $i);
                            $celD = $this->getCalculatedValueBis($cell, $rapport);
                            if ($celD == '#N/A') {
                                $avertissement = true;
                                $contenu = '                      Erreur : cellule D' . $i . '  le libelle ' . $celC . ' n\'a pas de correspondance dans la formule : ' . $worksheet->getCell('D' . $i)->getValue() . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                                $celD = $worksheet->getCell('D' . $i)->getCalculatedValue();
                            }
                        }
                        $pgSandreAppellationTaxon = $repoPgSandreAppellationTaxon->getPgSandreAppellationTaxonByCodeAppelTaxonCodeSupport($celD, '13');
                        if (!$pgSandreAppellationTaxon) {
                            $erreur = true;
                            $contenu = '                      Erreur : cellule D' . $i . '  le code Sandre ' . $celD . ' ne faire partie de la liste des codes possibles pour le support 13. ' . CHR(13) . CHR(10);
                            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                            fputs($rapport, $contenu);
                        } else {
                            if ($celC != $pgSandreAppellationTaxon->getNomAppelTaxon()) {
                                $avertissement = true;
                                $contenu = '                      Avertissement : cellule C' . $i . '  libellé sandre : ' . $celC . ' différent de celui en base : ' . $pgSandreAppellationTaxon->getNomAppelTaxon() . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                            if ($celE == "" && $celF == "" && $celG == "") {
                                $erreur = true;
                                $contenu = '                      Erreur : cellule D' . $i . '  le code Sandre ' . $celD . ' n’est pas dénombré dans les phases A, B et C . ' . CHR(13) . CHR(10);
                                $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                fputs($rapport, $contenu);
                            }
                            $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnByLotAnStation($pgProgLotAn, $pgRefStationMesure);
                            if ($pgProgLotStationAn) {
                                $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgLotStationAn->getRsxId());
                                if ($pgRefReseauMesure->getCodeAeagRsx() == '099') {
                                    $celH = $worksheet->getCell('H' . $i)->getCalculatedValue();
                                    $celI = $worksheet->getCell('I' . $i)->getCalculatedValue();
                                    $celJ = $worksheet->getCell('J' . $i)->getCalculatedValue();
                                    $celK = $worksheet->getCell('K' . $i)->getCalculatedValue();
                                    $celL = $worksheet->getCell('L' . $i)->getCalculatedValue();
                                    $celM = $worksheet->getCell('M' . $i)->getCalculatedValue();
                                    $celN = $worksheet->getCell('N' . $i)->getCalculatedValue();
                                    $celO = $worksheet->getCell('O' . $i)->getCalculatedValue();
                                    $celP = $worksheet->getCell('P' . $i)->getCalculatedValue();
                                    $celQ = $worksheet->getCell('Q' . $i)->getCalculatedValue();
                                    $celR = $worksheet->getCell('R' . $i)->getCalculatedValue();
                                    $celS = $worksheet->getCell('S' . $i)->getCalculatedValue();
                                    if ($celH == "" && $celI == "" && $celJ == "" &&
                                            $celK == "" && $celL == "" && $celM == "" &&
                                            $celN == "" && $celO == "" && $celP == "" &&
                                            $celQ == "" && $celR == "" && $celS == "") {
                                        $erreur = true;
                                        $contenu = '                     Erreur : cellule D' . $i . '   le code Sandre ' . $celD . ' n’est pas dénombré dans les microprélèvements P1 à P12. ' . CHR(13) . CHR(10);
                                        $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
                                        fputs($rapport, $contenu);
                                    }
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
//                 for ($i = 66; $i < 78; $i++) {
//                     if ($worksheet->getCell('H' . $i)->getCalculatedValue())
//                $H[$i] = $worksheet->getCell('H' . $i);
//                 }
//            }

            if (!$erreur) {
                $contenu = '                      Correct ' . CHR(13) . CHR(10);
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
                if (is_null($K24) || !is_numeric(floatval($K24)) || ( $K24 == 0)) {
                    $pgCmdPrelevHbInvert->setXAmont(str_replace(',', '.', $K23));
                } else {
                    $pgCmdPrelevHbInvert->setXAmont(str_replace(',', '.', $K24));
                }
                if (is_null($L24) || !is_numeric(floatval($L24)) || ( $L24 == 0)) {
                    $pgCmdPrelevHbInvert->setYAmont(str_replace(',', '.', $L23));
                } else {
                    $pgCmdPrelevHbInvert->setYAmont(str_replace(',', '.', $L24));
                }
                if (is_null($M24) || !is_numeric(floatval($M24)) || ( $M24 == 0)) {
                    $pgCmdPrelevHbInvert->setXAvalt(str_replace(',', '.', $M23));
                } else {
                    $pgCmdPrelevHbInvert->setXAval(str_replace(',', '.', $M24));
                }
                if (is_null($N24) || !is_numeric(floatval($N24)) || ( $N24 == 0)) {
                    $pgCmdPrelevHbInvert->setYAval(str_replace(',', '.', $N23));
                } else {
                    $pgCmdPrelevHbInvert->setYAval(str_replace(',', '.', $N24));
                }
                $pgCmdPrelevHbInvert->setLongueur($P23);
                $pgCmdPrelevHbInvert->setLargeurMoy(str_replace(',', '.', $E39));
                $pgCmdPrelevHbInvert->setLargeurPb(str_replace(',', '.', $O23));
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
                    $celH = $worksheet->getCell('H' . $i)->getCalculatedValue();
                    if (!is_null($celH)) {
                        $celG = $worksheet->getCell('G' . $i)->getCalculatedValue();
                        $substrat = $celG;
                        $pgCmdInvertRecouv = new PgCmdInvertRecouv();
                        $pgCmdInvertRecouv->setPrelev($pgCmdPrelevHbInvert);
                        $pgCmdInvertRecouv->setSubstrat($substrat);
                        $pgCmdInvertRecouv->setRecouvrement($celH);
                        if (is_numeric(intval($celH))) {
                            $pgCmdInvertRecouv->setRecouvNum($celH);
                        }
                        $emSqe->persist($pgCmdInvertRecouv);
                    }
                }

                //Table pg_cmd_invert_prelem : pour chaque ligne xx dans la zone C66 à K78

                for ($i = 66; $i < 78; $i++) {
                    $mpc = $worksheet->getCell('C' . $i)->getCalculatedValue();
                    if ($mpc != '') {
                        $pgCmdInvertPrelem = new PgCmdInvertPrelem();
                        $pgCmdInvertPrelem->setPrelev($pgCmdPrelevHbInvert);
                        $pgCmdInvertPrelem->setPrelem($mpc);
                        $mpd = $worksheet->getCell('D' . $i)->getCalculatedValue();
                        if (!is_null($mpd)) {
                            $pgCmdInvertPrelem->setSubstrat($mpd);
                        }
                        $mpe = $worksheet->getCell('E' . $i)->getCalculatedValue();
                        if (!is_null($mpe)) {
                            $pgCmdInvertPrelem->setVitesse($mpe);
                        }
                        $mpf = $worksheet->getCell('F' . $i)->getCalculatedValue();
                        if (!is_null($mpf)) {
                            $pgCmdInvertPrelem->setPhase($mpf);
                        }
                        $mpg = $worksheet->getCell('G' . $i)->getCalculatedValue();
                        if (!is_null($mpg) && is_numeric(intval($mpg))) {
                            $pgCmdInvertPrelem->setHauteurEau($mpg);
                        }
                        $mph = $worksheet->getCell('H' . $i)->getCalculatedValue();
                        if (!is_null($mph) && is_numeric(intval($mph))) {
                            $pgCmdInvertPrelem->setColmatage($mph);
                        }
                        $mpi = $worksheet->getCell('I' . $i)->getCalculatedValue();
                        if (!is_null($mpi)) {
                            $pgCmdInvertPrelem->setStabilite($mpi);
                        }
                        $mpj = $worksheet->getCell('J' . $i)->getCalculatedValue();
                        if (!is_null($mpj)) {
                            $pgCmdInvertPrelem->setNatureVeget($mpj);
                        }
                        $mpk = $worksheet->getCell('K' . $i)->getCalculatedValue();
                        if (!is_null($mpk)) {
                            $pgCmdInvertPrelem->setAbondVeget($mpk);
                        }
                        $emSqe->persist($pgCmdInvertPrelem);
                    }
                }

                // Table pg_cmd_invert_liste : pour chaque ligne xx à partir de la ligne 88, et jusqu’à rencontrer une cellule vide dans la colonne D

                for ($i = 88; $i < 1000; $i++) {
                    $ecd = $worksheet->getCell('D' . $i)->getCalculatedValue();
                    if ($ecd != '') {
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
                            $pgCmdInvertListe->setCodeSandre($ecd);
                            $qec = $worksheet->getCell('C' . $i)->getCalculatedValue();
                            $pgCmdInvertListe->setTaxon($qec);
                            $pgCmdInvertListe->setDenombrement(intval($tabPhase[$jj]['valeur']));
                            $emSqe->persist($pgCmdInvertListe);
                        }
                        for ($kk = 0; $kk < count($tabQE); $kk++) {
                            $pgCmdInvertListe = new PgCmdInvertListe();
                            $pgCmdInvertListe->setPrelev($pgCmdPrelevHbInvert);
                            $pgCmdInvertListe->setPrelem($tabQE[$kk]['nom']);
                            $pgCmdInvertListe->setCodeSandre($ecd);
                            $qec = $worksheet->getCell('C' . $i)->getCalculatedValue();
                            $pgCmdInvertListe->setTaxon($qec);
                            $pgCmdInvertListe->setDenombrement(intval($tabQE[$kk]['valeur']));
                            $emSqe->persist($pgCmdInvertListe);
                        }
                    } else {
                        break;
                    }
                }
                // $pgCmdPrelev->setDatePrelev(new \DateTime());
                $pgCmdSuiviPrels = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelByPrelev($pgCmdPrelev);
                $nbOk = 0;
                foreach ($pgCmdSuiviPrels as $pgCmdSuiviPrel) {
                    if ($pgCmdSuiviPrel->getStatutPrel() == 'D' || $pgCmdSuiviPrel->getStatutPrel() == 'DF') {
                        $nbOk++;
                    }
                }
                if ($nbOk < 2) {
                    $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M20');
                } else {
                    $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('M30');
                }
                $pgCmdPrelev->setPhaseDmd($pgProgPhases);
                $emSqe->persist($pgCmdPrelev);
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
        //$contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
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
                    if (strlen($codeStation) > 0 && $codeStation != $tab1[5]) {
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
                        if (!$erreur && $pgCmdPrelev) {
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
        if (!$erreur && $pgCmdPrelev) {
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
        // $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
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

    /**
     * Get calculated cell value
     *
     * @return mixed
     */
    private function getCalculatedValueBis($cell, $rapport) {
        echo 'Cell ' . $cell->getCoordinate() . ' value : ' . $cell->getValue() . '<br />';
        try {
            $result = $cell->getCalculatedValue();
        } catch (Exception $ex) {
            $result = '#N/A';
            $erreur = true;
            $contenu = '                     Erreur : cellule ' . $cell->getCoordinate() . '  avec formule en erreur : ' . $ex->getMessage() . CHR(13) . CHR(10);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            fputs($rapport, $contenu);
        }
        echo $cell->getCoordinate() . ' resultat :  ' . $result . '<br />';
        return $result;
    }

}
