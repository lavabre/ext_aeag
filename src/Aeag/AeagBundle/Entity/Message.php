<?php

/**
 * Description of Messages
 *
 * @author Lavabre
 */

namespace Aeag\AeagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="Message",indexes={@ORM\Index(name="emetteur_idx", columns={"emetteur"}),@ORM\Index(name="recepteur_idx", columns={"recepteur"})})
 * @ORM\Entity(repositoryClass="Aeag\AeagBundle\Repository\MessageRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Message {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="message_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(name="emetteur", type="integer")
     * */
    private $Emetteur;

    /**
     * @ORM\Column(name="recepteur", type="integer")
     * */
    private $Recepteur;

    /**
     * @ORM\Column(name="nouveau", type="boolean")
     */
    protected $nouveau;

    /**
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    protected $message;

    /**
     * @ORM\Column(name="iteration", type="integer", nullable=true)
     */
    protected $iteration;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    public function __construct() {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getEmetteur() {
        return $this->Emetteur;
    }

    public function setEmetteur($Emetteur) {
        $this->Emetteur = $Emetteur;
    }

    public function getRecepteur() {
        return $this->Recepteur;
    }

    public function setRecepteur($Recepteur) {
        $this->Recepteur = $Recepteur;
    }

    public function getNouveau() {
        return $this->nouveau;
    }

    public function setNouveau($nouveau) {
        $this->nouveau = $nouveau;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getIteration() {
        return $this->iteration;
    }

    public function setIteration($iteration) {
        $this->iteration = $iteration;
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

}

