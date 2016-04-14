<?php

namespace Aeag\EdlBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Aeag\EdlBundle\Entity\RisqueMeProposed;
use Aeag\EdlBundle\Entity\RisqueType;
use Aeag\EdlBundle\Entity\RisqueMe;
use Aeag\EdlBundle\Entity\RisqueMeRepository;
use Aeag\EdlBundle\Entity\MasseEau;
use Aeag\EdlBundle\Resources\php;

class DateTimeRisque extends \DateTime {

    public function __toString() {
        return $this->format('Y-m-d H:i:s');
    }

}

class RisqueController extends Controller {

    public function risqueFormAction() {
        $request = $this->getRequest();
        //if ($request->isXmlHttpRequest()) { // is it an Ajax request?
        $euCd = $request->get('euCd');
        $cdRisque = $request->get('cdRisque');

        $repo = $this->getDoctrine()
                ->getRepository('AeagEdlBundle:RisqueMe');
        $risqueInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdRisque' => $cdRisque));

        $proposed = new RisqueMeProposed();
        $proposed->setEuCd($euCd);
        $proposed->setCdRisque($cdRisque);

        $form = $this->createFormBuilder($proposed)
                ->add('euCd', 'hidden')
                ->add('cdRisque', 'hidden')
                ->add('valeur', 'hidden')
                ->add('commentaire', 'textarea')
                ->getForm();

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:RisqueMe');
        $derniereProp = $repo->getLastProposition($euCd, $cdRisque);

        $ua = php\fonctions::getBrowser();

        if ($ua['name'] == 'Internet Explorer' and $ua['version'] <= 8.0) {

            return $this->render('AeagEdlBundle:Risque:risqueFormIe8.html.twig', array(
                        'form' => $form->createView(),
                        'euCd' => $euCd,
                        'cdRisque' => $cdRisque,
                        'derniereProposition' => $derniereProp ? $derniereProp->getValeur() : $risqueInitiale->getValeur()
                    ));
        } else {

            return $this->render('AeagEdlBundle:Risque:risqueFormIe8.html.twig', array(
                        'form' => $form->createView(),
                        'euCd' => $euCd,
                        'cdRisque' => $cdRisque,
                        'derniereProposition' => $derniereProp ? $derniereProp->getValeur() : $risqueInitiale->getValeur()
                    ));
        }
        //}    
    }

    /**
     * Réception du formulaire, retour vers le navigateur au format json
     */
    public function risqueSubmitAction() {
        try {
            $em = $this->getDoctrine()->getEntityManager();

            // récupération des paramètres
            $request = $this->getRequest();
            $euCd = $request->get('euCd');
            $cdRisque = $request->get('cdRisque');
            $commentaire = $request->get('commentaire');
            $valeur = $request->get('valeur');



            // sauvegarde
            $proposed = new RisqueMeProposed();

            // @todo adapter login 
            $proposed->setEuCd($euCd);
            $proposed->setPropositionDate(new DateTimeRisque("now"));
            $proposed->setCdRisque($cdRisque);



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

            $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:RisqueMe');
            $risqueInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdRisque' => $cdRisque));
            $proposed->setRisqueOriginal($risqueInitiale);

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
                $msg = "Risque enregistrée... $commentaire";
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
     * Retourne le code HTML correspondant à une liste de risque proposées (pour un type donné)
     * 
     * mode Ajax
     */
    public function risqueListProposedAction() {
        $request = $this->getRequest();

        $euCd = $request->get('euCd');
        $cdRisque = $request->get('cdRisque');

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:RisqueMe');
        $risqueInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdRisque' => $cdRisque));

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:RisqueMe');
        $derniereProp = $repo->getLastProposition($euCd, $cdRisque);
        
        // recuperatiion de l'user connecté
        $user = $this->container->get('security.context')->getToken()->getUser();

        return $this->render('AeagEdlBundle:Risque:risqueListProposed.html.twig', array(
                    'risque' => $risqueInitiale,
                    'derniereProp' => $derniereProp,
                    'user' => $user
                ));
    }

    public function removeRisqueAction() {
        $request = $this->getRequest();

        $euCd = $request->get('euCd');
        $cdRisque = $request->get('cdRisque');
        $login = $request->get('login');
        $propositionDate = $request->get('propositionDate');

        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:RisqueMeProposed');

        $proposition = $repo->findOneBy(array('euCd' => $euCd, 'cdRisque' => $cdRisque, 'utilisateur' => $login, 'propositionDate' => $propositionDate));

        $em->remove($proposition);
        $em->flush();

        return $this->forward('AeagEdlBundle:Risque:risqueListProposed', array(
                    'euCd' => $euCd,
                    'cdRisque' => $cdRisque
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
