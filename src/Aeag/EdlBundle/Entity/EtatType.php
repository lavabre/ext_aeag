<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EtatType
 *
 * @ORM\Table(name="etat_type", indexes={@ORM\Index(name="IDX_57A8D57B7A0B352", columns={"cd_groupe"})})
 * @ORM\Entity
 */
class EtatType
{
    /**
     * @var string
     *
     * @ORM\Column(name="cd_etat", type="string", length=16, nullable=false)
     * @ORM\Id
        */
    private $cdEtat;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255, nullable=false)
     */
    private $libelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="integer", nullable=false)
     */
    private $ordre;

    /**
     * @var string
     *
     * @ORM\Column(name="type_me", type="string", length=2, nullable=true)
     */
    private $typeMe;

    /**
     * @var \EtatGroupe
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
     *
     * @return etatType
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
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
     *
     * @return etatType
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
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
     *
     * @return etatType
     */
    public function setTypeMe($typeMe)
    {
        $this->typeMe = $typeMe;

        return $this;
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
     * @param \Aeag\EdlBundle\Entity\EtatGroupe $cdGroupe
     *
     * @return etatType
     */
    public function setCdGroupe(\Aeag\EdlBundle\Entity\EtatGroupe $cdGroupe = null)
    {
        $this->cdGroupe = $cdGroupe;

        return $this;
    }

    /**
     * Get cdGroupe
     *
     * @return \Aeag\EdlBundle\Entity\EtatGroupe
     */
    public function getCdGroupe()
    {
        return $this->cdGroupe;
    }
}
