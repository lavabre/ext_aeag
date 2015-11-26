<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgTypeMilieu
 *
 * @ORM\Table(name="pg_prog_type_milieu", indexes={@ORM\Index(name="IDX_PgProgTypeMilieu_typePeriode", columns={"type_periode"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgTypeMilieuRepository")
 */
class PgProgTypeMilieu
{
    /**
     * @var string
     *
     * @ORM\Column(name="code_milieu", type="string", length=3, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_type_milieu_code_milieu_seq", allocationSize=1, initialValue=1)
     */
    private $codeMilieu;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_milieu", type="string", length=255, nullable=false)
     */
    private $nomMilieu;

    /**
     * @var string
     *
     * @ORM\Column(name="type_ouvrage", type="string", length=4, nullable=false)
     */
    private $typeOuvrage;

    /**
     * @var string
     *
     * @ORM\Column(name="categorie_milieu", type="string", length=50, nullable=false)
     */
    private $categorieMilieu;

    /**
     * @var string
     *
     * @ORM\Column(name="thematique", type="string", length=20, nullable=false)
     */
    private $thematique;

    /**
     * @var \PgProgTypePeriode
     *
     * @ORM\ManyToOne(targetEntity="PgProgTypePeriode")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_periode", referencedColumnName="code_type_periode")
     * })
     */
    private $typePeriode;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgProgZoneGeoRef", mappedBy="codeMilieu")
     */
    private $zgeoRef;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->zgeoRef = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set nomMilieu
     *
     * @param string $nomMilieu
     * @return PgProgTypeMilieu
     */
    public function setNomMilieu($nomMilieu)
    {
        $this->nomMilieu = $nomMilieu;

        return $this;
    }

    /**
     * Get nomMilieu
     *
     * @return string 
     */
    public function getNomMilieu()
    {
        return $this->nomMilieu;
    }

    /**
     * Set typeOuvrage
     *
     * @param string $typeOuvrage
     * @return PgProgTypeMilieu
     */
    public function setTypeOuvrage($typeOuvrage)
    {
        $this->typeOuvrage = $typeOuvrage;

        return $this;
    }

    /**
     * Get typeOuvrage
     *
     * @return string 
     */
    public function getTypeOuvrage()
    {
        return $this->typeOuvrage;
    }

    /**
     * Set categorieMilieu
     *
     * @param string $categorieMilieu
     * @return PgProgTypeMilieu
     */
    public function setCategorieMilieu($categorieMilieu)
    {
        $this->categorieMilieu = $categorieMilieu;

        return $this;
    }

    /**
     * Get categorieMilieu
     *
     * @return string 
     */
    public function getCategorieMilieu()
    {
        return $this->categorieMilieu;
    }

    /**
     * Set thematique
     *
     * @param string $thematique
     * @return PgProgTypeMilieu
     */
    public function setThematique($thematique)
    {
        $this->thematique = $thematique;

        return $this;
    }

    /**
     * Get thematique
     *
     * @return string 
     */
    public function getThematique()
    {
        return $this->thematique;
    }

    /**
     * Set typePeriode
     *
     * @param \Aeag\SqeBundle\Entity\PgProgTypePeriode $typePeriode
     * @return PgProgTypeMilieu
     */
    public function setTypePeriode(\Aeag\SqeBundle\Entity\PgProgTypePeriode $typePeriode = null)
    {
        $this->typePeriode = $typePeriode;

        return $this;
    }

    /**
     * Get typePeriode
     *
     * @return \Aeag\SqeBundle\Entity\PgProgTypePeriode 
     */
    public function getTypePeriode()
    {
        return $this->typePeriode;
    }

    /**
     * Add zgeoRef
     *
     * @param \Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoRef
     * @return PgProgTypeMilieu
     */
    public function addZgeoRef(\Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoRef)
    {
        $this->zgeoRef[] = $zgeoRef;

        return $this;
    }

    /**
     * Remove zgeoRef
     *
     * @param \Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoRef
     */
    public function removeZgeoRef(\Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoRef)
    {
        $this->zgeoRef->removeElement($zgeoRef);
    }

    /**
     * Get zgeoRef
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getZgeoRef()
    {
        return $this->zgeoRef;
    }
}
