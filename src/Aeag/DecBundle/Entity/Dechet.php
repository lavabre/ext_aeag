<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Dechet
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="Dechet")
 * @ORM\Entity(repositoryClass="Aeag\DecBundle\Repository\DechetRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class Dechet {

     /**
     * @ORM\Id
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\NotBlank()
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     *
     * @Assert\NotBlank()
     */
    private $unite;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     *
     * @Assert\NotBlank()
     */
    private $libelle;

    /**
     * @ORM\Column(type="string",nullable=true)
     *
     */
    private $aidable;

    /**
     * @ORM\Column(type="string",nullable=true)
     *
     */
    private $valide;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     *
     */
    public function __construct() {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedValue() {
        $this->setUpdated(new \DateTime());
    }

  
    public function getCode() {
        return $this->code;
    }

    public function setCode($code) {
        $this->code = $code;
    }

    public function getUnite() {
        return $this->unite;
    }

    public function setUnite($unite) {
        $this->unite = $unite;
    }

    public function getLibelle() {
        return $this->libelle;
    }

    public function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

    public function getAidable() {
        return $this->aidable;
    }

    public function setAidable($aidable) {
        $this->aidable = $aidable;
    }

    public function getValide() {
        return $this->valide;
    }

    public function setValide($valide) {
        $this->valide = $valide;
    }

    public function getCreated() {
        return $this->created;
    }

    public function setCreated($created) {
        $this->created = $created;
    }

    public function getUpdated() {
        return $this->updated;
    }

    public function setUpdated($updated) {
        $this->updated = $updated;
    }

    public function getCodeLibelle() {
        return $this->code . ' : ' . $this->libelle;
    }

}

