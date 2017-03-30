<?php

namespace Aeag\DecBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Aeag\DecBundle\Entity\Taux;
use Aeag\DecBundle\Entity\Dechet;
use Aeag\DecBundle\Entity\Form\Referentiel\MajDechet;
use Aeag\DecBundle\Form\Referentiel\MajDechetType;
use Aeag\DecBundle\Entity\Filiere;
use Aeag\DecBundle\Entity\Form\Referentiel\MajFiliere;
use Aeag\DecBundle\Form\Referentiel\MajFiliereType;
use Aeag\DecBundle\Entity\FiliereAide;
use Aeag\DecBundle\Entity\DechetFiliere;
use Aeag\DecBundle\Entity\Conditionnement;
use Aeag\DecBundle\Entity\Naf;
use Aeag\DecBundle\Entity\Form\Referentiel\MajNaf;
use Aeag\DecBundle\Form\Referentiel\MajNafType;
use Aeag\AeagBundle\Entity\Region;
use Aeag\AeagBundle\Entity\Departement;
use Aeag\AeagBundle\Entity\Commune;
use Aeag\AeagBundle\Entity\CodePostal;
use Aeag\DecBundle\Entity\Operation;
use Aeag\DecBundle\Entity\OperationNaf;
use Aeag\DecBundle\Entity\OperationCommune;
use Aeag\AeagBundle\Entity\Ouvrage;
use Aeag\DecBundle\Entity\OuvrageFiliere;
use Aeag\AeagBundle\Entity\OuvrageCorrespondant;
use Aeag\DecBundle\Entity\ProducteurNonPlafonne;
use Aeag\DecBundle\Entity\ProducteurTauxSpecial;
use Aeag\DecBundle\Entity\Form\Referentiel\MajProducteurNonPlafonne;
use Aeag\DecBundle\Form\Referentiel\MajProducteurNonPlafonneType;
use Aeag\DecBundle\Entity\CollecteurProducteur;
use Aeag\DecBundle\Form\Collecteur\MajProducteurType;
use Aeag\DecBundle\Entity\Form\Collecteur\MajProducteur;
use Aeag\UserBundle\Entity\User;
use Aeag\AeagBundle\Entity\Correspondant;
use Aeag\DecBundle\Entity\DeclarationCollecteur;
use Aeag\DecBundle\Entity\DeclarationProducteur;
use Aeag\DecBundle\Entity\SousDeclarationCollecteur;
use Aeag\DecBundle\Entity\DeclarationDetail;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Aeag\DecBundle\Controller\CollecteurController;
use Aeag\DecBundle\DependencyInjection\PdfListeDechet;
use Aeag\DecBundle\DependencyInjection\PdfListeDechetAll;
use Aeag\DecBundle\DependencyInjection\PdfListeFiliere;
use Aeag\DecBundle\DependencyInjection\PdfListeFiliereAll;
use Aeag\DecBundle\DependencyInjection\PdfListeNaf;
use Aeag\DecBundle\DependencyInjection\PdfListeNafAll;
use Aeag\DecBundle\DependencyInjection\PdfListeCollecteurs;
use Aeag\DecBundle\DependencyInjection\PdfListeAllProducteurs;
use Aeag\DecBundle\DependencyInjection\PdfListeCentresTransits;
use Aeag\DecBundle\DependencyInjection\PdfListeCentresTraitements;
use Aeag\DecBundle\DependencyInjection\PdfListeProducteurNonPlafonne;
use Aeag\DecBundle\DependencyInjection\PdfListeProducteurNonPlafonneAll;
use Aeag\DecBundle\DependencyInjection\PdfListeProducteurTauxSpecialAll;

class ReferentielController extends Controller {

    /**
     * @Cache(maxage="900000")
     */
    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');
        $session->set('refMess', array());

        return $this->render('AeagDecBundle:Referentiel:index.html.twig');
    }

    public function chargeReferentielAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeReferentiel');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $annee = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));
        $annee = new \DateTime($annee->getLibelle());
        $session->set('annee', $annee);

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');

        $message4 = $this->chargeFiliereAideAction();

        $rep_export = $parametre->getLibelle();
        $fichiers = array();
        $i = 0;
        $dir = opendir($rep_export) || die("Erreur le repertoire $rep_export n\'existe pas");
        while ($fic = readdir($dir)) {
//print_r('file : ' . $fic. "\n");
            if (is_file($fic) || !in_array($fic, array(".", ".."))) {
                if (substr($fic, 0, 7) == 'ext_dec' || substr($fic, 0, 5) == 'init_') {
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


        return $this->render('AeagDecBundle:Referentiel:listeFichiersExport.html.twig', array(
                    'repertoire' => $rep_export,
                    'fichiers' => $fichiers,
                    'message' => $session->get('refMess')
        ));
    }

    public function chargeDeclarationAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeDeclaration');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $annee = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));
        $annee = new \DateTime($annee->getLibelle());
        $session->set('annee', $annee);

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $parametre = $repoParametre->getParametreByCode('REP_EXPORT');

        $rep_export = $parametre->getLibelle();
        $fichiers = array();
        $i = 0;
        $dir = opendir($rep_export) || die("Erreur le repertoire $rep_export n\'existe pas");
        while ($fic = readdir($dir)) {
            if (is_file($fic) || !in_array($fic, array(".", ".."))) {
                if (substr($fic, 0, 10) == 'dec_deccol') {
                    //   print_r('file : ' . $fic. ' --> ' . substr($fic, 0, 10) . "\n");
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


        return $this->render('AeagDecBundle:Referentiel:listeFichiersDeclaration.html.twig', array(
                    'repertoire' => $rep_export,
                    'fichiers' => $fichiers,
                    'message' => $session->get('refMess')
        ));
    }

    public function chargeFichierAction($ficent = null, $message = null, $passage = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeFichier');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');

        $session->set('refMess', array());

        $ficent = $_POST['ficent'];
        $ficori = $ficent;

        if (!$passage) {

            if ($ficent == "init_ref_para.csv") {
                $message = $this->chargeTauxAction($ficent);
            }
            if ($ficent == "init_ref_dec.csv") {
                $message = $this->chargeDechetAction($ficent);
            }
            if ($ficent == "init_ref_filiere.csv") {
                $message = $this->chargeFiliereAction($ficent);
            }
            if ($ficent == "init_ref_dec_filiere.csv") {
                $message = $this->chargeDechetFiliereAction($ficent);
            }
            if ($ficent == "init_ref_conditionnement.csv") {
                $message = $this->chargeConditionnementAction($ficent);
            }
            if ($ficent == "init_ref_naf.csv") {
                $message = $this->chargeNafAction($ficent);
            }
            if ($ficent == "init_ref_reg.csv") {
                $message = $this->chargeRegionAction($ficent);
            }
            if ($ficent == "init_ref_dept.csv") {
                $message = $this->chargeDepartementAction($ficent);
            }
            if ($ficent == "init_ref_insee.csv") {
                $message = $this->chargeCommuneAction($ficent);
            }
            if ($ficent == "init_ref_cp.csv") {
                $message = $this->chargeCodePostalAction($ficent);
            }
            if ($ficent == "init_ref_type_operation.csv") {
                $message = $this->chargeOperationAction($ficent);
            }
            if ($ficent == "init_ref_ouvrages.csv") {
                $message = $this->chargeOuvrageAction($ficent);
            }
            if ($ficent == "init_ref_ouv_filiere.csv") {
                $message = $this->chargeOuvrageFiliereAction($ficent);
            }
            if ($ficent == "init_ref_correspondants.csv") {
                $message = $this->chargeCorrespondantsAction($ficent);
            }
            if ($ficent == "init_ref_ouv_corr.csv") {
                $message = $this->chargeOuvrageCorrespondantAction($ficent);
            }
            if ($ficent == "init_ref_producteurs.csv") {
                $message = $this->chargeProducteurAction($ficent);
            }
            if ($ficent == "init_ref_siret_non_plafonne.csv") {
                $message = $this->chargeProducteurNonPlafonneAction($ficent);
            }
            if ($ficent == "init_ref_prospe.csv") {
                $message = $this->chargeProducteurTauxSpecialAction($ficent);
            }
            if ($ficent == "init_ref_deccol.csv") {
                $message = $this->chargeDeclarationcollecteurAction($ficent);
                $message[1] = null;
            }
            if ($ficent == "init_ref_decprod.csv") {
                $message = $this->chargeDeclarationProducteurAction($ficent);
                $message[1] = null;
            }
            if ($ficent == "init_ref_sousdeccol.csv") {
                $message = $this->chargeSousDeclarationCollecteurAction($ficent);
                $message[1] = null;
            }
            if ($ficent == "init_ref_decdet.csv") {
                $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
                $rep = $parametre->getLibelle();
                $fichier = $rep . "/" . $ficent;
                $tabfich = file($fichier);
                $i = 0;
                $nb = 0;
                for ($j = 0; $j < count($tabfich); $j++) {
                    if ($j == 0) {
                        $entete = $tabfich[$i];
                        $nb++;
                        $sousFichier = $rep . "/init_sous_decdet_" . $nb . ".csv";
                        $sousFic = fopen($sousFichier, "w+");
                        fputs($sousFic, $entete);
                        $i = 0;
                    } else {
                        fputs($sousFic, $tabfich[$j]);
                        $i++;
                        if ($i > 999) {
                            fclose($sousFic);
                            $nb++;
                            $sousFichier = $rep . "/init_sous_decdet_" . $nb . ".csv";
                            $sousFic = fopen($sousFichier, "w");
                            fputs($sousFic, $entete);
                            $i = 0;
                        }
                    }
                }
                fclose($sousFic);
                $nb++;
                $ajout = 0;
                $modif = 0;
                set_time_limit(0);
                for ($j = 1; $j < $nb; $j++) {
                    $fichier = "init_sous_decdet_" . $j . ".csv";
                    //print_r('fichier en cours : ' . $fichier);
                    $message = $this->chargeDeclarationDetailAction($fichier, $ajout, $modif);
                }

                $message4 = $this->chargeFiliereAideAction();

                $paraAnnee = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));
                $annee = $paraAnnee->getLibelle();
                $declarations = $repoDeclarationCollecteur->getDeclarationCollecteursByAnnee($annee);
                foreach ($declarations as $declaration) {
                    $ok = CollecteurController::majStatutDeclarationCollecteursAction($declaration->getId(), $user, $emDec, $session);
                    //  print_r($declaration->getId() . ' statut : ' . $declaration->getStatut()->getCode());
                }
                /* $declarations = $repoDeclarationCollecteur->getDeclarationCollecteursByAnnee($annee - 1);
                  foreach ($declarations as $declaration) {
                  $ok = CollecteurController::majStatutDeclarationCollecteursAction($declaration->getId(), $user, $em, $session);
                  // print_r($declaration->getId() . ' statut : ' . $declaration->getStatut()->getCode());
                  }
                 *
                 */
                $fichier = $rep . "/" . $ficent;
            }

            $parametre = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'REP_REFERENTIEL'));
            $rep_export = $parametre->getLibelle();

            $source = $rep_export . "/" . $ficori;
            $dest = $rep_export . "/Sauvegardes/" . $ficori;
            if (file_exists($dest)) {
                unlink($dest);
            }
            if (copy($source, $dest)) {
                if (unlink($source)) {
//$message = $message . " -------> Fichier : " . $ficent . " exporter dans le site extranet";
                    null;
                } else {
// $message = $message . " -------> Fichier : " . $ficent . " exporter dans le site extranet avec succès mais impossible de le supprimer dans le répertoire " . $rep;
                    null;
                }
                chmod($dest, 0775);
            } else {
// $message = $message . " -------> Fichier : " . $ficent . " exporter dans le site extranet avec succès mais impossible de le déplacer dans le répertoire de sauvegarde " . $rep . "/Sauvegardes";
                null;
            }

            $session->set('refMess', $message);
        }

        return $this->redirect($this->generateUrl('AeagDecBundle_admin_chargeReferentiel'));
    }

    public function chargeFichierDeclarationAction($ficent = null, $message = null, $passage = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeFichierDeclaration');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');

        $session->set('refMess', array());

        $ficent = $_POST['ficent'];

        $tabFicent = explode('_', $ficent);

        $message = $this->chargeDeclarationcollecteurAction($ficent);
        $declaration = $message[1];
//        return new Response($declaration);
        $message[1] = null;
        $fic_decprod = 'dec_decprod_' . $tabFicent[2] . '_' . $tabFicent[3] . '_' . $tabFicent[4];
        $message = $this->chargeDeclarationProducteurAction($fic_decprod);
        $declarationProducteur = $message[1];
        $message[1] = null;
        $fic_sousdeccol = 'dec_sousdeccol_' . $tabFicent[2] . '_' . $tabFicent[3] . '_' . $tabFicent[4];
        ;
        $message = $this->chargeSousDeclarationCollecteurAction($fic_sousdeccol);
        $sousDeclaration = $message[1];
        $message[1] = null;
        $ajout = 0;
        $modif = 0;
        $fic_decdet = 'dec_decdet_' . $tabFicent[2] . '_' . $tabFicent[3] . '_' . $tabFicent[4];
        $message = $this->chargeDeclarationDetailAction($fic_decdet, $ajout, $modif);
//         \Symfony\Component\VarDumper\VarDumper::dump($message);
//        return new Response ('');

        $message4 = $this->chargeFiliereAideAction();

        $ok = CollecteurController::majStatutDeclarationCollecteursAction($declaration->getId(), $user, $emDec, $session);
        //return new Response ('aide disponible : ' . $ok);

        /* $statut = $repoStatut->getStatutByCode('60');
          $totQuantiteReel = 0;
          $totMontReel = 0;
          $totQuantiteRet = 0;
          $totMontRet = 0;
          $totQuantiteAide = 0;
          $totMontAide = 0;
          $montDispo = $declaration->getMontantAp();
          if ($statut->getCode() > $sousDeclaration->getStatut()->getCode()) {
          $statut = $sousDeclaration->getStatut();
          }
          $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsBySousDeclarationCollecteur($sousDeclaration->getid());
          $quantiteReel = 0;
          $montReel = 0;
          $quantiteRet = 0;
          $montRet = 0;
          $quantiteAide = 0;
          $montAide = 0;
          foreach ($declarationDetails as $declarationDetail) {
          $quantiteReel += $declarationDetail->getQuantiteReel();
          $montReel += $declarationDetail->getMontreel();
          $quantiteRet += $declarationDetail->getQuantiteRet();
          $montRet += $declarationDetail->getMontret();
          $quantiteAide += $declarationDetail->getQuantiteAide();
          $montAide += $declarationDetail->getMontAide();
          }
          $sousDeclaration->setQuantiteRet($quantiteRet);
          $sousDeclaration->setMontRet($montRet);
          $sousDeclaration->setQuantiteReel($quantiteReel);
          $sousDeclaration->setMontReel($montReel);
          $sousDeclaration->setQuantiteAide($quantiteAide);
          $sousDeclaration->setMontAide($montAide);
          $sousDeclaration->setMontantAp($declaration->getMontantAp());
          $sousDeclaration->setMontantApDispo($montDispo - $montAide);
          $emDec->persist($sousDeclaration);
          $montDispo -= $montAide;
          $totQuantiteReel += $quantiteReel;
          $totMontReel += $montReel;
          $totQuantiteRet += $quantiteRet;
          $totMontRet += $montRet;
          $totQuantiteAide += $quantiteAide;
          $totMontAide += $montAide;
          $declaration->setQuantiteRet($totQuantiteRet);
          $declaration->setMontRet($totMontRet);
          $declaration->setQuantiteReel($totQuantiteReel);
          $declaration->setMontReel($totMontReel);
          $declaration->setQuantiteAide($totQuantiteAide);
          $declaration->setMontAide($totMontAide);
          $declaration->setMontantApDispo($declaration->getMontantAp() - $totMontAide);
          $declaration->setStatut($statut);
          $emDec->persist($declaration);
          //$ok = CollecteurController::majCompteursAction($declaration->getId(), $user, $em, $session);

          $emDec->flush();
         */

        $parametre = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'REP_EXPORT'));
        $rep_export = $parametre->getLibelle();

        $source = $rep_export . "/" . $ficent;
        $dest = $rep_export . "/Sauvegardes/" . $ficent;
        if (copy($source, $dest)) {
            unlink($source);
            chmod($dest, 0775);
        }

        $source = $rep_export . "/" . $fic_decprod;
        $dest = $rep_export . "/Sauvegardes/" . $fic_decprod;
        if (copy($source, $dest)) {
            unlink($source);
            chmod($dest, 0775);
        }

        $source = $rep_export . "/" . $fic_sousdeccol;
        $dest = $rep_export . "/Sauvegardes/" . $fic_sousdeccol;
        if (copy($source, $dest)) {
            unlink($source);
            chmod($dest, 0775);
        }

        $source = $rep_export . "/" . $fic_decdet;
        $dest = $rep_export . "/Sauvegardes/" . $fic_decdet;
        if (copy($source, $dest)) {
            unlink($source);
            chmod($dest, 0775);
        }

        $messages = array(' les déclarations ont été mises à jour.', null, null, null);
        $session->set('refMess', $messages);


        return $this->redirect($this->generateUrl('AeagDecBundle_admin_chargeDeclaration'));
    }

    public function chargeTauxAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeTaux');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoTaux = $emDec->getRepository('AeagDecBundle:Taux');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');

                if (!(is_null($tab[0]))) {

                    $Taux = $repoTaux->getTauxByAnneeCode($tab[0], $tab[1]);

//return new Response("code: " .  $tab[0] . " annee : " .  $session->get('annee')->format('Y'));

                    if (!$Taux) {
                        $entity = new Taux();
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $Taux;
                        $modif = $modif + 1;
                    }

                    $entity->setAnnee($tab[0]);
                    $entity->setCode($tab[1]);
                    $entity->setLibelle($this->wd_remove_accents($tab[3]));
                    if ($tab[2] == "REEL") {
                        $entity->setValeur($tab[4]);
                        $emDec->persist($entity);
                    }
                };
            };

            $emDec->flush();
            $message = $ajout . " Taux créés et " . $modif . " Taux mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function listeTauxAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeTaux');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoTaux = $emDec->getRepository('AeagDecBundle:Taux');

        $entities = $repoTaux->getTaux();

        return $this->render('AeagDecBundle:Referentiel:listeTaux.html.twig', array(
                    'entities' => $entities
        ));
    }

    public function chargeDechetAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeDechet');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoDechet = $emDec->getRepository('AeagDecBundle:Dechet');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {
                if (!(is_null($tab[0]))) {

                    $Dechet = $repoDechet->getDechetByCode($tab[0]);

                    if (!$Dechet) {
                        $entity = new Dechet();
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $Dechet;
                        $modif = $modif + 1;
                    }
                    //print_r($tab[0] . '\n');
                    $entity->setCode($tab[0]);
                    $entity->setUnite($tab[1]);
                    $entity->setLibelle($this->wd_remove_accents($tab[2]));
                    if (!$Dechet) {
                        $entity->setAidable($tab[3]);
                    }
                    $entity->setValide($tab[4]);
                    $emDec->persist($entity);
                };
            };

            $emDec->flush();
            $message = $ajout . " Codes dêchets créés et " . $modif . " Codes dêchets mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function listeDechetAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeDechet');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoDechet = $emDec->getRepository('AeagDecBundle:Dechet');
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINDEC')) {
            $entities = $repoDechet->getDechets();
        } else {
            $entities = $repoDechet->getDechetsAidables('O');
        }
        return $this->render('AeagDecBundle:Referentiel:listeDechet.html.twig', array(
                    'entities' => $entities
        ));
    }

    /**
     *  Fichier PDF
     */
    public function pdfListeDechetAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'pdfListeDechet');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoDechet = $emDec->getRepository('AeagDecBundle:Dechet');
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINDEC')) {
            $entities = $repoDechet->getDechets();
            $pdf = new PdfListeDechetAll('P', 'mm', 'A4');
        } else {
            $entities = $repoDechet->getDechetsAidables('O');
            $pdf = new PdfListeDechet('P', 'mm', 'A4');
        }
        $titre = 'Liste des codes déchets';
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup();
        $pdf->AddPage($entities);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($entities);
        $fichier = 'DEC_CODES_DECHET.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagDecBundle:Collecteur:pdf.html.twig');
    }

    public function ajouterDechetAction(Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'ajouterDechet');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $majDechet = new MajDechet();
        $form = $this->createForm(new MajDechetType(), $majDechet);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $dechet = new Dechet();
                $dechet->setCode($majDechet->getCode());
                $dechet->setLibelle($majDechet->getLibelle());
                $dechet->setAidable($majDechet->getAidable());
                $dechet->setValide('O');
                $dechet->setUnite('tonne');
                $emDec->persist($dechet);
                $emDec->flush();
                return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeDechet'));
            }
        }

        return $this->render('AeagDecBundle:Referentiel:ajouterDechet.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function editerDechetAction($code = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'editerDechet');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoDechet = $emDec->getRepository('AeagDecBundle:Dechet');
        $dechet = $repoDechet->getDechetByCode($code);
        $majDechet = clone($dechet);
        $form = $this->createForm(new MajDechetType(), $majDechet);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $dechet->setCode($majDechet->getCode());
                $dechet->setLibelle($majDechet->getLibelle());
                $dechet->setAidable($majDechet->getAidable());
                $emDec->persist($dechet);
                $emDec->flush();
                return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeDechet'));
            }
        }

        return $this->render('AeagDecBundle:Referentiel:editerDechet.html.twig', array(
                    'form' => $form->createView(),
                    'dechet' => $dechet
        ));
    }

    public function chargeFiliereAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeFiliere');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoFiliere = $emDec->getRepository('AeagDecBundle:Filiere');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {
                if (!(is_null($tab[0]))) {

                    $Filiere = $repoFiliere->getFiliereByCode($tab[0]);

                    if (!$Filiere) {
                        $entity = new Filiere();
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $Filiere;
                        $modif = $modif + 1;
                    }

                    $entity->setCode($tab[0]);
                    $entity->setLibelle($this->wd_remove_accents($tab[1]));
                    if (!$Filiere) {
                        $entity->setAidable('O');
                    }
                    $emDec->persist($entity);
                };
            };

            $emDec->flush();
            $message = $ajout . " Filières créés et " . $modif . " Filières mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function listeFiliereAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeFiliere');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoFiliere = $emDec->getRepository('AeagDecBundle:Filiere');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINDEC')) {
            $entities = $repoFiliere->getFilieres();
        } else {
            $entities = $repoFiliere->getFilieresAidables('O');
        }

        return $this->render('AeagDecBundle:Referentiel:listeFiliere.html.twig', array(
                    'entities' => $entities
        ));
    }

    /**
     *  Fichier PDF
     */
    public function pdfListeFiliereAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'pdfListeFiliere');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoFiliere = $emDec->getRepository('AeagDecBundle:Filiere');
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINDEC')) {
            $entities = $repoFiliere->getFilieres();
            $pdf = new PdfListeFiliereAll('P', 'mm', 'A4');
        } else {
            $entities = $repoFiliere->getFilieresAidables('O');
            $pdf = new PdfListeFiliere('P', 'mm', 'A4');
        }
        $titre = 'Liste des filières';
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup();
        $pdf->AddPage($entities);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($entities);
        $fichier = 'DEC_FILIERE.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagDecBundle:Collecteur:pdf.html.twig');
    }

    public function ajouterFiliereAction(Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'ajouterFiliere');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $majFiliere = new majFiliere();
        $form = $this->createForm(new MajFiliereType(), $majFiliere);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $filiere = new Filiere();
                $filiere->setCode($majFiliere->getCode());
                $filiere->setLibelle($majFiliere->getLibelle());
                $filiere->setAidable($majFiliere->getAidable());
                $emDec->persist($filiere);
                $emDec->flush();
                return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeFiliere'));
            }
        }

        return $this->render('AeagDecBundle:Referentiel:ajouterFiliere.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function editerFiliereAction($code = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'editerFiliere');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoFiliere = $emDec->getRepository('AeagDecBundle:Filiere');
        $filiere = $repoFiliere->getFiliereByCode($code);
        $majFiliere = clone($filiere);
        $form = $this->createForm(new MajFiliereType(), $majFiliere);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $filiere->setCode($majFiliere->getCode());
                $filiere->setLibelle($majFiliere->getLibelle());
                $filiere->setAidable($majFiliere->getAidable());
                $emDec->persist($filiere);
                $emDec->flush();
                return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeFiliere'));
            }
        }

        return $this->render('AeagDecBundle:Referentiel:editerFiliere.html.twig', array(
                    'form' => $form->createView(),
                    'filiere' => $filiere
        ));
    }

    public function chargeDechetFiliereAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeDechetFiliere');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoDechet = $emDec->getRepository('AeagDecBundle:Dechet');
        $repoFiliere = $emDec->getRepository('AeagDecBundle:Filiere');
        $repoDechetFiliere = $emDec->getRepository('AeagDecBundle:DechetFiliere');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {
                if (!(is_null($tab[0]))) {

                    $Dechet = $repoDechet->getDechetByCode($tab[0]);
                    $Filiere = $repoFiliere->getFiliereByCode($tab[1]);
                    $i = 0;
                    if ($Dechet && $Filiere) {

                        $DechetFiliere = $repoDechetFiliere->getDechetFiliereByDechetFiliereAnnee($Dechet->getCode(), $Filiere->getCode(), $tab[2]);

                        if (!$DechetFiliere) {
                            $entity = new DechetFiliere();
                            $ajout = $ajout + 1;
                        } else {
                            $entity = $DechetFiliere;
                            $modif = $modif + 1;
                        }

                        $entity->setDechet($Dechet);
                        $entity->setFiliere($Filiere);
                        $entity->setAnnee($tab[2]);
                        $emDec->persist($entity);
                    }
                };
            };

            $emDec->flush();
            $message = $ajout . " Filières par déchet créées et " . $modif . " Filières par déchet mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function listeDechetFiliereAction($dechet) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeDechetFiliere');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoDechet = $emDec->getRepository('AeagDecBundle:Dechet');
        $repoDechetFiliere = $emDec->getRepository('AeagDecBundle:DechetFiliere');

        $Dechet = $repoDechet->getDechetByCode($dechet);
        $entities = $repoDechetFiliere->getDechetFiliereByDechet($Dechet->getId());

        return $this->render('AeagDecBundle:Referentiel:listeDechetFiliere.html.twig', array(
                    'dechet' => $RefDechet,
                    'entities' => $entities
        ));
    }

    public function chargeConditionnementAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeConditionnement');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoConditionnement = $emDec->getRepository('AeagDecBundle:Conditionnement');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {
                if (!(is_null($tab[0]))) {

                    $Conditionnement = $repoConditionnement->getConditionnementByCode($tab[0]);

                    if (!$Conditionnement) {
                        $entity = new Conditionnement();
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $Conditionnement;
                        $modif = $modif + 1;
                    }

                    $entity->setCode($tab[0]);
                    $entity->setLibelle($this->wd_remove_accents($tab[1]));
                    $emDec->persist($entity);
                };
            };

            $emDec->flush();
            $message = $ajout . " Types de conditionnement créés et " . $modif . " Types de conditionnement mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function listeConditionnementAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeConditionnement');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoConditionnement = $emDec->getRepository('AeagDecBundle:Conditionnement');

        $entities = $repoConditionnement->getConditionnements();

        return $this->render('AeagDecBundle:Referentiel:listeConditionnement.html.twig', array(
                    'entities' => $entities
        ));
    }

    public function chargeNafAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeNaf');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoNaf = $emDec->getRepository('AeagDecBundle:Naf');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {
                if (!(is_null($tab[0]))) {

                    $Naf = $repoNaf->getNafByCode($tab[0]);

                    if (!$Naf) {
                        $entity = new Naf();
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $Naf;
                        $modif = $modif + 1;
                    }

//print_r( 'code : ' . $tab[0] . ' ' . $tab[1]);

                    $entity->setCode($tab[0]);
                    $entity->setLibelle($this->wd_remove_accents($tab[1]));
                    if (!$Naf) {
                        $entity->setAidable('O');
                    }
                    $emDec->persist($entity);
                };
            };

            $emDec->flush();
            $message = $ajout . " Codes NAF créés et " . $modif . " Codes NAF mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function listeNafAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeNaf');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoNaf = $emDec->getRepository('AeagDecBundle:Naf');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINDEC')) {
            $entities = $repoNaf->getNafs();
        } else {
            $entities = $repoNaf->getNafsAidables('O');
        }

        return $this->render('AeagDecBundle:Referentiel:listeNaf.html.twig', array(
                    'entities' => $entities
        ));
    }

    /**
     *  Fichier PDF
     */
    public function pdfListeNafAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'pdfListeNaf');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoNaf = $emDec->getRepository('AeagDecBundle:Naf');
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINDEC')) {
            $entities = $repoNaf->getNafs();
            $pdf = new PdfListeNafAll('P', 'mm', 'A4');
        } else {
            $entities = $repoNaf->getNafsAidables('O');
            $pdf = new PdfListeNaf('P', 'mm', 'A4');
        }
        $titre = 'Liste des codes NAF';
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup();
        $pdf->AddPage($entities);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($entities);
        $fichier = 'DEC_CODES_NAF.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagDecBundle:Collecteur:pdf.html.twig');
    }

    public function ajouterNafAction(Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'ajouterNaf');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $majNaf = new majNaf();
        $form = $this->createForm(new MajNafType(), $majNaf);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $naf = new Naf();
                $naf->setCode($majNaf->getCode());
                $naf->setLibelle($majNaf->getLibelle());
                $naf->setAidable($majNaf->getAidable());
                $emDec->persist($naf);
                $emDec->flush();
                return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeNaf'));
            }
        }

        return $this->render('AeagDecBundle:Referentiel:ajouterNaf.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function editerNafAction($code = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'editerNaf');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoNaf = $emDec->getRepository('AeagDecBundle:Naf');
        $naf = $repoNaf->getNafByCode($code);
        $majNaf = clone($naf);
        $form = $this->createForm(new MajNafType(), $majNaf);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $naf->setCode($majNaf->getCode());
                $naf->setLibelle($majNaf->getLibelle());
                $naf->setAidable($majNaf->getAidable());
                $emDec->persist($naf);
                $emDec->flush();
                return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeNaf'));
            }
        }

        return $this->render('AeagDecBundle:Referentiel:editerNaf.html.twig', array(
                    'form' => $form->createView(),
                    'naf' => $naf
        ));
    }

    public function chargeRegionAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeRegion');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoRegion = $em->getRepository('AeagAeagBundle:Region');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {
                if (!(is_null($tab[0]))) {

                    $region = $repoRegion->getRegionByReg($tab[0]);

                    if (!$region) {
                        $entity = new Region();
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $region;
                        $modif = $modif + 1;
                    }

//print_r( 'code : ' . $tab[0] . ' ' . $tab[1]);

                    $entity->setReg($tab[0]);
                    $entity->setLibelle($this->wd_remove_accents($tab[1]));
                    if (!$region) {
                        $entity->setDec($tab[2]);
                    }
                    $em->persist($entity);
                };
            };

            $em->flush();
            $message = $ajout . " Régions créés et " . $modif . " Régions mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function chargeDepartementAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeDepartement');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoRegion = $em->getRepository('AeagAeagBundle:Region');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {
                if (!(is_null($tab[0]))) {

                    $Departement = $repoDepartement->getDepartementByDept($tab[0]);

                    if (!$Departement) {
                        $entity = new Departement();
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $Departement;
                        $modif = $modif + 1;
                    }

//print_r( 'code : ' . $tab[0] . ' ' . $tab[1]);

                    $entity->setDept($tab[0]);
                    $entity->setLibelle($this->wd_remove_accents($tab[1]));
                    $region = $repoRegion->getRegionByReg($tab[2]);
                    $entity->setRegion($region);
                    if (!$Departement) {
                        $entity->setDec($tab[3]);
                    }
                    $em->persist($entity);
                };
            };

            $em->flush();
            $message = $ajout . " Départements créés et " . $modif . " Départements mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function chargeCommuneAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeCommune');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            $i = 0;
            foreach ($tabTri as $tab) {
                if (!(is_null($tab[0]))) {

                    $insee = $tab[0] . $tab[1];
                    $Commune = $repoCommune->getCommuneByCommune($insee);

                    if (!$Commune) {
                        $entity = new Commune();
                        $ajout = $ajout + 1;


//print_r( 'code : ' . $tab[0] . ' ' . $tab[1]);

                        $Departement = $repoDepartement->getDepartementByDept($tab[0]);
                        $entity->setCommune($insee);
                        $entity->setDepartement($Departement);
                        $entity->setLibelle($this->wd_remove_accents($tab[2]));
                        if (!$Commune) {
                            $entity->setDec($tab[3]);
                        }
                        $em->persist($entity);
                        $i++;
                        if ($i > 1000) {
                            $em->flush();
                            $i = 0;
                        }
                    };
                };
            };

            $em->flush();
            $message = $ajout . " Communes créés et " . $modif . " Communes mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function chargeCodePostalAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeCodePostal');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoCodePostal = $em->getRepository('AeagAeagBundle:CodePostal');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);


            $i = 0;
            foreach ($tabTri as $tab) {
                if (!(is_null($tab[0]))) {
                    $insee = str_pad($tab[0], 2, "0", STR_PAD_LEFT) . str_pad($tab[1], 3, "0", STR_PAD_LEFT);
                    //print_r('commune : ' . $insee);
                    $commune = $repoCommune->getCommuneByCommune($insee);
                    if ($commune) {
                        $codePostal = $repoCodePostal->getCodePostalByCommuneCpAcheminementLocalite($commune->getId(), $tab[2], $this->wd_remove_accents($tab[3]), $this->wd_remove_accents($tab[4]));
                    } else {
                        $codePostal = $repoCodePostal->getCodePostalByCpAcheminementLocalite($tab[2], $this->wd_remove_accents($tab[3]), $this->wd_remove_accents($tab[4]));
                    }
                    if (!$codePostal) {
                        $entity = new CodePostal();
                        $ajout = $ajout + 1;

                        if ($commune) {
                            $entity->setCommune($commune);
                        }
                        $entity->setCp($tab[2]);
                        $entity->setAcheminement($this->wd_remove_accents($tab[3]));
                        $entity->setLocalite($this->wd_remove_accents($tab[4]));
                        $entity->setDec('O');
                        $em->persist($entity);
                        $i++;
                        if ($i > 1000) {
                            $em->flush();
                            $i = 0;
                        }
                    };
                };
            };

            $em->flush();
            $message = $ajout . " Codes postaux créés et " . $modif . " Codes postaux mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function chargeOperationAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeOperation');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoOperation = $emDec->getRepository('AeagDecBundle:Operation');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;
        $message1 = null;
        $message2 = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');

                if (!(is_null($tab[0]))) {

                    $Operation = $repoOperation->getOperationByCode($tab[0]);

                    if (!$Operation) {
                        $entity = new Operation();
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $Operation;
                        $modif = $modif + 1;
                    }

//print_r( 'code : ' . $tab[0] . ' ' . $tab[1]);

                    $entity->setCode($tab[0]);
                    $entity->setLibelle($this->wd_remove_accents($tab[1]));
                    $emDec->persist($entity);
                };
            };

            $emDec->flush();
            $message = $ajout . " Types opérations créés et " . $modif . " Types opérations mises à jour";

            $message1 = $this->chargeOperationNafAction("init_ref_typeop_naf.csv");
            $message2 = $this->chargeOperationCommuneAction("init_ref_typeop_insee.csv");
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, $message1, $message2, null);
        return $messages;
    }

    public function chargeOperationNafAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeOperationNaf');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoOperation = $emDec->getRepository('AeagDecBundle:Operation');
        $repoNaf = $emDec->getRepository('AeagDecBundle:Naf');
        $repoOperationNaf = $emDec->getRepository('AeagDecBundle:OperationNaf');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message1 = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');

                if (!(is_null($tab[0]))) {

                    $Operation = $repoOperation->getOperationByCode($tab[0]);
                    $Naf = $repoNaf->getNafByCode($tab[1]);

                    if ($Operation && $Naf) {

                        $OperationNaf = $repoOperationNaf->getOperationNafByOperationNaf($Operation->getId(), $Naf->getId());

                        if (!$OperationNaf) {
                            $entity = new OperationNaf();
                            $ajout = $ajout + 1;
                        } else {
                            $entity = $OperationNaf;
                            $modif = $modif + 1;
                        }

//print_r( 'code : ' . $tab[0] . ' ' . $tab[1]);

                        $entity->setOperation($Operation);
                        $entity->setNaf($Naf);
                        $emDec->persist($entity);
                    }
                };
            };

            $emDec->flush();
            $message1 = $ajout . " codes NAF par opération créés et " . $modif . " codes NAF par opération mises à jour";
            $source = $rep . "/" . $ficent;
            $dest = $rep . "/Sauvegardes/" . $ficent;
            if (copy($source, $dest)) {
                chmod($dest, 0775);
            }
        } else {
            $message1 = "Fichier inexistant";
        }

        return $message1;
    }

    public function chargeOperationCommuneAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeOperationCommune');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoOperation = $emDec->getRepository('AeagDecBundle:Operation');
        $repoDepartement = $emDec->getRepository('AeagDecBundle:Departement');
        $repoCommune = $emDec->getRepository('AeagDecBundle:Commune');
        $repoOperationCommune = $emDec->getRepository('AeagDecBundle:OperationCommune');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message1 = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');

                if (!(is_null($tab[0]))) {
                    $Operation = $repoOperation->getOperationByCode($tab[0]);
                    if ($Operation) {
                        $Commune = $repoCommune->getCommuneByCommune($tab[1]);
                        if ($Commune) {
                            $OperationCommune = $repoOperationCommune->getOperationCommuneByOperationCommune($Operation->getId(), $Commune->getId());
                            if (!$OperationCommune) {
                                $entity = new OperationCommune();
                                $ajout = $ajout + 1;
                            } else {
                                $entity = $OperationCommune;
                                $modif = $modif + 1;
                            }
//print_r( 'code : ' . $tab[0] . ' ' . $tab[1]);
                            $entity->setOperation($Operation);
                            $entity->setCommune($Commune);
                            $emDec->persist($entity);
                        }
                    };
                };
            };

            $emDec->flush();
            $message1 = $ajout . " Communes par opération créés et " . $modif . " Communes par opération mises à jour";
            $source = $rep . "/" . $ficent;
            $dest = $rep . "/Sauvegardes/" . $ficent;
            if (copy($source, $dest)) {
                chmod($dest, 0775);
            }
        } else {
            $message1 = "Fichier inexistant";
        }

        return $message1;
    }

    public function listeOperationAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeOperation');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOperation = $emDec->getRepository('AeagDecBundle:Operation');

        $entities = $repoOperation->getOperations();

        return $this->render('AeagDecBundle:Referentiel:listeOperation.html.twig', array(
                    'entities' => $entities
        ));
    }

    public function listeOperationNafAction($operation) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeOperationNaf');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOperation = $emDec->getRepository('AeagDecBundle:Operation');
        $repoOperationNaf = $emDec->getRepository('AeagDecBundle:OperationNaf');
        $Operation = $repoOperation->getOperationByCode($operation);
        $entities = $repoOperationNaf->getOperationNafByOperation($Operation->getId());

        return $this->render('AeagDecBundle:Referentiel:listeOperationNaf.html.twig', array(
                    'operation' => $Operation,
                    'entities' => $entities
        ));
    }

    public function listeOperationCommuneAction($operation) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeOperationCommune');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOperation = $emDec->getRepository('AeagDecBundle:Operation');
        $repoOperationCommune = $emDec->getRepository('AeagDecBundle:OperationCommune');
        $Operation = $repoOperation->getOperationByCode($operation);
        $entities = $repoOperationCommune->getOperationCommuneByOperation($Operation->getId());

        return $this->render('AeagDecBundle:Referentiel:listeOperationCommune.html.twig', array(
                    'operation' => $Operation,
                    'entities' => $entities
        ));
    }

    public function chargeOuvrageAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeOuvrage');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {

                if (!(is_null($tab[0]))) {

                    $Ouvrage = $repoOuvrage->getOuvrageByNumeroType($tab[1], $tab[3]);

                    if (!$Ouvrage) {
                        if ($tab[4] != '000000000000000' && $tab[3] != 'ODEC') {
                            $Ouvrages = $repoOuvrage->getOuvragesBySiretType($tab[4], $tab[3]);
                            $Ouvrage = null;
                            if ($Ouvrages) {
                                $Ouvrage = $Ouvrages[0];
                            }
                            if (!$Ouvrage) {
                                $entity = new Ouvrage();
                                $ajout = $ajout + 1;
                            } else {
                                $entity = $Ouvrage;
                                $modif = $modif + 1;
                            }
                        } else {
                            $entity = new Ouvrage();
                            $ajout = $ajout + 1;
                        }
                    } else {
                        $entity = $Ouvrage;
                        $modif = $modif + 1;
                    }

                    $entity->setOuvId($tab[0]);
                    $entity->setNumero($tab[1]);
                    $entity->setLibelle($this->wd_remove_accents($tab[2]));
                    $entity->setType($tab[3]);
                    $entity->setSiret($tab[4]);
                    if ($tab[5] && $tab[6]) {
                        $commune = $repoCommune->getCommuneByCommune($tab[5] . $tab[6]);
                        if ($commune) {
                            $entity->setCommune($commune);
                            $entity->setCp($commune->getCommune());
                            $entity->setVille($commune->getLibelle());
                        }
                    }
                    if (!$Ouvrage) {
                        $entity->setDec('O');
                    }
                    $em->persist($entity);
                };
            };

            $em->flush();
            $message = $ajout . " Ouvrages créés et " . $modif . " Ouvrages mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function chargeOuvrageFiliereAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeOuvrageFiliere');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoFiliere = $emDec->getRepository('AeagDecBundle:Filiere');
        $repoOuvrageFiliere = $emDec->getRepository('AeagDecBundle:OuvrageFiliere');
        $annee = $repoParametre->getParametreByCode('ANNEE');
        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {
                if (!(is_null($tab[0]))) {
                    $Ouvrage = $repoOuvrage->getOuvrageByOuvIdType($tab[0], $tab[1]);
                    $Filiere = $repoFiliere->getFiliereByCode($tab[2]);

                    if ($Ouvrage && $Filiere) {

                        $OuvrageFiliere = $repoOuvrageFiliere->getOuvrageFiliereByOuvrageFiliere($Ouvrage->getId(), $Filiere->getCode(), $tab[3]);

                        if (!$OuvrageFiliere) {
                            $entity = new OuvrageFiliere();
                            $ajout = $ajout + 1;
                        } else {
                            $entity = $OuvrageFiliere;
                            $modif = $modif + 1;
                        }

                        $entity->setOuvrage($Ouvrage->getId());
                        $entity->setFiliere($Filiere);
                        $entity->setAnnee($tab[3]);
                        $entity->setValidite('O');
                        $emDec->persist($entity);
                    }
                };
            };

            $emDec->flush();
            $message = $ajout . " Filières par ouvrages créées et " . $modif . " Filières par ouvragess mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function listeOuvrageFiliereAction($ouvrage) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeOuvrageFiliere');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoOuvrageFiliere = $emDec->getRepository('AeagDecBundle:OuvrageFiliere');

        $Ouvrage = $repoOuvrage->getOuvrageByNumero($ouvrage);
        $entities = $repoOuvrageFiliere->getOuvrageFiliereByOuvrage($Ouvrage->getId());

        return $this->render('AeagDecBundle:Referentiel:listeOuvrageFiliere.html.twig', array(
                    'ouvrage' => $Ouvrage,
                    'entities' => $entities
        ));
    }

    public function chargeCorrespondantsAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeCorrespondant');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;

            $emDec = $this->getDoctrine()->getEntityManager();
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {

                if (!(is_null($tab[0]))) {
                    $correspondant = $repoCorrespondant->getCorrespondant($tab[1]);
                    if (!$correspondant) {
                        $entity = new Correspondant();
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $correspondant;
                        $modif = $modif + 1;
                    }

                    $entity->setCorId($tab[0]);
                    $entity->setIdentifiant($tab[1]);
                    $entity->setAdr1($this->wd_remove_accents($tab[2]));
                    $entity->setAdr2($this->wd_remove_accents($tab[3]));
                    $entity->setAdr3($this->wd_remove_accents($tab[4]));
                    $entity->setAdr4($this->wd_remove_accents($tab[5]));
                    $entity->setCp($tab[6]);
                    $entity->setVille($tab[7]);
                    $entity->setTel($tab[8]);
                    $entity->setEmail($this->wd_remove_accents($tab[9]));
                    $entity->setSiret($tab[10]);

                    $em->persist($entity);
                };
            };
            $em->flush();
            $message = $ajout . " correspondants créés et " . $modif . " correspondants mis à jour";
        } else {
            $message = "Fichier inexistant : " . $ficent;
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function chargeOuvrageCorrespondantAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeOuvrageCorrespondant');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $factory = $this->get('security.encoder_factory');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');
        $repoUsers = $em->getRepository('AeagUserBundle:User');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;
        $messageUser = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            $ajoutUser = 0;
            $modifUser = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $tabUserOri = array();
            $tabUserTri = array();
            $i = 0;
            $j = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {

                if (!(is_null($tab[0]))) {
                    $Ouvrage = $repoOuvrage->getOuvrageByOuvIdType($tab[0], $tab[1]);
                    $Correspondant = $repoCorrespondant->getCorrespondantByCorId($tab[2]);

                    if ($Ouvrage && $Correspondant) {

                        $OuvrageCorrespondant = $repoOuvrageCorrespondant->getOuvrageCorrespondantByOuvrageCorrespondant($Ouvrage->getId(), $Correspondant->getId());

                        if (!$OuvrageCorrespondant) {
                            $entity = new OuvrageCorrespondant();
                            $ajout = $ajout + 1;
                        } else {
                            $entity = $OuvrageCorrespondant;
                            $modif = $modif + 1;
                        }

                        $entity->setOuvrage($Ouvrage);
                        $entity->setCorrespondant($Correspondant);
                        $em->persist($entity);
                        if ($tab[1] == 'ODEC') {
                            $tabUserOri[$j] = $Correspondant->getCorId();
                            $j++;
                        }
                    }
                };
            };

            $tabUserTri = \array_unique($tabUserOri);
            foreach ($tabUserTri as $tab) {
                $Correspondant = $repoCorrespondant->getCorrespondantByCorId($tab);
                $user = $repoUsers->getUserByCorrespondant($Correspondant->getId());

                if (!$user) {
                    $entityUser = new User();
                    $ajoutUser = $ajoutUser + 1;
                    $encoder = $factory->getEncoder($entityUser);
                    if ($Correspondant) {
                        $entityUser->setCorrespondant($Correspondant->getId());
                    }

                    $entityUser->setUsername($Correspondant->getIdentifiant());
                    $entityUser->setSalt('');
                    $password = $encoder->encodePassword($Correspondant->getCorId(), $entityUser->getSalt());
                    $entityUser->setpassword($password);
                    $entityUser->setPlainPassword($entityUser->getPassword());
                    $entityUser->setTel($Correspondant->getTel());
                    $email = $this->wd_remove_accents($Correspondant->getEmail());
                    if ($email) {
                        $entityUser->setEmail($email);
                    } else {
                        $entityUser->setEmail($Correspondant->getIdentifiant() . '@a-renseigner-merci.svp');
                    }
                    $entityUser->addRole('ROLE_AEAG');
                    $entityUser->addRole('ROLE_ODEC');
                    $entityUser->setEnabled(true);
                    $em->persist($entityUser);
                }
            }
            $em->flush();
            $message = $ajout . " ouvrages  par correspondants créées et " . $modif . " ouvrages par correspondants mises à jour";
            $messageUser = $ajoutUser . " comptes créés et " . $modifUser . " comptes mis à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($messageUser, $message, null, null);
        return $messages;
    }

    public function chargeProducteurAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeProducteur');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoCodePostal = $em->getRepository('AeagAeagBundle:CodePostal');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;
        $message1 = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {
                if (!(is_null($tab[0]))) {
                    if ($tab[0] != '000000000000000') {
                        $tab[0] = str_replace("'", "", $tab[0]);
                        $Ouvrages = $repoOuvrage->getOuvragesBySiretType($tab[0], 'PDEC');
                        $Ouvrage = null;
                        if (count($Ouvrages) > 0) {
                            $Ouvrage = $Ouvrages[0];
                        }
                        if (!$Ouvrage) {
                            $entity = new Ouvrage();
                            $ajout = $ajout + 1;
//                        } else {
//                            $entity = $Ouvrage;
//                            $modif = $modif + 1;
//                        }

                            $entity->setLibelle($this->wd_remove_accents($tab[1]));
                            if ($tab[2]) {
                                $entity->setAdresse($this->wd_remove_accents($tab[2]));
                            }

                            $commune = null;
                            if ($tab[5] && $tab[6]) {
                                $codeCommune = $tab[5] . $tab[6];
                                $commune = $repoCommune->getCommuneByCommune($tab[5] . $tab[6]);
                                if ($commune) {
                                    $entity->setCommune($commune);
//                                $entity->setCp($commune->getCommune());
//                                $entity->setVille($commune->getLibelle());
                                }
                            }


                            $entity->setCp(null);
                            $entity->setVille(null);
                            if ($tab[3]) {
                                $entity->setCp($tab[3]);
                                if ($tab[3] == $codeCommune) {
                                    if ($commune) {
                                        $codePostal = $repoCodePostal->getCodePostalByCommune($commune->getId());
                                    } else {
                                        $codePostal = $repoCodePostal->getCodePostalByCp($tab[3]);
                                    }
                                } else {
                                    $codePostal = $repoCodePostal->getCodePostalByCp($tab[3]);
                                }
                                if (count($codePostal) == 1) {
                                    $entity->setCp($codePostal[0]->getCp());
                                    $entity->setVille($codePostal[0]->getAcheminement());
                                } else {
                                    foreach ($codePostal as $cp) {
                                        if ($cp->getAcheminement() == $tab[4]) {
                                            $entity->setCp($codePostal[0]->getCp());
                                            $entity->setVille($cp->getAcheminement());
                                        }
                                    }
                                }
                            }

                            $entity->setType('PDEC');
                            if ($tab[7]) {
                                $entity->setNaf($tab[7]);
                            }
                            $entity->setSiret($tab[0]);
                            $em->persist($entity);
                        }
                    }
                };
            };


            $em->flush();

            $ficent = "init_ref_collecteur_producteurs.csv";

            $message1 = $this->chargeCollecteurProducteurAction($ficent);
            $message = $ajout . " Ouvrages créés et " . $modif . " Ouvrages mises à jour";
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
                chmod($dest, 0775);
            } else {
// $message = $message . " -------> Fichier : " . $ficent . " exporter dans le site extranet avec succès mais impossible de le déplacer dans le répertoire de sauvegarde " . $rep . "/Sauvegardes";
                null;
            }
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, $message1, null, null);
        return $messages;
    }

    public function chargeCollecteurProducteurAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeCollecteurProducteur');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoCollecteurProducteur = $emDec->getRepository('AeagDecBundle:CollecteurProducteur');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajoutCollecteurProducteur = 0;
            $modifCollecteurProducteur = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {
                if (!(is_null($tab[0]))) {

                    $Producteurs = $repoOuvrage->getOuvragesBySiretType($tab[0], 'PDEC');
                    $Producteur = null;
                    if ($Producteurs) {
                        $Producteur = $Producteurs[0];
                    }
                    $Collecteur = $repoOuvrage->getOuvrageByOuvIdType($tab[1], 'ODEC');
                    if ($Producteur && $Collecteur) {
                        $CollecteurProducteur = $repoCollecteurProducteur->getCollecteurProducteurByCollecteurProducteur($Collecteur->getId(), $Producteur->getId());
                        if (!$CollecteurProducteur) {
                            $entityCollecteurProducteur = new CollecteurProducteur();
                            $ajoutCollecteurProducteur = $ajoutCollecteurProducteur + 1;
                        } else {
                            $entityCollecteurProducteur = $CollecteurProducteur;
                            $modifCollecteurProducteur = $modifCollecteurProducteur + 1;
                        }

                        /* $message = "collecteur : " . $Collecteur->getid() . " siret : " . $Collecteur->getSiret();
                          $message1 = "producteur : " . $entity->getid() . " siret : " . $entity->getSiret();
                          $messages = array($message, $message1, null);
                          return $messages; */

                        $entityCollecteurProducteur->setCollecteur($Collecteur->getId());
                        $entityCollecteurProducteur->setProducteur($Producteur->getId());
                        $emDec->persist($entityCollecteurProducteur);
                    }
                };
            };

            $emDec->flush();
            $message = $ajoutCollecteurProducteur . " liens Collecteur-producteur créés et " . $modifCollecteurProducteur . " liens Collecteur-producteur mises à jour";
        } else {
            $message = "Fichier inexistant";
        }
        return $message;
    }

    public function chargeProducteurNonPlafonneAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeProducteurNonPlafonne');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoProducteurNonPlafonne = $emDec->getRepository('AeagDecBundle:ProducteurNonPlafonne');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {

                if (!(is_null($tab[0]))) {

                    $ProducteurNonPlafonne = $repoProducteurNonPlafonne->getProducteurNonPlafonneBySiret($tab[0]);

                    if (!$ProducteurNonPlafonne) {
                        $entity = new ProducteurNonPlafonne();
                        $entity->setAidable('O');
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $ProducteurNonPlafonne;
                        $modif = $modif + 1;
                    }

                    $entity->setSiret($tab[0]);
                    $entity->setLibelle($this->wd_remove_accents($tab[1]));
                    if ($tab[2]) {
                        $entity->setCorId($tab[2]);
                    }
                    $correspondant = $repoCorrespondant->getCorrespondantBySiret($tab[0]);
                    if ($correspondant) {
                        $entity->setCorrespondant($correspondant->getId());
                    }
                    $emDec->persist($entity);
                };
            };

            $emDec->flush();
            $message = $ajout . " Producteur non plafonnés créés et " . $modif . " Producteur non plafonnés mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function listeProducteurNonPlafonneAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeProducteurNonPlafonne');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoProducteurNonPlafonne = $emDec->getRepository('AeagDecBundle:ProducteurNonPlafonne');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');

        $producteurs = $repoProducteurNonPlafonne->getProducteurNonPlafonnes();

        $entities = array();
        $i = 0;
        foreach ($producteurs as $producteur) {
            $entities[$i][0] = $producteur;
            if ($producteur->getCorrespondant()) {
                $correspondant = $repoCorrespondant->getCorrespondantById($producteur->getCorrespondant());
            } else {
                $correspondant = null;
            }
            $entities[$i]['Correspondant'] = $correspondant;
            $i++;
        }

        return $this->render('AeagDecBundle:Referentiel:listeProducteurNonPlafonne.html.twig', array(
                    'entities' => $entities
        ));
    }

    /**
     *  Fichier PDF
     */
    public function pdfListeProducteurNonPlafonneAction($collecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'pdfListeProducteurNonPlafonne');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoProducteurNonPlafonne = $emDec->getRepository('AeagDecBundle:ProducteurNonPlafonne');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');

        $producteurs = $repoProducteurNonPlafonne->getProducteurNonPlafonnes();


        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINDEC')) {
            $producteurs = $repoProducteurNonPlafonne->getProducteurNonPlafonnes();
        } else {
            $producteurs = $repoProducteurNonPlafonne->getProducteurNonPlafonnesAidables('O');
        }

        $entities = array();
        $i = 0;
        foreach ($producteurs as $producteur) {
            $entities[$i][0] = $producteur;
            if ($producteur->getCorrespondant()) {
                $correspondant = $repoCorrespondant->getCorrespondantById($producteur->getCorrespondant());
            } else {
                $correspondant = null;
            }
            $entities[$i]['Correspondant'] = $correspondant;
            $i++;
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINDEC')) {
            $pdf = new PdfListeProducteurNonPlafonneAll('P', 'mm', 'A4');
        } else {
            $pdf = new PdfListeProducteurNonPlafonne('P', 'mm', 'A4');
        }
        $titre = 'Liste des producteurs  non plafonnés';
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup();
        $pdf->AddPage($entities);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($entities);
        $fichier = 'DEC_PRODUCTEURS_NON_PLAFONNES.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagDecBundle:Collecteur:pdf.html.twig');
    }

    public function ajouterProducteurNonPlafonneAction(Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'ajouterProducteurNonPlafonne');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $majProducteurNonPlafonne = new MajProducteurNonPlafonne();
        $form = $this->createForm(new MajProducteurNonPlafonneType(), $majProducteurNonPlafonne);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $producteurNonPlafonne = new ProducteurNonPlafonne();
                $producteurNonPlafonne->setSiret($majProducteurNonPlafonne->getSiret());
                $producteurNonPlafonne->setLibelle($majProducteurNonPlafonne->getLibelle());
                $producteurNonPlafonne->setAidable($majProducteurNonPlafonne->getAidable());
                $emDec->persist($producteurNonPlafonne);
                $emDec->flush();
                return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeProducteurNonPlafonne'));
            }
        }

        return $this->render('AeagDecBundle:Referentiel:ajouterProducteurNonPlafonne.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function editerProducteurNonPlafonneAction($siret = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'editerProducteurNonPlafonne');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoProducteurNonPlafonne = $emDec->getRepository('AeagDecBundle:ProducteurNonPlafonne');
        $producteurNonPlafonne = $repoProducteurNonPlafonne->getProducteurNonPlafonneBySiret($siret);
        $majProducteurNonPlafonne = clone($producteurNonPlafonne);
        $form = $this->createForm(new MajProducteurNonPlafonneType(), $majProducteurNonPlafonne);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $producteurNonPlafonne->setLibelle($majProducteurNonPlafonne->getLibelle());
                $producteurNonPlafonne->setAidable($majProducteurNonPlafonne->getAidable());
                $emDec->persist($producteurNonPlafonne);
                $emDec->flush();
                return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeProducteurNonPlafonne'));
            }
        }

        return $this->render('AeagDecBundle:Referentiel:editerProducteurNonPlafonne.html.twig', array(
                    'form' => $form->createView(),
                    'producteurNonPlafonne' => $producteurNonPlafonne
        ));
    }

    public function chargeProducteurTauxSpecialAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeProducteurTauxSpecial');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoProducteurTauxSpecial = $emDec->getRepository('AeagDecBundle:ProducteurTauxSpecial');

        $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;

//return new Response("fichier: " . $fichier->getFichier() );

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }

            $ProducteurTauxSpecials = $repoProducteurTauxSpecial->getProducteurTauxSpecials();
            foreach ($ProducteurTauxSpecials as $ProducteurTauxSpecial) {
                $emDec->remove($ProducteurTauxSpecial);
            }
            $emDec->flush();

            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {

                if (!(is_null($tab[0]))) {

                    $ProducteurTauxSpecial = $repoProducteurTauxSpecial->getProducteurTauxSpecialBySiret($tab[0]);

                    if (!$ProducteurTauxSpecial) {
                        $entity = new ProducteurTauxSpecial();
                        $ajout = $ajout + 1;
                    } else {
                        $entity = $ProducteurTauxSpecial;
                        $modif = $modif + 1;
                    }

                    $entity->setSiret($tab[0]);
                    $entity->setRaisonSociale($tab[1]);
                    $entity->setLocalisation($tab[2]);
                    $entity->setTaux($tab[3]);
                    $emDec->persist($entity);
                }
            }

            $emDec->flush();
            $message = $ajout . " Producteurs avec un taux spécial créés et " . $modif . " Producteurs avec un taux spécial mises à jour";
        } else {
            $message = "Fichier inexistant";
        }

        $messages = array($message, null, null, null);
        return $messages;
    }

    public function listeProducteurTauxSpecialAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeProducteurTauxSpecial');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoProducteurTauxSpecial = $emDec->getRepository('AeagDecBundle:ProducteurTauxSpecial');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');

        $producteurs = $repoProducteurTauxSpecial->getProducteurTauxSpecials();

        return $this->render('AeagDecBundle:Referentiel:listeProducteurTauxSpecial.html.twig', array(
                    'entities' => $producteurs
        ));
    }

    public function pdfListeProducteurTauxSpecialAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'pdfListeProducteurTauxSpecial');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoProducteurTauxSpecial = $emDec->getRepository('AeagDecBundle:ProducteurTauxSpecial');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');

        $producteurs = $repoProducteurTauxSpecial->getProducteurTauxSpecials();

        $pdf = new PdfListeProducteurTauxSpecialAll('P', 'mm', 'A4');

        $titre = 'Liste des producteurs avec un taux d\'aide spécial';
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup();
        $pdf->AddPage($producteurs);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($producteurs);
        $fichier = 'DEC_PRODUCTEURS_TAUX_SPECIAL.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagDecBundle:Collecteur:pdf.html.twig');
    }

    public function listeCollecteursAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeCollecteurs');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');

        $ouvrages = $repoOuvrage->getOuvragesByType('ODEC');
        $i = 0;

        $entities = array();
        foreach ($ouvrages as $ouvrage) {
            $entities[$i][0] = $ouvrage;
            $ouvragecorrespondants = $repoOuvrageCorrespondant->getOuvrageCorrespondantByOuvrage($ouvrage->getId());
            $j = 0;
            $correspondants = array();
            foreach ($ouvragecorrespondants as $ouvragecorrespondant) {
                if ($ouvragecorrespondant) {
                    $correspondants[$j] = $ouvragecorrespondant->getCorrespondant();
                } else {
                    $correspondants[$j] = new Correspondant();
                }
                $j++;
            }
            $entities[$i][1] = $correspondants;
            $i++;
        }

        $session->set('retour', $this->generateUrl('AeagDecBundle_admin_listeCollecteurs'));

        return $this->render('AeagDecBundle:Referentiel:listeCollecteurs.html.twig', array(
                    'entities' => $entities
        ));
    }

    /*
     *  Fichier PDF
     */

    public function pdfListeCollecteursAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'pdfListeCollecteurs');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');

        $ouvrages = $repoOuvrage->getOuvragesByType('ODEC');
        $i = 0;

        $entities = array();
        foreach ($ouvrages as $ouvrage) {
            $entities[$i][0] = $ouvrage;
            $ouvragecorrespondants = $repoOuvrageCorrespondant->getOuvrageCorrespondantByOuvrage($ouvrage->getId());
            $j = 0;
            $correspondants = array();
            foreach ($ouvragecorrespondants as $ouvragecorrespondant) {
                if ($ouvragecorrespondant) {
                    $correspondants[$j] = $ouvragecorrespondant->getCorrespondant();
                } else {
                    $correspondants[$j] = new Correspondant();
                }
                $j++;
            }
            $entities[$i][1] = $correspondants;
            $i++;
        }

        $pdf = new PdfListeCollecteurs('P', 'mm', 'A4');
        $titre = 'Liste des collecteurs';
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup();
        $pdf->AddPage($entities);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($entities);
        $fichier = 'DEC_COLLECTEURS.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagDecBundle:Collecteur:pdf.html.twig');
    }

    public function listeCentresTransitsAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeCentresTransits');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');

        $entities = $repoOuvrage->getOuvragesByType('CT');

        $session->set('retour', $this->generateUrl('AeagDecBundle_admin_listeCentresTransits'));

        return $this->render('AeagDecBundle:Referentiel:listeCentresTransits.html.twig', array(
                    'entities' => $entities
        ));
    }

    /**
     *  Fichier PDF
     */
    public function pdfListeCentresTransitsAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'pdfListeCentresTransits');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $entities = $repoOuvrage->getOuvragesByType('CT');
        $pdf = new PdfListeCentresTransits('P', 'mm', 'A4');
        $titre = 'Liste des centres de transit';
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup();
        $pdf->AddPage($entities);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($entities);
        $fichier = 'DEC_CENTRES_TRANSIT.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagDecBundle:Collecteur:pdf.html.twig');
    }

    public function listeCentresTraitementsAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeCentresTraitements');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');

        $entities = $repoOuvrage->getOuvragesByType('CTT');

        $session->set('retour', $this->generateUrl('AeagDecBundle_admin_listeCentresTraitements'));

        return $this->render('AeagDecBundle:Referentiel:listeCentresTraitements.html.twig', array(
                    'entities' => $entities
        ));
    }

    /**
     *  Fichier PDF
     */
    public function pdfListeCentresTraitementsAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'pdfListeCentresTraitements');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $entities = $repoOuvrage->getOuvragesByType('CTT');
        $pdf = new PdfListeCentresTraitements('P', 'mm', 'A4');
        $titre = 'Liste des centres de traitement';
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup();
        $pdf->AddPage($entities);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($entities);
        $fichier = 'DEC_CENTRES_TRAITEMENT.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagDecBundle:Collecteur:pdf.html.twig');
    }

    public function listeProducteursAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeProducteurs');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoCollecteurProducteur = $emDec->getRepository('AeagDecBundle:CollecteurProducteur');

        $producteurs = $repoOuvrage->getOuvragesByType('PDEC');

        $prod = array();
        $i = 0;
        foreach ($producteurs as $producteur) {
            // $nbCollecteurs = $repoCollecteurProducteur->getNbCollecteurProducteurByProducteur($producteur->getId());
            $prod[$i][0] = $producteur;
            // $prod[$i][1] = $nbCollecteurs;
            $i++;
        }



        $session->set('retour', $this->generateUrl('AeagDecBundle_admin_listeProducteurs'));

        return $this->render('AeagDecBundle:Referentiel:listeProducteurs.html.twig', array(
                    'producteurs' => $prod
        ));
    }

    /*
     *  Fichier PDF
     */

    public function pdfListeProducteursAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'pdfListeProducteurs');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $entities = $repoOuvrage->getOuvragesByType('PDEC');

        $pdf = new PdfListeAllProducteurs('P', 'mm', 'A4');
        $titre = 'Liste des producteurs';
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup();
        $pdf->AddPage($entities);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($entities);
        $fichier = 'DEC_ALLPRODUCTEURS.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagDecBundle:Collecteur:pdf.html.twig');
    }

    public function majProducteurAction($producteur_id = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'majProducteur');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $user = $this->getUser();
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoCodePostal = $em->getRepository('AeagAeagBundle:CodePostal');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');
        $repoCollecteurProducteur = $emDec->getRepository('AeagDecBundle:CollecteurProducteur');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');
        $producteur = $repoOuvrage->getOuvrageById($producteur_id);

        if (!$producteur) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement : ' . $id);
        }

        $correspondants = $repoOuvrageCorrespondant->getOuvrageCorrespondantByOuvrage($producteur->getId());
        $collecteurProducteurs = $repoCollecteurProducteur->getCollecteurProducteurByProducteur($producteur->getId());
        $odecs = array();
        $i = 0;
        foreach ($collecteurProducteurs as $collecteurProducteur) {
            $odec = $repoOuvrage->getOuvrageById($collecteurProducteur->getCollecteur());
            if ($odec) {
                $odecs[$i] = $odec;
                $i++;
            }
        }
        $ctdts = $repoOuvrage->getOuvrageByNumeroType($producteur->getNumero(), 'CTT');
        $cts = $repoOuvrage->getOuvrageByNumeroType($producteur->getNumero(), 'CT');

        $majProducteur = new MajProducteur();
        $majProducteur->setOuvId($producteur->getOuvId());
        $majProducteur->setNumero($producteur->getNumero());
        $majProducteur->setLibelle($producteur->getLibelle());
        $majProducteur->setAdresse($producteur->getAdresse());
        $majProducteur->setCp($producteur->getCp());
        $majProducteur->setVille($producteur->getVille());
        $majProducteur->setSiret($producteur->getSiret());
        $form = $this->createForm(new MajProducteurType(), $majProducteur);
        $message = null;
        $maj = false;
        $err = false;

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {



                if (strlen($majProducteur->getSiret()) <> 14) {
                    $err = true;
                    $message = $message . "Le siret " . $majProducteur->getSiret() . " doit faire 14 caractères  \n";
                }

                $codePostal = $repoCodePostal->getCodePostalByCp($majProducteur->getCp());
                if (!$codePostal) {
                    $commune = $repoCommune->getCommuneByCommune($majProducteur->getCp());
                    if (!$commune) {
                        $err = true;
                        $message = $message . "Le code postal " . $majProducteur->getCp() . " est inconnu à l'agence de l'eau";
                        $constraint = new True(array(
                            'message' => "Le code postal " . $majProducteur->getCp() . " est inconnu à l'agence de l'eau"
                        ));
                        $erreurCp = $this->get('validator')->validateValue(false, $constraint);
                    } else {
                        if ($commune->getDec() == 'N') {
                            $err = true;
                            $message = $message . "Le code postal " . $majProducteur->getCp() . " n'est pas aidable par l'agence de l'eau";
                            $constraint = new True(array(
                                'message' => "Le code postal " . $majProducteur->getCp() . " n'est pas aidable par l'agence de l'eau"
                            ));
                            $erreurCp = $this->get('validator')->validateValue(false, $constraint);
                        }
                    }
                } else {
                    foreach ($codePostal as $cp) {
                        if ($cp->getDec() == 'N') {
                            $err = true;
                            $message = $message . "Le code postal " . $majProducteur->getCp() . " n'est pas aidable par l'agence de l'eau";
                            $constraint = new True(array(
                                'message' => "Le code postal " . $majProducteur->getCp() . " n'est pas aidable par l'agence de l'eau"
                            ));
                            $erreurCp = $this->get('validator')->validateValue(false, $constraint);
                        }
                    }
                }

                if (!$err) {
                    $producteur->setLibelle($majProducteur->getLibelle());
                    $producteur->setAdresse($majProducteur->getAdresse());
                    $producteur->setCp($majProducteur->getCp());
                    $producteur->setVille($majProducteur->getVille());
                    $producteur->setSiret($majProducteur->getSiret());
                    $em->persist($producteur);
                    $em->flush();
                    $maj = true;
                    return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeProducteurs'));
                }
            }
        }

        return $this->render('AeagDecBundle:Referentiel:majProducteur.html.twig', array(
                    'entity' => $producteur,
                    'correspondants' => $correspondants,
                    'odecs' => $odecs,
                    'ctdts' => $ctdts,
                    'cts' => $cts,
                    'message' => $message,
                    'maj' => $maj,
                    'form' => $form->createView(),
        ));
    }

    public function chargeDossierAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeDossier');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $session->set('refMess', array());
        $message = null;
        $message1 = null;
        $message2 = null;
        $message3 = null;
        $message4 = null;

        $message = $this->chargeDeclarationCollecteurAction($ficent);
        $message1 = $this->chargeSousDeclarationCollecteurAction("init_ref_sousdeccol.csv");
        $message2 = $this->chargeDeclarationProducteurAction("init_ref_decprod.csv");
        $message3 = $this->chargeDeclarationDetailAction("init_ref_decdet.csv");
        $message4 = $this->chargeFiliereAideAction();

        $messages = array($message, $message1, $message2, $message3);
        $session->set('refMess', $messages);

        return $this->redirect($this->generateUrl('AeagDecBundle_admin_chargeReferentiel'));
    }

    public function chargeDeclarationcollecteurAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeDeclarationcollecteur');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        if (substr($ficent, 0, 4) == 'init') {
            $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        } else {
            $parametre = $repoParametre->getParametreByCode('REP_EXPORT');
        }
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message = null;
        $declarationCollecteur = null;

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);

            foreach ($tabTri as $tab) {

                if (!(is_null($tab[0]))) {
                    $collecteur = $repoOuvrage->getOuvrageByOuvIdType($tab[0], 'ODEC');
                    if (!$collecteur) {
                        $ct = $repoOuvrage->getOuvrageByOuvIdType($tab[0], 'CT');
                        if ($ct) {
                            $collecteur = new Ouvrage();
                            $collecteur->setNumero($ct->getNumero());
                            $collecteur->setLibelle($ct->getLibelle());
                            $collecteur->setOuvId($ct->getOuvId());
                            $collecteur->setType('ODEC');
                            $collecteur->setSiret($ct->getSiret());
                            if ($ct->getCommune()) {
                                $collecteur->setCommune($ct->getCommune());
                                $collecteur->setCp($ct->getCp());
                                $collecteur->setVille($ct->getVille());
                            }
                            $collecteur->setAdresse($ct->getAdresse());
                            $em->persist($collecteur);
                            $em->flush();
                        } else {
                            echo ('pb ouvid : ' . $tab[0] . '\n');
                        }
                    }
                    $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurByCollecteurAnnee($collecteur->getId(), $tab[1]);
                    if (!$declarationCollecteur) {
                        $declarationCollecteur = new DeclarationCollecteur();
                        $ajout++;
                    } else {
                        $modif++;
                    }
                    $declarationCollecteur->setCollecteur($collecteur->getId());
                    $declarationCollecteur->setAnnee($tab[1]);
                    if ($tab[2]) {
                        if ($tab[2] == '45') {
                            $tab[2] = '50';
                        }
                        if ($tab[2] == '55') {
                            $tab[2] = '50';
                        }
                        $statut = $repoStatut->getStatutByCode($tab[2]);
                    } else {
                        $statut = $repoStatut->getStatutByCode('20');
                    }
                    $declarationCollecteur->setStatut($statut);
                    $declarationCollecteur->setQuantiteReel($tab[3] * 1000);
                    $declarationCollecteur->setMontReel($tab[4]);
                    $declarationCollecteur->setQuantiteRet($tab[5] * 1000);
                    $declarationCollecteur->setMontRet($tab[6]);
                    $declarationCollecteur->setQuantiteAide($tab[7] * 1000);
                    $declarationCollecteur->setMontAide($tab[8]);
                    $declarationCollecteur->setDossierAide($tab[9]);
                    $declarationCollecteur->setMontantAp($tab[10]);
                    $declarationCollecteur->setMontantApDispo($tab[11]);
                    $emDec->persist($declarationCollecteur);
                }
            };
            $emDec->flush();
            $message = $ajout . " Dossiers par collecteurs créés et " . $modif . " dossiers par collecteur mis à jour";
        } else {
            $message = "Fichier inexistant : " . $fichier;
        }

        $messages = array($message, $declarationCollecteur, null, null);
        return $messages;
    }

    public function chargeSousDeclarationCollecteurAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeSousDeclarationCollecteur');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoDeclarationProducteur = $emDec->getRepository('AeagDecBundle:DeclarationProducteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        if (substr($ficent, 0, 4) == 'init') {
            $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        } else {
            $parametre = $repoParametre->getParametreByCode('REP_EXPORT');
        }
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message1 = 'null';

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {
                if (!(is_null($tab[0]))) {
                    $collecteur = $repoOuvrage->getOuvrageByOuvidType($tab[0], 'ODEC');
                    $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurByCollecteurAnnee($collecteur->getId(), $tab[1]);
                    $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteurNumero($declarationCollecteur->getId(), $tab[2]);
                    if (!$sousDeclarationCollecteur) {
                        $sousDeclarationCollecteur = new SousDeclarationCollecteur();
                        $ajout++;
                    } else {
                        $modif++;
                        if ($tab[3] >= '45') {
                            $declarationdetails = $repoDeclarationDetail->getDeclarationDetailsBySousDeclarationCollecteur($sousDeclarationCollecteur->getId());
                            if ($declarationdetails) {
                                foreach ($declarationdetails as $declarationdetail) {
                                    $declarationProducteur = $repoDeclarationProducteur->getDeclarationProducteurById($declarationdetail->getDeclarationProducteur()->getId());
                                    if ($declarationProducteur) {
                                        $declarationProducteur->setQuantiteReel($declarationProducteur->getQuantiteReel() - $declarationdetail->getQuantiteReel());
                                        $declarationProducteur->setMontReel($declarationProducteur->getMontReel() - $declarationdetail->getMontReel());
                                        $declarationProducteur->setQuantiteRet($declarationProducteur->getQuantiteRet() - $declarationdetail->getQuantiteRet());
                                        $declarationProducteur->setMontRet($declarationProducteur->getMontRet() - $declarationdetail->getMontRet());
                                        $declarationProducteur->setQuantiteAide($declarationProducteur->getQuantiteAide() - $declarationdetail->getQuantiteAide());
                                        $declarationProducteur->setMontAide($declarationProducteur->getMontAide() - $declarationdetail->getMontAide());
                                        $emDec->persist($declarationProducteur);
                                    }
                                    $emDec->remove($declarationdetail);
                                }
                            }
                        }
                    }
                    $sousDeclarationCollecteur->setDeclarationCollecteur($declarationCollecteur);
                    $sousDeclarationCollecteur->setNumero($tab[2]);
                    if ($tab[3]) {
                        if ($tab[3] == '45') {
                            $tab[3] = '50';
                        }
                        if ($tab[3] == '55') {
                            $tab[3] = '50';
                        }
                        $statut = $repoStatut->getStatutByCode($tab[3]);
                    } else {
                        $statut = $repoStatut->getStatutByCode('20');
                    }
                    if ($statut >= '50') {
                        $sousDeclarationCollecteur->setStatut($statut);
                    }
                    if (!($tab[4] == "")) {
                        $date = new \DateTime(substr($tab[4], 0, 2) . '-' . substr($tab[4], 3, 2) . '-' . substr($tab[4], 6, 4));
                        $sousDeclarationCollecteur->setDateDebut($date);
                    }
                    $sousDeclarationCollecteur->setQuantiteReel($tab[5] * 1000);
                    $sousDeclarationCollecteur->setMontReel($tab[6]);
                    $sousDeclarationCollecteur->setQuantiteRet($tab[7] * 1000);
                    $sousDeclarationCollecteur->setMontRet($tab[8]);
                    $sousDeclarationCollecteur->setQuantiteAide($tab[9] * 1000);
                    $sousDeclarationCollecteur->setMontAide($tab[10]);
                    $sousDeclarationCollecteur->setDossierAide($tab[11]);
                    $sousDeclarationCollecteur->setMontantAp($tab[12]);
                    $sousDeclarationCollecteur->setMontantApDispo($tab[13]);
                    $emDec->persist($sousDeclarationCollecteur);
                }
            };
            $emDec->flush();

            $message1 = $ajout . " déclarations  créés et " . $modif . " déclarations  mis à jour";
        } else {
            $message1 = "Fichier inexistant : " . $ficent;
        }

        $messages = array($message1, null, null, null);
        return $messages;
    }

    public function chargeDeclarationProducteurAction($ficent = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeDeclarationProducteur');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoDeclarationProducteur = $emDec->getRepository('AeagDecBundle:DeclarationProducteur');
        if (substr($ficent, 0, 4) == 'init') {
            $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        } else {
            $parametre = $repoParametre->getParametreByCode('REP_EXPORT');
        }
        $rep = $parametre->getLibelle();
        $fichier = $rep . "/" . $ficent;

        $message2 = null;
        $declarationProducteur = null;

        if (file_exists($fichier)) {
            $fic = fopen($fichier, "r");
            $ajout = 0;
            $modif = 0;
            set_time_limit(10000); // temps dexecution du script le plus longtemps
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $i++;
            }
            $tabTri = \array_unique($tabOri, SORT_REGULAR);
            foreach ($tabTri as $tab) {

                if (!(is_null($tab[0]))) {
                    $producteurs = $repoOuvrage->getOuvragesBySiretType($tab[0], 'PDEC');
                    $producteur = null;
                    if ($producteurs) {
                        $producteur = $producteurs[0];
                    }
                    if ($producteur) {
                        $declarationProducteur = $repoDeclarationProducteur->getDeclarationProducteurByProducteurAnnee($producteur->getId(), $tab[1]);
                        if (!$declarationProducteur) {
                            $declarationProducteur = new DeclarationProducteur();
                            $ajout++;
                        } else {
                            $modif++;
                        }
                        $declarationProducteur->setProducteur($producteur->getId());
                        $declarationProducteur->setAnnee($tab[1]);
                        $declarationProducteur->setQuantiteReel($tab[2] * 1000);
                        $declarationProducteur->setMontReel($tab[3]);
                        $declarationProducteur->setQuantiteRet($tab[4] * 1000);
                        $declarationProducteur->setMontRet($tab[5]);
                        $declarationProducteur->setQuantiteAide($tab[6] * 1000);
                        $declarationProducteur->setMontAide($tab[7]);
                        $emDec->persist($declarationProducteur);
                    }
                }
            };

            $emDec->flush();

            $message2 = $ajout . " Dossiers par producteur créés et " . $modif . " dossiers par producteur mis à jour";
        } else {
            $message2 = "Fichier inexistant : " . $ficent;
        }

        $messages = array($message2, $declarationProducteur, null, null);
        return $messages;
    }

    public function chargeDeclarationDetailAction($ficent = null, $ajout = null, $modif = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeDeclarationDetail');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationProducteur = $emDec->getRepository('AeagDecBundle:DeclarationProducteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $repoCollecteurProducteur = $emDec->getRepository('AeagDecBundle:CollecteurProducteur');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoDechet = $emDec->getRepository('AeagDecBundle:Dechet');
        $repoFiliere = $emDec->getRepository('AeagDecBundle:Filiere');
        $repoConditionnement = $emDec->getRepository('AeagDecBundle:Conditionnement');
        $repoNaf = $emDec->getRepository('AeagDecBundle:Naf');
        $repoTaux = $emDec->getRepository('AeagDecBundle:Taux');
        $repoProducteurTauxSpecial = $emDec->getRepository('AeagDecBundle:ProducteurTauxSpecial');

        $anneeDecl = $repoParametre->getParametreByCode('ANNEE');
        if (substr($ficent, 0, 4) == 'init') {
            $parametre = $repoParametre->getParametreByCode('REP_REFERENTIEL');
        } else {
            $parametre = $repoParametre->getParametreByCode('REP_EXPORT');
        }
        $rep = $parametre->getLibelle();
        $fichierEncours = $rep . "/" . $ficent;
        //chmod($fichierEncours, 0775);


        $message3 = array();
        $nbMessage3 = 0;

        if (file_exists($fichierEncours)) {
            $fic = fopen($fichierEncours, "r");
            $tab = fgetcsv($fic, 1024, ';');
            $tabOri = array();
            $tabProducteur = array();
            $tabTri = array();
            $i = 0;
            while (!feof($fic)) {
                $tab = fgetcsv($fic, 1024, ';');
                $tabOri[$i] = $tab;
                $tabProducteur[$i][0] = $tab[0];
                $tabProducteur[$i][1] = $tab[1];
                $i++;
            }
            $tabTri = \array_unique($tabProducteur, SORT_REGULAR);
            foreach ($tabTri as $tab) {

                if (!(is_null($tab[0]))) {
                    //print_r('colelcteur : ' . $tab[0] . ' producteur : ' . $tab[1]);
                    $collecteur = $repoOuvrage->getOuvrageByOuvidType($tab[0], 'ODEC');
                    $producteurs = $repoOuvrage->getOuvragesBySiretType($tab[1], 'PDEC');
                    $producteur = null;
                    if ($producteurs) {
                        $producteur = $producteurs[0];
                    }
                    if ($producteur) {
                        $collecteurProducteur = $repoCollecteurProducteur->getCollecteurProducteurByCollecteurProducteur($collecteur->getId(), $producteur->getId());
                        if (!$collecteurProducteur) {
                            $collecteurProducteur = new CollecteurProducteur();
                            $collecteurProducteur->setCollecteur($collecteur->getId());
                            $collecteurProducteur->setProducteur($producteur->getId());
                            $emDec->persist($collecteurProducteur);
                        }
                    }
                }
            };
            $emDec->flush();
            $tabTri = \array_unique($tabOri, SORT_REGULAR);

            foreach ($tabTri as $tab) {

                if (!(is_null($tab[0]))) {
                    $tauxAide = $repoTaux->getTauxByAnneeCode($tab[2], 'TAUXAIDE');
                    $collecteur = $repoOuvrage->getOuvrageByOuvidType($tab[0], 'ODEC');
                    $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurByCollecteurAnnee($collecteur->getId(), $tab[2]);
                    $producteurs = $repoOuvrage->getOuvragesBySiretType($tab[1], 'PDEC');
                    $producteur = null;
                    if ($producteurs) {
                        $producteur = $producteurs[0];
                    }
                    if ($producteur) {
                        $producteurTauxSpecial = $repoProducteurTauxSpecial->getProducteurTauxSpecialBySiret($producteur->getSiret());
                        if ($producteurTauxSpecial) {
                            $tauxAide = $producteurTauxSpecial->getTaux() / 100;
                            $bonnifier = true;
                        } else {
                            $tauxAeag = $repoTaux->getTauxByAnneeCode($tab[2], 'TAUXAIDE');
                            $tauxAide = $tauxAeag->getValeur();
                            $bonnifier = false;
                        }
                        $declarationProducteur = $repoDeclarationProducteur->getDeclarationProducteurByProducteurAnnee($producteur->getId(), $tab[2]);
                        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteurNumero($declarationCollecteur->getId(), $tab[3]);
                        if ($tab[4]) {
                            if ($tab[4] == '45') {
                                $tab[4] = '50';
                            }
                            if ($tab[4] == '55') {
                                $tab[4] = '50';
                            }
                            $statut = $repoStatut->getStatutByCode($tab[4]);
                        } else {
                            $statut = $repoStatut->getStatutByCode('20');
                        }
                        if ($tab[5]) {
                            $centreTraitement = $repoOuvrage->getOuvrageByOuvIdType($tab[5], 'CTT');
                        } else {
                            $centreTraitement = null;
                        }
                        if ($tab[6]) {
                            $centreTransit = $repoOuvrage->getOuvrageByOuvIdType($tab[6], 'CT');
                        } else {
                            $centreTransit = null;
                        }
                        if ($tab[7]) {
                            $centreEntrepot = $repoOuvrage->getOuvrageByOuvIdType($tab[7], 'ODEC');
                        } else {
                            $centreEntrepot = null;
                        }
                        $dechet = $repoDechet->getDechetByCode($tab[8]);

                        $filiere = $repoFiliere->getFiliereByCode($tab[9]);
                        $traitFiliere = $repoFiliere->getFiliereByCode($tab[10]);
                        //$conditionnement = $repoConditionnement->getConditionnementByCode($tab[11]);
                        $naf = $repoNaf->getNafByCode($tab[12]);

                        if (!($tab[13] == "")) {
                            $dateFacture = new \DateTime(substr($tab[13], 0, 2) . '-' . substr($tab[13], 3, 2) . '-' . substr($tab[13], 6, 4));
                        }

                        $declarationDetail = $repoDeclarationDetail->getDeclarationDetail($sousDeclarationCollecteur->getId(), $declarationProducteur->getId(), $dechet->getCode(), $filiere->getCode(), $traitFiliere->getCode(), $tab[14], number_format($tab[15] * 1000, 6, '.', ''), number_format($tab[16], 2, '.', ''), $this->wd_remove_accents($tab[21]), $dateFacture);
                        if (!$declarationDetail) {
                            $entity = new DeclarationDetail();
                            $ancDeclarationDetail = null;
                            $ajout = $ajout + 1;
                        } else {
                            $entity = $declarationDetail;
                            $ancDeclarationDetail = clone $declarationDetail;
                            $modif = $modif + 1;
                        }

                        $entity->setSousDeclarationCollecteur($sousDeclarationCollecteur);
                        $entity->setDeclarationProducteur($declarationProducteur);
                        $entity->setStatut($statut);
                        if ($centreTraitement) {
                            $entity->setCentreTraitement($centreTraitement->getId());
                        }
                        if ($centreTransit) {
                            $entity->setCentreTransit($centreTransit->getId());
                        }
                        if ($centreEntrepot) {
                            $entity->setCentreDepot($centreEntrepot->getId());
                        }
                        if ($dechet) {
                            $entity->setDechet($dechet);
                        }
                        if ($filiere) {
                            $entity->setFiliere($filiere);
                        }
                        if ($traitFiliere) {
                            $entity->setTraitFiliere($traitFiliere);
                        }
                        if ($naf) {
                            $entity->setNaf($naf);
                        }

                        if (!($tab[13] == "")) {
                            $date = new \DateTime(substr($tab[13], 0, 2) . '-' . substr($tab[13], 3, 2) . '-' . substr($tab[13], 6, 4));
                            $entity->setDatefACTURE($date);
                        }
                        if (!($tab[14] == "")) {
                            $entity->setNumFacture($tab[14]);
                        }

                        if (!($tab[15] == "")) {
                            $entity->setQuantiteReel($tab[15] * 1000);
                        }
                        if (!($tab[16] == "")) {
                            $entity->setMontReel($tab[16]);
                        }
                        if (!($tab[17] == "")) {
                            $entity->setQuantiteRet($tab[17] * 1000);
                        }
                        if (!($tab[18] == "")) {
                            $entity->setMontRet($tab[18]);
                        }
                        if (!($tab[19] == "")) {
                            $entity->setQuantiteAide($tab[19] * 1000);
                        }
                        if (!($tab[20] == "")) {
                            $entity->setMontAide($tab[20]);
                        }
                        if ($entity->getMontAide() && $entity->getQuantiteAide()) {
                            $entity->setCoutFacture(round((($entity->getMontAide() / $entity->getQuantiteAide()) / $tauxAide), 4));
                        } else {
                            $entity->setCoutFacture(0);
                        }
                        if (!($tab[21] == "")) {
                            $entity->setNature($this->wd_remove_accents($tab[21]));
                        }
                        $entity->setDossierAide($tab[22]);
                        $entity->setMontantAp($tab[23]);
                        $entity->setMontantApDispo($tab[24]);
                        $emDec->persist($entity);

                        if (!$ancDeclarationDetail) {
                            $declarationProducteur->setQuantiteReel($declarationProducteur->getQuantiteReel() + $entity->getQuantiteReel());
                            $declarationProducteur->setMontReel($declarationProducteur->getMontReel() + $entity->getMontReel());
                            $declarationProducteur->setQuantiteRet($declarationProducteur->getQuantiteRet() + $entity->getQuantiteRet());
                            $declarationProducteur->setMontRet($declarationProducteur->getMontRet() + $entity->getMontRet());
                            $declarationProducteur->setQuantiteAide($declarationProducteur->getQuantiteAide() + $entity->getQuantiteAide());
                            $declarationProducteur->setMontAide($declarationProducteur->getMontAide() + $entity->getMontAide());
                            $statut = $repoStatut->getStatutByCode('21');
                            $nb21 = $repoDeclarationDetail->getCountStatutByDeclarationProducteurStatut($declarationProducteur->getId(), $statut->getCode());
                            if ($nb21 == 0) {
                                $statut = $repoStatut->getStatutByCode('20');
                            }
                            $declarationProducteur->setStatut($statut);
                        } else {
                            $declarationProducteur->setQuantiteReel($declarationProducteur->getQuantiteReel() + $entity->getQuantiteReel() - $ancDeclarationDetail->getQuantiteReel());
                            $declarationProducteur->setMontReel($declarationProducteur->getMontReel() + $entity->getMontReel() - $ancDeclarationDetail->getMontReel());
                            $declarationProducteur->setQuantiteRet($declarationProducteur->getQuantiteRet() + $entity->getQuantiteRet() - $ancDeclarationDetail->getQuantiteRet());
                            $declarationProducteur->setMontRet($declarationProducteur->getMontRet() + $entity->getMontRet() - $ancDeclarationDetail->getMontRet());
                            $declarationProducteur->setQuantiteAide($declarationProducteur->getQuantiteAide() + $entity->getQuantiteAide() - $ancDeclarationDetail->getQuantiteAide());
                            $declarationProducteur->setMontAide($declarationProducteur->getMontAide() + $entity->getMontAide() - $ancDeclarationDetail->getMontAide());
                            $statut = $repoStatut->getStatutByCode('21');
                            $nb21 = $repoDeclarationDetail->getCountStatutByDeclarationProducteurStatut($declarationProducteur->getId(), $statut->getCode());
                            if ($nb21 == 0) {
                                $statut = $repoStatut->getStatutByCode('20');
                            }
                            $declarationProducteur->setStatut($statut);
                        }

                        $emDec->persist($declarationProducteur);


                        //$emDec->flush();
                    } else {
                        $message3[$nbMessage3] = 'producteur inconnu : ' . $tab[1];
                        $nbMessage3++;
                    }
                }
            };
            if (count($message3) > 0) {
                $messages = array($message3, null, null, null);
                // \Symfony\Component\VarDumper\VarDumper::dump($messages);
                //        return new Response ('');
                return $messages;
            }


            fclose($fic);
            $message3 = $ajout . " producteurs par déclaration créés et " . $modif . " producteurs par déclaration mis à jour";
            if (substr($ficent, 0, 4) == 'init') {
                unlink($fichierEncours);
            }
            $emDec->flush();
        }

        $messages = array($message3, null, null, null);

        return $messages;
    }

    public function chargeFiliereAideAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'chargeFiliereAide');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoFiliereAide = $emDec->getRepository('AeagDecBundle:FiliereAide');

        $filiereAides = $repoFiliereAide->getFiliereAideCharges();

        if ($filiereAides) {
            foreach ($filiereAides as $filiereAide) {
                $entity = $repoFiliereAide->getFiliereByCode($filiereAide->getCode());
                if (!$entity) {
                    $entity = new FiliereAide();
                    $entity->setCode($filiereAide->getCode());
                    $entity->setLibelle($filiereAide->getLibelle());
                    $emDec->persist($entity);
                }
            }
            $emDec->flush();
        }
        $message4 = 'ok';
        return $message4;
    }

    public function listeDeclarationCollecteursAction($annee = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeDeclarationCollecteurs');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');

        $declarations = $repoDeclarationCollecteur->getDeclarationCollecteursByAnnee($annee);

        $odec = array();
        $i = 0;
        foreach ($declarations as $declaration) {
            $sousDeclarations = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteur($declaration->getId());
            $odec[$i][0] = $declaration;
            $odec[$i][1] = $sousDeclarations;
            $i++;
        }

        return $this->render('AeagDecBundle:Referentiel:listeDeclarationCollecteurs.html.twig', array(
                    'entities' => $odec,
                    'annee' => $annee
        ));
    }

    public function listeSousDeclarationCollecteursAction($declarationCollecteur_id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Referentiel');
        $session->set('fonction', 'listeSousDeclarationCollecteurs');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');

        $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($declarationCollecteur_id);
        $sousDeclarationCollecteurs = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteur($declarationCollecteur->getId());

        return $this->render('AeagDecBundle:Referentiel:listeSousDeclarationCollecteurs.html.twig', array(
                    'annee' => $session->get('annee'),
                    'declarationCollecteur' => $declarationCollecteur,
                    'entities' => $sousDeclarationCollecteurs,
        ));
    }

    private static function wd_remove_accents($str, $charset = 'utf-8') {


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
            '\'' => ' ',
                ))
        );


        return $str;
    }

    private static function dateFR2Time($date) {
        list($day, $month, $year) = explode('/', $date);
        $timestamp = mktime(0, 0, 0, $month, $day, $year);
        return $timestamp;
    }

}
