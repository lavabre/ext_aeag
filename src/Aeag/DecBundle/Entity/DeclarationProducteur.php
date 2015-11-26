<?php

namespace Aeag\DecBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="declarationProducteur",indexes={@ORM\Index(name="declarationProducteur_idx", columns={"Producteur_id","annee"})})
 * @ORM\Entity(repositoryClass="Aeag\DecBundle\Repository\DeclarationProducteurRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class DeclarationProducteur {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="declarationProducteur_seq", initialValue=1, allocationSize=1)
     */
    private $id;

    /**
     * @ORM\Column(name="producteur_id",type="integer")
     */
    private $Producteur;

    /**
     * @ORM\ManyToOne(targetEntity="Aeag\DecBundle\Entity\Statut" )
     * @ORM\JoinColumn(name="statut", referencedColumnName="code", nullable=true)
     */
    private $statut;

    /**
     *
     * @ORM\Column(name="annee", type="integer", nullable=true)
     */
    private $annee;

    /**
     *
     * @ORM\Column(name="quantiteReel", type="float", length=15, nullable=true)
     */
    private $quantiteReel;

    /**
     *
     * @ORM\Column(name="montReel", type="float", length=15, nullable=true)
     */
    private $montReel;

    /**
     *
     * @ORM\Column(name="quantiteRet", type="float", length=15, nullable=true)
     */
    private $quantiteRet;

    /**
     *
     * @ORM\Column(name="montRet", type="float", length=15, nullable=true)
     */
    private $montRet;

    /**
     *
     * @ORM\Column(name="quantiteAide", type="float", length=15, nullable=true)
     */
    private $quantiteAide;

    /**
     *
     * @ORM\Column(name="montAide", type="float", length=15, nullable=true)
     */
    private $montAide;

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

    public function getProducteur() {
        return $this->Producteur;
    }

    public function setProducteur($Producteur) {
        $this->Producteur = $Producteur;
    }

    public function getStatut() {
        return $this->statut;
    }

    public function setStatut($statut) {
        $this->statut = $statut;
    }

    public function getAnnee() {
        return $this->annee;
    }

    public function setAnnee($annee) {
        $this->annee = $annee;
    }

    public function getQuantiteReel() {
        return $this->quantiteReel;
    }

    public function setQuantiteReel($quantiteReel) {
        $this->quantiteReel = $quantiteReel;
    }

    public function getMontReel() {
        return $this->montReel;
    }

    public function setMontReel($montReel) {
        $this->montReel = $montReel;
    }

    public function getQuantiteRet() {
        return $this->quantiteRet;
    }

    public function setQuantiteRet($quantiteRet) {
        $this->quantiteRet = $quantiteRet;
    }

    public function getMontRet() {
        return $this->montRet;
    }

    public function setMontRet($montRet) {
        $this->montRet = $montRet;
    }

    public function getQuantiteAide() {
        return $this->quantiteAide;
    }

    public function setQuantiteAide($quantiteAide) {
        $this->quantiteAide = $quantiteAide;
    }

    public function getMontAide() {
        return $this->montAide;
    }

    public function setMontAide($montAide) {
        $this->montAide = $montAide;
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