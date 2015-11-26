<?php

/**
 * Description of  CollecteurProducteur
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="CollecteurProducteur", indexes={@ORM\Index(name="CollecteurProducteur_idx", columns={"collecteur_id"})})
 * @ORM\Entity(repositoryClass="Aeag\DecBundle\Repository\CollecteurProducteurRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class CollecteurProducteur {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="collecteurproducteur_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(name="collecteur_id",type="integer")
     */
    private $Collecteur;

    /**
     * @ORM\Column(name="producteur_id",type="integer")
     */
    private $Producteur;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     *
     */
    public function __construct() {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedValue() {
        $this->setUpdated(new \DateTime());
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getCollecteur() {
        return $this->Collecteur;
    }

    public function setCollecteur($Collecteur) {
        $this->Collecteur = $Collecteur;
    }

    public function getProducteur() {
        return $this->Producteur;
    }

    public function setProducteur($Producteur) {
        $this->Producteur = $Producteur;
    }

    public function getCreated() {
        return $this->created;
    }

    public function setCreated($created) {
        $this->created = $created;
    }

    public function getUpdated() {
        return $this->updated;
    }

    public function setUpdated($updated) {
        $this->updated = $updated;
    }
    
    public function getSiretLibelleProducteur() {
        $producteur = $this->getProducteur();
        return $producteur->getSiret() . ' : '  . $producteur->getLibelle() . '      ' . $producteur->getCp() . ' ' . $producteur->getVille();
    }

}

