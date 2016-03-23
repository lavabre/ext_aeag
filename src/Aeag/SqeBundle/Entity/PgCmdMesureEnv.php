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
class PgCmdMesureEnv {

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
     * @var \PgSandreUnites
     *
     * @ORM\ManyToOne(targetEntity="PgSandreUnites")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_unite", referencedColumnName="code_unite")
     * })
     */
    private $codeUnite;

    /**
     * @var string
     *
     * @ORM\Column(name="code_remarque", type="string", length=1, nullable=true)
     */
    private $codeRemarque;

    /**
     * @var string
     *
     * @ORM\Column(name="code_methode", type="string", length=5, nullable=true)
     */
    private $codeMethode;

    /**
     * @var string
     *
     * @ORM\Column(name="code_statut", type="string", length=1, nullable=true)
     */
    private $codeStatut;

    /**
     * @var \PgProgLotParamAn
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotParamAn")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="param_prog_id", referencedColumnName="id")
     * })
     */
    private $paramProg;

    function getPrelev() {
        return $this->prelev;
    }

    function getCodeParametre() {
        return $this->codeParametre;
    }

    function getDateMes() {
        return $this->dateMes;
    }

    function getResultat() {
        return $this->resultat;
    }

    function getCodeUnite() {
        return $this->codeUnite;
    }

    function getCodeRemarque() {
        return $this->codeRemarque;
    }

    function getCodeMethode() {
        return $this->codeMethode;
    }

    function getCodeStatut() {
        return $this->codeStatut;
    }

    function getParamProg() {
        return $this->paramProg;
    }

    function setPrelev($prelev) {
        $this->prelev = $prelev;
    }

    function setCodeParametre($codeParametre) {
        $this->codeParametre = $codeParametre;
    }

    function setDateMes(\DateTime $dateMes) {
        $this->dateMes = $dateMes;
    }

    function setResultat($resultat) {
        $this->resultat = $resultat;
    }

    function setCodeUnite($codeUnite) {
        $this->codeUnite = $codeUnite;
    }

    function setCodeRemarque($codeRemarque) {
        $this->codeRemarque = $codeRemarque;
    }

    function setCodeMethode($codeMethode) {
        $this->codeMethode = $codeMethode;
    }

    function setCodeStatut($codeStatut) {
        $this->codeStatut = $codeStatut;
    }

    function setParamProg($paramProg) {
        $this->paramProg = $paramProg;
    }

}
