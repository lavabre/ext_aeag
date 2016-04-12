<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\AdminCommune
 *
 * @ORM\Table(name="admin_commune")
 * @ORM\Entity
 */
class AdminCommune
{

    /**
     * @var string $inseeCommune
     *
     * @ORM\Column(name="insee_commune", type="string", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_commune_insee_commune_seq", allocationSize="1", initialValue="1")
     */
    private $inseeCommune;

    /**
     * @var string $nomCommune
     *
     * @ORM\Column(name="nom_commune", type="string", nullable=true)
     */
    private $nomCommune;

    /**
     * @var AdminDepartement
     *
     * @ORM\ManyToOne(targetEntity="AdminDepartement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="insee_departement", referencedColumnName="insee_departement")
     * })
     */
    private $inseeDepartement;



    /**
     * Get inseeCommune
     *
     * @return string 
     */
    public function getInseeCommune()
    {
        return $this->inseeCommune;
    }

    /**
     * Set nomCommune
     *
     * @param string $nomCommune
     */
    public function setNomCommune($nomCommune)
    {
        $this->nomCommune = $nomCommune;
    }

    /**
     * Get nomCommune
     *
     * @return string 
     */
    public function getNomCommune()
    {
        return $this->nomCommune;
    }

    /**
     * Set inseeDepartement
     *
     * @param Aeag\EdlBundle\Entity\AdminDepartement $inseeDepartement
     */
    public function setInseeDepartement(\Aeag\EdlBundle\Entity\AdminDepartement $inseeDepartement)
    {
        $this->inseeDepartement = $inseeDepartement;
    }

    /**
     * Get inseeDepartement
     *
     * @return Aeag\EdlBundle\Entity\AdminDepartement 
     */
    public function getInseeDepartement()
    {
        return $this->inseeDepartement;
    }

}