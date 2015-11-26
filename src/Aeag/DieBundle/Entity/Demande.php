<?php

namespace Aeag\DieBundle\Entity;

use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="Demande")
 */
class Demande {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="demande_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     *
     * @ORM\Column(name="nom", type="string", length=30)
     * @Assert\NotBlank(message="Le nom de famille est obligatoire")
     * @Assert\LessThan(value=30, message = "Le nom doit faire moins de 30 caractères") 
     */
    private $nom;

    /**
     * @ORM\Column(name="prenom", type="string", length=30)
     * @Assert\NotBlank(message = "Le prénom est obligatoire")
     * @Assert\LessThan(value=30, message = "Le prénom doit faire moins de 30 caractères") 
     */
    private $prenom;

    /**
     * @ORM\Column(name="dept", type="string", length=150, nullable=true)
     * @Assert\NotBlank(message = "Le département est obligatoire")
     */
    private $dept;

    /**
     * @ORM\Column(name="organisme", type="string", length=150)
     * @Assert\NotBlank(message = "L'organisme est obligatoire")
     */
    private $organisme;

    /**
     * @ORM\Column(name="email", type="string")
     * @Assert\NotBlank(message = "L'adresse mail est obligatoire")
     * @Assert\Email(message = "L'adresse mail n'est pas valide")
     */
    private $email;

    /**
     * @ORM\Column(name="theme", type="string", length=150)
     */
    private $theme;

    /**
     * @ORM\Column(name="sousTheme", type="string", length=150)
     * @Assert\LessThan(value=150, message = "Le sous thème doit faire moins de 150 caractères") 
     */
    private $sousTheme;

    /**
     * @ORM\Column(name="objet", type="string", length=80)
     * @Assert\NotBlank(message = "L'objet est obligatoire")
     * @Assert\LessThan(value=80, message = "Lobjet doit faire moins de 80 caractères") 
     */
    private $objet;

    /**
     * @ORM\Column(name="corps", type="text")
     * @Assert\NotBlank(message = "Le corps est obligatoire")
     *
     */
    private $corps;

    /**
     * 
     * @ORM\Column(name="dateCreation", type="date")
     *
     */
    private $dateCreation;

    /**
     * 
     * @ORM\Column(name="dateEcheance", type="date")
     * 
     *
     */
    private $dateEcheance;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getNom() {
        return $this->nom;
    }

    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function getPrenom() {
        return $this->prenom;
    }

    public function setPrenom($prenom) {
        $this->prenom = $prenom;
    }

    public function getDept() {
        return $this->dept;
    }

    public function setDept($dept) {
        $this->dept = $dept;
    }

    public function getOrganisme() {
        return $this->organisme;
    }

    public function setOrganisme($organisme) {
        $this->organisme = $organisme;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getTheme() {
        return $this->theme;
    }

    public function setTheme($theme) {
        $this->theme = $theme;
    }

    public function getSousTheme() {
        return $this->sousTheme;
    }

    public function setSousTheme($sousTheme) {
        $this->sousTheme = $sousTheme;
    }

    public function getObjet() {
        return $this->objet;
    }

    public function setObjet($objet) {
        $this->objet = $objet;
    }

    public function getCorps() {
        return $this->corps;
    }

    public function setCorps($corps) {
        $this->corps = $corps;
    }

    public function getDateCreation() {
        return $this->dateCreation;
    }

    public function setDateCreation($dateCreation) {
        $this->dateCreation = $dateCreation;
    }

    public function getDateEcheance() {
        return $this->dateEcheance;
    }

    public function setDateEcheance($dateEcheance) {
        $this->dateEcheance = $dateEcheance;
    }

}
