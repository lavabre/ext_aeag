<?php

namespace Aeag\DecBundle\Entity\Form\Referentiel;

use Symfony\Component\Validator\Constraints as Assert;

class MajCommuneCodePostal {

    /**
     * @Assert\Type( type="string")
     * @Assert\NotBlank(message="Le code postal est obligatoire")
     */
    private $cp;

    /**
     * @Assert\Type( type="string")
     * @Assert\NotBlank(message="L'acheminement est obligatoire")
     */
    private $acheminement;

    /**
     * @Assert\Type( type="string")
     * @Assert\Choice(choices = {"O", "N"}, message="Répondre 'oui' ou 'Non'")
     */
    private $aidable;

    public function getCp() {
        return $this->cp;
    }

    public function getAcheminement() {
        return $this->acheminement;
    }

    public function getAidable() {
        return $this->aidable;
    }

    public function setCp($cp) {
        $this->cp = $cp;
    }

    public function setAcheminement($acheminement) {
        $this->acheminement = $acheminement;
    }

    public function setAidable($aidable) {
        $this->aidable = $aidable;
    }

}
