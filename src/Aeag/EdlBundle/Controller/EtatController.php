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
        $session->clear();
        $session->set('controller', 'Etat');
        $session->set('fonction', 'etatForm');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');
        //if ($request->isXmlHttpRequest()) { // is it an Ajax request?
        $euCd = $request->get('euCd');
        $cdEtat = $request->get('cdEtat');
            
        $repo = $emEdl->getRepository('AeagEdlBundle:EtatMe');
        $etatInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdEtat' => $cdEtat));

        $proposed = new EtatMeProposed();
        $proposed->setEuCd($euCd);
        $proposed->setCdEtat($cdEtat);

        $form = $this->createFormBuilder($proposed)
                ->add('euCd', 'hidden')
                ->add('cdEtat', 'hidden')
                ->add('valeur', 'hidden')
                ->add('commentaire', 'textarea')
                ->getForm();

        $repo = $emEdl->getRepository('AeagEdlBundle:EtatMe');
        $derniereProp = $repo->getLastProposition($euCd, $cdEtat);

       
            return $this->render('AeagEdlBundle:Etat:etatForm.html.twig', array(
                        'form' => $form->createView(),
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
        $session = $this->get('session');
        $session->clear();
        $session->set('controller', 'Etat');
        $session->set('fonction', 'etatForm');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');
        $repoUtilisateur = $emEdl->getRepository('AeagEdlBundle:Utilisateur');
        $utilisateur = $repoUtilisateur->getUtilisateurByExtid($user->getId());
        
        // récupération des paramètres
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

            // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
            if (!is_object($user)) {
                throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
            }


            $proposed->setUtilisateur( $utilisateur);
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

            $validator = $this->container->get('validator');
            $errorList = $validator->validate($proposed);
            //\var_dump($errorList);            
            $msg = "";
            if (count($errorList) > 0) {
                foreach ($errorList as $err) {
                    $msg .= $err->getMessage() . "\n";
                }
            } else {
                $emEdl->persist($proposed);
                $emEdl->flush();
                $msg = "Etat enregistrée... $commentaire";
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
     * Retourne le code HTML correspondant à une liste de etat proposées (pour un type donné)
     * 
     * mode Ajax
     */
    
    
    public function etatListProposedAction(Request $request) {
        
        $user = $this->getUser();
        $session = $this->get('session');
        $session->clear();
        $session->set('controller', 'Etat');
        $session->set('fonction', 'etatListProposed');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');
          
        $euCd = $request->get('euCd');
        $cdEtat = $request->get('cdEtat');
      
    
        $repo = $emEdl->getRepository('AeagEdlBundle:EtatMe');
        $etatInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdEtat' => $cdEtat));
     
       // return new Response ('$masseEau : ' . $euCd. '  $etatType : ' . $cdEtat);
       $derniereProp = $repo->getLastPropositionSuperviseur($euCd, $cdEtat);
       
        if (!$derniereProp) {
            $derniereProp = $repo->getLastProposition($euCd, $cdEtat);
        }
        
          if (!$derniereProp) {
            $derniereProposition = null;
        } else {
            $derniereProposition = $derniereProp[0];
        }
          
        return $this->render('AeagEdlBundle:Etat:etatListProposed.html.twig', array(
                    'etat' => $etatInitiale,
                    'derniereProp' => $derniereProposition,
                    'user' => $user,
                ));
    }


    public function removeEtatAction(Request $request) {
        $user = $this->getUser();
        $session = $this->get('session');
        $session->clear();
        $session->set('controller', 'Etat');
        $session->set('fonction', 'etatListProposed');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');
        $repoUtilisateur = $emEdl->getRepository('AeagEdlBundle:Utilisateur');
        $utilisateur = $repoUtilisateur->getUtilisateurByExtid($user->getId());

        $euCd = $request->get('euCd');
        $cdEtat = $request->get('cdEtat');
        $login = $request->get('login');
        $propositionDate = $request->get('propositionDate');

        $repo = $emEdl->getRepository('AeagEdlBundle:EtatMeProposed');

        $proposition = $repo->findOneBy(array('euCd' => $euCd, 'cdEtat' => $cdEtat, 'utilisateur' => $login, 'propositionDate' => $propositionDate));

        $emEdl->remove($proposition);
        $emEdl->flush();

        return $this->forward('AeagEdlBundle:Etat:etatListProposed', array(
                    'euCd' => $euCd,
                    'cdEtat' => $cdEtat,
                    'delete' =>  "O"
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

