<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class IntegrationDonneesBrutesCommand extends AeagCommand {

    protected function configure() {
        $this
                ->setName('rai:integration_db')
                ->setDescription('Intégration des données brutes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        parent::execute($input, $output);

        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Integration Données Brutes : Début');

        // On récupère les RAIs dont les phases sont en R40 et R41
        $pgCmdFichiersRps = $this->repoPgCmdFichiersRps->getReponsesValidesDb();

        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R42');
        }

        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            $date = new \DateTime();
            $this->output->writeln($date->format('d/m/Y H:i:s') . '- Integration Données Brutes : Le traitement de la RAI ' . $pgCmdFichierRps->getId() . ' commence');
            $this->_integrationDonneesBrutes($pgCmdFichierRps);


            // Fichier csv
            $chemin = $this->getContainer()->getParameter('repertoire_echange');
            $donneesBrutes = $this->repoPgCmdPrelev->getDonneesBrutes($pgCmdFichierRps);
            $this->getContainer()->get('aeag_sqe.process_rai')->exportCsvDonneesBrutes($this->emSqe, $chemin, $pgCmdFichierRps, $donneesBrutes);

            // Envoi de mail au producteur et au titulaire
            $destinataires = array();
            // Verifier que le titulaire n'est pas nul
            if (!is_null($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getTitulaire())) {
                $prestataires = $this->repoPgProgWebUsers->getPgProgWebusersByPrestataireAndTypeMilieu($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getTitulaire(), $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getCodeMilieu());
                foreach($prestataires as $prestataire){
                    $destinataires[$prestataire->getId()] = $prestataire;
                }
            }
            if (!is_null($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getMarche()->getRespAdrCor())) {
                $producteurs = $this->repoPgProgWebUsers->getNotAdminPgProgWebusersByProducteurAndMarcheAndTypeMilieu($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getMarche()->getRespAdrCor(), $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getMarche(), $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getCodeMilieu());
                foreach($producteurs as $producteur) {
                    $destinataires[$producteur->getId()] = $producteur;
                }
            }
            
            $admins = $this->repoPgProgWebuserTypmil->getPgProgWebuserTypmilByTypMilAndTypeUser($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getCodeMilieu(), 'ADMIN');
            foreach ($admins as $admin) {
                if (!is_null($admin)) {
                    $destinataires[$admin->getWebuser()->getId()] = $admin->getWebuser();
                }
            }
                    
            // Compléter le fichier de logs
            $this->_insertFichierLog($pgCmdFichierRps, "Intégration Données Brutes");
            
            $objetMessage = "SQE - RAI : Fichier csv des données brutes disponible pour le lot " . $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getNomLot();
            if ($this->getEnv() !== 'prod') {
                $objetMessage .= " - ".$this->getEnv();
            }
            $urlDb = $this->getContainer()->get('router')->generate('AeagSqeBundle_echangefichiers_reponses_telecharger', array("reponseId" => $pgCmdFichierRps->getId(), "typeFichier" => "DB"), UrlGeneratorInterface::ABSOLUTE_URL);
            $urlCr = $this->getContainer()->get('router')->generate('AeagSqeBundle_echangefichiers_reponses_telecharger', array("reponseId" => $pgCmdFichierRps->getId(), "typeFichier" => "CR"), UrlGeneratorInterface::ABSOLUTE_URL);
            $txtMessage = "Lot : " . $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getNomLot() . "<br/>";
            $txtMessage .= "Période : " . $pgCmdFichierRps->getDemande()->getPeriode()->getLabelPeriode() . "<br/>";
            $txtMessage .= 'Vous pouvez récupérer le fichier csv à l\'adresse suivante: <a href="' . $urlDb . '">' . $pgCmdFichierRps->getNomFichierDonneesBrutes() . '</a><br/>';
            $txtMessage .= 'Le fichier de compte rendu est, quand à lui, disponible ici: <a href="' . $urlCr . '">' . $pgCmdFichierRps->getNomFichierCompteRendu() . '</a>';
            $txtMessage .= '<br/><br/>Merci de nous faire parvenir vos remarques ou corrections éventuelles.';
            
            $sendingMail = true;
            foreach ($destinataires as $destinataire) {
                if (!is_null($destinataire)) {
                    $mailer = $this->getContainer()->get('mailer');
                    if (!$this->getContainer()->get('aeag_sqe.message')->createMail($this->em, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                        $this->_addLog('warning', $pgCmdFichierRps->getDemande()->getId(), $pgCmdFichierRps->getId(), "Erreur lors de l\'envoi de mail dans le process de verification des RAIs", null, $destinataire->getMail());
                        $sendingMail = false;
                    } else {
                        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Integration Données Brutes : Un email a été envoyé à ' . $destinataire->getMail() . ' pour la RAI '.$pgCmdFichierRps->getId());
                    }
                }
            }
            
            if ($sendingMail) {
                // Vider la table tempo des lignes correspondant à la RAI
                $this->_cleanTmpTable($pgCmdFichierRps);

                // Evolution de la phase
                $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R45');

                $this->_updatePhaseDemande($pgCmdFichierRps->getDemande());
            }
        }

        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Integration Données Brutes : ' . count($pgCmdFichiersRps) . " RAI(s) traitée(s)");
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Integration Données Brutes : Fin');
    }

    protected function _integrationDonneesBrutes($pgCmdFichierRps) {

        $demandeId = $pgCmdFichierRps->getDemande()->getId();
        $reponseId = $pgCmdFichierRps->getId();

        $codesPrelevement = $this->repoPgTmpValidEdilabo->getCodePrelevement($demandeId, $reponseId);
        foreach ($codesPrelevement as $codePrelevement) {
            $pgCmdPrelev = $this->repoPgCmdPrelev->findOneBy(array('demande' => $demandeId, 'codePrelevCmd' => $codePrelevement));
            if (!is_null($pgCmdPrelev)) {
                if ($this->isAlreadyAdded($pgCmdFichierRps, $pgCmdPrelev, 'M50')) {
                    $this->_addLog('warning', $demandeId, $reponseId, "Le prélèvement ".$pgCmdPrelev->getCodePrelevCmd(). " existe déjà et ne peut pas être mis à jour (clos)");
                } else { // < 350
                    
                    // En fonction de la phase, ajouter des warnings lorsqu'une nouvelle données est insérée
                    $phaseDmd = $pgCmdPrelev->getPhaseDmd();
                    
                    if($phaseDmd->getId() >= '330' && $phaseDmd->getId() < '350') {
                        $this->_addLog('warning', $demandeId, $reponseId, "Le prélèvement ".$pgCmdPrelev->getCodePrelevCmd()." existe déjà - mise à jour");
                    }
                    
                    $pgCmdPrelev->setFichierRps($pgCmdFichierRps);
                    $pgTmpValidEdilabo = $this->repoPgTmpValidEdilabo->findOneBy(array('fichierRpsId' => $reponseId, 'demandeId' => $demandeId, 'codePrelevement' => $codePrelevement, 'numOrdre' => '1'));
                    if (!is_null($pgTmpValidEdilabo->getDatePrel())) {
                        if (!is_null($pgTmpValidEdilabo->getHeurePrel())) {
                            $date = $pgTmpValidEdilabo->getDatePrel() . ' ' . $pgTmpValidEdilabo->getHeurePrel();
                            $datePrel = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
                        } else {
                            $datePrel = \DateTime::createFromFormat('Y-m-d', $pgTmpValidEdilabo->getDatePrel());
                        }
                        $pgCmdPrelev->setDatePrelev($datePrel);
                    }
                    $pgCmdPrelev->setCodeMethode($pgTmpValidEdilabo->getMethPrel());
                    if ($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getCodeMilieu()->getCodeMilieu() == 'RPC') {
                        $meSituHydro = $this->getMesureByCodeParametre(1726, $demandeId, $reponseId, $codePrelevement);
                        if (!is_null($meSituHydro) && ($meSituHydro > 0 && $meSituHydro <= 2)) {
                            $pgCmdPrelev->setRealise("0");
                        } else {
                            $pgCmdPrelev->setRealise("1");
                        }
                    } else {
                        $codesRqValides = $this->repoPgTmpValidEdilabo->getCodeRqValideByCodePrelevement($demandeId, $reponseId, $codePrelevement);
                        if (count($codesRqValides) == 0) {
                            $this->_addLog('warning', $demandeId, $reponseId, "Toutes les analyses ont un code rq = 0");
                            $pgCmdPrelev->setRealise("0");
                        } else {
                            $pgCmdPrelev->setRealise("1");
                        }
                        
                    }
                    $this->emSqe->persist($pgCmdPrelev);
                    // Cas particulier selon num_ordre => creer une nouvelle ligne
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
                    $pgCmdPrelevPc->setCommentaire($pgTmpValidEdilabo->getCommentPrel());
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

                    $pgTmpValidEdilabos = $this->repoPgTmpValidEdilabo->findBy(array('fichierRpsId' => $reponseId, 'demandeId' => $demandeId, 'codePrelevement' => $codePrelevement));

                    foreach ($pgTmpValidEdilabos as $pgTmpValidEdilabo) {
                        if ($pgTmpValidEdilabo->getInSitu() == 0) {
                            $pgSandreParametres = $this->repoPgSandreParametres->findOneByCodeParametre($pgTmpValidEdilabo->getCodeParametre());
                            $pgCmdMesureEnv = $this->repoPgCmdMesureEnv->findOneBy(array("prelev" => $pgCmdPrelev, "codeParametre" => $pgSandreParametres));
                            if (is_null($pgCmdMesureEnv)) {
                                $pgCmdMesureEnv = new \Aeag\SqeBundle\Entity\PgCmdMesureEnv();
                                if($phaseDmd->getId() >= '330' && $phaseDmd->getId() < '350') {
                                    $this->_addLog('warning', $demandeId, $reponseId, "Paramètre ".$pgSandreParametres->getCodeParametre()." rajouté par rapport au dépôt précédent");
                                }
                            }
                            
                            $pgCmdMesureEnv->setPrelev($pgCmdPrelev);
                            
                            if (!is_null($pgSandreParametres)) {
                                $pgCmdMesureEnv->setCodeParametre($pgSandreParametres);
                            }
                            if (!is_null($pgTmpValidEdilabo->getDateM())) {
                                if (!is_null($pgTmpValidEdilabo->getHeureM())) {
                                    $date = $pgTmpValidEdilabo->getDateM() . ' ' . $pgTmpValidEdilabo->getHeureM();
                                    $dateM = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
                                } else {
                                    $dateM = \DateTime::createFromFormat('Y-m-d', $pgTmpValidEdilabo->getDateM());
                                }
                                $pgCmdMesureEnv->setDateMes($dateM);
                            }
                            
                            if ($pgTmpValidEdilabo->getResM() == "") {
                                $pgCmdMesureEnv->setResultat(null);
                            } else {
                                $pgCmdMesureEnv->setResultat($pgTmpValidEdilabo->getResM());
                            }
                            
                            $pgSandreUnites = $this->repoPgSandreUnites->findOneByCodeUnite($pgTmpValidEdilabo->getCodeUnite());
                            if (!is_null($pgSandreUnites)) {
                                $pgCmdMesureEnv->setCodeUnite($pgSandreUnites);
                            }
                            $pgCmdMesureEnv->setCodeRemarque($pgTmpValidEdilabo->getCodeRqM());
                            $pgCmdMesureEnv->setCodeMethode($pgTmpValidEdilabo->getMethPrel());
                            $pgCmdMesureEnv->setCodeStatut($pgTmpValidEdilabo->getCodeStatut());
                            $pgCmdMesureEnv->setCommentaire($pgTmpValidEdilabo->getCommentaire());
                            $pgProgLotParamAn = $this->repoPgProgLotParamAn->findOneById($pgTmpValidEdilabo->getParamProgId());
                            if (!is_null($pgProgLotParamAn)) {
                                $pgCmdMesureEnv->setParamProg($pgProgLotParamAn);
                            }
                            $this->emSqe->persist($pgCmdMesureEnv);
                        } else {
                            $pgSandreParametres = $this->repoPgSandreParametres->findOneByCodeParametre($pgTmpValidEdilabo->getCodeParametre());
                            $pgSandreFractions = $this->repoPgSandreFractions->findOneByCodeFraction($pgTmpValidEdilabo->getCodeFraction());
                            $pgCmdAnalyse = $this->repoPgCmdAnalyse->findOneBy(array("prelevId" => $pgCmdPrelev->getId(), "numOrdre" => $pgTmpValidEdilabo->getNumOrdre(), "codeParametre" => $pgSandreParametres, "codeFraction" => $pgSandreFractions));
                            if (is_null($pgCmdAnalyse)) {
                                $pgCmdAnalyse = new \Aeag\SqeBundle\Entity\PgCmdAnalyse();
                                if($phaseDmd->getId() >= '330' && $phaseDmd->getId() < '350') {
                                    $this->_addLog('warning', $demandeId, $reponseId, "Paramètre ".$pgSandreParametres->getCodeParametre()." rajouté par rapport au dépôt précédent");
                                }
                            }
                            
                            $pgCmdAnalyse->setPrelevId($pgCmdPrelev->getId());
                            $pgCmdAnalyse->setNumOrdre($pgTmpValidEdilabo->getNumOrdre());
                            if (!is_null($pgSandreParametres)) {
                                $pgCmdAnalyse->setCodeParametre($pgSandreParametres);
                            }
                            if (!is_null($pgSandreFractions)) {
                                $pgCmdAnalyse->setCodeFraction($pgSandreFractions);
                            }
                            $pgCmdAnalyse->setLieuAna($pgTmpValidEdilabo->getInSitu());

                            if (!is_null($pgTmpValidEdilabo->getDateM())) {
                                if (!is_null($pgTmpValidEdilabo->getHeureM())) {
                                    $date = $pgTmpValidEdilabo->getDateM() . ' ' . $pgTmpValidEdilabo->getHeureM();
                                    $dateM = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
                                } else {
                                    $dateM = \DateTime::createFromFormat('Y-m-d', $pgTmpValidEdilabo->getDateM());
                                }
                                $pgCmdAnalyse->setDateAna($dateM);
                            }
                            $resM = trim(preg_replace('/\t+/', '', $pgTmpValidEdilabo->getResM()));
                            if ($resM == "") {
                                $pgCmdAnalyse->setResultat(null);
                            } else {
                                $pgCmdAnalyse->setResultat($resM);
                            }
                            
                            $pgSandreUnites = $this->repoPgSandreUnites->findOneByCodeUnite($pgTmpValidEdilabo->getCodeUnite());
                            if (!is_null($pgSandreUnites)) {
                                $pgCmdAnalyse->setCodeUnite($pgSandreUnites);
                            }
                            $pgCmdAnalyse->setCodeRemarque($pgTmpValidEdilabo->getCodeRqM());
                            if ($pgTmpValidEdilabo->getLqM() == "") {
                                $pgCmdAnalyse->setLqAna(null);
                            } else {
                                $pgCmdAnalyse->setLqAna($pgTmpValidEdilabo->getLqM());
                            }
                            $pgCmdAnalyse->setRefAnaLabo($pgTmpValidEdilabo->getRefAnaLabo());
                            $pgCmdAnalyse->setCodeMethode($pgTmpValidEdilabo->getMethAna());
                            $pgCmdAnalyse->setAccreditation($pgTmpValidEdilabo->getAccredAna());
                            $pgCmdAnalyse->setConfirmation($pgTmpValidEdilabo->getConfirmAna());
                            $pgCmdAnalyse->setReserve($pgTmpValidEdilabo->getReservAna());
                            $pgCmdAnalyse->setCodeStatut($pgTmpValidEdilabo->getCodeStatut());
                            $pgCmdAnalyse->setCommentaire($pgTmpValidEdilabo->getCommentaire());
                            $pgProgLotParamAn = $this->repoPgProgLotParamAn->findOneById($pgTmpValidEdilabo->getParamProgId());
                            if (!is_null($pgProgLotParamAn)) {
                                $pgCmdAnalyse->setParamProg($pgProgLotParamAn);
                            }
                            $this->emSqe->persist($pgCmdAnalyse);
                        }
                    }
                    // Evolution de la phase du prelevement
                    $this->emSqe->flush();
                    $this->_updatePhasePrelevement($pgCmdPrelev, 'M40');
                }
            }
        }
        
        return true;
    }

}
