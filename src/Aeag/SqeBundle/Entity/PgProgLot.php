<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLot
 *
 * @ORM\Table(name="pg_prog_lot",
 *                        indexes={@ORM\Index(name="IDX_PgProgLot_zgeoRef", columns={"zgeo_ref_id"}), 
 *                                      @ORM\Index(name="IDX_PgProgLot_marche", columns={"marche_id"}), 
 *                                      @ORM\Index(name="IDX_PgProgLot_codeMilieu", columns={"code_milieu"}), 
 *                                      @ORM\Index(name="IDX_PgProgLot_titulaire", columns={"titulaire_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgLotRepository")
 */
class PgProgLot {

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_lot_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_lot", type="string", length=50, nullable=false)
     */
    private $nomLot;

    /**
     * @var string
     *
     * @ORM\Column(name="delai_lot", type="decimal", precision=3, scale=0, nullable=true)
     */
    private $delaiLot;

    /**
     * @var string
     *
     * @ORM\Column(name="delai_prel", type="decimal", precision=3, scale=0, nullable=true)
     */
    private $delaiPrel;

    /**
     * @var \PgProgZoneGeoRef
     *
     * @ORM\ManyToOne(targetEntity="PgProgZoneGeoRef")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zgeo_ref_id", referencedColumnName="id")
     * })
     */
    private $zgeoRef;

    /**
     * @var \PgProgMarche
     *
     * @ORM\ManyToOne(targetEntity="PgProgMarche")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="marche_id", referencedColumnName="id")
     * })
     */
    private $marche;

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
     * @var \PgRefCorresPresta
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="titulaire_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $titulaire;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgProgGrpParamRef", mappedBy="lot")
     */
    private $grparRef;

    /**
     * @var string
     *
     * @ORM\Column(name="type_echange", type="string", length=20, nullable=false)
     */
    private $typeEchange;

    /**
     * Constructor
     */
    public function __construct() {
        $this->grparRef = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return string 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nomLot
     *
     * @param string $nomLot
     * @return PgProgLot
     */
    public function setNomLot($nomLot) {
        $this->nomLot = $nomLot;

        return $this;
    }

    /**
     * Get nomLot
     *
     * @return string 
     */
    public function getNomLot() {
        return $this->nomLot;
    }

    /**
     * Set delaiLot
     *
     * @param string $delaiLot
     * @return PgProgLot
     */
    public function setDelaiLot($delaiLot) {
        $this->delaiLot = $delaiLot;

        return $this;
    }

    /**
     * Get delaiLot
     *
     * @return string 
     */
    public function getDelaiLot() {
        return $this->delaiLot;
    }

    function getDelaiPrel() {
        return $this->delaiPrel;
    }

    function setDelaiPrel($delaiPrel) {
        $this->delaiPrel = $delaiPrel;
    }

    /**
     * Set zgeoRef
     *
     * @param \Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoRef
     * @return PgProgLot
     */
    public function setZgeoRef(\Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoRef = null) {
        $this->zgeoRef = $zgeoRef;

        return $this;
    }

    /**
     * Get zgeoRef
     *
     * @return \Aeag\SqeBundle\Entity\PgProgZoneGeoRef 
     */
    public function getZgeoRef() {
        return $this->zgeoRef;
    }

    /**
     * Set marche
     *
     * @param \Aeag\SqeBundle\Entity\PgProgMarche $marche
     * @return PgProgLot
     */
    public function setMarche(\Aeag\SqeBundle\Entity\PgProgMarche $marche = null) {
        $this->marche = $marche;

        return $this;
    }

    /**
     * Get marche
     *
     * @return \Aeag\SqeBundle\Entity\PgProgMarche 
     */
    public function getMarche() {
        return $this->marche;
    }

    /**
     * Set codeMilieu
     *
     * @param \Aeag\SqeBundle\Entity\PgProgTypeMilieu $codeMilieu
     * @return PgProgLot
     */
    public function setCodeMilieu(\Aeag\SqeBundle\Entity\PgProgTypeMilieu $codeMilieu = null) {
        $this->codeMilieu = $codeMilieu;

        return $this;
    }

    /**
     * Get codeMilieu
     *
     * @return \Aeag\SqeBundle\Entity\PgProgTypeMilieu 
     */
    public function getCodeMilieu() {
        return $this->codeMilieu;
    }

    /**
     * Set titulaire
     *
     * @param \Aeag\SqeBundle\Entity\PgRefCorresPresta $titulaire
     * @return PgProgLot
     */
    public function setTitulaire(\Aeag\SqeBundle\Entity\PgRefCorresPresta $titulaire = null) {
        $this->titulaire = $titulaire;

        return $this;
    }

    /**
     * Get titulaire
     *
     * @return \Aeag\SqeBundle\Entity\PgRefCorresPresta 
     */
    public function getTitulaire() {
        return $this->titulaire;
    }

    /**
     * Add grparRef
     *
     * @param \Aeag\SqeBundle\Entity\PgProgGrpParamRef $grparRef
     * @return PgProgLot
     */
    public function addGrparRef(\Aeag\SqeBundle\Entity\PgProgGrpParamRef $grparRef) {
        $this->grparRef[] = $grparRef;

        return $this;
    }

    /**
     * Remove grparRef
     *
     * @param \Aeag\SqeBundle\Entity\PgProgGrpParamRef $grparRef
     */
    public function removeGrparRef(\Aeag\SqeBundle\Entity\PgProgGrpParamRef $grparRef) {
        $this->grparRef->removeElement($grparRef);
    }

    /**
     * Get grparRef
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGrparRef() {
        return $this->grparRef;
    }

    function getTypeEchange() {
        return $this->typeEchange;
    }

    function setTypeEchange($typeEchange) {
        $this->typeEchange = $typeEchange;
    }

}
