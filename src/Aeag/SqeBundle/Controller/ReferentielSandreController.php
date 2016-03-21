<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ReferentielSandreController extends Controller {

    public function pgSandreParametresAction() {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielSandre');
        $session->set('fonction', 'pgSandreParametres');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgSandreParametres = $emSqe->getRepository('AeagSqeBundle:PgSandreParametres');
        $pgSandreParametres = $repoPgSandreParametres->getPgSandreParametres();
        $tabSandreParametres = array();
        $i = 0;
        $nom_fichier = "parametres.csv";
        if (count($pgSandreParametres) > 0) {
            $repertoire = "fichiers";
            $date_import = date('Ymd_His');
            $nom_fichier = "parametres.csv";
            $fic_import = $repertoire . "/" . $nom_fichier;
            //ouverture fichier
            $fic = fopen($fic_import, "w");
            $contenu = "Code;Nom;Libellé court;Type;Cas;\n";
            fputs($fic, utf8_decode($contenu));
            foreach ($pgSandreParametres as $pgSandreParametre) {
                $tabSandreParametres[$i]['pgSandreParametre'] = $pgSandreParametre;
                $i++;
                $contenu = $pgSandreParametre->getCodeParametre() . ";";
                $contenu = $contenu . $pgSandreParametre->getNomParametre() . ";";
                $contenu = $contenu . $pgSandreParametre->getLibelleCourt() . ";";
                $contenu = $contenu . $pgSandreParametre->getTypeParametre() . ";";
                $contenu = $contenu . $pgSandreParametre->getCodeCas() . ";\n";
                fputs($fic, utf8_decode($contenu));
            }
            fclose($fic);
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_sandre_parametres');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }

        $session->set('retour', $this->generateUrl('aeag_sqe'));
        return $this->render('AeagSqeBundle:Referentiel:Sandre/pgSandreParametres.html.twig', array('entities' => $tabSandreParametres,
                    'fichier' => $nom_fichier));
    }

    public function pgSandreSupportsAction() {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'referentielSandre');
        $session->set('fonction', 'pgSandreSupports');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgSandreSupports = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');

        $pgSandreSupports = $repoPgSandreSupports->getPgSandreSupports();
        $tabSandreSupports = array();
        $i = 0;
        $nom_fichier = "supports.csv";
        if (count($pgSandreSupports) > 0) {
            $repertoire = "fichiers";
            $date_import = date('Ymd_His');
            $nom_fichier = "supports.csv";
            $fic_import = $repertoire . "/" . $nom_fichier;
            //ouverture fichier
            $fic = fopen($fic_import, "w");
            $contenu = "Code;Nom;\n";
            fputs($fic, utf8_decode($contenu));
            foreach ($pgSandreSupports as $pgSandreSupport) {
                $tabSandreSupports[$i]['pgSandreSupport'] = $pgSandreSupport;
                $i++;
                $contenu = $pgSandreSupport->getCodeSupport() . ";";
                $contenu = $contenu . $pgSandreSupport->getNomSupport() . ";\n";
                fputs($fic, utf8_decode($contenu));
            }
            fclose($fic);
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_sandre_supports');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }
        $session->set('retour', $this->generateUrl('aeag_sqe'));
        return $this->render('AeagSqeBundle:Referentiel:Sandre/pgSandreSupports.html.twig', array('entities' => $tabSandreSupports,
                    'fichier' => $nom_fichier));
    }

    public function pgSandreSupportsFractionsAction($support = null) {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'referentielSandre');
        $session->set('fonction', 'pgSandreSupportsFractions');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgSandreSupports = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');

        $pgSandreSupport = $repoPgSandreSupports->getPgSandreSupportsByCodeSupport($support);
        if ($pgSandreSupport) {
            $tabSandreFractions = array();
            $i = 0;
            $pgSandreFractions = $repoPgSandreFractions->getPgSandreFractionsByCodeSupport($pgSandreSupport);
            if (count($pgSandreFractions) > 0) {
                foreach ($pgSandreFractions as $pgSandreFraction) {
                    $tabSandreFractions[$i]['pgSandreFraction'] = $pgSandreFraction;
                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de fractions pour le support ' . $pgSandreSupport->getNomSupport() . '  dans la table pg_sandre_fractions');
                return $this->redirect($this->generateUrl('AeagSqeBundle_referentiel_sandre_supports'));
            }
            $session->set('retour', $this->generateUrl('AeagSqeBundle_referentiel_sandre_supports'));
            return $this->render('AeagSqeBundle:Referentiel:Sandre/pgSandreSupportsFractions.html.twig', array('entities' => $tabSandreFractions,
                        'support' => $pgSandreSupport));
        } else {
            $session->getFlashBag()->add('notice-warning', 'Support  : ' . $support . ' inconnu dans la table : pg_sandre_support');
            return $this->redirect($this->generateUrl('AeagSqeBundle_referentiel_sandre_supports'));
        }
    }

    public function pgSandreFractionsAction() {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'referentielSandre');
        $session->set('fonction', 'pgSandreFractions');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');

        $pgSandreFractions = $repoPgSandreFractions->getPgSandreFractions();
        if (count($pgSandreFractions) > 0) {
            $tabSandreFractions = array();
            $i = 0;
            foreach ($pgSandreFractions as $pgSandreFraction) {
                $tabSandreFractions[$i]['pgSandreFraction'] = $pgSandreFraction;
                $i++;
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_sandre_fractions');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }
        $session->set('retour', $this->generateUrl('aeag_sqe'));
        return $this->render('AeagSqeBundle:Referentiel:Sandre/pgSandreFractions.html.twig', array('entities' => $tabSandreFractions));
    }

    public function pgSandreUnitesAction() {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'referentielSandre');
        $session->set('fonction', 'pgSandreUnites');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');

        $pgSandreUnites = $repoPgSandreUnites->getPgSandreUnites();
        $tabSandreUnites = array();
        $i = 0;
        $nom_fichier = "unites.csv";
        if (count($pgSandreUnites) > 0) {
            $repertoire = "fichiers";
            $date_import = date('Ymd_His');
            $nom_fichier = "unites.csv";
            $fic_import = $repertoire . "/" . $nom_fichier;
            //ouverture fichier
            $fic = fopen($fic_import, "w");
            $contenu = "Code;Nom;\n";
            fputs($fic, utf8_decode($contenu));
            foreach ($pgSandreUnites as $pgSandreUnite) {
                $tabSandreUnites[$i]['pgSandreUnite'] = $pgSandreUnite;
                $i++;
                $contenu = $pgSandreUnite->getCodeUnite() . ";";
                $contenu = $contenu . $pgSandreUnite->getNomUnite() . ";\n";
                fputs($fic, utf8_decode($contenu));
            }
            fclose($fic);
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_sandre_unites');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }
        $session->set('retour', $this->generateUrl('aeag_sqe'));
        return $this->render('AeagSqeBundle:Referentiel:Sandre/pgSandreUnites.html.twig', array('entities' => $tabSandreUnites,
                    'fichier' => $nom_fichier));
    }

    public function pgSandreMethodesAction() {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'referentielSandre');
        $session->set('fonction', 'pgSandreMethodes');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgSandreMethodes = $emSqe->getRepository('AeagSqeBundle:PgSandreMethodes');

        $pgSandreMethodes = $repoPgSandreMethodes->getPgSandreMethodes();
        $tabSandreMethodes = array();
        $i = 0;
        $nom_fichier = "methodes.csv";
        if (count($pgSandreMethodes) > 0) {
            $repertoire = "fichiers";
            $date_import = date('Ymd_His');
            $nom_fichier = "methodes.csv";
            $fic_import = $repertoire . "/" . $nom_fichier;
            //ouverture fichier
            $fic = fopen($fic_import, "w");
            $contenu = "Code;Nom;\n";
            fputs($fic, utf8_decode($contenu));
            foreach ($pgSandreMethodes as $pgSandreMethode) {
                $tabSandreMethodes[$i]['pgSandreMethode'] = $pgSandreMethode;
                $i++;
                $contenu = $pgSandreMethode->getCodeMethode() . ";";
                $contenu = $contenu . $pgSandreMethode->getNomMethode() . ";\n";
                fputs($fic, utf8_decode($contenu));
            }
            fclose($fic);
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_sandre_methodes');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }
        $session->set('retour', $this->generateUrl('aeag_sqe'));
        return $this->render('AeagSqeBundle:Referentiel:Sandre/pgSandreMethodes.html.twig', array('entities' => $tabSandreMethodes,
                    'fichier' => $nom_fichier));
    }

    public function pgSandreZoneVerticaleProspecteesAction() {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'referentielSandre');
        $session->set('fonction', 'pgSandreZoneVerticaleProspectees');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgSandreZoneVerticaleProspectee = $emSqe->getRepository('AeagSqeBundle:PgSandreZoneVerticaleProspectee');

        $pgSandreZoneVerticaleProspectees = $repoPgSandreZoneVerticaleProspectee->getPgSandreZoneVerticaleProspectees();
        $tabSandreZoneVerticaleProspectees = array();
        $i = 0;
        $nom_fichier = "zonesverticalesprospectees.csv";
        if (count($pgSandreZoneVerticaleProspectees) > 0) {
            $repertoire = "fichiers";
            $date_import = date('Ymd_His');
            $nom_fichier = "zonesverticalesprospectees.csv";
            $fic_import = $repertoire . "/" . $nom_fichier;
            //ouverture fichier
            $fic = fopen($fic_import, "w");
            $contenu = "Code;Nom;\n";
            fputs($fic, utf8_decode($contenu));
            foreach ($pgSandreZoneVerticaleProspectees as $pgSandreZoneVerticaleProspectee) {
                $tabSandreZoneVerticaleProspectees[$i]['pgSandreZoneVerticaleProspectee'] = $pgSandreZoneVerticaleProspectee;
                $i++;
                $contenu = $pgSandreZoneVerticaleProspectee->getCodeZone() . ";";
                $contenu = $contenu . $pgSandreZoneVerticaleProspectee->getNomZone() . ";\n";
                fputs($fic, utf8_decode($contenu));
            }
            fclose($fic);
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_sandre_zone_verticlae_prospectee');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }
        $session->set('retour', $this->generateUrl('aeag_sqe'));
        return $this->render('AeagSqeBundle:Referentiel:Sandre/pgSandreZoneVerticaleProspectees.html.twig', array('entities' => $tabSandreZoneVerticaleProspectees,
                    'fichier' => $nom_fichier));
    }

}
