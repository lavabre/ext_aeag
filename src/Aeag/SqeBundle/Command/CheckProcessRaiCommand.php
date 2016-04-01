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
    
    private $repoPgCmdFichiersRps;
    private $repoPgProgPhases;
    private $repoPgTmpValidEdilabo;
    private $repoPgLogValidEdilabo;
    private $repoPgCmdDemande;
    private $repoPgRefCorresPresta;
    private $repoPgProgLotLqParam;
    private $repoPgProgUnitesPossiblesParam;
    private $repoPgSandreFractions;
    private $repoPgCmdPrelev;
    private $repoPgCmdPrelevPc;
    
    private $detectionCodeRemarqueComplet;
    private $detectionCodeRemarqueMoitie;
    
    private $phase82atteinte = false;

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

        $this->repoPgCmdFichiersRps = $this->emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $this->repoPgCmdDemande = $this->emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $this->repoPgProgPhases = $this->emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $this->repoPgTmpValidEdilabo = $this->emSqe->getRepository('AeagSqeBundle:PgTmpValidEdilabo');
        $this->repoPgLogValidEdilabo = $this->emSqe->getRepository('AeagSqeBundle:PgLogValidEdilabo');
        $this->repoPgRefCorresPresta = $this->emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $this->repoPgProgLotLqParam = $this->emSqe->getRepository('AeagSqeBundle:PgProgLotLqParam');
        $this->repoPgProgUnitesPossiblesParam = $this->emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $this->repoPgSandreFractions = $this->emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $this->repoPgCmdPrelev = $this->emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $this->pgCmdPrelevPc = $this->emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');

        // Chargement des fichiers csv dans des tableaux 
        $this->detectionCodeRemarqueComplet = $this->_csvToArray(getcwd() . "/web/tablesCorrespondancesRai/detectionCodeRemarqueComplet.csv");
        $this->detectionCodeRemarqueMoitie = $this->_csvToArray(getcwd() . "/web/tablesCorrespondancesRai/detectionCodeRemarqueMoitie.csv");

        // On récupère les RAIs dont les phases sont en R25
        $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase('R25');
        $pgCmdFichiersRps = $this->repoPgCmdFichiersRps->findBy(array('phaseFichier' => $pgProgPhases, 'typeFichier' => 'RPS', 'suppr' => 'N'));

        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {

            $this->_coherenceRaiDai($pgCmdFichierRps);

            // Changement de la phase en fonction des retours
            $logErrorsCoherence = $this->repoPgLogValidEdilabo->findBy(array('demandeId' => $pgCmdFichierRps->getDemande()->getId(), 'fichierRpsId' => $pgCmdFichierRps->getId(), 'typeErreur' => 'error'));
            $logWarningsCoherence = $this->repoPgLogValidEdilabo->findBy(array('demandeId' => $pgCmdFichierRps->getDemande()->getId(), 'fichierRpsId' => $pgCmdFichierRps->getId(), 'typeErreur' => 'warning'));


            if (count($logErrorsCoherence) > 0) {
                $this->_updatePhase($pgCmdFichierRps, 'R82');
                $this->phase82atteinte = true;
            } else {
                if (count($logWarningsCoherence) > 0) { // Avec avertissements
                    $this->_updatePhase($pgCmdFichierRps, 'R31');
                } else { // Sans avertissements
                    $this->_updatePhase($pgCmdFichierRps, 'R30');
                }
            }

            $this->_controleVraisemblance($pgCmdFichierRps);

            // Changement de la phase en fonction des retours
            $logErrorsVraisemblance = $this->repoPgLogValidEdilabo->findBy(array('demandeId' => $pgCmdFichierRps->getDemande()->getId(), 'fichierRpsId' => $pgCmdFichierRps->getId(), 'typeErreur' => 'error'));
            $logWarningsVraisemblance = $this->repoPgLogValidEdilabo->findBy(array('demandeId' => $pgCmdFichierRps->getDemande()->getId(), 'fichierRpsId' => $pgCmdFichierRps->getId(), 'typeErreur' => 'warning'));

            if (count($logErrorsVraisemblance) > 0) {
                // Erreur
                $this->_updatePhase($pgCmdFichierRps, 'R84', $this->phase82atteinte);
            } else { // Succes
                if (count($logWarningsVraisemblance) > 0) { // Avec avertissements
                    $this->_updatePhase($pgCmdFichierRps, 'R41', $this->phase82atteinte);
                } else { // Sans avertissements
                    $this->_updatePhase($pgCmdFichierRps, 'R40', $this->phase82atteinte);
                }
            }

            // Compléter le fichier de logs
            $this->_insertFichierLog($pgCmdFichierRps);

            // Insertion données brutes
            //if ((count($logErrorsVraisemblance) == 0) && (count($logErrorsCoherence) == 0 )) {
                $this->_integrationDonneesBrutes($pgCmdFichierRps);
            //}

            // TODO Vider la table tempo des lignes correspondant à la RAI
        }
    }

    protected function _coherenceRaiDai($pgCmdFichierRps) {

        $this->_updatePhase($pgCmdFichierRps, 'R26');

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

        $this->_updatePhase($pgCmdFichierRps, 'R36', $this->phase82atteinte);

        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->findBy(array('fichierRpsId' => $reponseId, 'demandeId' => $demandeId));

        // Contrôles sur toutes les valeurs insérées
        foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
            $mesure = $pgTmpValidEdilabo->getResM();
            $codeRq = $pgTmpValidEdilabo->getCodeRqM();
            $codeParametre = $pgTmpValidEdilabo->getCodeParametre();
            $codePrelevement = $pgTmpValidEdilabo->getCodePrelevement();
            $inSitu = $pgTmpValidEdilabo->getInSitu();


            // III.1 Champs non renseignés (valeurs et code remarque) ou valeurs non numériques ou valeurs impossibles params env / code remarque peut ne pas être renseigné pour cette liste (car réponse en edilabo 1.0) => avertissement
            if (is_null($mesure)) {
                if ($codeRq != 0 || is_null($codeRq)) { // III.2 Si valeur "vide" avec code remarque "0" hors lecture échelle (1429),,,,,,,'ABSENT' / on doit avoir un code remarque = 0 pour les valeurs vides, sinon avertissement, sauf pour le 1429 (cote échelle) => Avertissement
                    if ($codeParametre == 1429 || $inSitu == 0) {
                        $this->_addLog('warning', $demandeId, $reponseId, "Valeur non renseignée et code remarque différent de 0", $codePrelevement, $codeParametre);
                    } else {
                        $this->_addLog('error', $demandeId, $reponseId, "Valeur non renseignée et code remarque différent de 0", $codePrelevement, $codeParametre);
                    }
                }
            }

            if (is_null($codeRq) && !is_null($mesure)) {
                if ($codeParametre == 1429 || $inSitu == 0) {
                    $this->_addLog('warning', $demandeId, $reponseId, "Valeur renseignée et code remarque vide", $codePrelevement, $codeParametre);
                } else {
                    $this->_addLog('error', $demandeId, $reponseId, "Valeur renseignée et code remarque vide", $codePrelevement, $codeParametre);
                }
            }

            if (!is_null($codeRq) && !is_null($mesure)) {
                if (!is_numeric($mesure) || !is_numeric($codeRq)) {
                    $this->_addLog('error', $demandeId, $reponseId, "La valeur n'est pas un nombre", $codePrelevement, $codeParametre);
                }
            }

            // Codes Params Env
            // III.3 Valeurs =0 (hors TH (1345), TA (1346), TAC (1347), Temp(1301)) hors codes observations environnementales / résultat = 0 possible pour les paramètres de cette liste (et pour 1345, 1346, 1347 et 1301) => Erreur
            if ($mesure == 0) {
                if ($codeParametre !== 1345 && $codeParametre !== 1346 && $codeParametre !== 1347 && $codeParametre !== 1301 && $inSitu != 0) {
                    $this->_addLog('error', $demandeId, $reponseId, "Valeur = 0 impossible pour ce paramètre", $codePrelevement, $codeParametre);
                }
            }

            // III.4 Valeurs < 0 (hors température de air 1409, potentiel REDOX 1330)
            if ($mesure < 0) {
                if ($codeParametre != 1409 && $codeParametre != 1330 && $codeParametre != 1420 && $codeParametre != 1429) {
                    $this->_addLog('error', $demandeId, $reponseId, "Valeur < 0 impossible pour ce paramètre", $codePrelevement, $codeParametre);
                }
            }

            // III.5 Valeurs avec code remarque '> (3)' hors bactério (1147,1448,1449,1451,5479,6455); code remarque ="Trace (7)"
            $codeParamsBacterio = array(
                1447, 1448, 1449, 1451, 5479, 6455
            );
            if (($codeRq == 3 && !in_array($codeParametre, $codeParamsBacterio)) || $codeRq == 7) {
                $this->_addLog('error', $demandeId, $reponseId, "Code Remarque > 3 ou == 7 impossible pour ce paramètre", $codePrelevement, $codeParametre);
            }
        }

        $codePrelevements = $this->repoPgTmpValidEdilabo->getCodePrelevement($demandeId, $reponseId);
        foreach ($codePrelevements as $codePrelevement) {
            $codePrelevement = $codePrelevement['codePrelevement'];
            // Contrôles spécifiques
            // III.6
            $this->_pH($demandeId, $reponseId, $codePrelevement);

            // III.7
            $this->_modeleWeiss($demandeId, $reponseId, $codePrelevement);

            // III.8 
            $this->_balanceIonique($demandeId, $reponseId, $codePrelevement);

            // III.9
            $this->_balanceIoniqueTds2($demandeId, $reponseId, $codePrelevement);

            // III.10
            $this->_ortophosphate($demandeId, $reponseId, $codePrelevement);

            // III.11
            $this->_ammonium($demandeId, $reponseId, $codePrelevement);

            // III.12
            $this->_pourcentageHorsOxygene($demandeId, $reponseId, $codePrelevement);

            // III.13
            $this->_sommeParametresDistincts($demandeId, $reponseId, $codePrelevement);

            // III.14
            $this->_controleVraisemblanceMacroPolluants($demandeId, $reponseId, $codePrelevement);

            // III.15
            $this->_detectionCodeRemarqueLot7($demandeId, $reponseId, $codePrelevement);

            // III.16
            $this->_detectionCodeRemarqueLot8($demandeId, $reponseId, $codePrelevement);

            // III.17
            $this->_controleLqAeag($pgCmdFichierRps, $codePrelevement);
        }
    }

    protected function _integrationDonneesBrutes($pgCmdFichierRps) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->findBy(array('fichierRpsId' => $reponseId, 'demandeId' => $demandeId));
        
        $dejaFait = false;
        foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
            $this->output->writeln(array($pgTmpValidEdilabo->getDemandeId(), $pgTmpValidEdilabo->getCodePrelevement(), $pgTmpValidEdilabo->getCodeStation()));
            $pgCmdPrelev = $this->repoPgCmdPrelev->findOneBy(array('demande' => $pgTmpValidEdilabo->getDemandeId(), 'codePrelevCmd' => $pgTmpValidEdilabo->getCodePrelevement(), 'station' => $pgTmpValidEdilabo->getCodeStation()));
            $this->output->writeln(count($pgCmdPrelev));
            if (!is_null($pgCmdPrelev)) {
                $pgCmdPrelev->setFichierRps($pgCmdFichierRps);
                $pgCmdPrelev->setDatePrelev($pgTmpValidEdilabo->getDatePrel());
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
                    $pgCmdPrelevPc->setZoneVerticale($pgTmpValidEdilabo->getZoneVert());
                    $pgCmdPrelevPc->setProfondeur($pgTmpValidEdilabo->getProf());
                    $pgCmdPrelevPc->setRefEchPrel($pgTmpValidEdilabo->getRefEchPrel());
                    $pgCmdPrelevPc->setRefEchLabo($pgTmpValidEdilabo->getRefEchLabo());
                    $pgCmdPrelevPc->setCompletudeEch($pgTmpValidEdilabo->getCompletEch());
                    $pgCmdPrelevPc->setAcceptabiliteEch($pgTmpValidEdilabo->getAcceptEch());
                    $pgCmdPrelevPc->setDateRecepEch($pgTmpValidEdilabo->getDateRecepEch());
                    $this->emSqe->persist($pgCmdPrelevPc);

                    $dejaFait = true;
                }

                if ($pgTmpValidEdilabo->getInSitu() == 0) {
                    $pgCmdMesureEnv = new \Aeag\SqeBundle\Entity\PgCmdMesureEnv();
                    $pgCmdMesureEnv->setPrelev($pgCmdPrelev);
                    $pgCmdMesureEnv->setCodeParametre($pgTmpValidEdilabo->getCodeParametre());
                    $pgCmdMesureEnv->setDateMes($pgTmpValidEdilabo->getDateM());
                    $pgCmdMesureEnv->setResultat($pgTmpValidEdilabo->getResM());
                    $pgCmdMesureEnv->setCodeUnite($pgTmpValidEdilabo->getCodeUnite());
                    $pgCmdMesureEnv->setCodeRemarque($pgTmpValidEdilabo->getCodeRqM());
                    $pgCmdMesureEnv->setCodeMethode($pgTmpValidEdilabo->getMethPrel());
                    $pgCmdMesureEnv->setCodeStatut($pgTmpValidEdilabo->getCodeStatut());
                    $pgCmdMesureEnv->setParamProg($pgTmpValidEdilabo->getParamProgId());
                    $this->emSqe->persist($pgCmdMesureEnv);
                } else {
                    $pgCmdAnalyse = new \Aeag\SqeBundle\Entity\PgCmdAnalyse();
                    $pgCmdAnalyse->setPrelevId($pgCmdPrelev);
                    $pgCmdAnalyse->setNumOrdre($pgTmpValidEdilabo->getNumOrdre());
                    $pgCmdAnalyse->setCodeParametre($pgTmpValidEdilabo->getCodeParametre());
                    $pgCmdAnalyse->setCodeFraction($pgTmpValidEdilabo->getCodeFraction());
                    $pgCmdAnalyse->setLieuAna($pgTmpValidEdilabo->getInSitu());
                    $pgCmdAnalyse->setDateAna($pgTmpValidEdilabo->getDateM());
                    $pgCmdAnalyse->setResultat($pgTmpValidEdilabo->getResM());
                    $pgCmdAnalyse->setCodeUnite($pgTmpValidEdilabo->getCodeUnite());
                    $pgCmdAnalyse->setCodeRemarque($pgTmpValidEdilabo->getCodeRqM());
                    $pgCmdAnalyse->setLqAna($pgTmpValidEdilabo->getLqM());
                    $pgCmdAnalyse->setRefAnaLabo($pgTmpValidEdilabo->getRefAnaLabo());
                    $pgCmdAnalyse->setCodeMethode($pgTmpValidEdilabo->getMethAna());
                    $pgCmdAnalyse->setAccreditation($pgTmpValidEdilabo->getAccredAna());
                    $pgCmdAnalyse->setConfirmation($pgTmpValidEdilabo->getConfirmAna());
                    $pgCmdAnalyse->setReserve($pgTmpValidEdilabo->getReservAna());
                    $pgCmdAnalyse->setCodeStatut($pgTmpValidEdilabo->getCodeStatut());
                    $pgCmdAnalyse->setParamProg($pgTmpValidEdilabo->getParamProgId());
                    $this->emSqe->persist($pgCmdAnalyse);
                }

                $this->emSqe->flush();
            }
        }
    }

    // III.6 1 < pH(1302) < 14
    protected function _pH($demandeId, $reponseId, $codePrelevement) {
        $mPh = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1302, $demandeId, $reponseId, $codePrelevement);
        if (!is_null($mPh)) {
            if ($mPh < 1 || $mPh > 14) {
                $this->_addLog('error', $demandeId, $reponseId, "Le pH n\'est pas entre 1 et 14", $codePrelevement, $mPh);
            }
        }
    }

    // III.7 modèle  de WEISS : cohérence Teau, % O2, Concentration O2  sauf si Conductivité > 10 000
    protected function _modeleWeiss($demandeId, $reponseId, $codePrelevement) {
        $mTxSatOx = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1312, $demandeId, $reponseId, $codePrelevement);
        $mOxDiss = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1311, $demandeId, $reponseId, $codePrelevement);
        $mTEau = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1301, $demandeId, $reponseId, $codePrelevement);
        $mConductivite = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1303, $demandeId, $reponseId, $codePrelevement);

        if (!is_null($mTxSatOx) && !is_null($mOxDiss) && !is_null($mTEau) && !is_null($mConductivite)) {
            if ($mConductivite < 10000) {
                $mTEauK = $mTEau + 273.15;
                $argExp = -173.4292 + 249.6339 * 100 / $mTEauK + 143.3483 * log($mTEauK / 100) - 21.8492 * $mTEauK / 100;
                $txSatModel = 1.4276 * exp($argExp);
                $indVraiWess = $txSatModel - $mTxSatOx;

                if (abs($indVraiWess) > 25) {
                    //error
                    $this->_addLog('error', $demandeId, $reponseId, "Modele de Weiss : valeurs non conformes", $codePrelevement, abs($indVraiWess));
                } else if (10 < abs($indVraiWess) && abs($indVraiWess) <= 25) {
                    // Avertissement
                    $this->_addLog('warning', $demandeId, $reponseId, "Modele de Weiss : valeur réservée", $codePrelevement, abs($indVraiWess));
                }
            } else {
                //error ou avertissement ?
                $this->_addLog('warning', $demandeId, $reponseId, "Modele de Weiss : Conductivité supérieur à 10000", $codePrelevement, $mConductivite);
            }
        }
    }

    // III.8 Balance ionique (meq) sauf si tous les résultats < LQ
    protected function _balanceIonique($demandeId, $reponseId, $codePrelevement) {
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
                $this->_addLog('error', $demandeId, $reponseId, "Balance Ionique : Tous les dosages sont en LQ", $codePrelevement);
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
                    $this->_addLog('warning', $demandeId, $reponseId, "Balance Ionique : Valeur réservée", $codePrelevement);
                } else if ($indVraiBion > 1.25) {
                    $this->_addLog('error', $demandeId, $reponseId, "Balance Ionique : Valeur non conforme", $codePrelevement);
                }
            }
        }
    }

    // III.9 Comparaison Balance ionique / conductivité (Feret)
    protected function _balanceIoniqueTds2($demandeId, $reponseId, $codePrelevement) {
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

        $mConductivite = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1303, $demandeId, $reponseId, $codePrelevement);

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
                $this->_addLog('error', $demandeId, $reponseId, "Balance Ionique TDS 2 : Tous les dosages sont en LQ", $codePrelevement);
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
                    $this->_addLog('warning', $demandeId, $reponseId, "Balance Ionique TDS 2 : Réserve", $codePrelevement);
                } else if (abs($indVraiTds) >= 280) {
                    $this->_addLog('warning', $demandeId, $reponseId, "Balance Ionique TDS 2 : Valeur non conforme", $codePrelevement);
                }
            }
        }
    }

    // III.10 [PO4] (1433) en P < [P total](1350) 
    protected function _ortophosphate($demandeId, $reponseId, $codePrelevement) {
        $mPo4 = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement);
        $mP = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1350, $demandeId, $reponseId, $codePrelevement);

        if (!is_null($mPo4) && !is_null($mP)) {
            if (($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement) == 10) &&
                    ($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1350, $demandeId, $reponseId, $codePrelevement) == 10)) {
                $this->_addLog('error', $demandeId, $reponseId, "Ortophosphate : Tous les dosages sont en LQ", $codePrelevement);
            } else {
                $indP = $mPo4 / $mP;
                if (1 < $indP && $indP <= 1.25) {
                    $this->_addLog('warning', $demandeId, $reponseId, "Ortophosphate : Réserve", $codePrelevement);
                } else if ($indP > 1.25) {
                    $this->_addLog('error', $demandeId, $reponseId, "Ortophosphate : Valeur non conforme", $codePrelevement);
                }
            }
        }
    }

    // III.11 NH4 (1335) en N < Nkj (1319)
    protected function _ammonium($demandeId, $reponseId, $codePrelevement) {
        $mNh4 = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement);
        $mNkj = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1319, $demandeId, $reponseId, $codePrelevement);

        if (!is_null($mNh4) && !is_null($mNkj)) {
            if (($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement) == 10) &&
                    ($this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1319, $demandeId, $reponseId, $codePrelevement) == 10)) {
                $this->_addLog('error', $demandeId, $reponseId, "Ammonium : tous les dosages sont en LQ", $codePrelevement);
            } else {
                $indP = $mNh4 / $mNkj;
                if (1 < $indP && $indP <= 1.25) {
                    $this->_addLog('warning', $demandeId, $reponseId, "Ammonium : Réserve", $codePrelevement);
                } else if ($indP > 1.25) {
                    $this->_addLog('error', $demandeId, $reponseId, "Ammonium : Valeur non conforme", $codePrelevement);
                }
            }
        }
    }

    // III.12 Valeur de pourcentage hors 1312 oxygène (ex : matière sèche ou granulo) : non compris entre 0 et 100 si code unité = 243 ou 246
    protected function _pourcentageHorsOxygene($demandeId, $reponseId, $codePrelevement) {
        $tabMesures = array(243 => $this->repoPgTmpValidEdilabo->getMesureByCodeUnite(243, $demandeId, $reponseId, $codePrelevement, 1312),
            246 => $this->repoPgTmpValidEdilabo->getMesureByCodeUnite(246, $demandeId, $reponseId, $codePrelevement, 1312));

        foreach ($tabMesures as $tabMesure) {
            if (!is_null($tabMesure) && count($tabMesure) > 0) {
                foreach ($tabMesure as $mesure) {
                    if ($mesure > 100 || $mesure < 0) {
                        $this->_addLog('error', $demandeId, $reponseId, "Valeur pourcentage : pourcentage n'est pas entre 0 et 100", $mesure);
                    }
                }
            }
        }
    }

    // III.13 Somme des paramètres distincts (1200+1201+1202+1203=5537; 1178+1179 = 1743; 1144+1146+ 1147+1148 = 7146; 2925 + 1292 =  1780) à  (+/- 20%)
    protected function _sommeParametresDistincts($demandeId, $reponseId, $codePrelevement) {
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
                    $this->_addLog('error', $demandeId, $reponseId, "Somme Parametres Distincts : La somme des paramètres ne correspond pas au paramètre global " . $params[$idx], $codePrelevement, $somme);
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
                    $this->_addLog('warning', $demandeId, $reponseId, "Controle Vraisemblance Macro Polluants : Le résultat est supérieur à la valeur attendue pour le paramètre " . $codeSandreMacroPolluant[0], $codePrelevement, $mesure);
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
            $nbCodeRq10 = 0;
            foreach ($codesParams as $codeParam) {
                if (in_array($codeParam, $this->detectionCodeRemarqueComplet)) {
                    $codeRq = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($codeParam, $demandeId, $reponseId, $codePrelevement);
                    if ($codeRq == 10) {
                        $nbCodeRq10++;
                    }
                    if ($codeRq != 0) {
                        $nbCodeRqTot++;
                    }
                }
            }

            if ($nbCodeRqTot <= $nbCodeRq10) {
                $this->_addLog('error', $demandeId, $reponseId, "Detection Code Remarque Lot 7 : Tous les codes remarques sont à 10", $codePrelevement);
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

            if ($nbCodeRq10 < ($nbTotalCodeRq / 2)) {
                $this->_addLog('warning', $demandeId, $reponseId, "Detection Code Remarque Lot 8 : La majorité des codes remarque est à 1", $codePrelevement);
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
                        $this->_addLog('warning', $demandeId, $reponseId, "Controle Lq AEAG : Lq supérieure à la valeur prévue", $codePrelevement, $pgTmpValidEdilabo->getLqM());
                    }
                }
            }
        }
    }

    protected function _updatePhase($pgCmdFichierRps, $phase, $phaseExclu = false) {

        $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase($phase);
        if (!$phaseExclu) {
            $pgCmdFichierRps->setPhaseFichier($pgProgPhases);
            $this->emSqe->persist($pgCmdFichierRps);
        }

        $pgProgSuiviPhases = new \Aeag\SqeBundle\Entity\PgProgSuiviPhases;
        $pgProgSuiviPhases->setTypeObjet('RPS');
        $pgProgSuiviPhases->setObjId($pgCmdFichierRps->getId());
        $pgProgSuiviPhases->setDatePhase(new \DateTime());
        $pgProgSuiviPhases->setPhase($pgProgPhases);
        $this->emSqe->persist($pgProgSuiviPhases);

        $this->emSqe->flush();
    }

    protected function _convertMultiArray($array) {
        $out = implode(",", array_map(function($a) {
                    return implode("-", $a);
                }, $array));
        return $out;
    }

    protected function _addLog($typeErreur, $demandeId, $fichierRpsId, $message, $codePrelevement = null, $commentaire = null) {
        $dateLog = new \DateTime();
        if (!is_null($commentaire) && is_array($commentaire)) {
            $commentaire = $this->_convertMultiArray($commentaire);
        }
        $pgLogValidEdilabo = new \Aeag\SqeBundle\Entity\PgLogValidEdilabo($demandeId, $fichierRpsId, $typeErreur, $message, $dateLog, $codePrelevement, $commentaire);

        $this->emSqe->persist($pgLogValidEdilabo);
        $this->emSqe->flush();
    }

    protected function _insertFichierLog($pgCmdFichierRps) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        $fileName = $pgCmdFichierRps->getNomFichierCompteRendu();
        $logs = $this->repoPgLogValidEdilabo->findBy(array('demandeId' => $demandeId, 'fichierRpsId' => $reponseId));

        // Récupération et ouverture du fichier de log
        $pathBase = $this->getContainer()->getParameter('repertoire_echange');
        $pathBase .= $pgCmdFichierRps->getDemande()->getAnneeProg() . '/' . $pgCmdFichierRps->getDemande()->getCommanditaire()->getNomCorres() .
                '/' . $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getId() . '/' . $pgCmdFichierRps->getDemande()->getLotan()->getId() . '/' . $pgCmdFichierRps->getId();

        $fullFileName = $pathBase . '/' . $fileName;

        $cr = '';
        foreach ($logs as $log) {
            $cr .= $log . "\r\n";
        }
        file_put_contents($fullFileName, $cr, FILE_APPEND);
    }

    protected function _csvToArray($nomFichier) {
        $result = array();
        if (($handle = fopen($nomFichier, "r")) !== FALSE) {
            $row = 0;
            while ((($data = fgetcsv($handle, 1000, ";")) !== FALSE)) {
                if ($row !== 0) {
                    $result[] = $data;
                }
            }
        }

        return $result;
    }

}
