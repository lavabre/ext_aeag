<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Aeag\EdlBundle\Entity\EtatMeProposed;

/**
 * Aeag\EdlBundle\Entity\EtatMe
 *
 * @ORM\Table(name="etat_me")
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Repository\EtatMeRepository")
 */
class EtatMe {

    /**
     * @var string $euCd
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     */
    private $euCd;

    /**
     * @var string $cdEtat
     *
     * @ORM\Column(name="cd_etat", type="string", length=16, nullable=false)
     * @ORM\Id
     */
    private $cdEtat;

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
     * @var MasseEau
     *
     * @ORM\ManyToOne(targetEntity="MasseEau")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eu_cd", referencedColumnName="eu_cd")
     * })
     */
    private $masseEau;

    /**
     * @var EtatType
     *
     * @ORM\ManyToOne(targetEntity="EtatType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cd_etat", referencedColumnName="cd_etat")
     * })
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="EtatMeProposed", mappedBy="etatOriginal" )
     */
    private $proposed;

    /**
     * @var Utilisateur
     * 
     * @ORM\OneToOne(targetEntity="Aeag\EdlBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id", nullable=true)
     * })
     */
    private $utilisateur;

    public function getValueLib() {
        switch ($this->valeur) {
            case '1' : return 'Très bon état';
            case '2' : return 'Bon';
            case '3' : return 'Moyen';
            case '4' : return 'Médiocre';
            case '5' : return 'Mauvais';
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
     * Set cdEtat
     *
     * @param string $cdEtat
     */
    public function setCdEtat($cdEtat) {
        $this->cdEtat = $cdEtat;
    }

    /**
     * Get cdEtat
     *
     * @return string 
     */
    public function getCdEtat() {
        return $this->cdEtat;
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

    public function getUtilisateur() {
        return $this->utilisateur;
    }

    public function setUtilisateur($utilisateur) {
        $this->utilisateur = $utilisateur;
    }

    /**
     * Set masseEau
     *
     * @param Aeag\EdlBundle\Entity\MasseEau $masseEau
     */
    public function setMasseEau(\Aeag\EdlBundle\Entity\MasseEau $masseEau) {
        $this->masseEau = $masseEau;
    }

    /**
     * Get masseEau
     *
     * @return Aeag\EdlBundle\Entity\MasseEau 
     */
    public function getMasseEau() {
        return $this->masseEau;
    }

    /**
     * Set type
     *
     * @param Aeag\EdlBundle\Entity\EtatType $type
     */
    public function setType(\Aeag\EdlBundle\Entity\EtatType $type) {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return Aeag\EdlBundle\Entity\EtatType 
     */
    public function getType() {
        return $this->type;
    }

    public function __construct() {
        $this->proposed = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add proposed
     *
     * @param Aeag\EdlBundle\Entity\EtatMeProposed $proposed
     */
    public function addProposed(\Aeag\EdlBundle\Entity\EtatMeProposed $proposed) {
        $this->proposed[] = $proposed;
    }

    /**
     * Get proposed
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getProposed() {
        return $this->proposed;
    }
    
    function setProposed($proposed) {
        $this->proposed = $proposed;
    }

    
    /**
     * Add proposed
     *
     * @param Aeag\EdlBundle\Entity\EtatMeProposed $proposed
     */
    public function addEtatMeProposed(\Aeag\EdlBundle\Entity\EtatMeProposed $proposed) {
        $this->proposed[] = $proposed;
    }

}