<?php

namespace Aeag\SqeBundle\Utils;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;

class MessageMaker {

    public function envoiMessage($em, $mailer, $txtMessage, $destinataire, $objet, $expediteur = 'automate@eau-adour-garonne.fr') {
        
        //$this->createMessage($em, $destinataire, $txtMessage);
        
        //$this->createNotification($em, $destinataire, $txtMessage);
        
        return $this->createMail($em, $mailer, $txtMessage, $destinataire, $objet, $expediteur);
        
    }

    public function createMessage($em, $destinataire, $txtMessage) {
        $message = new Message();
        $message->setRecepteur($destinataire->getPrestataire()->getAdrCorId());
        $message->setEmetteur($destinataire->getPrestataire()->getAdrCorId());
        $message->setNouveau(true);
        $message->setIteration(2);
        $texte = "Bonjour ," . PHP_EOL;
        $texte .= " " . PHP_EOL;
        $texte .= $txtMessage;
        $texte .= " " . PHP_EOL;
        $texte .= "Cordialement.";
        $message->setMessage($texte);
        $em->persist($message);
        
        $em->flush();
    }

    public function createNotification($em, $destinataire, $txtMessage) {
        $notification = new Notification();
        $notification->setRecepteur($destinataire->getPrestataire()->getAdrCorId());
        $notification->setEmetteur($destinataire->getPrestataire()->getAdrCorId());
        $notification->setNouveau(true);
        $notification->setIteration(2);
        $notification->setMessage($txtMessage);
        $em->persist($notification);
        
        $em->flush();
    }

    public function createMail($em, $mailer, $txtMessage, $destinataire, $objet, $expediteur = 'automate@eau-adour-garonne.fr') {
        $htmlMessage = "<html><head></head><body>";
        $htmlMessage .= "Bonjour, <br/><br/>";
        $htmlMessage .= $txtMessage;
        $htmlMessage .= "<br/><br/>Cordialement, <br/>L'équipe SQE";
        $htmlMessage .= "</body></html>";
        try {
            // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
            $mail = \Swift_Message::newInstance('Wonderful Subject')
                    ->setSubject($objet)
                    ->setFrom($expediteur)
                    ->setTo($destinataire->getMail())
                    ->setBody($htmlMessage, 'text/html');

            $mailer->send($mail);

            $em->flush();
            return true;
        } catch (\Swift_TransportException $ex) {
            return false;
        }
    }

}
