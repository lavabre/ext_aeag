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
 * Etat controller.
 *
 *  */
class EtatController extends Controller {

    /**
     * Lists all Etats entities.
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
        $session->set('controller', 'Etat');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        if (!$session->has('anneeSelect')) {
            $parametre = $repoParametre->getParametreByCode('ANNEE');
            $annee = new \DateTime($parametre->getLibelle());
            $session->set('annee', $annee);
            $session->set('anneeSelect', date_format($session->get('annee'), 'Y'));
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINFRD')) {
            return $this->redirect($this->generateUrl('AeagFrdBundle_etat_consulterEtatsParAnnee', array('anneeSelect' => $session->get('anneeSelect'))));
        } else {
            return $this->redirect($this->generateUrl('AeagFrdBundle_etat_consulterEtatParAnnee', array('anneeSelect' => $session->get('anneeSelect'))));
        }
    }

    public function consulterEtatsParAnneeAction($anneeSelect = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'consulterEtatsParAnnee');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoEtatFrais = $emFrd->getRepository('AeagFrdBundle:EtatFrais');
        $repoMandatement = $emFrd->getRepository('AeagFrdBundle:Mandatement');
        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoPhase = $emFrd->getRepository('AeagFrdBundle:Phase');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoParametre = $emFrd->getRepository('AeagFrdBundle:Parametre');

        if (!$session->has('annee')) {
            $annee = $repoParametre->getParametreByCode('ANNEE');
            $annee = new \DateTime($annee->getLibelle());
            $session->set('annee', $annee);
        }

        $anneeSel = $anneeSelect;

        $session->set('anneeSelect', $anneeSel);

        $annee1 = $anneeSel . '-01-01';
        $anneeDeb = new \DateTime($annee1);
        $annee1 = $anneeSel . '-12-31';
        $anneeFin = new \DateTime($annee1);

        $phase = $repoPhase->getPhaseByCode('40');
        $nbFraisDeplacementEnCours = $repoFraisDeplacement->getNbFraisDeplacementEnCoursByPhase($phase->getId(), $anneeDeb, $anneeFin);
        $i = 0;
        $entities = array();
        if ($anneeSel == date_format($session->get('annee'), 'Y')) {
            $etatFrais = new EtatFrais();
            $phase = $repoPhase->getPhaseByCode('10');
            $entities[$i]['etatFrais'] = $etatFrais;
            $entities[$i]['correspondant'] = null;
            $entities[$i]['mandatement'] = null;
            $entities[$i]['phase'] = $phase;
            $entities[$i]['nbFraisDeplacements'] = $nbFraisDeplacementEnCours;
            $i++;
        }
        $etatsFrais = $repoEtatFrais->getListeEtatFraisByAnnee($anneeSel);
        foreach ($etatsFrais as $etatFrais) {
            $nbFraisDeplacements = $repoFraisDeplacement->getNbFraisDeplacementByEtfrId($etatFrais->getId());
            if ($nbFraisDeplacements > 0) {
                $entities[$i]['etatFrais'] = $etatFrais;
                $correspondant = $repoCorrespondant->getCorrespondantByCorId($etatFrais->getCorId());
                if ($correspondant) {
                    $entities[$i]['correspondant'] = $correspondant;
                } else {
                    $entities[$i]['correspondant'] = null;
                }
                $mandatement = $repoMandatement->getMandatementByEtfrId($etatFrais->getId());
                if ($mandatement) {
                    $entities[$i]['mandatement'] = $mandatement;
                } else {
                    $entities[$i]['mandatement'] = null;
                }
                if ($etatFrais->getPhase() < '60') {
                    $codePhase = '40';
                } else {
                    $codePhase = $etatFrais->getPhase();
                }
                $phase = $repoPhase->getPhaseByCode($codePhase);
                $entities[$i]['phase'] = $phase;

                $entities[$i]['nbFraisDeplacements'] = $nbFraisDeplacements;
                $i++;
            }
        }
        usort($entities, create_function('$a,$b', 'return $a[\'etatFrais\']->getNum()-$b[\'etatFrais\']->getNum();'));

        $session->set('retour', $this->generateUrl('AeagFrdBundle_etat_consulterEtatsParAnnee', array('anneeSelect' => $session->get('anneeSelect'))));

        return $this->render('AeagFrdBundle:Etat:consulterEtatsParAnnee.html.twig', array(
                    'user' => $user,
                    'entities' => $entities,
                    'annee' => $anneeSelect
        ));
    }

    public function consulterEtatParAnneeAction($anneeSelect = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'consulterEtatParAnnee');
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

        $session->set('retour', $this->generateUrl('AeagFrdBundle_etat_consulterEtatParAnnee', array('anneeSelect' => $session->get('anneeSelect'))));

        return $this->render('AeagFrdBundle:Etat:consulterEtatParAnnee.html.twig', array(
                    'user' => $user,
                    'correspondant' => $correspondant,
                    'entities' => $entities,
                    'annee' => $anneeSelect
        ));
    }

    public function consulterFraisDeplacementsParEtatAction($etatFraisId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagFrdBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'Frais');
        $session->set('controller', 'Admin');
        $session->set('fonction', 'consulterFraisDeplacementsParEtat');
        $em = $this->get('doctrine')->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoEtatFrais = $emFrd->getRepository('AeagFrdBundle:EtatFrais');
        $repoMandatement = $emFrd->getRepository('AeagFrdBundle:Mandatement');
        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoPhase = $emFrd->getRepository('AeagFrdBundle:Phase');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');

        if ($etatFraisId) {
            $etatFrais = $repoEtatFrais->getEtatFraisById($etatFraisId);
            $correspondant = $repoCorrespondant->getCorrespondantByCorId($etatFrais->getCorId());
            $mandatement = $repoMandatement->getMandatementByEtfrId($etatFrais->getId());
            $fraisDeplacements = $repoFraisDeplacement->getFraisDeplacementByEtfrId($etatFrais->getId());
        } else {
            $etatFrais = null;
            $correspondant = null;
            $mandatement = null;
            $phase = $repoPhase->getPhaseByCode('40');
            $annee1 = $session->get('anneeSelect') . '-01-01';
            $anneeDeb = new \DateTime($annee1);
            $annee1 = $session->get('anneeSelect') . '-12-31';
            $anneeFin = new \DateTime($annee1);
            if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINFRD')) {
                $fraisDeplacements = $repoFraisDeplacement->getFraisDeplacementEnCoursByPhase($phase->getId(), $anneeDeb, $anneeFin);
            } else {
                $correspondant = $repoCorrespondant->getCorrespondantById($user->getCorrespondant());
                $fraisDeplacements = $repoFraisDeplacement->getFraisDeplacementEnCoursByUserPhase($user->getId(), $phase->getId(), $anneeDeb, $anneeFin);
            }
        }

        $i = 0;
        $entities = array();
        foreach ($fraisDeplacements as $fraisDeplacement) {
            // print_r('frais : '. $fraisDeplacement->getid() );
            $entities[$i][0] = $fraisDeplacement;
            $user = $repoUsers->getUserById($fraisDeplacement->getUser());
            $correspondant = $repoCorrespondant->getCorrespondantById($user->getCorrespondant());
            $entities[$i][1] = $user;
            if ($correspondant) {
                $entities[$i][2] = $correspondant;
            } else {
                $entities[$i][2] = null;
            }
            $i++;
        }

        $session->set('retour1', $this->generateUrl('AeagFrdBundle_etat_consulterFraisDeplacementsParEtat', array('etatFraisId' => $etatFraisId)));

        return $this->render('AeagFrdBundle:Etat:consulterFraisDeplacementsParEtat.html.twig', array(
                    'user' => $user,
                    'correspondant' => $correspondant,
                    'etatFrais' => $etatFrais,
                    'mandatement' => $mandatement,
                    'entities' => $entities,
                    'annee' => $session->get('anneeSelect')
        ));
    }

}
