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

namespace Aeag\DecBundle\DependencyInjection;

use Aeag\DecBundle\DependencyInjection\fpdf\PageGroup;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class PdfListeProducteurTauxSpecialAll extends PageGroup {

    function Header($entity) {


        // Logo
        $this->Image('./bundles/aeagaeag/images/aeagepmdd.jpg', 10, 4, 30, 0, '', 'http://www.eau-adour-garonne.fr');
        // Police Arial gras 15
        // Couleurs, épaisseur du trait et police grasse
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 102);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFont('Arial', 'B', 12);
        // Décalage à droite
        $this->Cell(100);
        // Titre
        $this->Cell(1, 12, 'Liste des producteurs avec un taux d\'aide spécial', 0, 1, 'C');
        $this->Cell(100);
        $this->Cell(1, 1, 'de l\'agence de l\'eau \'Adour-Garonne', 0, 1, 'C');
        //date
        $this->Cell(170);
        $this->SetFont('Arial', '', 8);
        $this->Cell(30, 12, date('d/m/o'), 0, 1, 'C');
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
    function Formatage($entities) {

        $this->ln(5);

        // Restauration des couleurs et de la police
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0, 0, 102);
        $this->SetFont('Arial', '', 8);


        // Entete
        $this->SetFont('Arial', 'B');
        // En-tête des colonnes
        $w = array(25, 75, 75, 15);
        $col = array();
        $header = array('Siret', 'Raison sociale', 'Localisation', 'Taux');
        // En-tête
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
            $col[$i] = $this->GetX();
        }
        $this->Ln();


        // Restauration des couleurs et de la police
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('Arial', '', 8);

        $x = $this->GetX();
        $y = $this->GetY();
        $h = 0;
        $nbLignes = 0;
        $totNbLignes = 0;
        $fill = false;
        // Chargement des données
        foreach ($entities as $entity) {
            $this->Cell($w[0], 4, $entity->getSiret(), 'LR', 0, 'C', $fill);
            $this->Cell($w[1], 4, $entity->getRaisonSociale(), 'LR', 0, 'L', $fill);
            $this->Cell($w[2], 4, $entity->getLocalisation(), 'LR', 0, 'L', $fill);
            $this->Cell($w[3], 4, number_format($entity->getTaux(), 2, ',', ' ') . ' %', 'LR', 0, 'C', $fill);
            $this->ln();
            $h = $h + 4;
            $fill = !$fill;
            $nbLignes += 1;
            $totNbLignes = $totNbLignes + 1;
            if ($nbLignes > 55) {
                // rectangle
                $this->Rect($x, $y, 190, $h, 'D');
                $this->ln();
                // saut de page
                $this->AddPage($entity, 'P');
                $this->ln(5);

                // Restauration des couleurs et de la police
                $this->SetFillColor(224, 235, 255);
                $this->SetTextColor(0, 0, 102);
                $this->SetFont('Arial', '', 8);

                // Entete
                $this->SetFont('Arial', 'B');
                // En-tête des colonnes
                $w = array(25, 75, 75, 15);
                $header = array('Siret', 'Raison sociale', 'Localisation', 'Taux');
                // En-tête
                for ($i = 0; $i < count($header); $i++)
                    $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
                $this->Ln();

                // Restauration des couleurs et de la police
                $this->SetFillColor(224, 235, 255);
                $this->SetTextColor(0);
                $this->SetFont('Arial', '', 8);

                $x = $this->GetX();
                $y = $this->GetY();
                $h = 0;
                $nbLignes = 0;
                $fill = false;
            }
        }
        // rectangle
        $this->Rect($x, $y, 190, $h, 'D');
        $this->ln();
    }

    /**
     * tronquer_texte
     * Coupe une chaine sans couper les mots
     *
     * @param string $texte Texte à couper
     * @param integer $nbreCar Longueur à garder en nbre de caractères
     * @return string
     */
    public function tronquer_texte($texte, $nbchar) {
        return (strlen($texte) > $nbchar ? substr(substr($texte, 0, $nbchar), 0, strrpos(substr($texte, 0, $nbchar), " ")) : $texte);
    }

}
