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
     * @var string
     *
     * @ORM\Column(name="libelle_statut", type="string", length=255, nullable=true)
     */
    private $libelleStatut;
    
    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=20000, nullable=true)
     */
    private $commentaire;

    /**
     * @var \PgProgLotParamAn
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotParamAn")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="param_prog_id", referencedColumnName="id")
     * })
     */
    private $paramProg;

    public function getPrelev() {
        return $this->prelev;
    }

    public function getCodeParametre() {
        return $this->codeParametre;
    }

    public function getDateMes() {
        return $this->dateMes;
    }

    public function getResultat() {
        return $this->resultat;
    }

    public function getCodeUnite() {
        return $this->codeUnite;
    }

    public function getCodeRemarque() {
        return $this->codeRemarque;
    }

    public function getCodeMethode() {
        return $this->codeMethode;
    }

    public function getCodeStatut() {
        return $this->codeStatut;
    }

    public function getLibelleStatut() {
        return $this->libelleStatut;
    }

    public function getParamProg() {
        return $this->paramProg;
    }

    public function setPrelev(\Aeag\SqeBundle\Entity\PgCmdPrelev $prelev) {
        $this->prelev = $prelev;
    }

    public function setCodeParametre(\Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre) {
        $this->codeParametre = $codeParametre;
    }

    public function setDateMes(\DateTime $dateMes) {
        $this->dateMes = $dateMes;
    }

    public function setResultat($resultat) {
        $this->resultat = $resultat;
    }

    public function setCodeUnite(\Aeag\SqeBundle\Entity\PgSandreUnites $codeUnite) {
        $this->codeUnite = $codeUnite;
    }

    public function setCodeRemarque($codeRemarque) {
        $this->codeRemarque = $codeRemarque;
    }

    public function setCodeMethode($codeMethode) {
        $this->codeMethode = $codeMethode;
    }

    public function setCodeStatut($codeStatut) {
        $this->codeStatut = $codeStatut;
    }

    public function setLibelleStatut($libelleStatut) {
        $this->libelleStatut = $libelleStatut;
    }

    public function setParamProg(\Aeag\SqeBundle\Entity\PgProgLotParamAn $paramProg) {
        $this->paramProg = $paramProg;
    }
    
    public function getCommentaire() {
        return $this->commentaire;
    }

    public function setCommentaire($commentaire) {
        $this->commentaire = $commentaire;
    }

}
