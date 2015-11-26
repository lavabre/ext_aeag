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

namespace Aeag\FrdBundle\DependencyInjection;

use Aeag\FrdBundle\DependencyInjection\fpdf\PageGroup;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Aeag\FrdBundle\Entity\FraisDeplacement;

class PDF extends PageGroup {

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
        $this->Cell(1, 12, 'Frais de déplacement n° ' . $entity[0]->getId(), 0, 1, 'C');
        $this->Cell(100);
        $this->Cell(1, 1, 'de ' . $entity[1]->getPrenom() . ' ' . $entity[1]->getUsername(), 0, 1, 'C');
        //date
        $this->Cell(170);
        $this->SetFont('Arial', '', 8);
        $this->Cell(30, 12, date('d/m/o'), 0, 1, 'C');

        // Statut
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(100);
        if ($entity[0]->getPhase()->getCode() == '60') {
            $this->Cell(1, 12, $entity[0]->getPhase()->getLibelle() . ' le ' . $entity[0]->getDatePaiement()->format('d/m/Y'), 0, 1, 'C');
        } else {
            $this->Cell(1, 12, $entity[0]->getPhase()->getLibelle(), 0, 1, 'C');
        }
    }

    function Footer() {
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 102);
        $this->SetDrawColor(0, 0, 0);
        $this->SetY(-20);
        //$this->Cell(0, 4, 'Page ' . $this->GroupPageNo() . '/' . $this->PageGroupAlias(), 0, 0, 'C');
        $this->Cell(0, 4, 'Agence de l\'eau Adour-Garonne - 90, rue du férétra - 31078 Toulouse Cedex 4 - Tél. : 05 61 36 37 38', 0, 0, 'C');
    }

    // Chargement des données
    function LoadData($file) {
        // Lecture des lignes du fichier
        $lines = file($file);
        $data = array();
        foreach ($lines as $line)
            $data[] = explode(';', trim($line));
        return $data;
    }

// Tableau simple
    function BasicTable($header, $data) {
        // En-tête
        foreach ($header as $col)
            $this->Cell(40, 7, $col, 1);
        $this->Ln();
        // Données
        foreach ($data as $row) {
            foreach ($row as $col)
                $this->Cell(40, 4, $col, 1);
            $this->Ln();
        }
    }

// Tableau amélioré
    function ImprovedTable($header, $data) {
        // Largeurs des colonnes
        $w = array(12, 15, 30, 30, 30);
        // En-tête
        for ($i = 0; $i < count($header); $i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
        $this->Ln();
        // Données
        foreach (is_array($data[dossiers]) || is_object($data[dossiers]) ? $data[dossiers] : array() as $row) {
            $this->Cell($w[0], 4, $row->getLigne()->getLigne(), 0);
            $this->Cell($w[1], 4, number_format($row->getMontant_aide_interne(), 0, ',', ' '), 0, 0, 'R');
            $this->Cell($w[2], 4, $row->getForme_aide(), 0);
            $this->Cell($w[3], 4, $row->getRaison_sociale(), 0);
            $this->Cell($w[4], 4, $row->getIntitule(), 0);
            $this->Ln();
        }
        // Trait de terminaison
        $this->Cell(array_sum($w), 0, '', 'T');
    }

// Tableau coloré
    function Formatage($entity) {

        $this->ln(5);

        // Restauration des couleurs et de la police
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0, 0, 102);
        $this->SetFont('Arial', '', 8);

        $nb = 0;
        $x = $this->GetX();
        $y = $this->GetY();
        $fill = false;

        // Objet
        $this->SetFont('Arial', 'B');
        // En-tête des colonnes
        $w = array(190);
        $header = array($entity[0]->getObjet() . ' du ' . $entity[0]->getDateDepart()->format('d/m/Y') . ' ' . $entity[0]->getHeureDepart() . ' au ' . $entity[0]->getDateRetour()->format('d/m/Y') . ' ' . $entity[0]->getHeureRetour());
        $this->Cell($w[0], 5, $header[0], 1, 0, 'L', true);
        $this->Ln();
        $nb += 5;

        // Restauration des couleurs et de la police
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('Arial');

        $w = array(50, 150);
        $this->Cell($w[0], 4, 'Finalité du déplacement ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, $entity[0]->getFinalite()->getLibelle(), 0, 1, 'L', $fill);
        $fill = $fill;
        $nb += 4;

        if ($entity[0]->getSousTheme()) {
            $this->Cell($w[0], 4, 'Sous_thème ', 0, 0, 'L', $fill);
            $this->Cell($w[1], 4, $entity[0]->getSousTheme()->getlibelle(), 0, 1, 'L', $fill);
            $fill = $fill;
            $nb += 4;
        }

        $this->Cell($w[0], 4, 'Itinéraire ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, $entity[0]->getItineraire(), 0, 1, 'L', $fill);
        $fill = $fill;
        $nb += 4;

        $this->Cell($w[0], 4, 'Département du lieu de mission ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, $entity[2]->getDept() . ' ' . $entity[2]->getLibelle(), 0, 1, 'L', $fill);
        $fill = $fill;
        $nb += 4;

        // rectangle 
        $this->Rect($x, $y, 190, $nb, 'D');
        $this->ln(2);


        // Restauration des couleurs et de la police
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0, 0, 102);
        $this->SetFont('Arial', '', 8);

        $nb = 0;
        $x = $this->GetX();
        $y = $this->GetY();
        $fill = false;

        // Frais engagés
        $this->SetFont('Arial', 'B');
        // En-tête des colonnes
        $w = array(190);
        $header = array('Frais engagés');
        $this->Cell($w[0], 5, $header[0], 1, 0, 'L', true);
        $this->Ln();
        $nb += 5;

        // Transport
        $this->SetFont('Arial', 'B');
        // En-tête des colonnes
        $w = array(190);
        $header = array('Transport');
        $this->Cell($w[0], 5, $header[0], 1, 0, 'L', true);
        $this->Ln();
        $nb += 5;

        // Restauration des couleurs et de la police
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('Arial');

        $w = array(50, 45, 45, 50);
        $this->Cell($w[0], 4, ' ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, 'Tickets joints', 0, 0, 'R', $fill);
        $this->Cell($w[2], 4, 'Tickets non joints', 0, 0, 'R', $fill);
        $this->Cell($w[3], 4, 'Montants', 0, 1, 'R', $fill);
        $nb += 4;

        // Parking
        $this->Cell($w[0], 4, 'Parking ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, number_format($entity[0]->getParkJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[2], 4, number_format($entity[0]->getParkNonJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[3], 4, number_format($entity[0]->getParkTotal(), 2, ',', ' ') . ' €', 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;

        // Péage
        $this->Cell($w[0], 4, 'Péage ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, number_format($entity[0]->getPeageJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[2], 4, number_format($entity[0]->getPeageNonJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[3], 4, number_format($entity[0]->getPeageTotal(), 2, ',', ' ') . ' €', 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;

        // Péage
        $this->Cell($w[0], 4, 'Transport en commun ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, number_format($entity[0]->getBusMetroJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[2], 4, number_format($entity[0]->getBusMetroNonJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[3], 4, number_format($entity[0]->getBusMetroTotal(), 2, ',', ' ') . ' €', 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;

        // Orlyval
        $this->Cell($w[0], 4, 'Orlyval ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, number_format($entity[0]->getOrlyvalJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[2], 4, number_format($entity[0]->getOrlyvalNonJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[3], 4, number_format($entity[0]->getOrlyvalTotal(), 2, ',', ' ') . ' €', 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;

        // Avion (et/ou bateau)
        $this->Cell($w[0], 4, 'Orlyval ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, number_format($entity[0]->getAvionJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[2], 4, number_format($entity[0]->getAvionNonJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[3], 4, number_format($entity[0]->getAvionTotal(), 2, ',', ' ') . ' €', 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;

        // Véhicule de location
        $this->Cell($w[0], 4, 'Véhicule de location ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, number_format($entity[0]->getReservationJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[2], 4, number_format($entity[0]->getReservationNonJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[3], 4, number_format($entity[0]->getReservationTotal(), 2, ',', ' ') . ' €', 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;

        // Taxi
        $this->Cell($w[0], 4, 'Taxi ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, number_format($entity[0]->getTaxiJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[2], 4, number_format($entity[0]->getTaxiNonJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[3], 4, number_format($entity[0]->getTaxiTotal(), 2, ',', ' ') . ' €', 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;

        // Train
        $libelle = 'Train classe : ';
        if ($entity[0]->getTrainClasse()) {
            $libelle = 'Train classe : ' . number_format($entity[0]->getTrainClasse(), 0, ',', ' ');
        } else {
            $libelle = 'Train ';
        }
        if ($entity[0]->getTrainCouchette() == 'O') {
            $libelle = $libelle . ' Couchette : Oui';
        } else {
            $libelle = $libelle . ' Couchette : Non';
        }
        $this->Cell($w[0], 4, $libelle, 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, number_format($entity[0]->getTrainJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[2], 4, number_format($entity[0]->getTrainNonJustif(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[3], 4, number_format($entity[0]->getTrainTotal(), 2, ',', ' ') . ' €', 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;

        // Moto
        $this->Cell($w[0], 4, 'Moto ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, ' ', 0, 0, 'L', $fill);
        $this->Cell($w[2], 4, ' ', 0, 0, 'L', $fill);
        $this->Cell($w[3], 4, number_format($entity[0]->getKmMoto(), 0, ',', ' ') . ' Km', 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;

        // Véhicule personnel (joindre la photocopie de la carte grise)
        $w = array(140, 50);
        $this->Cell($w[0], 4, 'Véhicule personnel (joindre la photocopie de la carte grise) ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, number_format($entity[0]->getKmVoiture(), 0, ',', ' ') . ' Km', 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;


        // Repas
        $this->SetFont('Arial', 'B');
        // En-tête des colonnes
        $w = array(190);
        $header = array('Repas');
        $this->Cell($w[0], 5, $header[0], 1, 0, 'L', true);
        $this->Ln();
        $nb += 5;

        // Restauration des couleurs et de la police
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('Arial');

        $w = array(90, 50, 50);
        $fill = false;
        $this->Cell($w[0], 4, ' ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, 'Restaurant', 0, 0, 'R', $fill);
        $this->Cell($w[2], 4, 'Offerts ou sans frais', 0, 1, 'R', $fill);
        $nb += 4;

        // Nombre de repas de midi en semaine
        $this->Cell($w[0], 4, 'Nombre de repas de midi en semaine ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, number_format($entity[0]->getAutreMidiSem(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[2], 4, number_format($entity[0]->getOffertMidiSem(), 0, ',', ' '), 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;

        // Nombre de repas de midi en week-end
        $this->Cell($w[0], 4, 'Nombre de repas de midi en week-end ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, number_format($entity[0]->getAutreMidiWeek(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[2], 4, number_format($entity[0]->getOffertMidiWeek(), 0, ',', ' '), 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;

        // Nombre de repas du soir
        $this->Cell($w[0], 4, 'Nombre de repas du soir ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, number_format($entity[0]->getAutreSoir(), 0, ',', ' '), 0, 0, 'R', $fill);
        $this->Cell($w[2], 4, number_format($entity[0]->getOffertSoir(), 0, ',', ' '), 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;

        // Nuité
        $this->SetFont('Arial', 'B');
        // En-tête des colonnes
        $w = array(190);
        $header = array('Nuité');
        $this->Cell($w[0], 5, $header[0], 1, 0, 'L', true);
        $this->Ln();
        $nb += 5;

        // Restauration des couleurs et de la police
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('Arial');

        $w = array(140, 50);
        $fill = false;
        $this->Cell($w[0], 4, ' ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, 'Nombre de nuitées', 0, 1, 'R', $fill);
        $nb += 4;

        // Justifiés (factures fournies)
        $this->Cell($w[0], 4, 'Justifiés (factures fournies) ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, number_format($entity[0]->getProvenceJustif(), 0, ',', ' '), 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;

        // Nuitées offertes ou sans frais
        $this->Cell($w[0], 4, 'Nuitées offertes ou sans frais ', 0, 0, 'L', $fill);
        $this->Cell($w[1], 4, number_format($entity[0]->getOffertNuit(), 0, ',', ' '), 0, 1, 'R', $fill);
        $fill = !$fill;
        $nb += 4;

        // Hébergement administratif
        $this->Cell($w[0], 4, 'Hébergement administratif ', 0, 0, 'L', $fill);
        if ($entity[0]->getAdminNuit() == 'O') {
            $this->Cell($w[1], 4, 'Oui', 0, 1, 'R', $fill);
        } else {
            $this->Cell($w[1], 4, 'Non', 0, 1, 'R', $fill);
        }
        $fill = !$fill;
        $nb += 4;

        // rectangle 
        $this->Rect($x, $y, 190, $nb, 'D');
        $this->ln(2);

        if ($entity[0]->getPhase()->getCode() == '20') {
            // Couleurs, épaisseur du trait et police grasse
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor(0, 0, 102);
            $this->SetDrawColor(0, 0, 0);
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(100);
            $this->Cell(1, 12, 'A TRANSMETTRE L\'ORIGINAL AVEC LES JUSTIFICATIFS ORIGINAUX ET UN RIB A :', 0, 1, 'C');
            $this->Cell(100);
            $this->Cell(1, 4, 'AGENCE DE L\'EAU "ADOUR-GARONNE"', 0, 1, 'C');
            $this->Cell(100);
            $this->SetFont('Arial', 'B', 8);
            $this->Cell(1, 4, '(DEPARTEMENT RESSOURCES HUMAINES)', 0, 1, 'C');
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(100);
            $this->Cell(1, 4, '90 RUE DU FERETRA', 0, 1, 'C');
            $this->Cell(100);
            $this->Cell(1, 4, 'CS 87801', 0, 1, 'C');
            $this->Cell(100);
            $this->Cell(1, 4, '31078 TOULOUSE CEDEX 4', 0, 1, 'C');
            $this->Cell(110);
            $this->Cell(1, 30, 'DATE : 	                        FAIT A :', 0, 1, 'C');
            $this->Cell(110);
            $this->Cell(1, 10, 'SIGNATURE OBLIGATOIRE :', 0, 1, 'C');
        }
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
