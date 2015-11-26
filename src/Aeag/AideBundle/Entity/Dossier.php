<?php

namespace Aeag\AideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="dossiers",indexes={@ORM\Index(name="dossier_idx", columns={"annee","ligne","dept","no_ordre"})})
 * @ORM\Entity(repositoryClass="Aeag\AideBundle\Repository\DossierRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Dossier {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="dossier_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $id;

    /**
     * Bidirectional 
     *
     * @ORM\ManyToOne(targetEntity="Ligne")
     * @ORM\JoinColumn(name="ligne", referencedColumnName="ligne")
     */
    private $ligne;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Departement")
     * @ORM\JoinColumn(name="dept", referencedColumnName="dept")
     */
    private $dept;

    /**
     * @ORM\Column(type="integer", length=4)
     * 
     * @Assert\NotBlank()
     */
    private $no_ordre;

    /**
     * @ORM\Column(type="string", length=4)
     * 
     * @Assert\NotBlank()
     */
    private $phase;

    /**
     * @ORM\ManyToOne(targetEntity="Annee")
     * @ORM\JoinColumn(name="annee", referencedColumnName="annee")
     * 
     */
    private $annee;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date_decision;

    /**
     * @ORM\Column(type="string", length=5)
     * 
     * @Assert\NotBlank()
     */
    private $typeci;

    /**
     * @ORM\Column(type="integer", length=5)
     * 
     * @Assert\NotBlank()
     */
    private $numero;

    /**
     * @ORM\Column(type="integer", length=4)
     * 
     * @Assert\NotBlank()
     */
    private $version;

    /**
     * @ORM\Column(type="decimal", length=12)
     * 
     */
    private $montant_retenu;

    /**
     * @ORM\Column(type="decimal", length=12)
     * 
     */
    private $montant_aide_interne;

    /**
     * @ORM\Column(type="string", length=100)
     * 
     */
    private $raison_sociale;

    /**
     * @ORM\Column(type="string", length=25)
     * 
     */
    private $cate;

    /**
     * @ORM\Column(type="string", length=100)
     * 
     */
    private $intitule;

    /**
     * @ORM\Column(type="integer", length=10)
     * 
     */
    private $cor_id_benef;

    /**
     * @ORM\Column(type="string", length=30)
     * 
     * @Assert\NotBlank()
     */
    private $forme_aide;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     * 
     */
    private $mod_annee;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $mod_typeci;

    /**
     * @ORM\Column(type="integer", length=4, nullable=true)
     * 
     */
    private $mod_numero;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     * 
     */
    private $mod_phase;

    /**
     * @ORM\Column(type="decimal", length=12, nullable=true)
     * 
     */
    private $mod_montant_retenu;

    /**
     * @ORM\Column(type="decimal", length=12, nullable=true)
     * 
     */
    private $mod_montant_aide_interne;

    /**
     * @ORM\Column(type="integer", length=4, nullable=true)
     * 
     */
    private $init_annee;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * 
     */
    private $init_typeci;

    /**
     * @ORM\Column(type="integer", length=4, nullable=true)
     * 
     */
    private $init_numero;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     * 
     */
    private $init_phase;

    /**
     * @ORM\Column(type="decimal", length=12, nullable=true)
     * 
     */
    private $init_montant_retenu;

    /**
     * @ORM\Column(type="decimal", length=12, nullable=true)
     * 
     */
    private $init_montant_aide_interne;

    /**
     *
     * @ORM\ManyToOne(targetEntity="RegionHydro")
     * @ORM\JoinColumn(name="reghydro", referencedColumnName="reg")
     * 
     */
    private $reghydro;

    /**
     *
     * @ORM\ManyToOne(targetEntity="RegionAdmin")
     * @ORM\JoinColumn(name="regadmin", referencedColumnName="reg")
     */
    private $regadmin;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getLigne() {
        return $this->ligne;
    }

    public function setLigne(array $ligne) {
        $this->ligne = $ligne;
    }

    public function getDept() {
        return $this->dept;
    }

    public function setDept(array $dept) {
        $this->dept = $dept;
    }

    public function getNo_ordre() {
        return $this->no_ordre;
    }

    public function setNo_ordre($no_ordre) {
        $this->no_ordre = $no_ordre;
    }

    public function getPhase() {
        return $this->phase;
    }

    public function setPhase($phase) {
        $this->phase = $phase;
    }

    public function getAnnee() {
        return $this->annee;
    }

    public function setAnnee($annee) {
        $this->annee = $annee;
    }

    public function getTypeci() {
        return $this->typeci;
    }

    public function setTypeci($typeci) {
        $this->typeci = $typeci;
    }

    public function getNumero() {
        return $this->numero;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
    }

    public function getVersion() {
        return $this->version;
    }

    public function setVersion($version) {
        $this->version = $version;
    }

    function getMontant_retenu() {
        return $this->montant_retenu;
    }

    function setMontant_retenu($montant_retenu) {
        $this->montant_retenu = $montant_retenu;
    }

    public function getMontant_aide_interne() {
        return $this->montant_aide_interne;
    }

    public function setMontant_aide_interne($montant_aide_interne) {
        $this->montant_aide_interne = $montant_aide_interne;
    }

    public function getRaison_sociale() {
        return $this->raison_sociale;
    }

    public function setRaison_sociale($raison_sociale) {
        $this->raison_sociale = $raison_sociale;
    }

    public function getCate() {
        return $this->cate;
    }

    public function setCate(array $cate) {
        $this->cate = $cate;
    }

    public function getIntitule() {
        return $this->intitule;
    }

    public function setIntitule($intitule) {
        $this->intitule = $intitule;
    }

    public function getCor_id_benef() {
        return $this->cor_id_benef;
    }

    public function setCor_id_benef($cor_id_benef) {
        $this->cor_id_benef = $cor_id_benef;
    }

    public function getForme_aide() {
        return $this->forme_aide;
    }

    public function setForme_aide($forme_aide) {
        $this->forme_aide = $forme_aide;
    }

    public function getMod_annee() {
        return $this->mod_annee;
    }

    public function setMod_annee($mod_annee) {
        $this->mod_annee = $mod_annee;
    }

    public function getMod_typeci() {
        return $this->mod_typeci;
    }

    public function setMod_typeci($mod_typeci) {
        $this->mod_typeci = $mod_typeci;
    }

    public function getMod_numero() {
        return $this->mod_numero;
    }

    public function setMod_numero($mod_numero) {
        $this->mod_numero = $mod_numero;
    }

    public function getMod_phase() {
        return $this->mod_phase;
    }

    public function setMod_phase($mod_phase) {
        $this->mod_phase = $mod_phase;
    }

    function getMod_montant_retenu() {
        return $this->mod_montant_retenu;
    }

    function setMod_montant_retenu($mod_montant_retenu) {
        $this->mod_montant_retenu = $mod_montant_retenu;
    }

    public function getMod_montant_aide_interne() {
        return $this->mod_montant_aide_interne;
    }

    public function setMod_montant_aide_interne($mod_montant_aide_interne) {
        $this->mod_montant_aide_interne = $mod_montant_aide_interne;
    }

    public function getInit_annee() {
        return $this->init_annee;
    }

    public function setInit_annee($init_annee) {
        $this->init_annee = $init_annee;
    }

    public function getInit_typeci() {
        return $this->init_typeci;
    }

    public function setInit_typeci($init_typeci) {
        $this->init_typeci = $init_typeci;
    }

    public function getInit_numero() {
        return $this->init_numero;
    }

    public function setInit_numero($init_numero) {
        $this->init_numero = $init_numero;
    }

    public function getInit_phase() {
        return $this->init_phase;
    }

    public function setInit_phase($init_phase) {
        $this->init_phase = $init_phase;
    }

    function getInit_montant_retenu() {
        return $this->init_montant_retenu;
    }

    function setInit_montant_retenu($init_montant_retenu) {
        $this->init_montant_retenu = $init_montant_retenu;
    }

    public function getInit_montant_aide_interne() {
        return $this->init_montant_aide_interne;
    }

    public function setInit_montant_aide_interne($init_montant_aide_interne) {
        $this->init_montant_aide_interne = $init_montant_aide_interne;
    }

    public function getReghydro() {
        return $this->reghydro;
    }

    public function setReghydro(array $reghydro) {
        $this->reghydro = $reghydro;
    }

    public function getRegadmin() {
        return $this->regadmin;
    }

    public function setRegadmin(array $regadmin) {
        $this->regadmin = $regadmin;
    }

    function getDate_decision() {
        return $this->date_decision;
    }

    function setDate_decision($date_decision) {
        $this->date_decision = $date_decision;
    }

}
