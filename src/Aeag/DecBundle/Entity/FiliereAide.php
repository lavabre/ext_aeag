<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RefFiliere
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="FiliereAide")
 * @ORM\Entity(repositoryClass="Aeag\DecBundle\Repository\FiliereAideRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class FiliereAide {

  
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\NotBlank()
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=200)
     *
     * @Assert\NotBlank()
     */
    private $libelle;

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

    public function getLibelle() {
        return $this->libelle;
    }

    public function setLibelle($libelle) {
        $this->libelle = $libelle;
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

