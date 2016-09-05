<?php

namespace Aeag\FrdBundle\Controller;

use Aeag\FrdBundle\Entity\Parametre;
use Aeag\FrdBundle\Entity\Phase;
use Aeag\AeagBundle\Entity\Departement;
use Aeag\FrdBundle\Entity\Finalite;
use Aeag\FrdBundle\Entity\FraisDeplacement;
use Aeag\FrdBundle\Entity\EtatFrais;
use Aeag\FrdBundle\Entity\Mandatement;
use Aeag\FrdBundle\Entity\SousTheme;
use Aeag\FrdBundle\Entity\TypeMission;
use Aeag\UserBundle\Entity\User;
use Aeag\AeagBundle\Entity\Correspondant;
use Aeag\FrdBundle\Form\Referentiel\MajPhaseType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ReferentielController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $session->set('refMess', null);

        return $this->render('AeagFrdBundle:Referentiel:index.html.twig');
    }

    public function chargeAppliAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeAppli');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $parametre = new Parametre();
        $parametre->setCode('ANNEE');
        $parametre->setLibelle(2016);
        $emFrd->persist($parametre);

        $parametre = new Parametre();
        $parametre->setCode('CONTACT');
        $parametre->setLibelle('bouyssi.corinne@eau-adour-garonne.fr');
        $emFrd->persist($parametre);

        $parametre = new Parametre();
        $parametre->setCode('LIB_MAINTENANCE');
        $parametre->setLibelle('Le site est actuellement indisponible pour cause de maintenance');
        $emFrd->persist($parametre);

        $parametre = new Parametre();
        $parametre->setCode('LIB_MESSAGE');
        $emFrd->persist($parametre);

        $parametre = new Parametre();
        $parametre->setCode('MAINTENANCE');
        $parametre->setLibelle('N');
        $emFrd->persist($parametre);

        $parametre = new Parametre();
        $parametre->setCode('REP_EXPORT');
        $parametre->setLibelle('/base/extranet/Transfert/Frd/Export');
        $emFrd->persist($parametre);

        $parametre = new Parametre();
        $parametre->setCode('REP_IMPORT');
        $parametre->setLibelle('/base/extranet/Transfert/Frd/Import');
        $emFrd->persist($parametre);

        $parametre = new Parametre();
        $parametre->setCode('REP_REFERENTIEL');
        $parametre->setLibelle('/base/extranet/Transfert/Frd/Referentiel');
        $emFrd->persist($parametre);

        $phase = new Phase();
        $phase->setCode('10');
        $phase->setLibelle('En cours de saisie');
        $emFrd->persist($phase);

        $phase = new Phase();
        $phase->setCode('20');
        $phase->setLibelle('En attente de réception du courrier');
        $emFrd->persist($phase);

        $phase = new Phase();
        $phase->setCode('30');
        $phase->setLibelle('Courrier reçu à l\'agence de l\'eau');
        $emFrd->persist($phase);



        $phase = new Phase();
        $phase->setCode('40');
        $phase->setLibelle('En cours de traitement par l\'agence de l\'eau');
        $emFrd->persist($phase);

        $phase = new Phase();
        $phase->setCode('60');
        $phase->setLibelle('Rembourser');
        $emFrd->persist($phase);

        $emFrd->flush();

        $message = null;

        return $message;
    }

    public function chargeReferentielAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeReferentiel');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $emFrd = $this->getDoctrine()->getManager('frd');
        $parametre = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => 'REP_REFERENTIEL'));
        $rep_export = $parametre->getLibelle();
        $fichiers = array();
        $i = 0;
        $dir = opendir($rep_export) or die("Erreur le repertoire $rep_export n\'existe pas");
        while ($fic = readdir($dir)) {
            //print_r('file : ' . $fic. "\n");
            if (is_file($fic) or ! in_array($fic, array(".", ".."))) {
                if (substr($fic, 0, 4) == 'frd_' or substr($fic, 0, 5) == 'init_') {
                    $lines = file($rep_export . "/" . $fic);
                    $nblignes = count($lines) - 1;
                    $taille = filesize($rep_export . "/" . $fic);
                    if ($taille >= 1073741824) {
                        $taille = round($taille / 1073741824 * 100) / 100 . " Go";
                    } elseif ($taille >= 1048576) {
                        $taille = round($taille / 1048576 * 100) / 100 . " Mo";
                    } elseif ($taille >= 1024) {
                        $taille = round($taille / 1024 * 100) / 100 . " Ko";
                    } else {
                        $taille = $taille . " octets";
                    }
                    if ($taille == " octets") {
                        $taille = "-";
                    }
                    $date = date("d/m/Y H:i:s", filemtime($rep_export . "/" . $fic));

                    $fichiers[$i] = array('fic' => $rep_export . "/" . $fic,
                        'nom' => $fic,
                        'nblignes' => $nblignes,
                        'taille' => $taille,
                        'date' => $date,
                        'indice' => $i);
                    $i++;
                }
            }
        }
        closedir($dir);

        //$session->set('refMess', null);

        return $this->render('AeagFrdBundle:Referentiel:listeFichiersExport.html.twig', array(
                    'repertoire' => $rep_export,
                    'fichiers' => $fichiers,
                    'message' => $session->get('refMess')
        ));
    }

    public function chargeFichierAction($ficent = null, $message = null, $passage = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeFichier');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $session->set('refMess', null);

        $ficent = $_POST['ficent'];


        if (!$passage) {

            if ($ficent == "frd_users.csv") {
                $message = $this->chargeUsersAction($ficent);
            }

            if ($ficent == "frd_finalite.csv" or $ficent == "init_finalite.csv") {
                $message = $this->chargeFinaliteAction($ficent);
            }
            if ($ficent == "frd_sous_theme.csv" or $ficent == "init_sous_theme.csv") {
                $message = $this->chargeSousThemeAction($ficent);
            }
            if ($ficent == "frd_type_mission.txt" or $ficent == "init_type_mission.csv") {
                $message = $this->chargeTypeMissionAction($ficent);
            }

            if ($ficent == "frd_dept.csv" or $ficent == "init_departement.csv") {
                $message = $this->chargeDepartementsAction($ficent);
            }
            //return new Response ('fichier : ' . $ficent);
            if ($ficent == "init_frais_deplacement.csv") {
                $message = $this->chargeFraisDeplacementsAction($ficent);
            }
            if ($ficent == "frd_frais_deplacement.csv") {
                $message = $this->chargeFraisDeplacementsRetourAction($ficent);
            }
            if ($ficent == "frd_etat_Frais.csv") {
                $message = $this->chargeEtatFraisAction($ficent);
            }
            if ($ficent == "frd_mandatement.csv") {
                $message = $this->chargeMandatementAction($ficent);
            }
        }

        $emFrd = $this->getDoctrine()->getManager('frd');
        $parametre = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => 'REP_REFERENTIEL'));
        $rep_export = $parametre->getLibelle();

        $source = $rep_export . "/" . $ficent;
        $dest = $rep_export . "/Sauvegardes/" . $ficent;
        if (copy($source, $dest)) {
            if (unlink($source)) {
                //$message = $message . " -------> Fichier : " . $ficent . " exporter dans le site extranet";
                null;
            } else {
                // $message = $message . " -------> Fichier : " . $ficent . " exporter dans le site extranet avec succès mais impossible de le supprimer dans le répertoire " . $rep;
                null;
            }
            chmod($dest, 0777);
        } else {
            // $message = $message . " -------> Fichier : " . $ficent . " exporter dans le site extranet avec succès mais impossible de le déplacer dans le répertoire de sauvegarde " . $rep . "/Sauvegardes";
            null;
        }

        $session->set('refMess', $message);



        return $this->redirect($this->generateUrl('AeagFrdBundle_admin_chargeReferentiel'));
    }

    public function chargeUsersAction($ficent = null, $repertoire = 'REP_REFERENTIEL') {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeUsers');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $factory = $this->get('security.encoder_factory');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $parametre = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => $repertoire));
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;
        $fichierAdministrateur = $rep . "/init_administrateur.csv";
        $fichierUser = $rep . "/init_user.csv";

        $message = null;

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            $emFrd = $this->getDoctrine()->getManager('frd');
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, '$');
            //echo "Traitement en cours (attendez la fin du chargement)";
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, '$');
                if (!(is_null($tab[0]))) {
                    $nomPrenom = explode(' ', $this->wd_remove_accents($tab[1]));

                    if (count($nomPrenom) == 4) {
                        $nomPrenom[0] = $nomPrenom[0] . ' ' . $nomPrenom[1] . ' ' . $nomPrenom[2];
                        $nomPrenom[1] = $nomPrenom[3];
                    } elseif (count($nomPrenom) == 3) {
                        $nomPrenom[0] = $nomPrenom[0] . ' ' . $nomPrenom[1];
                        $nomPrenom[1] = $nomPrenom[2];
                    } else {
                        $nomPrenom[0] = str_replace('\'', ' ', $nomPrenom[0]);
                    }

                    $correspondant = $repoCorrespondant->getCorrespondantByCorId($tab[0]);
                    if (!$correspondant) {

                        $correspondant = new Correspondant();
                        $correspondant->setCorId($tab[0]);
                        $correspondant->setIdentifiant($tab[2]);
                        $correspondant->setAdr1($this->wd_remove_accents($tab[5]));
                        $correspondant->setAdr2($this->wd_remove_accents($tab[6]));
                        $correspondant->setAdr3($this->wd_remove_accents($tab[7]));
                        $correspondant->setAdr4($this->wd_remove_accents($tab[8]));
                        $correspondant->setCp($tab[9]);
                        $correspondant->setVille($this->wd_remove_accents($tab[10]));
                        $correspondant->setTel($tab[11]);
                        $correspondant->setEmail($tab[14]);
                        $em->persist($correspondant);
                        $em->flush();
                        $correspondant = $repoCorrespondant->getCorrespondantByCorId($tab[0]);
                    }

                    //print_r('correspondant : ' . $tab[0] . ' postgres : ' . $correspondant->getId());
                    $user = $repoUsers->getUserByCorrespondantUnique($correspondant->getId());
                    if (!$user) {
                        $entity = new User();
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $user;
                        $modif = $modif + 1;
                    }
                    $encoder = $factory->getEncoder($entity);
                    $entity->setCorrespondant($correspondant->getId());
                    $entity->setUsername($nomPrenom[0]);
                    if (count($nomPrenom) > 1) {
                        $entity->setPrenom($nomPrenom[1]);
                    }
                    $entity->setSalt('');
                    $password = $encoder->encodePassword($tab[3], $entity->getSalt());
                    $entity->setpassword($password);
                    $entity->setPlainPassword($entity->getPassword());
                    if ($correspondant) {
                        $entity->setEmail($correspondant->getIdentifiant() . '@a-renseigner-merci.svp');
                    } else {
                        $entity->setEmail($this->wd_remove_accents($tab[2]) . '@a-renseigner-merci.svp');
                    }
                    if ($tab[4] == 'G') {
                        if (!$entity->hasrole('ROLE_ADMINFRD')) {
                            $entity->addRole('ROLE_ADMINFRD');
                        }
                    } else {
                        if (!$entity->hasrole('ROLE_FRD')) {
                            $entity->addRole('ROLE_FRD');
                        }
                    }
                    if (!$entity->hasrole('ROLEAEAG')) {
                        $entity->addRole('ROLE_AEAG');
                    }
                    $entity->setEnabled(true);


                    if (file_exists($fichierAdministrateur)) {
                        $ficAd = fopen($fichierAdministrateur, "r");
                        $tabAd = fgetcsv($ficAd, 1024, '$');
                        if ($ficAd) {
                            //echo "Traitement en cours (attendez la fin du chargement)";
                            while (!feof($ficAd)) {
                                $tabAd = fgetcsv($ficAd, 1024, '$');
                                if (!(is_null($tabAd[2]))) {
                                    $adr1 = strtoupper($tabAd[2]) . ' ' . strtoupper($tabAd[1]);
                                    if ($correspondant->getAdr1() == $adr1 and $correspondant->getCp() == $tabAd[7]) {
                                        $entity->setUsername($this->wd_remove_accents($tabAd[2]));
                                        $entity->setPrenom($this->wd_remove_accents($tabAd[1]));
                                        $entity->setTel($tabAd[9]);
                                        if ($tabAd[11]) {
                                            $entity->setEmail($this->wd_remove_accents($tabAd[11]));
                                        }
                                    }
                                };
                            }
                        };
                        fclose($ficAd);
                    };

                    if (file_exists($fichierUser)) {
                        $ficUs = fopen($fichierUser, "r");
                        $tabUs = fgetcsv($ficUs, 1024, '$');
                        if ($ficUs) {
                            //echo "Traitement en cours (attendez la fin du chargement)";
                            while (!feof($ficUs)) {
                                $tabUs = fgetcsv($ficUs, 1024, '$');

                                if (!(is_null($tabUs[0]))) {
                                    $correspondantUs = $repoCorrespondant->getCorrespondantByCorId($tabUs[0]);
                                    if ($correspondantUs) {
                                        if ($correspondantUs->getId() == $correspondant->getId()) {
                                            $entity->setUsername($this->wd_remove_accents($tabUs[3]));
                                            $entity->setPrenom($this->wd_remove_accents($tabUs[2]));
                                            $entity->setSalt('');
                                            $password = $encoder->encodePassword($this->wd_remove_accents($tabUs[4]), $entity->getSalt());
                                            $entity->setpassword($password);
                                            $entity->setPlainPassword($entity->getPassword());
                                        }
                                    }
                                };
                            }
                            fclose($ficUs);
                        };
                    }

                    $em->persist($entity);
                }
            }

            $em->flush();
            $message = $ajout . " utilisateurs créés et " . $modif . " utilisateurs mis à jour";
        } else {
            $message = "Fichier inexistant : " . $ficent;
        }

        return $message;

        //return $this->redirect($this->generateUrl('AeagFrdBundle_chargeFichier', array('message' => $session->get('refMess'), 'passage' => '1')));
    }

    public function chargeFinaliteAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeFinalite');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $parametre = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => 'REP_REFERENTIEL'));
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $repoFinalite = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:Finalite');

        $message = null;

        //return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, '$');
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, '$');

                if (!(is_null($tab[0]))) {

                    $Finalite = $repoFinalite->getFinaliteByCode($tab[0]);

                    //return new Response("ouv amont: " .  $tab[0] . " ouv aval : " .  $tab[3] . " ouvrage : " . $ouvrage->getId());

                    if (!$Finalite) {
                        $entity = new Finalite();
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $Finalite;
                        $modif = $modif + 1;
                    }

                    $entity->setCode($tab[0]);
                    $entity->setLibelle($this->wd_remove_accents($tab[1]));
                    $emFrd->persist($entity);
                };
            };

            $emFrd->flush();
            $message = $ajout . " Finalités créés et " . $modif . " Finalités mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        return $message;
    }

    public function chargeSousThemeAction($ficent = null, $repertoire = 'REP_REFERENTIEL') {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeSousTheme');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $parametre = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => $repertoire));
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;
        //return new Response("fichier: " . $fichier->getFichier() );

        $repoFinalite = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:Finalite');
        $repoSousTheme = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:SousTheme');

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            $emFrd = $this->getDoctrine()->getManager('frd');
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 2048, '$');
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 2048, '$');

                if (!(is_null($tab[0]))) {

                    $finalite = $repoFinalite->getFinaliteByCode($tab[2]);

                    if (is_object($finalite)) {
                        // print_r($dossier->getId() . ' ' . $tab[3] . ' ' . $tab[4] . ' ' . $tab[5] . ' ' . date("d/m/Y",$this->dateFR2Time($tab[5])) . "\n");
                        $sousTheme = $repoSousTheme->getSousThemeByCode($tab[0]);
                        if (!$sousTheme) {
                            $entity = new SousTheme();
                            $ajout = $ajout + 1;
                        } else {
                            $entity = $sousTheme;
                            $modif = $modif + 1;
                        }
                        $entity->setFinalite($finalite);
                        $entity->setCode($tab[0]);
                        $entity->setLibelle($this->wd_remove_accents($tab[1]));
                        $emFrd->persist($entity);
                    };
                };
            };

            $emFrd->flush();
            $message = $ajout . " Sous-themes créés et " . $modif . " Sous-themes mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        return $message;
    }

    public function chargeTypeMissionAction($ficent = null, $repertoire = 'REP_REFERENTIEL') {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeTypeMission');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $parametre = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => $repertoire));
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;
        //return new Response("fichier: " . $fichier->getFichier() );

        $repoTypeMission = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:TypeMission');

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            $emFrd = $this->getDoctrine()->getManager('frd');
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 2048, '$');
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 2048, '$');

                if (!(is_null($tab[0]))) {

                    $typeMission = $repoTypeMission->getTypeMissionByCode($tab[0]);

                    if (!$typeMission) {
                        $entity = new TypeMission();
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $typeMission;
                        $modif = $modif + 1;
                    }
                    $entity->setCode($tab[0]);
                    $entity->setLibelle($this->wd_remove_accents($tab[1]));
                    $emFrd->persist($entity);
                };
            };

            $emFrd->flush();
            $message = $ajout . " Types mission créés et " . $modif . " Types mission mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        return $message;
    }

    public function chargeDepartementsAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeDepartement');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $parametre = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => 'REP_REFERENTIEL'));
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $repoRegion = $em->getRepository('AeagAeagBundle:Region');

        $message = null;

        //return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, '$');
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, '$');

                if (!(is_null($tab[0]))) {

                    $departement = $repoDepartement->getDepartementByDept($tab[0]);

                    if (!$departement) {
                        $entity = new Departement();
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $departement;
                        $modif = $modif + 1;
                    }

                    $region = $repoRegion->getRegionByReg($tab[2]);

                    $entity->setDept($tab[0]);
                    $entity->setLibelle($this->wd_remove_accents($tab[1]));
                    $entity->setRegion($region);
                    $em->persist($entity);
                };
            };

            $em->flush();
            $message = $ajout . " Départements créés et " . $modif . " Départements mis à jour";
        } else {
            $message = "Fichier inexistant";
        }

        return $message;
    }

    public function chargeFraisDeplacementsAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeFraisDeplacement');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $parametre = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => 'REP_REFERENTIEL'));
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoTypeMission = $emFrd->getRepository('AeagFrdBundle:TypeMission');
        $repoFinalite = $emFrd->getRepository('AeagFrdBundle:Finalite');
        $repoSousTheme = $emFrd->getRepository('AeagFrdBundle:SousTheme');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $repoPhase = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:Phase');

        $message = null;

        //return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, '$');
            //echo "Traitement en cours (attendez la fin du chargement)";
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, '$');
                if (count($tab) > 1) {
                    if (!(is_null($tab[0]))) {
                        $Correspondant = $repoCorrespondant->getCorrespondantByCorId($tab[1]);
                        if ($Correspondant) {
                            $user = $repoUsers->getUserByCorrespondantUnique($Correspondant->getId());
                            if ($user) {

                                $dat1 = explode("/", $tab[58]);
                                if (iconv_strlen($dat1[0]) < 2) {
                                    if (iconv_strlen($dat1[0]) == 1) {
                                        $dat1[0] = '0' . $dat1[0];
                                    } else {
                                        $dat1[0] = '01';
                                    }
                                }
                                if (iconv_strlen($dat1[1]) < 2) {
                                    if (iconv_strlen($dat1[1]) == 1) {
                                        $dat1[1] = '0' . $dat1[1];
                                    } else {
                                        $dat1[1] = '01';
                                    }
                                }
                                if (iconv_strlen($dat1[2]) < 4) {
                                    if (iconv_strlen($dat1[3]) == 1) {
                                        $dat1[2] = '1' . $dat1[2];
                                    } elseif (iconv_strlen($dat1[3]) == 2) {
                                        $dat1[2] = '20';
                                    } else {
                                        $dat1[2] = '201';
                                    }
                                }
                                $dat2 = $dat1[0] . '-' . $dat1[1] . '-' . $dat1[2];
                                $tab[58] = new \DateTime($dat2);

                                $dat1 = explode("/", $tab[3]);
                                if (iconv_strlen($dat1[0]) < 2) {
                                    if (iconv_strlen($dat1[0]) == 1) {
                                        $dat1[0] = '0' . $dat1[0];
                                    } else {
                                        $dat1[0] = '01';
                                    }
                                }
                                if (iconv_strlen($dat1[1]) < 2) {
                                    if (iconv_strlen($dat1[1]) == 1) {
                                        $dat1[1] = '0' . $dat1[1];
                                    } else {
                                        $dat1[1] = '01';
                                    }
                                }
                                if (iconv_strlen($dat1[2]) < 4) {
                                    if (iconv_strlen($dat1[3]) == 1) {
                                        $dat1[2] = '1' . $dat1[2];
                                    } elseif (iconv_strlen($dat1[3]) == 2) {
                                        $dat1[2] = '20';
                                    } else {
                                        $dat1[2] = '201';
                                    }
                                }
                                if ($dat1[2] < 2013) {
                                    $refuser = 'O';
                                } else {
                                    $refuser = 'N';
                                }
                                $dat2 = $dat1[0] . '-' . $dat1[1] . '-' . $dat1[2];
                                $tab[3] = new \DateTime($dat2);

                                $heur1 = explode(":", $tab[4]);
                                if (iconv_strlen($heur1[0]) < 2) {
                                    if (iconv_strlen($heur1[0]) == 1) {
                                        $heur1[0] = '0' . $heur1[0];
                                    } else {
                                        $heur1[0] = '00';
                                    }
                                }
                                if (iconv_strlen($heur1[1]) < 2) {
                                    if (iconv_strlen($heur1[1]) == 1) {
                                        $heur1[1] = '0' . $heur1[1];
                                    } else {
                                        $heur1[1] = '00';
                                    }
                                }
                                $tab[4] = $heur1[0] . ':' . $heur1[1];

                                $dat1 = explode("/", $tab[5]);
                                if (iconv_strlen($dat1[0]) < 2) {
                                    if (iconv_strlen($dat1[0]) == 1) {
                                        $dat1[0] = '0' . $dat1[0];
                                    } else {
                                        $dat1[0] = '01';
                                    }
                                }
                                if (iconv_strlen($dat1[1]) < 2) {
                                    if (iconv_strlen($dat1[1]) == 1) {
                                        $dat1[1] = '0' . $dat1[1];
                                    } else {
                                        $dat1[1] = '01';
                                    }
                                }
                                if (iconv_strlen($dat1[2]) < 4) {
                                    if (iconv_strlen($dat1[3]) == 1) {
                                        $dat1[2] = '1' . $dat1[2];
                                    } elseif (iconv_strlen($dat1[3]) == 2) {
                                        $dat1[2] = '20';
                                    } else {
                                        $dat1[2] = '201';
                                    }
                                }
                                $dat2 = $dat1[0] . '-' . $dat1[1] . '-' . $dat1[2];
                                $tab[5] = new \DateTime($dat2);

                                $heur1 = explode(":", $tab[6]);
                                if (iconv_strlen($heur1[0]) < 2) {
                                    if (iconv_strlen($heur1[0]) == 1) {
                                        $heur1[0] = '0' . $heur1[0];
                                    } else {
                                        $heur1[0] = '00';
                                    }
                                }
                                if (iconv_strlen($heur1[1]) < 2) {
                                    if (iconv_strlen($heur1[1]) == 1) {
                                        $heur1[1] = '0' . $heur1[1];
                                    } else {
                                        $heur1[1] = '00';
                                    }
                                }
                                $tab[6] = $heur1[0] . ':' . $heur1[1];

                                $fraisDeplacements = $repoFraisDeplacement->getFraisDeplacementByUserDate($user->getId(), $tab[3], $tab[4], $tab[5], $tab[6]);
                                $fraistrouver = 0;
                                foreach ($fraisDeplacements as $fraisDepl) {
                                    if ($fraisDepl->getObjet() == $this->wd_remove_accents($tab[2])) {
                                        $fraisDeplacement = $fraisDepl;
                                        $fraistrouver = 1;
                                        break;
                                        //print_r('<pre>');
                                        // print_r('trouve : ' . $tab[0] .  '    ' . $fraisDepl->getObjet() );
                                        //print_r('</pre>');
                                    }
                                }

                                if ($fraistrouver == 0) {
                                    $entity = new FraisDeplacement();
                                    $ajout = $ajout + 1;
                                } else {
                                    $entity = $fraisDeplacement;
                                    $modif = $modif + 1;
                                }

                                $entity->setUser($user->getId());
                                if ($tab[64] == 1) {
                                    $entity->setValider('O');
                                    $entity->setExporter('O');
                                } else {
                                    $entity->setValider('N');
                                    $entity->setExporter('N');
                                }
                                if ($tab[57] == 'CREER') {
                                    $phaseCode = '10';
                                } elseif ($tab[57] == 'TRAITER') {
                                    $phaseCode = '60';
                                } elseif ($tab[57] == 'VALIDER') {
                                    $phaseCode = '40';
                                } else {
                                    $phaseCode = '10';
                                }
                                $phase = $repoPhase->getPhaseByCode($phaseCode);
                                $entity->setPhase($phase);
                                $entity->setDatePhase($tab[58]);
                                $entity->setObjet($this->wd_remove_accents($tab[2]));
                                $entity->setDateDepart($tab[3]);
                                $entity->setHeureDepart($tab[4]);
                                $entity->setDateRetour($tab[5]);
                                $entity->setHeureRetour($tab[6]);
                                $typeMission = $repoTypeMission->getTypeMissionByCode($tab[7]);
                                $entity->setTypeMission($typeMission);
                                $finalite = $repoFinalite->getFinaliteByCode($tab[8]);
                                $entity->setFinalite($finalite);
                                $sousTheme = $repoSousTheme->getSousThemeByCode($tab[9]);
                                $entity->setSousTheme($sousTheme);
                                $entity->setItineraire($this->wd_remove_accents($tab[10]));
                                //var_dump($tab);
                                $departement = $repoDepartement->getDepartementByDept($tab[11]);
                                $entity->setDepartement($departement->getDept());
                                $entity->setKmVoiture(intval($tab[13]));
                                $entity->setKmMoto(intval($tab[14]));
                                if ($tab[15] == 'O') {
                                    $entity->setAeroport('O');
                                } else {
                                    $entity->setAeroport('N');
                                }
                                if ($tab[16] != '') {
                                    $entity->setAdmiMidiSem(intval($tab[16]));
                                }
                                if ($tab[17] != '') {
                                    $entity->setAdmiMidiWeek(intval($tab[17]));
                                }
                                if ($tab[18] != '') {
                                    $entity->setAdmiSoir(intval($tab[18]));
                                }
                                if ($tab[19] != '') {
                                    $entity->setAutreMidiSem(intval($tab[19]));
                                }
                                if ($tab[20] != '') {
                                    $entity->setAutreMidiWeek(intval($tab[20]));
                                }
                                if ($tab[21] != '') {
                                    $entity->setAutreSoir(intval($tab[21]));
                                }
                                if ($tab[22] != '') {
                                    $entity->setOffertMidiSem(intval($tab[22]));
                                }
                                if ($tab[23] != '') {
                                    $entity->setOffertMidiWeek(intval($tab[23]));
                                }
                                if ($tab[24] != '') {
                                    $entity->setOffertSoir(intval($tab[24]));
                                }
                                if ($tab[25] != '') {
                                    $entity->setProvenceJustif(intval($tab[25]));
                                }
                                if ($tab[26] != '') {
                                    $entity->setProvenceNonJustif(intval($tab[26]));
                                }
                                if ($tab[27] != '') {
                                    $entity->setParisJustif(intval($tab[27]));
                                }
                                if ($tab[28] != '') {
                                    $entity->setParisNonJustif(intval($tab[28]));
                                }
                                if ($tab[29] != '') {
                                    $entity->setOffertNuit(intval($tab[29]));
                                }
                                if ($tab[30] == 'O') {
                                    $entity->setAdminNuit('O');
                                } else {
                                    $entity->setAdminNuit('N');
                                }
                                if ($tab[31] != '') {
                                    $entity->setParkJustif(intval($tab[31]));
                                }
                                if ($tab[32] != '') {
                                    $entity->setParkNonJustif(intval($tab[32]));
                                }
                                if ($tab[33] != '') {
                                    $entity->setParkTotal(floatval($tab[33]));
                                }
                                if ($tab[34] != '') {
                                    $entity->setPeageJustif(intval($tab[34]));
                                }
                                if ($tab[35] != '') {
                                    $entity->setPeageNonJustif(intval($tab[35]));
                                }
                                if ($tab[36] != '') {
                                    $entity->setPeageTotal(floatval($tab[36]));
                                }
                                if ($tab[37] != '') {
                                    $entity->setBusMetroJustif(intval($tab[37]));
                                }
                                if ($tab[38] != '') {
                                    $entity->setBusMetroNonJustif(intval($tab[38]));
                                }
                                if ($tab[39] != '') {
                                    $entity->setBusMetroTotal(floatval($tab[39]));
                                }
                                if ($tab[40] != '') {
                                    $entity->setOrlyvalJustif(intval($tab[40]));
                                }
                                if ($tab[41] != '') {
                                    $entity->setOrlyvalNonJustif(intval($tab[41]));
                                }
                                if ($tab[42] != '') {
                                    $entity->setOrlyvalTotal(floatval($tab[42]));
                                }
                                if ($tab[43] != '') {
                                    $entity->setTrainJustif(intval($tab[43]));
                                }
                                if ($tab[44] != '') {
                                    $entity->setTrainNonJustif(intval($tab[44]));
                                }
                                if ($tab[45] != '') {
                                    $entity->setTrainTotal(floatval($tab[45]));
                                }
                                if ($tab[46] != '') {
                                    $entity->setTrainClasse(intval($tab[46]));
                                }
                                if ($tab[47] == 'O') {
                                    $entity->setTrainCouchette('O');
                                } else {
                                    $entity->setTrainCouchette('N');
                                }
                                if ($tab[48] != '') {
                                    $entity->setAvionJustif(intval($tab[48]));
                                }
                                if ($tab[49] != '') {
                                    $entity->setAvionNonJustif(intval($tab[49]));
                                }
                                if ($tab[50] != '') {
                                    $entity->setAvionTotal(floatval($tab[50]));
                                }
                                if ($tab[51] != '') {
                                    $entity->setReservationJustif(intval($tab[51]));
                                }
                                if ($tab[52] != '') {
                                    $entity->setReservationNonJustif(intval($tab[52]));
                                }
                                if ($tab[53] != '') {
                                    $entity->setReservationTotal(floatval($tab[53]));
                                }
                                if ($tab[54] != '') {
                                    $entity->setTaxiJustif(intval($tab[54]));
                                }
                                if ($tab[55] != '') {
                                    $entity->setTaxiNonJustif(intval($tab[55]));
                                }
                                if ($tab[56] != '') {
                                    $entity->setTaxiTotal(floatval($tab[56]));
                                }
                                if ($tab[59] != '') {
                                    $entity->setExercice(intval($tab[59]));
                                }
                                if ($tab[60] != '') {
                                    $entity->setNumMandat(intval($tab[60]));
                                }
                                if ($tab[61] != '') {
                                    $entity->setNumBordereau(intval($tab[61]));
                                }
                                if ($tab[62] != '') {
                                    $dat1 = explode("/", $tab[62]);
                                    if (iconv_strlen($dat1[0]) < 2) {
                                        if (iconv_strlen($dat1[0]) == 1) {
                                            $dat1[0] = '0' . $dat1[0];
                                        } else {
                                            $dat1[0] = '01';
                                        }
                                    }
                                    if (iconv_strlen($dat1[1]) < 2) {
                                        if (iconv_strlen($dat1[1]) == 1) {
                                            $dat1[1] = '0' . $dat1[1];
                                        } else {
                                            $dat1[1] = '01';
                                        }
                                    }
                                    if (iconv_strlen($dat1[2]) < 4) {
                                        if (iconv_strlen($dat1[3]) == 1) {
                                            $dat1[2] = '1' . $dat1[2];
                                        } elseif (iconv_strlen($dat1[3]) == 2) {
                                            $dat1[2] = '20';
                                        } else {
                                            $dat1[2] = '201';
                                        }
                                    }
                                    $dat2 = $dat1[0] . '-' . $dat1[1] . '-' . $dat1[2];
                                    $tab[62] = new \DateTime($dat2);
                                    $entity->setDatePaiement($tab[62]);
                                }
                                if ($tab[63] != '') {
                                    $entity->setMontRemtb(floatval($tab[63]));
                                }
                                if ($refuser == 'N') {
                                    $emFrd->persist($entity);
                                } else {
                                    if ($fraistrouver == 0) {
                                        $ajout = $ajout - 1;
                                    } else {
                                        $modif = $modif - 1;
                                    }
                                }
                            };
                        };
                    };
                }
            };

            $emFrd->flush();
            $message = $ajout . " Frais de déplacement créés et " . $modif . " Frais de déplacement mis à jour";
        } else {
            $message = "Fichier inexistant";
        }

        return $message;
    }

    public function chargeFraisDeplacementsRetourAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeFraisDeplacementsRetour');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $parametre = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => 'REP_REFERENTIEL'));
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoTypeMission = $emFrd->getRepository('AeagFrdBundle:TypeMission');
        $repoFinalite = $emFrd->getRepository('AeagFrdBundle:Finalite');
        $repoSousTheme = $emFrd->getRepository('AeagFrdBundle:SousTheme');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $repoPhase = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:Phase');

        $message = null;

        //return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, '$');
            //echo "Traitement en cours (attendez la fin du chargement)";
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, '$');

                if (count($tab) > 1) {
                    if (!(is_null($tab[0]))) {

                        $Correspondant = $repoCorrespondant->getCorrespondantByCorId($tab[0]);

                        if ($Correspondant) {

//                            print_r('<pre>');
//                            print_r('corid : ' . $tab[0] . ' date depart   ' . $tab[10] . ' ' . $tab[11] . ' ' . $tab[12] . ' ' . $tab[13] . ' ' . $tab[7]);
//                            print_r('</pre>');

                            $user = $repoUsers->getUserByCorrespondantUnique($Correspondant->getId());
                            if ($user) {
                                $dat1 = explode("/", $tab[10]);
                                if (iconv_strlen($dat1[0]) < 2) {
                                    if (iconv_strlen($dat1[0]) == 1) {
                                        $dat1[0] = '0' . $dat1[0];
                                    } else {
                                        $dat1[0] = '01';
                                    }
                                }
                                if (iconv_strlen($dat1[1]) < 2) {
                                    if (iconv_strlen($dat1[1]) == 1) {
                                        $dat1[1] = '0' . $dat1[1];
                                    } else {
                                        $dat1[1] = '01';
                                    }
                                }
                                if (iconv_strlen($dat1[2]) < 4) {
                                    if (iconv_strlen($dat1[3]) == 1) {
                                        $dat1[2] = '1' . $dat1[2];
                                    } elseif (iconv_strlen($dat1[3]) == 2) {
                                        $dat1[2] = '20';
                                    } else {
                                        $dat1[2] = '201';
                                    }
                                }
                                $dat2 = $dat1[0] . '-' . $dat1[1] . '-' . $dat1[2];

                                if ($dat1[2] < 2013) {
                                    $refuser = 'O';
                                } else {
                                    $refuser = 'N';
                                }

                                $tab[10] = new \DateTime($dat2);

                                $heur1 = explode(":", $tab[11]);
                                if (iconv_strlen($heur1[0]) < 2) {
                                    if (iconv_strlen($heur1[0]) == 1) {
                                        $heur1[0] = '0' . $heur1[0];
                                    } else {
                                        $heur1[0] = '00';
                                    }
                                }
                                if (iconv_strlen($heur1[1]) < 2) {
                                    if (iconv_strlen($heur1[1]) == 1) {
                                        $heur1[1] = '0' . $heur1[1];
                                    } else {
                                        $heur1[1] = '00';
                                    }
                                }
                                $tab[11] = $heur1[0] . ':' . $heur1[1];


                                $dat1 = explode("/", $tab[12]);
                                if (iconv_strlen($dat1[0]) < 2) {
                                    if (iconv_strlen($dat1[0]) == 1) {
                                        $dat1[0] = '0' . $dat1[0];
                                    } else {
                                        $dat1[0] = '01';
                                    }
                                }
                                if (iconv_strlen($dat1[1]) < 2) {
                                    if (iconv_strlen($dat1[1]) == 1) {
                                        $dat1[1] = '0' . $dat1[1];
                                    } else {
                                        $dat1[1] = '01';
                                    }
                                }
                                if (iconv_strlen($dat1[2]) < 4) {
                                    if (iconv_strlen($dat1[3]) == 1) {
                                        $dat1[2] = '1' . $dat1[2];
                                    } elseif (iconv_strlen($dat1[3]) == 2) {
                                        $dat1[2] = '20';
                                    } else {
                                        $dat1[2] = '201';
                                    }
                                }
                                $dat2 = $dat1[0] . '-' . $dat1[1] . '-' . $dat1[2];
                                $tab[12] = new \DateTime($dat2);

                                $heur1 = explode(":", $tab[13]);
                                if (iconv_strlen($heur1[0]) < 2) {
                                    if (iconv_strlen($heur1[0]) == 1) {
                                        $heur1[0] = '0' . $heur1[0];
                                    } else {
                                        $heur1[0] = '00';
                                    }
                                }
                                if (iconv_strlen($heur1[1]) < 2) {
                                    if (iconv_strlen($heur1[1]) == 1) {
                                        $heur1[1] = '0' . $heur1[1];
                                    } else {
                                        $heur1[1] = '00';
                                    }
                                }
                                $tab[13] = $heur1[0] . ':' . $heur1[1];

                                $fraisDeplacements = $repoFraisDeplacement->getFraisDeplacementByUserDateDepart($user->getId(), $tab[10], $tab[11]);
                                $fraistrouver = 0;
                                foreach ($fraisDeplacements as $fraisDepl) {
                                    $fraistrouver++;
                                    $entity = $fraisDepl;
                                    $modif = $modif + 1;
                                    $entity->setValider('O');
                                    $entity->setExporter('O');
                                    //$entity->setObjet($this->wd_remove_accents($tab[9]));
//                                    $entity->setDateDepart($tab[10]);
//                                    $entity->setHeureDepart($tab[11]);
//                                    $entity->setDateRetour($tab[12]);
//                                    $entity->setHeureRetour($tab[13]);
                                    //$typeMission = $repoTypeMission->getTypeMissionByCode($tab[14]);
                                    //$entity->setTypeMission($typeMission);

                                    if ($tab[4] != '') {
                                        $entity->setExercice(intval($tab[4]));
                                    }
                                    if ($tab[5] != '') {
                                        $entity->setNumMandat(intval($tab[5]));
                                    }
                                    if ($tab[6] != '') {
                                        $entity->setNumBordereau(intval($tab[6]));
                                    }


                                    if ($tab[7] != '') {
                                        if ($fraistrouver == 1) {
                                            $dat1 = explode("/", $tab[7]);
                                            if (iconv_strlen($dat1[0]) < 2) {
                                                if (iconv_strlen($dat1[0]) == 1) {
                                                    $dat1[0] = '0' . $dat1[0];
                                                } else {
                                                    $dat1[0] = '01';
                                                }
                                            }
                                            if (iconv_strlen($dat1[1]) < 2) {
                                                if (iconv_strlen($dat1[1]) == 1) {
                                                    $dat1[1] = '0' . $dat1[1];
                                                } else {
                                                    $dat1[1] = '01';
                                                }
                                            }
                                            if (iconv_strlen($dat1[2]) < 4) {
                                                if (iconv_strlen($dat1[3]) == 1) {
                                                    $dat1[2] = '1' . $dat1[2];
                                                } elseif (iconv_strlen($dat1[3]) == 2) {
                                                    $dat1[2] = '20';
                                                } else {
                                                    $dat1[2] = '201';
                                                }
                                            }
                                            $dat2 = $dat1[0] . '-' . $dat1[1] . '-' . $dat1[2];
                                            $tab[7] = new \DateTime($dat2);
                                        }
                                        $entity->setDatePaiement($tab[7]);
                                    }
                                    if ($tab[8] != '') {
                                        $entity->setMontRemtb(floatval($tab[8]));
                                    }

                                    if ($tab[3] == '60') {
                                        $phaseCode = '60';
                                    } else {
                                        $phaseCode = '40';
                                    }
                                    $phase = $repoPhase->getPhaseByCode($phaseCode);
                                    $entity->setPhase($phase);
                                    $now = date('Y-m-d');
                                    $now = new \DateTime($now);
                                    $entity->setDatePhase($now);
                                    $entity->setEtfrId($tab[16]);

                                    if ($refuser == 'N' and $fraistrouver == 1) {
                                        $emFrd->persist($entity);
                                    } else {
                                        $modif = $modif - 1;
                                    }
                                }
                            };
                        };
                    };
                }
            };

            $emFrd->flush();
            $message = $ajout . " Frais de déplacement créés et " . $modif . " Frais de déplacement mis à jour";
        } else {
            $message = "Fichier inexistant";
        }

        return $message;
    }

    public function chargeEtatFraisAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeFraisDeplacementsRetour');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $parametre = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => 'REP_REFERENTIEL'));
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $repoEtatFrais = $emFrd->getRepository('AeagFrdBundle:EtatFrais');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoUsers = $em->getRepository('AeagUserBundle:User');

        $message = null;

        //return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, '$');
            //echo "Traitement en cours (attendez la fin du chargement)";
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, '$');

                if (count($tab) > 1) {
                    if (!(is_null($tab[5]))) {

                        $Correspondant = $repoCorrespondant->getCorrespondantByCorId($tab[5]);

                        if ($Correspondant) {
                            $user = $repoUsers->getUserByCorrespondantUnique($Correspondant->getId());
                            if ($user) {

                                $etatFrais = $repoEtatFrais->getEtatFraisById($tab[0]);

                                if (!$etatFrais) {
                                    $entity = new EtatFrais();
                                    $ajout = $ajout + 1;
                                } else {
                                    $entity = $etatFrais;
                                    $modif = $modif + 1;
                                }

                                $entity->setId($tab[0]);
                                $entity->setAnnee($tab[1]);
                                $entity->setNum($tab[2]);
                                $entity->setPhase($tab[3]);
                                if (strlen($tab[4]) > 0) {
                                    $entity->setVersion($tab[4]);
                                };
                                $entity->setCorId($tab[5]);
                                if (strlen($tab[6]) > 0) {
                                    $entity->setDombanqId($tab[6]);
                                }
                                if (strlen($tab[7]) > 0) {
                                    $entity->setService($this->wd_remove_accents($tab[7]));
                                }
                                $entity->setFonction($this->wd_remove_accents($tab[8]));
                                if (strlen($tab[9]) > 0) {
                                    $entity->setTypeContrat($this->wd_remove_accents($tab[9]));
                                }
                                if (strlen($tab[10]) > 0) {
                                    $entity->setIndiceCateg($this->wd_remove_accents($tab[10]));
                                }
                                $entity->setResidFamil($this->wd_remove_accents($tab[11]));
                                $entity->setResidAdmin($this->wd_remove_accents($tab[12]));
                                $entity->setMajoration($tab[13]);
                                if (strlen($tab[14]) > 0) {
                                    $entity->setReducSncf($tab[14]);
                                }
                                if (strlen($tab[15]) > 0) {
                                    $entity->setCvVp($tab[15]);
                                }
                                if (strlen($tab[16]) > 0) {
                                    $entity->setKmAn($tab[16]);
                                }
                                if (strlen($tab[17]) > 0) {
                                    $entity->setKmEtfr($tab[17]);
                                }
                                if (strlen($tab[18]) > 0) {
                                    $texte = $this->wd_remove_accents(str_replace('&&&', CHR(10), $tab[18]));
                                    $texte = str_replace('<br />', '', $texte);
                                    $entity->setObsGen($texte);
                                }
                                if (strlen($tab[19]) > 0) {
                                    $texte = $this->wd_remove_accents(str_replace('&&&', CHR(10), $tab[19]));
                                    $texte = str_replace('<br />', '', $texte);
                                    $entity->setObsSup($texte);
                                }
                                if (strlen($tab[20]) > 0) {
                                    $entity->setMntRemb($tab[20]);
                                }
                                if (strlen($tab[21]) > 0) {
                                    $entity->setMntRegul($tab[21]);
                                }
                                if (strlen($tab[22]) > 0) {
                                    $entity->setMntARegul($tab[22]);
                                }
                                if (strlen($tab[23]) > 0) {
                                    $entity->setRegulVisee($tab[23]);
                                }
                                if (strlen($tab[24]) > 0) {
                                    $entity->setRegulEtfrId($tab[24]);
                                }
                                if (strlen($tab[25]) > 0) {
                                    $entity->setTrAdeduire($tab[25]);
                                }
                                if ($tab[26] != '') {
//                            echo('26 : ' . $tab[26] . '<br/>');
                                    $dat1 = explode("/", $tab[26]);
                                    if (iconv_strlen($dat1[0]) < 2) {
                                        if (iconv_strlen($dat1[0]) == 1) {
                                            $dat1[0] = '0' . $dat1[0];
                                        } else {
                                            $dat1[0] = '01';
                                        }
                                    }
                                    if (iconv_strlen($dat1[1]) < 2) {
                                        if (iconv_strlen($dat1[1]) == 1) {
                                            $dat1[1] = '0' . $dat1[1];
                                        } else {
                                            $dat1[1] = '01';
                                        }
                                    }
                                    if (iconv_strlen($dat1[2]) < 4) {
                                        if (iconv_strlen($dat1[3]) == 1) {
                                            $dat1[2] = '1' . $dat1[2];
                                        } elseif (iconv_strlen($dat1[3]) == 2) {
                                            $dat1[2] = '20';
                                        } else {
                                            $dat1[2] = '201';
                                        }
                                    }
                                    $dat2 = $dat1[0] . '-' . $dat1[1] . '-' . $dat1[2];
                                    $tab[26] = new \DateTime($dat2);
                                    $entity->setTrDateArret($tab[26]);
                                }

                                $entity->setTypeEtatFrais($tab[27]);
                                $emFrd->persist($entity);
                            };
                        }
                    }
                }
            };

            $emFrd->flush();
            $message = $ajout . " Frais de déplacement créés et " . $modif . " Frais de déplacement mis à jour";
        } else {
            $message = "Fichier inexistant";
        }

        return $message;
    }

    public function chargeMandatementAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeMandatement');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $parametre = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => 'REP_REFERENTIEL'));
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $repoEtatFrais = $emFrd->getRepository('AeagFrdBundle:EtatFrais');
        $repoMandatement = $emFrd->getRepository('AeagFrdBundle:Mandatement');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoUsers = $em->getRepository('AeagUserBundle:User');

        $message = null;

        //return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, '$');
            //echo "Traitement en cours (attendez la fin du chargement)";
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, '$');

                if (count($tab) > 1) {

                    $etatFrais = $repoEtatFrais->getEtatFraisById($tab[0]);

                    if ($etatFrais) {

//                            print_r('<pre>');
//                            print_r( $tab[0] . '   ' . $tab[1] . ' ' . $tab[2] . ' ' . $tab[3] . ' ' . $tab[4] . ' ' . $tab[5]);
//                            print_r('</pre>');

                        $mandatement = $repoMandatement->getMandatementByEtfrId($tab[0]);

                        if (!$mandatement) {
                            $entity = new Mandatement();
                            $ajout = $ajout + 1;
                        } else {
                            $entity = $mandatement;
                            $modif = $modif + 1;
                        }

                        $entity->setEtfrId($tab[0]);


                        if ($tab[1] != '') {
                            $entity->setAdrcorId(intval($tab[1]));
                        }
                        if ($tab[2] != '') {
                            $entity->setNumOrdreOpbudg(intval($tab[2]));
                        }
                        if ($tab[3] != '') {
                            $entity->setExercice(intval($tab[3]));
                        }
                        if ($tab[4] != '') {
                            $entity->setNumMandat(intval($tab[4]));
                        }
                        if ($tab[5] != '') {
                            $entity->setNumBordereau(intval($tab[5]));
                        }
                        if ($tab[6] != '') {
                            $entity->setEtatMandat($tab[6]);
                        }
                        if ($tab[7] != '') {
                            $dat1 = explode("/", $tab[7]);
                            if (iconv_strlen($dat1[0]) < 2) {
                                if (iconv_strlen($dat1[0]) == 1) {
                                    $dat1[0] = '0' . $dat1[0];
                                } else {
                                    $dat1[0] = '01';
                                }
                            }
                            if (iconv_strlen($dat1[1]) < 2) {
                                if (iconv_strlen($dat1[1]) == 1) {
                                    $dat1[1] = '0' . $dat1[1];
                                } else {
                                    $dat1[1] = '01';
                                }
                            }
                            if (iconv_strlen($dat1[2]) < 4) {
                                if (iconv_strlen($dat1[3]) == 1) {
                                    $dat1[2] = '1' . $dat1[2];
                                } elseif (iconv_strlen($dat1[3]) == 2) {
                                    $dat1[2] = '20';
                                } else {
                                    $dat1[2] = '201';
                                }
                            }
                            $dat2 = $dat1[0] . '-' . $dat1[1] . '-' . $dat1[2];
                            $tab[7] = new \DateTime($dat2);
                            $entity->setDateEtatFrais($tab[7]);
                        }

                        if ($tab[8] != '') {
                            $dat1 = explode("/", $tab[8]);
                            if (iconv_strlen($dat1[0]) < 2) {
                                if (iconv_strlen($dat1[0]) == 1) {
                                    $dat1[0] = '0' . $dat1[0];
                                } else {
                                    $dat1[0] = '01';
                                }
                            }
                            if (iconv_strlen($dat1[1]) < 2) {
                                if (iconv_strlen($dat1[1]) == 1) {
                                    $dat1[1] = '0' . $dat1[1];
                                } else {
                                    $dat1[1] = '01';
                                }
                            }
                            if (iconv_strlen($dat1[2]) < 4) {
                                if (iconv_strlen($dat1[3]) == 1) {
                                    $dat1[2] = '1' . $dat1[2];
                                } elseif (iconv_strlen($dat1[3]) == 2) {
                                    $dat1[2] = '20';
                                } else {
                                    $dat1[2] = '201';
                                }
                            }
                            $dat2 = $dat1[0] . '-' . $dat1[1] . '-' . $dat1[2];
                            $tab[8] = new \DateTime($dat2);
                            $entity->setDateMandatement($tab[8]);
                        }

                        if ($tab[9] != '') {
                            $dat1 = explode("/", $tab[9]);
                            if (iconv_strlen($dat1[0]) < 2) {
                                if (iconv_strlen($dat1[0]) == 1) {
                                    $dat1[0] = '0' . $dat1[0];
                                } else {
                                    $dat1[0] = '01';
                                }
                            }
                            if (iconv_strlen($dat1[1]) < 2) {
                                if (iconv_strlen($dat1[1]) == 1) {
                                    $dat1[1] = '0' . $dat1[1];
                                } else {
                                    $dat1[1] = '01';
                                }
                            }
                            if (iconv_strlen($dat1[2]) < 4) {
                                if (iconv_strlen($dat1[3]) == 1) {
                                    $dat1[2] = '1' . $dat1[2];
                                } elseif (iconv_strlen($dat1[3]) == 2) {
                                    $dat1[2] = '20';
                                } else {
                                    $dat1[2] = '201';
                                }
                            }
                            $dat2 = $dat1[0] . '-' . $dat1[1] . '-' . $dat1[2];
                            $tab[9] = new \DateTime($dat2);
                            $entity->setDatePaiement($tab[9]);
                        }


                        if ($tab[10] != '') {
                            $entity->setTexte1Mandat($this->wd_remove_accents($tab[10]));
                        }
                        if ($tab[11] != '') {
                            $entity->setTexte2Mandat($this->wd_remove_accents($tab[11]));
                        }
                        if ($tab[12] != '') {
                            $entity->setTexte2Mandat($this->wd_remove_accents($tab[12]));
                        }
                        if ($tab[13] != '') {
                            $entity->setTexte3Mandat($this->wd_remove_accents($tab[13]));
                        }
                        if ($tab[14] != '') {
                            $entity->setTexte4Mandat($this->wd_remove_accents($tab[14]));
                        }
                        if ($tab[15] != '') {
                            $entity->setTexte5Mandat($this->wd_remove_accents($tab[15]));
                        }
                        if ($tab[16] != '') {
                            $entity->setTexte6Mandat($this->wd_remove_accents($tab[16]));
                        }
                        $emFrd->persist($entity);
                    };
                }
            };

            $emFrd->flush();
            $message = $ajout . " Mandatements créés et " . $modif . " Mandatements mis à jour";
        } else {
            $message = "Fichier inexistant";
        }

        return $message;
    }

    public function consulterDepartementsAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'consulterDepartements');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoDepartement = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:Departement');

        $entities = $repoDepartement->getDepartements();

        return $this->render('AeagFrdBundle:Referentiel:consulterDepartements.html.twig', array(
                    'entities' => $entities
        ));
    }

    public function consulterFinalitesAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'consulterFinalites');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoFinalite = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:Finalite');

        $entities = $repoFinalite->getFinalites();

        return $this->render('AeagFrdBundle:Referentiel:consulterFinalites.html.twig', array(
                    'entities' => $entities
        ));
    }

    public function consulterSousThemesAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'consulterSousThemes');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoSousTheme = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:SousTheme');

        $entities = $repoSousTheme->getSousThemes();

        return $this->render('AeagFrdBundle:Referentiel:consulterSousThemes.html.twig', array(
                    'entities' => $entities
        ));
    }

    public function consulterEtatFraisParAnneeAction($anneeSelect = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'consulterEtatFraisParAnnee');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoEtatFrais = $emFrd->getRepository('AeagFrdBundle:EtatFrais');
        $repoMandatement = $emFrd->getRepository('AeagFrdBundle:Mandatement');
        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoPhase = $emFrd->getRepository('AeagFrdBundle:Phase');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');

        $anneeSel = $anneeSelect;

        $session->set('anneeSelect', $anneeSel);

        $annee1 = $anneeSel . '-01-01';
        $anneeDeb = new \DateTime($annee1);
        $annee1 = $anneeSel . '-12-31';
        $anneeFin = new \DateTime($annee1);

        $phase = $repoPhase->getPhaseByCode('40');
        $nbFraisDeplacementEnCours = $repoFraisDeplacement->getNbFraisDeplacementEnCoursByPhase($phase->getId(), $anneeDeb, $anneeFin);
        $i = 0;
        $entities = array();
        if ($anneeSel == date_format($session->get('annee'), 'Y')) {
            $etatFrais = new EtatFrais();
            $phase = $repoPhase->getPhaseByCode('10');
            $entities[$i]['etatFrais'] = $etatFrais;
            $entities[$i]['correspondant'] = null;
            $entities[$i]['mandatement'] = null;
            $entities[$i]['phase'] = $phase;
            $entities[$i]['nbFraisDeplacements'] = $nbFraisDeplacementEnCours;
            $i++;
        }
        $etatsFrais = $repoEtatFrais->getListeEtatFraisByAnnee($anneeSel);
        foreach ($etatsFrais as $etatFrais) {
            $nbFraisDeplacements = $repoFraisDeplacement->getNbFraisDeplacementByEtfrId($etatFrais->getId());
            if ($nbFraisDeplacements > 0) {
                $entities[$i]['etatFrais'] = $etatFrais;
                $correspondant = $repoCorrespondant->getCorrespondantByCorId($etatFrais->getCorId());
                if ($correspondant) {
                    $entities[$i]['correspondant'] = $correspondant;
                } else {
                    $entities[$i]['correspondant'] = null;
                }
                $mandatement = $repoMandatement->getMandatementByEtfrId($etatFrais->getId());
                if ($mandatement) {
                    $entities[$i]['mandatement'] = $mandatement;
                } else {
                    $entities[$i]['mandatement'] = null;
                }
                if ($etatFrais->getPhase() < '60') {
                    $codePhase = '40';
                } else {
                    $codePhase = $etatFrais->getPhase();
                }
                $phase = $repoPhase->getPhaseByCode($codePhase);
                $entities[$i]['phase'] = $phase;

                $entities[$i]['nbFraisDeplacements'] = $nbFraisDeplacements;
                $i++;
            }
        }
        usort($entities, create_function('$a,$b', 'return $a[\'etatFrais\']->getNum()-$b[\'etatFrais\']->getNum();'));

        $session->set('retour', $this->generateUrl('AeagFrdBundle_admin_consulterEtatFraisParAnnee', array('anneeSelect' => $session->get('anneeSelect'))));

        return $this->render('AeagFrdBundle:EtatFrais:adminConsulterEtatFrais.html.twig', array(
                    'user' => $user,
                    'entities' => $entities,
                    'annee' => $anneeSelect
        ));
    }

    public function consulterFraisDeplacementsParEtatFraisAction($etatFraisId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'consulterFraisDeplacementsParEtatFrais');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoEtatFrais = $emFrd->getRepository('AeagFrdBundle:EtatFrais');
        $repoMandatement = $emFrd->getRepository('AeagFrdBundle:Mandatement');
        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoPhase = $emFrd->getRepository('AeagFrdBundle:Phase');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');

         if ($etatFraisId) {
            $etatFrais = $repoEtatFrais->getEtatFraisById($etatFraisId);
            $correspondant = $repoCorrespondant->getCorrespondantByCorId($etatFrais->getCorId());
            $mandatement = $repoMandatement->getMandatementByEtfrId($etatFrais->getId());
            $fraisDeplacements = $repoFraisDeplacement->getFraisDeplacementByEtfrId($etatFrais->getId());
        } else {
            $etatFrais = null;
            $correspondant = null;
            $mandatement = null;
            $phase = $repoPhase->getPhaseByCode('40');
            $annee1 = $session->get('anneeSelect') . '-01-01';
            $anneeDeb = new \DateTime($annee1);
            $annee1 = $session->get('anneeSelect') . '-12-31';
            $anneeFin = new \DateTime($annee1);
            $fraisDeplacements = $repoFraisDeplacement->getFraisDeplacementEnCoursByPhase( $phase->getId(), $anneeDeb, $anneeFin);
        }

        $i = 0;
        $entities = array();
        foreach ($fraisDeplacements as $fraisDeplacement) {
            // print_r('frais : '. $fraisDeplacement->getid() );
            $entities[$i][0] = $fraisDeplacement;
            $user = $repoUsers->getUserById($fraisDeplacement->getUser());
            $correspondant = $repoCorrespondant->getCorrespondantById($user->getCorrespondant());
            $entities[$i][1] = $user;
            if ($correspondant) {
                $entities[$i][2] = $correspondant;
            } else {
                $entities[$i][2] = null;
            }
            $i++;
        }

        $session->set('retour1', $this->generateUrl('AeagFrdBundle_admin_consulterFraisDeplacementsParEtatFrais', array('etatFraisId' => $etatFraisId)));

        return $this->render('AeagFrdBundle:EtatFrais:adminConsulterFraisDeplacementsParEtatFrais.html.twig', array(
                    'user' => $user,
                    'correspondant' => $correspondant,
                    'etatFrais' => $etatFrais,
                    'mandatement' => $mandatement,
                    'entities' => $entities,
                    'annee' => $session->get('anneeSelect')
        ));
    }

    public function consulterFraisDeplacementsAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'consulterFraisDeplacements');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $annee = $emFrd->getRepository('AeagFrdBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));
        $annee = new \DateTime($annee->getLibelle());
        $annee1 = $annee->format('Y');
        $annee2 = $annee1 - 2 . '-01-01';
        $annee = new \DateTime($annee2);

        $fraisDeplacements = $repoFraisDeplacement->getFraisDeplacementByAnnee($annee);
        $i = 0;
        $entities = array();
        foreach ($fraisDeplacements as $fraisDeplacement) {
            // print_r('frais : '. $fraisDeplacement->getid() );
            $entities[$i][0] = $fraisDeplacement;
            $user = $repoUsers->getUserById($fraisDeplacement->getUser());
            if ($user) {
                if ($user->getCorrespondant()) {
                    $correspondant = $repoCorrespondant->getCorrespondantById($user->getCorrespondant());
                } else {
                    $correspondant = $repoCorrespondant->getCorrespondant($user->getUsername());
                }
                $entities[$i][1] = $user;
                if ($correspondant) {
                    $entities[$i][2] = $correspondant;
                } else {
                    $entities[$i][2] = null;
                }
            } else {
                $entities[$i][1] = null;
                $entities[$i][2] = null;
            }
            $i++;
        }


        return $this->render('AeagFrdBundle:Referentiel:consulterFraisDeplacements.html.twig', array(
                    'entities' => $entities
        ));
    }

    public function consulterFraisDeplacementsParAnneeAction($anneeSelect = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'consulterFraisDeplacementsParAnnee');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');

        if ($anneeSelect == '9999') {
            $anneeSel = $session->get('annee');
        } else {
            $annee = $anneeSelect . '-01-01';
            $anneeSel = new \DateTime($annee);
        }

        $annee1 = $anneeSel->format('Y');
        $anneeDeb1 = $annee1 . '-01-01';
        $anneeFin1 = $annee1 . '-12-31';
        $anneeDeb = new \DateTime($anneeDeb1);
        $anneeFin = new \DateTime($anneeFin1);

        $fraisDeplacements = $repoFraisDeplacement->getFraisDeplacementByAnnee($anneeDeb, $anneeFin);
        $i = 0;
        $entities = array();
        foreach ($fraisDeplacements as $fraisDeplacement) {
            // print_r('frais : '. $fraisDeplacement->getid() );
            $entities[$i][0] = $fraisDeplacement;
            $user = $repoUsers->getUserById($fraisDeplacement->getUser());
            if ($user) {
                if ($user->getCorrespondant()) {
                    $correspondant = $repoCorrespondant->getCorrespondantById($user->getCorrespondant());
                } else {
                    $correspondant = $repoCorrespondant->getCorrespondant($user->getUsername());
                }
                $entities[$i][1] = $user;
                if ($correspondant) {
                    $entities[$i][2] = $correspondant;
                } else {
                    $entities[$i][2] = null;
                }
            } else {
                $entities[$i][1] = null;
                $entities[$i][2] = null;
            }
            $i++;
        }

        return $this->render('AeagFrdBundle:Referentiel:consulterFraisDeplacements.html.twig', array(
                    'entities' => $entities,
                    'annee' => $anneeSelect
        ));
    }

    /**
     * Finds and displays a FraisDeplacement entity.
     *
     * @Route("/{id}", name="AeagFrdBundle_admin_consulterFraisDeplacement")
     * @Method("GET")
     * @Template()
     */
    public function consulterFraisDeplacementAction($id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'consulterFraisDeplacement');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $entity = $repoFraisDeplacement->getFraisDeplacementById($id);
        } else {
            $entity = $repoFraisDeplacement->getFraisDeplacementByIdUser($id, $user->getId());
        }

        if (!$entity) {
            return $this->redirect($this->generateUrl('AeagFrdBundle_membre'));
        }


        return array(
            'entity' => $entity,
        );
    }

    public function consulterPhasesAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'consulterPhases');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoPhase = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:Phase');

        $entities = $repoPhase->getPhases();

        return $this->render('AeagFrdBundle:Referentiel:consulterPhases.html.twig', array(
                    'entities' => $entities
        ));
    }

    public function phaseAction($code = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'phase');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoPhase = $emFrd->getRepository('AeagFrdBundle:Phase');

        $phase = $repoPhase->getPhaseByCode($code);

        $form = $this->createForm(new MajPhaseType(), $phase);


        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $emFrd->persist($phase);
                $emFrd->flush();
                $entities = $repoPhase->getPhases();
                return $this->render('AeagFrdBundle:Referentiel:consulterPhases.html.twig', array(
                            'entities' => $entities
                ));
            }
        }

        return $this->render('AeagFrdBundle:Referentiel:majPhase.html.twig', array(
                    'form' => $form->createView(),
                    'phase' => $phase,
        ));
    }

    public static function wd_remove_accents($str, $charset = 'utf-8') {

        $str = str_replace('\'', ' ', $str);

        $str = utf8_encode($str);


        $str = nl2br(strtr(
                        $str, array(
            '&eacute;' => 'é',
            '&ecirc;' => 'ê',
            '&egrave;' => 'è',
            '&Eagrave;' => 'È',
            '&Eacute;' => 'É',
            '&Ecirc;' => 'Ê',
            '&Agarve;' => 'À',
            '&Aacute;' => 'Á',
            '&Acirc;' => 'Â',
            '&Ccirc;' => 'Ç',
            '&Icirc;' => 'Î',
            '&Iuml;' => 'Ï',
            '&Ocirc;' => 'Ô',
            '&Uagrave;' => 'Ù',
            '&Ucirc;' => 'Û',
            '&agrave;' => 'à',
            '&aacute;' => 'á',
            '&acirc;' => 'â',
            '&ccirc;' => 'ç',
            '&icirc;' => 'î',
            '&ocirc;' => 'ô',
            '&ucirc;' => 'û',
            '&#039' => '\'',
            '&#168' => '\'',
            '&#424' => '\'',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'à' => 'a',
            'â' => 'a',
            'î' => 'i',
            'ô' => 'o',
            'û' => 'u'
                ))
        );


        return $str;
    }

    public static function dateFR2Time($date) {
        list($day, $month, $year) = explode('/', $date);
        $timestamp = mktime(0, 0, 0, $month, $day, $year);
        return $timestamp;
    }

}
