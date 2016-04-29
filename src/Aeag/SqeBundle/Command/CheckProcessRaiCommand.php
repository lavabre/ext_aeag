<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CheckProcessRaiCommand extends AeagCommand {

    private $phase82atteinte = false;

    protected function configure() {
        $this
                ->setName('rai:check_process')
                ->setDescription('Controle des RAI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        parent::execute($input, $output);

        // On récupère les RAIs dont les phases sont en R25
        $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase('R25');
        $pgCmdFichiersRps = $this->repoPgCmdFichiersRps->findBy(array('phaseFichier' => $pgProgPhases, 'typeFichier' => 'RPS', 'suppr' => 'N'));
        $cptRaisTraitesOk = 0;
        $cptRaisTraitesNok = 0;
        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {

            // TODO On vérifie que l'on insère pas deux fois la même RAI
            $this->_coherenceRaiDai($pgCmdFichierRps);

            // Changement de la phase en fonction des retours
            $logErrorsCoherence = $this->repoPgLogValidEdilabo->findBy(array('demandeId' => $pgCmdFichierRps->getDemande()->getId(), 'fichierRpsId' => $pgCmdFichierRps->getId(), 'typeErreur' => 'error'));
            $logWarningsCoherence = $this->repoPgLogValidEdilabo->findBy(array('demandeId' => $pgCmdFichierRps->getDemande()->getId(), 'fichierRpsId' => $pgCmdFichierRps->getId(), 'typeErreur' => 'warning'));

            if (count($logErrorsCoherence) > 0) {
                $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R82');
                $this->phase82atteinte = true;
            } else {
                if (count($logWarningsCoherence) > 0) { // Avec avertissements
                    $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R31');
                } else { // Sans avertissements
                    $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R30');
                }
            }
            $this->_controleVraisemblance($pgCmdFichierRps);
            
            // Changement de la phase en fonction des retours
            $logErrorsVraisemblance = $this->repoPgLogValidEdilabo->findBy(array('demandeId' => $pgCmdFichierRps->getDemande()->getId(), 'fichierRpsId' => $pgCmdFichierRps->getId(), 'typeErreur' => 'error'));
            $logWarningsVraisemblance = $this->repoPgLogValidEdilabo->findBy(array('demandeId' => $pgCmdFichierRps->getDemande()->getId(), 'fichierRpsId' => $pgCmdFichierRps->getId(), 'typeErreur' => 'warning'));

            if (count($logErrorsVraisemblance) > 0) {
                // Erreur
                $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R84', $this->phase82atteinte);
            } else { // Succes
                if (count($logWarningsVraisemblance) > 0) { // Avec avertissements
                    $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R41', $this->phase82atteinte);
                } else { // Sans avertissements
                    $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R40', $this->phase82atteinte);
                }
            }

            // Compléter le fichier de logs
            $this->_insertFichierLog($pgCmdFichierRps);

            // Envoi mail
            $context = $this->getContainer()->get('router')->getContext();
            $context->setHost($this->getContainer()->getParameter('router_host'));
            $context->setScheme($this->getContainer()->getParameter('router_scheme'));
            $context->setBaseUrl($this->getContainer()->getParameter('router_baseurl'));

            $objetMessage = "SQE - RAI : Fichier " . $pgCmdFichierRps->getNomFichier() . " - Récapitulatif";
            $url = $this->getContainer()->get('router')->generate('AeagSqeBundle_echangefichiers_reponses_telecharger', array("reponseId" => $pgCmdFichierRps->getId(), "typeFichier" => "CR"), UrlGeneratorInterface::ABSOLUTE_URL);
            $txtMessage = "Lot : " . $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getNomLot() . "<br/>";
            $txtMessage .= "Période : " . $pgCmdFichierRps->getDemande()->getPeriode()->getLabelPeriode() . "<br/>";
            $txtMessage .= 'Le traitement de la RAI ' . $pgCmdFichierRps->getNomFichier() . ' est maintenant terminé <br/>';
            $txtMessage .= "L'état final est le suivant : <strong>" . $pgCmdFichierRps->getPhaseFichier()->getLibellePhase() . "</strong><br/>";
            $txtMessage .= 'Vous pouvez lire le récapitulatif dans le fichier disponible à l\'adresse suivante : <a href="' . $url . '">' . $pgCmdFichierRps->getNomFichierCompteRendu() . '</a>';
            $destinataire = $this->repoPgProgWebUsers->findOneByPrestataire($pgCmdFichierRps->getDemande()->getPrestataire());
            $mailer = $this->getContainer()->get('mailer');
            if (!$this->getContainer()->get('aeag_sqe.message')->createMail($this->em, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                $this->_addLog('warning', $pgCmdFichierRps->getDemande()->getId(), $pgCmdFichierRps->getId(), "Erreur lors de l\'envoi de mail dans le process de verification des RAIs", null, $destinataire);
            }

            // Insertion données brutes
            if ((count($logErrorsVraisemblance) == 0) && (count($logErrorsCoherence) == 0 )) {
                $this->_integrationDonneesBrutes($pgCmdFichierRps);

                // Evolution de la phase
                $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R45', $this->phase82atteinte);
                
                $this->_updatePhaseDemande($pgCmdFichierRps->getDemande());
                
                // Fichier csv
                //$this->_exportCsvDonneesBrutes($pgCmdFichierRps);

                $cptRaisTraitesOk++;
            } else {
                $cptRaisTraitesNok++;
            }

            // Vider la table tempo des lignes correspondant à la RAI
            $this->_cleanTmpTable($pgCmdFichierRps);
        }

        $date = new \DateTime();
        $cptRaisTraitesTot = $cptRaisTraitesOk + $cptRaisTraitesNok;
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : ' . $cptRaisTraitesTot . " RAI(s) traitée(s), " . $cptRaisTraitesOk . " OK, " . $cptRaisTraitesNok . " NOK");
    }

    protected function _coherenceRaiDai($pgCmdFichierRps) {
        $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R26');

        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();

        // Vérif code demande
        if (count($diff = $this->repoPgTmpValidEdilabo->getDiffCodeDemande($demandeId, $reponseId)) > 0) {
            $this->_addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: code demande", null, $diff);
        }

        // Vérif code prélèvement
        if (count($diff = $this->repoPgTmpValidEdilabo->getDiffCodePrelevementAdd($demandeId, $reponseId)) > 0) {
            $this->_addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: codes prélèvement RAI en trop", null, $diff);
        }

        // A tester à l'issue des données brutes (lorsqu'on est censé avoir tous les codes prélevements de la DAI)
//        if (count($diff = $this->repoPgTmpValidEdilabo->getDiffCodePrelevementMissing($demandeId, $reponseId)) > 0) {
//            $this->_addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: code prélèvement RAI manquant", null, $diff);
//        }
        // Vérif Date prélèvement, si hors période
        $codePrelevs = $this->repoPgTmpValidEdilabo->getCodePrelevement($demandeId, $reponseId);
        foreach ($codePrelevs as $codePrelev) {
            // Vérification de la date de prélèvement
            $datePrelRps = $this->repoPgTmpValidEdilabo->getDatePrelevement($codePrelev["codePrelevement"], $demandeId, $reponseId);
            $datePrelRps = new \DateTime($datePrelRps["datePrel"]);

            $datePrelDmd = $this->repoPgTmpValidEdilabo->getDatePrelevement($codePrelev["codePrelevement"], $demandeId);
            $datePrelDmdMin = new \DateTime($datePrelDmd["datePrel"]);

            $delaiPrel = $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getDelaiPrel();
            if (is_null($delaiPrel) || $delaiPrel == 0) {
                $delaiPrel = 7;
            }

            $datePrelDmdMax = clone $datePrelDmdMin;
            $datePrelDmdMax->add(new \DateInterval('P' . $delaiPrel . 'D'));

            if ($datePrelDmdMin > $datePrelRps || $datePrelRps > $datePrelDmdMax) {
                $this->_addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Date Prelevement hors période", $codePrelev["codePrelevement"]);
            }

            // Vérif code intervenant
            if (count($diff = $this->repoPgTmpValidEdilabo->getDiffPreleveur($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                if ($this->_existePresta($diff)) {
                    $this->_addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Preleveur", $codePrelev["codePrelevement"], $diff);
                } else {
                    $this->_addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Preleveur", $codePrelev["codePrelevement"], $diff);
                }
            }

            if (count($diff = $this->repoPgTmpValidEdilabo->getDiffLabo($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                if ($this->_existePresta($diff)) {
                    $this->_addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Laboratoire", $codePrelev["codePrelevement"], $diff);
                } else {
                    $this->_addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Laboratoire", $codePrelev["codePrelevement"], $diff);
                }
            }

            // Vérif STQ : concordance STQ RAI (unique ou multiple) / DAI : stations rajoutées => Erreur
            if (count($diff = $this->repoPgTmpValidEdilabo->getDiffCodeStation($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                $this->_addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Date Prelevement", $codePrelev["codePrelevement"], $diff);
            }

            // paramètres/unité : si unité changée => erreur
            $mesuresRps = $this->repoPgTmpValidEdilabo->getMesures($codePrelev["codePrelevement"], $demandeId, $reponseId);
            if (count($mesuresRps) > 0) {
                foreach ($mesuresRps as $mesureRps) {
                    $mesureDmd = $this->repoPgTmpValidEdilabo->getMesuresByCodeParametre($mesureRps['codeParametre'], $codePrelev["codePrelevement"], $demandeId);
                    if ((count($mesureDmd) == 1) && ($mesureRps['codeUnite'] != $mesureDmd['codeUnite'])) {
                        $pgSandreFractions = $this->repoPgSandreFractions->findOneByCodeFraction($mesureRps['codeFraction']);
                        $pgProgUnitesPossiblesParam = $this->repoPgProgUnitesPossiblesParam->findOneBy(array('codeParametre' => $mesureRps['codeParametre'], 'codeUnite' => $mesureRps['codeUnite'], 'natureFraction' => $pgSandreFractions->getNatureFraction()));
                        if (!$pgProgUnitesPossiblesParam) {
                            $this->_addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Unité changée", $codePrelev["codePrelevement"], $mesureRps['codeUnite']);
                        }
                    }
                }
            } else {
                $this->_addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Mesures absentes", $codePrelev["codePrelevement"]);
            }

            // paramètres/unité : rajout de paramètres => avertissement
            if (count($diff = $this->repoPgTmpValidEdilabo->getDiffCodeParametreAdd($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                //Avertissement
                $this->_addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Rajout de paramètre", $codePrelev["codePrelevement"], $diff);
            }

            // paramètres/unité : paramètre manquant => erreur
            if (count($diff = $this->repoPgTmpValidEdilabo->getDiffCodeParametreMissing($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                if ((count($diff) == 1) && in_array(1429, $diff)) {
                    $this->_addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Paramètre manquant", $codePrelev["codePrelevement"], $diff);
                } else {
                    $this->_addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Paramètre manquant", $codePrelev["codePrelevement"], $diff);
                }
            }
        }
    }

    protected function _existePresta($codeIntervenants) {
        foreach ($codeIntervenants as $codeIntervenant) {

            $pgRefCorresPrestas = $this->repoPgRefCorresPresta->findByCodeSiret($codeIntervenant);
            if (count($pgRefCorresPrestas) == 0) {
                $pgRefCorresPrestas = $this->repoPgRefCorresPresta->findByCodeSandre($codeIntervenant);
                if (count($pgRefCorresPrestas) == 0) {
                    return false;
                }
            }
            return true;
        }
    }

    protected function _controleVraisemblance($pgCmdFichierRps) {
        $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R36', $this->phase82atteinte);

        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        $codePrelevements = $this->repoPgTmpValidEdilabo->getCodePrelevement($demandeId, $reponseId);
        // Contrôles sur toutes les valeurs insérées
        foreach ($codePrelevements as $codePrelevement) {
            $codePrelevement = $codePrelevement['codePrelevement'];

            $meSituHydro = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1726, $demandeId, $reponseId, $codePrelevement);

            if (!is_null($meSituHydro) && $meSituHydro <= 2) {
                $this->_addLog('warning', $demandeId, $reponseId, "Situation Hydro = " . $meSituHydro, $codePrelevement, 1726);
                $codesRqValides = $this->repoPgTmpValidEdilabo->getCodeRqValideByCodePrelevement($demandeId, $reponseId, $codePrelevement);
                if (count($codesRqValides) > 0) {
                    $this->_addLog('error', $demandeId, $reponseId, "Situation Hydro : Code Remarque impossible ", $codePrelevement, 1726);
                } else {
                    //todo update tmp set code_remarque = 0 and res = null where in_situ = 0
                    $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->findBy(array('fichierRpsId' => $reponseId, 'demandeId' => $demandeId, 'codePrelevement' => $codePrelevement, 'inSitu' => 0));
                    foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
                        $pgTmpValidEdilabo->setCodeRqM(0);
                        $pgTmpValidEdilabo->setResM(null);
                    }
                    $this->emSqe->flush();
                }
            } else {
                $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->findBy(array('fichierRpsId' => $reponseId, 'demandeId' => $demandeId, 'codePrelevement' => $codePrelevement));
                foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
                    // Appel du service
                    $this->controleVraisemblanceProcess($pgTmpValidEdilabo, $codePrelevement, $pgCmdFichierRps);
                }
            }
        }
    }
    
    public function controleVraisemblanceProcess($pgTmpValidEdilabo, $codePrelevement, $pgCmdFichierRps) {
        
        $mesure = $pgTmpValidEdilabo->getResM();
        $codeRq = $pgTmpValidEdilabo->getCodeRqM();
        $codeParametre = $pgTmpValidEdilabo->getCodeParametre();
        $inSitu = $pgTmpValidEdilabo->getInSitu();
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        
        $controleVraisemblaceService = $this->getContainer()->get('aeag_sqe.controle_vraisemblance');
        // III.1
        if (($result = $controleVraisemblaceService->champsNonRenseignes($mesure, $codeRq, $codeParametre, $inSitu) !== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.1.1
        if (($result = $controleVraisemblaceService->champsNonRenseignesEtValeursVides($mesure, $codeRq, $codeParametre, $inSitu) !== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.2
        if (($result = $controleVraisemblaceService->valeursNumeriques($mesure, $codeRq) !== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.3 Valeurs =0 (hors TH (1345), TA (1346), TAC (1347), Temp(1301)) hors codes observations environnementales / résultat = 0 possible pour les paramètres de cette liste (et pour 1345, 1346, 1347 et 1301) => Erreur
        if (($result = $controleVraisemblaceService->valeursEgalZero($mesure, $codeParametre, $inSitu) !== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.4 Valeurs < 0 (hors température de air 1409, potentiel REDOX 1330)
        if (($result = $controleVraisemblaceService->valeursInfZero($mesure, $codeParametre) !== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.5 Valeurs avec code remarque '> (3)' hors bactério (1147,1448,1449,1451,5479,6455); code remarque ="Trace (7)"
        if (($result = $controleVraisemblaceService->valeursSupTrois($codeParametre, $codeRq) !== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.6 1 < pH(1302) < 14
        $mPh = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1302, $demandeId, $reponseId, $codePrelevement);
        if (($result = $controleVraisemblaceService->pH($mPh) !== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.7 modèle  de WEISS : cohérence Teau, % O2, Concentration O2  sauf si Conductivité > 10 000
        $mTxSatOx = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1312, $demandeId, $reponseId, $codePrelevement);
        $mOxDiss = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1311, $demandeId, $reponseId, $codePrelevement);
        $mTEau = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1301, $demandeId, $reponseId, $codePrelevement);
        $mConductivite = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1303, $demandeId, $reponseId, $codePrelevement);
        if (($result = $controleVraisemblaceService->modeleWeiss($mTxSatOx, $mOxDiss, $mTEau, $mConductivite) !== true)) {
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
        $codeRqCationParams = array();
        $codeRqAnionParams = array();
        foreach ($cCationParams as $idx => $cCationParam) {
            $codeRqCationParams[$idx] = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement);
        }
        foreach ($cAnionParams as $idx => $cAnionParam) {
            $codeRqAnionParams[$idx] = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement);
        }

        if (($result = $controleVraisemblaceService->balanceIonique($cCationParams, $cAnionParams, $codeRqCationParams, $codeRqAnionParams) !== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.9 Comparaison Balance ionique / conductivité (Feret)
        if (($result = $controleVraisemblaceService->balanceIoniqueTds2($cCationParams, $cAnionParams, $codeRqCationParams, $codeRqAnionParams, $mConductivite) !== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.10 [PO4] (1433) en P < [P total](1350) 
        $mPo4 = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement);
        $mP = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1350, $demandeId, $reponseId, $codePrelevement);
        $codeRqPo4 = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement);
        $codeRqP = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1350, $demandeId, $reponseId, $codePrelevement);
        if (($result = $controleVraisemblaceService->orthophosphate($mPo4, $mP, $codeRqPo4, $codeRqP) !== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.11 NH4 (1335) en N < Nkj (1319)
        $mNh4 = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement);
        $mNkj = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1319, $demandeId, $reponseId, $codePrelevement);
        $codeRqNh4 = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement);
        $codeRqNkj = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1319, $demandeId, $reponseId, $codePrelevement);
        if (($result = $controleVraisemblaceService->ammonium($mNh4, $mNkj, $codeRqNh4, $codeRqNkj) !== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }

        // III.12 Valeur de pourcentage hors 1312 oxygène (ex : matière sèche ou granulo) : non compris entre 0 et 100 si code unité = 243 ou 246
        $tabMesures = array(243 => $this->repoPgTmpValidEdilabo->getMesureByCodeUnite(243, $demandeId, $reponseId, $codePrelevement, 1312),
            246 => $this->repoPgTmpValidEdilabo->getMesureByCodeUnite(246, $demandeId, $reponseId, $codePrelevement, 1312));
        if (($result = $controleVraisemblaceService->pourcentageHorsOxygene($tabMesures) !== true)) {
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
        if (($result = $controleVraisemblaceService->sommeParametresDistincts($sommeParams, $resultParams, $params) !== true)) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }
        
        $this->controleVraisemblanceMacroPolluants($demandeId, $reponseId, $codePrelevement);
        
        $this->detectionCodeRemarqueLot7($demandeId, $reponseId, $codePrelevement);
        
        $this->detectionCodeRemarqueLot8($demandeId, $reponseId, $codePrelevement);
        
        $this->controleLqAeag($pgCmdFichierRps, $codePrelevement);
        
    }
    
    
    //III.14 Contrôle de vraisemblance par parmètres macropolluants : Résultat d’analyse< Valeur max de la base x 2 
    // TODO A Retoucher
    public function controleVraisemblanceMacroPolluants($demandeId, $reponseId, $codePrelevement) {
        $pgProgUnitesPossiblesParams = $this->repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamWithValeurMax();
        foreach ($pgProgUnitesPossiblesParams as $pgProgUnitesPossiblesParam) {
            $mesure = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre($pgProgUnitesPossiblesParam->getCodeParametre(), $demandeId, $reponseId, $codePrelevement);
            if (!is_null($mesure)) {
                if ($mesure > $pgProgUnitesPossiblesParam->getValMax()) {
                    $this->_addLog('warning', $demandeId, $reponseId, "Controle Vraisemblance Macro Polluants : Le résultat est supérieur à la valeur attendue pour le paramètre " . $codeSandreMacroPolluant[0], $codePrelevement, $mesure);
                    //return array("warning", "Controle Vraisemblance Macro Polluants : Le résultat est supérieur à la valeur attendue pour le paramètre");
                }
            }
        }
    }

    //III.15 Détection Code remarque Lot 7 (Etat chimique, Substance pertinentes, Complément AEAG, PSEE) :  % de détection différent de 100 (= recherche d'absence de code remarque) suivant liste ref-doc
    // TODO A Retoucher
    public function detectionCodeRemarqueLot7($demandeId, $reponseId, $codePrelevement) {

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
                $this->_addLog('error', $demandeId, $reponseId, "Detection Code Remarque Lot 7 : Tous les codes remarques sont à 1", $codePrelevement);
                //return array("error", "Detection Code Remarque Lot 7 : Tous les codes remarques sont à 1");
            }
        }
    }

    //III.16
    // TODO A Retoucher
    public function detectionCodeRemarqueLot8($demandeId, $reponseId, $codePrelevement) {

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
                $this->_addLog('warning', $demandeId, $reponseId, "Detection Code Remarque Lot 8 : La majorité des codes remarque est à 1", $codePrelevement);
                //return array("warning", "Detection Code Remarque Lot 8 : La majorité des codes remarque est à 1");
            }
        }
    }

    // III.17
    // TODO A Retoucher
    public function controleLqAeag($pgCmdFichierRps, $codePrelevement) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->findBy(array('demandeId' => $demandeId, 'fichierRpsId' => $reponseId, 'codePrelevement' => $codePrelevement));
        if ($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getMarche()->getTypeMarche() == 'MOA') {
            foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
                if (!is_null($pgTmpValidEdilabo->getLqM())) {
                    $lq = $this->repoPgProgLotLqParam->isValidLq($pgCmdFichierRps->getDemande()->getLotan()->getLot(), $pgTmpValidEdilabo->getCodeParametre(), $pgTmpValidEdilabo->getCodeFraction(), $pgTmpValidEdilabo->getLqM());
                    if (count($lq) == 0) {
                        $this->_addLog('warning', $demandeId, $reponseId, "Controle Lq AEAG : Lq supérieure à la valeur prévue", $codePrelevement, $pgTmpValidEdilabo->getLqM());
                        //return array("warning", "Controle Lq AEAG : Lq supérieure à la valeur prévue");
                    }
                }
            }
        }
    }

    protected function _integrationDonneesBrutes($pgCmdFichierRps) {

        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->findBy(array('fichierRpsId' => $reponseId, 'demandeId' => $demandeId));

        $dejaFait = false;
        foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
            $pgCmdPrelev = $this->repoPgCmdPrelev->findOneBy(array('demande' => $pgTmpValidEdilabo->getDemandeId(), 'codePrelevCmd' => $pgTmpValidEdilabo->getCodePrelevement()));
            if (!is_null($pgCmdPrelev)) {
                if ($this->isAlreadyAdded($pgCmdFichierRps, $pgCmdPrelev)) {
                    $this->_addLog('warning', $pgCmdFichierRps->getDemande()->getId(), $pgCmdFichierRps->getId(), "Cette RAI a déjà été intégrée dans SQE");
                } else {
                    $pgCmdPrelev->setFichierRps($pgCmdFichierRps);
                    if (!is_null($pgTmpValidEdilabo->getDatePrel())) {
                        if (!is_null($pgTmpValidEdilabo->getHeurePrel())) {
                            $date = $pgTmpValidEdilabo->getDatePrel().' '.$pgTmpValidEdilabo->getHeurePrel();
                            $datePrel = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
                        } else {
                            $datePrel = \DateTime::createFromFormat('Y-m-d', $pgTmpValidEdilabo->getDatePrel());
                        }
                        $pgCmdPrelev->setDatePrelev($datePrel);
                    }
                    $pgCmdPrelev->setCodeMethode($pgTmpValidEdilabo->getMethPrel());
                    $pgCmdPrelev->setRealise("1");
                    $this->emSqe->persist($pgCmdPrelev);

                    // Cas particulier selon num_ordre => creer une nouvelle ligne
                    // Ne faire qu'une fois le traitement $pgCmdPrelevPc
                    if (!$dejaFait) {
                        if ($pgTmpValidEdilabo->getNumOrdre() == 1) {
                            $pgCmdPrelevPc = $this->repoPgCmdPrelevPc->findOneBy(array('prelev' => $pgCmdPrelev, 'numOrdre' => $pgTmpValidEdilabo->getNumOrdre()));
                        } else {
                            $pgCmdPrelevPc = new \Aeag\SqeBundle\Entity\PgCmdPrelevPc();
                            $pgCmdPrelevPc->setPrelev($pgCmdPrelev);
                            $pgCmdPrelevPc->setNumOrdre($pgTmpValidEdilabo->getNumOrdre());
                            $pgCmdPrelevPc->setRefEchCmd($pgTmpValidEdilabo->getRefEchCmd());
                        }
                        $pgCmdPrelevPc->setConformite($pgTmpValidEdilabo->getConformPrel());
                        $pgCmdPrelevPc->setAccreditation($pgTmpValidEdilabo->getAccredPrel());
                        $pgCmdPrelevPc->setAgrement($pgTmpValidEdilabo->getAgrePrel());
                        $pgCmdPrelevPc->setReserves($pgTmpValidEdilabo->getReservPrel());
                        $pgCmdPrelevPc->setCommentaire($pgTmpValidEdilabo->getCommentaire());
                        $pgCmdPrelevPc->setXPrel($pgTmpValidEdilabo->getXPrel());
                        $pgCmdPrelevPc->setYPrel($pgTmpValidEdilabo->getYPrel());
                        $pgCmdPrelevPc->setLocalisation($pgTmpValidEdilabo->getLocalisation());
                        $pgSandreZoneVerticaleProspectee = $this->repoPgSandreZoneVerticaleProspectee->findOneByCodeZone($pgTmpValidEdilabo->getZoneVert());
                        if (!is_null($pgSandreZoneVerticaleProspectee)) {
                            $pgCmdPrelevPc->setZoneVerticale($pgSandreZoneVerticaleProspectee);
                        }
                        $pgCmdPrelevPc->setProfondeur($pgTmpValidEdilabo->getProf());
                        $pgCmdPrelevPc->setRefEchPrel($pgTmpValidEdilabo->getRefEchPrel());
                        $pgCmdPrelevPc->setRefEchLabo($pgTmpValidEdilabo->getRefEchLabo());
                        $pgCmdPrelevPc->setCompletudeEch($pgTmpValidEdilabo->getCompletEch());
                        $pgCmdPrelevPc->setAcceptabiliteEch($pgTmpValidEdilabo->getAcceptEch());
                        if (!is_null($pgTmpValidEdilabo->getDateRecepEch())) {
                            $dateRecepEch = \DateTime::createFromFormat('Y-m-d', $pgTmpValidEdilabo->getDateRecepEch());
                            $pgCmdPrelevPc->setDateRecepEch($dateRecepEch);
                        }
                        $this->emSqe->persist($pgCmdPrelevPc);

                        $dejaFait = true;
                    }

                    if ($pgTmpValidEdilabo->getInSitu() == 0) {
                        $pgCmdMesureEnv = new \Aeag\SqeBundle\Entity\PgCmdMesureEnv();
                        $pgCmdMesureEnv->setPrelev($pgCmdPrelev);
                        $pgSandreParametres = $this->repoPgSandreParametres->findOneByCodeParametre($pgTmpValidEdilabo->getCodeParametre());
                        if (!is_null($pgSandreParametres)) {
                            $pgCmdMesureEnv->setCodeParametre($pgSandreParametres);
                        }
                        if (!is_null($pgTmpValidEdilabo->getDateM())) {
                            if (!is_null($pgTmpValidEdilabo->getHeureM())) {
                                $date = $pgTmpValidEdilabo->getDateM().' '.$pgTmpValidEdilabo->getHeureM();
                                $dateM = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
                            } else {
                                $dateM = \DateTime::createFromFormat('Y-m-d', $pgTmpValidEdilabo->getDateM());
                            }
                            $pgCmdMesureEnv->setDateMes($dateM);
                        }
                        
                        $pgCmdMesureEnv->setResultat($pgTmpValidEdilabo->getResM());
                        $pgSandreUnites = $this->repoPgSandreUnites->findOneByCodeUnite($pgTmpValidEdilabo->getCodeUnite());
                        if (!is_null($pgSandreUnites)) {
                            $pgCmdMesureEnv->setCodeUnite($pgSandreUnites);
                        }
                        $pgCmdMesureEnv->setCodeRemarque($pgTmpValidEdilabo->getCodeRqM());
                        $pgCmdMesureEnv->setCodeMethode($pgTmpValidEdilabo->getMethPrel());
                        $pgCmdMesureEnv->setCodeStatut($pgTmpValidEdilabo->getCodeStatut());
                        $pgProgLotParamAn = $this->repoPgProgLotParamAn->findOneById($pgTmpValidEdilabo->getParamProgId());
                        if (!is_null($pgProgLotParamAn)) {
                            $pgCmdMesureEnv->setParamProg($pgProgLotParamAn);
                        }
                        $this->emSqe->persist($pgCmdMesureEnv);
                    } else {
                        $pgCmdAnalyse = new \Aeag\SqeBundle\Entity\PgCmdAnalyse();
                        $pgCmdAnalyse->setPrelevId($pgCmdPrelev->getId());
                        $pgCmdAnalyse->setNumOrdre($pgTmpValidEdilabo->getNumOrdre());
                        $pgSandreParametres = $this->repoPgSandreParametres->findOneByCodeParametre($pgTmpValidEdilabo->getCodeParametre());
                        if (!is_null($pgSandreParametres)) {
                            $pgCmdAnalyse->setCodeParametre($pgSandreParametres);
                        }
                        $pgSandreFractions = $this->repoPgSandreFractions->findOneByCodeFraction($pgTmpValidEdilabo->getCodeFraction());
                        if (!is_null($pgSandreFractions)) {
                            $pgCmdAnalyse->setCodeFraction($pgSandreFractions);
                        }
                        $pgCmdAnalyse->setLieuAna($pgTmpValidEdilabo->getInSitu());
                        
                         if (!is_null($pgTmpValidEdilabo->getDateM())) {
                            if (!is_null($pgTmpValidEdilabo->getHeureM())) {
                                $date = $pgTmpValidEdilabo->getDateM().' '.$pgTmpValidEdilabo->getHeureM();
                                $dateM = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
                            } else {
                                $dateM = \DateTime::createFromFormat('Y-m-d', $pgTmpValidEdilabo->getDateM());
                            }
                            $pgCmdAnalyse->setDateAna($dateM);
                        }
                        
                        $pgCmdAnalyse->setResultat($pgTmpValidEdilabo->getResM());
                        $pgSandreUnites = $this->repoPgSandreUnites->findOneByCodeUnite($pgTmpValidEdilabo->getCodeUnite());
                        if (!is_null($pgSandreUnites)) {
                            $pgCmdAnalyse->setCodeUnite($pgSandreUnites);
                        }
                        $pgCmdAnalyse->setCodeRemarque($pgTmpValidEdilabo->getCodeRqM());
                        $pgCmdAnalyse->setLqAna($pgTmpValidEdilabo->getLqM());
                        $pgCmdAnalyse->setRefAnaLabo($pgTmpValidEdilabo->getRefAnaLabo());
                        $pgCmdAnalyse->setCodeMethode($pgTmpValidEdilabo->getMethAna());
                        $pgCmdAnalyse->setAccreditation($pgTmpValidEdilabo->getAccredAna());
                        $pgCmdAnalyse->setConfirmation($pgTmpValidEdilabo->getConfirmAna());
                        $pgCmdAnalyse->setReserve($pgTmpValidEdilabo->getReservAna());
                        $pgCmdAnalyse->setCodeStatut($pgTmpValidEdilabo->getCodeStatut());
                        $pgProgLotParamAn = $this->repoPgProgLotParamAn->findOneById($pgTmpValidEdilabo->getParamProgId());
                        if (!is_null($pgProgLotParamAn)) {
                            $pgCmdAnalyse->setParamProg($pgProgLotParamAn);
                        }
                        $this->emSqe->persist($pgCmdAnalyse);
                    }
                    $this->emSqe->flush();
                }
                // Evolution de la phase du prelevement
                $this->_updatePhasePrelevement($pgCmdPrelev, 'M40');
            }
        }
    }
    
    protected function _exportCsvDonneesBrutes($pgCmdFichierRps) {
        
        // Fichier CSV :
        // Récupérer le nom du fichier déposé
        // Supprimer l'extension, rajouter csv
        $nomFichierRps = str_replace('zip', 'csv', $pgCmdFichierRps->getNomFichier());
        $chemin = $this->getContainer()->getParameter('repertoire_echange');
        $pathBase = $this->getContainer()->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdFichierRps->getDemande(), $pgCmdFichierRps->getId());
        $fullFileName = $pathBase . '/' . $nomFichierRps;
        
        $fichier_csv = fopen($fullFileName, 'w+');
        
        // Chaque ligne du tableau correspond a une ligne du fichier csv
        $lignes = array();
        // Entete
        $lignes[] = array('Année', 'Code station','Nom station','Code masse d\'eau','Code du prélèvement',
                        'Siret préleveur','Nom préleveur','Date-heure du prélèvement','Code du paramètre', 
                        'Libellé court paramètre', 'Nom paramètre','Zone verticale','Profondeur', 'Code support',
                        'Nom support', 'Code fraction', 'Nom fraction', 'Code méthode', 'Nom méthode', 'Code remarque',
                        'Résultat', 'Valeur textuelle', 'Code unité', 'libellé unité', 'symbole unité', 'LQ', 'Siret labo',
                        'Nom labo', 'Code réseau', 'Nom réseau', 'Siret prod', 'Nom prod', 'Commentaire');
        
        // Requete de récupération des différents champs
        $donneesBrutes = $this->repoPgCmdPrelev->getDonneesBrutes($pgCmdFichierRps);
        $lignes = array_merge($lignes, $donneesBrutes);
        foreach ($lignes as $ligne) {
            fputcsv($fichier_csv, $ligne, ';');
        }
        
        fclose($fichier_csv);
        
        // Mettre à jour la table pgCmdFichierRps avec le lien vers le fichier des données brutes
        $pgCmdFichierRps->setNomFichierDonneesBrutes($nomFichierRps);
        $this->emSqe->persist($pgCmdFichierRps);
        $this->emSqe->flush();
    }

    protected function isAlreadyAdded($pgCmdFichierRps, $pgCmdPrelev) {
        $pgProgPhase = $this->repoPgProgPhases->findOneBy('M30');
        $pgCmdPrelevExisting = $this->repoPgCmdPrelev->getPgCmdPrelevByCodePrelevCodeDmdAndPhase($pgCmdPrelev, $pgCmdFichierRps->getDemande(), $pgProgPhase);
        if (count($pgCmdPrelevExisting) > 0) {
            return false;
        }
        return true;
    }

    protected function _insertFichierLog($pgCmdFichierRps) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        $fileName = $pgCmdFichierRps->getNomFichierCompteRendu();
        $logs = $this->repoPgLogValidEdilabo->findBy(array('demandeId' => $demandeId, 'fichierRpsId' => $reponseId));

        // Récupération et ouverture du fichier de log
        $chemin = $this->getContainer()->getParameter('repertoire_echange');
        $pathBase = $this->getContainer()->get('aeag_sqe.process_rai')->getCheminEchange($chemin, $pgCmdFichierRps->getDemande(), $pgCmdFichierRps->getId());
        $fullFileName = $pathBase . '/' . $fileName;

        $cr = '';
        foreach ($logs as $log) {
            $cr .= $log . "\r\n";
        }
        file_put_contents($fullFileName, $cr, FILE_APPEND);
    }

    protected function _cleanTmpTable($pgCmdFichierRps) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->findBy(array('demandeId' => $demandeId, 'fichierRpsId' => $reponseId));
        foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
            $this->emSqe->remove($pgTmpValidEdilabo);
        }

        $pgPgLogValidEdilabos = $this->repoPgLogValidEdilabo->findBy(array('demandeId' => $demandeId, 'fichierRpsId' => $reponseId));
        foreach ($pgPgLogValidEdilabos as $pgPgLogValidEdilabo) {
            $this->emSqe->remove($pgPgLogValidEdilabo);
        }

        $this->emSqe->flush();
    }

}
