<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Taux
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="Taux",indexes={@ORM\Index(name="tauxCode_idx", columns={"code"})})
 * @ORM\Entity(repositoryClass="Aeag\DecBundle\Repository\TauxRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class Taux {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="taux_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="integer", length=4)
     *
     * @Assert\NotBlank()
     */
    private $annee;

    /**
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
     * @ORM\Column(type="float",nullable=true)
     *
     */
    private $valeur;

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

    public function getAnnee() {
        return $this->annee;
    }

    public function setAnnee($annee) {
        $this->annee = $annee;
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

    public function getValeur() {
        return $this->valeur;
    }

    public function setValeur($valeur) {
        $this->valeur = $valeur;
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

}

