<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\Lwbody
 *
 * @ORM\Table(name="lwbody")
 * @ORM\Entity
 */
class Lwbody
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
     * @var string $statusYr
     *
     * @ORM\Column(name="status_yr", type="string", nullable=true)
     */
    private $statusYr;

    /**
     * @var string $altCat
     *
     * @ORM\Column(name="alt_cat", type="string", nullable=true)
     */
    private $altCat;

    /**
     * @var string $geolCat
     *
     * @ORM\Column(name="geol_cat", type="string", nullable=true)
     */
    private $geolCat;

    /**
     * @var string $sizeCat
     *
     * @ORM\Column(name="size_cat", type="string", nullable=true)
     */
    private $sizeCat;

    /**
     * @var string $depthCat
     *
     * @ORM\Column(name="depth_cat", type="string", nullable=true)
     */
    private $depthCat;

    /**
     * @var float $alt
     *
     * @ORM\Column(name="alt", type="float", nullable=true)
     */
    private $alt;

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
     * @var float $depth
     *
     * @ORM\Column(name="depth", type="float", nullable=true)
     */
    private $depth;

    /**
     * @var float $size
     *
     * @ORM\Column(name="size", type="float", nullable=true)
     */
    private $size;

    /**
     * @var float $avDepth
     *
     * @ORM\Column(name="av_depth", type="float", nullable=true)
     */
    private $avDepth;

    /**
     * @var string $lakeShape
     *
     * @ORM\Column(name="lake_shape", type="string", nullable=true)
     */
    private $lakeShape;

    /**
     * @var float $resTime
     *
     * @ORM\Column(name="res_time", type="float", nullable=true)
     */
    private $resTime;

    /**
     * @var string $mixing
     *
     * @ORM\Column(name="mixing", type="string", nullable=true)
     */
    private $mixing;

    /**
     * @var string $critmdo
     *
     * @ORM\Column(name="critmdo", type="string", nullable=true)
     */
    private $critmdo;

    /**
     * @var string $cgenesur
     *
     * @ORM\Column(name="cgenesur", type="string", nullable=true)
     */
    private $cgenesur;

    /**
     * @var string $typeFr
     *
     * @ORM\Column(name="type_fr", type="string", nullable=true)
     */
    private $typeFr;

    /**
     * @var string $hydroecor
     *
     * @ORM\Column(name="hydroecor", type="string", nullable=true)
     */
    private $hydroecor;

    /**
     * @var float $capTotal
     *
     * @ORM\Column(name="cap_total", type="float", nullable=true)
     */
    private $capTotal;

    /**
     * @var float $capUseful
     *
     * @ORM\Column(name="cap_useful", type="float", nullable=true)
     */
    private $capUseful;

    /**
     * @var float $perimeter
     *
     * @ORM\Column(name="perimeter", type="float", nullable=true)
     */
    private $perimeter;

    /**
     * @var string $barrage
     *
     * @ORM\Column(name="barrage", type="string", nullable=true)
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
     * Set altCat
     *
     * @param string $altCat
     */
    public function setAltCat($altCat)
    {
        $this->altCat = $altCat;
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
     */
    public function setGeolCat($geolCat)
    {
        $this->geolCat = $geolCat;
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
     */
    public function setSizeCat($sizeCat)
    {
        $this->sizeCat = $sizeCat;
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
     * Set alt
     *
     * @param float $alt
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;
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
     * Set depth
     *
     * @param float $depth
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
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
     */
    public function setSize($size)
    {
        $this->size = $size;
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
     */
    public function setAvDepth($avDepth)
    {
        $this->avDepth = $avDepth;
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
     */
    public function setLakeShape($lakeShape)
    {
        $this->lakeShape = $lakeShape;
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
     */
    public function setResTime($resTime)
    {
        $this->resTime = $resTime;
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
     */
    public function setMixing($mixing)
    {
        $this->mixing = $mixing;
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
     */
    public function setCritmdo($critmdo)
    {
        $this->critmdo = $critmdo;
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
     */
    public function setCgenesur($cgenesur)
    {
        $this->cgenesur = $cgenesur;
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

    /**
     * Set hydroecor
     *
     * @param string $hydroecor
     */
    public function setHydroecor($hydroecor)
    {
        $this->hydroecor = $hydroecor;
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
     */
    public function setCapTotal($capTotal)
    {
        $this->capTotal = $capTotal;
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
     */
    public function setCapUseful($capUseful)
    {
        $this->capUseful = $capUseful;
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
     */
    public function setPerimeter($perimeter)
    {
        $this->perimeter = $perimeter;
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
     */
    public function setBarrage($barrage)
    {
        $this->barrage = $barrage;
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