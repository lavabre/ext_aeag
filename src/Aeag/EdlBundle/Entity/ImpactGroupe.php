<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\ImpactGroupe
 *
 * @ORM\Table(name="impact_groupe")
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Entity\ImpactGroupeRepository")
 */
class ImpactGroupe
{

    /**
     * @var string $cdGroupe
     *
     * @ORM\Column(name="cd_groupe", type="string", length=16, nullable=false)
     * @ORM\Id
      */
    private $cdGroupe;

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
     * Get cdGroupe
     *
     * @return string 
     */
    public function getCdGroupe()
    {
        return $this->cdGroupe;
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

}