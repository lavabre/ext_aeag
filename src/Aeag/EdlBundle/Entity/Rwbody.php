<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rwbody
 *
 * @ORM\Table(name="rwbody")
 * @ORM\Entity
 */
class Rwbody
{
    /**
     * @var string
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="rwbody_eu_cd_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="geology", type="string", length=254, nullable=true)
     */
    private $geology;

    /**
     * @var string
     *
     * @ORM\Column(name="critmdo", type="string", length=1, nullable=true)
     */
    private $critmdo;

    /**
     * @var string
     *
     * @ORM\Column(name="ecor_eu", type="string", length=2, nullable=true)
     */
    private $ecorEu;

    /**
     * @var string
     *
     * @ORM\Column(name="hydroecor", type="string", length=4, nullable=true)
     */
    private $hydroecor;

    /**
     * @var float
     *
     * @ORM\Column(name="size", type="float", precision=10, scale=0, nullable=true)
     */
    private $size;

    /**
     * @var string
     *
     * @ORM\Column(name="type_fr", type="string", length=9, nullable=true)
     */
    private $typeFr;

    /**
     * @var string
     *
     * @ORM\Column(name="strah_max", type="string", length=2, nullable=true)
     */
    private $strahMax;

    /**
     * @var string
     *
     * @ORM\Column(name="strah_min", type="string", length=2, nullable=true)
     */
    private $strahMin;

    /**
     * @var string
     *
     * @ORM\Column(name="tpme", type="string", length=4, nullable=true)
     */
    private $tpme;

    /**
     * @var string
     *
     * @ORM\Column(name="ctx_pisci", type="string", length=1, nullable=true)
     */
    private $ctxPisci;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_ibgn", type="decimal", precision=4, scale=2, nullable=true)
     */
    private $refIbgn;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_ibd", type="decimal", precision=4, scale=2, nullable=true)
     */
    private $refIbd;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_ip", type="decimal", precision=4, scale=2, nullable=true)
     */
    private $refIp;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_ibmr", type="decimal", precision=4, scale=2, nullable=true)
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
     *
     * @return rwbody
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
     * @return rwbody
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
     * Set altCat
     *
     * @param string $altCat
     *
     * @return rwbody
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
     * @return rwbody
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
     * @return rwbody
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
     * Set lat
     *
     * @param float $lat
     *
     * @return rwbody
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
     * @return rwbody
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
     * Set geology
     *
     * @param string $geology
     *
     * @return rwbody
     */
    public function setGeology($geology)
    {
        $this->geology = $geology;

        return $this;
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
     *
     * @return rwbody
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
     * Set ecorEu
     *
     * @param string $ecorEu
     *
     * @return rwbody
     */
    public function setEcorEu($ecorEu)
    {
        $this->ecorEu = $ecorEu;

        return $this;
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
     *
     * @return rwbody
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
     * Set size
     *
     * @param float $size
     *
     * @return rwbody
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
     * Set typeFr
     *
     * @param string $typeFr
     *
     * @return rwbody
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
     * Set strahMax
     *
     * @param string $strahMax
     *
     * @return rwbody
     */
    public function setStrahMax($strahMax)
    {
        $this->strahMax = $strahMax;

        return $this;
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
     *
     * @return rwbody
     */
    public function setStrahMin($strahMin)
    {
        $this->strahMin = $strahMin;

        return $this;
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
     *
     * @return rwbody
     */
    public function setTpme($tpme)
    {
        $this->tpme = $tpme;

        return $this;
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
     *
     * @return rwbody
     */
    public function setCtxPisci($ctxPisci)
    {
        $this->ctxPisci = $ctxPisci;

        return $this;
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
     * @param string $refIbgn
     *
     * @return rwbody
     */
    public function setRefIbgn($refIbgn)
    {
        $this->refIbgn = $refIbgn;

        return $this;
    }

    /**
     * Get refIbgn
     *
     * @return string
     */
    public function getRefIbgn()
    {
        return $this->refIbgn;
    }

    /**
     * Set refIbd
     *
     * @param string $refIbd
     *
     * @return rwbody
     */
    public function setRefIbd($refIbd)
    {
        $this->refIbd = $refIbd;

        return $this;
    }

    /**
     * Get refIbd
     *
     * @return string
     */
    public function getRefIbd()
    {
        return $this->refIbd;
    }

    /**
     * Set refIp
     *
     * @param string $refIp
     *
     * @return rwbody
     */
    public function setRefIp($refIp)
    {
        $this->refIp = $refIp;

        return $this;
    }

    /**
     * Get refIp
     *
     * @return string
     */
    public function getRefIp()
    {
        return $this->refIp;
    }

    /**
     * Set refIbmr
     *
     * @param string $refIbmr
     *
     * @return rwbody
     */
    public function setRefIbmr($refIbmr)
    {
        $this->refIbmr = $refIbmr;

        return $this;
    }

    /**
     * Get refIbmr
     *
     * @return string
     */
    public function getRefIbmr()
    {
        return $this->refIbmr;
    }
}
