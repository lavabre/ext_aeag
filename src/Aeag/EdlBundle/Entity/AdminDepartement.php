<?php

namespace Aeag\EdlBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\AdminDepartement
 * @ORM\Entity
 * @ORM\Table(name="admin_departement")
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Repository\AdminDepartementRepository")
 *
 */
class AdminDepartement
{

    /**
     * @var string $inseeDepartement
     *
     * @ORM\Column(name="insee_departement", type="string", nullable=false)
     * @ORM\Id
     */
    private $inseeDepartement;

    /**
     * @var string $nomDepartement
     *
     * @ORM\Column(name="nom_departement", type="string", nullable=true)
     */
    private $nomDepartement;

    /**
     * @var MasseEau
     *
     * @ORM\ManyToMany(targetEntity="MasseEau", mappedBy="euCd")
     */
    private $euCd;

    public function __construct()
    {
        $this->euCd = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Set nomDepartement
     *
     * @param string $nomDepartement
     */
    public function setNomDepartement($nomDepartement)
    {
        $this->nomDepartement = $nomDepartement;
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

    /**
     * Add euCd
     *
     * @param Aeag\EdlBundle\Entity\MasseEau $euCd
     */
    public function addEuCd($euCd)
    {
        $this->euCd[] = $euCd;
    }

    /**
     * Get euCd
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getEuCd()
    {
        return $this->euCd;
    }


    /**
     * Add euCd
     *
     * @param Aeag\EdlBundle\Entity\MasseEau $euCd
     */
    public function addMasseEau($euCd)
    {
        $this->euCd[] = $euCd;
    }
    
     public function getDeptLibelle() {
        return $this->inseeDepartement . '    '  . $this->nomDepartement;
    }

}