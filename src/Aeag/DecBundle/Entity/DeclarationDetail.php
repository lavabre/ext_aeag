<?php

namespace Aeag\DecBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="declarationDetail",indexes={@ORM\Index(name="sousDeclarationDetail_idx", columns={"sousDeclarationCollecteur_id"},name="declarationProducteur_idx", columns={"declarationProducteur_id"})})
 * @ORM\Entity(repositoryClass="Aeag\DecBundle\Repository\DeclarationDetailRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class DeclarationDetail {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="declarationDetail_seq", initialValue=1, allocationSize=1)
     */
    private $id;

    /**
     * @var SousDeclarationCollecteur
     * @ORM\ManyToOne(targetEntity="Aeag\DecBundle\Entity\SousDeclarationCollecteur" )
     * @ORM\JoinColumn(name="sousDeclarationCollecteur_id", referencedColumnName="id")
     */
    private $SousDeclarationCollecteur;

    /**
     * @var DeclarationProducteur
     * @ORM\ManyToOne(targetEntity="Aeag\DecBundle\Entity\DeclarationProducteur" , cascade={"all"})
     * @ORM\JoinColumn(name="declarationProducteur_id", referencedColumnName="id")
     */
    private $DeclarationProducteur;

    /**
     * @ORM\ManyToOne(targetEntity="Aeag\DecBundle\Entity\Statut" )
     * @ORM\JoinColumn(name="statut", referencedColumnName="code", nullable=true)
     */
    private $statut;

    /**
     * @ORM\Column(name="centretraitement_id",type="integer", nullable=true)
     * */
    protected $CentreTraitement;

    /**
     * @ORM\Column(name="centretransit_id",type="integer", nullable=true)
     * */
    protected $CentreTransit;

    /**
     * @ORM\Column(name="centredepot_id",type="integer", nullable=true)
     * */
    protected $CentreDepot;

    /**
     * @var Dechet
     * @ORM\ManyToOne(targetEntity="Aeag\DecBundle\Entity\Dechet" )
     * @ORM\JoinColumn(name="dechet_code", referencedColumnName="code")
     */
    private $Dechet;

    /**
     * @var Filiere
     * @ORM\ManyToOne(targetEntity="Aeag\DecBundle\Entity\Filiere")
     * @ORM\JoinColumn(name="filiere_code", referencedColumnName="code")
     * */
    protected $Filiere;

    /**
     * @var Filiere
     * @ORM\ManyToOne(targetEntity="Aeag\DecBundle\Entity\Filiere")
     * @ORM\JoinColumn(name="traitfiliere_code", referencedColumnName="code")
     * */
    protected $traitFiliere;

    /**
     * @var Naf
     * @ORM\ManyToOne(targetEntity="Aeag\DecBundle\Entity\Naf")
     * @ORM\JoinColumn(name="naf_code", referencedColumnName="code")
     * */
    protected $Naf;

    /**
     *
     * @ORM\Column(name="nature", type="string", length=100)
     */
    private $nature;

    /**
     * @ORM\Column(name="dateFacture",type="datetime")
     */
    protected $dateFacture;

    /**
     *
     * @ORM\Column(name="numFacture", type="string", length=20)
     */
    private $numFacture;

    /**
     *
     * @ORM\Column(name="coutfacture", type="float", length=15)
     * @Assert\NotBlank(message=" Veuillez renseigner le coût facturé")
     */
    private $coutFacture;

    /**
     *
     * @ORM\Column(name="quantiteReel", type="float", length=15, nullable=true)
     */
    private $quantiteReel;

    /**
     *
     * @ORM\Column(name="montReel", type="float", length=15, nullable=true)
     */
    private $montReel;

    /**
     *
     * @ORM\Column(name="quantiteRet", type="float", length=15, nullable=true)
     */
    private $quantiteRet;

    /**
     *
     * @ORM\Column(name="montRet", type="float", length=15, nullable=true)
     */
    private $montRet;

    /**
     *
     * @ORM\Column(name="quantiteAide", type="float", length=15, nullable=true)
     */
    private $quantiteAide;

    /**
     *
     * @ORM\Column(name="montAide", type="float", length=15, nullable=true)
     */
    private $montAide;
    
    /**
     *
     * @ORM\Column(name="tauxAide", type="float", length=15, nullable=true)
     */
    private $tauxAide;
    
     /**
     *
     * @ORM\Column(name="bonnifie",  type="boolean")
     */
    private $bonnifie;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     *
     */
    private $dossierAide;

    /**
     *
     * @ORM\Column(name="montantAp", type="float", length=15, nullable=true)
     */
    private $montantAp;

    /**
     *
     * @ORM\Column(name="montantApDispo", type="float", length=15, nullable=true)
     */
    private $montantApDispo;

    /**
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    public function __construct() {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getSousDeclarationCollecteur() {
        return $this->SousDeclarationCollecteur;
    }

    public function setSousDeclarationCollecteur($SousDeclarationCollecteur) {
        $this->SousDeclarationCollecteur = $SousDeclarationCollecteur;
    }

    public function getDeclarationProducteur() {
        return $this->DeclarationProducteur;
    }

    public function setDeclarationProducteur($DeclarationProducteur) {
        $this->DeclarationProducteur = $DeclarationProducteur;
    }

    public function getStatut() {
        return $this->statut;
    }

    public function setStatut($statut) {
        $this->statut = $statut;
    }

    public function getCentreTraitement() {
        return $this->CentreTraitement;
    }

    public function setCentreTraitement($CentreTraitement) {
        $this->CentreTraitement = $CentreTraitement;
    }

    public function getCentreTransit() {
        return $this->CentreTransit;
    }

    public function setCentreTransit($CentreTransit) {
        $this->CentreTransit = $CentreTransit;
    }

    public function getCentreDepot() {
        return $this->CentreDepot;
    }

    public function setCentreDepot($CentreDepot) {
        $this->CentreDepot = $CentreDepot;
    }

    public function getDechet() {
        return $this->Dechet;
    }

    public function setDechet(Dechet $Dechet) {
        $this->Dechet = $Dechet;
    }

    public function getFiliere() {
        return $this->Filiere;
    }

    public function setFiliere(Filiere $Filiere) {
        $this->Filiere = $Filiere;
    }

    public function getTraitFiliere() {
        return $this->traitFiliere;
    }

    public function setTraitFiliere(Filiere $traitFiliere) {
        $this->traitFiliere = $traitFiliere;
    }

    public function getNaf() {
        return $this->Naf;
    }

    public function setNaf(Naf $Naf) {
        $this->Naf = $Naf;
    }

    public function getNature() {
        return $this->nature;
    }

    public function setNature($nature) {
        $this->nature = $nature;
    }

    public function getDateFacture() {
        return $this->dateFacture;
    }

    public function setDateFacture($dateFacture) {
        $this->dateFacture = $dateFacture;
    }

    public function getNumFacture() {
        return $this->numFacture;
    }

    public function setNumFacture($numFacture) {
        $this->numFacture = $numFacture;
    }

    public function getCoutFacture() {
        return $this->coutFacture;
    }

    public function setCoutFacture($coutFacture) {
        $this->coutFacture = $coutFacture;
    }

    public function getQuantiteReel() {
        return $this->quantiteReel;
    }

    public function setQuantiteReel($quantiteReel) {
        $this->quantiteReel = $quantiteReel;
    }

    public function getMontReel() {
        return $this->montReel;
    }

    public function setMontReel($montReel) {
        $this->montReel = $montReel;
    }

    public function getQuantiteRet() {
        return $this->quantiteRet;
    }

    public function setQuantiteRet($quantiteRet) {
        $this->quantiteRet = $quantiteRet;
    }

    public function getMontRet() {
        return $this->montRet;
    }

    public function setMontRet($montRet) {
        $this->montRet = $montRet;
    }

    public function getQuantiteAide() {
        return $this->quantiteAide;
    }

    public function setQuantiteAide($quantiteAide) {
        $this->quantiteAide = $quantiteAide;
    }

    public function getMontAide() {
        return $this->montAide;
    }

    public function setMontAide($montAide) {
        $this->montAide = $montAide;
    }

    public function getDossierAide() {
        return $this->dossierAide;
    }

    public function getMontantAp() {
        return $this->montantAp;
    }

    public function getMontantApDispo() {
        return $this->montantApDispo;
    }

    public function setDossierAide($dossierAide) {
        $this->dossierAide = $dossierAide;
    }

    public function setMontantAp($montantAp) {
        $this->montantAp = $montantAp;
    }

    public function setMontantApDispo($montantApDispo) {
        $this->montantApDispo = $montantApDispo;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getCreated() {
        return $this->created;
    }

    public function setCreated($created) {
        $this->created = $created;
    }

    public function getUpdated() {
        return $this->updated;
    }

    public function setUpdated($updated) {
        $this->updated = $updated;
    }
    
    function getTauxAide() {
        return $this->tauxAide;
    }

    function getBonnifie() {
        return $this->bonnifie;
    }

    function setTauxAide($tauxAide) {
        $this->tauxAide = $tauxAide;
    }

    function setBonnifie($bonnifie) {
        $this->bonnifie = $bonnifie;
    }



}
