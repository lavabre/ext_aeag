<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;
use Aeag\EdlBundle\Entity\PressionMeProposed;

/**
 * Aeag\EdlBundle\Entity\PressionMe
 *
 * @ORM\Table(name="pression_me")
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Repository\PressionMeRepository")
 */
class PressionMe {

    /**
     * @var string $euCd
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     */
    private $euCd;

    /**
     * @var string $cdPression
     *
     * @ORM\Column(name="cd_pression", type="string", length=16, nullable=false)
     * @ORM\Id
     */
    private $cdPression;

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
     * @ORM\ManyToOne(targetEntity="MasseEau", inversedBy="pressions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="eu_cd", referencedColumnName="eu_cd")
     * })
     */
    private $masseEau;

    /**
     * @var PressionType
     *
     * @ORM\ManyToOne(targetEntity="PressionType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cd_pression", referencedColumnName="cd_pression")
     * })
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="PressionMeProposed", mappedBy="pressionOriginale")
     */
    private $proposed;

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
     * Set masseEau
     *
     * @param Aeag\EdlBundle\Entity\MasseEau $masseEau
     */
    public function setMasseEau(\Aeag\EdlBundle\Entity\MasseEau $masseEau) {
        $this->masseEau = $masseEau;
    }

    /**
     * Get masseEau
     *
     * @return Aeag\EdlBundle\Entity\MasseEau 
     */
    public function getMasseEau() {
        return $this->masseEau;
    }

    /**
     * Set type
     *
     * @param Aeag\EdlBundle\Entity\PressionType $type
     */
    public function setType(\Aeag\EdlBundle\Entity\PressionType $type) {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return Aeag\EdlBundle\Entity\PressionType 
     */
    public function getType() {
        return $this->type;
    }

    public function __construct() {
        $this->proposed = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add proposed
     *
     * @param Aeag\EdlBundle\Entity\PressionMeProposed $proposed
     */
    public function addProposed(\Aeag\EdlBundle\Entity\PressionMeProposed $proposed) {
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
     * @param Aeag\EdlBundle\Entity\PressionMeProposed $proposed
     */
    public function addPressionMeProposed(\Aeag\EdlBundle\Entity\PressionMeProposed $proposed) {
        $this->proposed[] = $proposed;
    }

}