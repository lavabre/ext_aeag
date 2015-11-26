<?php

/**
 * Description of Messages
 *
 * @author Lavabre
 */

namespace Aeag\AeagBundle\Entity\Form;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class EnvoyerMessage {

    /**
     * @ORM\Column(name="destinataire", type="text", nullable=true)
     */
    protected $destinataire;

    /**
     * @ORM\Column(name="sujet", type="text", nullable=true)
     */
    protected $sujet;

    /**
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    protected $message;

    public function getDestinataire() {
        return $this->destinataire;
    }

    public function getSujet() {
        return $this->sujet;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setDestinataire($destinataire) {
        $this->destinataire = $destinataire;
    }

    public function setSujet($sujet) {
        $this->sujet = $sujet;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

}
