<?php

/**
 * Description of LivreOr
 *
 * @author Lavabre
 */

namespace Aeag\AeagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="LivreOr",indexes={@ORM\Index(name="emetteur_idx", columns={"emetteur"})})
 * @ORM\Entity(repositoryClass="Aeag\AeagBundle\Repository\LivreOrRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class LivreOr {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="livreOr_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(name="emetteur", type="integer")
     * */
    private $emetteur;

    /**
     * @ORM\Column(type="string", length=100)
     * 
     */
    private $application;

    /**
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    protected $message;

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

    function getId() {
        return $this->id;
    }

    function getEmetteur() {
        return $this->emetteur;
    }

    function getMessage() {
        return $this->message;
    }

    function getCreated() {
        return $this->created;
    }

    function getUpdated() {
        return $this->updated;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setEmetteur($emetteur) {
        $this->emetteur = $emetteur;
    }

    function setMessage($message) {
        $this->message = $message;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function setUpdated($updated) {
        $this->updated = $updated;
    }

    function getApplication() {
        return $this->application;
    }

    function setApplication($application) {
        $this->application = $application;
    }

}
