<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Aeag\EdlBundle\Entity\RisqueMeProposed;

/**
 * Aeag\EdlBundle\Entity\RisqueMe
 *
 * @ORM\Table(name="risque_me")
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Repository\RisqueMeRepository")
 */
class RisqueMe
{

    /**
     * @var string $euCd
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     */
    private $euCd;
	
	/**
     * @var string $cdRisque
     *
     * @ORM\Column(name="cd_risque", type="string", length=16, nullable=false)
     * @ORM\Id
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
     * @var MasseEau
     *
     * @ORM\ManyToOne(targetEntity="MasseEau", inversedBy="risques")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eu_cd", referencedColumnName="eu_cd")
     * })
     */
    private $masseEau;
    
    /**
     * @var RisqueType
     *
     * @ORM\ManyToOne(targetEntity="RisqueType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cd_risque", referencedColumnName="cd_risque")
     * })
     */
    private $type;
    
    /**
     * @ORM\OneToMany(targetEntity="RisqueMeProposed", mappedBy="risqueOriginal")
     */
    private $proposed;
    
    
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
     * Set masseEau
     *
     * @param Aeag\EdlBundle\Entity\MasseEau $masseEau
     */
    public function setMasseEau(\Aeag\EdlBundle\Entity\MasseEau $masseEau)
    {
        $this->masseEau = $masseEau;
    }

    /**
     * Get masseEau
     *
     * @return Aeag\EdlBundle\Entity\MasseEau 
     */
    public function getMasseEau()
    {
        return $this->masseEau;
    }

    /**
     * Set type
     *
     * @param Aeag\EdlBundle\Entity\RisqueType $type
     */
    public function setType(\Aeag\EdlBundle\Entity\RisqueType $type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return Aeag\EdlBundle\Entity\RisqueType 
     */
    public function getType()
    {
        return $this->type;
    }
    public function __construct()
    {
        $this->proposed = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add proposed
     *
     * @param Aeag\EdlBundle\Entity\RisqueMeProposed $proposed
     */
    public function addProposed(\Aeag\EdlBundle\Entity\RisqueMeProposed $proposed)
    {
        $this->proposed[] = $proposed;
    }

    /**
     * Get proposed
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getProposed()
    {
        return $this->proposed;
    }


    /**
     * Add proposed
     *
     * @param Aeag\EdlBundle\Entity\RisqueMeProposed $proposed
     */
    public function addRisqueMeProposed(\Aeag\EdlBundle\Entity\RisqueMeProposed $proposed)
    {
        $this->proposed[] = $proposed;
    }
}