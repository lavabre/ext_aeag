<?php

namespace Aeag\EdlBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Aeag\EdlBundle\Form\MasseEauRechercheForm;
use Aeag\EdlBundle\Entity\Contact;
use Aeag\EdlBundle\Entity\Criteres;
use Aeag\EdlBundle\Form\ContactType;
use Aeag\UserBundle\Entity\User;

class MyDateTime extends \DateTime {

    public function __toString() {
        return $this->format('Y-m-d H:i:s');
    }

}

class DefaultController extends Controller {

    public function indexAction(Request $request) {


        /* Recherche  des mases d'eau
         * 
         */

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('retourErreur', $this->generateUrl('Aeag_edl'));
        $session->set('menu', 'index');
        $session->set('controller', 'default');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

        if (is_object($user) && ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINEDL'))) {
            // insertion des users
            // $message = $this->majUtilisateurs();
            $message = $this->initUtilisateurs();
            //return new Response($message);
            //$session->getFlashBag()->add('notice-success', $message);
        }

//        $session->set('codecle', null);
//        $session->set('masseEaucle', null);
//        $session->set('deptcle', null);
//        $session->set('typecle', null);
//        $session->set('territoirecle', null);
        $session->set('recherche', 'N');

        $critere = new Criteres();
        $form = $this->createForm(new MasseEauRechercheForm(), $critere);

        return $this->render('AeagEdlBundle:Default:index.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    /**
     *  Liste des masses d'eau
     *
     */
    public function listeMasseEauAction(Request $request) {

// Liste des dossiers selectionnés
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('retourErreur', $this->generateUrl('Aeag_edl'));
        $session->set('menu', 'acceuil');
        $session->set('controller', 'default');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

        $critere = new Criteres();
        $form = $this->createForm(new MasseEauRechercheForm(), $critere);

        $form->handleRequest($request);

        $session = $this->get("session");

        if ($session->get('recherche') == 'N') {

            $request = $this->getRequest();

            $codecle = $critere->getCodecle();
            $masseEaucle = $critere->getMassecle();
            if ($critere->getDeptcle()) {
                $deptcle = $critere->getDeptcle()->getInseeDepartement();
                $deptnom = $critere->getDeptcle()->getnomDepartement();
            } else {
                $deptcle = null;
                $deptnom = null;
            };
            $typecle = $critere->getTypecle();
            $territoirecle = $critere->getTerritoirecle();

            $session->set('codecle', $codecle);
            $session->set('masseEaucle', $masseEaucle);
            $session->set('deptcle', $deptcle);
            $session->set('deptnom', $deptnom);
            $session->set('typecle', $typecle);
            $session->set('recherche', 'O');

            if (!($territoirecle == '1')) {
                $territoirecle = '2';
            }
            $session->set('territoirecle', $territoirecle);
        } else {
            $codecle = $session->get('codecle');
            $masseEaucle = $session->get('masseEaucle');
            $deptcle = $session->get('deptcle');
            $typecle = $session->get('typecle');
            $territoirecle = $session->get('territoirecle');
        }

        $tabSelection = array();
        if ($deptcle) {
            $tabSelection['dept'] = $session->get('deptnom');
        } else {
            $tabSelection['dept'] = null;
        }
        if ($typecle) {
            if ($typecle == 'CW') {
                $tabSelection['type'] = 'Cotière';
            }
            if ($typecle == 'TW') {
                $tabSelection['type'] = 'Transition';
            }
            if ($typecle == 'LW') {
                $tabSelection['type'] = 'Lac';
            }
            if ($typecle == 'RW') {
                $tabSelection['type'] = 'Rivière';
            }
            if ($typecle == 'GW') {
                $tabSelection['type'] = 'Souterraine';
            }
        } else {
            $tabSelection['type'] = null;
        }
        if ($codecle) {
            $tabSelection['code'] = $codecle;
        } else {
            $tabSelection['code'] = null;
        }
        if ($masseEaucle) {
            $tabSelection['masseEau'] = $masseEaucle;
        } else {
            $tabSelection['masseEau'] = null;
        }
        if ($territoirecle == '1') {
            $tabSelection['territoire'] = 'O';
        } else {
            $tabSelection['territoire'] = 'N';
        }



        //return new Response('codecle : ' . $codecle . ' massecle : ' . $masseEaucle . ' dept : ' . $deptcle . ' typecle : ' . $typecle . ' territoire : ' . $territoirecle);


        $where = "a.euCd = a.euCd";


        if (!empty($typecle)) {
            $where = $where . " and upper(a.typeMe) = '" . $typecle . "'";
        }

        if (!empty($codecle)) {
            $where = $where . " and upper(a.euCd) LIKE '%" . strtoupper($codecle) . "%'";
            if (!empty($masseEaucle)) {
                $where = $where . " and upper(a.nomMasseEau) LIKE '%" . strtoupper($masseEaucle) . "%'";
            };
        } else {
            if (!empty($masseEaucle)) {
                $where = $where . " and upper(a.nomMasseEau) LIKE '%" . strtoupper($masseEaucle) . "%'";
            };
        };


        if ($territoirecle == '1') {
            $user = $this->container->get('security.context')->getToken()->getUser();

            // Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
            if (!is_object($user)) {
                throw new AccessDeniedException('Vous n\'êtes pas authentifié.');
            }

            //return new Response('role : ' . isset(in_array("ROLE_SUPERVISEUR", $user->getRoles())));

            if (!empty($deptcle)) {
                $where = $where . " and b.euCd = a.euCd";
                $where = $where . " and b.inseeDepartement = '" . $deptcle . "'";
                $where = $where . " and b.inseeDepartement = c.inseeDepartement";
                $where = $where . " and c.utilisateur = " . $user->getId();
            } else {

                $where = $where . " and b.euCd = a.euCd";
                $where = $where . " and b.inseeDepartement = c.inseeDepartement";
                $where = $where . " and c.utilisateur = " . $user->getId();
            }

            $query = "select a from Aeag\EdlBundle\Entity\MasseEau a,";
            $query = $query . " Aeag\EdlBundle\Entity\DepartementMe b,";
            $query = $query . " Aeag\EdlBundle\Entity\DepUtilisateur c where ";
        } else {
            if (!empty($deptcle)) {
                $where = $where . " and b.euCd = a.euCd";
                $where = $where . " and b.inseeDepartement = '" . $deptcle . "'";

                $query = "select a from Aeag\EdlBundle\Entity\MasseEau a,";
                $query = $query . " Aeag\EdlBundle\Entity\DepartementMe b where ";
            } else {



                $query = "select a from Aeag\EdlBundle\Entity\MasseEau a where ";
            }
        }

        //return new Response('where  : ' .  $where );


        $query = $query . $where . " order by  a.nomMasseEau";

        //return new Response('query  : ' . $query);

        $MasseEaux = $emEdl->createQuery($query)
                ->getResult();

        $res = array();
        $i = 0;
        foreach ($MasseEaux as $MasseEau) {
            $repo = $emEdl->getRepository('AeagEdlBundle:EtatMe');
            $etatMes = $repo->getDerniereProposition($MasseEau->getEuCd());

            if ($etatMes) {
                $etatMe = $etatMes[0];
                $etat_user = $etatMe->getUtilisateur();
                $etat_date = $etatMe->getpropositionDate();
            } else {
                $etat_user = null;
                $etat_date = null;
            };


            $repo = $emEdl->getRepository('AeagEdlBundle:PressionMe');
            $pressionMes = $repo->getDerniereProposition($MasseEau->getEuCd());

            if ($pressionMes) {
                $pressionMe = $pressionMes[0];
                $pression_user = $pressionMe->getUtilisateur();
                $pression_date = $pressionMe->getPropositionDate();
            } else {
                $pression_user = null;
                $pression_date = null;
            };

            $der_user = $etat_user;
            $der_date = $etat_date;

            if ($pression_date > $der_date) {
                $der_user = $pression_user;
                $der_date = $pression_date;
            };


            //return new Response('etatMe : ' .  $etatMe->getEuCd() . ' user : ' . $etatMe->getUtilisateur());


            $res[$i] = array('euCd' => $MasseEau->getEuCd(),
                'nomMasseEau' => $MasseEau->getNomMasseEau(),
                'etat_user' => $etat_user,
                'etat_date' => $etat_date,
                'pression_user' => $pression_user,
                'pression_date' => $pression_date,
                'der_user' => $der_user,
                'der_date' => $der_date);
            $i++;
        }

        $variables['MasseEau'] = $res;
        $variables['Selection'] = $tabSelection;

        return $this->render('AeagEdlBundle:MasseEau:listeMasseEau.html.twig', $variables);
    }

    public function etatGroupeAction($code = null, Request $request) {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('controller', 'default');
        $session->set('fonction', 'etatGroupe');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');


        $repo = $emEdl->getRepository('AeagEdlBundle:EtatGroupe');
        $meRepo = $emEdl->getRepository('AeagEdlBundle:MasseEau');
        $repoAvisHistorique = $emEdl->getRepository('AeagEdlBundle:AvisHistorique');
        
        $etatGroupes = $repo->getEtatGroupe();

        if (!$etatGroupes) {
            throw $this->createNotFoundException('Table EtatGroupe non trouvée : ');
        }
        $me = $meRepo->findOneBy(array('euCd' => $code));

        if (!$me) {
            throw $this->createNotFoundException('Masse d\'eau non trouvée : ' . $code);
        }
        
        $avisHistorique = $repoAvisHistorique->getAvisHistoriqueByCodeEpr($code, 'Etat');
     
        $session->set('UrlRetour', $this->generateUrl('AeagEdlBundle_listeMasseEau'));



        return $this->render('AeagEdlBundle:Etat:etatGroupe.html.twig', array(
                    'etatGroupes' => $etatGroupes,
                    'me' => $me,
                    'avisHistorique' => $avisHistorique,
                     'url' => $session->get('UrlRetour')
                        )
        );
    }

    public function etatAction($code = null, $cdGroupe = null, Request $request) {

        $user = $this->getUser();
        $session = $this->get('session');
         $session->set('controller', 'default');
        $session->set('fonction', 'etat');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');


        $repo = $emEdl->getRepository('AeagEdlBundle:EtatGroupe');
        $etatGroupe = $repo->findOneBy(array('cdGroupe' => $cdGroupe));
      
        $repo = $emEdl->getRepository('AeagEdlBundle:EtatMe');
        $etats = $repo->getEtatMe($code, $cdGroupe);
        $nbEtats = $repo->getNbEtatMe($code, $cdGroupe);
     

        if ($session->get('UrlRetour') == '') {
            $session->set('UrlRetour', $this->generateUrl('AeagEdlBundle_listeMasseEau'));
        }

        return $this->render('AeagEdlBundle:Etat:etat.html.twig', array(
                    'etatGroupe' => $etatGroupe,
                   'etats' => $etats,
                    'nbEtats' => $nbEtats,
                    'url' => $session->get('UrlRetour')
                        )
        );
    }

    public function pressionGroupeAction($code = null, Request $request) {

        $user = $this->getUser();
        $session = $this->get('session');
         $session->set('controller', 'default');
        $session->set('fonction', 'pressionGroupe');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');


        $repo = $emEdl->getRepository('AeagEdlBundle:PressionGroupe');
        $meRepo = $emEdl->getRepository('AeagEdlBundle:MasseEau');
        $repoAvisHistorique = $emEdl->getRepository('AeagEdlBundle:AvisHistorique');
        
        
        $pressionGroupes = $repo->getPressionGroupe();

        if (!$pressionGroupes) {
            throw $this->createNotFoundException('Table PressionGroupe non trouvée : ');
        }

        $me = $meRepo->findOneBy(array('euCd' => $code));

        if (!$me) {
            throw $this->createNotFoundException('Masse d\'eau non trouvée : ' . $code);
        }

        $avisHistorique = $repoAvisHistorique->getAvisHistoriqueByCodeEpr($code, 'Pression');

        return $this->render('AeagEdlBundle:Pression:pressionGroupe.html.twig', array(
                    'pressionGroupes' => $pressionGroupes,
                     'avisHistorique' => $avisHistorique,
                    'me' => $me,
                    'url' => $session->get('UrlRetour')
                        )
        );
    }

    public function pressionAction($code = null, $cdGroupe = null, Request $request) {

        $user = $this->getUser();
        $session = $this->get('session');
         $session->set('controller', 'default');
        $session->set('fonction', 'pression');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

        $repo = $emEdl->getRepository('AeagEdlBundle:PressionGroupe');
        $pressionGroupe = $repo->findOneBy(array('cdGroupe' => $cdGroupe));

        $repo = $emEdl->getRepository('AeagEdlBundle:PressionMe');
        $pressions = $repo->getPressionMe($code, $cdGroupe);
        $nbPressions = $repo->getNbPressionMe($code, $cdGroupe);

        if ($session->get('UrlRetour') == '') {
            $session->set('UrlRetour', $this->generateUrl('AeagEdlBundle_listeMasseEau', array('page' => 1)));
        }

        return $this->render('AeagEdlBundle:Pression:pression.html.twig', array(
                    'pressionGroupe' => $pressionGroupe,
                    'pressions' => $pressions,
                    'nbPressions' => $nbPressions,
                    'url' => $session->get('UrlRetour'),
                        )
        );
    }

    public function massedeauAction($code) {

        $request = $this->container->get('request');

        $meRepo = $this->getDoctrine()
                ->getRepository('AeagEdlBundle:MasseEau');

        $me = $meRepo->findOneBy(array('euCd' => $code));

        if (!$me) {
            throw $this->createNotFoundException('Masse d\'eau non trouvée : ' . $code);
        }

        return $this->render('AeagEdlBundle:Default:massedeau.html.twig', array(
                    'me' => $me,
                    'url' => $request->headers->get('referer'),
                        )
        );
    }

    public function impactAction($code = null, $cdGroupe = null) {


        $request = $this->container->get('request');

        $session = $request->getSession();

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:ImpactGroupe');
        $impactGroupe = $repo->findOneBy(array('cdGroupe' => $cdGroupe));

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:ImpactMe');
        $impacts = $repo->getImpactMe($code, $cdGroupe);
        $nbImpacts = $repo->getNbImpactMe($code, $cdGroupe);


        if ($session->get('UrlRetour') == '') {
            $session->set('UrlRetour', $this->generateUrl('AeagEdlBundle_listeMasseEau', array('page' => 1)));
        }


        return $this->render('AeagEdlBundle:Impact:impact.html.twig', array(
                    'impactGroupe' => $impactGroupe,
                    'impacts' => $impacts,
                    'nbImpacts' => $nbImpacts,
                    'url' => $session->get('UrlRetour'),
                        )
        );
    }

    public function impactGroupeAction($code = null) {

        $request = $this->container->get('request');

        $session = $request->getSession();

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:ImpactGroupe');
        $impactGroupes = $repo->getImpactGroupe();

        if (!$impactGroupes) {
            throw $this->createNotFoundException('Table ImpactGroupe non trouvée : ');
        }

        $meRepo = $this->getDoctrine()
                ->getRepository('AeagEdlBundle:MasseEau');

        $me = $meRepo->findOneBy(array('euCd' => $code));

        if (!$me) {
            throw $this->createNotFoundException('Masse d\'eau non trouvée : ' . $code);
        }



        return $this->render('AeagEdlBundle:Impact:impactGroupe.html.twig', array(
                    'impactGroupes' => $impactGroupes,
                    'me' => $me,
                    'url' => $session->get('UrlRetour')
                        )
        );
    }

    public function risqueAction($code = null, $cdGroupe = null) {

        $request = $this->container->get('request');

        $session = $request->getSession();

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:RisqueGroupe');
        $risqueGroupe = $repo->findOneBy(array('cdGroupe' => $cdGroupe));

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:RisqueMe');
        $risques = $repo->getRisqueMe($code, $cdGroupe);
        $nbRisques = $repo->getNbRisqueMe($code, $cdGroupe);

        if ($session->get('UrlRetour') == '') {
            $session->set('UrlRetour', $this->generateUrl('AeagEdlBundle_listeMasseEau', array('page' => 1)));
        }


        return $this->render('AeagEdlBundle:Risque:risque.html.twig', array(
                    'risqueGroupe' => $risqueGroupe,
                    'risques' => $risques,
                    'nbRisques' => $nbRisques,
                    'url' => $session->get('UrlRetour'),
                        )
        );
    }

    public function risqueGroupeAction($code = null) {

        $request = $this->container->get('request');

        $session = $request->getSession();

        $repo = $this->getDoctrine()->getRepository('AeagEdlBundle:RisqueGroupe');
        $risqueGroupes = $repo->getRisqueGroupe();

        if (!$risqueGroupes) {
            throw $this->createNotFoundException('Table RisqueGroupe non trouvée : ');
        }

        $meRepo = $this->getDoctrine()
                ->getRepository('AeagEdlBundle:MasseEau');

        $me = $meRepo->findOneBy(array('euCd' => $code));

        if (!$me) {
            throw $this->createNotFoundException('Masse d\'eau non trouvée : ' . $code);
        }

        return $this->render('AeagEdlBundle:Risque:risqueGroupe.html.twig', array(
                    'risqueGroupes' => $risqueGroupes,
                    'me' => $me,
                    'url' => $session->get('UrlRetour')
                        )
        );
    }

    public function contactAction() {

        $enquiry = new Contact();
        $form = $this->createForm(new ContactType(), $enquiry);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {

                $message = \Swift_Message::newInstance()
                        ->setSubject($enquiry->getSubject())
                        ->setFrom('automate@eau-adour-garonne.fr')
                        ->setTo('jle@eau-adour-garonne.fr')
                        ->setBody($this->renderView('AeagEdlBundle:Default:contactEmail.txt.twig', array('enquiry' => $enquiry)));
                $this->get('mailer')->send($message);

                $this->get('session')->setFlash('aeag-notice', 'Votre message à été envoyé avec succés. Merci!');

                // Redirect - This is important to prevent users re-posting
                // the form if they refresh the page
                return $this->redirect($this->generateUrl('AeagEdlBundle_contact'));
            }
        }

        return $this->render('AeagEdlBundle:Default:contact.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function majUtilisateurs() {

        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repo = $emEdl->getRepository('AeagSqeBundle:Utilisateur');

        $utilisateurs = $repo->findAll();
        $utilisateursNbModifies = 0;
        $message = '';


        foreach ($utilisateurs as $utilisateur) {
            $entityUser = $repoUsers->getUserByUsernamePassword($utilisateur->getUsername(), $utilisateur->getPassword());
            if ($entityUser) {
                $utilisateur->setExtId($entityUser->getId());
                $utilisateur->setMail($entityUser->getEmail());
                $utilisateur->setPassword($entityUser->getPassword());
                $utilisateur->setPasswordEnClair($entityUser->getPassword());
                $emEdl->persist($utilisateur);
                $utilisateursNbModifies++;
            }
        }
        $emEdl->flush();
        $message = "utilisateur  modifiés : " . $utilisateursNbModifies;
        return $message;
    }

    public function initUtilisateurs() {

        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');
        $factory = $this->get('security.encoder_factory');

        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repo = $emEdl->getRepository('AeagEdlBundle:Utilisateur');

        $utilisateurs = $repo->findAll();

        $utilisateursNbCrees = 0;
        $utilisateursNbModifies = 0;
        $message = ' ';


        foreach ($utilisateurs as $utilisateur) {
            if ($utilisateur->getExtId()) {
                $user = $repoUsers->getUserById($utilisateur->getExtId());
            } else {
                $user = null;
            }
            if (!$user) {
                $entityUser = new User();
                $entityUser->setEnabled(true);
                $utilisateursNbCrees++;
            } else {
                $entityUser = $user;
                $utilisateursNbModifies++;
            }
            $tabRoles = array();
            $tabRoles[] = 'ROLE_EDL';
            $entityUser->setRoles($tabRoles);
            $roles = $utilisateur->getRoles();
            for ($i = 0; $i < count($roles); $i++) {
                if ($roles[$i] == 'ROLE_COMMENTATEUR') {
                    $entityUser->addRole('ROLE_COMMENTATEUREDL');
                }
                if ($roles[$i] == 'ROLE_SUPERVISEUR') {
                    $entityUser->addRole('ROLE_SUPERVISEUREDL');
                }
                if ($roles[$i] == 'ROLE_ADMIN') {
                    $entityUser->addRole('ROLE_ADMINEDL');
                }
            }
            $encoder = $factory->getEncoder($entityUser);
            $entityUser->setUsername($utilisateur->getUserName());
            $entityUser->setSalt('');
            $password = $encoder->encodePassword($utilisateur->getPasswordenclair(), $entityUser->getSalt());
            $entityUser->setpassword($password);
            $entityUser->setPlainPassword($entityUser->getPassword());
            $email = $utilisateur->getEmail();
            if ($email) {
                $entityUser->setEmail($email);
            } else {
                $entityUser->setEmail($utilisateur->getUserName() . '@a-renseigner-merci.svp');
            }
            $em->persist($entityUser);

            //print_r('user : ' . $entityUser->getid() . ' ' . $entityUser->getUsername() . ' ' . $entityUser->getEmail() . ' ' . $entityUser->getPassword() . '  edluser : ' . $utilisateur->getid() . ' ' . $utilisateur->getUserName() . '\n  ');

            $utilisateur->setExtId($entityUser->getId());
            $utilisateur->setEmail($entityUser->getEmail());
            $utilisateur->setPassword($entityUser->getPassword());
            $utilisateur->setPasswordEnClair($entityUser->getPassword());
            $emEdl->persist($utilisateur);
        }
        $em->flush();
        $emEdl->flush();
        $message = "users edl crees : " . $utilisateursNbCrees . "   users edl modifiés : " . $utilisateursNbModifies;
        return $message;
    }

}