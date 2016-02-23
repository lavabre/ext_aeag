<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgZgeorefTypmil
 *
 * @ORM\Table(name="pg_prog_zgeoref_typmil", indexes={@ORM\Index(name="IDX_ PgProgZgeorefTypmil_zgeoRef", columns={"zgeo_ref_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgZgeorefTypmilRepository")
 */
class PgProgZgeorefTypmil
{
    /**
     * @var string
     *
     * @ORM\Column(name="code_milieu", type="string", length=3, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $codeMilieu;

    /**
     * @var \PgProgZoneGeoRef
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="PgProgZoneGeoRef")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zgeo_ref_id", referencedColumnName="id")
     * })
     */
    private $zgeoRef;



    /**
     * Set codeMilieu
     *
     * @param string $codeMilieu
     * @return PgProgZgeorefTypmil
     */
    public function setCodeMilieu($codeMilieu)
    {
        $this->codeMilieu = $codeMilieu;

        return $this;
    }

    /**
     * Get codeMilieu
     *
     * @return string 
     */
    public function getCodeMilieu()
    {
        return $this->codeMilieu;
    }

    /**
     * Set zgeoRef
     *
     * @param \Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoRef
     * @return PgProgZgeorefTypmil
     */
    public function setZgeoRef(\Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoRef)
    {
        $this->zgeoRef = $zgeoRef;

        return $this;
    }

    /**
     * Get zgeoRef
     *
     * @return \Aeag\SqeBundle\Entity\PgProgZoneGeoRef 
     */
    public function getZgeoRef()
    {
        return $this->zgeoRef;
    }
}
