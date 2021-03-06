<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Lwbody
 *
 * @ORM\Table(name="lwbody")
 * @ORM\Entity
 */
class Lwbody
{
    /**
     * @var string
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="lwbody_eu_cd_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="alt_cat", type="string", length=4, nullable=true)
     */
    private $altCat;

    /**
     * @var string
     *
     * @ORM\Column(name="geol_cat", type="string", length=1, nullable=true)
     */
    private $geolCat;

    /**
     * @var string
     *
     * @ORM\Column(name="size_cat", type="string", length=2, nullable=true)
     */
    private $sizeCat;

    /**
     * @var string
     *
     * @ORM\Column(name="depth_cat", type="string", length=1, nullable=true)
     */
    private $depthCat;

    /**
     * @var float
     *
     * @ORM\Column(name="alt", type="float", precision=10, scale=0, nullable=true)
     */
    private $alt;

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
     * @var float
     *
     * @ORM\Column(name="depth", type="float", precision=10, scale=0, nullable=true)
     */
    private $depth;

    /**
     * @var float
     *
     * @ORM\Column(name="size", type="float", precision=10, scale=0, nullable=true)
     */
    private $size;

    /**
     * @var float
     *
     * @ORM\Column(name="av_depth", type="float", precision=10, scale=0, nullable=true)
     */
    private $avDepth;

    /**
     * @var string
     *
     * @ORM\Column(name="lake_shape", type="string", length=2, nullable=true)
     */
    private $lakeShape;

    /**
     * @var float
     *
     * @ORM\Column(name="res_time", type="float", precision=10, scale=0, nullable=true)
     */
    private $resTime;

    /**
     * @var string
     *
     * @ORM\Column(name="mixing", type="string", length=2, nullable=true)
     */
    private $mixing;

    /**
     * @var string
     *
     * @ORM\Column(name="critmdo", type="string", length=1, nullable=true)
     */
    private $critmdo;

    /**
     * @var string
     *
     * @ORM\Column(name="cgenesur", type="string", length=8, nullable=true)
     */
    private $cgenesur;

    /**
     * @var string
     *
     * @ORM\Column(name="type_fr", type="string", length=4, nullable=true)
     */
    private $typeFr;

    /**
     * @var string
     *
     * @ORM\Column(name="hydroecor", type="string", length=4, nullable=true)
     */
    private $hydroecor;

    /**
     * @var float
     *
     * @ORM\Column(name="cap_total", type="float", precision=10, scale=0, nullable=true)
     */
    private $capTotal;

    /**
     * @var float
     *
     * @ORM\Column(name="cap_useful", type="float", precision=10, scale=0, nullable=true)
     */
    private $capUseful;

    /**
     * @var float
     *
     * @ORM\Column(name="perimeter", type="float", precision=10, scale=0, nullable=true)
     */
    private $perimeter;

    /**
     * @var string
     *
     * @ORM\Column(name="barrage", type="string", length=1, nullable=true)
     */
    private $barrage;



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
     * @return lwbody
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
     * @return lwbody
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
     * @return lwbody
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
     * Set altCat
     *
     * @param string $altCat
     *
     * @return lwbody
     */
    public function setAltCat($altCat)
    {
        $this->altCat = $altCat;

        return $this;
    }

    /**
     * Get altCat
     *
     * @return string
     */
    public function getAltCat()
    {
        return $this->altCat;
    }

    /**
     * Set geolCat
     *
     * @param string $geolCat
     *
     * @return lwbody
     */
    public function setGeolCat($geolCat)
    {
        $this->geolCat = $geolCat;

        return $this;
    }

    /**
     * Get geolCat
     *
     * @return string
     */
    public function getGeolCat()
    {
        return $this->geolCat;
    }

    /**
     * Set sizeCat
     *
     * @param string $sizeCat
     *
     * @return lwbody
     */
    public function setSizeCat($sizeCat)
    {
        $this->sizeCat = $sizeCat;

        return $this;
    }

    /**
     * Get sizeCat
     *
     * @return string
     */
    public function getSizeCat()
    {
        return $this->sizeCat;
    }

    /**
     * Set depthCat
     *
     * @param string $depthCat
     *
     * @return lwbody
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
     * Set alt
     *
     * @param float $alt
     *
     * @return lwbody
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return float
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Set lat
     *
     * @param float $lat
     *
     * @return lwbody
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
     * @return lwbody
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
     * Set depth
     *
     * @param float $depth
     *
     * @return lwbody
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * Get depth
     *
     * @return float
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Set size
     *
     * @param float $size
     *
     * @return lwbody
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return float
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set avDepth
     *
     * @param float $avDepth
     *
     * @return lwbody
     */
    public function setAvDepth($avDepth)
    {
        $this->avDepth = $avDepth;

        return $this;
    }

    /**
     * Get avDepth
     *
     * @return float
     */
    public function getAvDepth()
    {
        return $this->avDepth;
    }

    /**
     * Set lakeShape
     *
     * @param string $lakeShape
     *
     * @return lwbody
     */
    public function setLakeShape($lakeShape)
    {
        $this->lakeShape = $lakeShape;

        return $this;
    }

    /**
     * Get lakeShape
     *
     * @return string
     */
    public function getLakeShape()
    {
        return $this->lakeShape;
    }

    /**
     * Set resTime
     *
     * @param float $resTime
     *
     * @return lwbody
     */
    public function setResTime($resTime)
    {
        $this->resTime = $resTime;

        return $this;
    }

    /**
     * Get resTime
     *
     * @return float
     */
    public function getResTime()
    {
        return $this->resTime;
    }

    /**
     * Set mixing
     *
     * @param string $mixing
     *
     * @return lwbody
     */
    public function setMixing($mixing)
    {
        $this->mixing = $mixing;

        return $this;
    }

    /**
     * Get mixing
     *
     * @return string
     */
    public function getMixing()
    {
        return $this->mixing;
    }

    /**
     * Set critmdo
     *
     * @param string $critmdo
     *
     * @return lwbody
     */
    public function setCritmdo($critmdo)
    {
        $this->critmdo = $critmdo;

        return $this;
    }

    /**
     * Get critmdo
     *
     * @return string
     */
    public function getCritmdo()
    {
        return $this->critmdo;
    }

    /**
     * Set cgenesur
     *
     * @param string $cgenesur
     *
     * @return lwbody
     */
    public function setCgenesur($cgenesur)
    {
        $this->cgenesur = $cgenesur;

        return $this;
    }

    /**
     * Get cgenesur
     *
     * @return string
     */
    public function getCgenesur()
    {
        return $this->cgenesur;
    }

    /**
     * Set typeFr
     *
     * @param string $typeFr
     *
     * @return lwbody
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

    /**
     * Set hydroecor
     *
     * @param string $hydroecor
     *
     * @return lwbody
     */
    public function setHydroecor($hydroecor)
    {
        $this->hydroecor = $hydroecor;

        return $this;
    }

    /**
     * Get hydroecor
     *
     * @return string
     */
    public function getHydroecor()
    {
        return $this->hydroecor;
    }

    /**
     * Set capTotal
     *
     * @param float $capTotal
     *
     * @return lwbody
     */
    public function setCapTotal($capTotal)
    {
        $this->capTotal = $capTotal;

        return $this;
    }

    /**
     * Get capTotal
     *
     * @return float
     */
    public function getCapTotal()
    {
        return $this->capTotal;
    }

    /**
     * Set capUseful
     *
     * @param float $capUseful
     *
     * @return lwbody
     */
    public function setCapUseful($capUseful)
    {
        $this->capUseful = $capUseful;

        return $this;
    }

    /**
     * Get capUseful
     *
     * @return float
     */
    public function getCapUseful()
    {
        return $this->capUseful;
    }

    /**
     * Set perimeter
     *
     * @param float $perimeter
     *
     * @return lwbody
     */
    public function setPerimeter($perimeter)
    {
        $this->perimeter = $perimeter;

        return $this;
    }

    /**
     * Get perimeter
     *
     * @return float
     */
    public function getPerimeter()
    {
        return $this->perimeter;
    }

    /**
     * Set barrage
     *
     * @param string $barrage
     *
     * @return lwbody
     */
    public function setBarrage($barrage)
    {
        $this->barrage = $barrage;

        return $this;
    }

    /**
     * Get barrage
     *
     * @return string
     */
    public function getBarrage()
    {
        return $this->barrage;
    }
}
