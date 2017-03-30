<?php

namespace Aeag\AeagBundle\Services;

use Aeag\AeagBundle\Entity\Message;

/**
 * Description of Notification
 *
 * @author lavabre
 */
class Messages {

    public function createMessage($emetteur, $recepteur, $em, $session, $txtMessage) {

        if (!is_object($emetteur) || !is_object($recepteur)) {
            return;
        }
        $session->set('service', 'Messages');
        $session->set('fonction', 'createMessage');

        $message = new Message();
        $message->setRecepteur($recepteur->getId());
        $message->setEmetteur($emetteur->getId());
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

    public function envoiMessage($recepteur, $em, $session) {

        if (!$recepteur) {
            return;
        }
        $session->set('service', 'Messages');
        $session->set('fonction', 'envoiMessage');


        $messages = null;
        $mess = array();
        $repoUser = $em->getRepository('AeagUserBundle:User');
        $repoMessages = $em->getRepository('AeagAeagBundle:Message');
        $messages = $repoMessages->getMessageByRecepteur($recepteur);
        $i = 0;
        foreach ($messages as $message) {
            $emetteur = $repoUser->getUserById($message->getEmetteur());
            $recepteur = $repoUser->getUserById($message->getRecepteur());
            $mess[$i]['emetteur'] = $emetteur;
            $mess[$i]['recepteur'] = $recepteur;
            $mess[$i]['message'] = $message;
            $i++;
        }
        $session->set('Messages', $mess);
    }

}
