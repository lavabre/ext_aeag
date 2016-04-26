<?php

namespace Aeag\SqeBundle\Utils;

class ControleVraisemblance {

    public function controleVraisemblanceNew($mesure, $codeRq, $codeParametre, $inSitu, $codePrelevement, $demandeId, $reponseId) {

        // III.1
        if (($result = $this->_champsNonRenseignes($mesure, $codeRq, $codeParametre, $inSitu) !== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.1.1
        if (($result = $this->_champsNonRenseignesEtValeursVides($mesure, $codeRq, $codeParametre, $inSitu)!== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.2
        if (($result = $this->_valeursNumeriques($mesure, $codeRq)!== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.3 Valeurs =0 (hors TH (1345), TA (1346), TAC (1347), Temp(1301)) hors codes observations environnementales / résultat = 0 possible pour les paramètres de cette liste (et pour 1345, 1346, 1347 et 1301) => Erreur
        if (($result = $this->_valeursEgalZero($mesure, $codeParametre, $inSitu)!== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.4 Valeurs < 0 (hors température de air 1409, potentiel REDOX 1330)
        if (($result = $this->_valeursInfZero($mesure, $codeParametre)!== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.5 Valeurs avec code remarque '> (3)' hors bactério (1147,1448,1449,1451,5479,6455); code remarque ="Trace (7)"
        if (($result = $this->_valeursSupTrois($codeParametre, $codeRq)!== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.6 1 < pH(1302) < 14
        $mPh = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1302, $demandeId, $reponseId, $codePrelevement);
        if (($result = $this->_pH($mPh)!== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.7 modèle  de WEISS : cohérence Teau, % O2, Concentration O2  sauf si Conductivité > 10 000
        $mTxSatOx = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1312, $demandeId, $reponseId, $codePrelevement);
        $mOxDiss = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1311, $demandeId, $reponseId, $codePrelevement);
        $mTEau = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1301, $demandeId, $reponseId, $codePrelevement);
        $mConductivite = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1303, $demandeId, $reponseId, $codePrelevement);
        if (($result = $this->_modeleWeiss($mTxSatOx, $mOxDiss, $mTEau, $mConductivite)!== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.8 Balance ionique (meq) sauf si tous les résultats < LQ
        $cCationParams = array(1374 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1374, $demandeId, $reponseId, $codePrelevement),
            1335 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement),
            1372 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1372, $demandeId, $reponseId, $codePrelevement),
            1367 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1367, $demandeId, $reponseId, $codePrelevement),
            1375 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1375, $demandeId, $reponseId, $codePrelevement)
        );

        $cAnionParams = array(1433 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement),
            1340 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1340, $demandeId, $reponseId, $codePrelevement),
            1338 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1338, $demandeId, $reponseId, $codePrelevement),
            1337 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1337, $demandeId, $reponseId, $codePrelevement),
            1327 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1327, $demandeId, $reponseId, $codePrelevement),
            1339 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1339, $demandeId, $reponseId, $codePrelevement)
        );
        if (($result = $this->_balanceIonique($cCationParams, $cAnionParams)!== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }
        
        // III.9 Comparaison Balance ionique / conductivité (Feret)
        if (($result = $this->_balanceIoniqueTds2($cCationParams, $cAnionParams, $mConductivite)!== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.10 [PO4] (1433) en P < [P total](1350) 
        $mPo4 = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement);
        $mP = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1350, $demandeId, $reponseId, $codePrelevement);
        if (($result = $this->_orthophosphate($mPo4, $mP)!== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.11 NH4 (1335) en N < Nkj (1319)
        $mNh4 = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement);
        $mNkj = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1319, $demandeId, $reponseId, $codePrelevement);
        if (($result = $this->_ammonium($mNh4, $mNkj)!== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.12 Valeur de pourcentage hors 1312 oxygène (ex : matière sèche ou granulo) : non compris entre 0 et 100 si code unité = 243 ou 246
        $tabMesures = array(243 => $this->repoPgTmpValidEdilabo->getMesureByCodeUnite(243, $demandeId, $reponseId, $codePrelevement, 1312),
            246 => $this->repoPgTmpValidEdilabo->getMesureByCodeUnite(246, $demandeId, $reponseId, $codePrelevement, 1312));
        if (($result = $this->_pourcentageHorsOxygene($tabMesures)!== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.13 Somme des paramètres distincts (1200+1201+1202+1203=5537; 1178+1179 = 1743; 1144+1146+ 1147+1148 = 7146; 2925 + 1292 =  1780) à  (+/- 20%)
        $sommeParams = array(0 => array(1200 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1200, $demandeId, $reponseId, $codePrelevement),
                1201 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1201, $demandeId, $reponseId, $codePrelevement),
                1202 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1202, $demandeId, $reponseId, $codePrelevement),
                1203 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1203, $demandeId, $reponseId, $codePrelevement)
            ),
            1 => array(1178 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1178, $demandeId, $reponseId, $codePrelevement),
                1179 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1179, $demandeId, $reponseId, $codePrelevement)
            ),
            2 => array(1144 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1144, $demandeId, $reponseId, $codePrelevement),
                1146 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1146, $demandeId, $reponseId, $codePrelevement),
                1147 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1147, $demandeId, $reponseId, $codePrelevement),
                1148 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1148, $demandeId, $reponseId, $codePrelevement)
            ),
            3 => array(2925 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(2925, $demandeId, $reponseId, $codePrelevement),
                1292 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1292, $demandeId, $reponseId, $codePrelevement)
            ),
        );

        $resultParams = array(0 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(5537, $demandeId, $reponseId, $codePrelevement),
            1 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1743, $demandeId, $reponseId, $codePrelevement),
            2 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(7146, $demandeId, $reponseId, $codePrelevement),
            3 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1780, $demandeId, $reponseId, $codePrelevement));

        $params = array(5537, 1743, 7146, 1780);
        if (($result = $this->_sommeParametresDistincts($sommeParams, $resultParams, $params)!== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }
    }

    // III.1 Champs non renseignés (valeurs et code remarque)  ou valeurs non numériques ou valeurs impossibles params env
    protected function _champsNonRenseignes($mesure, $codeRq, $codeParametre, $inSitu) {
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
    protected function _champsNonRenseignesEtValeursVides($mesure, $codeRq, $codeParametre, $inSitu) {
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
    protected function _valeursNumeriques($mesure, $codeRq) {
        if (!is_null($codeRq) && $codeRq != 0 && !is_null($mesure)) {
            if (!is_numeric($mesure) || !is_numeric($codeRq)) {
                //$this->_addLog('error', $demandeId, $reponseId, "La valeur n'est pas un nombre", $codePrelevement, $codeParametre);
                return array("error", "La valeur n'est pas un nombre");
            }
        }
        return true;
    }

    // III.3 Valeurs =0 (hors TH (1345), TA (1346), TAC (1347), Temp(1301)) hors codes observations environnementales / résultat = 0 possible pour les paramètres de cette liste (et pour 1345, 1346, 1347 et 1301) => Erreur
    protected function _valeursEgalZero($mesure, $codeParametre, $inSitu) {
        if (!is_null($mesure) && $mesure == 0) {
            if ($codeParametre !== 1345 && $codeParametre !== 1346 && $codeParametre !== 1347 && $codeParametre !== 1301 && $inSitu != 0) {
                //$this->_addLog('error', $demandeId, $reponseId, "Valeur = 0 impossible pour ce paramètre", $codePrelevement, $codeParametre);
                return array("error", "Valeur = 0 impossible pour ce paramètre");
            }
        }
        return true;
    }

    // III.4 Valeurs < 0 (hors température de air 1409, potentiel REDOX 1330)
    protected function _valeursInfZero($mesure, $codeParametre) {
        if ($mesure < 0) {
            if ($codeParametre != 1409 && $codeParametre != 1330 && $codeParametre != 1420 && $codeParametre != 1429) {
                //$this->_addLog('error', $demandeId, $reponseId, "Valeur < 0 impossible pour ce paramètre", $codePrelevement, $codeParametre);
                return array("error", "Valeur < 0 impossible pour ce paramètre");
            }
        }
        return true;
    }

    // III.5 Valeurs avec code remarque '> (3)' hors bactério (1147,1448,1449,1451,5479,6455); code remarque ="Trace (7)"
    protected function _valeursSupTrois($codeParametre, $codeRq) {
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
    //$mPh = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1302, $demandeId, $reponseId, $codePrelevement);
    protected function _pH($mPh) {
        if (!is_null($mPh)) {
            if ($mPh < 1 || $mPh > 14) {
                //$this->_addLog('error', $demandeId, $reponseId, "Le pH n\'est pas entre 1 et 14", $codePrelevement, $mPh);
                return array("error", "Le pH n\'est pas entre 1 et 14");
            }
        }
        return true;
    }

    // III.7 modèle  de WEISS : cohérence Teau, % O2, Concentration O2  sauf si Conductivité > 10 000
    protected function _modeleWeiss($mTxSatOx, $mOxDiss, $mTEau, $mConductivite) {
        if (!is_null($mTxSatOx) && !is_null($mOxDiss) && !is_null($mTEau) && !is_null($mConductivite)) {
            if ($mConductivite < 10000) {
                $mTEauK = $mTEau + 273.15;
                $argExp = -173.4292 + 249.6339 * 100 / $mTEauK + 143.3483 * log($mTEauK / 100) - 21.8492 * $mTEauK / 100;
                $txSatModel = ($mOxDiss * 100) / 1.4276 * exp($argExp);
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
    protected function _balanceIonique($cCationParams, $cAnionParams) {
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
            foreach ($cCationParams as $idx => $cCationParam) {
                if ($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement) == 10) {
                    $countLq++;
                }
            }

            foreach ($cAnionParams as $idx => $cAnionParam) {
                if ($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement) == 10) {
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
    protected function _balanceIoniqueTds2($cCationParams, $cAnionParams, $mConductivite) {

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
            foreach ($cCationParams as $idx => $cCationParam) {
                if ($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement) == 10) {
                    $countLq++;
                }
            }

            foreach ($cAnionParams as $idx => $cAnionParam) {
                if ($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement) == 10) {
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
    protected function _orthophosphate($mPo4, $mP) {
        if (!is_null($mPo4) && !is_null($mP)) {
            if (($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement) != 10) ||
                    ($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1350, $demandeId, $reponseId, $codePrelevement) != 10)) {
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
    protected function _ammonium($mNh4, $mNkj) {
        if (!is_null($mNh4) && !is_null($mNkj)) {
            if (($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement) != 10) ||
                    ($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1319, $demandeId, $reponseId, $codePrelevement) != 10)) {
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
    protected function _pourcentageHorsOxygene($tabMesures) {

        foreach ($tabMesures as $tabMesure) {
            if (!is_null($tabMesure) && count($tabMesure) > 0) {
                foreach ($tabMesure as $mesure) {
                    if ($mesure > 100 || $mesure < 0) {
                        //$this->_addLog('error', $demandeId, $reponseId, "Valeur pourcentage : pourcentage n'est pas entre 0 et 100", $mesure);
                        return array("error", "Valeur pourcentage : pourcentage n'est pas entre 0 et 100");
                    }
                }
            }
        }
    }

    // III.13 Somme des paramètres distincts (1200+1201+1202+1203=5537; 1178+1179 = 1743; 1144+1146+ 1147+1148 = 7146; 2925 + 1292 =  1780) à  (+/- 20%)
    protected function _sommeParametresDistincts($sommeParams, $resultParams, $params) {
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
            foreach ($sommeParams as $idx => $sommeParam) {
                $somme = 0;
                foreach ($sommeParam as $key => $param) {
                    $somme += $param;
                }

                $percent = ((20 / 100) * $resultParams[$idx]);
                $resultParamMin = $resultParams[$idx] - $percent;
                $resultParamMax = $resultParams[$idx] + $percent;
                if (($resultParamMin > $somme) || ($somme > $resultParamMax)) {
                    //$this->_addLog('error', $demandeId, $reponseId, "Somme Parametres Distincts : La somme des paramètres ne correspond pas au paramètre global " . $params[$idx], $codePrelevement, $somme);
                    return array("error", "Somme Parametres Distincts : La somme des paramètres ne correspond pas au paramètre global");
                }
            }
        }
    }

//III.14 Contrôle de vraisemblance par parmètres macropolluants : Résultat d’analyse< Valeur max de la base x 2 
    protected function _controleVraisemblanceMacroPolluants($demandeId, $reponseId, $codePrelevement) {
        $pgProgUnitesPossiblesParams = $this->repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamWithValeurMax();
        foreach ($pgProgUnitesPossiblesParams as $pgProgUnitesPossiblesParam) {
            $mesure = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre($pgProgUnitesPossiblesParam->getCodeParametre(), $demandeId, $reponseId, $codePrelevement);
            if (!is_null($mesure)) {
                if ($mesure > $pgProgUnitesPossiblesParam->getValMax()) {
                    //$this->_addLog('warning', $demandeId, $reponseId, "Controle Vraisemblance Macro Polluants : Le résultat est supérieur à la valeur attendue pour le paramètre " . $codeSandreMacroPolluant[0], $codePrelevement, $mesure);
                    return array("warning", "Controle Vraisemblance Macro Polluants : Le résultat est supérieur à la valeur attendue pour le paramètre");
                }
            }
        }
    }

//III.15 Détection Code remarque Lot 7 (Etat chimique, Substance pertinentes, Complément AEAG, PSEE) :  % de détection différent de 100 (= recherche d'absence de code remarque) suivant liste ref-doc
    protected function _detectionCodeRemarqueLot7($demandeId, $reponseId, $codePrelevement) {

// Vérification marché Demande = marché Aeag
        $demandeAeag = $this->repoPgCmdDemande->isPgCmdDemandesMarcheAeag($demandeId);
        if (count($demandeAeag) > 0) {
// Récupération des codes Parametre de la RAI
            $codesParams = $this->repoPgTmpValidEdilabo->getCodesParametres($demandeId, $reponseId, $codePrelevement);
            $nbCodeRqTot = 0;
            $nbCodeRq1 = 0;
            foreach ($codesParams as $codeParam) {
                if (in_array($codeParam, $this->detectionCodeRemarqueComplet)) {
                    $codeRq = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($codeParam, $demandeId, $reponseId, $codePrelevement);
                    if ($codeRq == 1) {
                        $nbCodeRq1++;
                    }
                    if ($codeRq != 0) {
                        $nbCodeRqTot++;
                    }
                }
            }

            if (($nbCodeRqTot > 0) && ($nbCodeRqTot <= $nbCodeRq1)) {
                //$this->_addLog('error', $demandeId, $reponseId, "Detection Code Remarque Lot 7 : Tous les codes remarques sont à 1", $codePrelevement);
                return array("error", "Detection Code Remarque Lot 7 : Tous les codes remarques sont à 1");
            }
        }
    }

//III.16
    protected function _detectionCodeRemarqueLot8($demandeId, $reponseId, $codePrelevement) {

// Vérification marché Demande = marché Aeag
        $demandeAeag = $this->repoPgCmdDemande->isPgCmdDemandesMarcheAeag($demandeId);

        if (count($demandeAeag) > 0) {
// Récupération des codes Parametre de la RAI
            $codesParams = $this->repoPgTmpValidEdilabo->getCodesParametres($demandeId, $reponseId, $codePrelevement);
            $nbTotalCodeRq = 0;
            $nbCodeRq10 = 0;
            foreach ($codesParams as $codeParam) {
                if (in_array($codeParam, $this->detectionCodeRemarqueMoitie)) {
                    $codeRq = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($codeParam, $demandeId, $reponseId, $codePrelevement);
                    if ($codeRq == 10) {
                        $nbCodeRq10++;
                    }
                    if ($codeRq !== 0) {
                        $nbTotalCodeRq++;
                    }
                }
            }

            if (($nbTotalCodeRq > 0) && ($nbCodeRq10 < ($nbTotalCodeRq / 2))) {
                //$this->_addLog('warning', $demandeId, $reponseId, "Detection Code Remarque Lot 8 : La majorité des codes remarque est à 1", $codePrelevement);
                return array("warning", "Detection Code Remarque Lot 8 : La majorité des codes remarque est à 1");
            }
        }
    }

// III.17
    protected function _controleLqAeag($pgCmdFichierRps, $codePrelevement) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->findBy(array('demandeId' => $demandeId, 'fichierRpsId' => $reponseId, 'codePrelevement' => $codePrelevement));
        if ($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getMarche()->getTypeMarche() == 'MOA') {
            foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
                if (!is_null($pgTmpValidEdilabo->getLqM())) {
                    $lq = $this->repoPgProgLotLqParam->isValidLq($pgCmdFichierRps->getDemande()->getLotan()->getLot(), $pgTmpValidEdilabo->getCodeParametre(), $pgTmpValidEdilabo->getCodeFraction(), $pgTmpValidEdilabo->getLqM());
                    if (count($lq) == 0) {
                        //$this->_addLog('warning', $demandeId, $reponseId, "Controle Lq AEAG : Lq supérieure à la valeur prévue", $codePrelevement, $pgTmpValidEdilabo->getLqM());
                        return array("warning", "Controle Lq AEAG : Lq supérieure à la valeur prévue");
                    }
                }
            }
        }
    }

}
