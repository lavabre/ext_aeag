<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Finalite
 *
 * @author lavabre
 */

namespace Aeag\FrdBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="Finalite", indexes={@ORM\Index(name="finaliteCode_idx", columns={"code"})})
 * @ORM\Entity(repositoryClass="Aeag\FrdBundle\Repository\FinaliteRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class Finalite {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="finalite_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\NotBlank()
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=100)
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

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * @param $code
     */
    public function setCode($code) {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getLibelle() {
        return $this->libelle;
    }

    /**
     * @param $libelle
     */
    public function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

    /**
     * @return string
     */
    public function getCodeLibelle() {
        return $this->code . ' : ' . $this->libelle;
    }

    /**
     * @return mixed
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * @param $created
     */
    public function setCreated($created) {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getUpdated() {
        return $this->updated;
    }

    /**
     * @param $updated
     */
    public function setUpdated($updated) {
        $this->updated = $updated;
    }

}

