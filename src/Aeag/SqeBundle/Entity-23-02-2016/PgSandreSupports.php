<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgSandreSupports
 *
 * @ORM\Table(name="pg_sandre_supports")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgSandreSupportsRepository")
 */
class PgSandreSupports
{
    /**
     * @var string
     *
     * @ORM\Column(name="code_support", type="string", length=3, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_sandre_supports_code_support_seq", allocationSize=1, initialValue=1)
     */
    private $codeSupport;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_support", type="string", length=255, nullable=false)
     */
    private $nomSupport;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgProgGrpParamRef", mappedBy="codeSupport")
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
     * Get codeSupport
     *
     * @return string 
     */
    public function getCodeSupport()
    {
        return $this->codeSupport;
    }

    /**
     * Set nomSupport
     *
     * @param string $nomSupport
     * @return PgSandreSupports
     */
    public function setNomSupport($nomSupport)
    {
        $this->nomSupport = $nomSupport;

        return $this;
    }

    /**
     * Get nomSupport
     *
     * @return string 
     */
    public function getNomSupport()
    {
        return $this->nomSupport;
    }

    /**
     * Add grparRef
     *
     * @param \Aeag\SqeBundle\Entity\PgProgGrpParamRef $grparRef
     * @return PgSandreSupports
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
