<?php

namespace Aeag\AeagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="interlocuteur",indexes={@ORM\Index(name="nom_idx", columns={"nom"}),@ORM\Index(name="correspondant_idx", columns={"correspondant"})})
 * @ORM\Entity(repositoryClass="Aeag\AeagBundle\Repository\InterlocuteurRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Interlocuteur {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="correspondant_seq", initialValue=1, allocationSize=100)
     */
    private $id;

    /**
     * @ORM\Column(type="integer", length=10, nullable=true)
     */
    private $correspondant;

    /**
     *
     * @ORM\Column(name="nom", type="string", length=100, nullable=true)
     */
    private $nom;

    /**
     *
     * @ORM\Column(name="prenom", type="string", length=100, nullable=true)
     */
    private $prenom;

    /**
     *
     * @ORM\Column(name="fonction", type="string", length=100, nullable=true)
     */
    private $fonction;

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

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedValue() {
        $this->setUpdated(new \DateTime());
    }

    public function getId() {
        return $this->id;
    }

    public function getCorrespondant() {
        return $this->correspondant;
    }

    public function getNom() {
        return $this->nom;
    }

    public function getPrenom() {
        return $this->prenom;
    }

    public function getFonction() {
        return $this->fonction;
    }

    public function getTel() {
        return $this->tel;
    }

    public function getEmail() {
        return $this->email;
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

    public function setCorrespondant($correspondant) {
        $this->correspondant = $correspondant;
    }

    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function setPrenom($prenom) {
        $this->prenom = $prenom;
    }

    public function setFonction($fonction) {
        $this->fonction = $fonction;
    }

    public function setTel($tel) {
        $this->tel = $tel;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setCreated($created) {
        $this->created = $created;
    }

    public function setUpdated($updated) {
        $this->updated = $updated;
    }

}
