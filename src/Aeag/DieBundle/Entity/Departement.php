<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Departement
 *
 * @author lavabre
 */

namespace Aeag\DieBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="departement")
 * @ORM\Entity(repositoryClass="Aeag\DieBundle\Repository\DepartementRepository")
 * 
 */
class Departement {

    
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=2)
     * 
     * @Assert\NotBlank()
     * @Assert\Length(min="2") 
     */
    private $dept;

    /**
     * @ORM\Column(type="string", length=25)
     * 
     * @Assert\NotBlank()
     * @Assert\Length(min="4") 
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
        return $this->dept . '    '  . $this->libelle;
    }

}

