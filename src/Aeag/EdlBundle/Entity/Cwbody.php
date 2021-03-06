<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cwbody
 *
 * @ORM\Table(name="cwbody")
 * @ORM\Entity
 */
class Cwbody
{
    /**
     * @var string
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="cwbody_eu_cd_seq", allocationSize=1, initialValue=1)
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
     * @var string
     *
     * @ORM\Column(name="depth_cat", type="string", length=1, nullable=true)
     */
    private $depthCat;

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
     * @return cwbody
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
     * @return cwbody
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
     * Set salinity
     *
     * @param string $salinity
     *
     * @return cwbody
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
     * @return cwbody
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
     * Set depthCat
     *
     * @param string $depthCat
     *
     * @return cwbody
     */
    public function setDepthCat($depthCat)
    {
        $this->depthCat = $depthCat;

        return $this;
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
     *
     * @return cwbody
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
     * @return cwbody
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
     * @return cwbody
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
