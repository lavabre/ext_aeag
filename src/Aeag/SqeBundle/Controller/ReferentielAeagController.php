<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ReferentielAeagController extends Controller {

    public function pgRefCorresProducteursAction() {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielAeag');
        $session->set('fonction', 'pgRefCorresProducteurs');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgRefCorresProducteur = $emSqe->getRepository('AeagSqeBundle:PgRefCorresProducteur');
        $pgRefCorresProducteurs = $repoPgRefCorresProducteur->getPgRefCorresProducteurs();
        $tabRefCorresProducteurs = array();
        $i = 0;
        $nom_fichier = "producteurs.csv";
        if (count($pgRefCorresProducteurs) > 0) {
            $repertoire = "fichiers";
            $date_import = date('Ymd_His');
            $nom_fichier = "producteurs.csv";
            $fic_import = $repertoire . "/" . $nom_fichier;
            //ouverture fichier
            $fic = fopen($fic_import, "w");
            $contenu = "Identifiant;Nom;Siret;sandre;\n";
            fputs($fic, $contenu);
            foreach ($pgRefCorresProducteurs as $pgRefCorresProducteur) {
                $tabSRefCorresProducteurs[$i]['pgRefCorresProducteur'] = $pgRefCorresProducteur;
                $i++;
                $contenu = $pgRefCorresProducteur->getAncnum() . ";";
                $contenu = $contenu . $pgRefCorresProducteur->getNomCorres() . ";";
                $contenu = $contenu . $pgRefCorresProducteur->getCodeSiret() . ";";
                $contenu = $contenu . $pgRefCorresProducteur->getCodeSandre() . ";\n";
                fputs($fic, $contenu);
            }
            fclose($fic);
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_ref_corres_producteur');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }
        $session->set('retour', $this->generateUrl('aeag_sqe'));
        return $this->render('AeagSqeBundle:Referentiel:Aeag/pgRefCorresProducteurs.html.twig', array('entities' => $tabSRefCorresProducteurs,
                    'fichier' => $nom_fichier));
    }

    public function pgRefCorresPrestatairesAction() {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielAeag');
        $session->set('fonction', 'pgRefCorresPrestataires');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgRefCorresPrestataire = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $pgRefCorresPrestataires = $repoPgRefCorresPrestataire->getPgRefCorresPrestas();
        $tabRefCorresPrestataires = array();
        $i = 0;
        $nom_fichier = "prestataires.csv";
        if (count($pgRefCorresPrestataires) > 0) {
            $repertoire = "fichiers";
            $date_import = date('Ymd_His');
            $nom_fichier = "prestataires.csv";
            $fic_import = $repertoire . "/" . $nom_fichier;
            //ouverture fichier
            $fic = fopen($fic_import, "w");
            $contenu = "Identifiant;Nom;Siret;sandre;\n";
            fputs($fic, $contenu);
            foreach ($pgRefCorresPrestataires as $pgRefCorresPrestataire) {
                $tabSRefCorresPrestataires[$i]['pgRefCorresPrestataire'] = $pgRefCorresPrestataire;
                $i++;
                $contenu = $pgRefCorresPrestataire->getAncnum() . ";";
                $contenu = $contenu . $pgRefCorresPrestataire->getNomCorres() . ";";
                $contenu = $contenu . $pgRefCorresPrestataire->getCodeSiret() . ";";
                $contenu = $contenu . $pgRefCorresPrestataire->getCodeSandre() . ";\n";
                fputs($fic, $contenu);
            }
            fclose($fic);
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_ref_corres_producteur');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }
        $session->set('retour', $this->generateUrl('aeag_sqe'));
        return $this->render('AeagSqeBundle:Referentiel:Aeag/pgRefCorresPrestataires.html.twig', array('entities' => $tabSRefCorresPrestataires,
                    'fichier' => $nom_fichier));
    }

    public function pgRefStationMesuresAction() {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielAeag');
        $session->set('fonction', 'pgRefStationMesures');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');

        $pgRefStationMesures = $repoPgRefStationMesure->getSqeRefStationMesureSitePrelevements();
        $tabStationMesures = array();
        $i = 0;
        $nom_fichier = "stations.csv";
        if ($pgRefStationMesures) {
            $repertoire = "fichiers";
            $date_import = date('Ymd_His');
            $nom_fichier = "stations.csv";
            $fic_import = $repertoire . "/" . $nom_fichier;
            //ouverture fichier
            $fic = fopen($fic_import, "w");
            $contenu = "Code;Libelle;Type;Commune;Cours d'eau;Masse d'eau;\n";
            fputs($fic, utf8_decode($contenu));
            for ($j = 0; $j < count($pgRefStationMesures); $j++) {
                $tabStationMesures[$i]['pgRefStationMesure'] = $pgRefStationMesures[$j];
                $tabStationMesures[$i]['lien'] = '/sqe_fiches_stations/' . $pgRefStationMesures[$j]['code'] . '.pdf';
//                if ($pgRefStationMesures[$j]['type'] == 'STQ') {
//                    $tabStationMesures[$i]['lien'] = 'http://adour-garonne.eaufrance.fr/station/' . $pgRefStationMesures[$j]['code'] . '/print';
//                }
//                if ($pgRefStationMesures[$j]['type'] == 'STQL') {
//                    $tabStationMesures[$i]['lien'] = 'http://adour-garonne.eaufrance.fr/lac/' . $pgRefStationMesures[$j]['code'] . '/print';
//                }
//                if ($pgRefStationMesures[$j]['type'] == 'QZ') {
//                    $tabStationMesures[$i]['lien'] = 'http://www.ades.eaufrance.fr/FichePtEau.aspx?code=' . $pgRefStationMesures[$j]['code'];
//                }
                $tabStationMesures[$i]['nbSitePrelevements'] = $pgRefStationMesures[$j]['nbSites'];
                $tabStationMesures[$i]['commune']['libelle'] = $pgRefStationMesures[$j]['nomCommune'];
                $i++;
                $contenu =$pgRefStationMesures[$j]['code'] . ";";
                $contenu = $contenu . $pgRefStationMesures[$j]['libelle'] . ";";
                $contenu = $contenu . $pgRefStationMesures[$j]['type'] . ";";
                $contenu = $contenu . $pgRefStationMesures[$j]['nomCommune'] . ";";
                $contenu = $contenu . $pgRefStationMesures[$j]['nomCoursEau'] . ";";
                $contenu = $contenu . $pgRefStationMesures[$j]['nomMasdo'] . ";\n";
                fputs($fic, utf8_decode($contenu));
            }
            fclose($fic);
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_ref_station_mesure');
            return $this->redirect($this->generateUrl('sqe_accueil'));
        }

        $session->set('retour', $this->generateUrl('AeagSqeBundle_referentiel_pg_ref_station_mesures'));
        return $this->render('AeagSqeBundle:Referentiel:Aeag/pgRefStationMesures.html.twig', array('entities' => $tabStationMesures,
                    'fichier' => $nom_fichier));
    }

    public function pgRefSationMesurePgRefSitePrelevementsAction($pgRefStationMesureOuvFoncId) {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielAeag');
        $session->set('fonction', 'pgRefSationMesurePgRefSitePrelevements');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgRefSitePrelevement = $emSqe->getRepository('AeagSqeBundle:PgRefSitePrelevement');
        $repoPgSandreSupports = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');


        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($pgRefStationMesureOuvFoncId);

        $tabSitePrelevements = array();
        $i = 0;
        if ($pgRefStationMesure) {
            $pgRefSitePrelevements = $repoPgRefSitePrelevement->getPgRefSitePrelevementByOuvFoncId($pgRefStationMesure->getOuvFoncId());
            if (count($pgRefSitePrelevements) > 0) {
                foreach ($pgRefSitePrelevements as $pgRefSitePrelevement) {
                    $tabSitePrelevements[$i]['pgRefSitePrelevement'] = $pgRefSitePrelevement;
                    $pgSandreSupport = $repoPgSandreSupports->getPgSandreSupportsByCodeSupport($pgRefSitePrelevement->getCodeSupport()->getCodeSupport());
                    $tabSitePrelevements[$i]['pgSandreSupport'] = $pgSandreSupport;
                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de site de prélèvement pour la station de mesure ' . $pgRefStationMesure->getNumero());
                return $this->redirect($this->generateUrl('AeagSqeBundle_referentiel_pg_ref_station_mesures'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Station de mesure  : ' . $pgRefStationMesureOuvFoncId . ' inconnu dans la table : pg_ref_station_mesure');
            return $this->redirect($this->generateUrl('AeagSqeBundle_referentiel_pg_ref_station_mesures'));
        }
        return $this->render('AeagSqeBundle:Referentiel:Aeag/pgRefStationMesurePgRefSitePrelevements.html.twig', array('entities' => $tabSitePrelevements,
                    'pgRefStationMesure' => $pgRefStationMesure));
    }

    public function pgRefStationMesurePgRefReseauMesuresAction($pgRefStationMesureOuvFoncId) {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielAeag');
        $session->set('fonction', 'pgRefStationMesurePgRefReseauMesures');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgRefStationRsx = $emSqe->getRepository('AeagSqeBundle:PgRefStationRsx');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');

        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($pgRefStationMesureOuvFoncId);

        $tabReseauMesures = array();
        $i = 0;
        if ($pgRefStationMesure) {
            $pgRefStationRsxs = $repoPgRefStationRsx->getPgRefStationRsxByStationMesure($pgRefStationMesure);
            if (count($pgRefStationRsxs) > 0) {
                foreach ($pgRefStationRsxs as $pgRefStationRsx) {
                    $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgRefStationRsx->getReseauMesure()->getGroupementId());
                    $tabReseauMesures[$i]['pgRefReseauMesure'] = $pgRefReseauMesure;
                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de réseaux pour la station de mesure ' . $pgRefStationMesure->getNumero());
                return $this->redirect($this->generateUrl('AeagSqeBundle_referentiel_pg_ref_station_mesures'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Station de mesure  : ' . $pgRefStationMesureOuvFoncId . ' inconnu dans la table : pg_ref_station_mesure');
            return $this->redirect($this->generateUrl('AeagSqeBundle_referentiel_pg_ref_station_mesures'));
        }
        return $this->render('AeagSqeBundle:Referentiel:Aeag/pgRefStationMesurePgRefReseauMesures.html.twig', array('entities' => $tabReseauMesures,
                    'pgRefStationMesure' => $pgRefStationMesure));
    }

    public function pgRefReseauMesuresAction() {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielAeag');
        $session->set('fonction', 'pgRefReseauMesures');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoPgRefStationRsx = $emSqe->getRepository('AeagSqeBundle:PgRefStationRsx');

//        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE')) {
//            $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusers();
//        } else {
//            $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByLoginPasswordm($user->getUsername(), $user->getPassword());
//        }
//        $tabReseauMesures = array();
//        $i = 0;
//        if ($pgProgWebuser) {
//            $pgProgWebuserRsxs = $repoPgProgWebuserRsx->getPgProgWebuserRsxByWebuser($pgProgWebuser);
//            if ($pgProgWebuserRsxs) {
//                foreach ($pgProgWebuserRsxs as $pgProgWebuserRsx) {
//                    $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgProgWebuserRsx->getGroupementid());
//                    if ($pgRefReseauMesure) {
//                        $tabReseauMesures[$i] = $pgRefReseauMesure;
//                        $i++;
//                    }
//                }
//            }
//        } else {
//            if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE')) {
//                $session->getFlashBag()->add('notice-warning', 'pas d\'utilisateurs dans la table : pg_prog_webusers');
//            } else {
//                $session->getFlashBag()->add('notice-warning', 'Utilisateur : ' . $user->getUsername() . ' inconnu dans la table : pg_prog_webusers');
//            }
//        }
        $tabReseauMesures = array();
        $i = 0;
        $nom_fichier = "reseaux.csv";
        $pgRefReseauMesures = $repoPgRefReseauMesure->getPgRefReseauMesures();
        if ($pgRefReseauMesures) {
            $repertoire = "fichiers";
            $date_import = date('Ymd_His');
            $nom_fichier = "reseaux.csv";
            $fic_import = $repertoire . "/" . $nom_fichier;
            //ouverture fichier
            $fic = fopen($fic_import, "w");
            $contenu = "Code aeag;code Sandre;Nom;\n";
            fputs($fic, $contenu);
            foreach ($pgRefReseauMesures as $pgRefReseauMesure) {
                $tabReseauMesures[$i]['pgRefReseauMesure'] = $pgRefReseauMesure;
                $pgRefStationRsxs = $repoPgRefStationRsx->getPgRefStationRsxByResauMesure($pgRefReseauMesure);
                $tabReseauMesures[$i]['nbStations'] = count($pgRefStationRsxs);
                $i++;
                $contenu = $pgRefReseauMesure->getCodeAeagRsx() . ";";
                $contenu = $contenu . $pgRefReseauMesure->getCodeSandre() . ";";
                $contenu = $contenu . $pgRefReseauMesure->getNomRsx() . ";\n";
                fputs($fic, $contenu);
            }
            fclose($fic);
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_ref_reseau_mesure');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }
        $session->set('retour', $this->generateUrl('AeagSqeBundle_referentiel_pg_ref_reseau_mesures'));
        return $this->render('AeagSqeBundle:Referentiel:Aeag/pgRefReseauMesures.html.twig', array('entities' => $tabReseauMesures,
                    'fichier' => $nom_fichier));
    }

    public function pgRefReseauMesurePgRefStationMesuresAction($pgRefReseauMesureId) {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielAeag');
        $session->set('fonction', 'pgRefReseauMesurePgRefStationMesures');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgRefStationRsx = $emSqe->getRepository('AeagSqeBundle:PgRefStationRsx');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgRefSitePrelevement = $emSqe->getRepository('AeagSqeBundle:PgRefSitePrelevement');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');

        $pgRefReseauMesure = $repoPgRefReseauMesure->getPgRefReseauMesureByGroupementId($pgRefReseauMesureId);

        $tabStationMesures = array();
        $i = 0;
        if ($pgRefReseauMesure) {
            $pgRefStationRsxs = $repoPgRefStationRsx->getPgRefStationRsxByResauMesure($pgRefReseauMesure);
            if ($pgRefStationRsxs) {
                foreach ($pgRefStationRsxs as $pgRefStationRsx) {
                    $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($pgRefStationRsx->getStationMesure()->getOuvFoncId());
                    $tabStationMesures[$i]['pgRefStationMesure'] = $pgRefStationMesure;
                    $tabStationMesures[$i]['lien'] = '/sqe_fiches_stations/' . $pgRefStationMesure->getCode() . '.pdf';
//                    if ($pgRefStationMesure->getType() == 'STQ') {
//                        $tabStationMesures[$i]['lien'] = 'http://adour-garonne.eaufrance.fr/station/' . $pgRefStationMesure->getCode() . '/print';
//                    }
//                    if ($pgRefStationMesure->getType() == 'STQL') {
//                        $tabStationMesures[$i]['lien'] = 'http://adour-garonne.eaufrance.fr/lac/' . $pgRefStationMesure->getCode() . '/print';
//                    }
//                    if ($pgRefStationMesure->getType() == 'QZ') {
//                        $tabStationMesures[$i]['lien'] = 'http://www.ades.eaufrance.fr/FichePtEau.aspx?code=' . $pgRefStationMesure->getCode();
//                    }
                    $nbPgRefSitePrelevements = $repoPgRefSitePrelevement->getNbPgRefSitePrelevementByOuvFoncId($pgRefStationMesure->getOuvFoncId());
                    $tabStationMesures[$i]['nbSitePrelevements'] = $nbPgRefSitePrelevements;
                    $inseeCommune = sprintf("%05d", $pgRefStationMesure->getInseeCommune());
                    $commune = $repoCommune->getCommuneByCommune($inseeCommune);
                    $tabStationMesures[$i]['commune']['libelle'] = $commune->getLibelle();

                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de stations pour le réseau de mesure ' . $pgRefReseauMesure->getNomRsx());
                return $this->redirect($session->get('retour'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'réseau de mesure  : ' . $pgRefReseauMesureId . ' inconnu dans la table : pg_ref_reseau_mesure');
            return $this->redirect($session->get('retour'));
        }
        return $this->render('AeagSqeBundle:Referentiel:Aeag/pgRefReseauMesurePgRefStationMesures.html.twig', array('entities' => $tabStationMesures,
                    'pgRefReseauMesure' => $pgRefReseauMesure));
    }

    public function pgRefReseauMesurePgRefSationMesurePgRefSitePrelevementsAction($pgRefReseauMesureId, $pgRefStationMesureOuvFoncId) {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielAeag');
        $session->set('fonction', 'pgRefReseauMesurePgRefSationMesurePgRefSitePrelevements');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoPgRefSitePrelevement = $emSqe->getRepository('AeagSqeBundle:PgRefSitePrelevement');
        $repoPgSandreSupports = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');


        $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($pgRefStationMesureOuvFoncId);

        $tabSitePrelevements = array();
        $i = 0;
        if ($pgRefStationMesure) {
            $pgRefSitePrelevements = $repoPgRefSitePrelevement->getPgRefSitePrelevementByOuvFoncId($pgRefStationMesure->getOuvFoncId());
            if (count($pgRefSitePrelevements) > 0) {
                foreach ($pgRefSitePrelevements as $pgRefSitePrelevement) {
                    $tabSitePrelevements[$i]['pgRefSitePrelevement'] = $pgRefSitePrelevement;
                    $pgSandreSupport = $repoPgSandreSupports->getPgSandreSupportsByCodeSupport($pgRefSitePrelevement->getCodeSupport()->getCodeSupport());
                    $tabSitePrelevements[$i]['pgSandreSupport'] = $pgSandreSupport;
                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de site de prélèvement pour la station de mesure ' . $pgRefStationMesure->getNumero());
                return $this->redirect($this->generateUrl('AeagSqeBundle_referentiel_pg_ref_reseau_mesure_pg_ref_station_mesures', array('pgRefReseauMesureId' => $pgRefReseauMesureId)));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Station de mesure  : ' . $pgRefStationMesureOuvFoncId . ' inconnu dans la table : pg_ref_station_mesure');
            return $this->redirect($this->generateUrl('AeagSqeBundle_referentiel_pg_ref_reseau_mesure_pg_ref_station_mesures', array('pgRefReseauMesureId' => $pgRefReseauMesureId)));
        }
        return $this->render('AeagSqeBundle:Referentiel:Aeag/pgRefReseauMesurePgRefStationMesurePgRefSitePrelevements.html.twig', array('entities' => $tabSitePrelevements,
                    'pgRefReseauMesureId' => $pgRefReseauMesureId,
                    'pgRefStationMesure' => $pgRefStationMesure));
    }

    public function pgRefSitePrelevementsAction() {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielAeag');
        $session->set('fonction', 'pgRefSitePrelevements');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgRefSitePrelevement = $emSqe->getRepository('AeagSqeBundle:PgRefSitePrelevement');
        $tabSitePrelevements = array();
        $i = 0;
        $pgRefSitePrelevements = $repoPgRefSitePrelevement->getPgRefSitePrelevements();
        if ($pgRefSitePrelevements) {
            foreach ($pgRefSitePrelevements as $pgRefSitePrelevement) {
                $tabSitePrelevements[$i]['pgRefSitePrelevement'] = $pgRefSitePrelevement;
                $nbStationMesures = $repoPgRefSitePrelevement->getNbPgRefSitePrelevementByCodeSite($pgRefSitePrelevement['codeSite']);
                $tabSitePrelevements[$i]['nbStationMesures'] = $nbStationMesures;
                $i++;
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_ref_site_prelevement');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }
        $session->set('retour', $this->generateUrl('AeagSqeBundle_referentiel_pg_ref_site_prelevement'));
        return $this->render('AeagSqeBundle:Referentiel:Aeag/pgRefSitePrelevements.html.twig', array('entities' => $tabSitePrelevements));
    }

    public function pgRefSitePrelevementPgRefStationMesuresAction($pgRefSitePrelevementCode = null, $pgRefSitePrelevementNom = null) {

        $user = $this->getUser();
         if (!$user) {
             return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielAeag');
        $session->set('fonction', 'pgRefSitePrelevementPgRefStationMesures');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgRefSitePrelevement = $emSqe->getRepository('AeagSqeBundle:PgRefSitePrelevement');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');

        $pgRefSitePrelevements = $repoPgRefSitePrelevement->getPgRefSitePrelevementByCodeSite($pgRefSitePrelevementCode);
        $tabStationMesures = array();
        $i = 0;
        if (count($pgRefSitePrelevements) > 0) {
            foreach ($pgRefSitePrelevements as $pgRefSitePrelevement) {
                $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($pgRefSitePrelevement->getOuvFonc()->getOuvFoncId());
                $tabStationMesures[$i]['pgRefStationMesure'] = $pgRefStationMesure;
                $inseeCommune = sprintf("%05d", $pgRefStationMesure->getInseeCommune());
                $commune = $repoCommune->getCommuneByCommune($inseeCommune);
                $tabStationMesures[$i]['commune'] = $commune;
                $i++;
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas de stations de mesure pour le site de prélèvement  : ' . $pgRefSitePrelevementCode . ' ' . $pgRefSitePrelevementNom);
            return $this->redirect($this->generateUrl('AeagSqeBundle_referentiel_pg_ref_site_prelevement'));
        }
        return $this->render('AeagSqeBundle:Referentiel:Aeag/pgRefSitePrelevementPgRefStationMesures.html.twig', array('entities' => $tabStationMesures,
                    'pgRefSitePrelevementCode' => $pgRefSitePrelevementCode,
                    'pgRefSitePrelevementNom' => $pgRefSitePrelevementNom));
    }

}
