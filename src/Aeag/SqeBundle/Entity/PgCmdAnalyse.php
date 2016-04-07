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
class PgCmdAnalyse {

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
     * @ORM\Column(name="code_remarque", type="string", length=2, nullable=true)
     */
    private $codeRemarque;

    /**
     * @var string
     *
     * @ORM\Column(name="lq_ana", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $lqAna;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_ana_labo", type="string", length=100, nullable=true)
     */
    private $refAnaLabo;

    /**
     * @var string
     *
     * @ORM\Column(name="code_methode", type="string", length=5, nullable=true)
     */
    private $codeMethode;

    /**
     * @var string
     *
     * @ORM\Column(name="accreditation", type="string", length=1, nullable=true)
     */
    private $accreditation;

    /**
     * @var string
     *
     * @ORM\Column(name="confirmation", type="string", length=1, nullable=true)
     */
    private $confirmation;

    /**
     * @var string
     *
     * @ORM\Column(name="reserve", type="string", length=1, nullable=true)
     */
    private $reserve;

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
     * @var \PgProgLotParamAn
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotParamAn")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="param_prog_id", referencedColumnName="id")
     * })
     */
    private $paramProg;

    function getPrelevId() {
        return $this->prelevId;
    }

    function getNumOrdre() {
        return $this->numOrdre;
    }

    function getCodeParametre() {
        return $this->codeParametre;
    }

    function getCodeFraction() {
        return $this->codeFraction;
    }

    function getLieuAna() {
        return $this->lieuAna;
    }

    function getDateAna() {
        return $this->dateAna;
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

    function getLqAna() {
        return $this->lqAna;
    }

    function getRefAnaLabo() {
        return $this->refAnaLabo;
    }

    function getCodeMethode() {
        return $this->codeMethode;
    }

    function getAccreditation() {
        return $this->accreditation;
    }

    function getConfirmation() {
        return $this->confirmation;
    }

    function getReserve() {
        return $this->reserve;
    }

    function getCodeStatut() {
        return $this->codeStatut;
    }

    function getParamProg() {
        return $this->paramProg;
    }

    function setPrelevId($prelevId) {
        $this->prelevId = $prelevId;
    }

    function setNumOrdre($numOrdre) {
        $this->numOrdre = $numOrdre;
    }

    function setCodeParametre($codeParametre) {
        $this->codeParametre = $codeParametre;
    }

    function setCodeFraction($codeFraction) {
        $this->codeFraction = $codeFraction;
    }

    function setLieuAna($lieuAna) {
        $this->lieuAna = $lieuAna;
    }

    function setDateAna(\DateTime $dateAna) {
        $this->dateAna = $dateAna;
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

    function setLqAna($lqAna) {
        $this->lqAna = $lqAna;
    }

    function setRefAnaLabo($refAnaLabo) {
        $this->refAnaLabo = $refAnaLabo;
    }

    function setCodeMethode($codeMethode) {
        $this->codeMethode = $codeMethode;
    }

    function setAccreditation($accreditation) {
        $this->accreditation = $accreditation;
    }

    function setConfirmation($confirmation) {
        $this->confirmation = $confirmation;
    }

    function setReserve($reserve) {
        $this->reserve = $reserve;
    }

    function setCodeStatut($codeStatut) {
        $this->codeStatut = $codeStatut;
    }

    function setParamProg($paramProg) {
        $this->paramProg = $paramProg;
    }

    function getLibelleStatut() {
        return $this->libelleStatut;
    }

    function setLibelleStatut($libelleStatut) {
        $this->libelleStatut = $libelleStatut;
    }

}
