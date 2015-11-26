<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgSandreFractions
 *
 * @ORM\Table(name="pg_sandre_fractions", indexes={@ORM\Index(name="IDX_54142E36B8A3AF39", columns={"code_support"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgSandreFractionsRepository")
 */
class PgSandreFractions
{
    /**
     * @var string
     *
     * @ORM\Column(name="code_fraction", type="string", length=3, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_sandre_fractions_code_fraction_seq", allocationSize=1, initialValue=1)
     */
    private $codeFraction;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_fraction", type="string", length=255, nullable=false)
     */
    private $nomFraction;

    /**
     * @var string
     *
     * @ORM\Column(name="nature_fraction", type="string", length=1, nullable=true)
     */
    private $natureFraction;

    /**
     * @var \PgSandreSupports
     *
     * @ORM\ManyToOne(targetEntity="PgSandreSupports")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_support", referencedColumnName="code_support")
     * })
     */
    private $codeSupport;



    /**
     * Get codeFraction
     *
     * @return string 
     */
    public function getCodeFraction()
    {
        return $this->codeFraction;
    }

    /**
     * Set nomFraction
     *
     * @param string $nomFraction
     * @return PgSandreFractions
     */
    public function setNomFraction($nomFraction)
    {
        $this->nomFraction = $nomFraction;

        return $this;
    }

    /**
     * Get nomFraction
     *
     * @return string 
     */
    public function getNomFraction()
    {
        return $this->nomFraction;
    }

    /**
     * Set natureFraction
     *
     * @param string $natureFraction
     * @return PgSandreFractions
     */
    public function setNatureFraction($natureFraction)
    {
        $this->natureFraction = $natureFraction;

        return $this;
    }

    /**
     * Get natureFraction
     *
     * @return string 
     */
    public function getNatureFraction()
    {
        return $this->natureFraction;
    }

    /**
     * Set codeSupport
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreSupports $codeSupport
     * @return PgSandreFractions
     */
    public function setCodeSupport(\Aeag\SqeBundle\Entity\PgSandreSupports $codeSupport = null)
    {
        $this->codeSupport = $codeSupport;

        return $this;
    }

    /**
     * Get codeSupport
     *
     * @return \Aeag\SqeBundle\Entity\PgSandreSupports 
     */
    public function getCodeSupport()
    {
        return $this->codeSupport;
    }
}
