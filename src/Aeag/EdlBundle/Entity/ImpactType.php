<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\ImpactType
 *
 * @ORM\Table(name="impact_type")
 * @ORM\Entity
 */
class ImpactType
{

    /**
     * @var string $cdImpact
     *
     * @ORM\Column(name="cd_impact", type="string", length=16, nullable=false)
     * @ORM\Id
       */
    private $cdImpact;

    /**
     * @var string $libelle
     *
     * @ORM\Column(name="libelle", type="string", length=255, nullable=false)
     */
    private $libelle;

    /**
     * @var integer $ordre
     *
     * @ORM\Column(name="ordre", type="integer", nullable=false)
     */
    private $ordre;

    /**
     * @var string $typeMe
     *
     * @ORM\Column(name="type_me", type="string", nullable=false)
     */
    private $typeMe;

    /**
     * @var MasseEau
     *
     * @ORM\ManyToMany(targetEntity="MasseEau", mappedBy="cdImpact")
     */
    private $euCd;

    /**
     * @var ImpactGroupe
     *
     * @ORM\ManyToOne(targetEntity="ImpactGroupe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cd_groupe", referencedColumnName="cd_groupe")
     * })
     */
    private $cdGroupe;

    public function __construct()
    {
        $this->euCd = new \Doctrine\Common\Collections\ArrayCollection();
    }
    

    /**
     * Get cdImpact
     *
     * @return string 
     */
    public function getCdImpact()
    {
        return $this->cdImpact;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;
    }

    /**
     * Get ordre
     *
     * @return integer 
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set typeMe
     *
     * @param string $typeMe
     */
    public function setTypeMe($typeMe)
    {
        $this->typeMe = $typeMe;
    }

    /**
     * Get typeMe
     *
     * @return string 
     */
    public function getTypeMe()
    {
        return $this->typeMe;
    }

    /**
     * Add euCd
     *
     * @param Aeag\EdlBundle\Entity\MasseEau $euCd
     */
    public function addEuCd(\Aeag\EdlBundle\Entity\MasseEau $euCd)
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
     * Set cdGroupe
     *
     * @param Aeag\EdlBundle\Entity\ImpactGroupe $cdGroupe
     */
    public function setCdGroupe(\Aeag\EdlBundle\Entity\ImpactGroupe $cdGroupe)
    {
        $this->cdGroupe = $cdGroupe;
    }

    /**
     * Get cdGroupe
     *
     * @return Aeag\EdlBundle\Entity\ImpactGroupe 
     */
    public function getCdGroupe()
    {
        return $this->cdGroupe;
    }

    /**
     * Add euCd
     *
     * @param Aeag\EdlBundle\Entity\MasseEau $euCd
     */
    public function addMasseEau(\Aeag\EdlBundle\Entity\MasseEau $euCd)
    {
        $this->euCd[] = $euCd;
    }
}