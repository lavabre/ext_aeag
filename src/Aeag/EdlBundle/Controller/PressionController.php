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

        $repo = $emEdl->getRepository('AeagEdlBundle:PressionMe');
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

        $repo = $emEdl->getRepository('AeagEdlBundle:PressionMe');
        $derniereProp = $repo->getLastPropositionSuperviseur($euCd, $cdPression);
        if (!$derniereProp) {
            $derniereProp = $repo->getLastProposition($euCd, $cdPression);
        }
          if (!$derniereProp) {
            $derniereProposition = null;
        } else {
            $derniereProposition = $derniereProp[0];
        }

        return $this->render('AeagEdlBundle:Pression:pressionForm.html.twig', array(
                    'form' => $form->createView(),
                    'euCd' => $euCd,
                    'cdPression' => $cdPression,
                    'derniereProposition' => $derniereProposition ? $derniereProposition->getValeur() : $pressionInitiale->getValeur()
        ));
        //}    
    }

    /**
     * Réception du formulaire, retour vers le navigateur au format json
     */
    public function pressionSubmitAction(Request $request) {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('controller', 'Pression');
        $session->set('fonction', 'pressionListProposed');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');
        $repoUtilisateur = $emEdl->getRepository('AeagEdlBundle:Utilisateur');
        $utilisateur = $repoUtilisateur->getUtilisateurByExtid($user->getId());

        try {
            // récupération des paramètres
            $euCd = $request->get('euCd');
            $cdPression = $request->get('cdPression');
            $commentaire = $request->get('commentaire');
            $valeur = $request->get('valeur');

            // sauvegarde
            $proposed = new PressionMeProposed();

            // @todo adapter login 
            $proposed->setEuCd($euCd);
            $proposed->setPropositionDate(new DateTimePression("now"));
            $proposed->setCdPression($cdPression);

            // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
            if (!is_object($user)) {
                throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
            }


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

            $validator = $this->container->get('validator');
            $errorList = $validator->validate($proposed);
            // var_dump($errorList);            
            $msg = "";
            if (count($errorList) > 0) {
                foreach ($errorList as $err) {
                    $msg .= $err->getMessage() . "\n";
                }
            } else {
                $emEdl->persist($proposed);
                $emEdl->flush();
                $msg = "Pression enregistrée... $commentaire";
            }

            // retour vers le navigateur
            $response = new Response(json_encode(array('message' => $msg)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } catch (Exception $e) {
            $response = new Response(json_encode(array('message' => $e->getMessage())));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    /**
     * Retourne le code HTML correspondant à une liste de pression proposées (pour un type donné)
     * 
     * mode Ajax
     */
    public function pressionListProposedAction(Request $request) {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('controller', 'Pression');
        $session->set('fonction', 'pressionListProposed');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

        $euCd = $request->get('euCd');
        $cdPression = $request->get('cdPression');

        $repo = $emEdl->getRepository('AeagEdlBundle:PressionMe');
        $pressionInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdPression' => $cdPression));

        $derniereProp = $repo->getLastPropositionSuperviseur($euCd, $cdPression);

        if (!$derniereProp) {
            $derniereProp = $repo->getLastProposition($euCd, $cdPression);
        }

        if (!$derniereProp) {
            $derniereProposition = null;
        } else {
            $derniereProposition = $derniereProp[0];
        }

        return $this->render('AeagEdlBundle:Pression:pressionListProposed.html.twig', array(
                    'pression' => $pressionInitiale,
                    'derniereProp' => $derniereProposition,
                    'user' => $user
        ));
    }

    public function removePressionAction(Request $request) {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('controller', 'Pression');
        $session->set('fonction', 'pressionListProposed');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

        $euCd = $request->get('euCd');
        $cdPression = $request->get('cdPression');
        $login = $request->get('login');
        $propositionDate = $request->get('propositionDate');
        
        //return new Response ('eucd : ' . $euCd . '  cdPression : ' . $cdPression . '  login : ' . $login . ' date : ' . $propositionDate);

        $repo = $emEdl->getRepository('AeagEdlBundle:PressionMeProposed');

        $proposition = $repo->findOneBy(array('euCd' => $euCd, 'cdPression' => $cdPression, 'utilisateur' => $login, 'propositionDate' => $propositionDate));

        $emEdl->remove($proposition);
        $emEdl->flush();

        return $this->forward('AeagEdlBundle:Pression:pressionListProposed', array(
                    'euCd' => $euCd,
                    'cdPression' => $cdPression
        ));
    }

    /* Exemple de gestion ajax    
      public function validateEmailAction(){
      # Is the request an ajax one?
      if ($this->get('request')->isXmlHttpRequest())
      {
      # Lets get the email parameter's value
      $email = $this->get('request')->request->get('email');
      #if the email is correct
      if(....){
      return new Response("<b>The email is valid</b>");
      }#endif
      #else if the email is incorrect
      else
      {
      return new Response("<b>We are sorry, the email is already
      taken</b>");
      }#endelse
      }# endif this is an ajax request
      } #end of the controller.
     */
}
