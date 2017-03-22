<?php

namespace Aeag\AeagBundle\Services;

use Aeag\AeagBundle\Entity\Notification;

/**
 * Description of Notification
 *
 * @author lavabre
 */
class Notifications {

    public function createNotification($emetteur, $recepteur, $em, $session, $txtMessage) {
        if (!$emetteur or ! $recepteur) {
            return;
        }
        $session->set('service', 'Notifications');
        $session->set('fonction', 'createNotification');

        $notification = new Notification();
        $notification->setRecepteur($recepteur->getId());
        $notification->setEmetteur($emetteur->getId());
        $notification->setNouveau(true);
        $notification->setIteration(5);
        $notification->setMessage($txtMessage);
        $em->persist($notification);

        $em->flush();
    }

    public function envoiNotification($recepteur, $em, $session) {

        if (!$recepteur) {
            return;
        }
        $session->set('service', 'Notifications');
        $session->set('fonction', 'envoiNotification');

        $notifications = null;

        $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');
        $notifications = $repoNotifications->getNotificationByRecepteur($recepteur);
        $session->set('Notifications', '');
        if ($notifications) {
            foreach ($notifications as $notification) {
                if ($notification->getIteration() == 0) {
                    $em->remove($notification);
                } else {
                    $iteration = $notification->getIteration() - 1;
                    $notification->setIteration($iteration);
                    $em->persist($notification);
                }
            }
            $em->flush();
            $notifications = $repoNotifications->getNotificationByRecepteur($recepteur);
            $noti = array();
            $i = 0;
            foreach ($notifications as $notification) {
                $repoUser = $em->getRepository('AeagUserBundle:User');
                $emetteur = $repoUser->getUserById($notification->getEmetteur());
                $recepteur = $repoUser->getUserById($notification->getRecepteur());
                $noti[$i]['emetteur'] = $emetteur;
                $noti[$i]['recepteur'] = $recepteur;
                $noti[$i]['notification'] = $notification;
                $i++;
            }
            $session->set('Notifications', $noti);
        }
    }

}
