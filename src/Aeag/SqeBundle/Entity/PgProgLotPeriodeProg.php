<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotPeriodeProg
 *
 * @ORM\Table(name="pg_prog_lot_periode_prog", uniqueConstraints={@ORM\UniqueConstraint(name="uk_pg_prog_lot_pprog", columns={"station_an_id", "grpar_an_id", "periodan_id"})},
 *      indexes={@ORM\Index(name="IDX_PgProgLotPeriodeProg_grparAn", columns={"grpar_an_id"}),
 *                    @ORM\Index(name="IDX_PgProgLotPeriodeProg_stationAn", columns={"station_an_id"}), 
 *                    @ORM\Index(name="IDX_PgProgLotPeriodeProg_periodeAn", columns={"periodan_id"}), 
 *                    @ORM\Index(name="IDX_PgProgLotPeriodeProg_pprogCompl", columns={"pprog_compl_id"}),
 *                    @ORM\Index(name="IDX_PgProgLotPeriodeProg_stationAn_periodeAn", columns={"station_an_id,periodan_id"}),
 *                    @ORM\Index(name="IDX_PgProgLotPeriodeProg_stationAn_grparAn", columns={"station_an_id,grparAn_id"}),
 *                    @ORM\Index(name="IDX_PgProgLotPeriodeProg_grparAn_periodeAn", columns={"grpar_an_id, periodan_id"}),
 *                    @ORM\Index(name="IDX_PgProgLotPeriodeProg_grparAn_stationAn_periodeAn", columns={"grpar_an_id, station_an_id, periodan_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgLotPeriodeProgRepository")
 */
class PgProgLotPeriodeProg {

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_lot_periode_prog_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \PgProgLotGrparAn
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotGrparAn")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grpar_an_id", referencedColumnName="id")
     * })
     */
    private $grparAn;

    /**
     * @var \PgProgLotStationAn
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotStationAn")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="station_an_id", referencedColumnName="id")
     * })
     */
    private $stationAn;

    /**
     * @var \PgProgLotPeriodeAn
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotPeriodeAn")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="periodan_id", referencedColumnName="id")
     * })
     */
    private $periodan;

    /**
     * @var \PgProgLotPeriodeProg
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotPeriodeProg")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pprog_compl_id", referencedColumnName="id")
     * })
     */
    private $pprogCompl;

    /**
     * @var string
     *
     * @ORM\Column(name="rsx_id", type="decimal", precision=38, scale=0, nullable=true)
     */
    private $rsxId;

    /**
     * @var string
     *
     * @ORM\Column(name="statut", type="string", length=1, nullable=false)
     */
    private $statut;

    /**
     * Get id
     *
     * @return string 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set grparAn
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotGrparAn $grparAn
     * @return PgProgLotPeriodeProg
     */
    public function setGrparAn(\Aeag\SqeBundle\Entity\PgProgLotGrparAn $grparAn = null) {
        $this->grparAn = $grparAn;

        return $this;
    }

    /**
     * Get grparAn
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotGrparAn 
     */
    public function getGrparAn() {
        return $this->grparAn;
    }

    /**
     * Set stationAn
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotStationAn $stationAn
     * @return PgProgLotPeriodeProg
     */
    public function setStationAn(\Aeag\SqeBundle\Entity\PgProgLotStationAn $stationAn = null) {
        $this->stationAn = $stationAn;

        return $this;
    }

    /**
     * Get stationAn
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotStationAn 
     */
    public function getStationAn() {
        return $this->stationAn;
    }

    /**
     * Set periodan
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotPeriodeAn $periodan
     * @return PgProgLotPeriodeProg
     */
    public function setPeriodan(\Aeag\SqeBundle\Entity\PgProgLotPeriodeAn $periodan = null) {
        $this->periodan = $periodan;

        return $this;
    }

    /**
     * Get periodan
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotPeriodeAn 
     */
    public function getPeriodan() {
        return $this->periodan;
    }

    /**
     * Set pprogCompl
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotPeriodeProg $pprogCompl
     * @return PgProgLotPeriodeProg
     */
    public function setPprogCompl(\Aeag\SqeBundle\Entity\PgProgLotPeriodeProg $pprogCompl = null) {
        $this->pprogCompl = $pprogCompl;

        return $this;
    }

    /**
     * Get pprogCompl
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotPeriodeProg 
     */
    public function getPprogCompl() {
        return $this->pprogCompl;
    }

    function getRsxId() {
        return $this->rsxId;
    }

    function setRsxId($rsxId) {
        $this->rsxId = $rsxId;
    }

    function getStatut() {
        return $this->statut;
    }

    function setStatut($statut) {
        $this->statut = $statut;
    }

}
