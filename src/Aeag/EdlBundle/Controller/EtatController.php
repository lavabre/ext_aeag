<?php

namespace Aeag\EdlBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Aeag\EdlBundle\Entity\EtatMeProposed;

class DateTimeEtat extends \DateTime {

    public function __toString() {
        return $this->format('Y-m-d H:i:s');
    }

}

class EtatController extends Controller {

    public function etatFormAction(Request $request) {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('controller', 'Etat');
        $session->set('fonction', 'etatForm');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');
        //if ($request->isXmlHttpRequest()) { // is it an Ajax request?
        $euCd = $request->get('euCd');
        $cdEtat = $request->get('cdEtat');
        $cdGroupe = $request->get('cdGroupe');

        $repoEtatDerniereProposition = $emEdl->getRepository('AeagEdlBundle:EtatDerniereProposition');
        $repo = $emEdl->getRepository('AeagEdlBundle:EtatMe');
        $etatInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdEtat' => $cdEtat));

        $proposed = new EtatMeProposed();
        $proposed->setEuCd($euCd);
        $proposed->setCdEtat($cdEtat);

        $form = $this->createFormBuilder($proposed)
                ->add('euCd', 'hidden')
                ->add('cdEtat', 'hidden')
                ->add('valeur', 'hidden')
                ->add('commentaire', 'textarea', array('required' => true))
                ->getForm();

        $derniereProps = $repoEtatDerniereProposition->getDernierePropositionByEucdCdEtat($euCd, $cdEtat);

        if ($derniereProps) {
            $derniereProp = $derniereProps[0];
        } else {
            $derniereProp = null;
        }


        return $this->render('AeagEdlBundle:Etat:etatForm.html.twig', array(
                    'form' => $form->createView(),
                    'cdGroupe' => $cdGroupe,
                    'euCd' => $euCd,
                    'cdEtat' => $cdEtat,
                    'derniereProposition' => $derniereProp ? $derniereProp->getValeur() : $etatInitiale->getValeur()
        ));
        //}    
    }

    /**
     * Réception du formulaire, retour vers le navigateur au format json
     */
    public function etatSubmitAction(Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagEdlBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('controller', 'Etat');
        $session->set('fonction', 'etatSubmit');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');
        $repoUtilisateur = $emEdl->getRepository('AeagEdlBundle:Utilisateur');
        $repoEtatDerniereProposition = $emEdl->getRepository('AeagEdlBundle:EtatDerniereProposition');

        $utilisateur = $repoUtilisateur->getUtilisateurByExtid($user->getId());

        // récupération des paramètres
        $cdGroupe = $request->get('cdGroupe');
        $euCd = $request->get('euCd');
        $cdEtat = $request->get('cdEtat');
        $commentaire = $request->get('commentaire');
        $valeur = $request->get('valeur');


        try {

            //return new Response('eucd : ' . $euCd . ' etat : ' . $cdEtat . ' commentaire : ' . $commentaire . ' valeur : ' . $valeur  );
            // sauvegarde
            $proposed = new EtatMeProposed();

            // @todo adapter login 
            $proposed->setEuCd($euCd);
            $proposed->setPropositionDate(new DateTimeEtat("now"));
            $proposed->setCdEtat($cdEtat);


            $proposed->setUtilisateur($utilisateur);
            $proposed->setValeur($valeur);
            $proposed->setCommentaire($commentaire);

            if ($this->get('security.context')->isGranted('ROLE_SUPERVISEUREDL')) {
                $proposed->setRole('expert');
            } else {
                $proposed->setRole('local');
            };


            $repo = $emEdl->getRepository('AeagEdlBundle:EtatMe');
            $etatInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdEtat' => $cdEtat));
            $proposed->setEtatOriginal($etatInitiale);
            $emEdl->persist($proposed);
            $emEdl->flush();

            $derniereProps = $repoEtatDerniereProposition->getDernierePropositionByEucdCdEtat($euCd, $cdEtat);

            if ($derniereProps) {
                $derniereProp = $derniereProps[0];
                $msg = "Proposition :<span class=dce_etat_" . $derniereProp->getValeur() . ">" . $derniereProp->getValueLib();
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

    /**
     * Retourne le code HTML correspondant à une liste de etat proposés (pour un type donné)
     * 
     * mode Ajax
     */
    public function etatListProposedAction(Request $request) {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('controller', 'Etat');
        $session->set('fonction', 'etatListProposed');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

        $cdGroupe = $request->get('cdGroupe');
        $euCd = $request->get('euCd');
        $cdEtat = $request->get('cdEtat');

        $repoEtatDerniereProposition = $emEdl->getRepository('AeagEdlBundle:EtatDerniereProposition');

        $repo = $emEdl->getRepository('AeagEdlBundle:EtatMe');
        $etatInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdEtat' => $cdEtat));
        $tabProposeds = array();
        if ($etatInitiale) {
            $proposeds = $etatInitiale->getProposed();

            $k = 0;
            foreach ($proposeds as $proposed) {
                $tabProposeds[$k] = $proposed;
                $k++;
            }
            if (count($tabProposeds) > 0) {
                usort($tabProposeds, create_function('$a,$b', 'return strcasecmp($a->getPropositionDate(),$b->getPropositionDate());'));
            }
        }

        // return new Response ('$masseEau : ' . $euCd. '  $etatType : ' . $cdEtat);
        $derniereProps = $repoEtatDerniereProposition->getDernierePropositionByEucdCdEtat($euCd, $cdEtat);

        if (!$derniereProps) {
            $derniereProposition = null;
        } else {
            $derniereProposition = $derniereProps[0];
        }

        return $this->render('AeagEdlBundle:Etat:etatListProposed.html.twig', array(
                    'cdGroupe' => $cdGroupe,
                    'etat' => $etatInitiale,
                    'proposeds' => $tabProposeds,
                    'derniereProp' => $derniereProposition,
                    'user' => $user,
        ));
    }

    public function removeEtatAction(Request $request) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagEdlBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('controller', 'Etat');
        $session->set('fonction', 'removeEtat');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');


        $cdGroupe = $request->get('cdGroupe');
        $euCd = $request->get('euCd');
        $cdEtat = $request->get('cdEtat');
        $login = $request->get('login');
        $propositionDate = $request->get('propositionDate');

        $repoEtatDerniereProposition = $emEdl->getRepository('AeagEdlBundle:EtatDerniereProposition');
        $repo = $emEdl->getRepository('AeagEdlBundle:EtatMeProposed');
        $proposition = $repo->findOneBy(array('euCd' => $euCd, 'cdEtat' => $cdEtat, 'utilisateur' => $login, 'propositionDate' => $propositionDate));

        $emEdl->remove($proposition);
        $emEdl->flush();

        $derniereProps = $repoEtatDerniereProposition->getDernierePropositionByEucdCdEtat($euCd, $cdEtat);

        if ($derniereProps) {
            $derniereProp = $derniereProps[0];
            $msg = "Proposition :<span class='dce_etat_" . $derniereProp->getValeur() . "'>" . $derniereProp->getValueLib();
        } else {
            $derniereProp = null;
            $msg = "Proposition :";
        }
        return new Response($msg);
    }

}
