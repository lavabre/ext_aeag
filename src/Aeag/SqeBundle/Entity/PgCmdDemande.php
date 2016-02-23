<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdDemande
 *
 * @ORM\Table(name="pg_cmd_demande", indexes={@ORM\Index(name="IDX_DDE59519E980DBA9", columns={"lotan_id"}), @ORM\Index(name="IDX_DDE595193D2F91B5", columns={"commanditaire_id"}), @ORM\Index(name="IDX_DDE59519BE3DB2B7", columns={"prestataire_id"}), @ORM\Index(name="IDX_DDE5951979E92E8C", columns={"emetteur_id"}), @ORM\Index(name="IDX_DDE59519D09615FF", columns={"phase_dmd_id"}), @ORM\Index(name="IDX_DDE59519F384C1CF", columns={"periode_id"})})
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
     * @var string
     *
     * @ORM\Column(name="code_demande_presta", type="string", length=100, nullable=true)
     */
    private $codeDemandePresta;

    /**
     * @var string
     *
     * @ORM\Column(name="type_demande", type="string", length=1, nullable=false)
     */
    private $typeDemande;

    /**
     * @var string
     *
     * @ORM\Column(name="dest_ana_id", type="decimal", precision=38, scale=0, nullable=false)
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
     * @var string
     *
     * @ORM\Column(name="format_fic", type="string", length=50, nullable=true)
     */
    private $formatFichier;

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
     * @var \PgRefCorresPresta
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prestataire_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $prestataire;

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
     * @var \PgProgPhases
     *
     * @ORM\ManyToOne(targetEntity="PgProgPhases")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="phase_dmd_id", referencedColumnName="id")
     * })
     */
    private $phaseDemande;

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
     * Get id
     *
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set codeDemandeCmd
     *
     * @param string $codeDemandeCmd
     *
     * @return PgCmdDemande
     */
    public function setCodeDemandeCmd($codeDemandeCmd) {
        $this->codeDemandeCmd = $codeDemandeCmd;

        return $this;
    }

    /**
     * Get codeDemandeCmd
     *
     * @return string
     */
    public function getCodeDemandeCmd() {
        return $this->codeDemandeCmd;
    }

    /**
     * Set anneeProg
     *
     * @param string $anneeProg
     *
     * @return PgCmdDemande
     */
    public function setAnneeProg($anneeProg) {
        $this->anneeProg = $anneeProg;

        return $this;
    }

    /**
     * Get anneeProg
     *
     * @return string
     */
    public function getAnneeProg() {
        return $this->anneeProg;
    }

    /**
     * Set codeDemandePresta
     *
     * @param string $codeDemandePresta
     *
     * @return PgCmdDemande
     */
    public function setCodeDemandePresta($codeDemandePresta) {
        $this->codeDemandePresta = $codeDemandePresta;

        return $this;
    }

    /**
     * Get codeDemandePresta
     *
     * @return string
     */
    public function getCodeDemandePresta() {
        return $this->codeDemandePresta;
    }

    /**
     * Set typeDemande
     *
     * @param string $typeDemande
     *
     * @return PgCmdDemande
     */
    public function setTypeDemande($typeDemande) {
        $this->typeDemande = $typeDemande;

        return $this;
    }

    /**
     * Get typeDemande
     *
     * @return string
     */
    public function getTypeDemande() {
        return $this->typeDemande;
    }

    /**
     * Set destAnaId
     *
     * @param string $destAnaId
     *
     * @return PgCmdDemande
     */
    public function setDestAna($destAna) {
        $this->destAna = $destAna;

        return $this;
    }

    /**
     * Get destAnaId
     *
     * @return string
     */
    public function getDestAna() {
        return $this->destAna;
    }

    /**
     * Set dateDemande
     *
     * @param \DateTime $dateDemande
     *
     * @return PgCmdDemande
     */
    public function setDateDemande($dateDemande) {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    /**
     * Get dateDemande
     *
     * @return \DateTime
     */
    public function getDateDemande() {
        return $this->dateDemande;
    }

    /**
     * Set nomFichier
     *
     * @param string $nomFichier
     *
     * @return PgCmdDemande
     */
    public function setNomFichier($nomFichier) {
        $this->nomFichier = $nomFichier;

        return $this;
    }

    /**
     * Get nomFichier
     *
     * @return string
     */
    public function getNomFichier() {
        return $this->nomFichier;
    }

    /**
     * Set formatFichier
     *
     * @param string $formatFichier
     *
     * @return PgCmdDemande
     */
    public function setFormatFichier($formatFichier) {
        $this->formatFic = $formatFichier;

        return $this;
    }

    /**
     * Get formatFichier
     *
     * @return string
     */
    public function getFormatFichier() {
        return $this->formatFichier;
    }

    /**
     * Set lotan
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotAn $lotan
     *
     * @return PgCmdDemande
     */
    public function setLotan(\Aeag\SqeBundle\Entity\PgProgLotAn $lotan = null) {
        $this->lotan = $lotan;

        return $this;
    }

    /**
     * Get lotan
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotAn
     */
    public function getLotan() {
        return $this->lotan;
    }

    /**
     * Set commanditaire
     *
     * @param \Aeag\SqeBundle\Entity\PgRefCorresProducteur $commanditaire
     *
     * @return PgCmdDemande
     */
    public function setCommanditaire(\Aeag\SqeBundle\Entity\PgRefCorresProducteur $commanditaire = null) {
        $this->commanditaire = $commanditaire;

        return $this;
    }

    /**
     * Get commanditaire
     *
     * @return \Aeag\SqeBundle\Entity\PgRefCorresProducteur
     */
    public function getCommanditaire() {
        return $this->commanditaire;
    }

    /**
     * Set prestataire
     *
     * @param \Aeag\SqeBundle\Entity\PgRefCorresPresta $prestataire
     *
     * @return PgCmdDemande
     */
    public function setPrestataire(\Aeag\SqeBundle\Entity\PgRefCorresPresta $prestataire = null) {
        $this->prestataire = $prestataire;

        return $this;
    }

    /**
     * Get prestataire
     *
     * @return \Aeag\SqeBundle\Entity\PgRefCorresPresta
     */
    public function getPrestataire() {
        return $this->prestataire;
    }

    /**
     * Set emetteur
     *
     * @param \Aeag\SqeBundle\Entity\PgProgWebusers $emetteur
     *
     * @return PgCmdDemande
     */
    public function setEmetteur(\Aeag\SqeBundle\Entity\PgProgWebusers $emetteur = null) {
        $this->emetteur = $emetteur;

        return $this;
    }

    /**
     * Get emetteur
     *
     * @return \Aeag\SqeBundle\Entity\PgProgWebusers
     */
    public function getEmetteur() {
        return $this->emetteur;
    }

    /**
     * Set phaseDmd
     *
     * @param \Aeag\SqeBundle\Entity\PgProgPhases $phaseDmd
     *
     * @return PgCmdDemande
     */
    public function setPhaseDemande(\Aeag\SqeBundle\Entity\PgProgPhases $phaseDemande = null) {
        $this->phaseDemande = $phaseDemande;

        return $this;
    }

    /**
     * Get phaseDemande
     *
     * @return \Aeag\SqeBundle\Entity\PgProgPhases
     */
    public function getPhaseDemande() {
        return $this->phaseDmd;
    }

    /**
     * Set periode
     *
     * @param \Aeag\SqeBundle\Entity\PgProgPeriodes $periode
     *
     * @return PgCmdDemande
     */
    public function setPeriode(\Aeag\SqeBundle\Entity\PgProgPeriodes $periode = null) {
        $this->periode = $periode;

        return $this;
    }

    /**
     * Get periode
     *
     * @return \Aeag\SqeBundle\Entity\PgProgPeriodes
     */
    public function getPeriode() {
        return $this->periode;
    }

}
