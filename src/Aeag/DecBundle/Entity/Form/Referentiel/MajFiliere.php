<?php

namespace Aeag\DecBundle\Entity\Form\Referentiel;

use Symfony\Component\Validator\Constraints as Assert;

class MajFiliere {

    /**
     * @Assert\Type( type="string")
     * @Assert\NotBlank(message="Le code est obligatoire")
     */
    private $code;
    /**
     * @Assert\Type( type="string")
     * @Assert\NotBlank(message="Le libelle est obligatoire")
     */
    private $libelle;
   /**
     * @Assert\Type( type="string")
     * @Assert\Choice(choices = {"O", "N"}, message="Répondre 'oui' ou 'Non'")
     */
    private $aidable;

    public function getCode() {
        return $this->code;
    }

    public function getLibelle() {
        return $this->libelle;
    }

    public function getAidable() {
        return $this->aidable;
    }

    public function setCode($code) {
        $this->code = $code;
    }

    public function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

    public function setAidable($aidable) {
        $this->aidable = $aidable;
    }

}
