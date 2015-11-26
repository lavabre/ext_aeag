<?php

namespace Aeag\AeagBundle\Entity\Form;

use Symfony\Component\Validator\Constraints as Assert;

class MajInterlocuteur {

    /**
     *
     * @Assert\NotBlank(message="Le nom est obligatoire")
     */
    private $nom;
    private $prenom;
    private $fonction;
    private $tel;

    /**
     *  @Assert\Email
     * @Assert\NotBlank(message="L'adresse email est obligatoire")
     */
    private $email;

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

}
