<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AvisHistorique
 *
 * @ORM\Table(name="avis_historique")
 * @ORM\Entity
 */
class AvisHistorique
{
    /**
     * @var string
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $euCd;

    /**
     * @var string
     *
     * @ORM\Column(name="epr", type="string", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $epr;

    /**
     * @var string
     *
     * @ORM\Column(name="avis", type="string", nullable=true)
     */
    private $avis;



    /**
     * Set euCd
     *
     * @param string $euCd
     *
     * @return avisHistorique
     */
    public function setEuCd($euCd)
    {
        $this->euCd = $euCd;

        return $this;
    }

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
     * Set epr
     *
     * @param string $epr
     *
     * @return avisHistorique
     */
    public function setEpr($epr)
    {
        $this->epr = $epr;

        return $this;
    }

    /**
     * Get epr
     *
     * @return string
     */
    public function getEpr()
    {
        return $this->epr;
    }

    /**
     * Set avis
     *
     * @param string $avis
     *
     * @return avisHistorique
     */
    public function setAvis($avis)
    {
        $this->avis = $avis;

        return $this;
    }

    /**
     * Get avis
     *
     * @return string
     */
    public function getAvis()
    {
        return $this->avis;
    }
}
