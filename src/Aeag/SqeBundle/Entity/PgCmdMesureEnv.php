<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdMesureEnv
 *
 * @ORM\Table(name="pg_cmd_mesure_env", indexes={@ORM\Index(name="IDX_ECB3F1E8D8E1F6AA", columns={"prelev_id"}), @ORM\Index(name="IDX_ECB3F1E831275661", columns={"param_prog_id"}), @ORM\Index(name="IDX_ECB3F1E882E879", columns={"code_parametre"}), @ORM\Index(name="IDX_ECB3F1E8F131D854", columns={"code_unite"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdMesureEnvRepository")
 */
class PgCmdMesureEnv
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_mes", type="datetime", nullable=true)
     */
    private $dateMes;

    /**
     * @var string
     *
     * @ORM\Column(name="resultat", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $resultat;

    /**
     * @var \PgCmdPrelev
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="PgCmdPrelev")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prelev_id", referencedColumnName="id")
     * })
     */
    private $prelev;

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
     *   @ORM\JoinColumn(name="code_unite", referencedColumnName="code_unite")
     * })
     */
    private $codeUnite;



    /**
     * Set dateMes
     *
     * @param \DateTime $dateMes
     *
     * @return PgCmdMesureEnv
     */
    public function setDateMes($dateMes)
    {
        $this->dateMes = $dateMes;

        return $this;
    }

    /**
     * Get dateMes
     *
     * @return \DateTime
     */
    public function getDateMes()
    {
        return $this->dateMes;
    }

    /**
     * Set resultat
     *
     * @param string $resultat
     *
     * @return PgCmdMesureEnv
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
     * Set prelev
     *
     * @param \Aeag\SqeBundle\Entity\PgCmdPrelev $prelev
     *
     * @return PgCmdMesureEnv
     */
    public function setPrelev(\Aeag\SqeBundle\Entity\PgCmdPrelev $prelev)
    {
        $this->prelev = $prelev;

        return $this;
    }

    /**
     * Get prelev
     *
     * @return \Aeag\SqeBundle\Entity\PgCmdPrelev
     */
    public function getPrelev()
    {
        return $this->prelev;
    }

    /**
     * Set paramProg
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotParamAn $paramProg
     *
     * @return PgCmdMesureEnv
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

    /**
     * Set codeParametre
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre
     *
     * @return PgCmdMesureEnv
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
     * Set codeUnite
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreUnites $codeUnite
     *
     * @return PgCmdMesureEnv
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
}
