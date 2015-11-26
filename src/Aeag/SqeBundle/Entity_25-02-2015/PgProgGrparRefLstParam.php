<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgGrparRefLstParam
 *
 * @ORM\Table(name="pg_prog_grpar_ref_lst_param", indexes={@ORM\Index(name="IDX_FD392616979D571C", columns={"grpar_ref_id"}), @ORM\Index(name="IDX_FD39261682E879", columns={"code_parametre"}), @ORM\Index(name="IDX_FD392616987A8378", columns={"unite_defaut"}), @ORM\Index(name="IDX_FD3926167DEE24D", columns={"code_fraction"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgGrparRefLstParamRepository")
 */
class PgProgGrparRefLstParam
{
    /**
     * @var string
     *
     * @ORM\Column(name="param_defaut", type="string", length=1, nullable=false)
     */
    private $paramDefaut;

    /**
     * @var \PgProgGrpParamRef
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="PgProgGrpParamRef")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grpar_ref_id", referencedColumnName="id")
     * })
     */
    private $grparRef;

    /**
     * @var \PgSandreParametres
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="PgSandreParametres")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_parametre", referencedColumnName="code_parametre")
     * })
     */
    private $codeParametre;

    /**
     * @var \PgSandreUnites
     *
     * @ORM\ManyToOne(targetEntity="PgSandreUnites")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="unite_defaut", referencedColumnName="code_unite")
     * })
     */
    private $uniteDefaut;

    /**
     * @var \PgSandreFractions
     *
     * @ORM\ManyToOne(targetEntity="PgSandreFractions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_fraction", referencedColumnName="code_fraction")
     * })
     */
    private $codeFraction;



    /**
     * Set paramDefaut
     *
     * @param string $paramDefaut
     * @return PgProgGrparRefLstParam
     */
    public function setParamDefaut($paramDefaut)
    {
        $this->paramDefaut = $paramDefaut;

        return $this;
    }

    /**
     * Get paramDefaut
     *
     * @return string 
     */
    public function getParamDefaut()
    {
        return $this->paramDefaut;
    }

    /**
     * Set grparRef
     *
     * @param \Aeag\SqeBundle\Entity\PgProgGrpParamRef $grparRef
     * @return PgProgGrparRefLstParam
     */
    public function setGrparRef(\Aeag\SqeBundle\Entity\PgProgGrpParamRef $grparRef)
    {
        $this->grparRef = $grparRef;

        return $this;
    }

    /**
     * Get grparRef
     *
     * @return \Aeag\SqeBundle\Entity\PgProgGrpParamRef 
     */
    public function getGrparRef()
    {
        return $this->grparRef;
    }

    /**
     * Set codeParametre
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre
     * @return PgProgGrparRefLstParam
     */
    public function setCodeParametre(\Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre)
    {
        $this->codeParametre = $codeParametre;

        return $this;
    }

    /**
     * Get codeParametre
     *
     * @return \Aeag\SqeBundle\Entity\PgSandreParametres 
     */
    public function getCodeParametre()
    {
        return $this->codeParametre;
    }

    /**
     * Set uniteDefaut
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreUnites $uniteDefaut
     * @return PgProgGrparRefLstParam
     */
    public function setUniteDefaut(\Aeag\SqeBundle\Entity\PgSandreUnites $uniteDefaut = null)
    {
        $this->uniteDefaut = $uniteDefaut;

        return $this;
    }

    /**
     * Get uniteDefaut
     *
     * @return \Aeag\SqeBundle\Entity\PgSandreUnites 
     */
    public function getUniteDefaut()
    {
        return $this->uniteDefaut;
    }

    /**
     * Set codeFraction
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreFractions $codeFraction
     * @return PgProgGrparRefLstParam
     */
    public function setCodeFraction(\Aeag\SqeBundle\Entity\PgSandreFractions $codeFraction = null)
    {
        $this->codeFraction = $codeFraction;

        return $this;
    }

    /**
     * Get codeFraction
     *
     * @return \Aeag\SqeBundle\Entity\PgSandreFractions 
     */
    public function getCodeFraction()
    {
        return $this->codeFraction;
    }
}
