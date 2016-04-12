<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Aeag\EdlBundle\Entity\ImpactMeProposed;

/**
 * Aeag\EdlBundle\Entity\ImpactMe
 *
 * @ORM\Table(name="impact_me")
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Entity\ImpactMeRepository")
 */
class ImpactMe
{
    /**
     * @var string $euCd
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     */
    private $euCd;
	
	/**
     * @var string $cdImpact
     *
     * @ORM\Column(name="cd_impact", type="string", length=16, nullable=false)
     * @ORM\Id
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
     * @var MasseEau
     *
     * @ORM\ManyToOne(targetEntity="MasseEau", inversedBy="impacts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eu_cd", referencedColumnName="eu_cd")
     * })
     */
    private $masseEau;
    
    /**
     * @var ImpactType
     *
     * @ORM\ManyToOne(targetEntity="ImpactType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cd_impact", referencedColumnName="cd_impact")
     * })
     */
    private $type;
    
    /**
     * @ORM\OneToMany(targetEntity="ImpactMeProposed", mappedBy="impactOriginal")
     */
    private $proposed;
    
    
    public function getValueLib() 
    {
    	switch ($this->valeur) {
    		case '1' : return 'Oui';
    		case '2' : return 'Non';
    		case 'U' : return 'Inconnu';
    	}
    }
    
/*    public function getDerniereProposition() 
    {
    	$em = EdlBundle::getContainer()->get('doctrine')->getEntityManager('default'); 
		
		$query = $em->createQueryBuilder('p')
		    ->orderBy('p.proposition_date', 'DESC')
		    ->getQuery();
		
		$products = $query->getSingleResult();    	
    }
*/    
    
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
     * Set cdImpact
     *
     * @param string $cdImpact
     */
    public function setCdImpact($cdImpact)
    {
        $this->cdImpact = $cdImpact;
    }

    /**
     * Get cdImpact
     *
     * @return string 
     */
    public function getCdImpact()
    {
        return $this->cdImpact;
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
     * @param Aeag\EdlBundle\Entity\ImpactType $type
     */
    public function setType(\Aeag\EdlBundle\Entity\ImpactType $type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return Aeag\EdlBundle\Entity\ImpactType 
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
     * @param Aeag\EdlBundle\Entity\ImpactMeProposed $proposed
     */
    public function addProposed(\Aeag\EdlBundle\Entity\ImpactMeProposed $proposed)
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
     * @param Aeag\EdlBundle\Entity\ImpactMeProposed $proposed
     */
    public function addImpactMeProposed(\Aeag\EdlBundle\Entity\ImpactMeProposed $proposed)
    {
        $this->proposed[] = $proposed;
    }
}