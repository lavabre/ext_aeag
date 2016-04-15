<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\MasseEau
 *
 * @ORM\Table(name="masse_eau")
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Repository\MasseEauRepository")
 */
class MasseEau
{
	
	/**
     * @var string $euCd
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     */
    private $euCd;

    /**
     * @var string $nomMasseEau
     *
     * @ORM\Column(name="nom_masse_eau", type="string", length=250, nullable=false)
     */
    private $nomMasseEau;
    
    /**
     * @var string $typeMe
     *
     * @ORM\Column(name="type_me", type="string", length=2, nullable=false)
     */
    private $typeMe;

    /**
     * @var AdminDepartement
     *
     * @ORM\ManyToMany(targetEntity="DepartementMe", mappedBy="euCd")
     */
    private $inseeDepartement;

    /**
     * @var Station
     *
     * @ORM\ManyToMany(targetEntity="Station", inversedBy="euCd")
     * @ORM\JoinTable(name="station_me",
     *   joinColumns={
     *     @ORM\JoinColumn(name="eu_cd", referencedColumnName="eu_cd")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="code_station", referencedColumnName="code_station")
     *   }
     * )
     */
    private $codeStation;
    
    /**
     * @ORM\OneToMany(targetEntity="EtatMe", mappedBy="masseEau")
     */
    private $etats;
    
    /**
     * @ORM\OneToMany(targetEntity="PressionMe", mappedBy="masseEau")
     */
    private $pressions;
    
    /**
     * @ORM\OneToMany(targetEntity="ImpactMe", mappedBy="masseEau")
     */
    private $impacts;
    
    /**
     * @ORM\OneToMany(targetEntity="RisqueMe", mappedBy="masseEau")
     */
    private $risques;
   
    public function __construct()
    {
        $this->inseeDepartement = new \Doctrine\Common\Collections\ArrayCollection();
    $this->codeStation = new \Doctrine\Common\Collections\ArrayCollection();
    $this->cdEtat = new \Doctrine\Common\Collections\ArrayCollection();
    $this->cdImpact = new \Doctrine\Common\Collections\ArrayCollection();
    $this->cdPression = new \Doctrine\Common\Collections\ArrayCollection();
    $this->cdRisque = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getLibType() {
    	
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
     * Set nomMasseEau
     *
     * @param string $nomMasseEau
     */
    public function setNomMasseEau($nomMasseEau)
    {
        $this->nomMasseEau = $nomMasseEau;
    }

    /**
     * Get nomMasseEau
     *
     * @return string 
     */
    public function getNomMasseEau()
    {
        return $this->nomMasseEau;
    }

    /**
     * Add inseeDepartement
     *
     * @param Aeag\EdlBundle\Entity\AdminDepartement $inseeDepartement
     */
    public function addInseeDepartement(\Aeag\EdlBundle\Entity\AdminDepartement $inseeDepartement)
    {
        $this->inseeDepartement[] = $inseeDepartement;
    }

    /**
     * Get inseeDepartement
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getInseeDepartement()
    {
        return $this->inseeDepartement;
    }

    /**
     * Add codeStation
     *
     * @param Aeag\EdlBundle\Entity\Station $codeStation
     */
    public function addCodeStation(\Aeag\EdlBundle\Entity\Station $codeStation)
    {
        $this->codeStation[] = $codeStation;
    }

    /**
     * Get codeStation
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCodeStation()
    {
        return $this->codeStation;
    }


    /**
     * Add pressions
     *
     * @param Aeag\EdlBundle\Entity\PressionMe $pressions
     */
    public function addPressions(\Aeag\EdlBundle\Entity\PressionMe $pressions)
    {
        $this->pressions[] = $pressions;
    }

    /**
     * Get pressions
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPressions()
    {
        return $this->pressions;
    }

    /**
     * Add etats
     *
     * @param Aeag\EdlBundle\Entity\EtatMe $etats
     */
    public function addEtats(\Aeag\EdlBundle\Entity\EtatMe $etats)
    {
        $this->etats[] = $etats;
    }

    /**
     * Get etats
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getEtats()
    {
        return $this->etats;
    }

    /**
     * Add impacts
     *
     * @param Aeag\EdlBundle\Entity\ImpactMe $impacts
     */
    public function addImpacts(\Aeag\EdlBundle\Entity\ImpactMe $impacts)
    {
        $this->impacts[] = $impacts;
    }

    /**
     * Get impacts
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getImpacts()
    {
        return $this->impacts;
    }

    /**
     * Add risques
     *
     * @param Aeag\EdlBundle\Entity\RisqueMe $risques
     */
    public function addRisques(\Aeag\EdlBundle\Entity\RisqueMe $risques)
    {
        $this->risques[] = $risques;
    }

    /**
     * Get risques
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getRisques()
    {
        return $this->risques;
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
     * Add inseeDepartement
     *
     * @param Aeag\EdlBundle\Entity\AdminDepartement $inseeDepartement
     */
    public function addAdminDepartement(\Aeag\EdlBundle\Entity\AdminDepartement $inseeDepartement)
    {
        $this->inseeDepartement[] = $inseeDepartement;
    }

    /**
     * Add codeStation
     *
     * @param Aeag\EdlBundle\Entity\Station $codeStation
     */
    public function addStation(\Aeag\EdlBundle\Entity\Station $codeStation)
    {
        $this->codeStation[] = $codeStation;
    }

    /**
     * Add etats
     *
     * @param Aeag\EdlBundle\Entity\EtatMe $etats
     */
    public function addEtatMe(\Aeag\EdlBundle\Entity\EtatMe $etats)
    {
        $this->etats[] = $etats;
    }

    /**
     * Add pressions
     *
     * @param Aeag\EdlBundle\Entity\PressionMe $pressions
     */
    public function addPressionMe(\Aeag\EdlBundle\Entity\PressionMe $pressions)
    {
        $this->pressions[] = $pressions;
    }

    /**
     * Add impacts
     *
     * @param Aeag\EdlBundle\Entity\ImpactMe $impacts
     */
    public function addImpactMe(\Aeag\EdlBundle\Entity\ImpactMe $impacts)
    {
        $this->impacts[] = $impacts;
    }

    /**
     * Add risques
     *
     * @param Aeag\EdlBundle\Entity\RisqueMe $risques
     */
    public function addRisqueMe(\Aeag\EdlBundle\Entity\RisqueMe $risques)
    {
        $this->risques[] = $risques;
    }
}