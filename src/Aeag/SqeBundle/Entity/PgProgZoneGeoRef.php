<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgZoneGeoRef
 *
 * @ORM\Table(name="pg_prog_zone_geo_ref", uniqueConstraints={@ORM\UniqueConstraint(name="uk_pg_prog_zgeoref_nom", columns={"nom_zone_geo"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgZoneGeoRefRepository")
 */
class PgProgZoneGeoRef
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_zone_geo_ref_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_zone_geo", type="string", length=255, nullable=false)
     */
    private $nomZoneGeo;

    /**
     * @var string
     *
     * @ORM\Column(name="type_zone_geo", type="string", length=3, nullable=false)
     */
    private $typeZoneGeo;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgProgWebusers", mappedBy="zgeoRef")
     */
    private $webuser;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgProgTypeMilieu", inversedBy="zgeoRef")
     * @ORM\JoinTable(name="pg_prog_zgeoref_typmil",
     *   joinColumns={
     *     @ORM\JoinColumn(name="zgeo_ref_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="code_milieu", referencedColumnName="code_milieu")
     *   }
     * )
     */
    private $codeMilieu;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgRefStationMesure", inversedBy="zgeoRef")
     * @ORM\JoinTable(name="pg_prog_zgeoref_station",
     *   joinColumns={
     *     @ORM\JoinColumn(name="zgeo_ref_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="station_id", referencedColumnName="ouv_fonc_id")
     *   }
     * )
     */
    private $station;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->webuser = new \Doctrine\Common\Collections\ArrayCollection();
        $this->codeMilieu = new \Doctrine\Common\Collections\ArrayCollection();
        $this->station = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set nomZoneGeo
     *
     * @param string $nomZoneGeo
     * @return PgProgZoneGeoRef
     */
    public function setNomZoneGeo($nomZoneGeo)
    {
        $this->nomZoneGeo = $nomZoneGeo;

        return $this;
    }

    /**
     * Get nomZoneGeo
     *
     * @return string 
     */
    public function getNomZoneGeo()
    {
        return $this->nomZoneGeo;
    }

    /**
     * Set typeZoneGeo
     *
     * @param string $typeZoneGeo
     * @return PgProgZoneGeoRef
     */
    public function setTypeZoneGeo($typeZoneGeo)
    {
        $this->typeZoneGeo = $typeZoneGeo;

        return $this;
    }

    /**
     * Get typeZoneGeo
     *
     * @return string 
     */
    public function getTypeZoneGeo()
    {
        return $this->typeZoneGeo;
    }

    /**
     * Add webuser
     *
     * @param \Aeag\SqeBundle\Entity\PgProgWebusers $webuser
     * @return PgProgZoneGeoRef
     */
    public function addWebuser(\Aeag\SqeBundle\Entity\PgProgWebusers $webuser)
    {
        $this->webuser[] = $webuser;

        return $this;
    }

    /**
     * Remove webuser
     *
     * @param \Aeag\SqeBundle\Entity\PgProgWebusers $webuser
     */
    public function removeWebuser(\Aeag\SqeBundle\Entity\PgProgWebusers $webuser)
    {
        $this->webuser->removeElement($webuser);
    }

    /**
     * Get webuser
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWebuser()
    {
        return $this->webuser;
    }

    /**
     * Add codeMilieu
     *
     * @param \Aeag\SqeBundle\Entity\PgProgTypeMilieu $codeMilieu
     * @return PgProgZoneGeoRef
     */
    public function addCodeMilieu(\Aeag\SqeBundle\Entity\PgProgTypeMilieu $codeMilieu)
    {
        $this->codeMilieu[] = $codeMilieu;

        return $this;
    }

    /**
     * Remove codeMilieu
     *
     * @param \Aeag\SqeBundle\Entity\PgProgTypeMilieu $codeMilieu
     */
    public function removeCodeMilieu(\Aeag\SqeBundle\Entity\PgProgTypeMilieu $codeMilieu)
    {
        $this->codeMilieu->removeElement($codeMilieu);
    }

    /**
     * Get codeMilieu
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCodeMilieu()
    {
        return $this->codeMilieu;
    }

    /**
     * Add station
     *
     * @param \Aeag\SqeBundle\Entity\PgRefStationMesure $station
     * @return PgProgZoneGeoRef
     */
    public function addStation(\Aeag\SqeBundle\Entity\PgRefStationMesure $station)
    {
        $this->station[] = $station;

        return $this;
    }

    /**
     * Remove station
     *
     * @param \Aeag\SqeBundle\Entity\PgRefStationMesure $station
     */
    public function removeStation(\Aeag\SqeBundle\Entity\PgRefStationMesure $station)
    {
        $this->station->removeElement($station);
    }

    /**
     * Get station
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStation()
    {
        return $this->station;
    }
}
