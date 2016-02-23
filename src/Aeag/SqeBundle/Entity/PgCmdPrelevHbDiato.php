<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdPrelevHbDiato
 *
 * @ORM\Table(name="pg_cmd_prelev_hb_diato")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdPrelevHbDiatoRepository")
 */
class PgCmdPrelevHbDiato
{
    /**
     * @var string
     *
     * @ORM\Column(name="x_prel", type="decimal", precision=12, scale=5, nullable=true)
     */
    private $xPrel;

    /**
     * @var string
     *
     * @ORM\Column(name="y_prel", type="decimal", precision=12, scale=5, nullable=true)
     */
    private $yPrel;

    /**
     * @var string
     *
     * @ORM\Column(name="temp_eau", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $tempEau;

    /**
     * @var string
     *
     * @ORM\Column(name="ph", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $ph;

    /**
     * @var string
     *
     * @ORM\Column(name="conductivite", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $conductivite;

    /**
     * @var string
     *
     * @ORM\Column(name="largeur", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $largeur;

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
     * @ORM\Column(name="ombrage", type="string", length=12, nullable=true)
     */
    private $ombrage;

    /**
     * @var string
     *
     * @ORM\Column(name="conditions_hydro", type="string", length=20, nullable=true)
     */
    private $conditionsHydro;

    /**
     * @var \PgCmdPrelev
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="PgCmdPrelev")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prelev_id", referencedColumnName="id")
     * })
     */
    private $prelev;



    /**
     * Set xPrel
     *
     * @param string $xPrel
     *
     * @return PgCmdPrelevHbDiato
     */
    public function setXPrel($xPrel)
    {
        $this->xPrel = $xPrel;

        return $this;
    }

    /**
     * Get xPrel
     *
     * @return string
     */
    public function getXPrel()
    {
        return $this->xPrel;
    }

    /**
     * Set yPrel
     *
     * @param string $yPrel
     *
     * @return PgCmdPrelevHbDiato
     */
    public function setYPrel($yPrel)
    {
        $this->yPrel = $yPrel;

        return $this;
    }

    /**
     * Get yPrel
     *
     * @return string
     */
    public function getYPrel()
    {
        return $this->yPrel;
    }

    /**
     * Set tempEau
     *
     * @param string $tempEau
     *
     * @return PgCmdPrelevHbDiato
     */
    public function setTempEau($tempEau)
    {
        $this->tempEau = $tempEau;

        return $this;
    }

    /**
     * Get tempEau
     *
     * @return string
     */
    public function getTempEau()
    {
        return $this->tempEau;
    }

    /**
     * Set ph
     *
     * @param string $ph
     *
     * @return PgCmdPrelevHbDiato
     */
    public function setPh($ph)
    {
        $this->ph = $ph;

        return $this;
    }

    /**
     * Get ph
     *
     * @return string
     */
    public function getPh()
    {
        return $this->ph;
    }

    /**
     * Set conductivite
     *
     * @param string $conductivite
     *
     * @return PgCmdPrelevHbDiato
     */
    public function setConductivite($conductivite)
    {
        $this->conductivite = $conductivite;

        return $this;
    }

    /**
     * Get conductivite
     *
     * @return string
     */
    public function getConductivite()
    {
        return $this->conductivite;
    }

    /**
     * Set largeur
     *
     * @param string $largeur
     *
     * @return PgCmdPrelevHbDiato
     */
    public function setLargeur($largeur)
    {
        $this->largeur = $largeur;

        return $this;
    }

    /**
     * Get largeur
     *
     * @return string
     */
    public function getLargeur()
    {
        return $this->largeur;
    }

    /**
     * Set substrat
     *
     * @param string $substrat
     *
     * @return PgCmdPrelevHbDiato
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
     * @return PgCmdPrelevHbDiato
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
     * Set ombrage
     *
     * @param string $ombrage
     *
     * @return PgCmdPrelevHbDiato
     */
    public function setOmbrage($ombrage)
    {
        $this->ombrage = $ombrage;

        return $this;
    }

    /**
     * Get ombrage
     *
     * @return string
     */
    public function getOmbrage()
    {
        return $this->ombrage;
    }

    /**
     * Set conditionsHydro
     *
     * @param string $conditionsHydro
     *
     * @return PgCmdPrelevHbDiato
     */
    public function setConditionsHydro($conditionsHydro)
    {
        $this->conditionsHydro = $conditionsHydro;

        return $this;
    }

    /**
     * Get conditionsHydro
     *
     * @return string
     */
    public function getConditionsHydro()
    {
        return $this->conditionsHydro;
    }

    /**
     * Set prelev
     *
     * @param \Aeag\SqeBundle\Entity\PgCmdPrelev $prelev
     *
     * @return PgCmdPrelevHbDiato
     */
    public function setPrelev(\Aeag\SqeBundle\Entity\PgCmdPrelev $prelev)
    {
        $this->prelev = $prelev;

        return $this;
    }

    /**
     * Get prelev
     *
     * @return \Aeag\SqeBundle\Entity\PgCmdPrelev
     */
    public function getPrelev()
    {
        return $this->prelev;
    }
}
