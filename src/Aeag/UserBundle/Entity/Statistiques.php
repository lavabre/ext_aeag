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
     * @ORM\Column(type="datetime")
     */
    protected $dateConnexion;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbConnexion;

    /**
     *
     */
    public function __construct() {
        $this->setDateConnexion(new \DateTime());
    }

    function getId() {
        return $this->id;
    }

    function getUser() {
        return $this->user;
    }

    function getDateConnexion() {
        return $this->dateConnexion;
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

    function setDateConnexion($dateConnexion) {
        $this->dateConnexion = $dateConnexion;
    }

    function setNbConnexion($nbConnexion) {
        $this->nbConnexion = $nbConnexion;
    }

}
