<?php

namespace Aeag\DecBundle\Entity\Form\Referentiel;

use Symfony\Component\Validator\Constraints as Assert;

class MajDepartement {

    /**
     * @Assert\Type( type="string")
     * @Assert\NotBlank(message="Le departement est obligatoire")
     */
    private $dept;

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

    public function getDept() {
        return $this->dept;
    }

    public function getLibelle() {
        return $this->libelle;
    }

    public function getAidable() {
        return $this->aidable;
    }

    public function setDept($dept) {
        $this->dept = $dept;
    }

    public function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

    public function setAidable($aidable) {
        $this->aidable = $aidable;
    }

}
