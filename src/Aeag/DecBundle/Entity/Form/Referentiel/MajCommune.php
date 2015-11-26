<?php

namespace Aeag\DecBundle\Entity\Form\Referentiel;

use Symfony\Component\Validator\Constraints as Assert;

class MajCommune {

    /**
     * @Assert\Type( type="string")
     * @Assert\NotBlank(message="La commune est obligatoire")
     */
    private $commune;

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

    public function getCommune() {
        return $this->commune;
    }

    public function getLibelle() {
        return $this->libelle;
    }

    public function getAidable() {
        return $this->aidable;
    }

    public function setCommune($commune) {
        $this->commune = $commune;
    }

    public function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

    public function setAidable($aidable) {
        $this->aidable = $aidable;
    }

}
