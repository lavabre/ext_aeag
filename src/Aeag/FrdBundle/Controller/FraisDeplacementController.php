<?php

namespace Aeag\FrdBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\True;
use Aeag\FrdBundle\Entity\EtatFrais;
use Aeag\FrdBundle\Entity\FraisDeplacement;
use Aeag\FrdBundle\Form\FraisDeplacement\FraisDeplacementType;
use Aeag\FrdBundle\Form\FraisDeplacement\CompteType;
use Aeag\FrdBundle\Form\FraisDeplacement\EnvoyerMessageType;
use Aeag\FrdBundle\Entity\Form\EnvoyerMessage;
use Aeag\FrdBundle\DependencyInjection\PDF;
use Symfony\Component\HttpFoundation\Response;
use Aeag\AeagBundle\Controller\AeagController;
use Aeag\AeagBundle\Entity\Message;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\UserBundle\Entity\Statistiques;

/**
 * FraisDeplacement controller.
 *
 * @Route("/membre/fraisdeplacement")
 */
class FraisDeplacementController extends Controller {

    /**
     * Lists all FraisDeplacement entities.
     *
     * @Route("/", name="membre_fraisdeplacement")
     * @Method("GET")
     * @Template()
     */
    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');


        return $this->redirect($this->generateUrl('AeagFrdBundle_membre_consulterEtatFraisParAnnee', array('anneeSelect' => $session->get('anneeSelect'))));
    }

    public function consulterEtatFraisParAnneeAction($anneeSelect = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'consulterEtatFraisParAnnee');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoEtatFrais = $emFrd->getRepository('AeagFrdBundle:EtatFrais');
        $repoMandatement = $emFrd->getRepository('AeagFrdBundle:Mandatement');
        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoPhase = $emFrd->getRepository('AeagFrdBundle:Phase');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');

        if ($anneeSelect == '9999') {
            $anneeSel = $session->get('anneeSelect');
        } else {
            $anneeSel = $anneeSelect;
        }

        $session->set('anneeSelect', $anneeSel);

        $annee1 = $anneeSel . '-01-01';
        $anneeDeb = new \DateTime($annee1);
        $annee1 = $anneeSel . '-12-31';
        $anneeFin = new \DateTime($annee1);

        $correspondant = $repoCorrespondant->getCorrespondantById($user->getCorrespondant());
        $phase = $repoPhase->getPhaseByCode('40');
        $nbFraisDeplacementEnCours = $repoFraisDeplacement->getNbFraisDeplacementEnCoursByUserPhase($user->getId(), $phase->getId(), $anneeDeb, $anneeFin);
        $i = 0;
        $entities = array();
        if ($anneeSel == date_format($session->get('annee'), 'Y')) {
            $etatFrais = new EtatFrais();
            $phase = $repoPhase->getPhaseByCode('10');
            $entities[$i]['etatFrais'] = $etatFrais;
            $entities[$i]['mandatement'] = null;
            $entities[$i]['phase'] = $phase;
            $entities[$i]['nbFraisDeplacements'] = $nbFraisDeplacementEnCours;
            $i++;
        }
        $etatsFrais = $repoEtatFrais->getListeEtatFraisByCorrespondantAnnee($correspondant->getCorId(), $anneeSel);
        foreach ($etatsFrais as $etatFrais) {
            $entities[$i]['etatFrais'] = $etatFrais;
            $mandatement = $repoMandatement->getMandatementByEtfrId($etatFrais->getId());
            if ($mandatement) {
                $entities[$i]['mandatement'] = $mandatement;
            } else {
                $entities[$i]['mandatement'] = null;
            }
            if ($etatFrais->getPhase() == '10') {
                $codePhase = '40';
            } else {
                $codePhase = $etatFrais->getPhase();
            }
            $phase = $repoPhase->getPhaseByCode($codePhase);
            $entities[$i]['phase'] = $phase;
            $nbFraisDeplacements = $repoFraisDeplacement->getNbFraisDeplacementByEtfrId($etatFrais->getId());
            $entities[$i]['nbFraisDeplacements'] = $nbFraisDeplacements;
            $i++;
        }
        usort($entities, create_function('$a,$b', 'return $a[\'etatFrais\']->getNum()-$b[\'etatFrais\']->getNum();'));

        $session->set('retour', $this->generateUrl('AeagFrdBundle_membre_consulterEtatFraisParAnnee', array('anneeSelect' => $session->get('anneeSelect'))));

        return $this->render('AeagFrdBundle:EtatFrais:consulterEtatFrais.html.twig', array(
                    'user' => $user,
                    'correspondant' => $correspondant,
                    'entities' => $entities,
                    'annee' => $anneeSelect
        ));
    }

    public function consulterFraisDeplacementsParEtatFraisAction($etatFraisId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'consulterFraisDeplacementsParEtatFrais');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoEtatFrais = $emFrd->getRepository('AeagFrdBundle:EtatFrais');
        $repoMandatement = $emFrd->getRepository('AeagFrdBundle:Mandatement');
        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoPhase = $emFrd->getRepository('AeagFrdBundle:Phase');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');

        $correspondant = $repoCorrespondant->getCorrespondantById($user->getCorrespondant());
        if ($etatFraisId) {
            $etatFrais = $repoEtatFrais->getEtatFraisById($etatFraisId);
            $mandatement = $repoMandatement->getMandatementByEtfrId($etatFrais->getId());
            $fraisDeplacements = $repoFraisDeplacement->getFraisDeplacementByEtfrId($etatFrais->getId());
        } else {
            $etatFrais = null;
            $mandatement = null;
            $phase = $repoPhase->getPhaseByCode('40');
            $annee1 = $session->get('anneeSelect') . '-01-01';
            $anneeDeb = new \DateTime($annee1);
            $annee1 = $session->get('anneeSelect') . '-12-31';
            $anneeFin = new \DateTime($annee1);
            $fraisDeplacements = $repoFraisDeplacement->getFraisDeplacementEnCoursByUserPhase($user->getId(), $phase->getId(), $anneeDeb, $anneeFin);
        }

        $i = 0;
        $entities = array();
        foreach ($fraisDeplacements as $fraisDeplacement) {
            // print_r('frais : '. $fraisDeplacement->getid() );
            $entities[$i][0] = $fraisDeplacement;
            $user = $repoUsers->getUserById($fraisDeplacement->getUser());
            $entities[$i][1] = $user;
            if ($correspondant) {
                $entities[$i][2] = $correspondant;
            } else {
                $entities[$i][2] = null;
            }
            $i++;
        }

        $session->set('retour1', $this->generateUrl('AeagFrdBundle_membre_consulterFraisDeplacementsParEtatFrais', array('etatFraisId' => $etatFraisId)));

        return $this->render('AeagFrdBundle:EtatFrais:consulterFraisDeplacementsParEtatFrais.html.twig', array(
                    'user' => $user,
                    'correspondant' => $correspondant,
                    'etatFrais' => $etatFrais,
                    'mandatement' => $mandatement,
                    'entities' => $entities,
                    'annee' => $session->get('anneeSelect')
        ));
    }

    public function consulterFraisDeplacementsParAnneeAction($anneeSelect = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'consulterFraisDeplacementsParAnnee');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');

        if ($anneeSelect == '9999') {
            $anneeSel = $session->get('annee');
        } else {
            $annee = $anneeSelect . '-01-01';
            $anneeSel = new \DateTime($annee);
        }

        $annee1 = $anneeSel->format('Y');
        $anneeDeb1 = $annee1 . '-01-01';
        $anneeFin1 = $annee1 . '-12-31';
        $anneeDeb = new \DateTime($anneeDeb1);
        $anneeFin = new \DateTime($anneeFin1);

        $fraisDeplacements = $repoFraisDeplacement->getFraisDeplacementByUserAnnee($user->getId(), $anneeDeb, $anneeFin);
        $i = 0;
        $entities = array();
        foreach ($fraisDeplacements as $fraisDeplacement) {
            // print_r('frais : '. $fraisDeplacement->getid() );
            $entities[$i][0] = $fraisDeplacement;
            $user = $repoUsers->getUserById($fraisDeplacement->getUser());
            if ($user) {
                if ($user->getCorrespondant()) {
                    $correspondant = $repoCorrespondant->getCorrespondantById($user->getCorrespondant());
                } else {
                    $correspondant = $repoCorrespondant->getCorrespondant($user->getUsername());
                }
                $entities[$i][1] = $user;
                if ($correspondant) {
                    $entities[$i][2] = $correspondant;
                } else {
                    $entities[$i][2] = null;
                }
            } else {
                $entities[$i][1] = null;
                $entities[$i][2] = null;
            }
            $i++;
        }

        return $this->render('AeagFrdBundle:FraisDeplacement:consulterFraisDeplacements.html.twig', array(
                    'user' => $user,
                    'entities' => $entities,
                    'annee' => $anneeSelect
        ));
    }

    public function validerFraisDeplacementAction($id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'validerFraisDeplacement');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');


        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoPhase = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:Phase');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoDept = $em->getRepository('AeagAeagBundle:Departement');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINFRD')) {
            $entity = $repoFraisDeplacement->getFraisDeplacementById($id);
        } else {
            $entity = $repoFraisDeplacement->getFraisDeplacementByIdUser($id, $user->getId());
        }

        if (!$entity) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement .');
        }

        $phase = $repoPhase->getPhaseByCode('20');
        $entity->setPhase($phase);
        $now = date('Y-m-d');
        $now = new \DateTime($now);
        $entity->setDatePhase($now);
        $entity->setValider('N');
        $entity->setExporter('N');
        $emFrd->persist($entity);
        $emFrd->flush();

        $notifications = $this->get('aeag.notifications');
        $texte = 'Votre demande ' . $entity->getId() . ' a été validé';
        $notifications->createNotification($user, $user, $em, $session, $texte);

        $fraisDeplacement = $entity;
        $entity = array();
        $entity[0] = $fraisDeplacement;
        $membre = $repoUsers->getUserById($fraisDeplacement->getUser());
        $entity[1] = $membre;
        $correspondant = $repoCorrespondant->getCorrespondantById($membre->getCorrespondant());
        $entity[2] = $correspondant;
        $entity[3] = null;

        if (!($this->get('security.authorization_checker')->isGranted('ROLE_ADMINFRD'))) {
            if ($entity[1]->getEmail()) {
                $this->sendAccuseReception($entity[0]->getId());
                $session->set('passage', '1');
            }
            $this->sendAccuseResponsable($entity[0]->getId());
        }

        return $this->render('AeagFrdBundle:FraisDeplacement:afficherLigneFraisDeplacement.html.twig', array(
                    'entity' => $entity
        ));
    }

    public function devaliderFraisDeplacementAction($id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'devaliderFraisDeplacement');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoPhase = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:Phase');
        $repoUsers = $em->getRepository('AeagUserBundle:User');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINFRD')) {
            $entity = $repoFraisDeplacement->getFraisDeplacementById($id);
        } else {
            $entity = $repoFraisDeplacement->getFraisDeplacementByIdUser($id, $user->getId());
        }

        if (!$entity) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement .');
        }

        $phase = $repoPhase->getPhaseByCode($entity->getPhase()->getCode());
        if ($phase->getCode() == '60') {
            $phaseCode = '40';
            $entity->setValider('O');
            $entity->setExporter('O');
        } elseif ($phase->getCode() == '40') {
            $phaseCode = '30';
            $entity->setValider('N');
            $entity->setExporter('N');
        } elseif ($phase->getCode() == '30') {
            $phaseCode = '20';
            $entity->setValider('N');
            $entity->setExporter('N');
        } else {
            $phaseCode = '10';
            $entity->setValider('N');
            $entity->setExporter('N');
        }
        $phase = $repoPhase->getPhaseByCode($phaseCode);
        $entity->setPhase($phase);
        $now = date('Y-m-d');
        $now = new \DateTime($now);
        $entity->setDatePhase($now);

        $emFrd->persist($entity);
        $emFrd->flush();

        $fraisDeplacement = $entity;
        $entity = array();
        $entity[0] = $fraisDeplacement;
        $membre = $repoUsers->getUserById($fraisDeplacement->getUser());
        $entity[1] = $membre;
        $correspondant = $repoCorrespondant->getCorrespondantById($membre->getCorrespondant());
        $entity[2] = $correspondant;
        $entity[3] = null;

        $message = 'Les frais de déplacement n° ' . $entity[0]->getId();
        $message = $message . ' du ' . $entity[0]->getDateDepart()->format('d/m/Y') . ' ' . $entity[0]->getHeureDepart();
        $message = $message . ' au ' . $entity[0]->getDateRetour()->format('d/m/Y') . ' ' . $entity[0]->getHeureRetour();
        $message = $message . ' ont été dévalidés. ';
        $this->get('session')->getFlashBag()->add('notice-success', $message);


        return $this->render('AeagFrdBundle:FraisDeplacement:afficherLigneFraisDeplacement.html.twig', array(
                    'entity' => $entity
        ));
    }

    /**
     * Deletes a FraisDeplacement entity.
     *
     * @Route("/{id}", name="membre_deleteFraisDeplacemente")
     * @Method("DELETE")
     */
    public function deleteFraisDeplacementAction(Request $request, $id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'deleteFraisDeplacement');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoUsers = $em->getRepository('AeagUserBundle:User');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINFRD')) {
            $entity = $repoFraisDeplacement->getFraisDeplacementById($id);
        } else {
            $entity = $repoFraisDeplacement->getFraisDeplacementByIdUser($id, $user->getId());
        }

        if (!$entity) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement .');
        }

        $fraisDeplacement = $entity;
        $entity = array();
        $entity[0] = clone($fraisDeplacement);
        $membre = $repoUsers->getUserById($fraisDeplacement->getUser());
        $entity[1] = $membre;
        $correspondant = $repoCorrespondant->getCorrespondantById($membre->getCorrespondant());
        $entity[2] = $correspondant;
        $entity[3] = 'SUP';

        $emFrd->remove($fraisDeplacement);
        $emFrd->flush();

        $notifications = $this->get('aeag.notifications');
        $texte = 'Votre demande n° ' . $entity[0]->getId() . ' a été supprimée.';
        $notifications->createNotification($user, $user, $em, $session, $texte);

        return $this->render('AeagFrdBundle:FraisDeplacement:afficherLigneFraisDeplacement.html.twig', array(
                    'entity' => $entity
        ));

        //return new response('delete');
    }

    /**
     * Creates a new FraisDeplacement entity.
     * or modify a FraisDeplacement entity.
     */
    public function fraisDeplacementAction($id, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'fraisDeplacement');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoPhase = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:Phase');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoDept = $em->getRepository('AeagAeagBundle:Departement');
        $repoFinalite = $emFrd->getRepository('AeagFrdBundle:Finalite');
        $repoSousTheme = $emFrd->getRepository('AeagFrdBundle:SousTheme');
        $repoPhase = $emFrd->getRepository('AeagFrdBundle:Phase');


        if ($id) {
            $fraisDeplacement = $repoFraisDeplacement->getFraisDeplacementById($id);
            if ($fraisDeplacement->getSousTheme()) {
                $fraisDeplacement->setSousTheme($fraisDeplacement->getSousTheme()->getLibelle());
            }
        } else {
            $fraisDeplacement = new FraisDeplacement();
            $fraisDeplacement->setTrainCouchette('N');
        }


        $entity = array();
        $entity[0] = $fraisDeplacement;
        if ($fraisDeplacement->getUser()) {
            $membre = $repoUsers->getUserById($fraisDeplacement->getUser());
        } else {
            $membre = $repoUsers->getUserById($user->getid());
        }
        $entity[1] = $membre;
        $dept = $repoDept->getDepartementByDept($fraisDeplacement->getdepartement());
        $entity[2] = $dept;

        $departements = $repoDept->getDepartements();

        $erreurDate = null;
        $erreurHeure = null;
        $erreurDateHeure = null;

        $form = $this->createForm(new FraisDeplacementType(), $fraisDeplacement);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            //return new response ('couchette1 : ' . $editFraisDeplacement->getTrainCouchette1());

            if ($form->isValid()) {

                $datetime1 = date_create($fraisDeplacement->getDateDepart()->format('Ymd'));
                $datetime2 = date_create($fraisDeplacement->getDateRetour()->format('Ymd'));
                $interval = date_diff($datetime1, $datetime2);
                if ($interval->format('%R%a') < 0) {
                    $constraint = new True(array(
                        'message' => 'La date de départ ne peut être supérieure à la date de retour'
                    ));
                    $erreurDate = $this->get('validator')->validateValue(false, $constraint);
                }

                if ($interval->format('%R%a') > 30) {
                    $constraint = new True(array(
                        'message' => 'La date de départ et la date de retour ne peut  être supérieure à 30 jours'
                    ));
                    $erreurDate = $this->get('validator')->validateValue(false, $constraint);
                }

                if ($interval->format('%R%a') == 0) {
                    $heureDepart = preg_split("/_/", $fraisDeplacement->getHeureDepart());
                    $heureRetour = preg_split("/_/", $fraisDeplacement->getHeureRetour());
                    if ($heureDepart[0] > $heureRetour[0]) {
                        $constraint = new True(array(
                            'message' => 'L\'heure de départ ne peut être supérieure à l\'heure de retour'
                        ));
                        $erreurHeure = $this->get('validator')->validateValue(false, $constraint);
                    }
                    if ($heureDepart[0] == $heureRetour[0]) {
                        if ($heureDepart[1] > $heureRetour[1]) {
                            $constraint = new True(array(
                                'message' => 'L\'heure de départ ne peut être supérieure à l\'heure de retour'
                            ));
                            $erreurHeure = $this->get('validator')->validateValue(false, $constraint);
                        }
                    }
                }

                //$fraisDeplacementPresents = $repoFraisDeplacement->getFraisDeplacementByUserDate($user->getId(), $fraisDeplacement->getDateDepart(), '00:00', $fraisDeplacement->getDateRetour(), '24:00');
                $fraisDeplacementPresents = $repoFraisDeplacement->getFraisDeplacementByUserAnneeDebutAnneeFin($user->getId(), $fraisDeplacement->getDateDepart()->format('Y'), $fraisDeplacement->getDateRetour()->format('Y'));
                foreach ($fraisDeplacementPresents as $fraisDeplacementPresent) {
                    if ($fraisDeplacementPresent->getDateDepart()->format('YYYYMMDD') >= $fraisDeplacement->getDateDepart()->format('YYYYMMDD')) {
                        if ($fraisDeplacementPresent->getHeureDepart() >= $fraisDeplacement->getHeureDepart()) {
                            if ($fraisDeplacementPresent->getDateRetour()->format('YYYYMMDD') <= $fraisDeplacement->getDateRetour()->format('YYYYMMDD')) {
                                if ($fraisDeplacementPresent->getHeureRetour() <= $fraisDeplacement->getHeureRetour()) {
                                    $constraint = new True(array(
                                        'message' => 'la  demande  ' . $fraisDeplacementPresent->getid() . ' existe déjà  entre le ' . $fraisDeplacement->getDateDepart()->format('d/m/Y') . ' ' . $fraisDeplacement->getHeureDepart() . ' et le ' . $fraisDeplacement->getDateRetour()->format('d/m/Y') . ' ' . $fraisDeplacement->getHeureRetour()
                                    ));
                                    $erreurDateHeure = $this->get('validator')->validateValue(false, $constraint);
                                }
                            }
                        }
                    }
                }

                //return new Response ('dept : ' . $fraisDeplacement->getDepartement() );

                if (count($erreurDate) == 0 && count($erreurHeure) == 0 && count($erreurDateHeure) == 0) {


                    $emFrd = $this->getDoctrine()->getManager('frd');
                    if (!$id) {
                        $fraisDeplacement->setUser($user->getid());
                    }
                    if ($fraisDeplacement->getValider() == 'O') {
                        $phase = $repoPhase->getPhaseByCode('20');
                        $fraisDeplacement->setPhase($phase);
                        $fraisDeplacement->setValider('N');
                    } else {
                        $phase = $repoPhase->getPhaseByCode('10');
                        $fraisDeplacement->setPhase($phase);
                        $fraisDeplacement->setValider('N');
                    }
                    $now = date('Y-m-d');
                    $now = new \DateTime($now);
                    $fraisDeplacement->setDatePhase($now);
                    $fraisDeplacement->setExporter('N');
                    //return new response ('sous-theme : ' . $editFraisDeplacement->getSousTheme());
                    if ($fraisDeplacement->getSousTheme()) {
                        //return new response ('sous-theme 2 : ' . $fraisDeplacement->getSousTheme());
                        $sousTheme = $repoSousTheme->getSousThemeByCode($fraisDeplacement->getSousTheme());
                        $fraisDeplacement->setSousTheme($sousTheme);
                    }

                    $emFrd->persist($fraisDeplacement);
                    $emFrd->flush();

                    if (!($this->get('security.authorization_checker')->isGranted('ROLE_ADMINFRD'))) {
                        if ($fraisDeplacement->getPhase()->getCode() == '20') {
                            $membre = $repoUsers->getUserById($fraisDeplacement->getUser());
                            if ($membre->getEmail()) {
                                $this->sendAccuseReception($fraisDeplacement->getId());
                                $session->set('passage', '1');
                            }
                            $this->sendAccuseResponsable($fraisDeplacement->getId());

//                        return $this->redirect($this->generateUrl('AeagFrdBundle_declaration_pdf', array('id' => $fraisDeplacement->getId())));
                            $entity = array();
                            $entity[0] = $fraisDeplacement;
                            $user = $repoUsers->getUserById($fraisDeplacement->getUser());
                            $entity[1] = $user;
                            $dept = $repoDept->getDepartementByDept($fraisDeplacement->getdepartement());
                            $entity[2] = $dept;
                            $pdf = new PDF('P', 'mm', 'A4');
                            $pdf->StartPageGroup();
                            $pdf->AddPage($entity);
                            $pdf->SetFont('Arial', '', 10);
                            $pdf->Formatage($entity);
                            $fichier = 'FRD_' . '_' . $entity[1]->getUsername();
                            $fichier = $fichier . '_' . $entity[1]->getPrenom() . '_' . $entity[0]->getId() . '.pdf';
                            $pdf->Output($fichier, 'D');
                        }
                    }
                    return $this->redirect($this->generateUrl('aeag_frd'));
                }
            }
        }

        return $this->render('AeagFrdBundle:FraisDeplacement:fraisDeplacement.html.twig', array(
                    'user' => $user,
                    'entity' => $entity,
                    'departements' => $departements,
                    'form' => $form->createView(),
                    'erreurDate' => $erreurDate,
                    'erreurHeure' => $erreurHeure,
                    'erreurDateHeure' => $erreurDateHeure,
        ));
    }

    /**
     * Finds and displays a FraisDeplacement entity.
     *
     * @Route("/{id}", name="membre_fraisdeplacement_show")
     * @Method("GET")
     * @Template()
     */
    public function viewFraisDeplacementAction($id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'viewfraisDeplacement');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoDept = $em->getRepository('AeagAeagBundle:Departement');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINFRD')) {
            $fraisDeplacement = $repoFraisDeplacement->getFraisDeplacementById($id);
        } else {
            $fraisDeplacement = $repoFraisDeplacement->getFraisDeplacementByIdUser($id, $user->getId());
        }
        $entity = array();
        $entity[0] = $fraisDeplacement;
        $user = $repoUsers->getUserById($fraisDeplacement->getUser());
        $entity[1] = $user;
        $dept = $repoDept->getDepartementByDept($fraisDeplacement->getdepartement());
        $entity[2] = $dept;
        if (!$entity) {
            return $this->redirect($this->generateUrl('AeagFrdBundle_membre'));
        }


        return array(
            'entity' => $entity,
        );
    }

    /**
     * Creates a form to delete a FraisDeplacement entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('AeagFrdBundle_membre_deleteFraisDeplacement', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

    public function afficherSousThemeAction() {
        $emFrd = $this->getDoctrine()->getManager('frd');
        $request = $this->container->get('request');
        $finalite = $request->get('finalite');
        $sousTheme = $request->get('sousTheme');
        $repoSousTheme = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:SousTheme');
        $sousThemes = $repoSousTheme->getSousThemeByFinalite($finalite);
        return $this->render('AeagFrdBundle:FraisDeplacement:afficherSousTheme.html.twig', array(
                    'sousThemes' => $sousThemes,
                    'sousTheme' => $sousTheme
        ));
    }

    /**
     *  Fichier PDF
     */
    public function pdfAction($id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'pdf');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoDept = $em->getRepository('AeagAeagBundle:Departement');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINFRD')) {
            $fraisDeplacement = $repoFraisDeplacement->getFraisDeplacementById($id);
        } else {
            $fraisDeplacement = $repoFraisDeplacement->getFraisDeplacementByIdUser($id, $user->getId());
        }
        $entity = array();
        $entity[0] = $fraisDeplacement;
        $user = $repoUsers->getUserById($fraisDeplacement->getUser());
        $entity[1] = $user;
        $dept = $repoDept->getDepartementByDept($fraisDeplacement->getdepartement());
        $entity[2] = $dept;

        if (!$entity) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement .');
        }

        $pdf = new PDF('P', 'mm', 'A4');

        $pdf->StartPageGroup();
        $pdf->AddPage($entity);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($entity);
        $fichier = 'FRD_' . '_' . $entity[1]->getUsername();
        $fichier = $fichier . '_' . $entity[1]->getPrenom() . '_' . $entity[0]->getId() . '.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagFrdBundle:FraisDeplacement:pdf.pdf.twig');
    }

    /*
     * envoi d'un mail à l'administrateur
     */

    private function sendAccuseReception($id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', ' sendAccuseReception');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoDept = $em->getRepository('AeagAeagBundle:Departement');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINFRD')) {
            $fraisDeplacement = $repoFraisDeplacement->getFraisDeplacementById($id);
        } else {
            $fraisDeplacement = $repoFraisDeplacement->getFraisDeplacementByIdUser($id, $user->getId());
        }
        $entity = array();
        $entity[0] = $fraisDeplacement;
        $membre = $repoUsers->getUserById($fraisDeplacement->getUser());
        $entity[1] = $membre;
        $dept = $repoDept->getDepartementByDept($fraisDeplacement->getdepartement());
        $entity[2] = $dept;

        if (!$entity) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement .');
        }

        // Récupération du service.
        $mailer = $this->get('mailer');

        // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
        $message = \Swift_Message::newInstance()
                ->setSubject('Frais de déplacement n° ' . $entity[0]->getId() . ' transmise à l\'agence de l\'eau "Adour-garonne"')
                ->setFrom('automate@eau-adour-garonne.fr')
                ->setTo($entity[1]->getEmail())
                ->setBody($this->renderView('AeagFrdBundle:FraisDeplacement:accuseReceptionEmail.txt.twig', array(
                    'entity' => $entity)));

        $repoParametres = $emFrd->getRepository('AeagFrdBundle:Parametre');
        $parametre = $repoParametres->findOneBy(array('code' => 'REP_IMPORT'));
        $rep_import = $parametre->getLibelle() . "/Pdf";
        $pdf = new PDF('P', 'mm', 'A4');
        $pdf->StartPageGroup();
        $pdf->AddPage($entity);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($entity);
        $fichier = 'FRD' . '_' . $entity[1]->getUsername();
        $fichier = $fichier . '_' . $entity[1]->getPrenom() . '_' . $entity[0]->getId() . '.pdf';
        $fic_import = $rep_import . "/" . $fichier;
        $pdf->Output($fic_import, 'F');
        $message->attach(\Swift_Attachment::fromPath($fic_import));

        // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
        $mailer->send($message);

        $message = 'Les frais de déplacement n° ' . $entity[0]->getId();
        $message = $message . ' du ' . $entity[0]->getDateDepart()->format('d/m/Y') . ' ' . $entity[0]->getHeureDepart();
        $message = $message . ' au ' . $entity[0]->getDateRetour()->format('d/m/Y') . ' ' . $entity[0]->getHeureRetour();
        $message = $message . ' ont été validés. L\'agence de l`\'eau  est en attente de votre courier. Vous allez recevoir un accusé de réception. ';
        $this->get('session')->getFlashBag()->add('notice-success', $message);
    }

    /*
     * envoi d'un mail au responsable
     */

    private function sendAccuseResponsable($id) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', ' sendAccuseResponsable');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoDept = $em->getRepository('AeagAeagBundle:Departement');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINFRD')) {
            $fraisDeplacement = $repoFraisDeplacement->getFraisDeplacementById($id);
        } else {
            $fraisDeplacement = $repoFraisDeplacement->getFraisDeplacementByIdUser($id, $user->getId());
        }
        $entity = array();
        $entity[0] = $fraisDeplacement;
        $membre = $repoUsers->getUserById($fraisDeplacement->getUser());
        $entity[1] = $membre;
        $dept = $repoDept->getDepartementByDept($fraisDeplacement->getdepartement());
        $entity[2] = $dept;

        if (!$entity) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement .');
        }


        // Récupération du service.
        $mailer = $this->get('mailer');

        // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
        $message = \Swift_Message::newInstance()
                ->setSubject('Frais de déplacement ' . $entity[0]->getId() . ' transmise à l\'agence de l\'eau "Adour-garonne"')
                ->setFrom('automate@eau-adour-garonne.fr')
                ->setTo('jle@eau-adour-garonne.fr')
                ->setBody($this->renderView('AeagFrdBundle:FraisDeplacement:accuseResponsableEmail.txt.twig', array(
                    'entity' => $entity)));

        // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
        $mailer->send($message);
    }

    public function envoyerMessageAction(Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'contact');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'envoyerMessage');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoMessages = $em->getRepository('AeagAeagBundle:Message');
        $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');

        $admin = $repoUsers->getUserByUsername('adminfrd');

        if (!$admin) {
            throw $this->createNotFoundException('Impossible de retouver les adminuistrateurs du site');
        }

        $envoyerMessage = new EnvoyerMessage();
        $form = $this->createForm(new EnvoyerMessageType(array($admin->getEmail(), $admin->getEmail1(), $admin->getEmail2())), $envoyerMessage);
        $message = null;

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);
            if ($form->isValid()) {

                $messages = $this->get('aeag.messages');
                $texte = $envoyerMessage->getMessage();
                $messages->createMessage($user, $User, $em, $session, $texte);

                $notifications = $this->get('aeag.notifications');
                $texte = 'Message envoyé à ' . $admin->getUsername();
                $notifications->createNotification($user, $user, $em, $session, $texte);


// Récupération du service.
                $mailer = $this->get('mailer');
                $dest = array();
                $i = 0;
                foreach ($envoyerMessage->getDestinataire() as $destinataire) {
                    // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
                    $desti = explode(" ", $destinataire);
                    $mail = \Swift_Message::newInstance()
                            ->setSubject($envoyerMessage->getSujet())
                            ->setFrom(array('automate@eau-adour-garonne.fr'))
                            ->setTo(array($desti[0]))
                            ->setBody($envoyerMessage->getMessage());
                    if ($envoyerMessage->getCopie()) {
                        $mail->addCc($envoyerMessage->getCopie());
                    }
                    // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
                    $mailer->send($mail);
                }

                $this->get('session')->getFlashBag()->add('notice-success', 'Message envoyé avec succès !');


                return $this->redirect($this->generateUrl('AeagFrdBundle_membre'));
            }
        }


        return $this->render('AeagFrdBundle:FraisDeplacement:envoyerMessage.html.twig', array(
                    'User' => $admin,
                    'form' => $form->createView()
        ));
    }

}
