<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdInvertRecouv
 *
 * @ORM\Table(name="pg_cmd_invert_recouv", indexes={@ORM\Index(name="IDX_F6DDC993D8E1F6AA", columns={"prelev_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdInvertRecouvRepository")
 */
class PgCmdInvertRecouv
{
    /**
     * @var string
     *
     * @ORM\Column(name="substrat", type="string", length=3, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $substrat;

    /**
     * @var string
     *
     * @ORM\Column(name="recouvrement", type="string", length=50, nullable=true)
     */
    private $recouvrement;

    /**
     * @var string
     *
     * @ORM\Column(name="recouv_num", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $recouvNum;

    /**
     * @var string
     *
     * @ORM\Column(name="recouv_zberge", type="string", length=50, nullable=true)
     */
    private $recouvZberge;

    /**
     * @var string
     *
     * @ORM\Column(name="recouv_zinter", type="string", length=50, nullable=true)
     */
    private $recouvZinter;

    /**
     * @var string
     *
     * @ORM\Column(name="recouv_zprof", type="string", length=50, nullable=true)
     */
    private $recouvZprof;

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
     * Set substrat
     *
     * @param string $substrat
     *
     * @return PgCmdInvertRecouv
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
     * Set recouvrement
     *
     * @param string $recouvrement
     *
     * @return PgCmdInvertRecouv
     */
    public function setRecouvrement($recouvrement)
    {
        $this->recouvrement = $recouvrement;

        return $this;
    }

    /**
     * Get recouvrement
     *
     * @return string
     */
    public function getRecouvrement()
    {
        return $this->recouvrement;
    }

    /**
     * Set recouvNum
     *
     * @param string $recouvNum
     *
     * @return PgCmdInvertRecouv
     */
    public function setRecouvNum($recouvNum)
    {
        $this->recouvNum = $recouvNum;

        return $this;
    }

    /**
     * Get recouvNum
     *
     * @return string
     */
    public function getRecouvNum()
    {
        return $this->recouvNum;
    }

    /**
     * Set recouvZberge
     *
     * @param string $recouvZberge
     *
     * @return PgCmdInvertRecouv
     */
    public function setRecouvZberge($recouvZberge)
    {
        $this->recouvZberge = $recouvZberge;

        return $this;
    }

    /**
     * Get recouvZberge
     *
     * @return string
     */
    public function getRecouvZberge()
    {
        return $this->recouvZberge;
    }

    /**
     * Set recouvZinter
     *
     * @param string $recouvZinter
     *
     * @return PgCmdInvertRecouv
     */
    public function setRecouvZinter($recouvZinter)
    {
        $this->recouvZinter = $recouvZinter;

        return $this;
    }

    /**
     * Get recouvZinter
     *
     * @return string
     */
    public function getRecouvZinter()
    {
        return $this->recouvZinter;
    }

    /**
     * Set recouvZprof
     *
     * @param string $recouvZprof
     *
     * @return PgCmdInvertRecouv
     */
    public function setRecouvZprof($recouvZprof)
    {
        $this->recouvZprof = $recouvZprof;

        return $this;
    }

    /**
     * Get recouvZprof
     *
     * @return string
     */
    public function getRecouvZprof()
    {
        return $this->recouvZprof;
    }

    /**
     * Set prelev
     *
     * @param \Aeag\SqeBundle\Entity\PgCmdPrelevHbInvert $prelev
     *
     * @return PgCmdInvertRecouv
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
