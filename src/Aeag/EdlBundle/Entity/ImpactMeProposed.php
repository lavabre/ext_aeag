<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\ImpactMeProposed
 *
 * @ORM\Table(name="impact_me_proposed")
 * @ORM\Entity
 */
class ImpactMeProposed {

    /**
     * @var string $euCd
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $euCd;

    /**
     * @var string $propositionDate
     *
     * @ORM\Column(name="proposition_date", type="string", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $propositionDate;

    /**
     * @var string $cdImpact
     *
     * @ORM\Column(name="cd_impact", type="string", length=16, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cdImpact;

    /**
     * @var string $valeur
     *
     * @ORM\Column(name="valeur", type="string", nullable=false)
     */
    private $valeur;

    /**
     * @var text $commentaire
     *
     * @ORM\Column(name="commentaire", type="text", nullable=true)
     */
    private $commentaire;

    /**
     * @var string $role
     *
     * @ORM\Column(name="role", type="string", nullable=false)
     */
    private $role;

    /**
     * @var ImpactMe
     *
     * @ORM\ManyToOne(targetEntity="ImpactMe", inversedBy="proposed")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eu_cd", referencedColumnName="eu_cd"),
     *   @ORM\JoinColumn(name="cd_impact", referencedColumnName="cd_impact")
     * })
     */
    private $impactOriginal;

    /**
     * @var Utilisateur
     * 
     * @ORM\ManyToOne(targetEntity="Aeag\EdlBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id")
     * })
     */
    private $utilisateur;

    public function getValueLib() {
        switch ($this->valeur) {
            case '1' : return 'Oui';
            case '2' : return 'Non';
            case 'U' : return 'Inconnu';
        }
    }

    /**
     * Set euCd
     *
     * @param string $euCd
     */
    public function setEuCd($euCd) {
        $this->euCd = $euCd;
    }

    /**
     * Get euCd
     *
     * @return string 
     */
    public function getEuCd() {
        return $this->euCd;
    }

    /**
     * Set propositionDate
     *
     * @param datetime $propositionDate
     */
    public function setPropositionDate($propositionDate) {
        $this->propositionDate = $propositionDate;
    }

    /**
     * Get propositionDate
     *
     * @return datetime 
     */
    public function getPropositionDate() {
        return $this->propositionDate;
    }

    /**
     * Set cdImpact
     *
     * @param string $cdImpact
     */
    public function setCdImpact($cdImpact) {
        $this->cdImpact = $cdImpact;
    }

    /**
     * Get cdImpact
     *
     * @return string 
     */
    public function getCdImpact() {
        return $this->cdImpact;
    }

    /**
     * Set login
     *
     * @param string $login
     */
    public function setLogin($login) {
        $this->login = $login;
    }

    /**
     * Get login
     *
     * @return string 
     */
    public function getLogin() {
        return $this->login;
    }

    /**
     * Set valeur
     *
     * @param string $valeur
     */
    public function setValeur($valeur) {
        $this->valeur = $valeur;
    }

    /**
     * Get valeur
     *
     * @return string 
     */
    public function getValeur() {
        return $this->valeur;
    }

    /**
     * Set commentaire
     *
     * @param text $commentaire
     */
    public function setCommentaire($commentaire) {
        $this->commentaire = $commentaire;
    }

    /**
     * Get commentaire
     *
     * @return text 
     */
    public function getCommentaire() {
        return $this->commentaire;
    }

    /**
     * Set impactOriginal
     *
     * @param Aeag\EdlBundle\Entity\ImpactMe $impactOriginal
     */
    public function setImpactOriginal(\Aeag\EdlBundle\Entity\ImpactMe $impactOriginal) {
        $this->impactOriginal = $impactOriginal;
    }

    /**
     * Get impactOriginal
     *
     * @return Aeag\EdlBundle\Entity\ImpactMe 
     */
    public function getImpactOriginal() {
        return $this->impactOriginal;
    }

    /**
     * Set utilisateur
     *
     * @param Aeag\EdlBundle\Entity\Utilisateur $utilisateur
     */
    public function setUtilisateur(\Aeag\EdlBundle\Entity\Utilisateur $utilisateur) {
        $this->utilisateur = $utilisateur;
    }

    /**
     * Get utilisateur
     *
     * @return Aeag\EdlBundle\Entity\Utilisateur 
     */
    public function getUtilisateur() {
        return $this->utilisateur;
    }

    public function getRole() {
        return $this->role;
    }

    public function setRole($role) {
        $this->role = $role;
    }

}