<?php

namespace Aeag\DieBundle\Entity;

use Symfony\Component\Validator\Constraints\All;
use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="Historique")
 */
class Historique {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="historique_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     *
     * @ORM\Column(name="date_creation", type="date")
     * @Assert\NotBlank()
     * 
     */
    private $dateCreation;

    /**
     *
     * @ORM\Column(name="nom", type="string", length=30)
     * @Assert\NotBlank()
     * @assert\Length(max=30)
     */
    private $nom;

    /**
     * @ORM\Column(name="prenom", type="string", length=30)
     * @Assert\NotBlank()
     * @Assert\Length(max=30)
     */
    private $prenom;

    /**
     * @ORM\Column(name="organisme", type="string", length=30)
     * @Assert\NotBlank()
     * @assert\Length(max=30)
     */
    private $organisme;

    /**
     * @ORM\Column(name="email", type="string")
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(name="theme", type="string", length=30)
     * @Assert\NotBlank()
     * @assert\Length(max=30)
     */
    private $theme;

    /**
     * @ORM\Column(name="sous_theme", type="string", length=30)
     * @Assert\NotBlank()
     * @assert\Length(max=30)
     */
    private $sousTheme;

    /**
     * @ORM\Column(name="objet", type="string", length=80)
     * @Assert\NotBlank()
     * @Assert\Length(max=80)
     */
    private $objet;

    /**
     * @ORM\Column(name="corps", type="text")
     * @Assert\NotBlank()
     */
    private $corps;

    /**
     *
     * @ORM\Column(name="date_echeance", type="date")
     * 
     */
    private $dateEcheance;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getDateCreation() {
        return $this->dateCreation;
    }

    public function setDateCreation($dateCreation) {
        $this->dateCreation = $dateCreation;
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

    public function getDateEcheance() {
        return $this->dateEcheance;
    }

    public function setDateEcheance($dateEcheance) {
        $this->dateEcheance = $dateEcheance;
    }

}
