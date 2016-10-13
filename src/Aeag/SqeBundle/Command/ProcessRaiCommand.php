<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProcessRaiCommand extends AeagCommand {

    private $phase82atteinte = false;

    protected function configure() {
        $this
                ->setName('rai:process')
                ->setDescription('Controle des RAI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        parent::execute($input, $output);

        // On récupère les RAIs dont les phases sont en R25
        $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase('R25');
        //$pgCmdFichiersRps = $this->repoPgCmdFichiersRps->findBy(array('phaseFichier' => $pgProgPhases, 'typeFichier' => 'RPS', 'suppr' => 'N'), array('id' => 'ASC'));
        $pgCmdFichierRps = $this->repoPgCmdFichiersRps->findOneBy(array('phaseFichier' => $pgProgPhases, 'typeFichier' => 'RPS', 'suppr' => 'N'), array('id' => 'ASC'));
        $cptRaisTraitesOk = 0;
        $cptRaisTraitesNok = 0;
        
        if (!is_null($pgCmdFichierRps)) {
        
        // On les passe tous en phase R26
        //foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R26');
        //}
        
        /*if (count($pgCmdFichiersRps) > 0) {
            $date = new \DateTime();
            $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : ' . count($pgCmdFichiersRps) . " RAI(s) vont être traitées ");
        }*/
        
        
        //foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            $date = new \DateTime();
            $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : Le traitement de la RAI '.$pgCmdFichierRps->getId().' commence');
            
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
            if (($pgCmdFichierRps->getPhaseFichier()->getCodePhase() == 'R41') || ($pgCmdFichierRps->getPhaseFichier()->getCodePhase() == 'R40')) {
                $cptRaisTraitesOk++;
            } else {
                // Vider les tables
                //$this->_cleanTmpTable($pgCmdFichierRps);
                $cptRaisTraitesNok++;
            }
            
            $date = new \DateTime();
            $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : Le traitement de la RAI '.$pgCmdFichierRps->getId().' est terminé');
        //}
        }    
        $date = new \DateTime();
        $cptRaisTraitesTot = $cptRaisTraitesOk + $cptRaisTraitesNok;
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : ' . $cptRaisTraitesTot . " RAI(s) traitée(s), " . $cptRaisTraitesOk . " OK, " . $cptRaisTraitesNok . " NOK");
    }

    protected function _coherenceRaiDai($pgCmdFichierRps) {
        
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Début cohérence RAI/DAI');
        
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();

        // Vérif code demande
        if (count($diff = $this->repoPgTmpValidEdilabo->getDiffCodeDemande($demandeId, $reponseId)) > 0) {
            $this->_addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: code demande", null, $diff);
        } else {
			//$this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - code demande ok');
		}

        // Vérif code prélèvement
        if (count($diff = $this->repoPgTmpValidEdilabo->getDiffCodePrelevementAdd($demandeId, $reponseId)) > 0) {
            $this->_addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: codes prélèvement RAI en trop", null, $diff);
        } else {
			//$this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - codes prélèvements ok');
		}
		
        // A tester à l'issue des données brutes (lorsqu'on est censé avoir tous les codes prélevements de la DAI)
//        if (count($diff = $this->repoPgTmpValidEdilabo->getDiffCodePrelevementMissing($demandeId, $reponseId)) > 0) {
//            $this->_addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: code prélèvement RAI manquant", null, $diff);
//        }
        // Vérif Date prélèvement, si hors période
        $codePrelevs = $this->repoPgTmpValidEdilabo->getCodePrelevement($demandeId, $reponseId);
		$this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - '.count($codePrelevs).' codes prélèvements');
        foreach ($codePrelevs as $codePrelev) {
			$prelevRealise = true;
			
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
            } else {
				//$this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : '.$codePrelev["codePrelevement"].' : preleveur ok');
			}

            if (count($diff = $this->repoPgTmpValidEdilabo->getDiffLabo($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                if ($this->_existePresta($diff)) {
                    $this->_addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Laboratoire", $codePrelev["codePrelevement"], $diff);
                } else {
                    $this->_addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Laboratoire", $codePrelev["codePrelevement"], $diff);
                }
            } else {
				//$this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : '.$codePrelev["codePrelevement"].' : labo ok');
			}

            // Vérif STQ : concordance STQ RAI (unique ou multiple) / DAI : stations rajoutées => Erreur
            if (count($diff = $this->repoPgTmpValidEdilabo->getDiffCodeStation($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
                $this->_addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Stations rajoutées", $codePrelev["codePrelevement"], $diff);
            } else {
				//$this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : '.$codePrelev["codePrelevement"].' : stations ok');
			}
			
			//test si prelevement ok
			$meSituHydro = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1726, $demandeId, $reponseId, $codePrelev["codePrelevement"]);
            if (!is_null($meSituHydro) && $meSituHydro <= 2) {
				$prelevRealise = false;
			}
			
			if ($prelevRealise) {
				
				// paramètres/unité : si unité changée => erreur
				$mesuresRps = $this->repoPgTmpValidEdilabo->getMesures($codePrelev["codePrelevement"], $demandeId, $reponseId);
				$date = new \DateTime();
				$this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - '.$codePrelev["codePrelevement"].' - '.count($mesuresRps).' mesures');
				if (count($mesuresRps) > 0) {
					foreach ($mesuresRps as $mesureRps) {
						$mesureDmd = $this->repoPgTmpValidEdilabo->getMesuresByCodeParametre($mesureRps['codeParametre'], $codePrelev["codePrelevement"], $demandeId, null, $mesureRps['codeFraction']);
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
				} else {
					//$this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : '.$codePrelev["codePrelevement"].' : pas de params ajoutes');
				}

				// paramètres/unité : paramètre manquant => erreur
				if (count($diffMiss = $this->repoPgTmpValidEdilabo->getDiffCodeParametreMissing($codePrelev["codePrelevement"], $demandeId, $reponseId)) > 0) {
					if ( ((count($diffMiss) == 1) && in_array(1429, $diffMiss)) || ($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getMarche()->getTypeMarche() == 'MOE') ) {
						$this->_addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Paramètre manquant", $codePrelev["codePrelevement"], $diffMiss);
					} else {
						$this->_addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Paramètre manquant", $codePrelev["codePrelevement"], $diffMiss);
					}
				} else {
					//$this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : '.$codePrelev["codePrelevement"].' : pas de params manquants');
				}
				
				// paramètres/unité : fractions différentes => erreur
				$diffFractions = $this->repoPgTmpValidEdilabo->getDiffCodeFraction($codePrelev["codePrelevement"], $demandeId, $reponseId);
				$diff = array();
				foreach($diffFractions as $diffFraction){
					if (!in_array($diffFraction, $diffMiss)) {
						$diff[] = $diffFraction;
					}
				}
				if (count($diff) > 0) {
					if ($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getMarche()->getTypeMarche() == 'MOE') {
						$this->_addLog('warning', $demandeId, $reponseId, "Incoherence RAI/DAI: Fractions différentes", $codePrelev["codePrelevement"], $diff);
					} else {
						$this->_addLog('error', $demandeId, $reponseId, "Incoherence RAI/DAI: Fractions différentes", $codePrelev["codePrelevement"], $diff);
					}
				}  else {
					//$this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : '.$codePrelev["codePrelevement"].' : fractions ok');
					//($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getMarche()->getTypeMarche() == 'MOE')
				}
            }
            
        }
        
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Fin cohérence RAI/DAI');
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
        $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R36');
        
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Début controle vraisemblance');

        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        $codePrelevements = $this->repoPgTmpValidEdilabo->getCodePrelevement($demandeId, $reponseId);
		$this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - '.count($codePrelevements).' prelevements');
		
        // Contrôles sur toutes les valeurs insérées
        foreach ($codePrelevements as $codePrelevement) {
            $codePrelevement = $codePrelevement['codePrelevement'];

            $meSituHydro = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1726, $demandeId, $reponseId, $codePrelevement);

            if (!is_null($meSituHydro) && $meSituHydro <= 2) {
                $this->_addLog('warning', $demandeId, $reponseId, "Situation Hydro = " . $meSituHydro, $codePrelevement, 1726);
                $codesRqValides = $this->repoPgTmpValidEdilabo->getCodeRqValideByCodePrelevement($demandeId, $reponseId, $codePrelevement);
                if (count($codesRqValides) > 0) {
                    $this->_addLog('error', $demandeId, $reponseId, "Situation Hydro : Code Remarque impossible ", $codePrelevement, 1726);
                } 
				//else {
                    $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->findBy(array('fichierRpsId' => $reponseId, 'demandeId' => $demandeId, 'codePrelevement' => $codePrelevement, 'inSitu' => 0));
                    foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
                        $pgTmpValidEdilabo->setCodeRqM(0);
                        //$pgTmpValidEdilabo->setResM(null);
                    }
                    $this->emSqe->flush();
                //}
            } else {
                $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->findBy(array('fichierRpsId' => $reponseId, 'demandeId' => $demandeId, 'codePrelevement' => $codePrelevement));
                $date = new \DateTime();
				$this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - '.$codePrelevement.' - '.count($pgTmpValidEdilabos).' mesures tmp');
				foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
                    // Appel du service
                    $this->controleVraisemblanceSpeProcess($pgTmpValidEdilabo, $codePrelevement, $pgCmdFichierRps);
                }
                $this->controleVraisemblanceGlobalProcess($codePrelevement, $pgCmdFichierRps);
            }
        }
        
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Fin controle vraisemblance');
    }
    
    public function controleVraisemblanceSpeProcess($pgTmpValidEdilabo, $codePrelevement, $pgCmdFichierRps) {
        
        $mesure = $pgTmpValidEdilabo->getResM();
        $codeRq = $pgTmpValidEdilabo->getCodeRqM();
        $codeParametre = $pgTmpValidEdilabo->getCodeParametre();
        $inSitu = $pgTmpValidEdilabo->getInSitu();
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        
        $controleVraisemblaceService = $this->getContainer()->get('aeag_sqe.controle_vraisemblance');
        // III.1
        //$dateLog = new \DateTime();
        //$this->output->writeln($dateLog->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Controle Vraisemblance - '.$codePrelevement.' - Début III.1');
        if (($result = $controleVraisemblaceService->champsNonRenseignes($mesure, $codeRq, $codeParametre, $inSitu)) != true) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }
        //$dateLog = new \DateTime();
        //$this->output->writeln($dateLog->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Controle Vraisemblance - '.$codePrelevement.' - Fin III.1');
        

        // III.1.1
        //$dateLog = new \DateTime();
        //$this->output->writeln($dateLog->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Controle Vraisemblance - '.$codePrelevement.' - Début III.1.1');
        if (($result = $controleVraisemblaceService->champsNonRenseignesEtValeursVides($mesure, $codeRq, $codeParametre, $inSitu)) != true) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }
        //$dateLog = new \DateTime();
        //$this->output->writeln($dateLog->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Controle Vraisemblance - '.$codePrelevement.' - Fin III.1.1');

        // III.2
        //$dateLog = new \DateTime();
        //$this->output->writeln($dateLog->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Controle Vraisemblance - '.$codePrelevement.' - Début III.2');
        if (($result = $controleVraisemblaceService->valeursNumeriques($mesure, $codeRq)) != true) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }
        //$dateLog = new \DateTime();
        //$this->output->writeln($dateLog->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Controle Vraisemblance - '.$codePrelevement.' - Fin III.2');

        // III.3 Valeurs =0 (hors TH (1345), TA (1346), TAC (1347), Temp(1301)) hors codes observations environnementales / résultat = 0 possible pour les paramètres de cette liste (et pour 1345, 1346, 1347 et 1301) => Erreur
        //$dateLog = new \DateTime();
        //$this->output->writeln($dateLog->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Controle Vraisemblance - '.$codePrelevement.' - Début III.3');
        if (($result = $controleVraisemblaceService->valeursEgalZero($mesure, $codeParametre, $inSitu)) != true) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }
        //$dateLog = new \DateTime();
        //$this->output->writeln($dateLog->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Controle Vraisemblance - '.$codePrelevement.' - Fin III.3');

        // III.4 Valeurs < 0 (hors température de air 1409, potentiel REDOX 1330)
        //$dateLog = new \DateTime();
        //$this->output->writeln($dateLog->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Controle Vraisemblance - '.$codePrelevement.' - Début III.4');
        if (($result = $controleVraisemblaceService->valeursInfZero($mesure, $codeParametre)) != true) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }
        //$dateLog = new \DateTime();
        //$this->output->writeln($dateLog->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Controle Vraisemblance - '.$codePrelevement.' - Fin III.4');

        // III.5 Valeurs avec code remarque '> (3)' hors bactério (1147,1448,1449,1451,5479,6455); code remarque ="Trace (7)"
        //$dateLog = new \DateTime();
        //$this->output->writeln($dateLog->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Controle Vraisemblance - '.$codePrelevement.' - Début III.5');
        if (($result = $controleVraisemblaceService->valeursSupTrois($codeParametre, $codeRq)) != true) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement, $codeParametre);
        }
        //$dateLog = new \DateTime();
        //$this->output->writeln($dateLog->format('d/m/Y H:i:s') . '- Process RAI : RAI '.$pgCmdFichierRps->getId().' - Controle Vraisemblance - '.$codePrelevement.' - Fin III.5');
    }
    
    public function controleVraisemblanceGlobalProcess($codePrelevement, $pgCmdFichierRps) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        
        $controleVraisemblaceService = $this->getContainer()->get('aeag_sqe.controle_vraisemblance');
        
        // III.6 1 < pH(1302) < 14
        $mPh = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1302, $demandeId, $reponseId, $codePrelevement);
        if (($result = $controleVraisemblaceService->pH($mPh)) != true) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement);
        }

        // III.7 modèle  de WEISS : cohérence Teau, % O2, Concentration O2  sauf si Conductivité > 10 000
        $mTxSatOx = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1312, $demandeId, $reponseId, $codePrelevement);
        $mOxDiss = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1311, $demandeId, $reponseId, $codePrelevement);
        $mTEau = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1301, $demandeId, $reponseId, $codePrelevement);
        $mConductivite = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1303, $demandeId, $reponseId, $codePrelevement);
        if (($result = $controleVraisemblaceService->modeleWeiss($mTxSatOx, $mOxDiss, $mTEau, $mConductivite)) != true) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement);
        }

        // III.8 Balance ionique (meq) sauf si tous les résultats < LQ
        $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1367, $demandeId, $reponseId, $codePrelevement, 3);
        $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1375, $demandeId, $reponseId, $codePrelevement, 3);
        $cCationParams = array(1374 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1374, $demandeId, $reponseId, $codePrelevement),
            1335 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement, 3),
            1372 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1372, $demandeId, $reponseId, $codePrelevement, 3),
            1367 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1367, $demandeId, $reponseId, $codePrelevement, 3),
            1375 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1375, $demandeId, $reponseId, $codePrelevement, 3)
        );
        $cAnionParams = array(1433 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement),
            1340 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1340, $demandeId, $reponseId, $codePrelevement, 3),
            1338 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1338, $demandeId, $reponseId, $codePrelevement, 3),
            1337 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1337, $demandeId, $reponseId, $codePrelevement, 3),
            1327 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1327, $demandeId, $reponseId, $codePrelevement, 3),
            1339 => $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1339, $demandeId, $reponseId, $codePrelevement, 3)
        );

        $codeRqCationParams = array();
        $codeRqAnionParams = array();
        foreach ($cCationParams as $idx => $cCationParam) {
            $codeRqCationParams[$idx] = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement, 3);
        }
        foreach ($cAnionParams as $idx => $cAnionParam) {
            $codeRqAnionParams[$idx] = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre($idx, $demandeId, $reponseId, $codePrelevement, 3);
        }
        if (($result = $controleVraisemblaceService->balanceIonique($cCationParams, $cAnionParams, $codeRqCationParams, $codeRqAnionParams)) != true) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement);
        }

        // III.9 Comparaison Balance ionique / conductivité (Feret)
        if (($result = $controleVraisemblaceService->balanceIoniqueTds2($cCationParams, $cAnionParams, $codeRqCationParams, $codeRqAnionParams, $mConductivite)) != true) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement);
        }

        // III.10 [PO4] (1433) en P < [P total](1350) 
        $mPo4 = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement);
        $mP = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1350, $demandeId, $reponseId, $codePrelevement);
        $codeRqPo4 = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1433, $demandeId, $reponseId, $codePrelevement);
        $codeRqP = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1350, $demandeId, $reponseId, $codePrelevement);
        if (($result = $controleVraisemblaceService->orthophosphate($mPo4, $mP, $codeRqPo4, $codeRqP)) != true) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement);
        }
        
        // III.11 NH4 (1335) en N < Nkj (1319)
        $mNh4 = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement);
        $mNkj = $this->repoPgTmpValidEdilabo->getMesureByCodeParametre(1319, $demandeId, $reponseId, $codePrelevement);
        $codeRqNh4 = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1335, $demandeId, $reponseId, $codePrelevement);
        $codeRqNkj = $this->repoPgTmpValidEdilabo->getCodeRqByCodeParametre(1319, $demandeId, $reponseId, $codePrelevement);
        if (($result = $controleVraisemblaceService->ammonium($mNh4, $mNkj, $codeRqNh4, $codeRqNkj)) != true) {
            $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement);
        }
        
        // III.12 Valeur de pourcentage hors 1312 oxygène (ex : matière sèche ou granulo) : non compris entre 0 et 100 si code unité = 243 ou 246
        $tabMesures = array(243 => $this->repoPgTmpValidEdilabo->getMesureByCodeUnite(243, $demandeId, $reponseId, $codePrelevement, 1312),
            246 => $this->repoPgTmpValidEdilabo->getMesureByCodeUnite(246, $demandeId, $reponseId, $codePrelevement, 1312));
        if (($results = $controleVraisemblaceService->pourcentageHorsOxygene($tabMesures)) != true) {
            foreach($results as $result) {
                $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement);
                //$this->_addLog('info', $demandeId, $reponseId, 'III.12', $codePrelevement, $codeParametre);
            }
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
        if (($results = $controleVraisemblaceService->sommeParametresDistincts($sommeParams, $resultParams, $params)) != true) {
            foreach ($results as $result) {
                $this->_addLog($result[0], $demandeId, $reponseId, $result[1], $codePrelevement);
            }
        }
        
        $this->controleVraisemblanceMacroPolluants($demandeId, $reponseId, $codePrelevement);
        
        $this->detectionCodeRemarqueLot7($demandeId, $reponseId, $codePrelevement);
        
        $this->detectionCodeRemarqueLot8($demandeId, $reponseId, $codePrelevement);
        
        $this->controleLqAeag($pgCmdFichierRps, $codePrelevement);
        
        $this->codeMethodesValides($pgCmdFichierRps, $codePrelevement);

    }
    
    
    //III.14 Contrôle de vraisemblance par parmètres macropolluants : Résultat d’analyse< Valeur max de la base x 2 
    public function controleVraisemblanceMacroPolluants($demandeId, $reponseId, $codePrelevement) {
        $pgProgUnitesPossiblesParams = $this->repoPgProgUnitesPossiblesParam->getPgProgUnitesPossiblesParamWithValeurMax();
        foreach ($pgProgUnitesPossiblesParams as $pgProgUnitesPossiblesParam) {
            $mesures = $this->repoPgTmpValidEdilabo->getAllMesureByCodeParametre($pgProgUnitesPossiblesParam->getCodeParametre(), $demandeId, $reponseId, $codePrelevement);
            foreach($mesures as $mesure) {
                if (!is_null($mesure)) {
                    if ($mesure['resM'] > $pgProgUnitesPossiblesParam->getValMax()) {
                        $this->_addLog('warning', $demandeId, $reponseId, "Controle Vraisemblance Macro Polluants : Le résultat est supérieur à la valeur attendue pour le paramètre " . $pgProgUnitesPossiblesParam->getCodeParametre()->getCodeParametre(), $codePrelevement, $mesure['resM']);
                        //return array("warning", "Controle Vraisemblance Macro Polluants : Le résultat est supérieur à la valeur attendue pour le paramètre");
                    }
                }
            }
        }
    }

    //III.15 Détection Code remarque Lot 7 (Etat chimique, Substance pertinentes, Complément AEAG, PSEE) :  % de détection différent de 100 (= recherche d'absence de code remarque) suivant liste ref-doc
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
    public function controleLqAeag($pgCmdFichierRps, $codePrelevement) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->findBy(array('demandeId' => $demandeId, 'fichierRpsId' => $reponseId, 'codePrelevement' => $codePrelevement));
        if ($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getMarche()->getTypeMarche() == 'MOA') {
            foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
                if (!is_null($pgTmpValidEdilabo->getLqM())) {
                    $lq = $this->repoPgProgLotLqParam->isValidLq($pgCmdFichierRps->getDemande()->getLotan()->getLot(), $pgTmpValidEdilabo->getCodeParametre(), $pgTmpValidEdilabo->getCodeFraction(), $pgTmpValidEdilabo->getLqM());
                    if (count($lq) == 0) {
                        $this->_addLog('warning', $demandeId, $reponseId, "Controle Lq AEAG : Lq supérieure à la valeur prévue: ". $pgTmpValidEdilabo->getLqM(), $codePrelevement, $pgTmpValidEdilabo->getCodeParametre());
                        //return array("warning", "Controle Lq AEAG : Lq supérieure à la valeur prévue");
                    }
                }
            }
        }
    }
    
    public function codeMethodesValides($pgCmdFichierRps, $codePrelevement) {
        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();
        // Récupération des code Methodes
        $codesMethodes = $this->repoPgTmpValidEdilabo->getCodesMethodes($codePrelevement, $demandeId, $reponseId);
        //
        foreach($codesMethodes as $codeMethode) {
            foreach($codeMethode as $codeMeth) {
                if ($codeMeth != "") {
                    $pgSandreMethode = $this->repoPgSandreMethodes->findOneByCodeMethode($codeMeth);
                    if (is_null($pgSandreMethode)) {
                        $this->_addLog('error', $demandeId, $reponseId, "Controle codes méthode : Code Méthode inexistant en base: ". $codeMeth, $codePrelevement, $codeMeth);
                    }    
                }
            }
        }
    }

}
