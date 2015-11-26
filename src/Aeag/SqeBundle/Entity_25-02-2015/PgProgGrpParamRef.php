<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgGrpParamRef
 *
 * @ORM\Table(name="pg_prog_grp_param_ref", indexes={@ORM\Index(name="IDX_9C24C4108004EBA5", columns={"support"}), @ORM\Index(name="IDX_9C24C410E01F52FC", columns={"code_milieu"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgGrpParamRefRepository")
 */
class PgProgGrpParamRef
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_grp_param_ref_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code_grp", type="string", length=4, nullable=false)
     */
    private $codeGrp;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_grp", type="string", length=50, nullable=false)
     */
    private $libelleGrp;

    /**
     * @var string
     *
     * @ORM\Column(name="type_grp", type="string", length=3, nullable=false)
     */
    private $typeGrp;

    /**
     * @var \PgSandreSupports
     *
     * @ORM\ManyToOne(targetEntity="PgSandreSupports")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="support", referencedColumnName="code_support")
     * })
     */
    private $support;

    /**
     * @var \PgProgTypeMilieu
     *
     * @ORM\ManyToOne(targetEntity="PgProgTypeMilieu")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_milieu", referencedColumnName="code_milieu")
     * })
     */
    private $codeMilieu;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgSandreSupports", inversedBy="grparRef")
     * @ORM\JoinTable(name="pg_prog_grpar_oblig_support",
     *   joinColumns={
     *     @ORM\JoinColumn(name="grpar_ref_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="code_support", referencedColumnName="code_support")
     *   }
     * )
     */
    private $codeSupport;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgSandreZoneVerticaleProspectee", inversedBy="grparRef")
     * @ORM\JoinTable(name="pg_prog_grpar_ref_zone_vert",
     *   joinColumns={
     *     @ORM\JoinColumn(name="grpar_ref_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="code_zone_vert", referencedColumnName="code_zone")
     *   }
     * )
     */
    private $codeZoneVert;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgProgLot", inversedBy="grparRef")
     * @ORM\JoinTable(name="pg_prog_lot_grpar_ref",
     *   joinColumns={
     *     @ORM\JoinColumn(name="grpar_ref_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     *   }
     * )
     */
    private $lot;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->codeSupport = new \Doctrine\Common\Collections\ArrayCollection();
        $this->codeZoneVert = new \Doctrine\Common\Collections\ArrayCollection();
        $this->lot = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set codeGrp
     *
     * @param string $codeGrp
     * @return PgProgGrpParamRef
     */
    public function setCodeGrp($codeGrp)
    {
        $this->codeGrp = $codeGrp;

        return $this;
    }

    /**
     * Get codeGrp
     *
     * @return string 
     */
    public function getCodeGrp()
    {
        return $this->codeGrp;
    }

    /**
     * Set libelleGrp
     *
     * @param string $libelleGrp
     * @return PgProgGrpParamRef
     */
    public function setLibelleGrp($libelleGrp)
    {
        $this->libelleGrp = $libelleGrp;

        return $this;
    }

    /**
     * Get libelleGrp
     *
     * @return string 
     */
    public function getLibelleGrp()
    {
        return $this->libelleGrp;
    }

    /**
     * Set typeGrp
     *
     * @param string $typeGrp
     * @return PgProgGrpParamRef
     */
    public function setTypeGrp($typeGrp)
    {
        $this->typeGrp = $typeGrp;

        return $this;
    }

    /**
     * Get typeGrp
     *
     * @return string 
     */
    public function getTypeGrp()
    {
        return $this->typeGrp;
    }

    /**
     * Set support
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreSupports $support
     * @return PgProgGrpParamRef
     */
    public function setSupport(\Aeag\SqeBundle\Entity\PgSandreSupports $support = null)
    {
        $this->support = $support;

        return $this;
    }

    /**
     * Get support
     *
     * @return \Aeag\SqeBundle\Entity\PgSandreSupports 
     */
    public function getSupport()
    {
        return $this->support;
    }

    /**
     * Set codeMilieu
     *
     * @param \Aeag\SqeBundle\Entity\PgProgTypeMilieu $codeMilieu
     * @return PgProgGrpParamRef
     */
    public function setCodeMilieu(\Aeag\SqeBundle\Entity\PgProgTypeMilieu $codeMilieu = null)
    {
        $this->codeMilieu = $codeMilieu;

        return $this;
    }

    /**
     * Get codeMilieu
     *
     * @return \Aeag\SqeBundle\Entity\PgProgTypeMilieu 
     */
    public function getCodeMilieu()
    {
        return $this->codeMilieu;
    }

    /**
     * Add codeSupport
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreSupports $codeSupport
     * @return PgProgGrpParamRef
     */
    public function addCodeSupport(\Aeag\SqeBundle\Entity\PgSandreSupports $codeSupport)
    {
        $this->codeSupport[] = $codeSupport;

        return $this;
    }

    /**
     * Remove codeSupport
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreSupports $codeSupport
     */
    public function removeCodeSupport(\Aeag\SqeBundle\Entity\PgSandreSupports $codeSupport)
    {
        $this->codeSupport->removeElement($codeSupport);
    }

    /**
     * Get codeSupport
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCodeSupport()
    {
        return $this->codeSupport;
    }

    /**
     * Add codeZoneVert
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee $codeZoneVert
     * @return PgProgGrpParamRef
     */
    public function addCodeZoneVert(\Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee $codeZoneVert)
    {
        $this->codeZoneVert[] = $codeZoneVert;

        return $this;
    }

    /**
     * Remove codeZoneVert
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee $codeZoneVert
     */
    public function removeCodeZoneVert(\Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee $codeZoneVert)
    {
        $this->codeZoneVert->removeElement($codeZoneVert);
    }

    /**
     * Get codeZoneVert
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCodeZoneVert()
    {
        return $this->codeZoneVert;
    }

    /**
     * Add lot
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLot $lot
     * @return PgProgGrpParamRef
     */
    public function addLot(\Aeag\SqeBundle\Entity\PgProgLot $lot)
    {
        $this->lot[] = $lot;

        return $this;
    }

    /**
     * Remove lot
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLot $lot
     */
    public function removeLot(\Aeag\SqeBundle\Entity\PgProgLot $lot)
    {
        $this->lot->removeElement($lot);
    }

    /**
     * Get lot
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLot()
    {
        return $this->lot;
    }
}
