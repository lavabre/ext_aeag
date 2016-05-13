<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EtatMeProposed
 *
 * @ORM\Table(name="etat_me_proposed", indexes={@ORM\Index(name="idx_b7e685ba50eae44", columns={"id_utilisateur"}), @ORM\Index(name="idx_b7e685baa8feab267a9ab42e", columns={"eu_cd", "cd_etat"})})
 * @ORM\Entity
 */
class EtatMeProposed {

    /**
     * @var string
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $euCd;

    /**
     * @var string
     *
     * @ORM\Column(name="proposition_date", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $propositionDate;

    /**
     * @var string
     *
     * @ORM\Column(name="cd_etat", type="string", length=16, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cdEtat;

    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="string", length=255, nullable=false)
     */
    private $valeur;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="text", nullable=true)
     */
    private $commentaire;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=255, nullable=false)
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
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id")
     * })
     */
    private $utilisateur;

    public function getValueLib() {
        if ($this->valeur) {
            switch ($this->valeur) {
                case '1' : return 'Très bon état';
                case '2' : return 'Bon';
                case '3' : return 'Moyen';
                case '4' : return 'Médiocre';
                case '5' : return 'Mauvais';
                case 'U' : return 'Inconnu';
            }
        } else {
            return 'non renseigner';
        }
    }

    /**
     * Set euCd
     *
     * @param string $euCd
     *
     * @return etatMeProposed
     */
    public function setEuCd($euCd) {
        $this->euCd = $euCd;

        return $this;
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
     * @param string $propositionDate
     *
     * @return etatMeProposed
     */
    public function setPropositionDate($propositionDate) {
        $this->propositionDate = $propositionDate;

        return $this;
    }

    /**
     * Get propositionDate
     *
     * @return string
     */
    public function getPropositionDate() {
        return $this->propositionDate;
    }

    /**
     * Set cdEtat
     *
     * @param string $cdEtat
     *
     * @return etatMeProposed
     */
    public function setCdEtat($cdEtat) {
        $this->cdEtat = $cdEtat;

        return $this;
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
     *
     * @return etatMeProposed
     */
    public function setValeur($valeur) {
        $this->valeur = $valeur;

        return $this;
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
     * @param string $commentaire
     *
     * @return etatMeProposed
     */
    public function setCommentaire($commentaire) {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string
     */
    public function getCommentaire() {
        return $this->commentaire;
    }

    /**
     * Set role
     *
     * @param string $role
     *
     * @return etatMeProposed
     */
    public function setRole($role) {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole() {
        return $this->role;
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
     * @param \Aeag\EdlBundle\Entity\Utilisateur $utilisateur
     *
     * @return etatMeProposed
     */
    public function setUtilisateur(\Aeag\EdlBundle\Entity\Utilisateur $utilisateur = null) {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur
     *
     * @return \Aeag\EdlBundle\Entity\Utilisateur
     */
    public function getUtilisateur() {
        return $this->utilisateur;
    }

}
