<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Aeag\EdlBundle\Entity\PressionMeProposed
 *
 * @ORM\Table(name="pression_me_proposed")
 * @ORM\Entity
 */
class PressionMeProposed {

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
     * @var string $cdPression
     *
     * @ORM\Column(name="cd_pression", type="string", length=16, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cdPression;

    /**
     * @var string $valeur
     *
     * @ORM\Column(name="valeur", type="string", nullable=false)
     * Assert\notNull(message = "l'état est obligatoire");
     */
    private $valeur;

    /**
     * @var text $commentaire
     *
     * @ORM\Column(name="commentaire", type="text", nullable=false)
     * Assert\notBlanck(message = "le commentaire est obligatoire");
     */
    private $commentaire;

    /**
     * @var string $role
     *
     * @ORM\Column(name="role", type="string", nullable=false)
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
     * @var Utilisateur
     * 
     * @ORM\ManyToOne(targetEntity="Aeag\EdlBundle\Entity\Utilisateur")
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
            switch ($this->valeur) {
                case '1' : return 'Pas de pression';
                case '2' : return 'Pression non significative';
                case '3' : return 'Pression significative';
                case 'U' : return 'Inconnu';
            }
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
     * Set cdPression
     *
     * @param string $cdPression
     */
    public function setCdPression($cdPression) {
        $this->cdPression = $cdPression;
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
     * Set pressionOriginale
     *
     * 
     */
    public function setPressionOriginale($pressionOriginale) {
        $this->pressionOriginale = $pressionOriginale;
    }

    /**
     * Get pressionOriginale
     *
     * @return Aeag\EdlBundle\Entity\PressionMe 
     */
    public function getPressionOriginale() {
        return $this->pressionOriginale;
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