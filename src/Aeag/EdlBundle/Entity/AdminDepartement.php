<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminDepartement
 *
 * @ORM\Table(name="admin_departement")
 * @ORM\Entity
 */
class AdminDepartement
{
    /**
     * @var string
     *
     * @ORM\Column(name="insee_departement", type="string", length=2, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_departement_insee_departement_seq", allocationSize=1, initialValue=1)
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
