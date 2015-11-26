<?php

namespace Aeag\AeagBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Aeag\AeagBundle\Form\MajDecType;
use Aeag\AeagBundle\Form\EnvoyerMessageType;
use Aeag\AeagBundle\Form\MajOuvrageType;
use Aeag\AeagBundle\Form\DocumentType;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\AeagBundle\Entity\Document;
use Aeag\AeagBundle\Entity\Form\EnvoyerMessage;
use Aeag\AeagBundle\Entity\Form\MajOuvrage;
use Aeag\AeagBundle\DependencyInjection\PdfListeRegion;
use Aeag\AeagBundle\DependencyInjection\PdfListeDepartement;
use Aeag\AeagBundle\DependencyInjection\PdfListeCommune;

class ReferentielController extends Controller {

    public function listeRegionAction() {

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $session->set('menu', 'referentiel');
        $session->set('referentiel', 'Region');

        $repoRegion = $em->getRepository('AeagAeagBundle:Region');

        $entities = $repoRegion->getRegions();

        return $this->render('AeagAeagBundle:Referentiel:listeRegion.html.twig', array(
                    'entities' => $entities
        ));
    }

    public function pdfListeRegionAction() {

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $session->set('referentiel', 'Region');

        $repoRegion = $em->getRepository('AeagAeagBundle:Region');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $entities = $repoRegion->getRegions();
        } else {
            $entities = $repoRegion->getRegionsByDec();
        }
        $pdf = new PdfListeRegion('P', 'mm', 'A4');
        $titre = 'Liste des régionss';
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup();
        $pdf->AddPage($entities);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($entities);
        $fichier = 'AEAG_REGIONS.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagAeagBundle:Referentiel:pdf.html.twig');
    }

    public function editerRegionAction($reg = null, Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $repoRegion = $em->getRepository('AeagAeagBundle:Region');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');
        $repoCodePostal = $em->getRepository('AeagAeagBundle:CodePostal');
        $region = $repoRegion->getRegionByReg($reg);
        $form = $this->createForm(new MajDecType(), $region);
        $maj = false;
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($region);
                $departements = $repoDepartement->getDepartementsByRegion($region->getReg());
                foreach ($departements as $departement) {
                    $departement->setDec($region->getDec());
                    $em->persist($departement);
                    $communes = $repoCommune->getCommuneByDept($departement->getDept());
                    foreach ($communes as $commune) {
                        $commune->setDec($departement->getDec());
                        $em->persist($commune);
                        $codePostals = $repoCodePostal->getCodePostalByCommune($commune->getid());
                        foreach ($codePostals as $codePostal) {
                            $codePostal->setDec($commune->getDec());
                            $em->persist($codePostal);
                        }
                    }
                }

                $em->flush();
                $maj = true;
                $session->getFlashBag()->add('notice-success', "La région " . $region->getReg() . ' ' . $region->getlibelle() . " a été modifiée !");
                return $this->redirect($this->generateUrl('Aeag_listeRegion'));
            }
        }

        return $this->render('AeagAeagBundle:Referentiel:editerRegion.html.twig', array(
                    'form' => $form->createView(),
                    'region' => $region,
                    'maj' => $maj
        ));
    }

    public function listeDepartementAction($reg = null) {

        $session = $this->get('session');
        $security = $this->get('security.authorization_checker');
        $em = $this->getDoctrine()->getManager();
        $session->set('menu', 'referentiel');
        $session->set('referentiel', 'Departement');
        $repoRegion = $em->getRepository('AeagAeagBundle:Region');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        if ($reg) {
            $region = $repoRegion->getRegionByReg($reg);
            $entities = $repoDepartement->getDepartementsByRegion($reg);
        } else {
            $entities = $repoDepartement->getDepartements();
            $region = null;
        }

        return $this->render('AeagAeagBundle:Referentiel:listeDepartement.html.twig', array(
                    'entities' => $entities,
                    'region' => $region
        ));
    }

    public function pdfListeDepartementAction($reg = null) {

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $session->set('referentiel', 'Departement');
        $repoRegion = $em->getRepository('AeagAeagBundle:Region');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $region = $repoRegion->getRegionByReg($reg);
        $entities = $repoDepartement->getDepartements();
        $entities = $repoDepartement->getDepartementsByRegion($reg);
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ODEC')) {
            $entities = $repoDepartement->getDepartementsByRegionDec($reg);
       }
          

        $pdf = new PdfListeDepartement('P', 'mm', 'A4');
        $titre = 'Liste des départements';
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup($region);
        $pdf->AddPage($region);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($region, $entities);
        $fichier = 'AEAG_DEPARTEMENTS.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagAeagBundle:Referentiel:pdf.html.twig');
    }

    public function editerDepartementAction($dept = null, Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $repoRegion = $em->getRepository('AeagAeagBundle:Region');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $departement = $repoDepartement->getDepartementByDept($dept);
        $region = $repoRegion->getRegionByReg($departement->getRegion()->getReg());
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');
        $repoCodePostal = $em->getRepository('AeagAeagBundle:CodePostal');
        $form = $this->createForm(new MajDecType(), $departement);
        $maj = false;
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($departement);
                $communes = $repoCommune->getCommuneByDept($departement->getDept());
                foreach ($communes as $commune) {
                    $commune->setDec($departement->getDec());
                    $em->persist($commune);
                    $codePostals = $repoCodePostal->getCodePostalByCommune($commune->getid());
                    foreach ($codePostals as $codePostal) {
                        $codePostal->setDec($commune->getDec());
                        $em->persist($codePostal);
                    }
                }
                $em->flush();
                $maj = true;
                $session->getFlashBag()->add('notice-success', "Le département " . $departement->getDept() . ' ' . $departement->getlibelle() . " a été modifié !");
                return $this->redirect($this->generateUrl('Aeag_listeDepartement', array('reg' => $departement->getRegion()->getReg())));
            }
        }

        return $this->render('AeagAeagBundle:Referentiel:editerDepartement.html.twig', array(
                    'form' => $form->createView(),
                    'region' => $region,
                    'departement' => $departement,
                    'maj' => $maj
        ));
    }

    public function listeCommuneAction($dept = null) {

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $session->set('menu', 'referentiel');
        $session->set('referentiel', 'Commune');

        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $departement = $repoDepartement->getDepartementByDept($dept);
        $entities = $repoCommune->getCommuneByDept($departement->getDept());

        return $this->render('AeagAeagBundle:Referentiel:listeCommune.html.twig', array(
                    'entities' => $entities,
                    'departement' => $departement
        ));
    }

    public function pdfListeCommuneAction($dept = null) {

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        $session->set('referentiel', 'Departement');

        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');

        $departement = $repoDepartement->getDepartementByDept($dept);
        $communes = $repoCommune->getCommuneByDept($departement->getDept());
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ODEC')) {
            $communes = $repoCommune->getCommuneByDeptDec($departement->getDept());
        }
        $pdf = new PdfListeCommune('P', 'mm', 'A4');
        $titre = 'Liste des communes du département ' . $departement->getdept() . ' ' . $departement->getlibelle();
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup();
        $pdf->AddPage($departement);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($departement, $communes);
        $fichier = 'AEAG_COMMUNES.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagAeagBundle:Referentiel:pdf.html.twig');
    }

    public function editerCommuneAction($id = null, Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $repoCodePostal = $em->getRepository('AeagAeagBundle:CodePostal');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');
        $commune = $repoCommune->getCommuneById($id);
        $form = $this->createForm(new MajDecType(), $commune);
        $maj = false;
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($commune);
                $codePostals = $repoCodePostal->getCodePostalByCommune($commune->getid());
                foreach ($codePostals as $codePostal) {
                    $codePostal->setDec($commune->getDec());
                    $em->persist($codePostal);
                }
                $em->flush();
                $maj = true;
                $session->getFlashBag()->add('notice-success', "La commune " . $commune->getCommune() . ' ' . $commune->getlibelle() . " a été modifié !");
                return $this->redirect($this->generateUrl('Aeag_listeCommune', array('dept' => $commune->getDepartement()->getDept())));
            }
        }

        return $this->render('AeagAeagBundle:Referentiel:editerCommune.html.twig', array(
                    'form' => $form->createView(),
                    'commune' => $commune,
                    'maj' => $maj
        ));
    }

    public function listeCodePostalAction($commune = null) {

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $session->set('menu', 'referentiel');
        $session->set('referentiel', 'CodePostal');
        $repoCodePostal = $em->getRepository('AeagAeagBundle:CodePostal');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');
        $commune = $repoCommune->getCommuneById($commune);
        $entities = $repoCodePostal->getCodePostalByCommune($commune->getid());

        return $this->render('AeagAeagBundle:Referentiel:listeCodePostal.html.twig', array(
                    'entities' => $entities,
                    'commune' => $commune
        ));
    }

    public function editerCodePostalAction($id = null, Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $repoCodePostal = $em->getRepository('AeagAeagBundle:CodePostal');
        $codePostal = $repoCodePostal->getCodePostalById($id);
        $form = $this->createForm(new MajDecType(), $codePostal);
        $maj = false;
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($codePostal);
                $em->flush();
                $maj = true;
                $session->getFlashBag()->add('notice-success', "Le code postal " . $codePostal->getCp() . ' ' . $codePostal->getAcheminement() . " a été modifié !");
                return $this->redirect($this->generateUrl('Aeag_listeCodePostal', array('commune' => $codePostal->getCommune()->getId())));
            }
        }

        return $this->render('AeagAeagBundle:Referentiel:editerCodePostal.html.twig', array(
                    'form' => $form->createView(),
                    'codePostal' => $codePostal,
                    'maj' => $maj
        ));
    }

    public function listeOuvrageAction() {

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $session->set('menu', 'referentiel');
        $session->set('referentiel', 'Ouvrage');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');

        $entities = $repoOuvrage->getOuvrages();

        return $this->render('AeagAeagBundle:Referentiel:listeOuvrage.html.twig', array(
                    'entities' => $entities
        ));
    }

    public function listeCorrespondantsAction() {

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $session->set('menu', 'acteurs');
        $session->set('referentiel', 'RefCentre');

        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');

        $entities = $repoCorrespondant->getCorrespondants();

        return $this->render('AeagAeagBundle:Referentiel:listeCorrespondants.html.twig', array(
                    'entities' => $entities
        ));
    }

    public function listeOuvrageCorrespondantAction($ouvrage) {

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $session->set('menu', 'acteurs');
        $session->set('referentiel', 'OuvrageCorrespondant');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');

        $Ouvrage = $repoOuvrage->getOuvrageByNumero($ouvrage);
        $entities = $repoOuvrageCorrespondant->getOuvrageCorrespondantByOuvrage($Ouvrage->getId());

        return $this->render('AeagAeagBundle:Referentiel:listeOuvrageCorrespondant.html.twig', array(
                    'ouvrage' => $Ouvrage,
                    'entities' => $entities
        ));
    }

    public function listeCorrespondantOuvrageAction($correspondant) {

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $session->set('menu', 'referentiel');
        $session->set('referentiel', 'OuvrageCorrespondant');

        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');

        $Correspondant = $repoCorrespondant->getCorrespondantByid($correspondant);
        $entities = $repoOuvrageCorrespondant->getOuvrageCorrespondantByCorrespondant($Correspondant->getId());

        return $this->render('AeagAeagBundle:Referentiel:listeCorrespondantOuvrage.html.twig', array(
                    'correspondant' => $Correspondant,
                    'entities' => $entities
        ));
    }

    public function consulterCorrespondantAction($id = null) {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $session = $this->get('session');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');

        $correspondant = $repoCorrespondant->getCorrespondantById($id);

        if (!$correspondant) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement : ' . $id);
        }

        $odecs = $repoOuvrageCorrespondant->getOuvrageByCorrespondantType($correspondant->getId(), 'ODEC');
        $ctdts = $repoOuvrageCorrespondant->getOuvrageByCorrespondantType($correspondant->getId(), 'CTDT');
        $cts = $repoOuvrageCorrespondant->getOuvrageByCorrespondantType($correspondant->getId(), 'CT');
        $pdecs = $repoOuvrageCorrespondant->getOuvrageByCorrespondantType($correspondant->getId(), 'PDEC');

        return $this->render('AeagAeagBundle:Referentiel:consulterCorrespondant.html.twig', array(
                    'correspondant' => $correspondant,
                    'odecs' => $odecs,
                    'ctdts' => $ctdts,
                    'cts' => $cts,
                    'pdecs' => $pdecs,
        ));
    }

    public function majOuvrageAction($id = null, Request $request) {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');

        $ouvrage = $repoOuvrage->getOuvrageById($id);

        if (!$ouvrage) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement : ' . $id);
        }

        $majOuvrage = clone($ouvrage);
        $form = $this->createForm(new MajOuvrageType(), $majOuvrage);
        $maj = false;
        $message = null;
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $ouvrage->setNumero($majOuvrage->getNumero());
                $ouvrage->setSiret($majOuvrage->getSiret());
                $ouvrage->setLibelle($majOuvrage->getLibelle());
                $ouvrage->setDec($majOuvrage->getDec());
                $em->persist($ouvrage);
                $em->flush();
                $session->getFlashBag()->add('notice-success', "l'ouvrage " . $ouvrage->getNumero() . " " . $ouvrage->getLibelle() . "  a été modifié.");
                $maj = true;
                return $this->redirect($session->get('retour'));
            }
        }

        return $this->render('AeagAeagBundle:Referentiel:majOuvrage.html.twig', array(
                    'form' => $form->createView(),
                    'ouvrage' => $ouvrage,
                    'maj' => $maj,
                    'message' => $message
        ));
    }

}
