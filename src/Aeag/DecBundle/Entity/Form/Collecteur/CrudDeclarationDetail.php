<?php

namespace Aeag\DecBundle\Entity\Form\Collecteur;

use Symfony\Component\Validator\Constraints as Assert;

class CrudDeclarationDetail {

     private $SousDeclarationCollecteur;

      private $DeclarationProducteur;

     private $statut;

    /**
     * 
     * @Assert\NotBlank(message="Le producteur est obligatoire")
     * */
    protected $Producteur;

    /**
     * @Assert\NotBlank(message="Le centre de traitement est obligatoire")
     * 
     * */
    protected $CentreTraitement;

    /**
     *
     * */
    protected $CentreTransit;

    /**
     * 
      * */
    protected $CentreDepot;

    /**
     * 
     * @Assert\NotBlank(message="Le code dechet est obligatoire")
     */
    private $Dechet;

    /**
      * @Assert\NotBlank(message="Le code conditionnement est obligatoire")
     * */
    protected $FiliereAide;

    /**
     * 
     * @Assert\NotBlank(message="le code D/R est obligatoire")
     * */
    protected $traitFiliere;

    /**
     * @Assert\NotBlank(message="le code NAF est obligatoire")
     * */
    protected $Naf;

    /**
     *
     */
    private $nature;

    /**
     * @Assert\NotBlank(message="La date de la facture est obligatoire")
     * @Assert\Type(type="datetime", message="La date de facture est incorrecte")
     */
    protected $dateFacture;

    /**
     *
     * @Assert\NotBlank(message="Le numéro de de la facture est obligatoire")
     * 
     */
    private $numFacture;

    /**
     *
     * @Assert\NotBlank(message="La quantité pesée est obligatoire")
     * @Assert\Type(type="numeric", message="La quantité pesée doit être une valeur numerique")
     * @Assert\Regex(pattern="/^[0-9]+(\.[0-9]{1,3})?$/", message="la quantité pesée ne peut avoir plus de 3 décimales"))
     */
    private $quantiteReel;

    /**
     *
     * @Assert\NotBlank(message="Le coût facturé est obligatoire")
     * @Assert\LessThanOrEqual(99,message=" Le coût facturé ne doit pas être supérieur à 99 €/kg")
     * @Assert\Type(type="numeric", message="Le coût facturé doit être une valeur numerique")
     * @Assert\Regex(pattern="/^[0-9]+(\.[0-9]{1,5})?$/", message="le coût facturé ne peut avoir plus de 5 décimales"))
     */
    private $coutFacture;

    /**
     *
     * @Assert\Type(type="numeric", message="Le montant réél doit être une valeur numerique")
     */
    private $montReel;

    /**
     *
     * @Assert\Type(type="numeric", message="la quantité retenue doit être une valeur numerique")
     */
    private $quantiteRet;

    /**
     */
    private $montRet;

    /**
     *
     * @Assert\Type(type="numeric", message="la quantité aidée doit être une valeur numerique")
     */
    private $quantiteAide;

    /**
     *
     * @Assert\Type(type="numeric", message="Le montant devl'aide doit être une valeur numerique")
     */
    private $montAide;
    
     /**
     *
     * @Assert\Type(type="numeric")
     */
    private $tauxAide;
    
     /**
     *
     * @Assert\Type(type="boolean")
     */
    private $bonnifie;
    
    /**
     *
     * 
     */
    private $message;
    
   
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

    public function getProducteur() {
        return $this->Producteur;
    }

    public function setProducteur($Producteur) {
        $this->Producteur = $Producteur;
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

    public function setDechet($Dechet) {
        $this->Dechet = $Dechet;
    }

    public function getFiliereAide() {
        return $this->FiliereAide;
    }

    public function setFiliereAide($FiliereAide) {
        $this->FiliereAide = $FiliereAide;
    }

    public function getTraitFiliere() {
        return $this->traitFiliere;
    }

    public function setTraitFiliere($traitFiliere) {
        $this->traitFiliere = $traitFiliere;
    }

    public function getNaf() {
        return $this->Naf;
    }

    public function setNaf($Naf) {
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

    public function getQuantiteReel() {
        return $this->quantiteReel;
    }

    public function setQuantiteReel($quantiteReel) {
        $this->quantiteReel = $quantiteReel;
    }

    public function getCoutFacture() {
        return $this->coutFacture;
    }

    public function setCoutFacture($coutFacture) {
        $this->coutFacture = $coutFacture;
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

    function getTauxAide() {
        return $this->tauxAide;
    }

    function getBonnifie() {
        return $this->bonnifie;
    }

    function getMessage() {
        return $this->message;
    }

    function setTauxAide($tauxAide) {
        $this->tauxAide = $tauxAide;
    }

    function setBonnifie($bonnifie) {
        $this->bonnifie = $bonnifie;
    }

    function setMessage($message) {
        $this->message = $message;
    }


}