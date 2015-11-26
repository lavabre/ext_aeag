<?php

/**
 * Description of ProducteurNonPlafonne
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="ProducteurNonPlafonne",indexes={@ORM\Index(name="siret_idx", columns={"siret"})})
 * @ORM\Entity(repositoryClass="Aeag\DecBundle\Repository\ProducteurNonPlafonneRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class ProducteurNonPlafonne {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="producteurnonplafonne_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     *
     * @Assert\NotBlank()
     */
    private $siret;

    /**
     * @ORM\Column(type="string", length=200)
     *
     * @Assert\NotBlank()
     */
    private $libelle;

    /**
     * @ORM\Column(type="integer", length=11, nullable=true)
     *
     */
    private $corId;

    /**
     * @ORM\Column(name="correspondant_id",type="integer", nullable=true)
     */
    private $Correspondant;

    /**
     * @ORM\Column(type="string",nullable=true)
     *
     */
    private $aidable;

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

    public function getSiret() {
        return $this->siret;
    }

    public function setSiret($siret) {
        $this->siret = $siret;
    }

    public function getLibelle() {
        return $this->libelle;
    }

    public function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

    public function getCorId() {
        return $this->corId;
    }

    public function setCorId($corId) {
        $this->corId = $corId;
    }

    public function getCorrespondant() {
        return $this->Correspondant;
    }

    public function setCorrespondant($Correspondant) {
        $this->Correspondant = $Correspondant;
    }

    public function getAidable() {
        return $this->aidable;
    }

    public function setAidable($aidable) {
        $this->aidable = $aidable;
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
