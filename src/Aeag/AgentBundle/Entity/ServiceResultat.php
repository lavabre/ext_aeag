<?php

namespace Aeag\AgentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="t_recherche_etat_service",indexes={@ORM\Index(name="matri_idx", columns={"matri"})})
 * @ORM\Entity(repositoryClass="Aeag\AgentBundle\Repository\AgentRepository")
 * 
 */
class ServiceResultat {

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=5)
     */
    private $matri;

    /**
     *
     * @ORM\Column(name="nomPrenom", type="string", length=30, nullable=true)
     */
    private $nomPrenom;

    /**
     *
     * @ORM\Column(name="motif", type="string", length=15, nullable=true)
     */
    private $motif;

    /**
     *
     * @ORM\Column(name="dateRetour", type="string", length=10, nullable=true)
     */
    private $dateRetour;

    /**
     *
     * @ORM\Column(name="etat", type="string", length=10, nullable=true)
     */
    private $etat;

    /**
     *
     * @ORM\Column(name="telAgent", type="string", length=10, nullable=true)
     */
    private $telAgent;

    /**
     *
     * @ORM\Column(name="bureau", type="string", length=10, nullable=true)
     */
    private $bureau;

    /**
     *
     * @ORM\Column(name="telSec", type="string", length=10, nullable=true)
     */
    private $telSec;

    /**
     *
     * @ORM\Column(name="nomSec", type="string", length=30, nullable=true)
     */
    private $nomSec;

    /**
     *
     * @ORM\Column(name="service", type="string", length=100, nullable=true)
     */
    private $service;

    function getMatri() {
        return $this->matri;
    }

    function getNomPrenom() {
        return $this->nomPrenom;
    }

    function getMotif() {
        return $this->motif;
    }

    function getDateRetour() {
        return $this->dateRetour;
    }

    function getEtat() {
        return $this->etat;
    }

    function getTelAgent() {
        return $this->telAgent;
    }

    function getBureau() {
        return $this->bureau;
    }

    function getTelSec() {
        return $this->telSec;
    }

    function getNomSec() {
        return $this->nomSec;
    }

    function setMatri($matri) {
        $this->matri = $matri;
    }

    function setNomPrenom($nomPrenom) {
        $this->nomPrenom = $nomPrenom;
    }

    function setMotif($motif) {
        $this->motif = $motif;
    }

    function setDateRetour($dateRetour) {
        $this->dateRetour = $dateRetour;
    }

    function setEtat($etat) {
        $this->etat = $etat;
    }

    function setTelAgent($telAgent) {
        $this->telAgent = $telAgent;
    }

    function setBureau($bureau) {
        $this->bureau = $bureau;
    }

    function setTelSec($telSec) {
        $this->telSec = $telSec;
    }

    function setNomSec($nomSec) {
        $this->nomSec = $nomSec;
    }

    function getService() {
        return $this->service;
    }

    function setService($service) {
        $this->service = $service;
    }

}
