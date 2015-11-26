<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PDF
 *
 * @author lavabre
 * 
 * 
 */

namespace Aeag\AideBundle\DependencyInjection;

use Aeag\AideBundle\DependencyInjection\fpdf\PageGroup;

class PdfAidesAccordees extends PageGroup {

    function Header($data) {
        // Logo
        $this->Image('./bundles/aeagaeag/images/aeagepmdd.jpg', 10, 6, 30, 0, '', 'http://www.eau-adour-garonne.fr');
        // Police Arial gras 15
        // Couleurs, épaisseur du trait et police grasse
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 102);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFont('Arial', 'B', 15);
        // Décalage à droite
        $this->Cell(130);
        // Titre
        $this->Cell(30, 10, 'Les aides accordées par l\'agence de l\'eau Adour-Garonne', 0, 0, 'C');
        //date
        $this->Cell(100);
        $this->SetFont('Arial', '', 6);
        $this->Cell(30, 6, date('d/m/o'), 0, 0, 'C');
    }

    function Footer() {
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 102);
        $this->SetDrawColor(0, 0, 0);
        $this->SetY(-20);
        //$this->Cell(0, 4, 'Page ' . $this->GroupPageNo() . '/' . $this->PageGroupAlias(), 0, 0, 'C');
        $this->Cell(0, 4, 'Agence de l\'eau Adour-Garonne - 90, rue du férétra - 31078 Toulouse Cedex 4 - Tél. : 05 61 36 37 38', 0, 0, 'C');
        // Numéro de page centré
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

// Tableau coloré
    function Formatage($data) {

        // Restauration des couleurs et de la police
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0, 0, 102);
        $this->SetFont('Arial', '', 6);
        // criteres
        $this->Ln();
        $this->Ln();
        $this->Cell(115);
        $this->Cell(10, 3, number_format($data['nb_dossiers'], 0, ',', ' ') . ' dossiers répondent aux critères suivants :', 0, 0, 'L');
        $this->Ln();
        $this->Cell(120);
        $this->Cell(10, 3, 'Ligne : ' . $data['ligne_libelle'], 0, 0, 'L');
        $this->Ln();
        $this->Cell(120);
        $this->Cell(10, 3, 'Categorie : ' . $data['categorie_libelle'], 0, 0, 'L');
        $this->Ln();
        $this->Cell(120);
        $this->Cell(10, 3, $data['annee_libelle'], 0, 0, 'L');
        if ($data['region_admin_libelle'] !== null) {
            $this->Ln();
            $this->Cell(120);
            $this->Cell(10, 3, 'Région administrative : ' . $data['region_admin_libelle'], 0, 0, 'L');
        }
        if ($data['departement_libelle'] !== null) {
            $this->Ln();
            $this->Cell(120);
            $this->Cell(10, 3, 'Département : ' . $data['departement_libelle'], 0, 0, 'L');
        }
        if ($data['region_hydro_libelle'] !== null) {
            $this->Ln();
            $this->Cell(120);
            $this->Cell(10, 3, 'Localisation hydrographique : ' . $data['region_hydro_libelle'], 0, 0, 'L');
        }
        if ($data['categorie_libelle'] == 'Association') {
            $this->Ln();
            $this->Cell(120);
            $this->Cell(10, 3, 'Publication conformément au décret n° 2006-887 du 17 juillet 2006', 0, 0, 'L');
        }
        $this->Ln(10);
        // Couleurs, épaisseur du trait et police grasse
        $this->SetFillColor(242, 242, 242);
        $this->SetTextColor(0, 0, 102);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial', 'B');
        // En-tête des colonnes
        $w = array(17, 30, 20, 30, 90, 90);
        $header = array('Dossiers', 'Montant des travaux retenus ', 'Montant aide', 'Nature opération', 'Raison sociale', 'Intitulé');
        for ($i = 0; $i < count($header); $i++)
            $this->Cell($w[$i], 6, $header[$i], 1, 0, 'C', true);
        $this->Ln();
        // Restauration des couleurs et de la police
        $this->SetFillColor(237, 240, 244);
        $this->SetTextColor(0, 0, 102);
        $this->SetFont('Arial', '', 6);
        // Chargement des données
        $fill = false;
        $nbLignes = 0;
        $montantRetenuPage = 0;
        $montantPage = 0;
        $montantRetenuDocument = 0;
        $montantDocument = 0;
        foreach ($data['Dossiers'] as $row) {
            //print_r('dossier : ' . $row['dossier']);
            $this->Cell($w[0], 3, $row['dossier'], 'LR', 0, 'C', $fill);
            $this->Cell($w[1], 3, number_format($row['montant_retenu'], 2, ',', ' ') . ' €', 'LR', 0, 'R', $fill);
            $this->Cell($w[2], 3, number_format($row['montant_aide_interne'], 2, ',', ' ') . ' €', 'LR', 0, 'R', $fill);
            $this->Cell($w[3], 3, $row['forme_aide'], 'LR', 0, 'L', $fill);
            $this->Cell($w[4], 3, substr($row['raison_sociale'], 0, 60), 'LR', 0, 'L', $fill);
            $this->Cell($w[5], 3, substr($row['intitule'], 0, 80), 'LR', 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill;
            $nbLignes += 1;
            $montantRetenuPage += $row['montant_retenu'];
            $montantPage += $row['montant_aide_interne'];
            $montantRetenuDocument += $row['montant_retenu'];
            $montantDocument += $row['montant_aide_interne'];
            if ($nbLignes > 44) {
                // Couleurs, épaisseur du trait et police grasse
                $this->SetFillColor(242, 242, 242);
                $this->SetTextColor(0, 0, 102);
                $this->SetDrawColor(0, 0, 0);
                $this->SetLineWidth(.3);
                $this->SetFont('Arial', 'B');
                // total page
                $this->Cell($w[0], 3, 'Total page ' . $this->GroupPageNo(), '1', 0, 'C', true);
                $this->Cell($w[1], 3, number_format($montantRetenuPage, 2, ',', ' ') . ' €', '1', 0, 'R', true);
                $this->Cell($w[2], 3, number_format($montantPage, 2, ',', ' ') . ' €', '1', 0, 'R', true);
                $this->Cell($w[3], 3, '', '1', 0, 'C', true);
                $this->Cell($w[4], 3, '', '1', 0, 'C', true);
                $this->Cell($w[5], 3, '', '1', 0, 'C', true);
                $this->Ln();
                $this->SetFont('Arial', '', 6);
                // Trait de terminaison
                $this->Cell(array_sum($w), 0, '', 'T');
                // saut de page
                $this->AddPage($data);
                // Restauration des couleurs et de la police
                $this->SetFillColor(237, 240, 244);
                $this->SetTextColor(0, 0, 102);
                $this->SetFont('Arial', '', 6);
                // criteres
                $this->Ln();
                $this->Ln();
                $this->Cell(115);
                $this->Cell(10, 3, number_format($data['nb_dossiers'], 0, ',', ' ') . ' dossiers répondent aux critères suivants :', 0, 0, 'L');
                $this->Ln();
                $this->Cell(120);
                $this->Cell(10, 3, 'Ligne : ' . $data['ligne_libelle'], 0, 0, 'L');
                $this->Ln();
                $this->Cell(120);
                $this->Cell(10, 3, 'Categorie : ' . $data['categorie_libelle'], 0, 0, 'L');
                $this->Ln();
                $this->Cell(120);
                $this->Cell(10, 3, $data['annee_libelle'], 0, 0, 'L');
                if ($data['region_admin_libelle'] !== null) {
                    $this->Ln();
                    $this->Cell(120);
                    $this->Cell(10, 3, 'Région administrative : ' . $data['region_admin_libelle'], 0, 0, 'L');
                }
                if ($data['departement_libelle'] !== null) {
                    $this->Ln();
                    $this->Cell(120);
                    $this->Cell(10, 3, 'Département : ' . $data['departement_libelle'], 0, 0, 'L');
                }
                if ($data['region_hydro_libelle'] !== null) {
                    $this->Ln();
                    $this->Cell(120);
                    $this->Cell(10, 3, 'Localisation hydrographique : ' . $data['region_hydro_libelle'], 0, 0, 'L');
                }
                if ($data['categorie_libelle'] == 'Association') {
                    $this->Ln();
                    $this->Cell(120);
                    $this->Cell(10, 3, 'Publication conformément au décret n° 2006-887 du 17 juillet 2006', 0, 0, 'L');
                }
                $this->Ln(10);
                // Couleurs, épaisseur du trait et police grasse
                $this->SetFillColor(242, 242, 242);
                $this->SetTextColor(0, 0, 102);
                $this->SetDrawColor(0, 0, 0);
                $this->SetLineWidth(.3);
                $this->SetFont('Arial', 'B');
                // En-tête
                $this->SetFont('Arial', 'B');
                for ($i = 0; $i < count($header); $i++)
                    $this->Cell($w[$i], 6, $header[$i], 1, 0, 'C', true);
                $this->Ln();
                $fill = false;
                // Restauration des couleurs et de la police
                $this->SetFillColor(237, 240, 244);
                $this->SetTextColor(0, 0, 102);
                $this->SetFont('Arial', '', 6);

                $nbLignes = 0;
                $montantPage = 0;
            }
        }
        // Couleurs, épaisseur du trait et police grasse
        $this->SetFillColor(242, 242, 242);
        $this->SetTextColor(0, 0, 102);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial', 'B');
        // total page
        $this->Cell($w[0], 3, 'Total page ' . $this->GroupPageNo(), '1', 0, 'C', true);
        $this->Cell($w[1], 3, number_format($montantRetenuPage, 2, ',', ' ') . ' €', '1', 0, 'R', true);
        $this->Cell($w[2], 3, number_format($montantPage, 2, ',', ' ') . ' €', '1', 0, 'R', true);
        $this->Cell($w[3], 3, '', '1', 0, 'C', true);
        $this->Cell($w[4], 3, '', '1', 0, 'C', true);
        $this->Cell($w[5], 3, '', '1', 0, 'C', true);
        $this->Ln();
        $fill = !$fill;
        // total document
        $this->Cell($w[0], 3, 'Total général ', '1', 0, 'C', true);
        $this->Cell($w[1], 3, number_format($montantRetenuDocument, 2, ',', ' ') . ' €', '1', 0, 'R', true);
        $this->Cell($w[2], 3, number_format($montantDocument, 2, ',', ' ') . ' €', '1', 0, 'R', true);
        $this->Cell($w[3], 3, '', '1', 0, 'C', true);
        $this->Cell($w[4], 3, '', '1', 0, 'C', true);
        $this->Cell($w[5], 3, '', '1', 0, 'C', true);
        $this->Ln();
        $this->SetFont('Arial', '', 6);
    }

}

?>
