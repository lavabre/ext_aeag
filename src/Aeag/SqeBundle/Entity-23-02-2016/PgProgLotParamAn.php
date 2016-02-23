<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotParamAn
 *
 * @ORM\Table(name="pg_prog_lot_param_an", 
 *                       indexes={@ORM\Index(name="IDX_PgProgLotParamAn_grparan", columns={"grparan_id"}), 
 *                                     @ORM\Index(name="IDX_PgProgLotParamAn_codeParametre", columns={"code_parametre"}), 
 *                                     @ORM\Index(name="IDX_PgProgLotParamAn_prestataire", columns={"prestataire_id"}), 
 *                                     @ORM\Index(name="IDX_PgProgLotParamAn_rsx", columns={"rsx_id"}), 
 *                                     @ORM\Index(name="IDX_PgProgLotParamAn_codeMethode", columns={"code_methode"}), 
 *                                     @ORM\Index(name="IDX_PgProgLotParamAn_codeStatut", columns={"code_statut"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgLotParamAnRepository")
 */
class PgProgLotParamAn {

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_lot_param_an_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code_fraction", type="string", length=3, nullable=true)
     */
    private $codeFraction;

    /**
     * @var string
     *
     * @ORM\Column(name="code_unite", type="string", length=5, nullable=true)
     */
    private $codeUnite;

    /**
     * @var \PgProgLotGrparAn
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotGrparAn")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grparan_id", referencedColumnName="id")
     * })
     */
    private $grparan;

    /**
     * @var \PgSandreParametres
     *
     * @ORM\ManyToOne(targetEntity="PgSandreParametres")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_parametre", referencedColumnName="code_parametre")
     * })
     */
    private $codeParametre;

    /**
     * @var \PgRefCorresPresta
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prestataire_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $prestataire;

    /**
     * @var \PgRefReseauMesure
     *
     * @ORM\ManyToOne(targetEntity="PgRefReseauMesure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rsx_id", referencedColumnName="groupement_id")
     * })
     */
    private $rsx;

    /**
     * @var \PgSandreMethodes
     *
     * @ORM\ManyToOne(targetEntity="PgSandreMethodes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_methode", referencedColumnName="code_methode")
     * })
     */
    private $codeMethode;

    /**
     * @var \PgProgStatut
     *
     * @ORM\ManyToOne(targetEntity="PgProgStatut")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_statut", referencedColumnName="code_statut")
     * })
     */
    private $codeStatut;

    /**
     * Get id
     *
     * @return string 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set codeFraction
     *
     * @param string $codeFraction
     * @return PgProgLotParamAn
     */
    public function setCodeFraction($codeFraction) {
        $this->codeFraction = $codeFraction;

        return $this;
    }

    /**
     * Get codeFraction
     *
     * @return string 
     */
    public function getCodeFraction() {
        return $this->codeFraction;
    }

    /**
     * Set codeUnite
     *
     * @param string $codeUnite
     * @return PgProgLotParamAn
     */
    public function setCodeUnite($codeUnite) {
        $this->codeUnite = $codeUnite;

        return $this;
    }

    /**
     * Get codeUnite
     *
     * @return string 
     */
    public function getCodeUnite() {
        return $this->codeUnite;
    }

    /**
     * Set grparan
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotGrparAn $grparan
     * @return PgProgLotParamAn
     */
    public function setGrparan(\Aeag\SqeBundle\Entity\PgProgLotGrparAn $grparan = null) {
        $this->grparan = $grparan;

        return $this;
    }

    /**
     * Get grparan
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotGrparAn 
     */
    public function getGrparan() {
        return $this->grparan;
    }

    /**
     * Set codeParametre
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre
     * @return PgProgLotParamAn
     */
    public function setCodeParametre(\Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre = null) {
        $this->codeParametre = $codeParametre;

        return $this;
    }

    /**
     * Get codeParametre
     *
     * @return \Aeag\SqeBundle\Entity\PgSandreParametres 
     */
    public function getCodeParametre() {
        return $this->codeParametre;
    }

    /**
     * Set prestataire
     *
     * @param \Aeag\SqeBundle\Entity\PgRefCorresPresta $prestataire
     * @return PgProgLotParamAn
     */
    public function setPrestataire(\Aeag\SqeBundle\Entity\PgRefCorresPresta $prestataire = null) {
        $this->prestataire = $prestataire;

        return $this;
    }

    /**
     * Get prestataire
     *
     * @return \Aeag\SqeBundle\Entity\PgRefCorresPresta 
     */
    public function getPrestataire() {
        return $this->prestataire;
    }

    /**
     * Set rsx
     *
     * @param \Aeag\SqeBundle\Entity\PgRefReseauMesure $rsx
     * @return PgProgLotParamAn
     */
    public function setRsx(\Aeag\SqeBundle\Entity\PgRefReseauMesure $rsx = null) {
        $this->rsx = $rsx;

        return $this;
    }

    /**
     * Get rsx
     *
     * @return \Aeag\SqeBundle\Entity\PgRefReseauMesure 
     */
    public function getRsx() {
        return $this->rsx;
    }

    /**
     * Set codeMethode
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreMethodes $codeMethode
     * @return PgProgLotParamAn
     */
    public function setCodeMethode(\Aeag\SqeBundle\Entity\PgSandreMethodes $codeMethode = null) {
        $this->codeMethode = $codeMethode;

        return $this;
    }

    /**
     * Get codeMethode
     *
     * @return \Aeag\SqeBundle\Entity\PgSandreMethodes 
     */
    public function getCodeMethode() {
        return $this->codeMethode;
    }

    /**
     * Set codeStatut
     *
     * @param \Aeag\SqeBundle\Entity\PgProgStatut $codeStatut
     * @return PgProgLotParamAn
     */
    public function setCodeStatut(\Aeag\SqeBundle\Entity\PgProgStatut $codeStatut = null) {
        $this->codeStatut = $codeStatut;

        return $this;
    }

    /**
     * Get codeStatut
     *
     * @return \Aeag\SqeBundle\Entity\PgProgStatut 
     */
    public function getCodeStatut() {
        return $this->codeStatut;
    }

}
