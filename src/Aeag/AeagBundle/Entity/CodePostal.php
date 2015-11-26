<?php

namespace Aeag\AeagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="CodePostal",indexes={@ORM\Index(name="communecp_idx", columns={"commune_id", "cp",})})
 * @ORM\Entity(repositoryClass="Aeag\AeagBundle\Repository\CodePostalRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class CodePostal {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="codePostal_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Commune
     * @ORM\ManyToOne(targetEntity="Aeag\AeagBundle\Entity\Commune" )
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id")
     */
    private $commune;

    /**

     * @ORM\Column(name="cp", type="string", length=5, nullable=true)
     */
    private $cp;

    /**
     * @var string $libelle
     *
     * @ORM\Column(name="acheminement", type="string", length=100, nullable=true)
     */
    private $acheminement;

    /**
     * @var string $libelle
     *
     * @ORM\Column(name="localite", type="string", length=100, nullable=true)
     */
    private $localite;

    /**
     * @ORM\Column(type="string",nullable=true)
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

    public function getId() {
        return $this->id;
    }

    public function getCommune() {
        return $this->commune;
    }

    public function getCp() {
        return $this->cp;
    }

    public function getAcheminement() {
        return $this->acheminement;
    }

    public function getLocalite() {
        return $this->localite;
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

    public function setCommune(Commune $commune) {
        $this->commune = $commune;
    }

    public function setCp($cp) {
        $this->cp = $cp;
    }

    public function setAcheminement($acheminement) {
        $this->acheminement = $acheminement;
    }

    public function setLocalite($localite) {
        $this->localite = $localite;
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
