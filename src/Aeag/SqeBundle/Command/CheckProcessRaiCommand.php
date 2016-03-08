<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;

class CheckProcessRaiCommand extends ContainerAwareCommand {

    private $emSqe;
    private $em;
    private $output;

    protected function configure() {
        $this
                ->setName('rai:check_process')
                ->setDescription('Controle des RAI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->emSqe = $this->getContainer()->get('doctrine')->getManager('sqe');

        $this->output = $output;

        $repoPgCmdFichiersRps = $this->emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $repoPgProgPhases = $this->emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $repoPgTmpValidEdilabo = $this->emSqe->getRepository('AeagSqeBundle:PgTmpValidEdilabo');
        $repoPgLogValidEdilabo = $this->emSqe->getRepository('AeagSqeBundle:PgLogValidEdilabo');

        // On récupère les RAIs dont les phases sont en R25
        $pgProgPhases = $repoPgProgPhases->findOneByCodePhase('R25');
        $pgCmdFichiersRps = $repoPgCmdFichiersRps->findBy(array('phaseFichier' => $pgProgPhases, 'suppr' => 'N'));

        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {

            $this->coherenceRaiDai($pgCmdFichierRps, $repoPgTmpValidEdilabo);

            $this->controleVraisemblance($pgCmdFichierRps, $repoPgTmpValidEdilabo);

            // TODO Changement de la phase en fonction des retours
            /*$logErrors = $repoPgLogValidEdilabo->findBy(array('demandeId' => $pgCmdFichierRps->getDemande()->getId(), 'fichierRpsId' => $pgCmdFichierRps->getId(), 'typeErreur' => 'error'));
            
            if (count($logErrors) > 0) {
                // Erreur
                
            } else { // Succes
                
            }*/
            
            
        }
    }

    protected function coherenceRaiDai($pgCmdFichierRps, $repoPgTmpValidEdilabo) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        //$demandeId = 14;
        $reponseId = $pgCmdFichierRps->getId();
        //$reponseId = 86;
        // Vérif code demande
        if (count($diff = $repoPgTmpValidEdilabo->getDiffCodeDemande($demandeId, $reponseId)) > 0) {
            $this->addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: code demande", null, $diff);
        }

        // Vérif code prélèvement
        if (count($diff = $repoPgTmpValidEdilabo->getDiffCodePrelevementAdd($demandeId, $reponseId)) > 0) {
            $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: codes prélèvement RAI en trop", null, $diff);
        }

        if (count($diff = $repoPgTmpValidEdilabo->getDiffCodePrelevementMissing($demandeId, $reponseId)) > 0) {
            $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: code prélèvement RAI manquant", null, $diff);
        }

        // Vérif Date prélèvement, si hors période
        $codePrelevs = $repoPgTmpValidEdilabo->getCodePrelevement($demandeId, $reponseId);
        foreach ($codePrelevs as $codePrelev) {
            // Vérification de la date de prélèvement
            $datePrelRps = $repoPgTmpValidEdilabo->getDatePrelevement($codePrelev["codePrelevement"], $demandeId, $reponseId);
            $datePrelRps = new \DateTime($datePrelRps["datePrel"]);

            $datePrelDmd = $repoPgTmpValidEdilabo->getDatePrelevement($codePrelev["codePrelevement"], $demandeId);
            $datePrelDmdMin = new \DateTime($datePrelDmd["datePrel"]);

            $delaiPrel = $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getDelaiPrel();
            //$delaiPrel = 7;
            if (is_null($delaiPrel) || $delaiPrel == 0) {
                $delaiPrel = 7;
            }

            $datePrelDmdMax = clone $datePrelDmdMin;
            $datePrelDmdMax->add(new \DateInterval('P' . $delaiPrel . 'D'));

            if ($datePrelDmdMin > $datePrelRps || $datePrelRps > $datePrelDmdMax) {
                $this->addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Date Prelevement hors période", $codePrelev["codePrelevement"]);
            }

            // Vérif code intervenant
            if (count($diff = $repoPgTmpValidEdilabo->getDiffPreleveur($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                if ($this->existePresta($diff)) {
                    $this->addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Preleveur", $codePrelev["codePrelevement"], $diff);
                } else {
                    $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Preleveur", $codePrelev["codePrelevement"], $diff);
                }
            }

            if (count($diff = $repoPgTmpValidEdilabo->getDiffLabo($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                if ($this->existePresta($diff)) {
                    $this->addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Laboratoire", $codePrelev["codePrelevement"], $diff);
                } else {
                    $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Laboratoire", $codePrelev["codePrelevement"], $diff);
                }
            }

            // Vérif STQ : concordance STQ RAI (unique ou multiple) / DAI : stations rajoutées => Erreur
            if (count($diff = $repoPgTmpValidEdilabo->getDiffCodeStation($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Date Prelevement", $codePrelev["codePrelevement"], $diff);
            }

            // paramètres/unité : si unité changée => erreur
            $mesuresRps = $repoPgTmpValidEdilabo->getMesures($codePrelev["codePrelevement"], $demandeId, $reponseId);
            $mesuresDmd = $repoPgTmpValidEdilabo->getMesures($codePrelev["codePrelevement"], $demandeId);
            if (count($mesuresRps) > 0 && count($mesuresDmd) > 0) {
                foreach ($mesuresRps as $idx => $mesureRps) {
                    if (($mesureRps['codeParametre'] == $mesuresDmd[$idx]['codeParametre']) && ($mesureRps['codeUnite'] != $mesuresDmd[$idx]['codeUnite'])) {
                        if (($handle = fopen(getcwd() . "/web/tablesCorrespondancesRai/corresUnites.csv", "r")) !== FALSE) {
                            $row = 0;
                            $found = false;
                            while ((($data = fgetcsv($handle, 1000, ";")) !== FALSE) || $found == false) {
                                if ($row !== 0) {
                                    if (($mesureRps['codeParametre'] == $data[0]) && ($mesureRps['codeFraction'] == $data[1]) && ($mesureRps['codeUnite'] == $data[3])) {
                                        $found = true;
                                    }
                                }
                                $row++;
                            }
                            if ($found == false) {
                                $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Unité changée", $codePrelev["codePrelevement"], $mesureRps['codeUnite']);
                            }
                        }
                        $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Unité changée", $codePrelev["codePrelevement"]);
                    }
                }
            } else {
                $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Mesures absentes", $codePrelev["codePrelevement"]);
            }

            // paramètres/unité : rajout de paramètres => avertissement
            if (count($diff = $repoPgTmpValidEdilabo->getDiffCodeParametreAdd($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                //Avertissement
                $this->addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Rajout de paramètre", $codePrelev["codePrelevement"], $diff);
            }

            // paramètres/unité : paramètre manquant => erreur
            if (count($diff = $repoPgTmpValidEdilabo->getDiffCodeParametreMissing($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                $this->addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Paramètre manquant", $codePrelev["codePrelevement"], $diff);
            }
        }
    }

    protected function existePresta($codeIntervenants) {
        foreach ($codeIntervenants as $codeIntervenant) {
            $repoPgRefCorresPresta = $this->emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
            $pgRefCorresPrestas = $repoPgRefCorresPresta->findByCodeSiret($codeIntervenant);
            if (count($pgRefCorresPrestas) == 0) {
                $pgRefCorresPrestas = $repoPgRefCorresPresta->findByCodeSandre($codeIntervenant);
                if (count($pgRefCorresPrestas) == 0) {
                    return false;
                }
            }
            return true;
        }
    }

    protected function controleVraisemblance($pgCmdFichierRps, $repoPgTmpValidEdilabo) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        $pgTmpValidEdilabos = $repoPgTmpValidEdilabo->findBy(array('fichierRpsId' => $reponseId, 'demandeId' => $demandeId));

        // Contrôles sur toutes les valeurs insérées
        $this->addLog('error', $demandeId, $reponseId, "TEST controleVraisemblance");
        foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
            $mesure = $pgTmpValidEdilabo->getResM();
            $codeRq = $pgTmpValidEdilabo->getCodeRqM();
            $codeParametre = $pgTmpValidEdilabo->getCodeParametre();

            // Codes Params Env
            $codeParamsEnv = array(
                1015, 1018, 1408, 1409, 1410, 1411, 1412, 1413, 1415, 1416,
                1420, 1422, 1423, 1424, 1425, 1427, 1428, 1429, 1434, 1726,
                1799, 1841, 1947, 1948, 5915, 6565, 6566, 6567, 7036
            );

            // III.1 Champs non renseignés (valeurs et code remarque) ou valeurs non numériques ou valeurs impossibles params env / code remarque peut ne pas être renseigné pour cette liste (car réponse en edilabo 1.0) => erreur
            if (is_null($mesure) || is_null($codeRq)) {
                $this->addLog('error', $demandeId, $reponseId, "La valeur n'existe pas", null, $codeParametre);
            }
            if (!is_numeric($mesure) || !is_numeric($codeRq)) {
                $this->addLog('error', $demandeId, $reponseId, "La valeur n'est pas un nombre", null, $codeParametre);
            }

            // III.2 Si valeur "vide" avec code remarque "0" hors lecture échelle (1429),,,,,,,'ABSENT' / on doit avoir un code remarque = 0 pour les valeurs vides, sinon avertissement, sauf pour le 1429 (cote échelle) => Avertissement
            if ($mesure == '') {
                if ($codeRq != 0 && $codeParametre != 1429) {
                    $this->addLog('error', $demandeId, $reponseId, "Valeur vide", null, $codeParametre);
                }
            }

            // III.3 Valeurs =0 (hors TH (1345), TA (1346), TAC (1347), Temp(1301)) hors codes observations environnementales / résultat = 0 possible pour les paramètres de cette liste (et pour 1345, 1346, 1347 et 1301) => Erreur
            if ($mesure == 0) {
                if ($codeParametre !== 1345 && $codeParametre !== 1346 && $codeParametre !== 1347 && $codeParametre !== 1301 && !in_array($codeParametre, $codeParamsEnv)) {
                    $this->addLog('error', $demandeId, $reponseId, "Valeur = 0", null, $codeParametre);
                }
            }

            // III.4 Valeurs < 0 (hors température de air 1409, potentiel REDOX 1330)
            if ($mesure < 0) {
                if ($codeParametre != 1409 && $codeParametre != 1330) {
                    $this->addLog('error', $demandeId, $reponseId, "Valeur < 0", null, $codeParametre);
                }
            }

            // III.5 Valeurs avec code remarque '> (3)' hors bactério (1147,1448,1449,1451,5479,6455); code remarque ="Trace (7)"
            $codeParamsBacterio = array(
                1147, 1448, 1449, 1451, 5479, 6455
            );
            if (($codeRq > 3 && !in_array($codeParametre, $codeParamsBacterio)) || $codeRq == 7) {
                $this->addLog('error', $demandeId, $reponseId, "Code Remarque > 3 ou == 7", null, $codeParametre);
            }
        }
        $this->addLog('error', $demandeId, $reponseId, "TEST On passe aux controles spécifiques");
        
        $codePrelevements = $repoPgTmpValidEdilabo->getCodePrelevement($demandeId, $reponseId);
        foreach($codePrelevements as $codePrelevement) {
            $codePrelevement = $codePrelevement['codePrelevement'];
            // Contrôles spécifiques
            // III.6
            $this->pH($repoPgTmpValidEdilabo, $demandeId, $reponseId, $codePrelevement);

            // III.7
            $this->modeleWeiss($repoPgTmpValidEdilabo, $demandeId, $reponseId, $codePrelevement);

            // III.8 
            $this->balanceIonique($repoPgTmpValidEdilabo, $demandeId, $reponseId,$codePrelevement);

            // III.9
            $this->balanceIoniqueTds2($repoPgTmpValidEdilabo, $demandeId, $reponseId, $codePrelevement);

            // III.10
            $this->ortophosphate($repoPgTmpValidEdilabo, $demandeId, $reponseId, $codePrelevement);

            // III.11
            $this->ammonium($repoPgTmpValidEdilabo, $demandeId, $reponseId, $codePrelevement);
            
        }
        
    }

    // III.6 1 < pH(1302) < 14
    protected function pH($repoPgTmpValidEdilabo, $demandeId, $reponseId, $codePrelevement) {
        $this->addLog('error', $demandeId, $reponseId, "TEST pH", $codePrelevement);
        $mPh = $repoPgTmpValidEdilabo->getMesureByCodeParametre(1302, $demandeId, $reponseId, $codePrelevement);
        if (!is_null($mPh)) {
            if ($mPh < 1 || $mPh > 14) {
                $this->addLog('error', $demandeId, $reponseId, "Le pH n\'est pas entre 1 et 14", $codePrelevement, $mPh);
            }
        } else {
            $this->addLog('error', $demandeId, $reponseId, "CodeParametre inexistant", $codePrelevement, 1302);
        }
    }

    // III.7 modèle  de WEISS : cohérence Teau, % O2, Concentration O2  sauf si Conductivité > 10 000
    protected function modeleWeiss($repoPgTmpValidEdilabo, $demandeId, $reponseId, $codePrelevement) {
        $this->addLog('error', $demandeId, $reponseId, "TEST modele Weiss",$codePrelevement);
        $mTxSatOx = $repoPgTmpValidEdilabo->getMesureByCodeParametre(1312, $demandeId, $reponseId, $codePrelevement);
        $mOxDiss = $repoPgTmpValidEdilabo->getMesureByCodeParametre(1311, $demandeId, $reponseId, $codePrelevement);
        $mTEau = $repoPgTmpValidEdilabo->getMesureByCodeParametre(1301, $demandeId, $reponseId, $codePrelevement);
        $mConductivite = $repoPgTmpValidEdilabo->getMesureByCodeParametre(1303, $demandeId, $reponseId, $codePrelevement);

        if (!is_null($mTxSatOx) && !is_null($mOxDiss) && !is_null($mTEau) && !is_null($mConductivite)) {
            if ($mConductivite < 10000) {
                $mTEauK = $mTEau + 273.15;
                $argExp = -173.4292 + 249.6339 * 100 / $mTEauK + 143.3483 * log($mTEauK / 100) - 21.8492 * $mTEauK / 100;
                $txSatModel = 1.4276 * exp($argExp);
                $indVraiWess = $txSatModel - $mTxSatOx;

                if (abs($indVraiWess) > 25) {
                    //error
                    $this->addLog('error', $demandeId, $reponseId, "Modele de Weiss : valeurs non conformes", $codePrelevement, abs($indVraiWess));
                } else if (10 < abs($indVraiWess) && abs($indVraiWess) <= 25) {
                    // Avertissement
                    $this->addLog('warning', $demandeId, $reponseId, "Modele de Weiss : valeur réservée", $codePrelevement, abs($indVraiWess));
                }
            } else {
                //error
                $this->addLog('error', $demandeId, $reponseId, "Modele de Weiss : Conductivité supérieur à 10000", $codePrelevement, $mConductivite);
            }
        } else {
            // error 
            $this->addLog('error', $demandeId, $reponseId, "Modele de Weiss : Code Parametre inexistant", $codePrelevement);
        }
    }

    // III.8 Balance ionique (meq) sauf si tous les résultats < LQ
    protected function balanceIonique($repoPgTmpValidEdilabo, $demandeId, $reponseId, $codePrelevement) {
        $this->addLog('error', $demandeId, $reponseId, "TEST balance ionique", $codePrelevement);
        $cCationParams = array(1374 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1374, $demandeId, $reponseId, $codePrelevement),
            1335 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement),
            1372 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1372, $demandeId, $reponseId, $codePrelevement),
            1367 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1367, $demandeId, $reponseId, $codePrelevement),
            1375 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1375, $demandeId, $reponseId, $codePrelevement)
        );

        $cAnionParams = array(1433 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement),
            1340 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1340, $demandeId, $reponseId, $codePrelevement),
            1338 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1338, $demandeId, $reponseId, $codePrelevement),
            1337 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1337, $demandeId, $reponseId, $codePrelevement),
            1327 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1327, $demandeId, $reponseId, $codePrelevement),
            1339 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1339, $demandeId, $reponseId, $codePrelevement)
        );
        $this->addLog('error', $demandeId, $reponseId, "TEST balance ionique On passe la", $codePrelevement);
        // Tests de validité
        $countLq = 0;
        foreach ($cCationParams as $idx => $cCationParam) {
            if (is_null($cCationParam)) {
                $this->addLog('error', $demandeId, $reponseId, "Balance Ionique : Parametre Cation inexistant", $codePrelevement);
            }
            
            if ($repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement) == 10) {
                $countLq++;
            }
        }

        foreach ($cAnionParams as $idx => $cAnionParam) {
            if (is_null($cAnionParam)) {
                $this->addLog('error', $demandeId, $reponseId, "Balance Ionique : Parametre Anion inexistant", $codePrelevement);
            }

            if ($repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement) == 10) {
                $countLq++;
            }
        }

        if ($countLq >= (count($cAnionParams) + count($cCationParams))) {
            $this->addLog('error', $demandeId, $reponseId, "Balance Ionique : Lq > somme des parametres", $codePrelevement);
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
                $this->output->writeln(var_dump($cCationParam));
                $this->output->writeln(var_dump($vCationParams[$idx]));
                $cCation += $cCationParam / $vCationParams[$idx];
            }

            $cAnion = 0;
            foreach ($cAnionParams as $idx => $cAnionParam) {
                $cAnion += $cAnionParam / $vAnionParams[$idx];
            }

            $indVraiBion = ($cCation - $cAnion);

            if (0.5 < $indVraiBion && $indVraiBion <= 1.25) {
                $this->addLog('warning', $demandeId, $reponseId, "Balance Ionique : Valeur réservée", $codePrelevement);
            } else if ($indVraiBion > 1.25) {
                $this->addLog('error', $demandeId, $reponseId, "Balance Ionique : Valeur non conforme", $codePrelevement);
            }
        }
    }

    // III.9 Comparaison Balance ionique / conductivité (Feret)
    protected function balanceIoniqueTds2($repoPgTmpValidEdilabo, $demandeId, $reponseId, $codePrelevement) {
        $this->addLog('error', $demandeId, $reponseId, "TEST balanceIoniqueTds2", $codePrelevement);
         $cCationParams = array(1374 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1374, $demandeId, $reponseId, $codePrelevement),
            1335 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement),
            1372 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1372, $demandeId, $reponseId, $codePrelevement),
            1367 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1367, $demandeId, $reponseId, $codePrelevement),
            1375 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1375, $demandeId, $reponseId, $codePrelevement)
        );

        $cAnionParams = array(1433 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement),
            1340 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1340, $demandeId, $reponseId, $codePrelevement),
            1338 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1338, $demandeId, $reponseId, $codePrelevement),
            1337 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1337, $demandeId, $reponseId, $codePrelevement),
            1327 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1327, $demandeId, $reponseId, $codePrelevement),
            1339 => $repoPgTmpValidEdilabo->getMesureByCodeParametre(1339, $demandeId, $reponseId, $codePrelevement)
        );

        $mConductivite = $repoPgTmpValidEdilabo->getMesureByCodeParametre(1303, $demandeId, $reponseId, $codePrelevement);

        // Tests de validité
        $countLq = 0;
        foreach ($cCationParams as $idx => $cCationParam) {
            if (is_null($cCationParam)) {
                $this->addLog('error', $demandeId, $reponseId, "Balance Ionique TDS 2 : Parametre Cation inexistant", $codePrelevement);
            }

            if ($repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement) == 10) {
                $countLq++;
            }
        }

        foreach ($cAnionParams as $idx => $cAnionParam) {
            if (is_null($cAnionParam)) {
                $this->addLog('error', $demandeId, $reponseId, "Balance Ionique TDS 2 : Parametre Anion inexistant", $codePrelevement);
            }

            if ($repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement) == 10) {
                $countLq++;
            }
        }

        if ($countLq >= (count($cAnionParams) + count($cCationParams))) {
            $this->addLog('error', $demandeId, $reponseId, "Balance Ionique TDS 2 : Lq > somme des parametres", $codePrelevement);
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
                $this->addLog('warning', $demandeId, $reponseId, "Balance Ionique TDS 2 : Réserve", $codePrelevement);
            } else if (abs($indVraiTds) >= 280) {
                $this->addLog('warning', $demandeId, $reponseId, "Balance Ionique TDS 2 : Valeur non conforme", $codePrelevement);
            }
        }
    }
    // III.10 [PO4] (1433) en P < [P total](1350) 
    protected function ortophosphate($repoPgTmpValidEdilabo, $demandeId, $reponseId, $codePrelevement) {
        $this->addLog('error', $demandeId, $reponseId, "TEST ortophosphate", $codePrelevement);
        $mPo4 = $repoPgTmpValidEdilabo->getMesureByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement);
        $mP = $repoPgTmpValidEdilabo->getMesureByCodeParametre(1350, $demandeId, $reponseId, $codePrelevement);

        if (!is_null($mPo4) && !is_null($mP)) {
            if (($repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement) == 10) &&
                    ($repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1350, $demandeId, $reponseId, $codePrelevement) == 10)) {
                $this->addLog('error', $demandeId, $reponseId, "Ortophosphate : tous les dosages sont en LQ", $codePrelevement);
            } else {
                $indP = $mPo4 / $mP;
                if (1 < $indP && $indP <= 1.25) {
                    $this->addLog('warning', $demandeId, $reponseId, "Ortophosphate : Réserve", $codePrelevement);
                } else if ($indP > 1.25) {
                    $this->addLog('error', $demandeId, $reponseId, "Ortophosphate : Valeur non conforme", $codePrelevement);
                }
            }
        } else {
            $this->addLog('error', $demandeId, $reponseId, "Ortophosphate : code paramètre inexistant", $codePrelevement);
            
        }
    }
    
    // III.11 NH4 (1335) en N < Nkj (1319)
    protected function ammonium($repoPgTmpValidEdilabo, $demandeId, $reponseId, $codePrelevement) {
        $this->addLog('error', $demandeId, $reponseId, "TEST ammonium", $codePrelevement);
        $mNh4 = $repoPgTmpValidEdilabo->getMesureByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement);
        $mNkj = $repoPgTmpValidEdilabo->getMesureByCodeParametre(1319, $demandeId, $reponseId, $codePrelevement);
        
        if (!is_null($mNh4) && !is_null($mNkj)) {
            if (($repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement) == 10) &&
                    ($repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1319, $demandeId, $reponseId, $codePrelevement) == 10)) {
                $this->addLog('error', $demandeId, $reponseId, "Ammonium : tous les dosages sont en LQ",$codePrelevement);
            } else {
                $indP = $mNh4 / $mNkj;
                if (1 < $indP && $indP <= 1.25) {
                    $this->addLog('warning', $demandeId, $reponseId, "Ammonium : Réserve", $codePrelevement);
                } else if ($indP > 1.25) {
                    $this->addLog('error', $demandeId, $reponseId, "Ammonium : Valeur non conforme", $codePrelevement);
                }
            }
        } else {
            $this->addLog('error', $demandeId, $reponseId, "Ammonium : code paramètre inexistant", $codePrelevement);
            
        }
    }
    
    protected function sommeParametresDistincts($repoPgTmpValidEdilabo, $demandeId, $reponseId, $codePrelevement) {
        
    }
    
    public function convertMultiArray($array) {
        $out = implode(",", array_map(function($a) {
                    return implode("-", $a);
                }, $array));
        return $out;
    }
    
    protected function addLog($typeErreur, $demandeId, $fichierRpsId, $message, $codePrelevement = null, $commentaire = null) {
        $dateLog = new \DateTime();
        if (!is_null($commentaire) && is_array($commentaire)) {
            $commentaire = $this->convertMultiArray($commentaire);
        }
        $pgLogValidEdilabo = new \Aeag\SqeBundle\Entity\PgLogValidEdilabo($demandeId, $fichierRpsId, $typeErreur, $message, $dateLog, $codePrelevement, $commentaire);

        $this->emSqe->persist($pgLogValidEdilabo);
        $this->emSqe->flush();
    }

}
