<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PressionMeProposed
 *
 * @ORM\Table(name="pression_me_proposed", indexes={@ORM\Index(name="idx_eb36ba9850eae44", columns={"id_utilisateur"})})
 * @ORM\Entity
 */
class PressionMeProposed {

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
     * @ORM\Column(name="cd_pression", type="string", length=16, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cdPression;

    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="string", length=1, nullable=true)
     */
    private $valeur;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="text", nullable=false)
     */
    private $commentaire;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=255, nullable=false)
     */
    private $role;

    /**
     * @var PressionMe
     *
     * @ORM\ManyToOne(targetEntity="PressionMe", inversedBy="proposed")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="eu_cd", referencedColumnName="eu_cd"),
     * @ORM\JoinColumn(name="cd_pression", referencedColumnName="cd_pression")
     * })
     */
    private $pressionOriginale;

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
        if ($this->cdPression == 'RW_HYM_CONT' or
                $this->cdPression == 'RW_HYM_HYD' or
                $this->cdPression == 'RW_HYM_MOR') {
            switch ($this->valeur) {
                case '1' : return 'Minime';
                case '2' : return 'Modérée';
                case '3' : return 'Elevée';
                case 'U' : return 'Inconnu';
            }
        } else {
            if ($this->valeur) {
                switch ($this->valeur) {
                    case '1' : return 'Pas de pression';
                    case '2' : return 'Pression non significative';
                    case '3' : return 'Pression significative';
                    case 'U' : return 'Inconnu';
                }
            } else {
                return 'manque valeur';
            }
        }
    }

    /**
     * Set euCd
     *
     * @param string $euCd
     *
     * @return pressionMeProposed
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
     * @return pressionMeProposed
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
     * Set cdPression
     *
     * @param string $cdPression
     *
     * @return pressionMeProposed
     */
    public function setCdPression($cdPression) {
        $this->cdPression = $cdPression;

        return $this;
    }

    /**
     * Get cdPression
     *
     * @return string
     */
    public function getCdPression() {
        return $this->cdPression;
    }

    /**
     * Set valeur
     *
     * @param string $valeur
     *
     * @return pressionMeProposed
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
     * @return pressionMeProposed
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
     * Set pressionOriginale
     *
     * @param Aeag\EtatdeslieuxBundle\Entity\PressionMe $pressionOriginale
     */
    public function setPressionOriginale($pressionOriginale) {
        $this->pressionOriginale = $pressionOriginale;
    }

    /**
     * Get pressionOriginale
     *
     * @return Aeag\EtatdeslieuxBundle\Entity\PressionMe 
     */
    public function getPressionOriginale() {
        return $this->pressionOriginale;
    }

    /**
     * Set role
     *
     * @param string $role
     *
     * @return pressionMeProposed
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
     * Set utilisateur
     *
     * @param \Aeag\EdlBundle\Entity\Utilisateur $utilisateur
     *
     * @return pressionMeProposed
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
