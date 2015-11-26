<?php

namespace Aeag\AideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="departement")
 * @ORM\Entity(repositoryClass="Aeag\AideBundle\Repository\DepartementRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Departement {

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=3)
     * 
     * @Assert\NotBlank()
     */
    private $dept;

    /**
     * @ORM\Column(type="string", length=25)
     * 
     * @Assert\NotBlank()
     */
    private $libelle;

    public function getDept() {
        return $this->dept;
    }

    public function setDept($dept) {
        $this->dept = $dept;
    }

    public function getLibelle() {
        return $this->libelle;
    }

    public function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

    public function getDeptLibelle() {
        return $this->dept . '    ' . $this->libelle;
    }

}
