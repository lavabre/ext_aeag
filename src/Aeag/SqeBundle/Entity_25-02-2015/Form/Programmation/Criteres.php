<?php

namespace Aeag\SqeBundle\Entity\Form\Programmation;

use Symfony\Component\Validator\Constraints as Assert;

class Criteres {

    /**
     *
     * @Assert\Type(type="numeric", message="L'année doit être une valeur numerique")
     * @Assert\Regex(pattern="/^[0-9]{4,4}?$/", message="l'année doit être saisie sur 4 chiffres"))
     */
    private $annee;
    private $webuser;
    private $typeMarche;
    private $marche;
    private $titulaire;
    private $zoneGeoRef;
    private $catMilieu;
    private $typeMilieu;
    private $lot;
    private $message;

    function getAnnee() {
        return $this->annee;
    }

    function getWebuser() {
        return $this->webuser;
    }

    function getTypeMarche() {
        return $this->typeMarche;
    }

    function getMarche() {
        return $this->marche;
    }

    function getTitulaire() {
        return $this->titulaire;
    }

    function getZoneGeoRef() {
        return $this->zoneGeoRef;
    }
    
    function getCatMilieu() {
        return $this->catMilieu;
    }

    function getTypeMilieu() {
        return $this->typeMilieu;
    }

    function getLot() {
        return $this->lot;
    }

    function getMessage() {
        return $this->message;
    }

    function setAnnee($annee) {
        $this->annee = $annee;
    }

    function setWebuser($webuser) {
        $this->webuser = $webuser;
    }

    function setTypeMarche($typeMarche) {
        $this->typeMarche = $typeMarche;
    }

    function setMarche($marche) {
        $this->marche = $marche;
    }

    function setTitulaire($titulaire) {
        $this->titulaire = $titulaire;
    }

    function setZoneGeoRef($zoneGeoRef) {
        $this->zoneGeoRef = $zoneGeoRef;
    }
    
    function setCatMilieu($catMilieu) {
        $this->catMilieu = $catMilieu;
    }

    function setTypeMilieu($typeMilieu) {
        $this->typeMilieu = $typeMilieu;
    }

    function setLot($lot) {
        $this->lot = $lot;
    }

    function setMessage($message) {
        $this->message = $message;
    }


}
