<?php

namespace Aeag\AideBundle\Entity\Form;

use Symfony\Component\Validator\Constraints as Assert;

class CriteresRequest {

    private $ligne;
    private $cate;
    private $debutAnnee;
    private $finAnnee;
    private $regionAdmin;
    private $departement;
    private $regionHydro;

    public function getLigne() {
        return $this->ligne;
    }

    public function getCate() {
        return $this->cate;
    }

    public function getDebutAnnee() {
        return $this->debutAnnee;
    }

    public function getFinAnnee() {
        return $this->finAnnee;
    }

    public function getRegionAdmin() {
        return $this->regionAdmin;
    }

    public function getDepartement() {
        return $this->departement;
    }

    public function getRegionHydro() {
        return $this->regionHydro;
    }

    public function setLigne($ligne) {
        $this->ligne = $ligne;
    }

    public function setCate($cate) {
        $this->cate = $cate;
    }

    public function setDebutAnnee($debutAnnee) {
        $this->debutAnnee = $debutAnnee;
    }

    public function setFinAnnee($finAnnee) {
        $this->finAnnee = $finAnnee;
    }

    public function setRegionAdmin($regionAdmin) {
        $this->regionAdmin = $regionAdmin;
    }

    public function setDepartement($departement) {
        $this->departement = $departement;
    }

    public function setRegionHydro($regionHydro) {
        $this->regionHydro = $regionHydro;
    }

}
