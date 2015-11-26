<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgRefStationMesure
 *
 * @ORM\Table(name="pg_ref_station_mesure")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgRefStationMesureRepository")
 */
class PgRefStationMesure
{
    /**
     * @var string
     *
     * @ORM\Column(name="ouv_fonc_id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_ref_station_mesure_ouv_fonc_id_seq", allocationSize=1, initialValue=1)
     */
    private $ouvFoncId;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=4, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="numero", type="string", length=12, nullable=false)
     */
    private $numero;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=17, nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=100, nullable=false)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="etat", type="string", length=4, nullable=false)
     */
    private $etat;

    /**
     * @var string
     *
     * @ORM\Column(name="insee_commune", type="string", length=5, nullable=false)
     */
    private $inseeCommune;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_commune", type="string", length=60, nullable=true)
     */
    private $nomCommune;

    /**
     * @var string
     *
     * @ORM\Column(name="code_zone_hydro", type="string", length=4, nullable=false)
     */
    private $codeZoneHydro;

    /**
     * @var string
     *
     * @ORM\Column(name="code_cours_eau", type="string", length=8, nullable=true)
     */
    private $codeCoursEau;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_cours_eau", type="string", length=64, nullable=true)
     */
    private $nomCoursEau;

    /**
     * @var string
     *
     * @ORM\Column(name="code_masdo", type="string", length=24, nullable=true)
     */
    private $codeMasdo;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_masdo", type="string", length=100, nullable=true)
     */
    private $nomMasdo;

    /**
     * @var string
     *
     * @ORM\Column(name="classe_profondeur", type="string", length=1, nullable=true)
     */
    private $classeProfondeur;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgRefReseauMesure", inversedBy="ouvFonc")
     * @ORM\JoinTable(name="pg_ref_station_rsx",
     *   joinColumns={
     *     @ORM\JoinColumn(name="ouv_fonc_id", referencedColumnName="ouv_fonc_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="groupement_id", referencedColumnName="groupement_id")
     *   }
     * )
     */
    private $groupement;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgProgZoneGeoRef", mappedBy="station")
     */
    private $zgeoRef;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groupement = new \Doctrine\Common\Collections\ArrayCollection();
        $this->zgeoRef = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Get ouvFoncId
     *
     * @return string 
     */
    public function getOuvFoncId()
    {
        return $this->ouvFoncId;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return PgRefStationMesure
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set numero
     *
     * @param string $numero
     * @return PgRefStationMesure
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get numero
     *
     * @return string 
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return PgRefStationMesure
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return PgRefStationMesure
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set etat
     *
     * @param string $etat
     * @return PgRefStationMesure
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return string 
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Set inseeCommune
     *
     * @param string $inseeCommune
     * @return PgRefStationMesure
     */
    public function setInseeCommune($inseeCommune)
    {
        $this->inseeCommune = $inseeCommune;

        return $this;
    }

    /**
     * Get inseeCommune
     *
     * @return string 
     */
    public function getInseeCommune()
    {
        return $this->inseeCommune;
    }

    /**
     * Set nomCommune
     *
     * @param string $nomCommune
     * @return PgRefStationMesure
     */
    public function setNomCommune($nomCommune)
    {
        $this->nomCommune = $nomCommune;

        return $this;
    }

    /**
     * Get nomCommune
     *
     * @return string 
     */
    public function getNomCommune()
    {
        return $this->nomCommune;
    }

    /**
     * Set codeZoneHydro
     *
     * @param string $codeZoneHydro
     * @return PgRefStationMesure
     */
    public function setCodeZoneHydro($codeZoneHydro)
    {
        $this->codeZoneHydro = $codeZoneHydro;

        return $this;
    }

    /**
     * Get codeZoneHydro
     *
     * @return string 
     */
    public function getCodeZoneHydro()
    {
        return $this->codeZoneHydro;
    }

    /**
     * Set codeCoursEau
     *
     * @param string $codeCoursEau
     * @return PgRefStationMesure
     */
    public function setCodeCoursEau($codeCoursEau)
    {
        $this->codeCoursEau = $codeCoursEau;

        return $this;
    }

    /**
     * Get codeCoursEau
     *
     * @return string 
     */
    public function getCodeCoursEau()
    {
        return $this->codeCoursEau;
    }

    /**
     * Set nomCoursEau
     *
     * @param string $nomCoursEau
     * @return PgRefStationMesure
     */
    public function setNomCoursEau($nomCoursEau)
    {
        $this->nomCoursEau = $nomCoursEau;

        return $this;
    }

    /**
     * Get nomCoursEau
     *
     * @return string 
     */
    public function getNomCoursEau()
    {
        return $this->nomCoursEau;
    }

    /**
     * Set codeMasdo
     *
     * @param string $codeMasdo
     * @return PgRefStationMesure
     */
    public function setCodeMasdo($codeMasdo)
    {
        $this->codeMasdo = $codeMasdo;

        return $this;
    }

    /**
     * Get codeMasdo
     *
     * @return string 
     */
    public function getCodeMasdo()
    {
        return $this->codeMasdo;
    }

    /**
     * Set nomMasdo
     *
     * @param string $nomMasdo
     * @return PgRefStationMesure
     */
    public function setNomMasdo($nomMasdo)
    {
        $this->nomMasdo = $nomMasdo;

        return $this;
    }

    /**
     * Get nomMasdo
     *
     * @return string 
     */
    public function getNomMasdo()
    {
        return $this->nomMasdo;
    }

    /**
     * Set classeProfondeur
     *
     * @param string $classeProfondeur
     * @return PgRefStationMesure
     */
    public function setClasseProfondeur($classeProfondeur)
    {
        $this->classeProfondeur = $classeProfondeur;

        return $this;
    }

    /**
     * Get classeProfondeur
     *
     * @return string 
     */
    public function getClasseProfondeur()
    {
        return $this->classeProfondeur;
    }

    /**
     * Add groupement
     *
     * @param \Aeag\SqeBundle\Entity\PgRefReseauMesure $groupement
     * @return PgRefStationMesure
     */
    public function addGroupement(\Aeag\SqeBundle\Entity\PgRefReseauMesure $groupement)
    {
        $this->groupement[] = $groupement;

        return $this;
    }

    /**
     * Remove groupement
     *
     * @param \Aeag\SqeBundle\Entity\PgRefReseauMesure $groupement
     */
    public function removeGroupement(\Aeag\SqeBundle\Entity\PgRefReseauMesure $groupement)
    {
        $this->groupement->removeElement($groupement);
    }

    /**
     * Get groupement
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroupement()
    {
        return $this->groupement;
    }

    /**
     * Add zgeoRef
     *
     * @param \Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoRef
     * @return PgRefStationMesure
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
