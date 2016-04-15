<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\PressionType
 *
 * @ORM\Table(name="pression_type")
 * @ORM\Entity
 */
class PressionType
{
    /**
     * @var string $cdPression
     *
     * @ORM\Column(name="cd_pression", type="string", length=16, nullable=false)
     * @ORM\Id
     */
    private $cdPression;

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
     * @var PressionGroupe
     *
     * @ORM\ManyToOne(targetEntity="PressionGroupe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cd_groupe", referencedColumnName="cd_groupe")
     * })
     */
    private $cdGroupe;


    /**
     * Get cdPression
     *
     * @return string 
     */
    public function getCdPression()
    {
        return $this->cdPression;
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
     * Set cdGroupe
     *
     * @param Aeag\EdlBundle\Entity\PressionGroupe $cdGroupe
     */
    public function setCdGroupe(\Aeag\EdlBundle\Entity\PressionGroupe $cdGroupe)
    {
        $this->cdGroupe = $cdGroupe;
    }

    /**
     * Get cdGroupe
     *
     * @return Aeag\EdlBundle\Entity\PressionGroupe 
     */
    public function getCdGroupe()
    {
        return $this->cdGroupe;
    }

    /**
     * Set cdPression
     *
     * @param string $cdPression
     */
    public function setCdPression($cdPression)
    {
        $this->cdPression = $cdPression;
    }
}