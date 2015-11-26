<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotStationAn
 *
 * @ORM\Table(name="pg_prog_lot_station_an", 
 *                       indexes={@ORM\Index(name="IDX_PgProgLotStationAn_station", columns={"station_id"}),
 *                                     @ORM\Index(name="IDX_PgProgLotStationAn_lotan", columns={"lotan_id"}),
 *                                     @ORM\Index(name="IDX_PgProgLotStationAn_rsx", columns={"rsxId"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgLotStationAnRepository")
 */
class PgProgLotStationAn
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_lot_station_an_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="rsx_id", type="decimal", precision=38, scale=0, nullable=true)
     */
    private $rsxId;

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
     * @var \PgProgLotAn
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotAn")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lotan_id", referencedColumnName="id")
     * })
     */
    private $lotan;



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
     * Set rsxId
     *
     * @param string $rsxId
     * @return PgProgLotStationAn
     */
    public function setRsxId($rsxId)
    {
        $this->rsxId = $rsxId;

        return $this;
    }

    /**
     * Get rsxId
     *
     * @return string 
     */
    public function getRsxId()
    {
        return $this->rsxId;
    }

    /**
     * Set station
     *
     * @param \Aeag\SqeBundle\Entity\PgRefStationMesure $station
     * @return PgProgLotStationAn
     */
    public function setStation($station = null)
    {
        $this->station = $station;

        return $this;
    }

    /**
     * Get station
     *
     * @return \Aeag\SqeBundle\Entity\PgRefStationMesure 
     */
    public function getStation()
    {
        return $this->station;
    }

    /**
     * Set lotan
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotAn $lotan
     * @return PgProgLotStationAn
     */
    public function setLotan($lotan = null)
    {
        $this->lotan = $lotan;

        return $this;
    }

    /**
     * Get lotan
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotAn 
     */
    public function getLotan()
    {
        return $this->lotan;
    }
}
