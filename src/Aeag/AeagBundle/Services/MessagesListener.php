<?php

namespace Aeag\AeagBundle\Services;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Description of MessageListener
 *
 * @author lavabre
 */
class MessagesListener {

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

    public function processMessages(FilterResponseEvent $event) {


        // On teste si la requête est bien la requête principale (et non une sous-requête)
        if (!$event->isMasterRequest()) {
            return;
        }

        $recepteur = $this->user;
        $emetteur = $this->user;
        if (!is_object($recepteur)) {
            return;
        }

        // On récupère la réponse que le gestionnaire a insérée dans l'évènement
        $response = $event->getResponse();

        // Ici on appelera la méthode
        $container = $this->container;
        $em = $this->em;
        $session = $this->session;
        $messages = $container->get('aeag.messages');
        //$messages->createMessage($emetteur, $recepteur, $em, $session, 'Mes1');
        $messages->envoiMessage($recepteur, $em, $session);

        // Puis on insère la réponse modifiée dans l'évènement
        $event->setResponse($response);
    }

}
