<?php
namespace Aeag\EdlBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @UniqueEntity(fields="username", message="Ce login existe déjà")
 * @UniqueEntity(fields="email", message="Ce email existe déjà")
 */
class Utilisateur extends BaseUser
{
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\generatedValue(strategy="AUTO")
    */
    protected $id;
    
       
    /**
     * @ORM\Column(type="string", length=30, name="passwordEnClair", nullable=false)
     *
     * @var string $passwordEnClair
     */
    protected $passwordEnClair;
    
    
    
    /**
     * @ORM\OneToMany(targetEntity="Aeag\EdlBundle\Entity\DepUtilisateur", mappedBy="utilisateur")
     */
    protected $dept;
    
    /**
     * @ORM\OneToMany(targetEntity="Aeag\EdlBundle\Entity\EtatMeProposed", mappedBy="utilisateur")
     */
    protected $etatMeProposed;
    
    /**
     * @ORM\OneToMany(targetEntity="Aeag\EdlBundle\Entity\PressionMeProposed", mappedBy="utilisateur")
     */
    protected $pressionMeProposed;
    
    /**
     * @ORM\OneToMany(targetEntity="Aeag\EdlBundle\Entity\ImpactMeProposed", mappedBy="utilisateur")
     */
    protected $impactMeProposed;
    
    /**
     * @ORM\OneToMany(targetEntity="Aeag\EdlBundle\Entity\RisqueMeProposed", mappedBy="utilisateur")
     */
    protected $risqueMeProposed;
    
    
    protected $enabled;
    
 
    public function __construct()
    {
        parent::__construct();
        
        
        
        $this->dept = new ArrayCollection();
        $this->etatMeProposed = new ArrayCollection();
        $this->pressionMeProposed = new ArrayCollection();
        $this->impactMeProposed = new ArrayCollection();
        $this->risqueMeProposed = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
     public function setId($id) {
        $this->id = $id;
    }
    
   
    
    /**
     * Add dept
     *
     * @param Aeag\EdlBundle\Entity\DepUtilisateur $dept
     */
    public function addDepUtilisateur(\Aeag\EdlBundle\Entity\DepUtilisateur $dept)
    {
        $this->dept[] = $dept;
    }

    /**
     * Get dept
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getDept()
    {
        return $this->dept;
    }
    
     /**
     * Add etatMeProposed
     *
     * @param Aeag\EdlBundle\Entity\EtatMeProposed $etatMe
     */
    public function addEtatMeProposed(\Aeag\EdlBundle\Entity\EtatMeProposed $etatMe)
    {
        $this->etatMeProposed[] = $etatMe;
    }

    /**
     * Get etatMeProposed
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getEtatMeProposed()
    {
        return $this->etatMeproposed;
    }
   
    
    /**
     * Add pressionMeProposed
     *
     * @param Aeag\pressiondeslieuxBundle\Entity\PressionMeProposed $pressionMe
     */
    public function addPressionMeProposed(\Aeag\EdlBundle\Entity\PressionMeProposed $pressionMe)
    {
        $this->pressionMeProposed[] = $pressionMe;
    }

    /**
     * Get pressionMeProposed
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPressionMeProposed()
    {
        return $this->pressionMeproposed;
    }
    
    
     /**
     * Add impactMeProposed
     *
     * @param Aeag\EdlBundle\Entity\ImpactMeProposed $impactMe
     */
    public function addImpactMeProposed(\Aeag\EdlBundle\Entity\ImpactMeProposed $impactMe)
    {
        $this->impactMeProposed[] = $impactMe;
    }

    /**
     * Get impactMeProposed
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getImpactMeProposed()
    {
        return $this->impactMeproposed;
    }
    
    
    
     /**
     * Add risqueMeProposed
     *
     * @param Aeag\EdlBundle\Entity\RisqueMeProposed $risqueMe
     */
    public function addRisqueMeProposed(\Aeag\EdlBundle\Entity\RisqueMeProposed $risqueMe)
    {
        $this->risqueMeProposed[] = $risqueMe;
    }

    /**
     * Get risqueMeProposed
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getRisqueMeProposed()
    {
        return $this->risqueMeproposed;
    }
  
    public function getPasswordEnClair() {
        return $this->passwordEnClair;
    }

    public function setPasswordEnClair($passwordEnClair) {
        $this->passwordEnClair = $passwordEnClair;
    }

    public function getEnabled() {
        return $this->enabled;
    }

    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }


  
}