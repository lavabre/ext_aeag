<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdInvertPrelem
 *
 * @ORM\Table(name="pg_cmd_invert_prelem", indexes={@ORM\Index(name="IDX_8E5F1932D8E1F6AA", columns={"prelev_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdInvertPrelemRepository")
 */
class PgCmdInvertPrelem
{
    /**
     * @var string
     *
     * @ORM\Column(name="prelem", type="string", length=3, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $prelem;

    /**
     * @var string
     *
     * @ORM\Column(name="substrat", type="string", length=3, nullable=true)
     */
    private $substrat;

    /**
     * @var string
     *
     * @ORM\Column(name="vitesse", type="string", length=2, nullable=true)
     */
    private $vitesse;

    /**
     * @var string
     *
     * @ORM\Column(name="phase", type="string", length=5, nullable=true)
     */
    private $phase;

    /**
     * @var string
     *
     * @ORM\Column(name="hauteur_eau", type="string", length=12, nullable=true)
     */
    private $hauteurEau;

    /**
     * @var string
     *
     * @ORM\Column(name="colmatage", type="string", length=1, nullable=true)
     */
    private $colmatage;

    /**
     * @var string
     *
     * @ORM\Column(name="stabilite", type="string", length=12, nullable=true)
     */
    private $stabilite;

    /**
     * @var string
     *
     * @ORM\Column(name="nature_veget", type="string", length=50, nullable=true)
     */
    private $natureVeget;

    /**
     * @var string
     *
     * @ORM\Column(name="abond_veget", type="string", length=1, nullable=true)
     */
    private $abondVeget;

    /**
     * @var string
     *
     * @ORM\Column(name="technique_prel", type="string", length=50, nullable=true)
     */
    private $techniquePrel;

    /**
     * @var \PgCmdPrelevHbInvert
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="PgCmdPrelevHbInvert")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prelev_id", referencedColumnName="prelev_id")
     * })
     */
    private $prelev;



    /**
     * Set prelem
     *
     * @param string $prelem
     *
     * @return PgCmdInvertPrelem
     */
    public function setPrelem($prelem)
    {
        $this->prelem = $prelem;

        return $this;
    }

    /**
     * Get prelem
     *
     * @return string
     */
    public function getPrelem()
    {
        return $this->prelem;
    }

    /**
     * Set substrat
     *
     * @param string $substrat
     *
     * @return PgCmdInvertPrelem
     */
    public function setSubstrat($substrat)
    {
        $this->substrat = $substrat;

        return $this;
    }

    /**
     * Get substrat
     *
     * @return string
     */
    public function getSubstrat()
    {
        return $this->substrat;
    }

    /**
     * Set vitesse
     *
     * @param string $vitesse
     *
     * @return PgCmdInvertPrelem
     */
    public function setVitesse($vitesse)
    {
        $this->vitesse = $vitesse;

        return $this;
    }

    /**
     * Get vitesse
     *
     * @return string
     */
    public function getVitesse()
    {
        return $this->vitesse;
    }

    /**
     * Set phase
     *
     * @param string $phase
     *
     * @return PgCmdInvertPrelem
     */
    public function setPhase($phase)
    {
        $this->phase = $phase;

        return $this;
    }

    /**
     * Get phase
     *
     * @return string
     */
    public function getPhase()
    {
        return $this->phase;
    }

    /**
     * Set hauteurEau
     *
     * @param string $hauteurEau
     *
     * @return PgCmdInvertPrelem
     */
    public function setHauteurEau($hauteurEau)
    {
        $this->hauteurEau = $hauteurEau;

        return $this;
    }

    /**
     * Get hauteurEau
     *
     * @return string
     */
    public function getHauteurEau()
    {
        return $this->hauteurEau;
    }

    /**
     * Set colmatage
     *
     * @param string $colmatage
     *
     * @return PgCmdInvertPrelem
     */
    public function setColmatage($colmatage)
    {
        $this->colmatage = $colmatage;

        return $this;
    }

    /**
     * Get colmatage
     *
     * @return string
     */
    public function getColmatage()
    {
        return $this->colmatage;
    }

    /**
     * Set stabilite
     *
     * @param string $stabilite
     *
     * @return PgCmdInvertPrelem
     */
    public function setStabilite($stabilite)
    {
        $this->stabilite = $stabilite;

        return $this;
    }

    /**
     * Get stabilite
     *
     * @return string
     */
    public function getStabilite()
    {
        return $this->stabilite;
    }

    /**
     * Set natureVeget
     *
     * @param string $natureVeget
     *
     * @return PgCmdInvertPrelem
     */
    public function setNatureVeget($natureVeget)
    {
        $this->natureVeget = $natureVeget;

        return $this;
    }

    /**
     * Get natureVeget
     *
     * @return string
     */
    public function getNatureVeget()
    {
        return $this->natureVeget;
    }

    /**
     * Set abondVeget
     *
     * @param string $abondVeget
     *
     * @return PgCmdInvertPrelem
     */
    public function setAbondVeget($abondVeget)
    {
        $this->abondVeget = $abondVeget;

        return $this;
    }

    /**
     * Get abondVeget
     *
     * @return string
     */
    public function getAbondVeget()
    {
        return $this->abondVeget;
    }

    /**
     * Set techniquePrel
     *
     * @param string $techniquePrel
     *
     * @return PgCmdInvertPrelem
     */
    public function setTechniquePrel($techniquePrel)
    {
        $this->techniquePrel = $techniquePrel;

        return $this;
    }

    /**
     * Get techniquePrel
     *
     * @return string
     */
    public function getTechniquePrel()
    {
        return $this->techniquePrel;
    }

    /**
     * Set prelev
     *
     * @param \Aeag\SqeBundle\Entity\PgCmdPrelevHbInvert $prelev
     *
     * @return PgCmdInvertPrelem
     */
    public function setPrelev(\Aeag\SqeBundle\Entity\PgCmdPrelevHbInvert $prelev)
    {
        $this->prelev = $prelev;

        return $this;
    }

    /**
     * Get prelev
     *
     * @return \Aeag\SqeBundle\Entity\PgCmdPrelevHbInvert
     */
    public function getPrelev()
    {
        return $this->prelev;
    }
}
