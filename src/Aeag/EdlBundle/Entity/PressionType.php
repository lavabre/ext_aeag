<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PressionType
 *
 * @ORM\Table(name="pression_type", indexes={@ORM\Index(name="IDX_D77B6AB5B7A0B352", columns={"cd_groupe"})})
 * @ORM\Entity
 */
class PressionType
{
    /**
     * @var string
     *
     * @ORM\Column(name="cd_pression", type="string", length=16, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pression_type_cd_pression_seq", allocationSize=1, initialValue=1)
     */
    private $cdPression;

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
     * @var \PressionGroupe
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
     *
     * @return pressionType
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
     * @return pressionType
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
     * Set cdGroupe
     *
     * @param \Aeag\EdlBundle\Entity\PressionGroupe $cdGroupe
     *
     * @return pressionType
     */
    public function setCdGroupe(\Aeag\EdlBundle\Entity\PressionGroupe $cdGroupe = null)
    {
        $this->cdGroupe = $cdGroupe;

        return $this;
    }

    /**
     * Get cdGroupe
     *
     * @return \Aeag\EdlBundle\Entity\PressionGroupe
     */
    public function getCdGroupe()
    {
        return $this->cdGroupe;
    }
}
