<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EtatMe
 *
 * @ORM\Table(name="etat_me", uniqueConstraints={@ORM\UniqueConstraint(name="uniq_1e35736d50eae44", columns={"id_utilisateur"})}, indexes={@ORM\Index(name="IDX_5AFC9DC37A9AB42E", columns={"cd_etat"}), @ORM\Index(name="IDX_5AFC9DC3A8FEAB26", columns={"eu_cd"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Repository\EtatMeRepository")
 */
class EtatMe {

    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="string", nullable=false)
     */
    private $valeur;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="text", nullable=true)
     */
    private $commentaire;

    /**
     * @var \Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id")
     * })
     */
    private $utilisateur;

    /**
     * @var \EtatType
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="EtatType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cd_etat", referencedColumnName="cd_etat")
     * })
     */
    private $etatType;

    /**
     * @var \MasseEau
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="MasseEau")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eu_cd", referencedColumnName="eu_cd")
     * })
     */
    private $masseEau;

    /**
     * @ORM\OneToMany(targetEntity="EtatMeProposed", mappedBy="etatOriginal" )
     */
    private $proposed;

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

    /**
     * Add proposed
     *
     * @param Aeag\EdlBundle\Entity\EtatMeProposed $proposed
     */
    public function addEtatMeProposed(\Aeag\EdlBundle\Entity\EtatMeProposed $proposed) {
        $this->proposed[] = $proposed;
    }

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
     * Set valeur
     *
     * @param string $valeur
     *
     * @return etatMe
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
     * @return etatMe
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
     * Set utilisateur
     *
     * @param \Aeag\EdlBundle\Entity\Utilisateur $utilisateur
     *
     * @return etatMe
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

    /**
     * Set $etatType
     *
     * @param \Aeag\EdlBundle\Entity\EtatType $etatType
     *
     * @return $etatType
     */
    public function setEtatType(\Aeag\EdlBundle\Entity\EtatType $etatType) {
        $this->etatType = $etatType;

        return $this;
    }

    /**
     * Get cdEtat
     *
     * @return \Aeag\EdlBundle\Entity\EtatType
     */
    public function getEtatType() {
        return $this->etatType;
    }

    /**
     * Set masseEau
     *
     * @param \Aeag\EdlBundle\Entity\MasseEau $masseEau
     *
     * @return $masseEau
     */
    public function setMasseEau(\Aeag\EdlBundle\Entity\MasseEau $masseEau) {
        $this->masseEau = $masseEau;

        return $this;
    }

    /**
     * Get masseEau
     *
     * @return \Aeag\EdlBundle\Entity\MasseEau
     */
    public function getMasseEau() {
        return $this->masseEau;
    }

}
