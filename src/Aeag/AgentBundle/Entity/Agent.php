<?php

namespace Aeag\AgentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="gtt_agent",indexes={@ORM\Index(name="identifiant_idx", columns={"matricule"})})
 * @ORM\Entity(repositoryClass="Aeag\AgentBundle\Repository\AgentRepository")
 * 
 */
class Agent {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $matricule;

    /**
     *
     * @ORM\Column(name="nom", type="string", length=30, nullable=true)
     */
    private $nom;

    /**
     *
     * @ORM\Column(name="prenom", type="string", length=30, nullable=true)
     */
    private $prenom;

    /**
     *
     * @ORM\Column(name="numero_badge", type="string", length=30, nullable=true)
     */
    private $numeroBadge;

    /**
     *
     * @ORM\Column(name="sexe", type="string", length=1, nullable=true)
     */
    private $sexe;

    /**
     *
     * @ORM\Column(name="categorie", type="string", length=2, nullable=true)
     */
    private $categorie;

    /**
     *
     * @ORM\Column(name="service", type="string", length=100, nullable=true)
     */
    private $service;

    /**
     *
     * @ORM\Column(name="fonction", type="string", length=100, nullable=true)
     */
    private $fonction;

    /**
     *
     * @ORM\Column(name="quotite_temps_partiel", type="decimal", precision=28, scale=0, nullable=true)
     */
    private $quotiteTempsPartiel;

    /**
     *
     * @ORM\Column(name="modif_dans_annee_cour", type="string", length=4, nullable=true)
     */
    private $modifDansAnneeCour;

    /**
     *
     * @ORM\Column(name="modif_dans_annee_prec", type="string", length=4, nullable=true)
     */
    private $modifDansAnneePrec;

    /**
     *
     * @ORM\Column(name="date_min_calcul_cour", type="string",  nullable=true)
     */
    private $dateMinCalculCour;

    /**
     *
     * @ORM\Column(name="date_max_calcul_cour", type="string",  nullable=true)
     */
    private $dateMaxCalculCour;

    /**
     *
     * @ORM\Column(name="date_min_calcul_prec", type="string",  nullable=true)
     */
    private $dateMinCalculPrec;

    /**
     *
     * @ORM\Column(name="date_max_calcul_prec", type="string",  nullable=true)
     */
    private $dateMaxCalculPrec;

    /**
     * @ORM\Column(name="statut", type="string", length=4, nullable=true)
     */
    private $statut;

    /**
     *
     * @ORM\Column(name="coef_heure_jour", type="integer", length=3, nullable=true)
     */
    private $coefHeureJour;

    /**
     *
     * @ORM\Column(name="date_entree", type="string",  nullable=true)
     */
    private $dateEntree;

    /**
     *
     * @ORM\Column(name="sous_direction", type="string", length=100, nullable=true)
     */
    private $sousDirection;

    /**
     *
     * @ORM\Column(name="date_sortie", type="string",  nullable=true)
     */
    private $dateSortie;

    /**
     *
     * @ORM\Column(name="code_service", type="string", length=100, nullable=true)
     */
    private $codeService;

    /**
     *
     * @ORM\Column(name="code_sous_direction", type="string", length=100, nullable=true)
     */
    private $codeSousDirection;

    /**
     *
     * @ORM\Column(name="cor_id", type="integer", length=10, nullable=true)
     */
    private $corId;

    function getMatricule() {
        return $this->matricule;
    }

    function getNom() {
        return $this->nom;
    }

    function getPrenom() {
        return $this->prenom;
    }

    function getNumeroBadge() {
        return $this->numeroBadge;
    }

    function getSexe() {
        return $this->sexe;
    }

    function getCategorie() {
        return $this->categorie;
    }

    function getService() {
        return $this->service;
    }

    function getFonction() {
        return $this->fonction;
    }

    function getQuotiteTempsPartiel() {
        return $this->quotiteTempsPartiel;
    }

    function getModifDansAnneeCour() {
        return $this->modifDansAnneeCour;
    }

    function getModifDansAnneePrec() {
        return $this->modifDansAnneePrec;
    }

    function getDateMinCalculCour() {
        return $this->dateMinCalculCour;
    }

    function getDateMaxCalculCour() {
        return $this->dateMaxCalculCour;
    }

    function getDateMinCalculPrec() {
        return $this->dateMinCalculPrec;
    }

    function getDateMaxCalculPrec() {
        return $this->dateMaxCalculPrec;
    }

    function getStatut() {
        return $this->statut;
    }

    function getCoefHeureJour() {
        return $this->coefHeureJour;
    }

    function getDateEntree() {
        return $this->dateEntree;
    }

    function getSousDirection() {
        return $this->sousDirection;
    }

    function getDateSortie() {
        return $this->dateSortie;
    }

    function getCodeService() {
        return $this->codeService;
    }

    function getCodeSousDirection() {
        return $this->codeSousDirection;
    }

    function getCorId() {
        return $this->corId;
    }

    function setMatricule($matricule) {
        $this->matricule = $matricule;
    }

    function setNom($nom) {
        $this->nom = $nom;
    }

    function setPrenom($prenom) {
        $this->prenom = $prenom;
    }

    function setNumeroBadge($numeroBadge) {
        $this->numeroBadge = $numeroBadge;
    }

    function setSexe($sexe) {
        $this->sexe = $sexe;
    }

    function setCategorie($categorie) {
        $this->categorie = $categorie;
    }

    function setService($service) {
        $this->service = $service;
    }

    function setFonction($fonction) {
        $this->fonction = $fonction;
    }

    function setQuotiteTempsPartiel($quotiteTempsPartiel) {
        $this->quotiteTempsPartiel = $quotiteTempsPartiel;
    }

    function setModifDansAnneeCour($modifDansAnneeCour) {
        $this->modifDansAnneeCour = $modifDansAnneeCour;
    }

    function setModifDansAnneePrec($modifDansAnneePrec) {
        $this->modifDansAnneePrec = $modifDansAnneePrec;
    }

    function setDateMinCalculCour($dateMinCalculCour) {
        $this->dateMinCalculCour = $dateMinCalculCour;
    }

    function setDateMaxCalculCour($dateMaxCalculCour) {
        $this->dateMaxCalculCour = $dateMaxCalculCour;
    }

    function setDateMinCalculPrec($dateMinCalculPrec) {
        $this->dateMinCalculPrec = $dateMinCalculPrec;
    }

    function setDateMaxCalculPrec($dateMaxCalculPrec) {
        $this->dateMaxCalculPrec = $dateMaxCalculPrec;
    }

    function setStatut($statut) {
        $this->statut = $statut;
    }

    function setCoefHeureJour($coefHeureJour) {
        $this->coefHeureJour = $coefHeureJour;
    }

    function setDateEntree($dateEntree) {
        $this->dateEntree = $dateEntree;
    }

    function setSousDirection($sousDirection) {
        $this->sousDirection = $sousDirection;
    }

    function setDateSortie($dateSortie) {
        $this->dateSortie = $dateSortie;
    }

    function setCodeService($codeService) {
        $this->codeService = $codeService;
    }

    function setCodeSousDirection($codeSousDirection) {
        $this->codeSousDirection = $codeSousDirection;
    }

    function setCorId($corId) {
        $this->corId = $corId;
    }

}
