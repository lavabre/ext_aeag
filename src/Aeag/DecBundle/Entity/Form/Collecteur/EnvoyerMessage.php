<?php

/**
 * Description of Messages
 *
 * @author Lavabre
 */

namespace Aeag\DecBundle\Entity\Form\Collecteur;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class EnvoyerMessage {

    /**
     * @ORM\Column(name="destinataire", type="text", nullable=true)
     */
    protected $destinataire;

    /**
     * @ORM\Column(type="string", length=250, name="copie", nullable=true)
     * @Assert\Email
     */
    protected $copie;

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

    public function getCopie() {
        return $this->copie;
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

    public function setCopie($copie) {
        $this->copie = $copie;
    }

    public function setSujet($sujet) {
        $this->sujet = $sujet;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

}
