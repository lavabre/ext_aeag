<?php

namespace Aeag\AideBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Aeag\AideBundle\Form\Criteres\CriteresRequestType;
use Aeag\AideBundle\Entity\Form\CriteresRequest;
use Aeag\AideBundle\DependencyInjection\PdfAidesAccordees;

/**
 * Description DefaultController
 *
 * @author lavabre
 *
 *
 */
class CriteresController extends Controller {

    /**
     * Parametres
     *
     * @Template()
     *
     *
     */
    public function criteresAction(Request $request) {
        $session = $this->get('session');

        $session->set('retourErreur', $this->generateUrl('aeag_aide'));

        $criteres = new CriteresRequest();

        $form = $this->createForm(new CriteresRequestType(), $criteres);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            $emAeag = $this->getDoctrine()->getManager();
            $em = $this->getDoctrine()->getManager('aide');
            $repoLigne = $em->getRepository('AeagAideBundle:Ligne');
            $repoCategorie = $em->getRepository('AeagAideBundle:Categorie');
            $repoAnnee = $em->getRepository('AeagAideBundle:Annee');
            $repoDepartement = $emAeag->getRepository('AeagAeagBundle:Departement');
            $repoRegionAdmin = $emAeag->getRepository('AeagAeagBundle:Region');
            $repoRegionHydro = $em->getRepository('AeagAideBundle:RegionHydro');
            $repoDossier = $em->getRepository('AeagAideBundle:Dossier');
            $variables = array();
            // Critère ligne
            If ($criteres->getLigne()) {
                // On recherche dans la table Lignes l'enregistremnet qui correspond à la selection
                $variables['ligne_libelle'] = $criteres->getLigne()->getLibelle();
                $variables['ligne_numero'] = $criteres->getLigne()->getLigne();
                $session->set('ligne_numero', $criteres->getLigne()->getLigne());
                $where_ligne = $criteres->getLigne()->getLigne();
            } else {
                $variables['ligne_libelle'] = " Toutes les lignes";
                $where_ligne = "a.ligne";
                //$criteres->setDebutAnnee($criteres->getFinAnnee());
            }
            $session->set('ligne_libelle', $variables['ligne_libelle']);


            // Critère categorie
            $variables['categorie_libelle'] = null;
            $where_cate = null;
            If (count($criteres->getCate()) > 0) {
                foreach ($criteres->getCate() as $categorie) {
                    if ($variables['categorie_libelle']) {
                        $variables['categorie_libelle'] = $variables['categorie_libelle'] . ', ' . trim($categorie->getCate()) . ' ';
                    } else {
                        $variables['categorie_libelle'] = trim($categorie->getCate());
                    }

                    if ($where_cate) {
                        $where_cate = $where_cate . " or a.cate  =  '" . $categorie->getCate() . "'";
                    } else {
                        $where_cate = "  and ( a.cate  = '" . $categorie->getCate() . "'";
                    }
//                if ($criteres->getCate()->getCate() != 'Privé (hors association)') {
//                    $where_cate = " = '" . $criteres->getCate()->getCate() . "'";
//                } else {
//                    $where_cate = " != 'Association' and a.cate != 'Public'";
//                }
                }
                if ($where_cate) {
                    $where_cate = $where_cate . ") ";
                }
            } else {
                $variables['categorie_libelle'] = " Tous";
            }
            $session->set('categorie_libelle', $variables['categorie_libelle']);

            // Critère annee
            if ($criteres->getDebutAnnee()) {
                if (!$criteres->getFinAnnee()) {
                    $criteres->setFinAnnee($criteres->getDebutAnnee());
                }
                if ($criteres->getDebutAnnee()->getAnnee() != $criteres->getFinAnnee()->getAnnee()) {
                    $variables['annees'] = true;
                } else {
                    $variables['annees'] = false;
                }
                $session->set('annees', $variables['annees']);
                $variables['annee_libelle'] = "décision prise entre le 1er janvier " . $criteres->getDebutAnnee()->getAnnee() . " et le 31 décembre " . $criteres->getFinAnnee()->getAnnee();
                $where_annee = " and a.annee >= " . $criteres->getDebutAnnee()->getAnnee() . " and a.annee <= " . $criteres->getFinAnnee()->getAnnee();
            } else {
                $variables['annee_libelle'] = "décision prise depuis le 1er janvier 2000";
                $where_annee = " and a.annee >= 2000 ";
            }
            $session->set('annee_libelle', $variables['annee_libelle']);



            // Critère Region administrative
            If ($criteres->getRegionAdmin()) {
                $variables['region_admin_libelle'] = $criteres->getRegionAdmin()->getLibelle();
                $where_regionAdmin = "'" . $criteres->getRegionAdmin()->getReg() . "'";
            } else {
                $variables['region_admin_libelle'] = null;
                $where_regionAdmin = " a.regadmin";
            }
            $session->set('region_admin_libelle', $variables['region_admin_libelle']);


            // Critère département
            If ($criteres->getDepartement()) {
                $variables['departement_libelle'] = $criteres->getDepartement()->getDeptLibelle();
                $where_departement = "'" . $criteres->getDepartement()->getDept() . "'";
            } else {
                $variables['departement_libelle'] = null;
                $where_departement = " a.dept";
            }
            $session->set('departement_libelle', $variables['departement_libelle']);

            // Critère Region hydrograhique
            If ($criteres->getRegionHydro()) {
                $variables['region_hydro_libelle'] = $criteres->getRegionHydro()->getLibelle();
                $where_regionHydro = "'" . $criteres->getRegionHydro()->getReg() . "'";
            } else {
                $variables['region_hydro_libelle'] = null;
                $where_regionHydro = " a.reghydro";
            }
            $session->set('region_hydro_libelle', $variables['region_hydro_libelle']);



            // construction de la requete de résultat

            $where = "a.ligne = " . $where_ligne;
            $where .= $where_cate;
            $where .= $where_annee;
            $where .= " and a.regadmin = " . $where_regionAdmin;
            $where .= " and a.dept = " . $where_departement;
            $where .= " and a.reghydro = " . $where_regionHydro;
            $where .= " and  ((a.typeci = 'CI' and a.phase <> 'T35')";
            $where .= " or (a.typeci ='DD' and  (a.phase <> 'T35' and a.init_annee is null)))";
            $variables['where'] = $where;
            $session->set('where', $variables['where']);

            $nb_dossiers = $repoDossier->getNbDossiers($where);
            $variables['nb_dossiers'] = $nb_dossiers;
            $session->set('nb_dossiers', $variables['nb_dossiers']);

            if ($nb_dossiers > 10000) {
                $full = true;
                $csv = false;
            } else {
                $full = false;
                $csv = true;
            }

            $total_retenu = $repoDossier->getSumMontantRetenu($where);
            $variables['total_retenu'] = $total_retenu;
            $session->set('total_retenu', $variables['total_retenu']);

            $total_dossiers = $repoDossier->getSumMontantAideInterne($where);
            $variables['total_dossiers'] = $total_dossiers;
            $session->set('total_dossiers', $variables['total_dossiers']);

            $dos = array();

            if (!$full) {

                $dossiers = $repoDossier->getDossiers($where);
                usort($dossiers, array('self', 'tri_dossiers'));
                $i = 0;

                $repertoire = 'fichiers/';
                $date_import = date('Ymd_His');
                $nom_fichier = "aides_accordees_" . $date_import . ".csv";
                $fic_import = $repertoire . "/" . $nom_fichier;
                //ouverture fichier
                $fic = fopen($fic_import, "w");
                if (!$variables['annees']) {
                    $contenu = "DOSSIER;MONTANT TRAVAUX RETENUS;MONTANT AIDE;NATURE OPEREATION;RAISON SOCIALE;INTITULE;\n";
                } else {
                    $contenu = "DOSSIER;ANNEE;MONTANT TRAVAUX RETENUS;MONTANT AIDE;NATURE OPEREATION;RAISON SOCIALE;INTITULE;\n";
                }
                fputs($fic, $contenu);
                foreach ($dossiers as $dossier) {
                    $montantRetenu = strval($dossier->getMontant_retenu());
                    $montant = strval($dossier->getMontant_aide_interne());
                    $dos[$i] = array(
                        'dossier' => $dossier->getLigne()->getLigne() . '-' . $dossier->getDept()->getDept() . '-' . $dossier->getNo_ordre(),
                        'annee' => $dossier->getAnnee()->getAnnee(),
                        'montant_retenu' => $montantRetenu,
                        'montant_aide_interne' => $montant,
                        'forme_aide' => $dossier->getForme_aide(),
                        'raison_sociale' => $dossier->getRaison_sociale(),
                        'intitule' => $dossier->getIntitule());
                    $i++;
                    $contenu = $dossier->getLigne()->getLigne() . '-' . $dossier->getDept()->getDept() . '-' . $dossier->getNo_ordre() . ";";
                    if ($variables['annees']) {
                        $contenu = $contenu . $dossier->getAnnee()->getAnnee() . ";";
                    }
                    $contenu = $contenu . $montantRetenu . ";";
                    $contenu = $contenu . $montant . ";";
                    $contenu = $contenu . $dossier->getForme_aide() . ";";
                    $contenu = $contenu . iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $dossier->getRaison_sociale()) . ";";
                    $contenu = $contenu . iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $dossier->getIntitule()) . ";\n";
                    fputs($fic, $contenu);
                }

                fclose($fic);
                $variables['fichier'] = $nom_fichier;
            }

//            $session->set('Dossiers', serialize($dos));



            return $this->render('AeagAideBundle:Criteres:resultat.html.twig', array(
                        'dossiers' => $dos,
                        'nb_dossiers' => $nb_dossiers,
                        'criteres' => $variables,
                        'full' => $full,
                        'csv' => $csv
            ));
        }


        return array("form" => $form->createView());
    }

    public function regionDepartementsAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $repoRegionAdmin = $em->getRepository('AeagAeagBundle:Region');


        $critRegion = $request->get('region');

        if ($critRegion) {
            $departements = $repoDepartement->getDepartementsByRegion($critRegion);
        } else {
            $departements = $repoDepartement->getDepartements();
        }

        return $this->render('AeagAideBundle:Criteres:regionDepartements.html.twig', array(
                    'region' => $critRegion,
                    'departements' => $departements
        ));
    }

    /**
     *  Fichier PDF
     */
    public function pdfAction() {

        $em = $this->getDoctrine()->getManager('aide');
        $repoDossier = $em->getRepository('AeagAideBundle:Dossier');

        $session = $this->get('session');

        // Critère ligne

        $variables['ligne_libelle'] = $session->get('ligne_libelle');
        $variables['ligne_numero'] = $session->get('ligne_numero');


        // Critère categorie
        $variables['categorie_libelle'] = $session->get('categorie_libelle');

        // Critère annee
        $variables['annee_libelle'] = $session->get('annee_libelle');
        $variables['annees'] = $session->get('annees');

        // Critère Region administrative
        $variables['region_admin_libelle'] = $session->get('region_admin_libelle');

        // Critère département
        $variables['departement_libelle'] = $session->get('departement_libelle');

        // Critère Region hydrograhique
        $variables['region_hydro_libelle'] = $session->get('region_hydro_libelle');


        // Nombre de dossiers
        $variables['nb_dossiers'] = $session->get('nb_dossiers');

        // total montant retenu
        $variables['total_retenu'] = $session->get('total_retenu');

        // total montant aide
        $variables['total_dossiers'] = $session->get('total_dossiers');

        // Liste des dossiers selectionnés
        $nb_dossiers = $repoDossier->getNbDossiers($session->get('where'));
        $dossiers = $repoDossier->getDossiers($session->get('where'));
        usort($dossiers, array('self', 'tri_dossiers'));
        $dos = array();
        $i = 0;
        foreach ($dossiers as $dossier) {
            $montantRetenu = strval($dossier->getMontant_retenu());
            $montant = strval($dossier->getMontant_aide_interne());
            if (!$session->get('annees')) {
                $dos[$i] = array(
                    'dossier' => $dossier->getLigne()->getLigne() . '-' . $dossier->getDept()->getDept() . '-' . $dossier->getNo_ordre(),
                    'montant_retenu' => $montantRetenu,
                    'montant_aide_interne' => $montant,
                    'forme_aide' => $dossier->getForme_aide(),
                    'raison_sociale' => $dossier->getRaison_sociale(),
                    'intitule' => $dossier->getIntitule());
            } else {
                $dos[$i] = array(
                    'dossier' => $dossier->getLigne()->getLigne() . '-' . $dossier->getDept()->getDept() . '-' . $dossier->getNo_ordre(),
                    'annee' => $dossier->getAnnee()->getAnnee(),
                    'montant_retenu' => $montantRetenu,
                    'montant_aide_interne' => $montant,
                    'forme_aide' => $dossier->getForme_aide(),
                    'raison_sociale' => $dossier->getRaison_sociale(),
                    'intitule' => $dossier->getIntitule());
            }
            $i++;
        }
        $variables['Dossiers'] = $dos;


        $pdf = new PdfAidesAccordees('L');
        $titre = 'Les aides accordées par l\'agence de l\'eau Adour-Garonne';
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup();
        $pdf->AddPage($variables);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($variables);

        $repertoire = 'fichiers/';
        $date_import = date('Ymd_His');
        $nom_fichier = "aeag_aides_accordees_" . $date_import . ".pdf";
        $fic_import = $repertoire . "/" . $nom_fichier;

        $fichier = 'AEAG_AIDES_ACCORDEES.pdf';
        if ($nb_dossiers > 10000) {
            $pdf->Output($fic_import, 'F');
        } else {
            $pdf->Output($fic_import, 'D');
        }

        return $this->render('AeagAideBundle:Criteres:pdf.html.twig', array('fichier' => $nom_fichier));
    }

    public function csvAction() {

        $em = $this->getDoctrine()->getManager('aide');
        $repoDossier = $em->getRepository('AeagAideBundle:Dossier');

        $session = $this->get('session');

        // Critère ligne

        $variables['ligne_libelle'] = $session->get('ligne_libelle');
        $variables['ligne_numero'] = $session->get('ligne_numero');


        // Critère categorie
        $variables['categorie_libelle'] = $session->get('categorie_libelle');

        // Critère annee
        $variables['annee_libelle'] = $session->get('annee_libelle');
        $variables['annees'] = $session->get('annees');

        // Critère Region administrative
        $variables['region_admin_libelle'] = $session->get('region_admin_libelle');

        // Critère département
        $variables['departement_libelle'] = $session->get('departement_libelle');

        // Critère Region hydrograhique
        $variables['region_hydro_libelle'] = $session->get('region_hydro_libelle');


        // Nombre de dossiers
        $variables['nb_dossiers'] = $session->get('nb_dossiers');

        // total montant retenu
        $variables['total_retenu'] = $session->get('total_retenu');

        // total montant aide
        $variables['total_dossiers'] = $session->get('total_dossiers');

        // Liste des dossiers selectionnés
        $dossiers = $repoDossier->getDossiers($session->get('where'));
        usort($dossiers, array('self', 'tri_dossiers'));
        $i = 0;

        $repertoire = 'fichiers/';
        $date_import = date('Ymd_His');
        $nom_fichier = "aeag_aides_accordees_" . $date_import . ".csv";
        $fic_import = $repertoire . "/" . $nom_fichier;
        //ouverture fichier
        $fic = fopen($fic_import, "w");
        if (!$variables['annees']) {
            $contenu = "DOSSIER;MONTANT TRAVAUX RETENUS;MONTANT AIDE;NATURE OPEREATION;RAISON SOCIALE;INTITULE;\n";
        } else {
            $contenu = "DOSSIER;ANNEE;MONTANT TRAVAUX RETENUS;MONTANT AIDE;NATURE OPEREATION;RAISON SOCIALE;INTITULE;\n";
        }
        fputs($fic, $contenu);
        foreach ($dossiers as $dossier) {
            $montantRetenu = strval($dossier->getMontant_retenu());
            $montant = strval($dossier->getMontant_aide_interne());
            $dos[$i] = array(
                'dossier' => $dossier->getLigne()->getLigne() . '-' . $dossier->getDept()->getDept() . '-' . $dossier->getNo_ordre(),
                'annee' => $dossier->getAnnee()->getAnnee(),
                'montant_retenu' => $montantRetenu,
                'montant_aide_interne' => $montant,
                'forme_aide' => $dossier->getForme_aide(),
                'raison_sociale' => $dossier->getRaison_sociale(),
                'intitule' => $dossier->getIntitule());
            $i++;
            $contenu = $dossier->getLigne()->getLigne() . '-' . $dossier->getDept()->getDept() . '-' . $dossier->getNo_ordre() . ";";
            if ($variables['annees']) {
                $contenu = $contenu . $dossier->getAnnee()->getAnnee() . ";";
            }
            $contenu = $contenu . $montantRetenu . ";";
            $contenu = $contenu . $montant . ";";
            $contenu = $contenu . $dossier->getForme_aide() . ";";
            $contenu = $contenu . iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $dossier->getRaison_sociale()) . ";";
            $contenu = $contenu . iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $dossier->getIntitule()) . ";\n";
            fputs($fic, $contenu);
        }

        fclose($fic);
        $variables['fichier'] = $nom_fichier;
        return $this->render('AeagAideBundle:Criteres:csv.html.twig', array('fichier' => $nom_fichier));
    }

    static function tri_dossiers($a, $b) {
        $al = strtolower($a->getLigne()->getLigne());
        $bl = strtolower($b->getLigne()->getLigne());
        if ($al == $bl) {
            $ad = strtolower($a->getDept()->getDept());
            $bd = strtolower($b->getDept()->getDept());
            if ($ad == $bd) {
                $an = strtolower($a->getNo_ordre());
                $bn = strtolower($b->getNo_ordre());
                if ($an == $bn) {
                    return 0;
                }
                return ($an > $bn) ? +1 : -1;
            }
            return ($ad > $bd) ? +1 : -1;
        }
        return ($al > $bl) ? +1 : -1;
    }

    public static function wd_remove_accents($str, $charset = 'utf-8') {


        //$str = utf8_encode($str);

        $str = str_replace('¿', '\'', $str);
        $str = str_replace('Ô', 'O', $str);
        $str = str_replace('É', 'E', $str);
        $str = str_replace('È', 'E', $str);
    }

}
