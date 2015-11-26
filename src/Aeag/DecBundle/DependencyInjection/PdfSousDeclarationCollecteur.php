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

class PdfSousDeclarationCollecteur extends PageGroup {

    function Header($dec) {


        // Logo
        $this->Image('./bundles/aeagaeag/images/aeagepmdd.jpg', 10, 4, 30, 0, '', 'http://www.eau-adour-garonne.fr');
        // Police Arial gras 15
        // Couleurs, épaisseur du trait et police grasse
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 102);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFont('Arial', 'B', 12);
        // Décalage à droite
        $this->Cell(20);
        // Titre
        $this->Cell(0, 12, 'Collecteur ' . $dec['collecteur']->getNumero() . ' ' . $dec['collecteur']->getLibelle(), 0, 1, 'C');
        $this->Cell(20);
        $this->Cell(0, 1, 'Déclaration n° ' . $dec['sousDeclarationCollecteur']->getNumero() . ' de l\'année ' . $dec['sousDeclarationCollecteur']->getdeclarationCollecteur()->getAnnee(), 0, 1, 'C');
        
        
        //date
        $this->Cell(250);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 12, date('d/m/o'), 0, 1, 'C');
        $this->ln(2);

        // Restauration des couleurs et de la police
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0, 0, 102);
        $this->SetFont('Arial', 'B');

        $w = array(40, 40, 40);
        $header = array('Quantité déclarée (kg)', 'Quantité retenue (kg)', 'Aide retenue (€)');
        $this->Cell(100);
        // En-tête
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        $x = 110;
        $y = $this->GetY();
        $h = 0;
        $nbLignes = 0;
        $this->SetFont('Arial', '', 8);

        $this->Cell(100);
        $this->Cell($w[0], 7, number_format($dec['sousDeclarationCollecteur']->getQuantiteReel(), 0, ',', ' '), 'LR', 0, 'C');
        $this->Cell($w[1], 7, number_format($dec['sousDeclarationCollecteur']->getQuantiteRet(), 0, ',', ' '), 'LR', 0, 'C');
        $this->Cell($w[2], 7, number_format($dec['sousDeclarationCollecteur']->getMontAide(), 2, ',', ' '), 'LR', 0, 'C');
        $this->Ln();
        $h = $h + 7;
        // rectangle 
        $this->Rect($x, $y, 120, $h, 'D');
        $this->ln(2);
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
    function Formatage($dec, $declarationDetails) {

        $this->ln(5);

        // Restauration des couleurs et de la police
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0, 0, 102);
        $this->SetFont('Arial', '', 8);


        // En-tête des colonnes
        $w = array(20, 50, 10, 15, 15, 15, 10, 50, 10, 10, 15, 10, 15, 15, 10, 10);
        $header = array('Siret', 'Raison sociale', 'Code postal', 'Avtivité (code NAF)', 'N° facture', 'Date de facture', 'Code nomenclature', 'dénomination usuelle du déchet', 'Code D/R', 'Centre traitement', 'Quantité pesée (en kg)', 'Code conditionnement', 'Coût facturé (€/kg)', 'Montant de l\'aide (€)', 'Centre entreposage', 'centre transit');
        // En-tête
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 10, '', 1, 0, 'C', true);
        }
        //$this->Ln();
        $x = $this->GetX();
        $y = $this->GetY();
        $this->setX($x - 280);
        $this->setY($y + 2);
        $this->Cell(20, 4, $header[0], 0, 0, 'C');
        $this->Cell(50, 4, 'Raison', 0, 0, 'C');
        $this->Cell(10, 4, 'Code', 0, 0, 'C');
        $this->Cell(15, 4, 'Activité', 0, 0, 'C');
        $this->Cell(15, 4, 'Numéro', 0, 0, 'C');
        $this->Cell(15, 4, 'Date', 0, 0, 'C');
        $this->Cell(10, 4, 'Code', 0, 0, 'C');
        $this->Cell(50, 4, 'Dénomination usuelle', 0, 0, 'C');
        $this->Cell(10, 4, 'Code', 0, 0, 'C');
        $this->Cell(10, 4, 'Centre', 0, 0, 'C');
        $this->Cell(15, 4, 'Quantité', 0, 0, 'C');
        $this->Cell(10, 4, 'Code', 0, 0, 'C');
        $this->Cell(15, 4, 'Coût', 0, 0, 'C');
        $this->Cell(15, 4, 'Montant', 0, 0, 'C');
        $this->Cell(10, 4, 'Centre', 0, 0, 'C');
        $this->Cell(10, 4, 'Centre', 0, 0, 'C');
        $this->ln();
        $this->Cell(20, 4, '', 0, 0, 'C');
        $this->Cell(50, 4, 'sociale', 0, 0, 'C');
        $this->Cell(10, 4, 'postal', 0, 0, 'C');
        $this->Cell(15, 4, '(code NAF)', 0, 0, 'C');
        $this->Cell(15, 4, 'facture', 0, 0, 'C');
        $this->Cell(15, 4, 'facture', 0, 0, 'C');
        $this->Cell(10, 4, 'déchet', 0, 0, 'C');
        $this->Cell(50, 4, 'du déchet', 0, 0, 'C');
        $this->Cell(10, 4, 'D/R', 0, 0, 'C');
        $this->Cell(10, 4, 'trait.', 0, 0, 'C');
        $this->Cell(15, 4, '(en kg)', 0, 0, 'C');
        $this->Cell(10, 4, 'cond.', 0, 0, 'C');
        $this->Cell(15, 4, 'fact.(€/kg)', 0, 0, 'C');
        $this->Cell(15, 4, 'd\'aide (€)', 0, 0, 'C');
        $this->Cell(10, 4, 'entrepo.', 0, 0, 'C');
        $this->Cell(10, 4, 'transit', 0, 0, 'C');
        $this->ln();

        // Restauration des couleurs et de la police
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('Arial', '', 6);

        $x = $this->GetX();
        $y = $this->GetY();
        $h = 0;
        $nbLignes = 0;
        $fill = false;
        // Chargement des données
        foreach ($declarationDetails as $declarationDetail) {
            $this->Cell(20, 4, $declarationDetail['producteurSiret'], 'LR', 0, 'C', $fill);
            $this->Cell(50, 4, $declarationDetail['producteurLibelle'], 'LR', 0, 'L', $fill);
            $this->Cell(10, 4, $declarationDetail['producteurCodePostal'], 'LR', 0, 'C', $fill);
            $this->Cell(15, 4, $declarationDetail['naf'], 'LR', 0, 'C', $fill);
            $this->Cell(15, 4, $declarationDetail['numFacture'], 'LR', 0, 'C', $fill);
            $this->Cell(15, 4, date_format($declarationDetail['dateFacture'],'d/m/Y'), 'LR', 0, 'C', $fill);
            $this->Cell(10, 4, $declarationDetail['dechet'], 'LR', 0, 'C', $fill);
            $this->Cell(50, 4, $declarationDetail['nature'], 'LR', 0, 'L', $fill);
            $this->Cell(10, 4, $declarationDetail['traitFiliere'], 'LR', 0, 'C', $fill);
            $this->Cell(10, 4, $declarationDetail['centreTraitement'], 'LR', 0, 'C', $fill);
            $this->Cell(15, 4, number_format($declarationDetail['quantiteReel'], 0, ',', ' '), 'LR', 0, 'R', $fill);
            $this->Cell(10, 4, $declarationDetail['filiere'], 'LR', 0, 'C', $fill);
            $this->Cell(15, 4, number_format($declarationDetail['coutFacture'], 4, ',', ' '), 'LR', 0, 'R', $fill);
            $this->Cell(15, 4, number_format($declarationDetail['montAide'], 2, ',', ' '), 'LR', 0, 'R', $fill);
            $this->Cell(10, 4, $declarationDetail['centreDepot'], 'LR', 0, 'C', $fill);
            $this->Cell(10, 4, $declarationDetail['centreTransit'], 'LR', 0, 'C', $fill);
            $this->Ln();
            $h = $h + 4;
            $fill = !$fill;
            $nbLignes += 1;
            if ($nbLignes > 29) {
                // rectangle 
                $this->Rect($x, $y, 280, $h, 'D');
                $this->ln();
                // saut de page
                $this->AddPage($dec, 'L');
                $this->ln(5);
                // Restauration des couleurs et de la police
                $this->SetFillColor(224, 235, 255);
                $this->SetTextColor(0, 0, 102);
                $this->SetFont('Arial', '', 8);


                 // En-tête des colonnes
                    $w = array(20, 50, 10, 15, 15, 15, 10, 50, 10, 10, 15, 10, 15, 15, 10, 10);
                    $header = array('Siret', 'Raison sociale', 'Code postal', 'Avtivité (code NAF)', 'N° facture', 'Date de facture', 'Code nomenclature', 'dénomination usuelle du déchet', 'Code D/R', 'Centre traitement', 'Quantité pesée (en kg)', 'Code conditionnement', 'Coût facturé (€/kg)', 'Montant de l\'aide (€)', 'Centre entreposage', 'centre transit');
                    // En-tête
                    for ($i = 0; $i < count($header); $i++) {
                        $this->Cell($w[$i], 10, '', 1, 0, 'C', true);
                    }
                    //$this->Ln();
                    $x = $this->GetX();
                    $y = $this->GetY();
                    $this->setX($x - 280);
                    $this->setY($y + 2);
                    $this->Cell(20, 4, $header[0], 0, 0, 'C');
                    $this->Cell(50, 4, 'Raison', 0, 0, 'C');
                    $this->Cell(10, 4, 'Code', 0, 0, 'C');
                    $this->Cell(15, 4, 'Activité', 0, 0, 'C');
                    $this->Cell(15, 4, 'Numéro', 0, 0, 'C');
                    $this->Cell(15, 4, 'Date', 0, 0, 'C');
                    $this->Cell(10, 4, 'Code', 0, 0, 'C');
                    $this->Cell(50, 4, 'Dénomination usuelle', 0, 0, 'C');
                    $this->Cell(10, 4, 'Code', 0, 0, 'C');
                    $this->Cell(10, 4, 'Centre', 0, 0, 'C');
                    $this->Cell(15, 4, 'Quantité', 0, 0, 'C');
                    $this->Cell(10, 4, 'Code', 0, 0, 'C');
                    $this->Cell(15, 4, 'Coût', 0, 0, 'C');
                    $this->Cell(15, 4, 'Montant', 0, 0, 'C');
                    $this->Cell(10, 4, 'Centre', 0, 0, 'C');
                    $this->Cell(10, 4, 'Centre', 0, 0, 'C');
                    $this->ln();
                    $this->Cell(20, 4, '', 0, 0, 'C');
                    $this->Cell(50, 4, 'sociale', 0, 0, 'C');
                    $this->Cell(10, 4, 'postal', 0, 0, 'C');
                    $this->Cell(15, 4, '(code NAF)', 0, 0, 'C');
                    $this->Cell(15, 4, 'facture', 0, 0, 'C');
                    $this->Cell(15, 4, 'facture', 0, 0, 'C');
                    $this->Cell(10, 4, 'déchet', 0, 0, 'C');
                    $this->Cell(50, 4, 'du déchet', 0, 0, 'C');
                    $this->Cell(10, 4, 'D/R', 0, 0, 'C');
                    $this->Cell(10, 4, 'trait.', 0, 0, 'C');
                    $this->Cell(15, 4, '(en kg)', 0, 0, 'C');
                    $this->Cell(10, 4, 'cond.', 0, 0, 'C');
                    $this->Cell(15, 4, 'fact.(€/kg)', 0, 0, 'C');
                    $this->Cell(15, 4, 'd\'aide (€)', 0, 0, 'C');
                    $this->Cell(10, 4, 'entrepo.', 0, 0, 'C');
                    $this->Cell(10, 4, 'transit', 0, 0, 'C');
                    $this->ln();

                // Restauration des couleurs et de la police
                $this->SetFillColor(224, 235, 255);
                $this->SetTextColor(0);
                $this->SetFont('Arial', '', 6);

                $x = $this->GetX();
                $y = $this->GetY();
                $h = 0;
                $nbLignes = 0;
                $fill = false;
            }
        }

        // rectangle 
        $this->Rect($x, $y, 280, $h, 'D');
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

?>
