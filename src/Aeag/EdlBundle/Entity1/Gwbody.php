<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\Gwbody
 *
 * @ORM\Table(name="gwbody")
 * @ORM\Entity
 */
class Gwbody
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
     * @var string $statusYr
     *
     * @ORM\Column(name="status_yr", type="string", nullable=true)
     */
    private $statusYr;

    /**
     * @var float $surfKm2
     *
     * @ORM\Column(name="surf_km2", type="float", nullable=true)
     */
    private $surfKm2;

    /**
     * @var float $surAff
     *
     * @ORM\Column(name="sur_aff", type="float", nullable=true)
     */
    private $surAff;

    /**
     * @var float $surfSsc
     *
     * @ORM\Column(name="surf_ssc", type="float", nullable=true)
     */
    private $surfSsc;

    /**
     * @var string $transdist
     *
     * @ORM\Column(name="transdist", type="string", nullable=true)
     */
    private $transdist;

    /**
     * @var string $transfron
     *
     * @ORM\Column(name="transfron", type="string", nullable=true)
     */
    private $transfron;

    /**
     * @var string $typeFr
     *
     * @ORM\Column(name="type_fr", type="string", nullable=true)
     */
    private $typeFr;

    /**
     * @var string $diss
     *
     * @ORM\Column(name="diss", type="string", nullable=true)
     */
    private $diss;

    /**
     * @var string $libre
     *
     * @ORM\Column(name="libre", type="string", nullable=true)
     */
    private $libre;

    /**
     * @var string $captif
     *
     * @ORM\Column(name="captif", type="string", nullable=true)
     */
    private $captif;

    /**
     * @var string $assCaptif
     *
     * @ORM\Column(name="ass_captif", type="string", nullable=true)
     */
    private $assCaptif;

    /**
     * @var string $assLibre
     *
     * @ORM\Column(name="ass_libre", type="string", nullable=true)
     */
    private $assLibre;

    /**
     * @var string $karstique
     *
     * @ORM\Column(name="karstique", type="string", nullable=true)
     */
    private $karstique;

    /**
     * @var string $frangeLit
     *
     * @ORM\Column(name="frange_lit", type="string", nullable=true)
     */
    private $frangeLit;

    /**
     * @var string $regroupe
     *
     * @ORM\Column(name="regroupe", type="string", nullable=true)
     */
    private $regroupe;

    /**
     * @var string $comment
     *
     * @ORM\Column(name="comment", type="string", nullable=true)
     */
    private $comment;

    /**
     * @var string $codeMasdoGoc
     *
     * @ORM\Column(name="code_masdo_goc", type="string", nullable=true)
     */
    private $codeMasdoGoc;



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
     * Set surfKm2
     *
     * @param float $surfKm2
     */
    public function setSurfKm2($surfKm2)
    {
        $this->surfKm2 = $surfKm2;
    }

    /**
     * Get surfKm2
     *
     * @return float 
     */
    public function getSurfKm2()
    {
        return $this->surfKm2;
    }

    /**
     * Set surAff
     *
     * @param float $surAff
     */
    public function setSurAff($surAff)
    {
        $this->surAff = $surAff;
    }

    /**
     * Get surAff
     *
     * @return float 
     */
    public function getSurAff()
    {
        return $this->surAff;
    }

    /**
     * Set surfSsc
     *
     * @param float $surfSsc
     */
    public function setSurfSsc($surfSsc)
    {
        $this->surfSsc = $surfSsc;
    }

    /**
     * Get surfSsc
     *
     * @return float 
     */
    public function getSurfSsc()
    {
        return $this->surfSsc;
    }

    /**
     * Set transdist
     *
     * @param string $transdist
     */
    public function setTransdist($transdist)
    {
        $this->transdist = $transdist;
    }

    /**
     * Get transdist
     *
     * @return string 
     */
    public function getTransdist()
    {
        return $this->transdist;
    }

    /**
     * Set transfron
     *
     * @param string $transfron
     */
    public function setTransfron($transfron)
    {
        $this->transfron = $transfron;
    }

    /**
     * Get transfron
     *
     * @return string 
     */
    public function getTransfron()
    {
        return $this->transfron;
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
     * Set diss
     *
     * @param string $diss
     */
    public function setDiss($diss)
    {
        $this->diss = $diss;
    }

    /**
     * Get diss
     *
     * @return string 
     */
    public function getDiss()
    {
        return $this->diss;
    }

    /**
     * Set libre
     *
     * @param string $libre
     */
    public function setLibre($libre)
    {
        $this->libre = $libre;
    }

    /**
     * Get libre
     *
     * @return string 
     */
    public function getLibre()
    {
        return $this->libre;
    }

    /**
     * Set captif
     *
     * @param string $captif
     */
    public function setCaptif($captif)
    {
        $this->captif = $captif;
    }

    /**
     * Get captif
     *
     * @return string 
     */
    public function getCaptif()
    {
        return $this->captif;
    }

    /**
     * Set assCaptif
     *
     * @param string $assCaptif
     */
    public function setAssCaptif($assCaptif)
    {
        $this->assCaptif = $assCaptif;
    }

    /**
     * Get assCaptif
     *
     * @return string 
     */
    public function getAssCaptif()
    {
        return $this->assCaptif;
    }

    /**
     * Set assLibre
     *
     * @param string $assLibre
     */
    public function setAssLibre($assLibre)
    {
        $this->assLibre = $assLibre;
    }

    /**
     * Get assLibre
     *
     * @return string 
     */
    public function getAssLibre()
    {
        return $this->assLibre;
    }

    /**
     * Set karstique
     *
     * @param string $karstique
     */
    public function setKarstique($karstique)
    {
        $this->karstique = $karstique;
    }

    /**
     * Get karstique
     *
     * @return string 
     */
    public function getKarstique()
    {
        return $this->karstique;
    }

    /**
     * Set frangeLit
     *
     * @param string $frangeLit
     */
    public function setFrangeLit($frangeLit)
    {
        $this->frangeLit = $frangeLit;
    }

    /**
     * Get frangeLit
     *
     * @return string 
     */
    public function getFrangeLit()
    {
        return $this->frangeLit;
    }

    /**
     * Set regroupe
     *
     * @param string $regroupe
     */
    public function setRegroupe($regroupe)
    {
        $this->regroupe = $regroupe;
    }

    /**
     * Get regroupe
     *
     * @return string 
     */
    public function getRegroupe()
    {
        return $this->regroupe;
    }

    /**
     * Set comment
     *
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set codeMasdoGoc
     *
     * @param string $codeMasdoGoc
     */
    public function setCodeMasdoGoc($codeMasdoGoc)
    {
        $this->codeMasdoGoc = $codeMasdoGoc;
    }

    /**
     * Get codeMasdoGoc
     *
     * @return string 
     */
    public function getCodeMasdoGoc()
    {
        return $this->codeMasdoGoc;
    }

}