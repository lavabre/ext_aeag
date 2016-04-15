<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\EtatType
 *
 * @ORM\Table(name="etat_type")
 * @ORM\Entity
 */
class EtatType
{

    /**
     * @var string $cdEtat
     *
     * @ORM\Column(name="cd_etat", type="string", length=16, nullable=false)
     * @ORM\Id
        */
    private $cdEtat;

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
     * @var EtatGroupe
     *
     * @ORM\ManyToOne(targetEntity="EtatGroupe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cd_groupe", referencedColumnName="cd_groupe")
     * })
     */
    private $cdGroupe;


    /**
     * Get cdEtat
     *
     * @return string 
     */
    public function getCdEtat()
    {
        return $this->cdEtat;
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

    /**
     * Set cdGroupe
     *
     * @param Aeag\EdlBundle\Entity\EtatGroupe $cdGroupe
     */
    public function setCdGroupe(\Aeag\EdlBundle\Entity\EtatGroupe $cdGroupe)
    {
        $this->cdGroupe = $cdGroupe;
    }

    /**
     * Get cdGroupe
     *
     * @return Aeag\EdlBundle\Entity\EtatGroupe 
     */
    public function getCdGroupe()
    {
        return $this->cdGroupe;
    }

}