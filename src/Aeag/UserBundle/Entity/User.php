<?php

namespace Aeag\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="aeag_user",indexes={@ORM\Index(name="userCorrespondant_idx", columns={"correspondant"})})
 * @ORM\Entity(repositoryClass="Aeag\UserBundle\Repository\UserRepository")
 */
class User extends BaseUser {

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=250, name="prenom", nullable=true)
     */
    protected $prenom;

    /**
     * @ORM\Column(type="string", length=250, name="email1", nullable=true)
     * @Assert\Email
     */
    protected $email1;

    /**
     * @ORM\Column(type="string", length=250, name="email2", nullable=true)
     * @Assert\Email
     */
    protected $email2;

    /**
     * @ORM\Column(type="string", length=20, name="tel", nullable=true)
     *
     * @var string $tel
     */
    protected $tel;

    /**
     * @ORM\Column(type="string", length=20, name="tel1", nullable=true)
     *
     * @var string $tel1
     */
    protected $tel1;

    /**
     * @ORM\Column(type="integer", length=10, nullable=true)
     */
    private $correspondant;

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * The salt to use for hashing
     * /**
     * The salt
     * @var string
     */
    protected $salt;

    public function _construct() {
        $this->roles = array();
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getPrenom() {
        return $this->prenom;
    }

    public function setPrenom($prenom) {
        $this->prenom = $prenom;
    }

    public function getEmail1() {
        return $this->email1;
    }

    public function getEmail2() {
        return $this->email2;
    }

    public function getTel() {
        return $this->tel;
    }

    public function setEmail1($email1) {
        $this->email1 = $email1;
    }

    public function setEmail2($email2) {
        $this->email2 = $email2;
    }

    public function setTel($tel) {
        $this->tel = $tel;
    }

    public function getTel1() {
        return $this->tel1;
    }

    public function setTel1($tel1) {
        $this->tel1 = $tel1;
    }

    public function getCorrespondant() {
        return $this->correspondant;
    }

    public function setCorrespondant($correspondant) {
        $this->correspondant = $correspondant;
    }

    public function getEnabled() {
        return $this->enabled;
    }

    public function getSalt() {
        return $this->salt;
    }

    public function setSalt($salt) {
        $this->salt = $salt;
    }

}
