<?php

/**
 * Description of  OuvrageFiliere
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="OuvrageFiliere", indexes={@ORM\Index(name="ouvragefiliere_idx", columns={"ouvrage_id"})})
 * @ORM\Entity(repositoryClass="Aeag\DecBundle\Repository\OuvrageFiliereRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class OuvrageFiliere {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="ouvragefiliere_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(name="ouvrage_id",type="integer")
     */
    private $Ouvrage;

    /**
     * @var Filiere
     * @ORM\ManyToOne(targetEntity="Aeag\DecBundle\Entity\Filiere" )
     *  @ORM\JoinColumn(name="filiere_code", referencedColumnName="code")
     */
    private $Filiere;

    /**
     * @ORM\Column(type="integer",nullable=true)
     *
     */
    private $annee;

    /**
     *
     * @ORM\Column(name="validite", type="string", length=1, nullable=true)
     * @Assert\Choice(choices = {"O", "N"}, message="Cette valeur doit être l'un des choix proposés")
     */
    private $validite;

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

    public function getOuvrage() {
        return $this->Ouvrage;
    }

    public function setOuvrage($Ouvrage) {
        $this->Ouvrage = $Ouvrage;
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

    public function getValidite() {
        return $this->validite;
    }

    public function setValidite($validite) {
        $this->validite = $validite;
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

