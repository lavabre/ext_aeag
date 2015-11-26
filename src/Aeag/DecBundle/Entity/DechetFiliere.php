<?php

/**
 * Description of DechetFiliere
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="DechetFiliere", indexes={@ORM\Index(name="dechet_idx", columns={"dechet_code"}),@ORM\Index(name="filiere_idx", columns={"filiere_code"})})
 * @ORM\Entity(repositoryClass="Aeag\DecBundle\Repository\DechetFiliereRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class DechetFiliere {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="dechetfiliere_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Dechet
     * @ORM\ManyToOne(targetEntity="Aeag\DecBundle\Entity\Dechet" )
     * @ORM\JoinColumn(name="dechet_code", referencedColumnName="code")
     */
    private $Dechet;

    /**
     * @var Filiere
     * @ORM\ManyToOne(targetEntity="Aeag\DecBundle\Entity\Filiere" )
     * @ORM\JoinColumn(name="filiere_code", referencedColumnName="code")
     */
    private $Filiere;

    /**
     * @ORM\Column(type="integer",nullable=true)
     *
     */
    private $annee;

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

    public function getDechet() {
        return $this->Dechet;
    }

    public function setDechet(Dechet $Dechet) {
        $this->Dechet = $Dechet;
    }

    public function getFiliere() {
        return $this->Filiere;
    }

    public function setFiliere(Filiere $Filiere) {
        $this->Filiere = $Filiere;
    }

    public function getAnnee() {
        return $this->annee;
    }

    public function setAnnee($annee) {
        $this->annee = $annee;
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

