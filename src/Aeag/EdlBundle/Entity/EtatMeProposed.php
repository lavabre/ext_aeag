<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Aeag\EdlBundle\Entity\EtatMeProposed
 *
 * @ORM\Table(name="etat_me_proposed")
 * @ORM\Entity
 */
class EtatMeProposed {

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
     * @var string $cdEtat
     *
     * @ORM\Column(name="cd_etat", type="string", length=16, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
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
     * @Assert\NotBlank(message = "le commentaire est obligatoire");
     */
    private $commentaire;
    
     /**
     * @var string $role
     *
     * @ORM\Column(name="role", type="string", nullable=false)
     */
    private $role;


    /**
     * @var EtatMe
     *
     * @ORM\ManyToOne(targetEntity="EtatMe", inversedBy="proposed")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="eu_cd", referencedColumnName="eu_cd"),
     * @ORM\JoinColumn(name="cd_etat", referencedColumnName="cd_etat")
     * })
     */
    private $etatOriginal;

    /**
     * @var Utilisateur
     * 
     * @ORM\ManyToOne(targetEntity="Aeag\UtilisateurBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id")
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

    /**
     * Set etatOriginal
     *
     * @param Aeag\EdlBundle\Entity\EtatMe $etatOriginal
     */
    public function setEtatOriginal(\Aeag\EdlBundle\Entity\EtatMe $etatOriginal) {
        $this->etatOriginal = $etatOriginal;
    }

    /**
     * Get etatOriginal
     *
     * @return Aeag\EdlBundle\Entity\EtatMe 
     */
    public function getEtatOriginal() {
        return $this->etatOriginal;
    }

    /**
     * Set utilisateur
     *
     * @param Aeag\UtilisateurBundle\Entity\Utilisateur $utilisateur
     */
    public function setUtilisateur(\Aeag\UtilisateurBundle\Entity\Utilisateur $utilisateur) {
        $this->utilisateur = $utilisateur;
    }

    /**
     * Get utilisateur
     *
     * @return Aeag\UtilisateurBundle\Entity\Utilisateur 
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
