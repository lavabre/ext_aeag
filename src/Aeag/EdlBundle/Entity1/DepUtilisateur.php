<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\DepUtilisateur
 *
 * @ORM\Table(name="dep_utilisateur")
 * @ORM\Entity
 */
class DepUtilisateur
{
    /**
     * @var string $inseeDepartement
     *
     * @ORM\Column(name="insee_departement", type="string", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $inseeDepartement;

     /**
     * @var Utilisateur
     * 
     * @ORM\ManyToOne(targetEntity="Aeag\EdlBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id")
     * })
     * @ORM\Id
     */
     private $utilisateur; 


    /**
     * Set inseeDepartement
     *
     * @param string $inseeDepartement
     */
    public function setInseeDepartement($inseeDepartement)
    {
        $this->inseeDepartement = $inseeDepartement;
    }

    /**
     * Get inseeDepartement
     *
     * @return string 
     */
    public function getInseeDepartement()
    {
        return $this->inseeDepartement;
    }

  
    public function getUtilisateur() {
        return $this->utilisateur;
    }

    public function setUtilisateur($utilisateur) {
        $this->utilisateur = $utilisateur;
    }


}