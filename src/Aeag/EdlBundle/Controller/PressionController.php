<?php

namespace Aeag\EdlBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Aeag\EdlBundle\Entity\PressionMeProposed;
use Aeag\EdlBundle\Entity\PressionType;
use Aeag\EdlBundle\Entity\PressionMe;
use Aeag\EdlBundle\Entity\PressionMeRepository;
use Aeag\EdlBundle\Entity\MasseEau;
use Aeag\EdlBundle\Resources\php;

class DateTimePression extends \DateTime {

    public function __toString() {
        return $this->format('Y-m-d H:i:s');
    }

}

class PressionController extends Controller {

    public function pressionFormAction() {
        $request = $this->getRequest();
        //if ($request->isXmlHttpRequest()) { // is it an Ajax request?
        $euCd = $request->get('euCd');
        $cdPression = $request->get('cdPression');

        $repo = $this->getDoctrine()
                ->getRepository('AeagEdlBundle:PressionMe');
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

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:PressionMe');
        $derniereProp = $repo->getLastPropositionSuperviseur($euCd, $cdPression);
        if (!is_object($derniereProp)) {
            $derniereProp = $repo->getLastProposition($euCd, $cdPression);
        }

        $ua = php\fonctions::getBrowser();

        if ($ua['name'] == 'Internet Explorer' and $ua['version'] <= 8.0) {

            return $this->render('AeagEdlBundle:Pression:pressionFormIe8.html.twig', array(
                        'form' => $form->createView(),
                        'euCd' => $euCd,
                        'cdPression' => $cdPression,
                        'derniereProposition' => $derniereProp ? $derniereProp->getValeur() : $pressionInitiale->getValeur()
                    ));
        } else {

            return $this->render('AeagEdlBundle:Pression:pressionFormIe8.html.twig', array(
                        'form' => $form->createView(),
                        'euCd' => $euCd,
                        'cdPression' => $cdPression,
                        'derniereProposition' => $derniereProp ? $derniereProp->getValeur() : $pressionInitiale->getValeur()
                    ));
        }
        //}    
    }

    /**
     * Réception du formulaire, retour vers le navigateur au format json
     */
    public function pressionSubmitAction() {
        try {
            $em = $this->getDoctrine()->getEntityManager();

            // récupération des paramètres
            $request = $this->getRequest();
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

            $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:PressionMe');
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
                $em->persist($proposed);
                $em->flush();
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
    public function pressionListProposedAction() {
        $request = $this->getRequest();

        $euCd = $request->get('euCd');
        $cdPression = $request->get('cdPression');

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:PressionMe');
        $pressionInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdPression' => $cdPression));

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:PressionMe');
        $derniereProp = $repo->getLastPropositionSuperviseur($euCd, $cdPression);
        if (!is_object($derniereProp)) {
            $derniereProp = $repo->getLastProposition($euCd, $cdPression);
        }
        
        // recuperatiion de l'user connecté
        $user = $this->container->get('security.context')->getToken()->getUser();

        return $this->render('AeagEdlBundle:Pression:pressionListProposed.html.twig', array(
                    'pression' => $pressionInitiale,
                    'derniereProp' => $derniereProp,
                    'user' => $user
                ));
    }

    public function removePressionAction() {
        $request = $this->getRequest();

        $euCd = $request->get('euCd');
        $cdPression = $request->get('cdPression');
        $login = $request->get('login');
        $propositionDate = $request->get('propositionDate');

        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:PressionMeProposed');

        $proposition = $repo->findOneBy(array('euCd' => $euCd, 'cdPression' => $cdPression, 'utilisateur' => $login, 'propositionDate' => $propositionDate));

        $em->remove($proposition);
        $em->flush();

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
