<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdPrelevHbMphyt
 *
 * @ORM\Table(name="pg_cmd_prelev_hb_mphyt")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdPrelevHbMphytRepository")
 */
class PgCmdPrelevHbMphyt
{
    /**
     * @var string
     *
     * @ORM\Column(name="x_amont", type="decimal", precision=12, scale=5, nullable=true)
     */
    private $xAmont;

    /**
     * @var string
     *
     * @ORM\Column(name="y_amont", type="decimal", precision=12, scale=5, nullable=true)
     */
    private $yAmont;

    /**
     * @var string
     *
     * @ORM\Column(name="rive_coord", type="string", length=6, nullable=true)
     */
    private $riveCoord;

    /**
     * @var string
     *
     * @ORM\Column(name="longueur", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $longueur;

    /**
     * @var string
     *
     * @ORM\Column(name="largeur", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $largeur;

    /**
     * @var string
     *
     * @ORM\Column(name="conditions_hydro", type="string", length=20, nullable=true)
     */
    private $conditionsHydro;

    /**
     * @var string
     *
     * @ORM\Column(name="conditions_meteo", type="string", length=30, nullable=true)
     */
    private $conditionsMeteo;

    /**
     * @var string
     *
     * @ORM\Column(name="turbidite", type="string", length=20, nullable=true)
     */
    private $turbidite;

    /**
     * @var string
     *
     * @ORM\Column(name="observations", type="string", length=500, nullable=true)
     */
    private $observations;

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
     * Set xAmont
     *
     * @param string $xAmont
     *
     * @return PgCmdPrelevHbMphyt
     */
    public function setXAmont($xAmont)
    {
        $this->xAmont = $xAmont;

        return $this;
    }

    /**
     * Get xAmont
     *
     * @return string
     */
    public function getXAmont()
    {
        return $this->xAmont;
    }

    /**
     * Set yAmont
     *
     * @param string $yAmont
     *
     * @return PgCmdPrelevHbMphyt
     */
    public function setYAmont($yAmont)
    {
        $this->yAmont = $yAmont;

        return $this;
    }

    /**
     * Get yAmont
     *
     * @return string
     */
    public function getYAmont()
    {
        return $this->yAmont;
    }

    /**
     * Set riveCoord
     *
     * @param string $riveCoord
     *
     * @return PgCmdPrelevHbMphyt
     */
    public function setRiveCoord($riveCoord)
    {
        $this->riveCoord = $riveCoord;

        return $this;
    }

    /**
     * Get riveCoord
     *
     * @return string
     */
    public function getRiveCoord()
    {
        return $this->riveCoord;
    }

    /**
     * Set longueur
     *
     * @param string $longueur
     *
     * @return PgCmdPrelevHbMphyt
     */
    public function setLongueur($longueur)
    {
        $this->longueur = $longueur;

        return $this;
    }

    /**
     * Get longueur
     *
     * @return string
     */
    public function getLongueur()
    {
        return $this->longueur;
    }

    /**
     * Set largeur
     *
     * @param string $largeur
     *
     * @return PgCmdPrelevHbMphyt
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
     * Set conditionsHydro
     *
     * @param string $conditionsHydro
     *
     * @return PgCmdPrelevHbMphyt
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
     * Set conditionsMeteo
     *
     * @param string $conditionsMeteo
     *
     * @return PgCmdPrelevHbMphyt
     */
    public function setConditionsMeteo($conditionsMeteo)
    {
        $this->conditionsMeteo = $conditionsMeteo;

        return $this;
    }

    /**
     * Get conditionsMeteo
     *
     * @return string
     */
    public function getConditionsMeteo()
    {
        return $this->conditionsMeteo;
    }

    /**
     * Set turbidite
     *
     * @param string $turbidite
     *
     * @return PgCmdPrelevHbMphyt
     */
    public function setTurbidite($turbidite)
    {
        $this->turbidite = $turbidite;

        return $this;
    }

    /**
     * Get turbidite
     *
     * @return string
     */
    public function getTurbidite()
    {
        return $this->turbidite;
    }

    /**
     * Set observations
     *
     * @param string $observations
     *
     * @return PgCmdPrelevHbMphyt
     */
    public function setObservations($observations)
    {
        $this->observations = $observations;

        return $this;
    }

    /**
     * Get observations
     *
     * @return string
     */
    public function getObservations()
    {
        return $this->observations;
    }

    /**
     * Set prelev
     *
     * @param \Aeag\SqeBundle\Entity\PgCmdPrelev $prelev
     *
     * @return PgCmdPrelevHbMphyt
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
