<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Twbody
 *
 * @ORM\Table(name="twbody")
 * @ORM\Entity
 */
class Twbody
{
    /**
     * @var string
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="twbody_eu_cd_seq", allocationSize=1, initialValue=1)
     */
    private $euCd;

    /**
     * @var string
     *
     * @ORM\Column(name="region_cd", type="string", length=2, nullable=true)
     */
    private $regionCd;

    /**
     * @var string
     *
     * @ORM\Column(name="system", type="string", length=1, nullable=true)
     */
    private $system;

    /**
     * @var string
     *
     * @ORM\Column(name="status_yr", type="string", length=4, nullable=true)
     */
    private $statusYr;

    /**
     * @var string
     *
     * @ORM\Column(name="salinity", type="string", length=1, nullable=true)
     */
    private $salinity;

    /**
     * @var string
     *
     * @ORM\Column(name="tidal", type="string", length=5, nullable=true)
     */
    private $tidal;

    /**
     * @var float
     *
     * @ORM\Column(name="lat", type="float", precision=10, scale=0, nullable=true)
     */
    private $lat;

    /**
     * @var float
     *
     * @ORM\Column(name="lon", type="float", precision=10, scale=0, nullable=true)
     */
    private $lon;

    /**
     * @var string
     *
     * @ORM\Column(name="type_fr", type="string", length=8, nullable=true)
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
     *
     * @return twbody
     */
    public function setRegionCd($regionCd)
    {
        $this->regionCd = $regionCd;

        return $this;
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
     *
     * @return twbody
     */
    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
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
     *
     * @return twbody
     */
    public function setStatusYr($statusYr)
    {
        $this->statusYr = $statusYr;

        return $this;
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
     *
     * @return twbody
     */
    public function setSalinity($salinity)
    {
        $this->salinity = $salinity;

        return $this;
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
     *
     * @return twbody
     */
    public function setTidal($tidal)
    {
        $this->tidal = $tidal;

        return $this;
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
     *
     * @return twbody
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
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
     *
     * @return twbody
     */
    public function setLon($lon)
    {
        $this->lon = $lon;

        return $this;
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
     *
     * @return twbody
     */
    public function setTypeFr($typeFr)
    {
        $this->typeFr = $typeFr;

        return $this;
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
