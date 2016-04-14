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

    public function etatFormAction() {
        $request = $this->getRequest();
        //if ($request->isXmlHttpRequest()) { // is it an Ajax request?
        $euCd = $request->get('euCd');
        $cdEtat = $request->get('cdEtat');

        $repo = $this->getDoctrine()
                ->getRepository('AeagEdlBundle:EtatMe');
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

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:EtatMe');
        $derniereProp = $repo->getLastProposition($euCd, $cdEtat);

        $ua = php\fonctions::getBrowser();
      
        if ($ua['name'] == 'Internet Explorer' and $ua['version'] <= 8.0) {

            return $this->render('AeagEdlBundle:Etat:etatFormIe8.html.twig', array(
                        'form' => $form->createView(),
                        'euCd' => $euCd,
                        'cdEtat' => $cdEtat,
                        'derniereProposition' => $derniereProp ? $derniereProp->getValeur() : $etatInitiale->getValeur()
                    ));
        } else {


            return $this->render('AeagEdlBundle:Etat:etatFormIe8.html.twig', array(
                        'form' => $form->createView(),
                        'euCd' => $euCd,
                        'cdEtat' => $cdEtat,
                        'derniereProposition' => $derniereProp ? $derniereProp->getValeur() : $etatInitiale->getValeur()
                    ));
        };
        //}    
    }

 
    /**
     * Réception du formulaire, retour vers le navigateur au format json
     */
    public function etatSubmitAction() {

        // récupération des paramètres
        $request = $this->getRequest();
        $euCd = $request->get('euCd');
        $cdEtat = $request->get('cdEtat');
        $commentaire = $request->get('commentaire');
        $valeur = $request->get('valeur');


        try {
            $em = $this->getDoctrine()->getEntityManager();

            //return new Response('eucd : ' . $euCd . ' etat : ' . $cdEtat . ' commentaire : ' . $commentaire . ' valeur : ' . $valeur  );
            // sauvegarde
            $proposed = new EtatMeProposed();

            // @todo adapter login 
            $proposed->setEuCd($euCd);
            $proposed->setPropositionDate(new DateTimeEtat("now"));
            $proposed->setCdEtat($cdEtat);



            // recuperatiion de l'user connecté
            $user = $this->container->get('security.context')->getToken()->getUser();

            // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
            if (!is_object($user)) {
                throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
            }


            $proposed->setUtilisateur($user);
            $proposed->setValeur($valeur);
            $proposed->setCommentaire($commentaire);

            if ($this->get('security.context')->isGranted('ROLE_SUPERVISEUR')) {
                $proposed->setRole('expert');
            } else {
                $proposed->setRole('local');
            };


            $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:EtatMe');
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
                $em->persist($proposed);
                $em->flush();
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
    public function etatListProposedAction() {
        
        $user = $this->getUser();
        $session = $this->get('session');
        $session->clear();
        $session->set('controller', 'default');
        $session->set('fonction', 'etat');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');
        
        $request = $this->getRequest();

        $euCd = $request->get('euCd');
        $cdEtat = $request->get('cdEtat');

        $repo = $emEdl->getRepository('AeagEdlBundle:EtatMe');
        $etatInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdEtat' => $cdEtat));


        $repo = $emEdl->getRepository('AeagEdlBundle:EtatMe');
        $derniereProp = $repo->getLastProposition($euCd, $cdEtat);
        
       
        return $this->render('AeagEdlBundle:Etat:etatListProposed.html.twig', array(
                    'etat' => $etatInitiale,
                    'derniereProp' => $derniereProp,
                    'user' => $user
                ));
    }

    public function removeEtatAction() {
        $request = $this->getRequest();

        $euCd = $request->get('euCd');
        $cdEtat = $request->get('cdEtat');
        $login = $request->get('login');
        $propositionDate = $request->get('propositionDate');

        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:EtatMeProposed');

        $proposition = $repo->findOneBy(array('euCd' => $euCd, 'cdEtat' => $cdEtat, 'utilisateur' => $login, 'propositionDate' => $propositionDate));

        $em->remove($proposition);
        $em->flush();

        return $this->forward('AeagEdlBundle:Etat:etatListProposed', array(
                    'euCd' => $euCd,
                    'cdEtat' => $cdEtat
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

