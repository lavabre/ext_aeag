<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminDepartement
 *
 * @ORM\Table(name="admin_departement")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Repository\AdminDepartementRepository")
 */
class AdminDepartement
{
    /**
     * @var string
     *
     * @ORM\Column(name="insee_departement", type="string", length=2, nullable=false)
     * @ORM\Id
      */
    private $inseeDepartement;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_departement", type="string", length=30, nullable=true)
     */
    private $nomDepartement;



    /**
     * Get inseeDepartement
     *
     * @return string
     */
    public function getInseeDepartement()
    {
        return $this->inseeDepartement;
    }

    /**
     * Set nomDepartement
     *
     * @param string $nomDepartement
     *
     * @return adminDepartement
     */
    public function setNomDepartement($nomDepartement)
    {
        $this->nomDepartement = $nomDepartement;

        return $this;
    }

    /**
     * Get nomDepartement
     *
     * @return string
     */
    public function getNomDepartement()
    {
        return $this->nomDepartement;
    }
}
