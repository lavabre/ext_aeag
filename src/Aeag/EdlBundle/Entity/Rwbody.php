<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\Rwbody
 *
 * @ORM\Table(name="rwbody")
 * @ORM\Entity
 */
class Rwbody
{
    /**
     * @var string $euCd
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="rwbody_eu_cd_seq", allocationSize="1", initialValue="1")
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
     * @var string $geology
     *
     * @ORM\Column(name="geology", type="string", nullable=true)
     */
    private $geology;

    /**
     * @var string $critmdo
     *
     * @ORM\Column(name="critmdo", type="string", nullable=true)
     */
    private $critmdo;

    /**
     * @var string $ecorEu
     *
     * @ORM\Column(name="ecor_eu", type="string", nullable=true)
     */
    private $ecorEu;

    /**
     * @var string $hydroecor
     *
     * @ORM\Column(name="hydroecor", type="string", nullable=true)
     */
    private $hydroecor;

    /**
     * @var float $size
     *
     * @ORM\Column(name="size", type="float", nullable=true)
     */
    private $size;

    /**
     * @var string $typeFr
     *
     * @ORM\Column(name="type_fr", type="string", nullable=true)
     */
    private $typeFr;

    /**
     * @var string $strahMax
     *
     * @ORM\Column(name="strah_max", type="string", nullable=true)
     */
    private $strahMax;

    /**
     * @var string $strahMin
     *
     * @ORM\Column(name="strah_min", type="string", nullable=true)
     */
    private $strahMin;

    /**
     * @var string $tpme
     *
     * @ORM\Column(name="tpme", type="string", length=4, nullable=true)
     */
    private $tpme;

    /**
     * @var string $ctxPisci
     *
     * @ORM\Column(name="ctx_pisci", type="string", nullable=true)
     */
    private $ctxPisci;

    /**
     * @var decimal $refIbgn
     *
     * @ORM\Column(name="ref_ibgn", type="decimal", nullable=true)
     */
    private $refIbgn;

    /**
     * @var decimal $refIbd
     *
     * @ORM\Column(name="ref_ibd", type="decimal", nullable=true)
     */
    private $refIbd;

    /**
     * @var decimal $refIp
     *
     * @ORM\Column(name="ref_ip", type="decimal", nullable=true)
     */
    private $refIp;

    /**
     * @var decimal $refIbmr
     *
     * @ORM\Column(name="ref_ibmr", type="decimal", nullable=true)
     */
    private $refIbmr;



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
     * Set geology
     *
     * @param string $geology
     */
    public function setGeology($geology)
    {
        $this->geology = $geology;
    }

    /**
     * Get geology
     *
     * @return string 
     */
    public function getGeology()
    {
        return $this->geology;
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
     * Set ecorEu
     *
     * @param string $ecorEu
     */
    public function setEcorEu($ecorEu)
    {
        $this->ecorEu = $ecorEu;
    }

    /**
     * Get ecorEu
     *
     * @return string 
     */
    public function getEcorEu()
    {
        return $this->ecorEu;
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
     * Set strahMax
     *
     * @param string $strahMax
     */
    public function setStrahMax($strahMax)
    {
        $this->strahMax = $strahMax;
    }

    /**
     * Get strahMax
     *
     * @return string 
     */
    public function getStrahMax()
    {
        return $this->strahMax;
    }

    /**
     * Set strahMin
     *
     * @param string $strahMin
     */
    public function setStrahMin($strahMin)
    {
        $this->strahMin = $strahMin;
    }

    /**
     * Get strahMin
     *
     * @return string 
     */
    public function getStrahMin()
    {
        return $this->strahMin;
    }

    /**
     * Set tpme
     *
     * @param string $tpme
     */
    public function setTpme($tpme)
    {
        $this->tpme = $tpme;
    }

    /**
     * Get tpme
     *
     * @return string 
     */
    public function getTpme()
    {
        return $this->tpme;
    }

    /**
     * Set ctxPisci
     *
     * @param string $ctxPisci
     */
    public function setCtxPisci($ctxPisci)
    {
        $this->ctxPisci = $ctxPisci;
    }

    /**
     * Get ctxPisci
     *
     * @return string 
     */
    public function getCtxPisci()
    {
        return $this->ctxPisci;
    }

    /**
     * Set refIbgn
     *
     * @param decimal $refIbgn
     */
    public function setRefIbgn($refIbgn)
    {
        $this->refIbgn = $refIbgn;
    }

    /**
     * Get refIbgn
     *
     * @return decimal 
     */
    public function getRefIbgn()
    {
        return $this->refIbgn;
    }

    /**
     * Set refIbd
     *
     * @param decimal $refIbd
     */
    public function setRefIbd($refIbd)
    {
        $this->refIbd = $refIbd;
    }

    /**
     * Get refIbd
     *
     * @return decimal 
     */
    public function getRefIbd()
    {
        return $this->refIbd;
    }

    /**
     * Set refIp
     *
     * @param decimal $refIp
     */
    public function setRefIp($refIp)
    {
        $this->refIp = $refIp;
    }

    /**
     * Get refIp
     *
     * @return decimal 
     */
    public function getRefIp()
    {
        return $this->refIp;
    }

    /**
     * Set refIbmr
     *
     * @param decimal $refIbmr
     */
    public function setRefIbmr($refIbmr)
    {
        $this->refIbmr = $refIbmr;
    }

    /**
     * Get refIbmr
     *
     * @return decimal 
     */
    public function getRefIbmr()
    {
        return $this->refIbmr;
    }

}