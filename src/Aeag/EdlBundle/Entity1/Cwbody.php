<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\Cwbody
 *
 * @ORM\Table(name="cwbody")
 * @ORM\Entity
 */
class Cwbody
{

    /**
     * @var string $euCd
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
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
     * @var string $depthCat
     *
     * @ORM\Column(name="depth_cat", type="string", nullable=true)
     */
    private $depthCat;

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
     * Set depthCat
     *
     * @param string $depthCat
     */
    public function setDepthCat($depthCat)
    {
        $this->depthCat = $depthCat;
    }

    /**
     * Get depthCat
     *
     * @return string 
     */
    public function getDepthCat()
    {
        return $this->depthCat;
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