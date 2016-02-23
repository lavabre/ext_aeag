<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdDemande
 *
 * @ORM\Table(name="pg_cmd_demande")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdDemandeRepository")
 */
class PgCmdDemande {
    
     /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_cmd_demande_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="code_demande_cmd", type="string", length=100, nullable=false)
     */
    private $codeDemandeCmd;
    
    /**
     * @var string
     *
     * @ORM\Column(name="annee_prog", type="decimal", precision=4, scale=0, nullable=false)
     */
    private $anneeProg;
    
    /**
     * @var \PgProgLotAn
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotAn")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lotan_id", referencedColumnName="id")
     * })
     */
    private $lotan;
    
    /**
     * @var \PgRefCorresProducteur
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresProducteur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="commanditaire_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $commanditaire;
    
    /**
     * @var string
     *
     * @ORM\Column(name="code_demande_presta", type="string", length=100, nullable=true)
     */
    private $codeDemandePresta;
    
     /**
     * @var \PgRefCorresPresta
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prestataire_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $prestataire;
    
    /**
     * @var string
     *
     * @ORM\Column(name="type_demande", type="string", length=1, nullable=false)
     */
    private $typeDemande;
    
    /**
     * @var \PgRefCorresProducteur
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresProducteur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dest_ana_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $destAna;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_demande", type="datetime", nullable=true)
     */
    private $dateDemande;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nom_fichier", type="string", length=255, nullable=true)
     */
    private $nomFichier;
    
    /**
     * @var \PgProgPhases
     *
     * @ORM\ManyToOne(targetEntity="PgProgPhases")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="phase_dmd_id", referencedColumnName="id")
     * })
     */
    private $phaseDemande;
    
    /**
     * @var \PgProgWebusers
     *
     * @ORM\ManyToOne(targetEntity="PgProgWebusers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="emetteur_id", referencedColumnName="id")
     * })
     */
    private $emetteur;
    
    /**
     * @var \PgProgPeriodes
     *
     * @ORM\ManyToOne(targetEntity="PgProgPeriodes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="periode_id", referencedColumnName="id")
     * })
     */
    private $periode;
    
    /**
     * @var string
     *
     * @ORM\Column(name="format_fic", type="string", length=50, nullable=true)
     */
    private $formatFichier;
    
    function getId() {
        return $this->id;
    }

    function getCodeDemandeCmd() {
        return $this->codeDemandeCmd;
    }

    function getAnneeProg() {
        return $this->anneeProg;
    }

    function getLotan() {
        return $this->lotan;
    }

    function getCommanditaire() {
        return $this->commanditaire;
    }

    function getCodeDemandePresta() {
        return $this->codeDemandePresta;
    }

    function getPrestataire() {
        return $this->prestataire;
    }

    function getTypeDemande() {
        return $this->typeDemande;
    }

    function getDestAna() {
        return $this->destAna;
    }

    function getDateDemande() {
        return $this->dateDemande;
    }

    function getNomFichier() {
        return $this->nomFichier;
    }

    function getPhaseDemande() {
        return $this->phaseDemande;
    }

    function getEmetteur() {
        return $this->emetteur;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setCodeDemandeCmd($codeDemandeCmd) {
        $this->codeDemandeCmd = $codeDemandeCmd;
    }

    function setAnneeProg($anneeProg) {
        $this->anneeProg = $anneeProg;
    }

    function setLotan($lotan) {
        $this->lotan = $lotan;
    }

    function setCommanditaire( $commanditaire) {
        $this->commanditaire = $commanditaire;
    }

    function setCodeDemandePresta($codeDemandePresta) {
        $this->codeDemandePresta = $codeDemandePresta;
    }

    function setPrestataire($prestataire) {
        $this->prestataire = $prestataire;
    }

    function setTypeDemande($typeDemande) {
        $this->typeDemande = $typeDemande;
    }

    function setDestAna($destAna) {
        $this->destAna = $destAna;
    }

    function setDateDemande(\DateTime $dateDemande) {
        $this->dateDemande = $dateDemande;
    }

    function setNomFichier($nomFichier) {
        $this->nomFichier = $nomFichier;
    }

    function setPhaseDemande($phaseDemande) {
        $this->phaseDemande = $phaseDemande;
    }

    function setEmetteur($emetteur) {
        $this->emetteur = $emetteur;
    }
    
    function getPeriode() {
        return $this->periode;
    }

    function getFormatFichier() {
        return $this->formatFichier;
    }

    function setPeriode($periode) {
        $this->periode = $periode;
    }

    function setFormatFichier($formatFichier) {
        $this->formatFichier = $formatFichier;
    }


    
}
