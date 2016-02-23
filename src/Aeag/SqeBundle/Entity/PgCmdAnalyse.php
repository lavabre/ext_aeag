<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdAnalyse
 *
 * @ORM\Table(name="pg_cmd_analyse", indexes={@ORM\Index(name="IDX_CE6A4EC282E879", columns={"code_parametre"}), @ORM\Index(name="IDX_CE6A4EC27DEE24D", columns={"code_fraction"}), @ORM\Index(name="IDX_CE6A4EC2F131D854", columns={"code_unite"}), @ORM\Index(name="IDX_CE6A4EC231275661", columns={"param_prog_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdAnalyseRepository")
 */
class PgCmdAnalyse
{
    /**
     * @var string
     *
     * @ORM\Column(name="prelev_id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $prelevId;

    /**
     * @var string
     *
     * @ORM\Column(name="num_ordre", type="decimal", precision=4, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $numOrdre;

    /**
     * @var string
     *
     * @ORM\Column(name="lieu_ana", type="string", length=1, nullable=false)
     */
    private $lieuAna;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_ana", type="datetime", nullable=true)
     */
    private $dateAna;

    /**
     * @var string
     *
     * @ORM\Column(name="resultat", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $resultat;

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
     * @var \PgSandreFractions
     *
     * @ORM\ManyToOne(targetEntity="PgSandreFractions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_fraction", referencedColumnName="code_fraction")
     * })
     */
    private $codeFraction;

    /**
     * @var \PgSandreUnites
     *
     * @ORM\ManyToOne(targetEntity="PgSandreUnites")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_unite", referencedColumnName="code_unite")
     * })
     */
    private $codeUnite;

    /**
     * @var \PgProgLotParamAn
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotParamAn")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="param_prog_id", referencedColumnName="id")
     * })
     */
    private $paramProg;



    /**
     * Set prelevId
     *
     * @param string $prelevId
     *
     * @return PgCmdAnalyse
     */
    public function setPrelevId($prelevId)
    {
        $this->prelevId = $prelevId;

        return $this;
    }

    /**
     * Get prelevId
     *
     * @return string
     */
    public function getPrelevId()
    {
        return $this->prelevId;
    }

    /**
     * Set numOrdre
     *
     * @param string $numOrdre
     *
     * @return PgCmdAnalyse
     */
    public function setNumOrdre($numOrdre)
    {
        $this->numOrdre = $numOrdre;

        return $this;
    }

    /**
     * Get numOrdre
     *
     * @return string
     */
    public function getNumOrdre()
    {
        return $this->numOrdre;
    }

    /**
     * Set lieuAna
     *
     * @param string $lieuAna
     *
     * @return PgCmdAnalyse
     */
    public function setLieuAna($lieuAna)
    {
        $this->lieuAna = $lieuAna;

        return $this;
    }

    /**
     * Get lieuAna
     *
     * @return string
     */
    public function getLieuAna()
    {
        return $this->lieuAna;
    }

    /**
     * Set dateAna
     *
     * @param \DateTime $dateAna
     *
     * @return PgCmdAnalyse
     */
    public function setDateAna($dateAna)
    {
        $this->dateAna = $dateAna;

        return $this;
    }

    /**
     * Get dateAna
     *
     * @return \DateTime
     */
    public function getDateAna()
    {
        return $this->dateAna;
    }

    /**
     * Set resultat
     *
     * @param string $resultat
     *
     * @return PgCmdAnalyse
     */
    public function setResultat($resultat)
    {
        $this->resultat = $resultat;

        return $this;
    }

    /**
     * Get resultat
     *
     * @return string
     */
    public function getResultat()
    {
        return $this->resultat;
    }

    /**
     * Set codeParametre
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre
     *
     * @return PgCmdAnalyse
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
     * Set codeFraction
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreFractions $codeFraction
     *
     * @return PgCmdAnalyse
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

    /**
     * Set codeUnite
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreUnites $codeUnite
     *
     * @return PgCmdAnalyse
     */
    public function setCodeUnite(\Aeag\SqeBundle\Entity\PgSandreUnites $codeUnite = null)
    {
        $this->codeUnite = $codeUnite;

        return $this;
    }

    /**
     * Get codeUnite
     *
     * @return \Aeag\SqeBundle\Entity\PgSandreUnites
     */
    public function getCodeUnite()
    {
        return $this->codeUnite;
    }

    /**
     * Set paramProg
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotParamAn $paramProg
     *
     * @return PgCmdAnalyse
     */
    public function setParamProg(\Aeag\SqeBundle\Entity\PgProgLotParamAn $paramProg = null)
    {
        $this->paramProg = $paramProg;

        return $this;
    }

    /**
     * Get paramProg
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotParamAn
     */
    public function getParamProg()
    {
        return $this->paramProg;
    }
}
