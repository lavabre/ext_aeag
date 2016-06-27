<?php

namespace Aeag\AgentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\AgentBundle\Form\Criteres\CriteresEtatType;
use Aeag\AgentBundle\Entity\Form\CriteresEtat;

class DefaultController extends Controller {

    public function indexAction() {

        $session = $this->get('session');
        $session->set('menu', 'agent');
        $session->set('controller', 'Default');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emIfc = $this->getDoctrine()->getManager('ifc');

        return $this->render('AeagAgentBundle:Default:index.html.twig');
    }

    public function listeAgentsAction() {

        $session = $this->get('session');
        $session->set('menu', 'agent');
        $session->set('controller', 'Default');
        $session->set('fonction', 'listeAgents');
        $em = $this->get('doctrine')->getManager();
        $emIfc = $this->getDoctrine()->getManager('ifc');

        $session->set('retourErreur', $this->generateUrl('aeag_agent'));
        $repoAgent = $emIfc->getRepository('AeagAgentBundle:Agent');
        $agents = $repoAgent->getAgents();
        return $this->render('AeagAgentBundle:Default:listeAgents.html.twig', array('agents' => $agents));
    }

    public function RechercheEtatAction() {

        $session = $this->get('session');
        $session->set('menu', 'agent');
        $session->set('controller', 'Default');
        $session->set('fonction', 'RechercheEtat');
        $em = $this->get('doctrine')->getManager();
        $emIfc = $this->getDoctrine()->getManager('ifc');

        $criteresEtat = new CriteresEtat();

        if (($session->has('critNomPrenom'))) {
            $session->remove('critNomPrenom');
        }

        $form = $this->createForm(new CriteresEtatType(), $criteresEtat);

        return $this->render('AeagAgentBundle:Default:rechercheEtat.html.twig', array(
                    'form' => $form->createView(),));
    }

    public function ResultatEtatAction() {

        $session = $this->get('session');
        $session->set('menu', 'agent');
        $session->set('controller', 'Default');
        $session->set('fonction', 'ResultatEtat');
        $em = $this->get('doctrine')->getManager();
        $emIfc = $this->getDoctrine()->getManager('ifc');

        $repoAgent = $emIfc->getRepository('AeagAgentBundle:Agent');
        $request = $this->container->get('request');
        $critNomPrenom = $request->get('nomPrenom');
        $tabAgents = array();
        $exec = $repoAgent->getRechercheEtatAgent(strtoupper($critNomPrenom));
        $tabAgents = $repoAgent->getAgentResultats();

        return $this->render('AeagAgentBundle:Default:ResultatEtat.html.twig', array(
                    'tabAgents' => $tabAgents));
    }

    public function ResultatServiceEtatAction($service = null) {

        $session = $this->get('session');
        $session->set('menu', 'agent');
        $session->set('controller', 'Default');
        $session->set('fonction', 'ResultatServiceEtat');
        $em = $this->get('doctrine')->getManager();
        $emIfc = $this->getDoctrine()->getManager('ifc');

        $repoAgent = $emIfc->getRepository('AeagAgentBundle:Agent');
        $tabAgents = array();
        $exec = $repoAgent->getRechercheEtatService(strtoupper($service));
        $tabAgents = $repoAgent->getServiceResultats();

        return $this->render('AeagAgentBundle:Default:ResultatServiceEtat.html.twig', array(
                    'service' => $service,
                    'tabAgents' => $tabAgents));
    }

}
