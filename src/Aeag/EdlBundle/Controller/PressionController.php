<?php

namespace Aeag\EdlBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Aeag\EdlBundle\Entity\PressionMeProposed;

class DateTimePression extends \DateTime {

    public function __toString() {
        return $this->format('Y-m-d H:i:s');
    }

}

class PressionController extends Controller {

    public function pressionFormAction(Request $request) {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('controller', 'Pression');
        $session->set('fonction', 'pressionListProposed');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');
        //if ($request->isXmlHttpRequest()) { // is it an Ajax request?
        $euCd = $request->get('euCd');
        $cdPression = $request->get('cdPression');
        $cdGroupe = $request->get('cdGroupe');

        $repo = $emEdl->getRepository('AeagEdlBundle:PressionMe');
        $repoPressionDerniereProposition = $emEdl->getRepository('AeagEdlBundle:PressionDerniereProposition');
        
        $pressionInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdPression' => $cdPression));

        $proposed = new PressionMeProposed();
        $proposed->setEuCd($euCd);
        $proposed->setCdPression($cdPression);

        $form = $this->createFormBuilder($proposed)
                ->add('euCd', 'hidden')
                ->add('cdPression', 'hidden')
                ->add('valeur', 'hidden')
                ->add('commentaire', 'textarea')
                ->getForm();

        $derniereProp = $repoPressionDerniereProposition->getDernierePropositionByEucdCdPression($euCd, $cdPression);
      
        if (!$derniereProp) {
            $derniereProposition = null;
        } else {
            $derniereProposition = $derniereProp[0];
        }

        return $this->render('AeagEdlBundle:Pression:pressionForm.html.twig', array(
                    'form' => $form->createView(),
                    'cdGroupe' => $cdGroupe,
                    'euCd' => $euCd,
                    'cdPression' => $cdPression,
                    'derniereProposition' => $derniereProposition ? $derniereProposition->getValeur() : $pressionInitiale->getValeur()
        ));
        //}    
    }

    public function pressionSubmitAction(Request $request) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagEdlBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('controller', 'Pression');
        $session->set('fonction', 'pressionListProposed');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');
        $repoUtilisateur = $emEdl->getRepository('AeagEdlBundle:Utilisateur');
        $repoPressionDerniereProposition = $emEdl->getRepository('AeagEdlBundle:PressionDerniereProposition');
        
        $utilisateur = $repoUtilisateur->getUtilisateurByExtid($user->getId());

        try {
            // récupération des paramètres
            $euCd = $request->get('euCd');
            $cdGroupe = $request->get('cdGroupe');
            $cdPression = $request->get('cdPression');
            $commentaire = $request->get('commentaire');
            $valeur = $request->get('valeur');

            // sauvegarde
            $proposed = new PressionMeProposed();

            // @todo adapter login 
            $proposed->setEuCd($euCd);
            $proposed->setPropositionDate(new DateTimePression("now"));
            $proposed->setCdPression($cdPression);



            $proposed->setUtilisateur($utilisateur);
            $proposed->setValeur($valeur);
            $proposed->setCommentaire($commentaire);

            if ($this->get('security.context')->isGranted('ROLE_SUPERVISEUREDL')) {
                $proposed->setRole('expert');
            } else {
                $proposed->setRole('local');
            };

            $repo = $emEdl->getRepository('AeagEdlBundle:PressionMe');
            $pressionInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdPression' => $cdPression));
            $proposed->setPressionOriginale($pressionInitiale);
            $emEdl->persist($proposed);
            $emEdl->flush();
            
           $derniereProps = $repoPressionDerniereProposition->getDernierePropositionByEucdCdPression($euCd, $cdPression);

        if ($derniereProps) {
            $derniereProp = $derniereProps[0];
            $msg = "Proposition :<span class='dce_pression_" . $derniereProp->getValeur() . "'>" . $derniereProp->getValueLib() ;
        } else {
            $derniereProp = null;
             $msg = "Proposition :";
        }
         return new Response(json_encode($msg));
        } catch (Exception $e) {
            $response = new Response(json_encode(array('message' => $e->getMessage())));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    public function pressionListProposedAction(Request $request) {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('controller', 'Pression');
        $session->set('fonction', 'pressionListProposed');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

        $cdGroupe = $request->get('cdGroupe');
        $euCd = $request->get('euCd');
        $cdPression = $request->get('cdPression');

        $repo = $emEdl->getRepository('AeagEdlBundle:PressionMe');
        $repoPressionDerniereProposition = $emEdl->getRepository('AeagEdlBundle:PressionDerniereProposition');
        
        $pressionInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdPression' => $cdPression));

        $proposeds = $pressionInitiale->getProposed();
        $tabProposeds = array();
        $k = 0;
        foreach ($proposeds as $proposed) {
            $tabProposeds[$k] = $proposed;
            $k++;
        }
        if (count($tabProposeds) > 0) {
            usort($tabProposeds, create_function('$a,$b', 'return strcasecmp($a->getPropositionDate(),$b->getPropositionDate());'));
        }

        $derniereProp = $repoPressionDerniereProposition->getDernierePropositionByEucdCdPression($euCd, $cdPression);

        if (!$derniereProp) {
            $derniereProposition = null;
        } else {
            $derniereProposition = $derniereProp[0];
        }

        return $this->render('AeagEdlBundle:Pression:pressionListProposed.html.twig', array(
                    'cdGroupe' => $cdGroupe,
                    'pression' => $pressionInitiale,
                    'proposeds' => $tabProposeds,
                    'derniereProp' => $derniereProposition,
                    'user' => $user
        ));
    }

    public function removePressionAction(Request $request) {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('controller', 'Pression');
        $session->set('fonction', 'removePression');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

        $cdGroupe = $request->get('cdGroupe');
        $euCd = $request->get('euCd');
        $cdPression = $request->get('cdPression');
        $login = $request->get('login');
        $propositionDate = $request->get('propositionDate');

        //return new Response ('eucd : ' . $euCd . '  cdPression : ' . $cdPression . '  login : ' . $login . ' date : ' . $propositionDate);

        $repo = $emEdl->getRepository('AeagEdlBundle:PressionMeProposed');
        $repoPressionDerniereProposition = $emEdl->getRepository('AeagEdlBundle:PressionDerniereProposition');

        $proposition = $repo->findOneBy(array('euCd' => $euCd, 'cdPression' => $cdPression, 'utilisateur' => $login, 'propositionDate' => $propositionDate));

        $emEdl->remove($proposition);
        $emEdl->flush();
        
          $derniereProps = $repoPressionDerniereProposition->getDernierePropositionByEucdCdPression($euCd, $cdPression);

        if ($derniereProps) {
            $derniereProp = $derniereProps[0];
            $msg = "Proposition :<span class='dce_pression_" . $derniereProp->getValeur() . "'>" . $derniereProp->getValueLib() ;
        } else {
            $derniereProp = null;
             $msg = "Proposition :";
        }
         return new Response($msg);

     
    }

}
