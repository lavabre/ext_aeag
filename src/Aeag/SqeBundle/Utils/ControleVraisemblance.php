<?php

namespace Aeag\SqeBundle\Utils;

class ControleVraisemblance {
    
    // III.1 Champs non renseignés (valeurs et code remarque)  ou valeurs non numériques ou valeurs impossibles params env
    public function champsNonRenseignes($mesure, $codeRq, $codeParametre, $inSitu) {
        if (is_null($mesure)) {
            if ($codeRq != 0 || is_null($codeRq)) { // III.2 Si valeur "vide" avec code remarque "0" hors lecture échelle (1429),,,,,,,'ABSENT' / on doit avoir un code remarque = 0 pour les valeurs vides, sinon avertissement, sauf pour le 1429 (cote échelle) => Avertissement
                if ($codeParametre == 1429 || $inSitu == 0) {
                    return array("warning", "Valeur non renseignée et code remarque différent de 0");
                    //$this->_addLog('warning', $demandeId, $reponseId, "Valeur non renseignée et code remarque différent de 0", $codePrelevement, $codeParametre);
                } else {
                    return array("error", "Valeur non renseignée et code remarque différent de 0");
                    //$this->_addLog('error', $demandeId, $reponseId, "Valeur non renseignée et code remarque différent de 0", $codePrelevement, $codeParametre);
                }
            }
        }
        return true;
    }

    // III.1.1
    public function champsNonRenseignesEtValeursVides($mesure, $codeRq, $codeParametre, $inSitu) {
        if (is_null($codeRq) && !is_null($mesure)) {
            if ($codeParametre == 1429 || $inSitu == 0) {
                // TODO Si version edilabo
                // A remettre lorsque la version edilabo sera prise en compte 
                // $this->_addLog('warning', $demandeId, $reponseId, "Valeur renseignée et code remarque vide", $codePrelevement, $codeParametre);
            } else {
                return array("error", "Valeur non renseignée et code remarque vide");
                //$this->_addLog('error', $demandeId, $reponseId, "Valeur renseignée et code remarque vide", $codePrelevement, $codeParametre);
            }
        }
        return true;
    }

    // III.2 Si valeur "vide" avec code remarque "0" hors lecture échelle (1429),,,,,,,'ABSENT' / on doit avoir un code remarque = 0 pour les valeurs vides, sinon avertissement, sauf pour le 1429 (cote échelle) => Avertissement
    public function valeursNumeriques($mesure, $codeRq) {
        if (!is_null($codeRq) && $codeRq != 0 && !is_null($mesure)) {
            if (!is_numeric($mesure) || !is_numeric($codeRq)) {
                //$this->_addLog('error', $demandeId, $reponseId, "La valeur n'est pas un nombre", $codePrelevement, $codeParametre);
                return array("error", "La valeur n'est pas un nombre");
            }
        }
        return true;
    }

    // III.3 Valeurs =0 (hors TH (1345), TA (1346), TAC (1347), Temp(1301)) hors codes observations environnementales / résultat = 0 possible pour les paramètres de cette liste (et pour 1345, 1346, 1347 et 1301) => Erreur
    public function valeursEgalZero($mesure, $codeParametre, $inSitu) {
        if (!is_null($mesure) && $mesure == 0) {
            if ($codeParametre !== 1345 && $codeParametre !== 1346 && $codeParametre !== 1347 && $codeParametre !== 1301 && $inSitu != 0) {
                //$this->_addLog('error', $demandeId, $reponseId, "Valeur = 0 impossible pour ce paramètre", $codePrelevement, $codeParametre);
                return array("error", "Valeur = 0 impossible pour ce paramètre");
            }
        }
        return true;
    }

    // III.4 Valeurs < 0 (hors température de air 1409, potentiel REDOX 1330)
    public function valeursInfZero($mesure, $codeParametre) {
        if ($mesure < 0) {
            if ($codeParametre != 1409 && $codeParametre != 1330 && $codeParametre != 1420 && $codeParametre != 1429) {
                //$this->_addLog('error', $demandeId, $reponseId, "Valeur < 0 impossible pour ce paramètre", $codePrelevement, $codeParametre);
                return array("error", "Valeur < 0 impossible pour ce paramètre");
            }
        }
        return true;
    }

    // III.5 Valeurs avec code remarque '> (3)' hors bactério (1147,1448,1449,1451,5479,6455); code remarque ="Trace (7)"
    public function valeursSupTrois($codeParametre, $codeRq) {
        $codeParamsBacterio = array(
            1447, 1448, 1449, 1451, 5479, 6455
        );
        if (($codeRq == 3 && !in_array($codeParametre, $codeParamsBacterio)) || $codeRq == 7) {
            //$this->_addLog('error', $demandeId, $reponseId, "Code Remarque > 3 ou == 7 impossible pour ce paramètre", $codePrelevement, $codeParametre);
            return array("error", "Code Remarque > 3 ou == 7 impossible pour ce paramètre");
        }
        return true;
    }

    // III.6 1 < pH(1302) < 14
    public function pH($mPh) {
        if (!is_null($mPh)) {
            if ($mPh < 1 || $mPh > 14) {
                //$this->_addLog('error', $demandeId, $reponseId, "Le pH n\'est pas entre 1 et 14", $codePrelevement, $mPh);
                return array("error", "Le pH n\'est pas entre 1 et 14");
            }
        }
        return true;
    }

    // III.7 modèle  de WEISS : cohérence Teau, % O2, Concentration O2  sauf si Conductivité > 10 000
    public function modeleWeiss($mTxSatOx, $mOxDiss, $mTEau, $mConductivite) {
        if (!is_null($mTxSatOx) && !is_null($mOxDiss) && !is_null($mTEau) && !is_null($mConductivite)) {
            if ($mConductivite < 10000) {
                $mTEauK = $mTEau + 273.15;
                $argExp = -173.4292 + 249.6339 * (100 / $mTEauK) + 143.3483 * log($mTEauK / 100) - 21.8492 * ($mTEauK / 100);
                $txSatModel = ($mOxDiss * 100) / (1.4276 * exp($argExp));
                $indVraiWess = $txSatModel - $mTxSatOx;

                if (abs($indVraiWess) > 25) {
                    //error
                    //$this->_addLog('error', $demandeId, $reponseId, "Modele de Weiss : valeurs non conformes", $codePrelevement, abs($indVraiWess));
                    return array("error", "Modele de Weiss : valeurs non conformes");
                } else if (10 < abs($indVraiWess) && abs($indVraiWess) <= 25) {
                    // Avertissement
                    //$this->_addLog('warning', $demandeId, $reponseId, "Modele de Weiss : valeur réservée", $codePrelevement, abs($indVraiWess));
                    return array("warning", "Modele de Weiss : valeur réservée");
                }
            } else {
                //error ou avertissement ?
                //$this->_addLog('warning', $demandeId, $reponseId, "Modele de Weiss : Conductivité supérieur à 10000", $codePrelevement, $mConductivite);
                return array("warning", "Modele de Weiss : Conductivité supérieur à 10000");
            }
        }
        return true;
    }

    // III.8 Balance ionique (meq) sauf si tous les résultats < LQ
    public function balanceIonique($cCationParams, $cAnionParams, $codeRqCationParams, $codeRqAnionParams) {
        // Vérification de l'existence des paramètres
        $valid = true;
        $cpt = 0;
        $keys = array_keys($cCationParams);
        while ($cpt < count($keys) && $valid) {
            if (is_null($cCationParams[$keys[$cpt]])) {
                $valid = false;
            }
            $cpt++;
        }


        if ($valid) {
            $cpt = 0;
            $keys = array_keys($cAnionParams);
            while ($cpt < count($keys) && $valid) {
                if (is_null($cAnionParams[$keys[$cpt]])) {
                    $valid = false;
                }
                $cpt++;
            }
        }

        if ($valid) {
            $countLq = 0;
            foreach ($codeRqCationParams as $idx => $codeRqCationParam) {
                if ($codeRqCationParam == 10) {
                    $countLq++;
                }
            }

            foreach ($codeRqAnionParams as $idx => $codeRqAnionParam) {
                if ($codeRqAnionParam == 10) {
                    $countLq++;
                }
            }

            if ($countLq >= (count($cAnionParams) + count($cCationParams))) {
                //$this->_addLog('error', $demandeId, $reponseId, "Balance Ionique : Tous les dosages sont en LQ", $codePrelevement);
                return array("error", "Balance Ionique : Tous les dosages sont en LQ");
            } else {
                $vCationParams = array(1374 => 20.039,
                    1335 => 18.03846,
                    1372 => 12.1525,
                    1367 => 39.0983,
                    1375 => 22.98977
                );

                $vAnionParams = array(1433 => 47.48568,
                    1340 => 63.0049,
                    1338 => 48.0313,
                    1337 => 35.453,
                    1327 => 61.01684,
                    1339 => 46.0055
                );


                $cCation = 0;
                foreach ($cCationParams as $idx => $cCationParam) {
                    $cCation += $cCationParam / $vCationParams[$idx];
                }

                $cAnion = 0;
                foreach ($cAnionParams as $idx => $cAnionParam) {
                    $cAnion += $cAnionParam / $vAnionParams[$idx];
                }

                $indVraiBion = ($cCation - $cAnion);

                if (0.5 < $indVraiBion && $indVraiBion <= 1.25) {
                    //$this->_addLog('warning', $demandeId, $reponseId, "Balance Ionique : Valeur réservée", $codePrelevement);
                    return array("warning", "Balance Ionique : Valeur réservée");
                } else if ($indVraiBion > 1.25) {
                    //$this->_addLog('error', $demandeId, $reponseId, "Balance Ionique : Valeur non conforme", $codePrelevement);
                    return array("error", "Balance Ionique : Valeur non conforme");
                }
            }
        }
        return true;
    }

    // III.9 Comparaison Balance ionique / conductivité (Feret)
    public function balanceIoniqueTds2($cCationParams, $cAnionParams, $codeRqCationParams, $codeRqAnionParams, $mConductivite) {

        // Vérification de l'existence des paramètres
        $valid = true;
        $cpt = 0;
        $keys = array_keys($cCationParams);
        while ($cpt < count($keys) && $valid) {
            if (is_null($cCationParams[$keys[$cpt]])) {
                $valid = false;
            }
            $cpt++;
        }

        if ($valid) {
            $cpt = 0;
            $keys = array_keys($cAnionParams);
            while ($cpt < count($keys) && $valid) {
                if (is_null($cAnionParams[$keys[$cpt]])) {
                    $valid = false;
                }
                $cpt++;
            }
        }

        if ($valid) {
            if (is_null($mConductivite)) {
                $valid = false;
            }
        }

        if ($valid) {
            $countLq = 0;
            foreach ($codeRqCationParams as $idx => $codeRqCationParam) {
                if ($codeRqCationParam == 10) {
                    $countLq++;
                }
            }

            foreach ($codeRqAnionParams as $idx => $codeRqAnionParam) {
                if ($codeRqAnionParam == 10) {
                    $countLq++;
                }
            }

            if ($countLq >= (count($cAnionParams) + count($cCationParams))) {
                //$this->_addLog('error', $demandeId, $reponseId, "Balance Ionique TDS 2 : Tous les dosages sont en LQ", $codePrelevement);
                return array("error", "Balance Ionique TDS 2 : Tous les dosages sont en LQ");
            } else {
                $cCation = 0;
                foreach ($cCationParams as $idx => $cCationParam) {
                    $cCation += $cCationParam;
                }

                $cAnion = 0;
                foreach ($cAnionParams as $idx => $cAnionParam) {
                    $cAnion += $cAnionParam;
                }

                $tdsEstime = $cCation + $cAnion;
                $tdsModele = 35.18 + 0.68 * $mConductivite;

                $indVraiTds = $tdsEstime - $tdsModele;

                if (175 <= abs($indVraiTds) && abs($indVraiTds) < 280) {
                    //$this->_addLog('warning', $demandeId, $reponseId, "Balance Ionique TDS 2 : Réserve", $codePrelevement, abs($indVraiTds));
                    return array("warning", "Balance Ionique TDS 2 : Réserve");
                } else if (abs($indVraiTds) >= 280) {
                    //$this->_addLog('warning', $demandeId, $reponseId, "Balance Ionique TDS 2 : Valeur non conforme", $codePrelevement, abs($indVraiTds));
                    return array("warning", "Balance Ionique TDS 2 : Valeur non conforme");
                }
            }
        }
        return true;
    }

    // III.10 [PO4] (1433) en P < [P total](1350) 
    public function orthophosphate($mPo4, $mP, $codeRqPo4, $codeRqP) {
        if (!is_null($mPo4) && !is_null($mP)) {
            if (($codeRqPo4 != 10) || ($codeRqP != 10)) {
                $indP = ($mPo4 * 0.3261379) / $mP;
                if (1 < $indP && $indP <= 1.25) {
                    //$this->_addLog('warning', $demandeId, $reponseId, "Orthophosphate : Réserve", $codePrelevement, $indP);
                    return array("warning", "Orthophosphate : Réserve");
                } else if ($indP > 1.25) {
                    //$this->_addLog('error', $demandeId, $reponseId, "Orthophosphate : Valeur non conforme", $codePrelevement, $indP);
                    return array("error", "Orthophosphate : Valeur non conforme");
                }
            }
        }
        return true;
    }

    // III.11 NH4 (1335) en N < Nkj (1319)
    public function ammonium($mNh4, $mNkj, $codeRqNh4, $codeRqNkj) {
        if (!is_null($mNh4) && !is_null($mNkj)) {
            if (($codeRqNh4 != 10) || ($codeRqNkj != 10)) {
                $indP = ($mNh4 * 0.7765) / $mNkj;
                if (1 < $indP && $indP <= 1.25) {
                    //$this->_addLog('warning', $demandeId, $reponseId, "Ammonium : Réserve", $codePrelevement, $indP);
                    return array("warning", "Ammonium : Réserve");
                } else if ($indP > 1.25) {
                    //$this->_addLog('error', $demandeId, $reponseId, "Ammonium : Valeur non conforme", $codePrelevement, $indP);
                    return array("error", "Ammonium : Valeur non conforme");
                }
            }
        }
        return true;
    }

    // III.12 Valeur de pourcentage hors 1312 oxygène (ex : matière sèche ou granulo) : non compris entre 0 et 100 si code unité = 243 ou 246
    public function pourcentageHorsOxygene($tabMesures) {
        $tabRetour = array();
        foreach ($tabMesures as $tabMesure) {
            if (!is_null($tabMesure) && count($tabMesure) > 0) {
                foreach ($tabMesure as $mesure) {
                    if ($mesure > 100 || $mesure < 0) {
                        //$this->_addLog('error', $demandeId, $reponseId, "Valeur pourcentage : pourcentage n'est pas entre 0 et 100", $mesure);
                        $tabRetour[] = array("error", "Valeur pourcentage : pourcentage n'est pas entre 0 et 100");
                    }
                }
            }
        }
        if (count($tabRetour) > 0) {
            return $tabRetour;
        } else {
            return true;
        }
        
    }

    // III.13 Somme des paramètres distincts (1200+1201+1202+1203=5537; 1178+1179 = 1743; 1144+1146+ 1147+1148 = 7146; 2925 + 1292 =  1780) à  (+/- 20%)
    public function sommeParametresDistincts($sommeParams, $resultParams, $params) {
        // Test de validité
        $valid = true;
        $i = 0;
        while ($i < count($sommeParams) && $valid) {
            $j = 0;
            $keys = array_keys($sommeParams[$i]);
            while ($j < count($keys) && $valid) {
                if (is_null($sommeParams[$i][$keys[$j]])) {
                    $valid = false;
                }
                $j++;
            }
            $i++;
        }

        if ($valid) {
            $i = 0;
            while ($i < count($resultParams) && $valid) {
                $j = 0;
                $keys = array_keys($sommeParams[$i]);
                while ($j < count($keys) && $valid) {
                    if (is_null($resultParams[$i][$keys[$j]])) {
                        $valid = false;
                    }
                    $j++;
                }
                $i++;
            }
        }
        
        if ($valid) {
            $tabRetour = array();
            foreach ($sommeParams as $idx => $sommeParam) {
                $somme = 0;
                foreach ($sommeParam as $param) {
                    $somme += $param;
                }

                $percent = ((20 / 100) * $resultParams[$idx]);
                $resultParamMin = $resultParams[$idx] - $percent;
                $resultParamMax = $resultParams[$idx] + $percent;
                if (($resultParamMin > $somme) || ($somme > $resultParamMax)) {
                    //$this->_addLog('error', $demandeId, $reponseId, "Somme Parametres Distincts : La somme des paramètres ne correspond pas au paramètre global " . $params[$idx], $codePrelevement, $somme);
                    $tabRetour[] = array("error", "Somme Parametres Distincts : La somme des paramètres ne correspond pas au paramètre global" . $params[$idx]);
                }
            }
            if (count($tabRetour) > 0) {
                return $tabRetour;
            } else {
                return true;
            }
        }
        return true;
    }

}
