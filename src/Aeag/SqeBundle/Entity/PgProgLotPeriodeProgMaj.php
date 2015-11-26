<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotPeriodeProgMaj
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
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgLotPeriodeProgMajRepository")
 */
class PgProgLotPeriodeProgMaj {

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
     * @ORM\Column(name="grpar_an_id", type="decimal", precision=38, scale=0, nullable=false)
     */
    private $grparAn;

    /**
     * @ORM\Column(name="station_an_id", type="decimal", precision=38, scale=0, nullable=false)
     */
    private $stationAn;

    /**
     *  @ORM\Column(name="periodan_id", type="decimal", precision=38, scale=0, nullable=false)
     */
    private $periodan;

    /**
     * @ORM\Column(name="pprog_compl_id", type="decimal", precision=38, scale=0, nullable=false)
     */
    private $pprogCompl;

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

    function getGrparAn() {
        return $this->grparAn;
    }

    function getStationAn() {
        return $this->stationAn;
    }

    function getPeriodan() {
        return $this->periodan;
    }

    function getPprogCompl() {
        return $this->pprogCompl;
    }

    function getStatut() {
        return $this->statut;
    }

    function setGrparAn($grparAn) {
        $this->grparAn = $grparAn;
    }

    function setStationAn($stationAn) {
        $this->stationAn = $stationAn;
    }

    function setPeriodan($periodan) {
        $this->periodan = $periodan;
    }

    function setPprogCompl($pprogCompl) {
        $this->pprogCompl = $pprogCompl;
    }

    function setStatut($statut) {
        $this->statut = $statut;
    }

}
