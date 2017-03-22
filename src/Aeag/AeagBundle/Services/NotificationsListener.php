<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Aeag\AeagBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Description of NotificationListener
 *
 * @author lavabre
 */
class NotificationsListener {

    protected $container;
    private $security;
    private $doctrine;
    private $session;

    public function __construct(Container $container, $security, $doctrine, $session) {

        $this->container = $container;
        $this->security = $security;
        if ($this->security->getToken()) {
            $this->user = $this->security->getToken()->getUser();
        } else {
            $this->user = null;
        }
        $this->em = $doctrine->getManager();
        $this->session = $session;
    }

    public function processNotifications(FilterResponseEvent $event) {


        // On teste si la requête est bien la requête principale (et non une sous-requête)
        if (!$event->isMasterRequest()) {
            return;
        }

        $recepteur = $this->user;
        if (!is_object($recepteur)) {
            return;
        }

        // On récupère la réponse que le gestionnaire a insérée dans l'évènement
        $response = $event->getResponse();

        // Ici on appelera la méthode
        $container = $this->container;
        $em = $this->em;
        $session = $this->session;
        $notifications = $container->get('aeag.notifications');
        $notifications->envoiNotification($recepteur, $em, $session);

        // Puis on insère la réponse modifiée dans l'évènement
        $event->setResponse($response);
    }

}
