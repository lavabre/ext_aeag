<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminCommune
 *
 * @ORM\Table(name="admin_commune", indexes={@ORM\Index(name="IDX_EEFADED72AE24E6C", columns={"insee_departement"})})
 * @ORM\Entity
 */
class AdminCommune
{
    /**
     * @var string
     *
     * @ORM\Column(name="insee_commune", type="string", length=5, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admin_commune_insee_commune_seq", allocationSize=1, initialValue=1)
     */
    private $inseeCommune;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_commune", type="string", length=50, nullable=true)
     */
    private $nomCommune;

    /**
     * @var \AdminDepartement
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
     *
     * @return adminCommune
     */
    public function setNomCommune($nomCommune)
    {
        $this->nomCommune = $nomCommune;

        return $this;
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
     * @param \Aeag\EdlBundle\Entity\AdminDepartement $inseeDepartement
     *
     * @return adminCommune
     */
    public function setInseeDepartement(\Aeag\EdlBundle\Entity\AdminDepartement $inseeDepartement = null)
    {
        $this->inseeDepartement = $inseeDepartement;

        return $this;
    }

    /**
     * Get inseeDepartement
     *
     * @return \Aeag\EdlBundle\Entity\AdminDepartement
     */
    public function getInseeDepartement()
    {
        return $this->inseeDepartement;
    }
}
