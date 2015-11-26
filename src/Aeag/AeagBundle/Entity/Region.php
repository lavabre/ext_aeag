<?php

namespace Aeag\AeagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="Region")
 * @ORM\Entity(repositoryClass="Aeag\AeagBundle\Repository\RegionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Region {

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=5)
     * 
     * @Assert\NotBlank()
     */
    private $reg;

    /**
     * @ORM\Column(type="string", length=100)
     * 
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * 
     */
    private $dec;

    public function getReg() {
        return $this->reg;
    }

    public function getLibelle() {
        return $this->libelle;
    }

    public function getDec() {
        return $this->dec;
    }

    public function setReg($reg) {
        $this->reg = $reg;
    }

    public function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

    public function setDec($dec) {
        $this->dec = $dec;
    }

    public function getRegionAdminLibelle() {
        return $this->reg . '    ' . $this->libelle;
    }

}
