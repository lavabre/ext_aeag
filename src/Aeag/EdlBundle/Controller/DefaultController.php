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
        $session->set('menu', 'index');
        $session->set('controller', 'Default');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');
        $session->set('appli', 'edl');
        $session->set('retourErreur', $this->generateUrl('aeag_edl'));

        $repoUtilisateur = $emEdl->getRepository('AeagEdlBundle:Utilisateur');
        $repoExportAvisEtat = $emEdl->getRepository('AeagEdlBundle:ExportAvisEtat');
        $repoExportAvisPression = $emEdl->getRepository('AeagEdlBundle:ExportAvisPression');

        if ($user) {
            $utilisateur = $repoUtilisateur->getUtilisateurByExtid($user->getId());
        } else {
            $utilisateur = null;
        }

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

    public function listeMasseEauAction(Request $request) {

// Liste des dossiers selectionnés
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'acceuil');
        $session->set('controller', 'default');
        $session->set('fonction', 'listeMasseEau');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

        $repoUtilisateur = $emEdl->getRepository('AeagEdlBundle:Utilisateur');
        $repoExportAvisEtat = $emEdl->getRepository('AeagEdlBundle:ExportAvisEtat');
        $repoExportAvisPression = $emEdl->getRepository('AeagEdlBundle:ExportAvisPression');
        $repoEtatDerniereProposition = $emEdl->getRepository('AeagEdlBundle:EtatDerniereProposition');
        $repoPressionDerniereProposition = $emEdl->getRepository('AeagEdlBundle:PressionDerniereProposition');

        if ($user) {
            $utilisateur = $repoUtilisateur->getUtilisateurByExtid($user->getId());
        } else {
            $utilisateur = null;
        }


        $critere = new Criteres();
        $form = $this->createForm(new MasseEauRechercheForm(), $critere);

        $form->handleRequest($request);

        $session = $this->get("session");

        if ($session->get('recherche') == 'N') {

//            $request = $this->getRequest();

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
        $whereCsv = "a.euCd = a.euCd";


        if (!empty($typecle)) {
            $where = $where . " and upper(a.typeMe) = '" . $typecle . "'";
            $whereCsv = $whereCsv . " and upper(a.typeMe) = '" . $typecle . "'";
        }

        if (!empty($codecle)) {
            $where = $where . " and upper(a.euCd) LIKE '%" . strtoupper($codecle) . "%'";
            $whereCsv = $whereCsv . " and upper(a.euCd) LIKE '%" . strtoupper($codecle) . "%'";
            if (!empty($masseEaucle)) {
                $where = $where . " and upper(a.nomMasseEau) LIKE '%" . strtoupper($masseEaucle) . "%'";
                $whereCsv = $whereCsv . " and upper(a.nomMasseEau) LIKE '%" . strtoupper($masseEaucle) . "%'";
            };
        } else {
            if (!empty($masseEaucle)) {
                $where = $where . " and upper(a.nomMasseEau) LIKE '%" . strtoupper($masseEaucle) . "%'";
                $whereCsv = $whereCsv . " and upper(a.nomMasseEau) LIKE '%" . strtoupper($masseEaucle) . "%'";
            };
        };


        if ($territoirecle == '1') {

// Et pour vérifier que l'utilisateur est authentifié (et non un anonyme)
            if (!$user) {
                return $this->render('AeagEdlBundle:Default:interdit.html.twig');
            }


//return new Response('role : ' . isset(in_array("ROLE_SUPERVISEUR", $user->getRoles())));

            if (!empty($deptcle)) {
                $where = $where . " and b.euCd = a.euCd";
                $where = $where . " and b.inseeDepartement = '" . $deptcle . "'";
                $where = $where . " and b.inseeDepartement = c.inseeDepartement";
                if ($utilisateur) {
                    $where = $where . " and c.utilisateur = " . $utilisateur->getId();
                }
            } else {

                $where = $where . " and b.euCd = a.euCd";
                $where = $where . " and b.inseeDepartement = c.inseeDepartement";
                if ($utilisateur) {
                    $where = $where . " and c.utilisateur = " . $utilisateur->getId();
                }
            }

            $query = "select a from Aeag\EdlBundle\Entity\MasseEau a,";
            $query = $query . " Aeag\EdlBundle\Entity\DepartementMe b,";
            $query = $query . " Aeag\EdlBundle\Entity\DepUtilisateur c where ";
        } else {
            if (!empty($deptcle)) {
                $where = $where . " and b.euCd = a.euCd";
                $where = $where . " and b.inseeDepartement = '" . $deptcle . "'";
                $whereCsv = $whereCsv . " and a.depts in ('" . $deptcle . "')";

                $query = "select a from Aeag\EdlBundle\Entity\MasseEau a,";
                $query = $query . " Aeag\EdlBundle\Entity\DepartementMe b where ";
            } else {



                $query = "select a from Aeag\EdlBundle\Entity\MasseEau a where ";
            }
        }

        //return new Response('where  : ' .  $whereCsv );


        $query = $query . $where . " order by  a.nomMasseEau";

        $session->set('whereCsv', $whereCsv);

//return new Response('query  : ' . $query);

        $MasseEaux = $emEdl->createQuery($query)
                ->getResult();

        $res = array();
        $i = 0;
        foreach ($MasseEaux as $MasseEau) {

            $etatMes = $repoEtatDerniereProposition->getDernierePropositionByEucd($MasseEau->getEuCd());

            if ($etatMes) {
                $etatMe = $etatMes[0];
                $utilisateur = $repoUtilisateur->getUtilisateurByNom($etatMe->getUsername());
                if ($utilisateur) {
                    $etat_user = $utilisateur;
                } else {
                    $etat_user = null;
                }
                $etat_date = $etatMe->getpropositionDate();
            } else {
                $etat_user = null;
                $etat_date = null;
            };

            $pressionMes = $repoPressionDerniereProposition->getDernierePropositionByEucd($MasseEau->getEuCd());

            if ($pressionMes) {
                $pressionMe = $pressionMes[0];
                $utilisateur = $repoUtilisateur->getUtilisateurByNom($pressionMe->getUsername());
                if ($utilisateur) {
                    $pression_user = $utilisateur;
                } else {
                    $pression_user = null;
                }
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
        $repoEtatMe = $emEdl->getRepository('AeagEdlBundle:EtatMe');
        $repoEtatDerniereProposition = $emEdl->getRepository('AeagEdlBundle:EtatDerniereProposition');

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

        $nbEtats = count($etatGroupes);

        $tabEtatGroupes = array();
        $i = 0;
        foreach ($etatGroupes as $etatGroupe) {
            $tabEtatGroupes[$i]['etatGroupe'] = $etatGroupe;
            $etats = $repoEtatMe->getEtatMe($code, $etatGroupe->getCdGroupe());
            $nbEtats = $repoEtatMe->getNbEtatMe($code, $etatGroupe->getCdGroupe());
            $tabEtatGroupes[$i]['nbEtats'] = $nbEtats;
            $tabEtats = array();
            $j = 0;
            foreach ($etats as $etat) {
                $tabEtats[$j]['etat'] = $etat;

                $proposeds = $etat->getProposed();
                $tabProposeds = array();
                $k = 0;
                foreach ($proposeds as $proposed) {
                    $tabProposeds[$k] = $proposed;
                    $k++;
                }
                if (count($tabProposeds) > 0) {
                    usort($tabProposeds, create_function('$a,$b', 'return strcasecmp($a->getPropositionDate(),$b->getPropositionDate());'));
                    $tabEtats[$j]['proposeds'] = $tabProposeds;
                } else {
                    $tabEtats[$j]['proposeds'] = null;
                }

                $derniereProp = $repoEtatDerniereProposition->getDernierePropositionByEucdCdEtat($etat->getEuCd(), $etat->getCdEtat());

                if (!$derniereProp) {
                    $derniereProposition = null;
                } else {
                    $derniereProposition = $derniereProp[0];
                }
                $tabEtats[$j]['derniereProp'] = $derniereProposition;
                $j++;
            }
            $tabEtatGroupes[$i]['etats'] = $tabEtats;
            $i++;
        }

//            \Symfony\Component\VarDumper\VarDumper::dump($tabEtatGroupes);
//            return new Response ('');   


        return $this->render('AeagEdlBundle:Etat:etatGroupe.html.twig', array(
                    'etatGroupes' => $tabEtatGroupes,
                    'me' => $me,
                    'avisHistorique' => $avisHistorique,
                    'user' => $user,
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

    public function exportEtatAction() {

// Liste des dossiers selectionnés
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'acceuil');
        $session->set('controller', 'default');
        $session->set('fonction', 'exportEtat');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

        $repoUtilisateur = $emEdl->getRepository('AeagEdlBundle:Utilisateur');
        $repoExportAvisEtat = $emEdl->getRepository('AeagEdlBundle:ExportAvisEtat');
        $repoExportAvisPression = $emEdl->getRepository('AeagEdlBundle:ExportAvisPression');
        $repoEtatDerniereProposition = $emEdl->getRepository('AeagEdlBundle:EtatDerniereProposition');
        $repoPressionDerniereProposition = $emEdl->getRepository('AeagEdlBundle:PressionDerniereProposition');

        if ($user) {
            $utilisateur = $repoUtilisateur->getUtilisateurByExtid($user->getId());
        } else {
            $utilisateur = null;
        }

        if ($session->get('whereCsv') != null) {
            $exportAvistEtats = $repoExportAvisEtat->getExportAvisEtatByWhere($session->get('whereCsv'));
        } else {
            $exportAvistEtats = $repoExportAvisEtat->getExportAvisEtats();
        }
        $repertoire = "fichiers";
        $date_import = date('Ymd_His');
        if ($utilisateur) {
            $nom_fichier_etat = "ExportAvisEtat_" . $utilisateur->getUserName() . ".csv";
            $nom_fichier_pression = "ExportAvisPression_" . $utilisateur->getUserName() . ".csv";
        } else {
            $nom_fichier_etat = "ExportAvisEtat_" . $date_import . ".csv";
            $nom_fichier_pression = "ExportAvisPression_" . $date_import . ".csv";
        }

        $fic_import_etat = $repertoire . "/" . $nom_fichier_etat;
//ouverture fichier
        $fic = fopen($fic_import_etat, "w");
        $contenu = "eu_cd; type_me; nom_masse_eau; ct; ct_lib; uhr; uhr_lib; depts; cd_etat; utilisateur; proposition_date; groupe; libelle; e_sdage2016; e_sdage2016_lib; e_propose; e_propose_lib; e_retenu; e_retenu_lib; commentaire\n";
        fputs($fic, utf8_decode($contenu));
        foreach ($exportAvistEtats as $exportAvistEtat) {
            $contenu = $exportAvistEtat->getEuCd() . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getTypeMe()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getNomMasseEau()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getCt()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getCtLib()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getUhr()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getUhrLib()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getDepts()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getCdEtat()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getUtilisateur()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getPropositionDate()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getGroupe()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getLibelle()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getESdage2016()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getESdage2016Lib()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getEpropose()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getEproposeLib()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getERetenu()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getERetenuLib()) . ";";
            $contenu = $contenu . rtrim($exportAvistEtat->getCommentaire()) . ";\n";
            $contenu = str_replace(CHR(13) . CHR(10), "", $contenu);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            fputs($fic, $contenu);
        }
        fclose($fic);

        //return $this->render('AeagEdlBundle:Etat:csv.html.twig', array('fichier' => $nom_fichier_etat));
        $ext = strtolower(pathinfo($nom_fichier_etat, PATHINFO_EXTENSION));
        header('Content-Type', 'application/' . $ext);
        header('Content-disposition: attachment; filename="' . $nom_fichier_etat . '"');
        header('Content-Length: ' . filesize($repertoire . '/' . $nom_fichier_etat));
        readfile($repertoire . '/' . $nom_fichier_etat);
        exit();
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
        $repoPressionMe = $emEdl->getRepository('AeagEdlBundle:PressionMe');
        $repoPressionDerniereProposition = $emEdl->getRepository('AeagEdlBundle:PressionDerniereProposition');

        $pressionGroupes = $repo->getPressionGroupe();

        if (!$pressionGroupes) {
            throw $this->createNotFoundException('Table PressionGroupe non trouvée : ');
        }

        $me = $meRepo->findOneBy(array('euCd' => $code));

        if (!$me) {
            throw $this->createNotFoundException('Masse d\'eau non trouvée : ' . $code);
        }

        $avisHistorique = $repoAvisHistorique->getAvisHistoriqueByCodeEpr($code, 'Pression');

        $nbPressions = count($pressionGroupes);

        $tabPressionGroupes = array();
        $i = 0;
        foreach ($pressionGroupes as $pressionGroupe) {
            $tabPressionGroupes[$i]['pressionGroupe'] = $pressionGroupe;

            $pressions = $repoPressionMe->getPressionMe($code, $pressionGroupe->getCdGroupe());
            $nbPressions = $repoPressionMe->getNbPressionMe($code, $pressionGroupe->getCdGroupe());
            $tabPressionGroupes[$i]['nbPressions'] = $nbPressions;
            $tabPressions = array();
            $j = 0;
            foreach ($pressions as $pression) {
                $tabPressions[$j]['pression'] = $pression;

                $proposeds = $pression->getProposed();
                $tabProposeds = array();
                $k = 0;
                foreach ($proposeds as $proposed) {
                    $tabProposeds[$k] = $proposed;
                    $k++;
                }
                if (count($tabProposeds) > 0) {
                    usort($tabProposeds, create_function('$a,$b', 'return strcasecmp($a->getPropositionDate(),$b->getPropositionDate());'));
                    $tabPressions[$j]['proposeds'] = $tabProposeds;
                } else {
                    $tabPressions[$j]['proposeds'] = null;
                }

                $derniereProp = $repoPressionDerniereProposition->getDernierePropositionByEucdCdPression($pression->getEuCd(), $pression->getCdPression());



                if (!$derniereProp) {
                    $derniereProposition = null;
                } else {
                    $derniereProposition = $derniereProp[0];
                }
                $tabPressions[$j]['derniereProp'] = $derniereProposition;
                $j++;
            }
            $tabPressionGroupes[$i]['pressions'] = $tabPressions;
            $i++;
        }

        return $this->render('AeagEdlBundle:Pression:pressionGroupe.html.twig', array(
                    'pressionGroupes' => $tabPressionGroupes,
                    'avisHistorique' => $avisHistorique,
                    'me' => $me,
                    'user' => $user,
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

        $repoPressionDerniereProposition = $emEdl->getRepository('AeagEdlBundle:PressionDerniereProposition');
        $repo = $emEdl->getRepository('AeagEdlBundle:PressionGroupe');
        $pressionGroupe = $repo->findOneBy(array('cdGroupe' => $cdGroupe));

        $repo = $emEdl->getRepository('AeagEdlBundle:PressionMe');
        $pressions = $repo->getPressionMe($code, $cdGroupe);
        $nbPressions = $repo->getNbPressionMe($code, $cdGroupe);

        $tabPressions = array();
        $i = 0;
        foreach ($pressions as $pression) {
            $tabPressions[$i]['pression'] = $pression;

            $derniereProp = $repoPressionDerniereProposition->getDernierePropositionByEucdCdPression($pression->getEuCd(), $pression->getCdPression());

            if (!$derniereProp) {
                $derniereProposition = null;
            } else {
                $derniereProposition = $derniereProp[0];
            }
            $tabPressions[$i]['derniereProp'] = $derniereProposition;
            $i++;
        }

        if ($session->get('UrlRetour') == '') {
            $session->set('UrlRetour', $this->generateUrl('AeagEdlBundle_listeMasseEau', array('page' => 1)));
        }

//        return new Response(  \Symfony\Component\VarDumper\VarDumper::dump($tabPressions));
//        return new Response ('');

        return $this->render('AeagEdlBundle:Pression:pression.html.twig', array(
                    'pressionGroupe' => $pressionGroupe,
                    'tabPressions' => $tabPressions,
                    'nbPressions' => $nbPressions,
                    'url' => $session->get('UrlRetour'),
                        )
        );
    }

    public function exportPressionAction() {

// Liste des dossiers selectionnés
        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'acceuil');
        $session->set('controller', 'default');
        $session->set('fonction', 'exportPression');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');
        $repoUtilisateur = $emEdl->getRepository('AeagEdlBundle:Utilisateur');
        $repoExportAvisPression = $emEdl->getRepository('AeagEdlBundle:ExportAvisPression');

        if ($user) {
            $utilisateur = $repoUtilisateur->getUtilisateurByExtid($user->getId());
        } else {
            $utilisateur = null;
        }

        if ($session->get('whereCsv') != null) {
            $exportAvistPressions = $repoExportAvisPression->getExportAvisPressionByWhere($session->get('whereCsv'));
        } else {
            $exportAvistPressions = $repoExportAvisPression->getExportAvisPressions();
        }
        $repertoire = "fichiers";
        $date_import = date('Ymd_His');
        if ($utilisateur) {
            $nom_fichier_pression = "ExportAvisPression_" . $utilisateur->getUserName() . ".csv";
        } else {
            $nom_fichier_pression = "ExportAvisPression_" . $date_import . ".csv";
        }

        $fic_import_pression = $repertoire . "/" . $nom_fichier_pression;
//ouverture fichier
        $fic = fopen($fic_import_pression, "w");
        $contenu = "eu_cd; type_me; nom_masse_eau; ct; ct_lib; uhr; uhr_lib; depts; cd_pression; utilisateur; proposition_date; groupe; libelle; e_sdage2016; e_sdage2016_lib; e_propose; e_propose_lib; e_retenu; e_retenu_lib; commentaire\n";
        fputs($fic, utf8_decode($contenu));
        foreach ($exportAvistPressions as $exportAvisPression) {
            $contenu = $exportAvisPression->getEuCd() . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getTypeMe()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getNomMasseEau()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getCt()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getCtLib()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getUhr()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getUhrLib()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getDepts()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getCdPression()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getUtilisateur()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getPropositionDate()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getGroupe()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getLibelle()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getPSdage2016()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getPSdage2016Lib()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getPpropose()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getPproposeLib()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getPRetenu()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getPRetenuLib()) . ";";
            $contenu = $contenu . rtrim($exportAvisPression->getCommentaire()) . ";\n";
            $contenu = str_replace(CHR(13) . CHR(10), "", $contenu);
            $contenu = \iconv("UTF-8", "Windows-1252//TRANSLIT", $contenu);
            fputs($fic, $contenu);
        }
        fclose($fic);
        //return $this->render('AeagEdlBundle:Pression:csv.html.twig', array('fichier' => $nom_fichier_pression));
        $ext = strtolower(pathinfo($nom_fichier_pression, PATHINFO_EXTENSION));
        header('Content-Type', 'application/' . $ext);
        header('Content-disposition: attachment; filename="' . $nom_fichier_pression . '"');
        header('Content-Length: ' . filesize($repertoire . '/' . $nom_fichier_pression));
        readfile($repertoire . '/' . $nom_fichier_pression);
        exit();
    }

    public function massedeauAction($code) {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'acceuil');
        $session->set('controller', 'default');
        $session->set('fonction', 'massedeau');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

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

    public function contactAction() {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'acceuil');
        $session->set('controller', 'default');
        $session->set('fonction', 'contact');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

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

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'acceuil');
        $session->set('controller', 'default');
        $session->set('fonction', 'majUtilisateurs');
        $em = $this->get('doctrine')->getManager();
        $emEdl = $this->get('doctrine')->getManager('edl');

        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repo = $emEdl->getRepository('AeagEdlBundle:Utilisateur');

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

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'acceuil');
        $session->set('controller', 'default');
        $session->set('fonction', 'initUtilisateur');
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
