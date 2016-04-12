<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\Twbody
 *
 * @ORM\Table(name="twbody")
 * @ORM\Entity
 */
class Twbody
{
    /**
     * @var string $euCd
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="twbody_eu_cd_seq", allocationSize="1", initialValue="1")
     */
    private $euCd;

    /**
     * @var string $regionCd
     *
     * @ORM\Column(name="region_cd", type="string", nullable=true)
     */
    private $regionCd;

    /**
     * @var string $system
     *
     * @ORM\Column(name="system", type="string", nullable=true)
     */
    private $system;

    /**
     * @var string $statusYr
     *
     * @ORM\Column(name="status_yr", type="string", nullable=true)
     */
    private $statusYr;

    /**
     * @var string $salinity
     *
     * @ORM\Column(name="salinity", type="string", nullable=true)
     */
    private $salinity;

    /**
     * @var string $tidal
     *
     * @ORM\Column(name="tidal", type="string", nullable=true)
     */
    private $tidal;

    /**
     * @var float $lat
     *
     * @ORM\Column(name="lat", type="float", nullable=true)
     */
    private $lat;

    /**
     * @var float $lon
     *
     * @ORM\Column(name="lon", type="float", nullable=true)
     */
    private $lon;

    /**
     * @var string $typeFr
     *
     * @ORM\Column(name="type_fr", type="string", nullable=true)
     */
    private $typeFr;



    /**
     * Get euCd
     *
     * @return string 
     */
    public function getEuCd()
    {
        return $this->euCd;
    }

    /**
     * Set regionCd
     *
     * @param string $regionCd
     */
    public function setRegionCd($regionCd)
    {
        $this->regionCd = $regionCd;
    }

    /**
     * Get regionCd
     *
     * @return string 
     */
    public function getRegionCd()
    {
        return $this->regionCd;
    }

    /**
     * Set system
     *
     * @param string $system
     */
    public function setSystem($system)
    {
        $this->system = $system;
    }

    /**
     * Get system
     *
     * @return string 
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * Set statusYr
     *
     * @param string $statusYr
     */
    public function setStatusYr($statusYr)
    {
        $this->statusYr = $statusYr;
    }

    /**
     * Get statusYr
     *
     * @return string 
     */
    public function getStatusYr()
    {
        return $this->statusYr;
    }

    /**
     * Set salinity
     *
     * @param string $salinity
     */
    public function setSalinity($salinity)
    {
        $this->salinity = $salinity;
    }

    /**
     * Get salinity
     *
     * @return string 
     */
    public function getSalinity()
    {
        return $this->salinity;
    }

    /**
     * Set tidal
     *
     * @param string $tidal
     */
    public function setTidal($tidal)
    {
        $this->tidal = $tidal;
    }

    /**
     * Get tidal
     *
     * @return string 
     */
    public function getTidal()
    {
        return $this->tidal;
    }

    /**
     * Set lat
     *
     * @param float $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * Get lat
     *
     * @return float 
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lon
     *
     * @param float $lon
     */
    public function setLon($lon)
    {
        $this->lon = $lon;
    }

    /**
     * Get lon
     *
     * @return float 
     */
    public function getLon()
    {
        return $this->lon;
    }

    /**
     * Set typeFr
     *
     * @param string $typeFr
     */
    public function setTypeFr($typeFr)
    {
        $this->typeFr = $typeFr;
    }

    /**
     * Get typeFr
     *
     * @return string 
     */
    public function getTypeFr()
    {
        return $this->typeFr;
    }
}