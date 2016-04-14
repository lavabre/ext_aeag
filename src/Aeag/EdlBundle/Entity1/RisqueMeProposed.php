<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Aeag\EdlBundle\Entity\RisqueMeProposed
 *
 * @ORM\Table(name="risque_me_proposed")
 * @ORM\Entity
 */
class RisqueMeProposed
{

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
     * @var string $cdRisque
     *
     * @ORM\Column(name="cd_risque", type="string", length=16, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cdRisque;

   
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
     * @var RisqueMe
     *
     * @ORM\ManyToOne(targetEntity="RisqueMe", inversedBy="proposed")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eu_cd", referencedColumnName="eu_cd"),
     *   @ORM\JoinColumn(name="cd_risque", referencedColumnName="cd_risque")
     * })
     */
    private $risqueOriginal;
    
    /**
     * @var Utilisateur
     * 
     * @ORM\ManyToOne(targetEntity="Aeag\EdlBundle\Entity\Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id")
     * })
     */
     private $utilisateur; 
     
     
        public function getValueLib() 
    {
    	switch ($this->valeur) {
    		case '1' : return 'Pas de risque';
    		case '2' : return 'Doute';
                case '3' : return 'Risque';
        	case 'U' : return 'Inconnu';
    	}
    }
    
    /**
     * Set euCd
     *
     * @param string $euCd
     */
    public function setEuCd($euCd)
    {
        $this->euCd = $euCd;
    }

    /**
     * Get euCd
     *
     * @return string 
     */
    public function getEuCd()
    {
        return $this->euCd;
    }

    /**
     * Set propositionDate
     *
     * @param datetime $propositionDate
     */
    public function setPropositionDate($propositionDate)
    {
        $this->propositionDate = $propositionDate;
    }

    /**
     * Get propositionDate
     *
     * @return datetime 
     */
    public function getPropositionDate()
    {
        return $this->propositionDate;
    }

    /**
     * Set cdRisque
     *
     * @param string $cdRisque
     */
    public function setCdRisque($cdRisque)
    {
        $this->cdRisque = $cdRisque;
    }

    /**
     * Get cdRisque
     *
     * @return string 
     */
    public function getCdRisque()
    {
        return $this->cdRisque;
    }

   
    /**
     * Set valeur
     *
     * @param string $valeur
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;
    }

    /**
     * Get valeur
     *
     * @return string 
     */
    public function getValeur()
    {
        return $this->valeur;
    }

    /**
     * Set commentaire
     *
     * @param text $commentaire
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;
    }

    /**
     * Get commentaire
     *
     * @return text 
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set risqueOriginal
     *
     * @param Aeag\EdlBundle\Entity\RisqueMe $risqueOriginal
     */
    public function setRisqueOriginal(\Aeag\EdlBundle\Entity\RisqueMe $risqueOriginal)
    {
        $this->risqueOriginal = $risqueOriginal;
    }

    /**
     * Get risqueOriginal
     *
     * @return Aeag\EdlBundle\Entity\RisqueMe 
     */
    public function getRisqueOriginal()
    {
        return $this->risqueOriginal;
    }

    /**
     * Set utilisateur
     *
     * @param Aeag\EdlBundle\Entity\Utilisateur $utilisateur
     */
    public function setUtilisateur(\Aeag\EdlBundle\Entity\Utilisateur $utilisateur)
    {
        $this->utilisateur = $utilisateur;
    }

    /**
     * Get utilisateur
     *
     * @return Aeag\EdlBundle\Entity\Utilisateur 
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }
    
    public function getRole() {
        return $this->role;
    }

    public function setRole($role) {
        $this->role = $role;
    }


}