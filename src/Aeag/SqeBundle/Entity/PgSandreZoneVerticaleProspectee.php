<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgSandreZoneVerticaleProspectee
 *
 * @ORM\Table(name="pg_sandre_zone_verticale_prospectee")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgSandreZoneVerticaleProspecteeRepository")
 */
class PgSandreZoneVerticaleProspectee
{
    /**
     * @var string
     *
     * @ORM\Column(name="code_zone", type="string", length=2, nullable=false)
     * @ORM\Id
      */
    private $codeZone;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_zone", type="string", length=50, nullable=false)
     */
    private $nomZone;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgProgGrpParamRef", mappedBy="codeZoneVert")
     */
    private $grparRef;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->grparRef = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Get codeZone
     *
     * @return string 
     */
    public function getCodeZone()
    {
        return $this->codeZone;
    }

    /**
     * Set nomZone
     *
     * @param string $nomZone
     * @return PgSandreZoneVerticaleProspectee
     */
    public function setNomZone($nomZone)
    {
        $this->nomZone = $nomZone;

        return $this;
    }

    /**
     * Get nomZone
     *
     * @return string 
     */
    public function getNomZone()
    {
        return $this->nomZone;
    }

    /**
     * Add grparRef
     *
     * @param \Aeag\SqeBundle\Entity\PgProgGrpParamRef $grparRef
     * @return PgSandreZoneVerticaleProspectee
     */
    public function addGrparRef(\Aeag\SqeBundle\Entity\PgProgGrpParamRef $grparRef)
    {
        $this->grparRef[] = $grparRef;

        return $this;
    }

    /**
     * Remove grparRef
     *
     * @param \Aeag\SqeBundle\Entity\PgProgGrpParamRef $grparRef
     */
    public function removeGrparRef(\Aeag\SqeBundle\Entity\PgProgGrpParamRef $grparRef)
    {
        $this->grparRef->removeElement($grparRef);
    }

    /**
     * Get grparRef
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGrparRef()
    {
        return $this->grparRef;
    }
}
