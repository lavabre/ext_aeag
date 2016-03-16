<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Aeag\AeagBundle\Entity\Form\EnvoyerMessage;
use Aeag\AeagBundle\Entity\Form\EnvoyerMessageAll;

class ReferentielProgrammationController extends Controller {

    public function typeMilieuxAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'typeMilieux');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgTypeMilieu = $emSqe->getRepository('AeagSqeBundle:PgProgTypeMilieu');
        $tabTypeMilieux = array();
        $i = 0;
        $pgProgTypeMilieux = $repoPgProgTypeMilieu->getPgProgTypeMilieux();
        if ($pgProgTypeMilieux) {
            foreach ($pgProgTypeMilieux as $pgProgTypeMilieu) {
                $tabTypeMilieux[$i] = $pgProgTypeMilieu;
                $i++;
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_prog_type_milieu');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }

        $session->set('niveau1', $this->generateUrl('AeagSqeBundle_referentiel_typeMilieux'));

        return $this->render('AeagSqeBundle:Referentiel:Programmation/typeMilieux.html.twig', array('entities' => $tabTypeMilieux));
    }

    public function typeMilieuTypePeriodesAction($progTypeMilieuCode = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'typeMilieuTypePeriodes');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgTypeMilieu = $emSqe->getRepository('AeagSqeBundle:PgProgTypeMilieu');
        $repoPgProgPeriode = $emSqe->getRepository('AeagSqeBundle:PgProgPeriodes');

        $pgProgTypeMilieu = $repoPgProgTypeMilieu->getPgProgTypeMilieuByCodeMilieu($progTypeMilieuCode);

        $tabPeriodes = array();
        $i = 0;
        if ($pgProgTypeMilieu) {
            if ($pgProgTypeMilieu->getTypePeriode()) {
                $pgProgTypePeriode = $pgProgTypeMilieu->getTypePeriode();
                $pgProgPeriodes = $repoPgProgPeriode->getPgProgPeriodesByTypePeriode($pgProgTypePeriode);
                foreach ($pgProgPeriodes as $pgProgPeriode) {
                    $tabTypePeriodes[$i]['pgProgPeriode'] = $pgProgPeriode;
                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de types de période pour la type de milieu ' . $pgProgTypeMilieu->getNomMilieu());
                return $this->redirect($session->get('niveau1'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Type de milieu  : ' . $progTypeMilieuCode . ' inconnu dans la table : pg_prog_type_milieu');
            return $this->redirect($session->get('niveau1'));
        }

        return $this->render('AeagSqeBundle:Referentiel:Programmation/typeMilieuTypePeriodes.html.twig', array('entities' => $tabTypePeriodes,
                    'pgProgTypeMilieu' => $pgProgTypeMilieu));
    }

    public function zoneGeographiquesAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'zoneGeographiques');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgZoneGeoRef = $emSqe->getRepository('AeagSqeBundle:PgProgZoneGeoRef');

        $tabZoneGeoRefs = array();
        $i = 0;
        $pgProgZoneGeoRefs = $repoPgProgZoneGeoRef->getPgProgZoneGeoRefs();
        if ($pgProgZoneGeoRefs) {
            foreach ($pgProgZoneGeoRefs as $pgProgZoneGeoRef) {
                $tabZoneGeoRefs[$i] = $pgProgZoneGeoRef;
                $i++;
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_prog_zone_geo_ref');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }
        $session->set('niveau1', $this->generateUrl('AeagSqeBundle_referentiel_zoneGeographiques'));
        return $this->render('AeagSqeBundle:Referentiel:Programmation/zoneGeographiques.html.twig', array('entities' => $tabZoneGeoRefs));
    }

    public function zoneGeographiqueStationsAction($progZoneGeoRefId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'zoneGeographiqueStations');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgZoneGeoRef = $emSqe->getRepository('AeagSqeBundle:PgProgZoneGeoRef');
        $repoPgProgZgeorrefStation = $emSqe->getRepository('AeagSqeBundle:PgProgZgeorefStation');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');
        $repoPgRefSitePrelevement = $emSqe->getRepository('AeagSqeBundle:PgRefSitePrelevement');

        $pgProgZoneGeoRef = $repoPgProgZoneGeoRef->getPgProgZoneGeoRefById($progZoneGeoRefId);

        $tabStationMesures = array();
        $i = 0;
        if ($pgProgZoneGeoRef) {
            $pgProgZgeorrefStations = $repoPgProgZgeorrefStation->getpgProgZgeorefStationByZgeoref($pgProgZoneGeoRef);
            if (count($pgProgZgeorrefStations) > 0) {
                foreach ($pgProgZgeorrefStations as $pgProgZgeorrefStation) {
                    $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($pgProgZgeorrefStation->getStationMesure()->getOuvFoncId());
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
                $session->getFlashBag()->add('notice-warning', 'pas de stations pour la zone géographique ' . $pgProgZoneGeoRef->getNomZoneGeo());
                return $this->redirect($session->get('niveau1'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'réseau de mesure  : ' . $pgRefReseauMesureId . ' inconnu dans la table : pg_ref_reseau_mesure');
            return $this->redirect($sesion->get('niveau1'));
        }

        $session->set('niveau2', $this->generateUrl('AeagSqeBundle_referentiel_zoneGeographique_stations', array('progZoneGeoRefId' => $progZoneGeoRefId)));

        return $this->render('AeagSqeBundle:Referentiel:Programmation/zoneGeographiqueStations.html.twig', array('entities' => $tabStationMesures,
                    'pgProgZoneGeoRef' => $pgProgZoneGeoRef));
    }

    public function zoneGeographiqueStationSitePrelevementsAction($pgRefStationMesureOuvFoncId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'zoneGeographiqueStationSitePrelevements');
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
        return $this->render('AeagSqeBundle:Referentiel:Programmation/zoneGeographiqueStationSitePrelevements.html.twig', array('entities' => $tabSitePrelevements,
                    'pgRefStationMesure' => $pgRefStationMesure));
    }

    public function webusersAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'webusers');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgMarcheUser = $emSqe->getRepository('AeagSqeBundle:PgProgMarcheUser');
        $repoPgProgWebuserRsx = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserRsx');
        $repoPgProgWebuserZgeoref = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserZgeoref');

        $tabWebusers = array();
        $i = 0;

        $pgProgWebusers = $repoPgProgWebusers->getPgProgWebusers();
        if ($pgProgWebusers) {
            foreach ($pgProgWebusers as $pgProgWebuser) {
                $tabWebusers[$i]['webuser'] = $pgProgWebuser;
                $pgProgMarcheUsers = $repoPgProgMarcheUser->getPgProgMarcheUserByUser($pgProgWebuser);
                if ($pgProgMarcheUsers) {
                    $tabWebusers[$i]['nbProgMarche'] = count($pgProgMarcheUsers);
                } else {
                    $tabWebusers[$i]['nbProgMarche'] = 0;
                }
                $pgProgWebuserRsxs = $repoPgProgWebuserRsx->getPgProgWebuserRsxByWebuser($pgProgWebuser);
                if ($pgProgWebuserRsxs) {
                    $tabWebusers[$i]['nbRefReseauMesure'] = count($pgProgWebuserRsxs);
                } else {
                    $tabWebusers[$i]['nbRefReseauMesure'] = 0;
                }
                $pgProgWebuserZgeorefs = $repoPgProgWebuserZgeoref->getPgProgWebuserZgeorefByWebuser($pgProgWebuser);
                if ($pgProgWebuserZgeorefs) {
                    $tabWebusers[$i]['nbProgZoneGeoRef'] = count($pgProgWebuserZgeorefs);
                } else {
                    $tabWebusers[$i]['nbProgZoneGeoRef'] = 0;
                }
                $i++;
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'utilisateurs dans la table : pg_prog_webusers');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }
        $session->set('retour', $this->generateUrl('AeagSqeBundle_referentiel_webusers'));
        $session->set('niveau1', $this->generateUrl('AeagSqeBundle_referentiel_webusers'));
        $session->set('niveau2', '');
        $session->set('niveau3', '');
        $session->set('niveau4', '');
        return $this->render('AeagSqeBundle:Referentiel:Programmation/webusers.html.twig', array('entities' => $tabWebusers));
    }

    public function webuserMarchesAction($webuserId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'webuserMarches');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgMarcheUser = $emSqe->getRepository('AeagSqeBundle:PgProgMarcheUser');
        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');
        $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByid($webuserId);
        $tabMarche = array();
        $i = 0;
        if ($pgProgWebuser) {
            $pgProgMarcheUsers = $repoPgProgMarcheUser->getPgProgMarcheUserByUser($pgProgWebuser);
            if (count($pgProgMarcheUsers) > 0) {
                foreach ($pgProgMarcheUsers as $pgProgMarcheUser) {
                    $pgProgMarche = $pgProgMarcheUser->getMarche();
                    if ($pgProgMarche) {
                        $tabMarches[$i] = $pgProgMarche;
                        $i++;
                    }
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de marchés pour l\'utilisateur : ' . $pgProgWebuser->getNom());
                return $this->redirect($session->get('niveau1'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Utilisateur : ' . $webuserId . ' inconnu dans la table : pg_prog_webusers');
            return $this->redirect($session->get('niveau1'));
        }
        $session->set('niveau2', $this->generateUrl('AeagSqeBundle_referentiel_webuser_marches', array('webuserId' => $pgProgWebuser->getid())));

        return $this->render('AeagSqeBundle:Referentiel:Programmation/webuserMarches.html.twig', array('entities' => $tabMarches,
                    'pgProgWebuser' => $pgProgWebuser));
    }

    public function webuserMarcheLotsAction($pgProgMarcheId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'webuserMarcheLots');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');
        $repoPgProgZoneGeoRef = $emSqe->getRepository('AeagSqeBundle:PgProgZoneGeoRef');
        $repoPgRefCorresPresta = $emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $repoPgProgTypeMilieu = $emSqe->getRepository('AeagSqeBundle:PgProgTypeMilieu');
        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');

        $pgProgMarche = $repoPgProgMarche->getPgProgMarcheByid($pgProgMarcheId);

        $tabLots = array();
        $i = 0;
        if ($pgProgMarche) {
            $pgProgLots = $repoPgProgLot->getPgProgLotByMarche($pgProgMarche);
            if (count($pgProgLots) > 0) {
                foreach ($pgProgLots as $pgProgLot) {
                    $tabLots[$i]['pgProgLot'] = $pgProgLot;
                    $pgProgZoneGeoRef = $pgProgLot->getZgeoRef();
                    $tabLots[$i]['pgProgZoneGeoRef'] = $pgProgZoneGeoRef;
                    $pgRefCorresPresta = $pgProgLot->getTitulaire();
                    $tabLots[$i]['pgRefCorresPresta'] = $pgRefCorresPresta;
                    $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
                    $tabLots[$i]['pgProgTypeMilieu'] = $pgProgTypeMilieu;
                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de lots pour le marché ' . $pgProgMarche->getNomMarche());
                return $this->redirect($session->get('niveau2'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Marché  : ' . $pgProgMarcheId . ' inconnu dans la table : pg_prog_marche');
            return $this->redirect($session->get('niveau2'));
        }
        $session->set('niveau3', $this->generateUrl('AeagSqeBundle_referentiel_webuser_marche_lots', array('pgProgMarcheId' => $pgProgMarcheId)));

        return $this->render('AeagSqeBundle:Referentiel:Programmation/webuserMarcheLots.html.twig', array('entities' => $tabLots,
                    'marche' => $pgProgMarche));
    }

    public function webuserMarcheLotGroupesAction($pgProgLotId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'webuserMarcheLotGroupes');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');
        $repoPgProgLotGrparRef = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparRef');
        $repoPgProGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProgTypeMilieu = $emSqe->getRepository('AeagSqeBundle:PgProgTypeMilieu');
        $repoPgSandreSupports = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoPgProGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');

        $pgProgLot = $repoPgProgLot->getPgProgLotByid($pgProgLotId);

        $tabGrpParamRefs = array();
        $i = 0;
        if ($pgProgLot) {
            $gProgLotGrpars = $repoPgProgLotGrparRef->getPgProgLotGrparRefByLot($pgProgLot);
            if (count($gProgLotGrpars) > 0) {
                foreach ($gProgLotGrpars as $gProgLotGrpar) {
                    $pgProGrpParamRef = $gProgLotGrpar->getGrpparref();
                    $tabGrpParamRefs[$i]['pgProGrpParamRef'] = $pgProGrpParamRef;
                    if ($pgProGrpParamRef) {
                        $pgProgTypeMilieu = $pgProGrpParamRef->getCodeMilieu();
                        $tabGrpParamRefs[$i]['pgProgTypeMilieu'] = $pgProgTypeMilieu;
                        $tabGrpParamRefs[$i]['pgSandreSupports'] = $pgProGrpParamRef->getSupport();
                        $pgProgGrparObliSupports = $repoPgProGrparObligSupport->getPgProgGrparObligSupportByGrparRefId($pgProGrpParamRef->getId());
                        $tabGrpParamRefs[$i]['pgProgGrparObligSupport'] = $pgProgGrparObliSupports;
                    } else {
                        $tabGrpParamRefs[$i]['pgProgTypeMilieu'] = null;
                        $tabGrpParamRefs[$i]['pgSandreSupports'] = null;
                        $tabGrpParamRefs[$i]['pgProgGrparObligSupport'] = null;
                    }
                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de grouipes de paramètres pour le lot ' . $pgProgLot->getNomLot());
                return $this->redirect($session->get('niveau3'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Lot  : ' . $pgProgLotId . ' inconnu dans la table : pg_prog_lot');
            return $this->redirect($session->get('niveau3'));
        }

        $session->set('niveau4', $this->generateUrl('AeagSqeBundle_referentiel_webuser_marche_lot_groupes', array('pgProgLotId' => $pgProgLotId)));

        return $this->render('AeagSqeBundle:Referentiel:Programmation/webuserMarcheLotGroupes.html.twig', array('entities' => $tabGrpParamRefs,
                    'lot' => $pgProgLot));
    }

    public function webuserMarcheLotGroupeParametresAction($pgProgGrpParamRefId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'webuserMarcheLotGroupeParametres');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');
        $repoPgSandreParametres = $emSqe->getRepository('AeagSqeBundle:PgSandreParametres');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgSandreSupports = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');

        $pgProGrpParamRef = $repoPgProGrpParamRef->getPgProgGrpParamRefById($pgProgGrpParamRefId);

        $tabLstParams = array();
        $i = 0;
        if ($pgProGrpParamRef) {
            $pgProgGrparRefLstParams = $repoPgProGrparRefLstParam->getPgProgGrparRefLstParamByGrparRef($pgProGrpParamRef);
            if (count($pgProgGrparRefLstParams) > 0) {
                foreach ($pgProgGrparRefLstParams as $pgProgGrparRefLstParam) {
                    $tabLstParams[$i]['pgProgGrparRefLstParam'] = $pgProgGrparRefLstParam;
                    $pgSandreParametre = $pgProgGrparRefLstParam->getCodeParametre();
                    if ($pgProgGrparRefLstParam->getCodeFraction()) {
                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgGrparRefLstParam->getCodeFraction()->getCodeFraction());
                    } else {
                        $pgSandreFraction = null;
                    } $pgSandreUnite = $pgProgGrparRefLstParam->getUniteDefaut();
                    $tabLstParams[$i]['pgSandreParametre'] = $pgSandreParametre;
                    $tabLstParams[$i]['pgSandreFraction'] = $pgSandreFraction;
                    $tabLstParams[$i]['pgSandreUnite'] = $pgSandreUnite;
                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de paramètres pour le groupe ' . $pgProGrpParamRef->getCodeGrp() . ' ' . $pgProGrpParamRef->getLibelleGrp());
                return $this->redirect($session('niveau4'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Groupe  : ' . $pgProgGrpParamRefId . ' inconnu dans la table : pg_prog_grp_param_ref');
            return $this->redirect($session('niveau4'));
        }

        return $this->render('AeagSqeBundle:Referentiel:Programmation/webuserMarcheLotGroupeParametres.html.twig', array('entities' => $tabLstParams,
                    'pgProgGrpParamRef' => $pgProGrpParamRef));
    }

    public function webuserwReseauxAction($webuserId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'webuserwReseaux');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgWebuserRsx = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserRsx');
        $repoPgRefReseauMesure = $emSqe->getRepository('AeagSqeBundle:PgRefReseauMesure');
        $repoPgRefStationRsx = $emSqe->getRepository('AeagSqeBundle:PgRefStationRsx');

        $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByid($webuserId);
        $tabReseauMesures = array();
        $i = 0;
        if ($pgProgWebuser) {
            $pgProgWebuserRsxs = $repoPgProgWebuserRsx->getPgProgWebuserRsxByWebuser($pgProgWebuser);
            if (count($pgProgWebuserRsxs) > 0) {
                foreach ($pgProgWebuserRsxs as $pgProgWebuserRsx) {
                    if ($pgProgWebuserRsx->getReseauMesure()) {
                        $tabReseauMesures[$i]['WebuserRsx'] = $pgProgWebuserRsx;
                        $tabReseauMesures[$i]['reseau'] = $pgProgWebuserRsx->getReseauMesure();
                        $nbPpgRefStationRsxs = $repoPgRefStationRsx->getNbPgRefStationRsxByReseauMesure($pgProgWebuserRsx->getReseauMesure());
                        $tabReseauMesures[$i]['nbStations'] = $nbPpgRefStationRsxs;
                        $i++;
                    }
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de réseaux de mesure pour l\'utilisateur : ' . $pgProgWebuser->getNom());
                return $this->redirect($session->get('niveau1'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Utilisateur : ' . $webuserId . ' inconnu dans la table : pg_prog_webusers');
            return $this->redirect($session->get('niveau1'));
        }
        $session->set('niveau2', $this->generateUrl('AeagSqeBundle_referentiel_webuser_reseaux', array('webuserId' => $pgProgWebuser->getid())));
        return $this->render('AeagSqeBundle:Referentiel:Programmation/webuserReseaux.html.twig', array('entities' => $tabReseauMesures,
                    'pgProgWebuser' => $pgProgWebuser));
    }

    public function webuserReseauStationsAction($pgRefReseauMesureId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'webuserReseauStations');
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
                return $this->redirect($session->get('niveau2'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'réseau de mesure  : ' . $pgRefReseauMesureId . ' inconnu dans la table : pg_ref_reseau_mesure');
            return $this->redirect($session->get('niveau2'));
        }

        $session->set('niveau3', $this->generateUrl('AeagSqeBundle_referentiel_webuser_reseau_stations', array('pgRefReseauMesureId' => $pgRefReseauMesureId)));

        return $this->render('AeagSqeBundle:Referentiel:Programmation/webuserReseauStations.html.twig', array('entities' => $tabStationMesures,
                    'pgRefReseauMesure' => $pgRefReseauMesure));
    }

    public function webuserReseauStationSitePrelevementsAction($pgRefStationMesureOuvFoncId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'webuserReseauSationSitePrelevements');
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
                return $this->redirect($session->get('niveau3'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Station de mesure  : ' . $pgRefStationMesureOuvFoncId . ' inconnu dans la table : pg_ref_station_mesure');
            return $this->redirect($session->get('niveau3'));
        }
        return $this->render('AeagSqeBundle:Referentiel:Programmation/webuserReseauStationSitePrelevements.html.twig', array('entities' => $tabSitePrelevements,
                    'pgRefStationMesure' => $pgRefStationMesure));
    }

    public function webuserZoneGeographiquesAction($webuserId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'webuserZoneGeographiques');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgWebuserZgeoref = $emSqe->getRepository('AeagSqeBundle:PgProgWebuserZgeoref');
        $repoPgProgZoneGeoRef = $emSqe->getRepository('AeagSqeBundle:PgProgZoneGeoRef');
        $repoPgProgZgeorrefStation = $emSqe->getRepository('AeagSqeBundle:PgProgZgeorefStation');

        $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByid($webuserId);
        $tabZoneGeoRefs = array();
        $i = 0;
        if ($pgProgWebuser) {
            $pgProgWebuserZgeorefs = $repoPgProgWebuserZgeoref->getPgProgWebuserZgeorefByWebuser($pgProgWebuser);
            if (count($pgProgWebuserZgeorefs) > 0) {
                foreach ($pgProgWebuserZgeorefs as $pgProgWebuserZgeoref) {
                    $tabZoneGeoRefs[$i]['zonegeo'] = $pgProgWebuserZgeoref->getZgeoref();
                    $nbPpgProgZgeorrefStations = $repoPgProgZgeorrefStation->getNbPgProgZgeorefStationByZgeoref($pgProgWebuserZgeoref->getZgeoref());
                    $tabZoneGeoRefs[$i]['nbStations'] = $nbPpgProgZgeorrefStations;
                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de zones géographques pour l\'utilisateur : ' . $pgProgWebuser->getNom());
                return $this->redirect($session->get('niveau1'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Utilisateur : ' . $webuserId . ' inconnu dans la table : pg_prog_webusers');
            return $this->redirect($session->get('niveau1'));
        }
        $session->set('niveau2', $this->generateUrl('AeagSqeBundle_referentiel_webuser_zoneGeographiques', array('webuserId' => $pgProgWebuser->getid())));
        return $this->render('AeagSqeBundle:Referentiel:Programmation/webuserZoneGeographiques.html.twig', array('entities' => $tabZoneGeoRefs,
                    'pgProgWebuser' => $pgProgWebuser));
    }

    public function webuserZoneGeographiqueStationsAction($progZoneGeoRefId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'webuserZoneGeographiqueStations');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgZoneGeoRef = $emSqe->getRepository('AeagSqeBundle:PgProgZoneGeoRef');
        $repoPgProgZgeorrefStation = $emSqe->getRepository('AeagSqeBundle:PgProgZgeorefStation');
        $repoPgRefStationMesure = $emSqe->getRepository('AeagSqeBundle:PgRefStationMesure');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');
        $repoPgRefSitePrelevement = $emSqe->getRepository('AeagSqeBundle:PgRefSitePrelevement');

        $pgProgZoneGeoRef = $repoPgProgZoneGeoRef->getPgProgZoneGeoRefById($progZoneGeoRefId);

        $tabStationMesures = array();
        $i = 0;
        if ($pgProgZoneGeoRef) {
            $pgProgZgeorrefStations = $repoPgProgZgeorrefStation->getpgProgZgeorefStationByZgeoref($pgProgZoneGeoRef);
            if (count($pgProgZgeorrefStations) > 0) {
                foreach ($pgProgZgeorrefStations as $pgProgZgeorrefStation) {
                    $pgRefStationMesure = $repoPgRefStationMesure->getPgRefStationMesureByOuvFoncId($pgProgZgeorrefStation->getStationMesure()->getOuvFoncId());
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
                $session->getFlashBag()->add('notice-warning', 'pas de stations pour la zone géographique ' . $pgProgZoneGeoRef->getNomZoneGeo());
                return $this->redirect($session->get('niveau2'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'réseau de mesure  : ' . $pgRefReseauMesureId . ' inconnu dans la table : pg_ref_reseau_mesure');
            return $this->redirect($session->get('niveau2'));
        }

        $session->set('niveau3', $this->generateUrl('AeagSqeBundle_referentiel_webuser_zoneGeographique_stations', array('progZoneGeoRefId' => $progZoneGeoRefId)));

        return $this->render('AeagSqeBundle:Referentiel:Programmation/webuserZoneGeographiqueStations.html.twig', array('entities' => $tabStationMesures,
                    'pgProgZoneGeoRef' => $pgProgZoneGeoRef));
    }

    public function webuserZoneGeographiqueStationSitePrelevementsAction($pgRefStationMesureOuvFoncId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'webuserZoneGeographiqueStationSitePrelevements');
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
                return $this->redirect($session->get('niveau3'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Station de mesure  : ' . $pgRefStationMesureOuvFoncId . ' inconnu dans la table : pg_ref_station_mesure');
            return $this->redirect($session->get('niveau3'));
        }
        return $this->render('AeagSqeBundle:Referentiel:Programmation/webuserZoneGeographiqueStationSitePrelevements.html.twig', array('entities' => $tabSitePrelevements,
                    'pgRefStationMesure' => $pgRefStationMesure));
    }

    public function groupesAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'groupes');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');
        $repoPgProGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');
        $repoPgSandreParametres = $emSqe->getRepository('AeagSqeBundle:PgSandreParametres');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');

        $pgProGrpParamRefs = $repoPgProGrpParamRef->getPgProgGrpParamRefs();
        $tabGrpParamRefs = array();
        $i = 0;
        $nom_fichier = "groupes.csv";
        if (count($pgProGrpParamRefs) > 0) {
            $repertoire = "fichiers";
            $date_import = date('Ymd_His');
            $nom_fichier = "groupes.csv";
            $fic_import = $repertoire . "/" . $nom_fichier;
            //ouverture fichier
            $fic = fopen($fic_import, "w");
            $contenu = "Code groupe;Libelle groupe;Type groupe;Milieu groupe;Support groupe;Code paramètre;Libelle paramètre;Fraction paramètre;Unite parametre;Defaut parametre;\n";
            fputs($fic, $contenu);
            foreach ($pgProGrpParamRefs as $pgProGrpParamRef) {
                $tabGrpParamRefs[$i]['pgProGrpParamRef'] = $pgProGrpParamRef;
                if ($pgProGrpParamRef) {
                    $pgProgTypeMilieu = $pgProGrpParamRef->getCodeMilieu();
                    $tabGrpParamRefs[$i]['pgProgTypeMilieu'] = $pgProgTypeMilieu;
                    $tabGrpParamRefs[$i]['pgSandreSupports'] = $pgProGrpParamRef->getSupport();
                    $pgProgGrparObliSupports = $repoPgProGrparObligSupport->getPgProgGrparObligSupportByGrparRefId($pgProGrpParamRef->getId());
                    $tabGrpParamRefs[$i]['pgProgGrparObligSupport'] = $pgProgGrparObliSupports;
                } else {
                    $tabGrpParamRefs[$i]['pgProgTypeMilieu'] = null;
                    $tabGrpParamRefs[$i]['pgSandreSupports'] = null;
                    $tabGrpParamRefs[$i]['pgProgGrparObligSupport'] = null;
                }
                $i++;
                $pgProgGrparRefLstParams = $repoPgProGrparRefLstParam->getPgProgGrparRefLstParamByGrparRef($pgProGrpParamRef);
                if (count($pgProgGrparRefLstParams) > 0) {
                    foreach ($pgProgGrparRefLstParams as $pgProgGrparRefLstParam) {
                        $pgSandreParametre = $pgProgGrparRefLstParam->getCodeParametre();
                        if ($pgProgGrparRefLstParam->getCodeFraction()) {
                            $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgGrparRefLstParam->getCodeFraction()->getCodeFraction());
                        } else {
                            $pgSandreFraction = null;
                        }
                        $pgSandreUnite = $pgProgGrparRefLstParam->getUniteDefaut();
                        $contenu = $pgProGrpParamRef->getCodeGrp() . ";";
                        $contenu = $contenu . $pgProGrpParamRef->getLibelleGrp() . ";";
                        $contenu = $contenu . $pgProGrpParamRef->getTypeGrp() . ";";
                        $contenu = $contenu . $pgProgTypeMilieu->getNomMilieu() . ";";
                        if ($pgProGrpParamRef->getSupport()) {
                            $contenu = $contenu . $pgProGrpParamRef->getSupport()->getNomSupport() . ";";
                        } else {
                            $contenu = $contenu . ";";
                        }
                        $contenu = $contenu . $pgSandreParametre->getCodeParametre() . ";";
                        $contenu = $contenu . $pgSandreParametre->getLibelleCourt() . ";";
                        if ($pgSandreFraction) {
                            $contenu = $contenu . $pgSandreFraction->getNomFraction() . ";";
                        } else {
                            $contenu = $contenu . ";";
                        }
                        if ($pgSandreUnite) {
                            $contenu = $contenu . $pgSandreUnite->getNomUnite() . ";";
                        } else {
                            $contenu = $contenu . ";";
                        }
                        $contenu = $contenu . $pgProgGrparRefLstParam->getParamDefaut() . ";\n";
                        fputs($fic, $contenu);
                    }
                }
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_prog_grp_param_ref');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }
        $session->set('niveau1', $this->generateUrl('AeagSqeBundle_referentiel_groupes'));
        return $this->render('AeagSqeBundle:Referentiel:Programmation/groupes.html.twig', array('entities' => $tabGrpParamRefs, 'fichier' => $nom_fichier));
    }

    public function groupeParametresAction($pgProgGrpParamRefId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'groupeParametres');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');
        $repoPgSandreParametres = $emSqe->getRepository('AeagSqeBundle:PgSandreParametres');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgSandreSupports = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');

        $pgProGrpParamRef = $repoPgProGrpParamRef->getPgProgGrpParamRefById($pgProgGrpParamRefId);

        $tabLstParams = array();
        $i = 0;
        $nom_fichier = "groupeParametres.csv";
        if ($pgProGrpParamRef) {
             $repertoire = "fichiers";
            $date_import = date('Ymd_His');
            $nom_fichier = "groupeParametres.csv";
            $fic_import = $repertoire . "/" . $nom_fichier;
            //ouverture fichier
            $fic = fopen($fic_import, "w");
            $contenu = "Code groupe;Libelle groupe;Type groupe;Milieu groupe;Support groupe;Code paramètre;Libelle paramètre;Fraction paramètre;Unite parametre;Defaut parametre;\n";
            fputs($fic, $contenu);
            $pgProgGrparRefLstParams = $repoPgProGrparRefLstParam->getPgProgGrparRefLstParamByGrparRef($pgProGrpParamRef);
            if (count($pgProgGrparRefLstParams) > 0) {
                foreach ($pgProgGrparRefLstParams as $pgProgGrparRefLstParam) {
                    $tabLstParams[$i]['pgProgGrparRefLstParam'] = $pgProgGrparRefLstParam;
                    $pgSandreParametre = $pgProgGrparRefLstParam->getCodeParametre();
                    if ($pgProgGrparRefLstParam->getCodeFraction()) {
                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgGrparRefLstParam->getCodeFraction()->getCodeFraction());
                    } else {
                        $pgSandreFraction = null;
                    }
                    $pgSandreUnite = $pgProgGrparRefLstParam->getUniteDefaut();
                    $tabLstParams[$i]['pgSandreParametre'] = $pgSandreParametre;
                    $tabLstParams[$i]['pgSandreFraction'] = $pgSandreFraction;
                    $tabLstParams[$i]['pgSandreUnite'] = $pgSandreUnite;
                    $i++;
                     $contenu = $pgProGrpParamRef->getCodeGrp() . ";";
                        $contenu = $contenu . $pgProGrpParamRef->getLibelleGrp() . ";";
                        $contenu = $contenu . $pgProGrpParamRef->getTypeGrp() . ";";
                        $contenu = $contenu . $pgProGrpParamRef->getCodeMilieu()->getNomMilieu() . ";";
                        if ($pgProGrpParamRef->getSupport()) {
                            $contenu = $contenu . $pgProGrpParamRef->getSupport()->getNomSupport() . ";";
                        } else {
                            $contenu = $contenu . ";";
                        }
                        $contenu = $contenu . $pgSandreParametre->getCodeParametre() . ";";
                        $contenu = $contenu . $pgSandreParametre->getLibelleCourt() . ";";
                        if ($pgSandreFraction) {
                            $contenu = $contenu . $pgSandreFraction->getNomFraction() . ";";
                        } else {
                            $contenu = $contenu . ";";
                        }
                        if ($pgSandreUnite) {
                            $contenu = $contenu . $pgSandreUnite->getNomUnite() . ";";
                        } else {
                            $contenu = $contenu . ";";
                        }
                        $contenu = $contenu . $pgProgGrparRefLstParam->getParamDefaut() . ";\n";
                        fputs($fic, $contenu);
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de paramètres pour l\analyse ' . $pgProGrpParamRef->getCodeGrp() . ' ' . $pgProGrpParamRef->getLibelleGrp());
                return $this->redirect($session->get('niveau1'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Analyse  : ' . $pgProgGrpParamRefId . ' inconnu dans la table : pg_prog_grp_param_ref');
            return $this->redirect($session->get('niveau1'));
        }
        return $this->render('AeagSqeBundle:Referentiel:Programmation/groupeParametres.html.twig', array('entities' => $tabLstParams,
                    'pgProgGrpParamRef' => $pgProGrpParamRef,
                    'fichier' => $nom_fichier));
    }

    public function marchesAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'marches');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');
//        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
//        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
//        $repoPgProgMarcheUser = $emSqe->getRepository('AeagSqeBundle:PgProgMarcheUser');
//        $correspondant = $repoCorrespondant->getCorrespondantById($user->getCorrespondant());
//
//        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE')) {
//            $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusers();
//        } else {
//            $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByid($correspondant->getCorid());
//        }
//        $tabMarches = array();
//        $i = 0;
//        if ($pgProgWebuser) {
//            $pgProgMarcheUsers = $repoPgProgMarcheUser->getPgProgMarcheUserByUser($pgProgWebuser);
//            if (count($pgProgmarcheUsers) > 0) {
//                foreach ($pgProgmarcheUsers as $pgProgmarcheUser) {
//                    $pgProgMarche = $repoPgProgMarche->getPgProgMarcheByid($repoPgProgmarcheUser->getMarche());
//                    if ($pgProgMarche) {
//                        $tabMarches[$i] = $pgProgMarche;
//                        $i++;
//                    }
//                }
//            }else{
//                $session->getFlashBag()->add('notice-warning', 'pas de marché pour ' .  $user->getUsername() );
//                return $this->redirect($this->generateUrl('aeag_sqe'));
//            }
//        } else {
//            if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINSQE')) {
//                $session->getFlashBag()->add('notice-warning', 'pas d\'utilisateurs dans la table : pg_prog_webusers');
//            } else {
//                $session->getFlashBag()->add('notice-warning', 'Utilisateur : ' . $user->getUsername() . ' inconnu dans la table : pg_prog_webusers');
//            }
//            return $this->redirect($this->generateUrl('aeag_sqe'));
//        }

        $tabMarches = array();
        $i = 0;
        $pgProgMarches = $repoPgProgMarche->getPgProgMarches();
        if (count($pgProgMarches) > 0) {
            foreach ($pgProgMarches as $pgProgMarche) {
                $tabMarches[$i] = $pgProgMarche;
                $i++;
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_prog_marche');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }

        $session->set('niveau1', $this->generateUrl('AeagSqeBundle_referentiel_marches'));
        $session->set('niveau2', '');
        $session->set('niveau3', '');
        $session->set('niveau4', '');

        return $this->render('AeagSqeBundle:Referentiel:Programmation/marches.html.twig', array('entities' => $tabMarches));
    }

    public function marcheLotsAction($pgProgMarcheId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'marcheLots');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');
        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');

        $pgProgMarche = $repoPgProgMarche->getPgProgMarcheByid($pgProgMarcheId);

        $tabLots = array();
        $i = 0;
        if ($pgProgMarche) {
            $pgProgLots = $repoPgProgLot->getPgProgLotByMarche($pgProgMarche);
            if (count($pgProgLots) > 0) {
                foreach ($pgProgLots as $pgProgLot) {
                    $tabLots[$i]['pgProgLot'] = $pgProgLot;
                    $pgProgZoneGeoRef = $pgProgLot->getZgeoRef();
                    $tabLots[$i]['pgProgZoneGeoRef'] = $pgProgZoneGeoRef;
                    $pgRefCorresPresta = $pgProgLot->getTitulaire();
                    $tabLots[$i]['pgRefCorresPresta'] = $pgRefCorresPresta;
                    $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
                    $tabLots[$i]['pgProgTypeMilieu'] = $pgProgTypeMilieu;
                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de lots pour le marché ' . $pgProgMarche->getNomMarche());
                return $this->redirect($session->get('niveau1'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Marché  : ' . $pgProgMarcheId . ' inconnu dans la table : pg_prog_marche');
            return $this->redirect($session->get('niveau1'));
        }
        $session->set('niveau2', $this->generateUrl('AeagSqeBundle_referentiel_marche_lots', array('pgProgMarcheId' => $pgProgMarcheId)));

        return $this->render('AeagSqeBundle:Referentiel:Programmation/marcheLots.html.twig', array('entities' => $tabLots,
                    'marche' => $pgProgMarche));
    }

    public function marcheLotgroupesAction($pgProgLotId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'marcheLotgroupes');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');
        $repoPgProgLotGrparRef = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrparRef');
        $repoPgProGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');

        $pgProgLot = $repoPgProgLot->getPgProgLotByid($pgProgLotId);

        $tabGrpParamRefs = array();
        $i = 0;
        if ($pgProgLot) {
            $gProgLotGrpars = $repoPgProgLotGrparRef->getPgProgLotGrparRefByLot($pgProgLot);
            if (count($gProgLotGrpars) > 0) {
                foreach ($gProgLotGrpars as $gProgLotGrpar) {
                    $pgProGrpParamRef = $gProgLotGrpar->getGrpparref();
                    $tabGrpParamRefs[$i]['pgProGrpParamRef'] = $pgProGrpParamRef;
                    if ($pgProGrpParamRef) {
                        $pgProgTypeMilieu = $pgProGrpParamRef->getCodeMilieu();
                        $tabGrpParamRefs[$i]['pgProgTypeMilieu'] = $pgProgTypeMilieu;
                        $tabGrpParamRefs[$i]['pgSandreSupports'] = $pgProGrpParamRef->getSupport();
                        $pgProgGrparObliSupports = $repoPgProGrparObligSupport->getPgProgGrparObligSupportByGrparRefId($pgProGrpParamRef->getId());
                        $tabGrpParamRefs[$i]['pgProgGrparObligSupport'] = $pgProgGrparObliSupports;
                    } else {
                        $tabGrpParamRefs[$i]['pgProgTypeMilieu'] = null;
                        $tabGrpParamRefs[$i]['pgSandreSupports'] = null;
                        $tabGrpParamRefs[$i]['pgProgGrparObligSupport'] = null;
                    }
                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de groupes de paramètres pour le lot ' . $pgProgLot->getNomLot());
                return $this->redirect($session->get('niveau2'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Lot  : ' . $pgProgLotId . ' inconnu dans la table : pg_prog_lot');
            return $this->redirect($session->get('niveau2'));
        }
        $session->set('niveau3', $this->generateUrl('AeagSqeBundle_referentiel_marche_lot_groupes', array('pgProgLotId' => $pgProgLotId)));

        return $this->render('AeagSqeBundle:Referentiel:Programmation/marcheLotGroupes.html.twig', array('entities' => $tabGrpParamRefs,
                    'lot' => $pgProgLot));
    }

    public function marcheLotGroupeParametresAction($pgProgGrpParamRefId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'marcheLotGroupeParametres');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');
        $repoPgSandreParametres = $emSqe->getRepository('AeagSqeBundle:PgSandreParametres');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgSandreSupports = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');

        $pgProGrpParamRef = $repoPgProGrpParamRef->getPgProgGrpParamRefById($pgProgGrpParamRefId);

        $tabLstParams = array();
        $i = 0;
        if ($pgProGrpParamRef) {
            $pgProgGrparRefLstParams = $repoPgProGrparRefLstParam->getPgProgGrparRefLstParamByGrparRef($pgProGrpParamRef);
            if (count($pgProgGrparRefLstParams) > 0) {
                foreach ($pgProgGrparRefLstParams as $pgProgGrparRefLstParam) {
                    $tabLstParams[$i]['pgProgGrparRefLstParam'] = $pgProgGrparRefLstParam;
                    $pgSandreParametre = $pgProgGrparRefLstParam->getCodeParametre();
                    if ($pgProgGrparRefLstParam->getCodeFraction()) {
                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgGrparRefLstParam->getCodeFraction()->getCodeFraction());
                    } else {
                        $pgSandreFraction = null;
                    }$pgSandreUnite = $pgProgGrparRefLstParam->getUniteDefaut();
                    $tabLstParams[$i]['pgSandreParametre'] = $pgSandreParametre;
                    $tabLstParams[$i]['pgSandreFraction'] = $pgSandreFraction;
                    $tabLstParams[$i]['pgSandreUnite'] = $pgSandreUnite;
                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de paramètres pour le groupe ' . $pgProGrpParamRef->getCodeGrp() . ' ' . $pgProGrpParamRef->getLibelleGrp());
                return $this->redirect($session->get('niveau3'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Groupe  : ' . $pgProgGrpParamRefId . ' inconnu dans la table : pg_prog_grp_param_ref');
            return $this->redirect($session->get('niveau3'));
        }
        return $this->render('AeagSqeBundle:Referentiel:Programmation/marcheLotGroupeParametres.html.twig', array('entities' => $tabLstParams,
                    'pgProgGrpParamRef' => $pgProGrpParamRef));
    }

    public function lotsAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'lots');
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');

        $pgProgLots = $repoPgProgLot->getPgProgLots();

        $tabLots = array();
        $i = 0;
        if (count($pgProgLots) > 0) {
            foreach ($pgProgLots as $pgProgLot) {
                $tabLots[$i]['pgProgLot'] = $pgProgLot;
                $pgProgMarche = $pgProgLot->getMarche();
                $tabLots[$i]['pgProgMarche'] = $pgProgMarche;
                $pgProgZoneGeoRef = $pgProgLot->getZgeoRef();
                $tabLots[$i]['pgProgZoneGeoRef'] = $pgProgZoneGeoRef;
                $pgRefCorresPresta = $pgProgLot->getTitulaire();
                $tabLots[$i]['pgRefCorresPresta'] = $pgRefCorresPresta;
                $pgProgTypeMilieu = $pgProgLot->getCodeMilieu();
                $tabLots[$i]['pgProgTypeMilieu'] = $pgProgTypeMilieu;
                $i++;
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'pas d\'enregistrements dans la table pg_prog_lot');
            return $this->redirect($this->generateUrl('aeag_sqe'));
        }
        $session->set('niveau1', $this->generateUrl('AeagSqeBundle_referentiel_lots'));
        $session->set('niveau2', '');
        $session->set('niveau3', '');
        $session->set('niveau4', '');

        return $this->render('AeagSqeBundle:Referentiel:Programmation/lots.html.twig', array('entities' => $tabLots));
    }

    public function lotGroupesAction($pgProgLotId) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'lotGroupes');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLot = $emSqe->getRepository('AeagSqeBundle:PgProgLot');
        $repoPgProgLotGrpar = $emSqe->getRepository('AeagSqeBundle:PgProgLotGrpar');
        $repoPgProGrparObligSupport = $emSqe->getRepository('AeagSqeBundle:PgProgGrparObligSupport');

        $pgProgLot = $repoPgProgLot->getPgProgLotByid($pgProgLotId);

        $tabGrpParamRefs = array();
        $i = 0;
        if ($pgProgLot) {
            $gProgLotGrpars = $repoPgProgLotGrpar->getPgProgLotGrparByLot($pgProgLot);
            if (count($gProgLotGrpars) > 0) {
                foreach ($gProgLotGrpars as $gProgLotGrpar) {
                    $pgProGrpParamRef = $gProgLotGrpar->getGrpparref();
                    $tabGrpParamRefs[$i]['pgProGrpParamRef'] = $pgProGrpParamRef;
                    if ($pgProGrpParamRef) {
                        $pgProgTypeMilieu = $pgProGrpParamRef->getCodeMilieu();
                        $tabGrpParamRefs[$i]['pgProgTypeMilieu'] = $pgProgTypeMilieu;
                        $tabGrpParamRefs[$i]['pgSandreSupports'] = $pgProGrpParamRef->getSupport();
                        $pgProgGrparObliSupports = $repoPgProGrparObligSupport->getPgProgGrparObligSupportByGrparRefId($pgProGrpParamRef->getId());
                        $tabGrpParamRefs[$i]['pgProgGrparObligSupport'] = $pgProgGrparObliSupports;
                    } else {
                        $tabGrpParamRefs[$i]['pgProgTypeMilieu'] = null;
                        $tabGrpParamRefs[$i]['pgSandreSupports'] = null;
                        $tabGrpParamRefs[$i]['pgProgGrparObligSupport'] = null;
                    }
                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de grouipes de paramètres pour le lot ' . $pgProgLot->getNomLot());
                return $this->redirect($sesion->get('niveau1'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Lot  : ' . $pgProgLotId . ' inconnu dans la table : pg_prog_lot');
            return $this->redirect($sesion->get('niveau1'));
        }
        $session->set('niveau2', $this->generateUrl('AeagSqeBundle_referentiel_lot_groupes', array('pgProgLotId' => $pgProgLotId)));

        return $this->render('AeagSqeBundle:Referentiel:Programmation/lotGroupes.html.twig', array('entities' => $tabGrpParamRefs,
                    'lot' => $pgProgLot));
    }

    public function lotGroupeParametresAction($pgProgGrpParamRefId = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'ReferentielProgrammation');
        $session->set('fonction', 'lotGroupeParametres');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProGrpParamRef = $emSqe->getRepository('AeagSqeBundle:PgProgGrpParamRef');
        $repoPgProGrparRefLstParam = $emSqe->getRepository('AeagSqeBundle:PgProgGrparRefLstParam');
        $repoPgSandreParametres = $emSqe->getRepository('AeagSqeBundle:PgSandreParametres');
        $repoPgSandreUnites = $emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $repoPgSandreFractions = $emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $repoPgSandreSupports = $emSqe->getRepository('AeagSqeBundle:PgSandreSupports');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');

        $pgProGrpParamRef = $repoPgProGrpParamRef->getPgProgGrpParamRefById($pgProgGrpParamRefId);

        $tabLstParams = array();
        $i = 0;
        if ($pgProGrpParamRef) {
            $pgProgGrparRefLstParams = $repoPgProGrparRefLstParam->getPgProgGrparRefLstParamByGrparRef($pgProGrpParamRef);
            if (count($pgProgGrparRefLstParams) > 0) {
                foreach ($pgProgGrparRefLstParams as $pgProgGrparRefLstParam) {
                    $tabLstParams[$i]['pgProgGrparRefLstParam'] = $pgProgGrparRefLstParam;
                    $pgSandreParametre = $pgProgGrparRefLstParam->getCodeParametre();
                    if ($pgProgGrparRefLstParam->getCodeFraction()) {
                        $pgSandreFraction = $repoPgSandreFractions->getPgSandreFractionsByCodeFraction($pgProgGrparRefLstParam->getCodeFraction()->getCodeFraction());
                    } else {
                        $pgSandreFraction = null;
                    }
                    $pgSandreUnite = $pgProgGrparRefLstParam->getUniteDefaut();
                    $tabLstParams[$i]['pgSandreParametre'] = $pgSandreParametre;
                    $tabLstParams[$i]['pgSandreFraction'] = $pgSandreFraction;
                    $tabLstParams[$i]['pgSandreUnite'] = $pgSandreUnite;
                    $i++;
                }
            } else {
                $session->getFlashBag()->add('notice-warning', 'pas de paramètres pour l\analyse ' . $pgProGrpParamRef->getCodeGrp() . ' ' . $pgProGrpParamRef->getLibelleGrp());
                return $this->redirect($session->get('niveau2'));
            }
        } else {
            $session->getFlashBag()->add('notice-warning', 'Analyse  : ' . $pgProgGrpParamRefId . ' inconnu dans la table : pg_prog_grp_param_ref');
            return $this->redirect($session->get('niveau2'));
        }
        return $this->render('AeagSqeBundle:Referentiel:Programmation/lotGroupeParametres.html.twig', array('entities' => $tabLstParams,
                    'pgProgGrpParamRef' => $pgProGrpParamRef));
    }

}
