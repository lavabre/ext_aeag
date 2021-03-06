<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Gwbody
 *
 * @ORM\Table(name="gwbody")
 * @ORM\Entity
 */
class Gwbody
{
    /**
     * @var string
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="gwbody_eu_cd_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="status_yr", type="string", length=8, nullable=true)
     */
    private $statusYr;

    /**
     * @var float
     *
     * @ORM\Column(name="surf_km2", type="float", precision=10, scale=0, nullable=true)
     */
    private $surfKm2;

    /**
     * @var float
     *
     * @ORM\Column(name="sur_aff", type="float", precision=10, scale=0, nullable=true)
     */
    private $surAff;

    /**
     * @var float
     *
     * @ORM\Column(name="surf_ssc", type="float", precision=10, scale=0, nullable=true)
     */
    private $surfSsc;

    /**
     * @var string
     *
     * @ORM\Column(name="transdist", type="string", length=11, nullable=true)
     */
    private $transdist;

    /**
     * @var string
     *
     * @ORM\Column(name="transfron", type="string", length=8, nullable=true)
     */
    private $transfron;

    /**
     * @var string
     *
     * @ORM\Column(name="type_fr", type="string", length=13, nullable=true)
     */
    private $typeFr;

    /**
     * @var string
     *
     * @ORM\Column(name="diss", type="string", length=11, nullable=true)
     */
    private $diss;

    /**
     * @var string
     *
     * @ORM\Column(name="libre", type="string", length=11, nullable=true)
     */
    private $libre;

    /**
     * @var string
     *
     * @ORM\Column(name="captif", type="string", length=7, nullable=true)
     */
    private $captif;

    /**
     * @var string
     *
     * @ORM\Column(name="ass_captif", type="string", length=7, nullable=true)
     */
    private $assCaptif;

    /**
     * @var string
     *
     * @ORM\Column(name="ass_libre", type="string", length=7, nullable=true)
     */
    private $assLibre;

    /**
     * @var string
     *
     * @ORM\Column(name="karstique", type="string", length=7, nullable=true)
     */
    private $karstique;

    /**
     * @var string
     *
     * @ORM\Column(name="frange_lit", type="string", length=7, nullable=true)
     */
    private $frangeLit;

    /**
     * @var string
     *
     * @ORM\Column(name="regroupe", type="string", length=7, nullable=true)
     */
    private $regroupe;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=254, nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="code_masdo_goc", type="string", length=10, nullable=true)
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
     *
     * @return gwbody
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
     * Set statusYr
     *
     * @param string $statusYr
     *
     * @return gwbody
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
     * Set surfKm2
     *
     * @param float $surfKm2
     *
     * @return gwbody
     */
    public function setSurfKm2($surfKm2)
    {
        $this->surfKm2 = $surfKm2;

        return $this;
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
     *
     * @return gwbody
     */
    public function setSurAff($surAff)
    {
        $this->surAff = $surAff;

        return $this;
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
     *
     * @return gwbody
     */
    public function setSurfSsc($surfSsc)
    {
        $this->surfSsc = $surfSsc;

        return $this;
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
     *
     * @return gwbody
     */
    public function setTransdist($transdist)
    {
        $this->transdist = $transdist;

        return $this;
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
     *
     * @return gwbody
     */
    public function setTransfron($transfron)
    {
        $this->transfron = $transfron;

        return $this;
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
     *
     * @return gwbody
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
     * Set diss
     *
     * @param string $diss
     *
     * @return gwbody
     */
    public function setDiss($diss)
    {
        $this->diss = $diss;

        return $this;
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
     *
     * @return gwbody
     */
    public function setLibre($libre)
    {
        $this->libre = $libre;

        return $this;
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
     *
     * @return gwbody
     */
    public function setCaptif($captif)
    {
        $this->captif = $captif;

        return $this;
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
     *
     * @return gwbody
     */
    public function setAssCaptif($assCaptif)
    {
        $this->assCaptif = $assCaptif;

        return $this;
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
     *
     * @return gwbody
     */
    public function setAssLibre($assLibre)
    {
        $this->assLibre = $assLibre;

        return $this;
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
     *
     * @return gwbody
     */
    public function setKarstique($karstique)
    {
        $this->karstique = $karstique;

        return $this;
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
     *
     * @return gwbody
     */
    public function setFrangeLit($frangeLit)
    {
        $this->frangeLit = $frangeLit;

        return $this;
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
     *
     * @return gwbody
     */
    public function setRegroupe($regroupe)
    {
        $this->regroupe = $regroupe;

        return $this;
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
     *
     * @return gwbody
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
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
     *
     * @return gwbody
     */
    public function setCodeMasdoGoc($codeMasdoGoc)
    {
        $this->codeMasdoGoc = $codeMasdoGoc;

        return $this;
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
