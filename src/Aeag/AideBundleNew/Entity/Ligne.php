<?php

namespace Aeag\AideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="lignes")
 * @ORM\Entity(repositoryClass="Aeag\AideBundle\Repository\LigneRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Ligne {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=4)
     * 
     */
    private $ligne;

    /**
     * 
     * @ORM\Column(type="string", length=100)
     * 
     * @Assert\NotBlank()
      */
    private $libelle;

    public function getLigne() {
        return $this->ligne;
    }

    public function setLigne($ligne) {
        $this->ligne = $ligne;
    }

    public function getLibelle() {
        return $this->libelle;
    }

    public function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

    public function getLigneLibelle() {
        return $this->ligne . '    ' . $this->libelle;
    }

}
