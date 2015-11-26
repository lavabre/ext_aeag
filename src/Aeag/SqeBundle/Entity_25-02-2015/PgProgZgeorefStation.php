<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgZgeorefStation
 *
 * @ORM\Table(name="pg_prog_zgeoref_station")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgZgeorefStationRepository")
 */
class PgProgZgeorefStation {

    /**
     * @var \PgProgZoneGeoRef
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgProgZoneGeoRef")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zgeo_ref_id", referencedColumnName="id")
     * })
     */
    private $zgeoref;

    /**
     * @var \PgRefStationMesure
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgRefStationMesure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="station_id", referencedColumnName="ouv_fonc_id")
     * })
     */
    private $stationMesure;

    
    /*
     * Constructor
     */

    public function __construct() {
        
    }

    function getZgeoref() {
        return $this->zgeoref;
    }

    function getStationMesure() {
        return $this->stationMesure;
    }

   
   

    /**
     * Set zgeoref
     *
     * @param \Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoref
     * @return PgProgZgeorefStation
     */
    public function setZgeoref(\Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoref)
    {
        $this->zgeoref = $zgeoref;

        return $this;
    }

    /**
     * Set stationMesure
     *
     * @param \Aeag\SqeBundle\Entity\PgRefStationMesure $stationMesure
     * @return PgProgZgeorefStation
     */
    public function setStationMesure(\Aeag\SqeBundle\Entity\PgRefStationMesure $stationMesure)
    {
        $this->stationMesure = $stationMesure;

        return $this;
    }
}
