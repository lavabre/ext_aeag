<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotPeriodeProg
 *
 * @ORM\Table(name="pg_prog_lot_periode_prog", uniqueConstraints={@ORM\UniqueConstraint(name="uk_pg_prog_lot_pprog", columns={"periode_id", "station_an_id", "grpar_an_id"})}, indexes={@ORM\Index(name="IDX_8E5F4725F384C1CF", columns={"periode_id"}), @ORM\Index(name="IDX_8E5F47256CB2BDCD", columns={"grpar_an_id"}), @ORM\Index(name="IDX_8E5F4725780F4AC4", columns={"station_an_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgLotPeriodeProgRepository")
 */
class PgProgLotPeriodeProg
{
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
     * @var \PgProgPeriodes
     *
     * @ORM\ManyToOne(targetEntity="PgProgPeriodes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="periode_id", referencedColumnName="id")
     * })
     */
    private $periode;

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
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set periode
     *
     * @param \Aeag\SqeBundle\Entity\PgProgPeriodes $periode
     * @return PgProgLotPeriodeProg
     */
    public function setPeriode(\Aeag\SqeBundle\Entity\PgProgPeriodes $periode = null)
    {
        $this->periode = $periode;

        return $this;
    }

    /**
     * Get periode
     *
     * @return \Aeag\SqeBundle\Entity\PgProgPeriodes 
     */
    public function getPeriode()
    {
        return $this->periode;
    }

    /**
     * Set grparAn
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotGrparAn $grparAn
     * @return PgProgLotPeriodeProg
     */
    public function setGrparAn(\Aeag\SqeBundle\Entity\PgProgLotGrparAn $grparAn = null)
    {
        $this->grparAn = $grparAn;

        return $this;
    }

    /**
     * Get grparAn
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotGrparAn 
     */
    public function getGrparAn()
    {
        return $this->grparAn;
    }

    /**
     * Set stationAn
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotStationAn $stationAn
     * @return PgProgLotPeriodeProg
     */
    public function setStationAn(\Aeag\SqeBundle\Entity\PgProgLotStationAn $stationAn = null)
    {
        $this->stationAn = $stationAn;

        return $this;
    }

    /**
     * Get stationAn
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotStationAn 
     */
    public function getStationAn()
    {
        return $this->stationAn;
    }
}
