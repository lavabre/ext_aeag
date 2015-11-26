<?php

/**
 * Description of Messages
 *
 * @author Lavabre
 */

namespace Aeag\AeagBundle\Entity\Form;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class EnvoyerMessageInterlocuteur {

    /**
     *  @Assert\Email
     * @Assert\NotBlank(message="L'adresse email du destinatire est obligatoire")
     */
    private $destinataire;

    protected $sujet;

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
