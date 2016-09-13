<?php

namespace Aeag\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Statistiques
 *
 * @ORM\Table(name="statistiques")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\UserBundle\Repository\StatistiquesRepository")
 */
class Statistiques {

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="statistiques_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    private $user;
    
    /**
     * @ORM\Column(type="string", length=10, name="appli", nullable=true)
     */
    protected $appli;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dateDebutConnexion;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dateFinConnexion;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbConnexion;

    /**
     *
     */
    public function __construct() {
        $this->setDateDebutConnexion(new \DateTime());
    }

    function getId() {
        return $this->id;
    }

    function getUser() {
        return $this->user;
    }

    function getAppli() {
        return $this->appli;
    }

    function getDateDebutConnexion() {
        return $this->dateDebutConnexion;
    }

    function getDateFinConnexion() {
        return $this->dateFinConnexion;
    }

    function getNbConnexion() {
        return $this->nbConnexion;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setUser($user) {
        $this->user = $user;
    }

    function setAppli($appli) {
        $this->appli = $appli;
    }

    function setDateDebutConnexion($dateDebutConnexion) {
        $this->dateDebutConnexion = $dateDebutConnexion;
    }

    function setDateFinConnexion($dateFinConnexion) {
        $this->dateFinConnexion = $dateFinConnexion;
    }

    function setNbConnexion($nbConnexion) {
        $this->nbConnexion = $nbConnexion;
    }


}
