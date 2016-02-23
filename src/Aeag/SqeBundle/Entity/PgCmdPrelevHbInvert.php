<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdPrelevHbInvert
 *
 * @ORM\Table(name="pg_cmd_prelev_hb_invert")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdPrelevHbInvertRepository")
 */
class PgCmdPrelevHbInvert
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
     * @ORM\Column(name="x_aval", type="decimal", precision=12, scale=5, nullable=true)
     */
    private $xAval;

    /**
     * @var string
     *
     * @ORM\Column(name="y_aval", type="decimal", precision=12, scale=5, nullable=true)
     */
    private $yAval;

    /**
     * @var string
     *
     * @ORM\Column(name="longueur", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $longueur;

    /**
     * @var string
     *
     * @ORM\Column(name="largeur_moy", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $largeurMoy;

    /**
     * @var string
     *
     * @ORM\Column(name="largeur_pb", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $largeurPb;

    /**
     * @var string
     *
     * @ORM\Column(name="pr_zberge", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $prZberge;

    /**
     * @var string
     *
     * @ORM\Column(name="pr_zinter", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $prZinter;

    /**
     * @var string
     *
     * @ORM\Column(name="pr_zprof", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $prZprof;

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
     * @return PgCmdPrelevHbInvert
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
     * @return PgCmdPrelevHbInvert
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
     * Set xAval
     *
     * @param string $xAval
     *
     * @return PgCmdPrelevHbInvert
     */
    public function setXAval($xAval)
    {
        $this->xAval = $xAval;

        return $this;
    }

    /**
     * Get xAval
     *
     * @return string
     */
    public function getXAval()
    {
        return $this->xAval;
    }

    /**
     * Set yAval
     *
     * @param string $yAval
     *
     * @return PgCmdPrelevHbInvert
     */
    public function setYAval($yAval)
    {
        $this->yAval = $yAval;

        return $this;
    }

    /**
     * Get yAval
     *
     * @return string
     */
    public function getYAval()
    {
        return $this->yAval;
    }

    /**
     * Set longueur
     *
     * @param string $longueur
     *
     * @return PgCmdPrelevHbInvert
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
     * Set largeurMoy
     *
     * @param string $largeurMoy
     *
     * @return PgCmdPrelevHbInvert
     */
    public function setLargeurMoy($largeurMoy)
    {
        $this->largeurMoy = $largeurMoy;

        return $this;
    }

    /**
     * Get largeurMoy
     *
     * @return string
     */
    public function getLargeurMoy()
    {
        return $this->largeurMoy;
    }

    /**
     * Set largeurPb
     *
     * @param string $largeurPb
     *
     * @return PgCmdPrelevHbInvert
     */
    public function setLargeurPb($largeurPb)
    {
        $this->largeurPb = $largeurPb;

        return $this;
    }

    /**
     * Get largeurPb
     *
     * @return string
     */
    public function getLargeurPb()
    {
        return $this->largeurPb;
    }

    /**
     * Set prZberge
     *
     * @param string $prZberge
     *
     * @return PgCmdPrelevHbInvert
     */
    public function setPrZberge($prZberge)
    {
        $this->prZberge = $prZberge;

        return $this;
    }

    /**
     * Get prZberge
     *
     * @return string
     */
    public function getPrZberge()
    {
        return $this->prZberge;
    }

    /**
     * Set prZinter
     *
     * @param string $prZinter
     *
     * @return PgCmdPrelevHbInvert
     */
    public function setPrZinter($prZinter)
    {
        $this->prZinter = $prZinter;

        return $this;
    }

    /**
     * Get prZinter
     *
     * @return string
     */
    public function getPrZinter()
    {
        return $this->prZinter;
    }

    /**
     * Set prZprof
     *
     * @param string $prZprof
     *
     * @return PgCmdPrelevHbInvert
     */
    public function setPrZprof($prZprof)
    {
        $this->prZprof = $prZprof;

        return $this;
    }

    /**
     * Get prZprof
     *
     * @return string
     */
    public function getPrZprof()
    {
        return $this->prZprof;
    }

    /**
     * Set prelev
     *
     * @param \Aeag\SqeBundle\Entity\PgCmdPrelev $prelev
     *
     * @return PgCmdPrelevHbInvert
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
