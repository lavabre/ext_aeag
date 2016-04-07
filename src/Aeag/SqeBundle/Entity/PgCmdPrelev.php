<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdPrelev
 *
 * @ORM\Table(name="pg_cmd_prelev", indexes={@ORM\Index(name="IDX_7025B80C1D51AD8D", columns={"presta_prel_id"}), @ORM\Index(name="IDX_7025B80C80E95E18", columns={"demande_id"}), @ORM\Index(name="IDX_7025B80C21BDB235", columns={"station_id"}), @ORM\Index(name="IDX_7025B80CF384C1CF", columns={"periode_id"}), @ORM\Index(name="IDX_7025B80CB8A3AF39", columns={"code_support"}), @ORM\Index(name="IDX_7025B80CD09615FF", columns={"phase_dmd_id"}), @ORM\Index(name="IDX_7025B80C9A9106A4", columns={"code_methode"}), @ORM\Index(name="IDX_7025B80CC6451FDC", columns={"fichier_rps_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdPrelevRepository")
 */
class PgCmdPrelev {

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_cmd_prelev_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code_prelev_cmd", type="string", length=100, nullable=false)
     */
    private $codePrelevCmd;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_prelev", type="datetime", nullable=true)
     */
    private $datePrelev;

    /**
     * @var string
     *
     * @ORM\Column(name="prof_max", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $profMax;

    /**
     * @var string
     *
     * @ORM\Column(name="realise", type="string", length=1, nullable=true)
     */
    private $realise;

    /**
     * @var \PgRefCorresPresta
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="presta_prel_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $prestaPrel;

    /**
     * @var \PgCmdDemande
     *
     * @ORM\ManyToOne(targetEntity="PgCmdDemande")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="demande_id", referencedColumnName="id")
     * })
     */
    private $demande;

    /**
     * @var \PgRefStationMesure
     *
     * @ORM\ManyToOne(targetEntity="PgRefStationMesure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="station_id", referencedColumnName="ouv_fonc_id")
     * })
     */
    private $station;

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
     * @var \PgSandreSupports
     *
     * @ORM\ManyToOne(targetEntity="PgSandreSupports")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_support", referencedColumnName="code_support")
     * })
     */
    private $codeSupport;

    /**
     * @var \PgProgPhases
     *
     * @ORM\ManyToOne(targetEntity="PgProgPhases")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="phase_dmd_id", referencedColumnName="id")
     * })
     */
    private $phaseDmd;

    /**
     * @var \PgSandreMethodes
     *
     * @ORM\ManyToOne(targetEntity="PgSandreMethodes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_methode", referencedColumnName="code_methode")
     * })
     */
    private $codeMethode;

    /**
     * @var \PgCmdFichiersRps
     *
     * @ORM\ManyToOne(targetEntity="PgCmdFichiersRps")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fichier_rps_id", referencedColumnName="id")
     * })
     */
    private $fichierRps;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgProgLotPeriodeProg", inversedBy="prelev")
     * @ORM\JoinTable(name="pg_cmd_prel_pprog",
     *   joinColumns={
     *     @ORM\JoinColumn(name="prelev_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="pprog_id", referencedColumnName="id")
     *   }
     * )
     */
    private $pprog;

    /**
     * Constructor
     */
    public function __construct() {
        $this->pprog = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set codePrelevCmd
     *
     * @param string $codePrelevCmd
     *
     * @return PgCmdPrelev
     */
    public function setCodePrelevCmd($codePrelevCmd) {
        $this->codePrelevCmd = $codePrelevCmd;

        return $this;
    }

    /**
     * Get codePrelevCmd
     *
     * @return string
     */
    public function getCodePrelevCmd() {
        return $this->codePrelevCmd;
    }

    /**
     * Set datePrelev
     *
     * @param \DateTime $datePrelev
     *
     * @return PgCmdPrelev
     */
    public function setDatePrelev($datePrelev) {
        $this->datePrelev = $datePrelev;

        return $this;
    }

    /**
     * Get datePrelev
     *
     * @return \DateTime
     */
    public function getDatePrelev() {
        return $this->datePrelev;
    }

    function getProfMax() {
        return $this->profMax;
    }

    function setProfMax($profMax) {
        $this->profMax = $profMax;
    }

    /**
     * Set realise
     *
     * @param string $realise
     *
     * @return PgCmdPrelev
     */
    public function setRealise($realise) {
        $this->realise = $realise;

        return $this;
    }

    /**
     * Get realise
     *
     * @return string
     */
    public function getRealise() {
        return $this->realise;
    }

    /**
     * Set prestaPrel
     *
     * @param \Aeag\SqeBundle\Entity\PgRefCorresPresta $prestaPrel
     *
     * @return PgCmdPrelev
     */
    public function setPrestaPrel(\Aeag\SqeBundle\Entity\PgRefCorresPresta $prestaPrel = null) {
        $this->prestaPrel = $prestaPrel;

        return $this;
    }

    /**
     * Get prestaPrel
     *
     * @return \Aeag\SqeBundle\Entity\PgRefCorresPresta
     */
    public function getPrestaPrel() {
        return $this->prestaPrel;
    }

    /**
     * Set demande
     *
     * @param \Aeag\SqeBundle\Entity\PgCmdDemande $demande
     *
     * @return PgCmdPrelev
     */
    public function setDemande(\Aeag\SqeBundle\Entity\PgCmdDemande $demande = null) {
        $this->demande = $demande;

        return $this;
    }

    /**
     * Get demande
     *
     * @return \Aeag\SqeBundle\Entity\PgCmdDemande
     */
    public function getDemande() {
        return $this->demande;
    }

    /**
     * Set station
     *
     * @param \Aeag\SqeBundle\Entity\PgRefStationMesure $station
     *
     * @return PgCmdPrelev
     */
    public function setStation(\Aeag\SqeBundle\Entity\PgRefStationMesure $station = null) {
        $this->station = $station;

        return $this;
    }

    /**
     * Get station
     *
     * @return \Aeag\SqeBundle\Entity\PgRefStationMesure
     */
    public function getStation() {
        return $this->station;
    }

    /**
     * Set periode
     *
     * @param \Aeag\SqeBundle\Entity\PgProgPeriodes $periode
     *
     * @return PgCmdPrelev
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

    /**
     * Set codeSupport
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreSupports $codeSupport
     *
     * @return PgCmdPrelev
     */
    public function setCodeSupport(\Aeag\SqeBundle\Entity\PgSandreSupports $codeSupport = null) {
        $this->codeSupport = $codeSupport;

        return $this;
    }

    /**
     * Get codeSupport
     *
     * @return \Aeag\SqeBundle\Entity\PgSandreSupports
     */
    public function getCodeSupport() {
        return $this->codeSupport;
    }

    /**
     * Set phaseDmd
     *
     * @param \Aeag\SqeBundle\Entity\PgProgPhases $phaseDmd
     *
     * @return PgCmdPrelev
     */
    public function setPhaseDmd(\Aeag\SqeBundle\Entity\PgProgPhases $phaseDmd = null) {
        $this->phaseDmd = $phaseDmd;

        return $this;
    }

    /**
     * Get phaseDmd
     *
     * @return \Aeag\SqeBundle\Entity\PgProgPhases
     */
    public function getPhaseDmd() {
        return $this->phaseDmd;
    }

    /**
     * Set codeMethode
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreMethodes $codeMethode
     *
     * @return PgCmdPrelev
     */
    public function setCodeMethode(\Aeag\SqeBundle\Entity\PgSandreMethodes $codeMethode = null) {
        $this->codeMethode = $codeMethode;

        return $this;
    }

    /**
     * Get codeMethode
     *
     * @return \Aeag\SqeBundle\Entity\PgSandreMethodes
     */
    public function getCodeMethode() {
        return $this->codeMethode;
    }

    /**
     * Set fichierRps
     *
     * @param \Aeag\SqeBundle\Entity\PgCmdFichiersRps $fichierRps
     *
     * @return PgCmdPrelev
     */
    public function setFichierRps(\Aeag\SqeBundle\Entity\PgCmdFichiersRps $fichierRps = null) {
        $this->fichierRps = $fichierRps;

        return $this;
    }

    /**
     * Get fichierRps
     *
     * @return \Aeag\SqeBundle\Entity\PgCmdFichiersRps
     */
    public function getFichierRps() {
        return $this->fichierRps;
    }

    /**
     * Add pprog
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotPeriodeProg $pprog
     *
     * @return PgCmdPrelev
     */
    public function addPprog(\Aeag\SqeBundle\Entity\PgProgLotPeriodeProg $pprog) {
        $this->pprog[] = $pprog;

        return $this;
    }

    /**
     * Remove pprog
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotPeriodeProg $pprog
     */
    public function removePprog(\Aeag\SqeBundle\Entity\PgProgLotPeriodeProg $pprog) {
        $this->pprog->removeElement($pprog);
    }

    /**
     * Get pprog
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPprog() {
        return $this->pprog;
    }

}
