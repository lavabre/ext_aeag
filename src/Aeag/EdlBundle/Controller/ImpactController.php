<?php

namespace Aeag\EdlBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Aeag\EdlBundle\Entity\ImpactMeProposed;
use Aeag\EdlBundle\Entity\ImpactType;
use Aeag\EdlBundle\Entity\ImpactMe;
use Aeag\EdlBundle\Entity\ImpactMeRepository;
use Aeag\EdlBundle\Entity\MasseEau;
use Aeag\EdlBundle\Resources\php;

class DateTimeImpact extends \DateTime {

    public function __toString() {
        return $this->format('Y-m-d H:i:s');
    }

}

class ImpactController extends Controller {

    public function impactFormAction() {
        $request = $this->getRequest();
        //if ($request->isXmlHttpRequest()) { // is it an Ajax request?
        $euCd = $request->get('euCd');
        $cdImpact = $request->get('cdImpact');

        $repo = $this->getDoctrine()
                ->getRepository('AeagEdlBundle:ImpactMe');
        $impactInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdImpact' => $cdImpact));

        $proposed = new ImpactMeProposed();
        $proposed->setEuCd($euCd);
        $proposed->setCdImpact($cdImpact);

        $form = $this->createFormBuilder($proposed)
                ->add('euCd', 'hidden')
                ->add('cdImpact', 'hidden')
                ->add('valeur', 'hidden')
                ->add('commentaire', 'textarea')
                ->getForm();

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:ImpactMe');
        $derniereProp = $repo->getLastProposition($euCd, $cdImpact);

        $ua = php\fonctions::getBrowser();

        if ($ua['name'] == 'Internet Explorer' and $ua['version'] <= 8.0) {

            return $this->render('AeagEdlBundle:Impact:impactFormIe8.html.twig', array(
                        'form' => $form->createView(),
                        'euCd' => $euCd,
                        'cdImpact' => $cdImpact,
                        'derniereProposition' => $derniereProp ? $derniereProp->getValeur() : $impactInitiale->getValeur()
                    ));
        } else {

            return $this->render('AeagEdlBundle:Impact:impactFormIe8.html.twig', array(
                        'form' => $form->createView(),
                        'euCd' => $euCd,
                        'cdImpact' => $cdImpact,
                        'derniereProposition' => $derniereProp ? $derniereProp->getValeur() : $impactInitiale->getValeur()
                    ));
        }
        //}    
    }

    /**
     * Réception du formulaire, retour vers le navigateur au format json
     */
    public function impactSubmitAction() {
        try {
            $em = $this->getDoctrine()->getEntityManager();

            // récupération des paramètres
            $request = $this->getRequest();
            $euCd = $request->get('euCd');
            $cdImpact = $request->get('cdImpact');
            $commentaire = $request->get('commentaire');
            $valeur = $request->get('valeur');



            // sauvegarde
            $proposed = new ImpactMeProposed();

            // @todo adapter login 
            $proposed->setEuCd($euCd);
            $proposed->setPropositionDate(new DateTimeImpact("now"));
            $proposed->setCdImpact($cdImpact);



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

            $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:ImpactMe');
            $impactInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdImpact' => $cdImpact));
            $proposed->setImpactOriginal($impactInitiale);

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
                $msg = "Impact enregistrée... $commentaire";
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
     * Retourne le code HTML correspondant à une liste de impact proposées (pour un type donné)
     * 
     * mode Ajax
     */
    public function impactListProposedAction() {
        $request = $this->getRequest();

        $euCd = $request->get('euCd');
        $cdImpact = $request->get('cdImpact');

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:ImpactMe');
        $impactInitiale = $repo->findOneBy(array('euCd' => $euCd, 'cdImpact' => $cdImpact));

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:ImpactMe');
        $derniereProp = $repo->getLastProposition($euCd, $cdImpact);
        
        // recuperatiion de l'user connecté
        $user = $this->container->get('security.context')->getToken()->getUser();

        return $this->render('AeagEdlBundle:Impact:impactListProposed.html.twig', array(
                    'impact' => $impactInitiale,
                    'derniereProp' => $derniereProp,
                    'user' => $user
                ));
    }

    public function removeImpactAction() {
        $request = $this->getRequest();

        $euCd = $request->get('euCd');
        $cdImpact = $request->get('cdImpact');
        $login = $request->get('login');
        $propositionDate = $request->get('propositionDate');

        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:ImpactMeProposed');

        $proposition = $repo->findOneBy(array('euCd' => $euCd, 'cdImpact' => $cdImpact, 'utilisateur' => $login, 'propositionDate' => $propositionDate));

        $em->remove($proposition);
        $em->flush();

        return $this->forward('AeagEdlBundle:Impact:impactListProposed', array(
                    'euCd' => $euCd,
                    'cdImpact' => $cdImpact
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
