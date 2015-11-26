<?php

namespace Aeag\AeagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="Commune",indexes={@ORM\Index(name="commune_idx", columns={"commune"})})
 * @ORM\Entity(repositoryClass="Aeag\AeagBundle\Repository\CommuneRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Commune {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="commune_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $id;

    /**

     * @ORM\Column(name="commune", type="string", length=5, nullable=true)
     */
    private $commune;

    /**
     * @var string $libelle
     *
     * @ORM\Column(name="libelle", type="string", length=100, nullable=true)
     */
    private $libelle;

    /**
     * @var Departement
     *
     * @ORM\ManyToOne(targetEntity="Aeag\AeagBundle\Entity\Departement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dept", referencedColumnName="dept")
     * })
     */
    private $Departement;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * 
     */
    private $dec;

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

    public function getCommuneLibelle() {
        return $this->commune . ' ' . $this->libelle;
    }

    public function getId() {
        return $this->id;
    }

    public function getCommune() {
        return $this->commune;
    }

    public function getLibelle() {
        return $this->libelle;
    }

    public function getDepartement() {
        return $this->Departement;
    }

    public function getDec() {
        return $this->dec;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getUpdated() {
        return $this->updated;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCommune($commune) {
        $this->commune = $commune;
    }

    public function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

    public function setDepartement(Departement $Departement) {
        $this->Departement = $Departement;
    }

    public function setDec($dec) {
        $this->dec = $dec;
    }

    public function setCreated($created) {
        $this->created = $created;
    }

    public function setUpdated($updated) {
        $this->updated = $updated;
    }

}
