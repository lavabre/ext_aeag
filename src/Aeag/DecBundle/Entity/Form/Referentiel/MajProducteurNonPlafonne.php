<?php

namespace Aeag\DecBundle\Entity\Form\Referentiel;

use Symfony\Component\Validator\Constraints as Assert;

class MajProducteurNonPlafonne {

    /**
     */
    private $id;

    /**
     * @Assert\Regex(pattern="/^[0-9]{14,14}?$/", message="Le siret doit faire 14 caractères"))
     * @Assert\NotBlank(message=" Le siret est obligatoire")
     */
    private $siret;

    /**
     *
     * @Assert\NotBlank()
     */
    private $libelle;

    /**
     *
     */
    private $corId;

    /**
     */
    private $Correspondant;

    /**
     *
     */
    private $aidable;

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

}
