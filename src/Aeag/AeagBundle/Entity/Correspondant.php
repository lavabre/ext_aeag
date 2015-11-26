<?php

namespace Aeag\AeagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="correspondant",indexes={@ORM\Index(name="identifiant_idx", columns={"identifiant"})})
 * @ORM\Entity(repositoryClass="Aeag\AeagBundle\Repository\CorrespondantRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Correspondant {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="correspondant_seq", initialValue=1, allocationSize=100)
     */
    private $id;

     /**
     *
     * @ORM\Column(name="corid", type="integer")
     * @Assert\NotBlank(message="Le corid  est obligatoire")
     */
    private $corId;

    /**
     *
     * @ORM\Column(name="identifiant", type="string", length=20)
     * @Assert\NotBlank(message="L'identifiant est obligatoire")
     */
    private $identifiant;

    /**
     *
     * @ORM\Column(name="siret", type="string", length=14, nullable=true)
     */
    private $siret;

    /**
     *
     * @ORM\Column(name="adr1", type="string", length=100, nullable=true)
     */
    private $adr1;

    /**
     *
     * @ORM\Column(name="adr2", type="string", length=100, nullable=true)
     */
    private $adr2;

    /**
     *
     * @ORM\Column(name="adr3", type="string", length=100, nullable=true)
     */
    private $adr3;

    /**
     *
     * @ORM\Column(name="adr4", type="string", length=100, nullable=true)
     */
    private $adr4;

    /**
     *
     * @ORM\Column(name="cp", type="string", length=5, nullable=true)
     */
    private $cp;

    /**
     *
     * @ORM\Column(name="ville", type="string", length=100, nullable=true)
     */
    private $ville;

    /**
     *
     * @ORM\Column(name="tel", type="string", length=20, nullable=true)
     */
    private $tel;

    /**
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Assert\Email
     */
    private $email;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    public function __construct() {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getCorId() {
        return $this->corId;
    }

    public function setCorId($corId) {
        $this->corId = $corId;
    }

    public function getIdentifiant() {
        return $this->identifiant;
    }

    public function setIdentifiant($identifiant) {
        $this->identifiant = $identifiant;
    }

    public function getSiret() {
        return $this->siret;
    }

    public function setSiret($siret) {
        $this->siret = $siret;
    }

    public function getAdr1() {
        return $this->adr1;
    }

    public function setAdr1($adr1) {
        $this->adr1 = $adr1;
    }

    public function getAdr2() {
        return $this->adr2;
    }

    public function setAdr2($adr2) {
        $this->adr2 = $adr2;
    }

    public function getAdr3() {
        return $this->adr3;
    }

    public function setAdr3($adr3) {
        $this->adr3 = $adr3;
    }

    public function getAdr4() {
        return $this->adr4;
    }

    public function setAdr4($adr4) {
        $this->adr4 = $adr4;
    }

    public function getCp() {
        return $this->cp;
    }

    public function setCp($cp) {
        $this->cp = $cp;
    }

    public function getVille() {
        return $this->ville;
    }

    public function setVille($ville) {
        $this->ville = $ville;
    }

    public function getTel() {
        return $this->tel;
    }

    public function setTel($tel) {
        $this->tel = $tel;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
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

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedValue() {
        $this->setUpdated(new \DateTime());
    }

    
}