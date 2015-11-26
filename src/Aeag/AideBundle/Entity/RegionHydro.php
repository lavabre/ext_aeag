<?php

namespace Aeag\AideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="RegionHydro")
 * @ORM\Entity(repositoryClass="Aeag\AideBundle\Repository\RegionHydroRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class RegionHydro {

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=4)
     * 
     * @Assert\NotBlank()
     */
    private $reg;

    /**
     * @ORM\Column(type="string", length=100)
     * 
     */
    private $libelle;

    public function getReg() {
        return $this->reg;
    }

    public function setReg($reg) {
        $this->reg = $reg;
    }

    public function getLibelle() {
        return $this->libelle;
    }

    public function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

    public function getRegionHydroLibelle() {
        return $this->reg . '    ' . $this->libelle;
    }

}
