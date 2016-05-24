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
        $pgCmdFichiersRps = $this->repoPgCmdFichiersRps->getReponsesValides();
        
        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R42');
        }
        
        foreach ($pgCmdFichiersRps as $pgCmdFichierRps) {
            $date = new \DateTime();
            $this->output->writeln($date->format('d/m/Y H:i:s') . '- Integration Données Brutes : Le traitement de la RAI '.$pgCmdFichierRps->getId().' commence');
            $this->_integrationDonneesBrutes($pgCmdFichierRps);

            // Evolution de la phase
            $this->_updatePhaseFichierRps($pgCmdFichierRps, 'R45');

            $this->_updatePhaseDemande($pgCmdFichierRps->getDemande());

            // Fichier csv
            $chemin = $this->getContainer()->getParameter('repertoire_echange');
            $donneesBrutes = $this->repoPgCmdPrelev->getDonneesBrutes($pgCmdFichierRps);
            $this->getContainer()->get('aeag_sqe.process_rai')->exportCsvDonneesBrutes($this->emSqe, $chemin, $pgCmdFichierRps, $donneesBrutes);

            // Envoi de mail au producteur et au titulaire
            // TODO A Modifier
            // TODO FAire un distinct sur le tableau de destinataires
            $destinataires = array();
            $destinataires[] = $this->repoPgProgWebUsers->findOneByPrestataire($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getTitulaire());
            $destinataires[] = $this->repoPgProgWebUsers->findOneByProducteur($pgCmdFichierRps->getDemande()->getLotan()->getLot()->getMarche()->getRespAdrCor());
            
            $objetMessage = "SQE - RAI : Fichier csv des données brutes disponible pour le lot " . $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getNomLot();
            $url = $this->getContainer()->get('router')->generate('AeagSqeBundle_echangefichiers_reponses_telecharger', array("reponseId" => $pgCmdFichierRps->getId(), "typeFichier" => "DB"), UrlGeneratorInterface::ABSOLUTE_URL);
            $txtMessage = "Lot : " . $pgCmdFichierRps->getDemande()->getLotan()->getLot()->getNomLot() . "<br/>";
            $txtMessage .= "Période : " . $pgCmdFichierRps->getDemande()->getPeriode()->getLabelPeriode() . "<br/>";
            $txtMessage .= 'Vous pouvez récupérer le fichier csv à l\'adresse suivante : <a href="' . $url . '">' . $pgCmdFichierRps->getNomFichierDonneesBrutes() . '</a>';
            foreach ($destinataires as $destinataire) {
                if (!is_null($destinataire)) {
                    $mailer = $this->getContainer()->get('mailer');
                    if (!$this->getContainer()->get('aeag_sqe.message')->createMail($this->em, $mailer, $txtMessage, $destinataire, $objetMessage)) {
                        $this->_addLog('warning', $pgCmdFichierRps->getDemande()->getId(), $pgCmdFichierRps->getId(), "Erreur lors de l\'envoi de mail dans le process de verification des RAIs", null, $destinataire);
                    }
                }
            }

            // Vider la table tempo des lignes correspondant à la RAI
            $this->_cleanTmpTable($pgCmdFichierRps);
        }
        
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Integration Données Brutes : ' . count($pgCmdFichiersRps) . " RAI(s) traitée(s)");
        $date = new \DateTime();
        $this->output->writeln($date->format('d/m/Y H:i:s') . '- Integration Données Brutes : Fin');
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
                            $date = $pgTmpValidEdilabo->getDatePrel() . ' ' . $pgTmpValidEdilabo->getHeurePrel();
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
                                $date = $pgTmpValidEdilabo->getDateM() . ' ' . $pgTmpValidEdilabo->getHeureM();
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
                                $date = $pgTmpValidEdilabo->getDateM() . ' ' . $pgTmpValidEdilabo->getHeureM();
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
}