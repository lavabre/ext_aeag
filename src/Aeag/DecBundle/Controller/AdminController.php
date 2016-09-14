<?php

namespace Aeag\DecBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\True;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\DecBundle\Entity\Statut;
use Aeag\DecBundle\Form\Collecteur\EnvoyerMessageType;
use Aeag\AeagBundle\Entity\Ouvrage;
use Aeag\DecBundle\Entity\CollecteurProducteur;
use Aeag\DecBundle\Entity\DeclarationCollecteur;
use Aeag\DecBundle\Entity\DeclarationProducteur;
use Aeag\DecBundle\Entity\SousDeclarationCollecteur;
use Aeag\DecBundle\Entity\DeclarationDetail;
use Aeag\DecBundle\Entity\Form\Collecteur\EnvoyerMessage;
use Aeag\DecBundle\Form\Collecteur\MajCompteType;
use Aeag\DecBundle\Form\Collecteur\MajOuvrageType;
use Aeag\DecBundle\Form\Collecteur\MajProducteurType;
use Aeag\DecBundle\Form\Collecteur\CrudDeclarationDetailType;
use Aeag\DecBundle\Form\Collecteur\FormReadDeclarationDetailType;
use Aeag\DecBundle\Entity\Form\Collecteur\MajProducteur;
use Aeag\DecBundle\Entity\Form\Collecteur\CrudDeclarationDetail;
use Aeag\DecBundle\Controller\CollecteurController;
use Aeag\AeagBundle\Controller\AeagController;

/**
 * A Gestion administrateur
 *
 * 
 */
class AdminController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'index');
        $session->set('controller', 'admin');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');
        
         if (is_object($user)) {
            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);
            $stat = AeagController::statistiquesAction($user, $em, $session);
        }

        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');

        $paraAnnee = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));
        $annee = $paraAnnee->getLibelle();
        $annees = $repoDeclarationCollecteur->getAnnees();
        $session->set('annees', $annees);
        $session->set('refMess', array());
        return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeDeclarationCollecteurs', array('annee' => $annee,
                            'statut' => '99')));
    }

    public function aideAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'aide');
        $session->set('controller', 'admin');
        $session->set('fonction', 'aide');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $annee = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));
        return $this->render('AeagDecBundle:Admin:aide.html.twig', array(
                    'annee' => $annee,
        ));
    }

    public function listeDeclarationCollecteursAction($annee = null, $statut = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'admin');
        $session->set('fonction', 'listeDeclarationCollecteurs');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $annees = $repoDeclarationCollecteur->getAnnees();
        $session->set('annees', $annees);
        $session->set('statut', $statut);
        if (!$annee) {
            $paraAnnee = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));
            $annee = $paraAnnee->getLibelle();
        }
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');


        $declarations = $repoDeclarationCollecteur->getDeclarationCollecteursByAnnee($annee);
        if (!$statut or $statut == '99') {
            $Statut = new Statut();
        } else {
            $Statut = $repoStatut->getStatutByCode($statut);
        }

        $odec = array();
        $i = 0;
        $maxSousDeclaration = 0;
        $collecteurs = $repoOuvrage->getOuvragesByType('ODEC');
        foreach ($collecteurs as $collecteur) {
            $declaration = $repoDeclarationCollecteur->getDeclarationCollecteurByCollecteurAnnee($collecteur->getId(), $annee);
            $sousDeclarations = null;
            if ($declaration) {
                if (!$statut or $statut == '99') {
                    $sousDeclarations = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteur($declaration->getId());
                } else {
                    $sousDeclarations = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteurStatut($declaration->getId(), $Statut->getCode());
                }
                if ($sousDeclarations) {
                    $maxSousDeclaration = $repoSousDeclarationCollecteur->getMaxNumero($annee);
                }
            }
            if (!$statut or $statut == '99') {
                $odec[$i]['collecteur'] = $collecteur;
                if ($declaration) {
                    $odec[$i]['declaration'] = $declaration;
                    if ($sousDeclarations) {
                        $odec[$i]['sousDeclarations'] = $sousDeclarations;
                    } else {
                        $odec[$i]['sousDeclarations'] = null;
                    }
                } else {
                    $odec[$i]['declaration'] = null;
                    $odec[$i]['sousDeclarations'] = null;
                }
                $i++;
            } else {
                if ($sousDeclarations) {
                    $odec[$i]['collecteur'] = $collecteur;
                    $odec[$i]['declaration'] = $declaration;
                    $odec[$i]['sousDeclarations'] = $sousDeclarations;
                    $i++;
                }
            }
        }
//        
//       
//        foreach ($declarations as $declaration) {
//            $collecteur = $repoOuvrage->getOuvrageById($declaration->getCollecteur());
//            if (!$Statut or $statut == '99') {
//                $sousDeclarations = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteur($declaration->getId());
//            } else {
//                $sousDeclarations = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteurStatut($declaration->getId(), $Statut->getCode());
//            }
//            if ($sousDeclarations) {
//                $maxSousDeclaration = $repoSousDeclarationCollecteur->getMaxNumero($annee);
//                $odec[$i][0] = $declaration;
//                $odec[$i][1] = $sousDeclarations;
//                $odec[$i][2] = $collecteur;
//                $i++;
//            }
//        }
        //return new response ('nb declarations : ' . count($odec));

        $session->set('retour', $this->generateUrl('AeagDecBundle_admin_listeDeclarationCollecteurs', array('annee' => $annee, 'statut' => $statut)));

        return $this->render('AeagDecBundle:Admin:listeDeclarationCollecteurs.html.twig', array(
                    'dossiers' => $odec,
                    'maxSousdeclaration' => $maxSousDeclaration,
                    'annee' => $annee,
                    'annees' => $session->get('annees'),
                    'statut' => $Statut
        ));
    }

    public function validerSousDeclarationAction($sousDeclarationCollecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'admin');
        $session->set('fonction', 'validerSousDeclaration');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $repoOuvrage = $emDec->getRepository('AeagDecBundle:Ouvrage');
        $repoOuvrageCorrespondant = $emDec->getRepository('AeagDecBundle:OuvrageCorrespondant');
        $repoUser = $em->getRepository('AeagUserBundle:User');

        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurById($sousDeclarationCollecteur_id);
        $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($sousDeclarationCollecteur->getDeclarationCollecteur()->getId());
        $collecteur = $repoOuvrage->getOuvrageById($sousDeclarationCollecteur->getDeclarationCollecteur()->getCollecteur()->getId());
        $correspondants = $repoOuvrageCorrespondant->getOuvrageCorrespondantByOuvrage($collecteur->getId());
        $statut = $repoStatut->getStatutByCode('30');

        if ($sousDeclarationCollecteur) {
            $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsBySousDeclarationCollecteur($sousDeclarationCollecteur->getId());
            if ($declarationDetails) {
                foreach ($declarationDetails as $declarationDetail) {
                    $declarationDetail->setStatut($statut);
                    $emDec->persist($declarationDetail);
                }
            }
            $sousDeclarationCollecteur->setStatut($statut);
            $emDec->persist($sousDeclarationCollecteur);
            $session->getFlashBag()->add('notice-success', "La déclaration n° " . $sousDeclarationCollecteur->getNumero() . " a été approuvée avec succès !");

            foreach ($correspondants as $correspondant) {
                $userOdec = $repoUser->getUserById($correspondant->getUser());
                $notification = new Notification();
                $notification->setRecepteur($userOdec->getId());
                $notification->setEmetteur($user->getId());
                $notification->setNouveau(true);
                $notification->setIteration(2);
                $notification->setMessage("La déclaration n° " . $sousDeclarationCollecteur->getNumero() . " a été approuvée par le responsable de l'agence de l'eau. ");
                $em->persist($notification);
            }
            $em->flush();
            $ok = CollecteurController::majStatutDeclarationCollecteursAction($declarationCollecteur->getid(), $user, $em, $session);
        } else {
            $session->getFlashBag()->add('notice-warning', "La déclaration N°" . $sousDeclarationCollecteur->getNumero() . " n' existe pas !");
        }

        return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeDeclarationCollecteurs', array('annee' => $sousDeclarationCollecteur->getDeclarationCollecteur()->getAnnee(), 'statut' => '30')));
    }

    public function devaliderSousDeclarationAction($sousDeclarationCollecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'admin');
        $session->set('fonction', 'devaliderSousDeclaration');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurById($sousDeclarationCollecteur_id);
        $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($sousDeclarationCollecteur->getDeclarationCollecteur()->getId());
        if ($sousDeclarationCollecteur) {
            $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsBySousDeclarationCollecteur($sousDeclarationCollecteur->getId());
            $statut = $repoStatut->getStatutByCode('22');
            if ($declarationDetails) {
                foreach ($declarationDetails as $declarationDetail) {
                    $declarationDetail->setStatut($statut);
                    $emDec->persist($declarationDetail);
                }
            }
            $statut = $repoStatut->getStatutByCode('22');
            $sousDeclarationCollecteur->setStatut($statut);
            $emDec->persist($sousDeclarationCollecteur);
            $session->getFlashBag()->add('notice-success', "La déclaration n° " . $sousDeclarationCollecteur->getNumero() . " a été dévalidée avec succès !");
            $emDec->flush();
            $ok = CollecteurController::majStatutDeclarationCollecteursAction($declarationCollecteur->getid(), $user, $em, $session);
        } else {
            $session->getFlashBag()->add('notice-warning', "La déclaration N°" . $sousDeclarationCollecteur->getNumero() . " n' existe pas !");
        }

        return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeDeclarationCollecteurs', array('annee' => $sousDeclarationCollecteur->getDeclarationCollecteur()->getAnnee(), 'statut' => '22')));
    }

    public function supprimerSousDeclarationAction($sousDeclarationCollecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'admin');
        $session->set('fonction', 'supprimerSousDeclaration');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoDeclarationProducteur = $emDec->getRepository('AeagDecBundle:DeclarationProducteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');


        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurById($sousDeclarationCollecteur_id);

        if ($sousDeclarationCollecteur) {
            $annee = $sousDeclarationCollecteur->getDeclarationCollecteur()->getAnnee();
            $statut = $sousDeclarationCollecteur->getStatut()->getCode();
            if ($sousDeclarationCollecteur->getStatut()->getCode() < '40') {
                $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsBySousDeclarationCollecteur($sousDeclarationCollecteur->getId());
                if ($declarationDetails) {
                    foreach ($declarationDetails as $declarationDetail) {

                        $declarationProducteur = $repoDeclarationProducteur->getDeclarationProducteurById($declarationDetail->getDeclarationProducteur()->getId());
                        if ($declarationProducteur) {

                            $declarationProducteur->setQuantiteReel($declarationProducteur->getQuantiteReel() - $declarationDetail->getQuantiteReel());
                            $declarationProducteur->setMontReel($declarationProducteur->getMontReel() - $declarationDetail->getMontReel());
                            $declarationProducteur->setQuantiteRet($declarationProducteur->getQuantiteRet() - $declarationDetail->getQuantiteRet());
                            $declarationProducteur->setMontRet($declarationProducteur->getMontRet() - $declarationDetail->getMontRet());
                            $declarationProducteur->setQuantiteAide($declarationProducteur->getQuantiteAide() - $declarationDetail->getQuantiteAide());
                            $declarationProducteur->setMontAide($declarationProducteur->getMontAide() - $declarationDetail->getMontaide());
                            $emDec->persist($declarationProducteur);
                        }

                        $em->remove($declarationDetail);
                    }
                }


                $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($sousDeclarationCollecteur->getDeclarationCollecteur()->getId());
                $declarationCollecteur->setQuantiteReel($declarationCollecteur->getQuantiteReel() - $sousDeclarationCollecteur->getQuantiteReel());
                $declarationCollecteur->setMontReel($declarationCollecteur->getMontReel() - $sousDeclarationCollecteur->getMontReel());
                $declarationCollecteur->setQuantiteRet($declarationCollecteur->getQuantiteRet() - $sousDeclarationCollecteur->getQuantiteRet());
                $declarationCollecteur->setMontRet($declarationCollecteur->getMontRet() - $sousDeclarationCollecteur->getMontRet());
                $declarationCollecteur->setQuantiteAide($declarationCollecteur->getQuantiteAide() - $sousDeclarationCollecteur->getQuantiteAide());
                $declarationCollecteur->setMontAide($declarationCollecteur->getMontAide() - $sousDeclarationCollecteur->getMontaide());
                $declarationCollecteur->setMontantApDispo($declarationCollecteur->getMontantApDispo() - $sousDeclarationCollecteur->getMontantApDispo());
                $emDec->persist($declarationCollecteur);



                $em->remove($sousDeclarationCollecteur);

                $session->getFlashBag()->add('notice-success', "La déclaration n° " . $sousDeclarationCollecteur->getNumero() . " a été supprimée avec succès !");

                $emDec->flush();

                $ok = CollecteurController::majStatutDeclarationCollecteursAction($declarationCollecteur->getid(), $user, $em, $session);
            } else {
                $session->getFlashBag()->add('notice-warning', "La déclaration N°" . $sousDeclarationCollecteur->getNumero() . " ne peut être supprimée !");
            }
        } else {
            $session->getFlashBag()->add('notice-warning', "La déclaration N°" . $sousDeclarationCollecteur->getNumero() . " n' existe déjà !");
        }

        return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeDeclarationCollecteurs', array('annee' => $annee, 'statut' => $statut)));
    }

    public function transfererSousDeclarationAction($sousDeclarationCollecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'admin');
        $session->set('fonction', 'tansfererSousDeclaration');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoUser = $em->getRepository('AeagUserBundle:User');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');

        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurById($sousDeclarationCollecteur_id);
        $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsBySousDeclarationCollecteur($sousDeclarationCollecteur->getid());
        $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($sousDeclarationCollecteur->getDeclarationCollecteur()->getId());
        $collecteur = $repoOuvrage->getOuvrageById($sousDeclarationCollecteur->getDeclarationCollecteur()->getCollecteur());
        $correspondants = $repoOuvrageCorrespondant->getOuvrageCorrespondantByOuvrage($collecteur->getId());

        $statut = $repoStatut->getStatutByCode('40');

        $parametre = $repoParametre->findOneBy(array('code' => 'REP_IMPORT'));
        $rep_import = $parametre->getLibelle();
        //$nom_fichier = 'dec_' . $collecteur->getNumero() . '_' . str_replace(' ','_',$collecteur->getLibelle()) . '_' . $declarationCollecteur->getAnnee() . '_' . $sousDeclarationCollecteur->getNumero() . '.csv';
        $nom_fichier = 'dec_' . $collecteur->getNumero() . '_' . $collecteur->getLibelle() . '_' . $declarationCollecteur->getAnnee() . '_' . $sousDeclarationCollecteur->getNumero() . '.csv';
        $nom_fichier = $this->wd_remove_accents($nom_fichier);
        $fic_import = $rep_import . "/" . $nom_fichier;
        $message = "";
        $fic = fopen($fic_import, "w");
        foreach ($declarationDetails as $entity) {
            $producteur = $repoOuvrage->getOuvrageById($entity->getDeclarationproducteur()->getProducteur());
            $contenu = $producteur->getSiret() . ';';
            $contenu = $contenu . substr($producteur->getLibelle(), 0, 31) . ';';
            $contenu = $contenu . $producteur->getCp() . ';';
            $contenu = $contenu . $entity->getNaf()->getCode() . ';';
            $contenu = $contenu . $entity->getNumFacture() . ';';
            $contenu = $contenu . $entity->getDateFacture()->format('d/m/Y') . ';';
            $contenu = $contenu . $entity->getDechet()->getCode() . ';';
            $contenu = $contenu . $entity->getNature() . ';';
            $contenu = $contenu . $entity->getTraitFiliere()->getCode() . ';';
            if ($entity->getCentreTraitement()) {
                $centreTraitement = $repoOuvrage->getOuvrageById($entity->getCentreTraitement());
                $contenu = $contenu . $centreTraitement->getNumero() . ';';
            } else {
                $contenu = $contenu . ';';
            }
            $contenu = $contenu . $entity->getQuantiteRet() . ';';
            $contenu = $contenu . $entity->getFiliere()->getCode() . ';';
            $contenu = $contenu . $entity->getCoutFacture() . ';';
            $contenu = $contenu . $entity->getMontAide() . ';';
            if ($entity->getCentreDepot()) {
                $centreDepot = $repoOuvrage->getOuvrageById($entity->getCentreDepot());
                $contenu = $contenu . $centreDepot->getSiret() . ';';
            } else {
                $contenu = $contenu . ';';
            }
            if ($entity->getCentreTransit()) {
                $centreTransit = $repoOuvrage->getOuvrageById($entity->getCentreTransit());
                $contenu = $contenu . $centreTransit->getSiret() . ';';
            } else {
                $contenu = $contenu . ';';
            }
            $contenu = $contenu . date('d/m/Y H:i') . "\n";
            fputs($fic, $contenu);
            $entity->setStatut($statut);
            $emDec->persist($entity);
        }
        fclose($fic);

        $sousDeclarationCollecteur->setStatut($statut);
        $emDec->persist($sousDeclarationCollecteur);

        //$message = $this->telechargerFichierAction($nom_fichier, $rep_import);
        $message = $this->Ftp($rep_import, 'Applications/Transfert/Dec/Import');

        if ($message == 'ko') {
            $session->getFlashBag()->add('notice-error', "ATTENTION : La déclaration n° " . $sousDeclarationCollecteur->getNumero() . " n'a pas été transférée à l'agence de l'eau  !");
            return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeSousDeclarations', array('declarationCollecteur_id' => $sousDeclarationCollecteur->getDeclarationCollecteur()->getId())));
        } else {

            $session->getFlashBag()->add('notice-success', "La déclaration n° " . $sousDeclarationCollecteur->getNumero() . " a été transférée à l'agence de l'eau avec succès !");

            foreach ($correspondants as $cor) {
                $notification = new Notification();
                $correspondant = $repoCorrespondant->getCorrespondantById($cor->getCorrespondant()->getId());
                $userOdecs = $repoUser->getUserByCorrespondant($correspondant->getId());
                foreach ($userOdecs as $userOdec) {
                    $notification->setRecepteur($userOdec->getId());
                    $notification->setEmetteur($user->getId());
                    $notification->setNouveau(true);
                    $notification->setIteration(2);
                    $notification->setMessage("La déclaration n° " . $sousDeclarationCollecteur->getNumero() . " est en cours de traitement par le responsable de l'agence de l'eau.");
                    $em->persist($notification);
                }
            }


            // Récupération du service.
            $mailer = $this->get('mailer');
            // message aux administrateurs du site
            $admins = $repoUser->getUsersByRole('ROLE_ADMINDEC');
            foreach ($admins as $admin) {
                // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
                $message = \Swift_Message::newInstance()
                        ->setSubject('Déclaration ' . $sousDeclarationCollecteur->getDeclarationCollecteur()->getAnnee() . ' N° ' . $sousDeclarationCollecteur->getNumero() . ' du collecteur : ' . $collecteur->getNumero() . ' ' . $collecteur->getLibelle() . ' exportée')
                        ->setFrom('automate@eau-adour-garonne.fr')
                        ->setTo($admin->getEmail())
                        ->setBody($this->renderView('AeagDecBundle:Collecteur:exporterEmail.txt.twig', array(
                                    'collecteur' => $collecteur,
                                    'declaration' => $sousDeclarationCollecteur)))
                        ->attach(\Swift_Attachment::fromPath($fic_import));

                // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
                $mailer->send($message);
            }


            $em->flush();
            $emDec->flush();

            $ok = CollecteurController::majStatutDeclarationCollecteursAction($declarationCollecteur->getid(), $user, $emDec, $session);

// sauvegardes
            $source = $fic_import;
            $dest = $rep_import . "/Sauvegardes/" . $nom_fichier;
//        $dest1 = "fichiers/dec/" . $nom_fichier;
//        copy($source, $dest1);
            if (copy($source, $dest)) {
                if (unlink($source)) {
                    $message = " Fichier : " . $nom_fichier . " importer sur AEAG";
                    $ok = 'ok';
                } else {
                    $message = " Fichier : " . $nom_fichier . " importer sur AEAG avec succès mais impossible de le supprimer dans le répertoire " . $rep;
                    $ok = 'ko';
                }
            } else {
                $message = " Fichier : " . $nom_fichier . " importer sur AEAG avec succès mais impossible de le déplacer dans le répertoire de sauvegarde " . $rep . "/Sauvegardes";
                $ok = 'ko';
            }
            //return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeDeclarationCollecteurs', array('annee' => $sousDeclarationCollecteur->getDeclarationCollecteur()->getAnnee(), 'statut' => '40')));
            return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeSousDeclarations', array('declarationCollecteur_id' => $sousDeclarationCollecteur->getDeclarationCollecteur()->getId())));
        }
    }

    public function envoyerMessageAction($id = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'admin');
        $session->set('fonction', 'envoyerMessage');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');

        $User = $repoUsers->getUserById($id);

        if (!$User) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement avec la cle : ' . $id);
        }


        $envoyerMessage = new EnvoyerMessage();
        $form = $this->createForm(new EnvoyerMessageType(array($User->getEmail(), $User->getEmail1(), $User->getEmail2())), $envoyerMessage);
        $message = null;


        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);
            if ($form->isValid()) {
                $message = new Message();
                $message->setRecepteur($User->getId());
                $message->setEmetteur($user->getid());
                $message->setNouveau(true);
                $message->setIteration(0);
                $texte = $envoyerMessage->getMessage();
                $message->setMessage($texte);
                $em->persist($message);

                // Récupération du service.
                $mailer = $this->get('mailer');
                $destinataires = explode(",", $envoyerMessage->getDestinataire());
                foreach ($destinataires as $destinataire) {
                    // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
                    $mail = \Swift_Message::newInstance('Wonderful Subject')
                            ->setSubject($envoyerMessage->getSujet())
                            ->setFrom(array('automate@eau-adour-garonne.fr' => 'Extranet Dec'))
                            ->setTo(array($destinataire))
                            ->setBody($envoyerMessage->getMessage());

                    // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
                    $mailer->send($mail);
                }

                $notification = new Notification();
                $notification->setRecepteur($user->getId());
                $notification->setEmetteur($user->getId());
                $notification->setNouveau(true);
                $notification->setIteration(0);
                $notification->setMessage('Message envoyé à ' . $User->getUsername());
                $em->persist($notification);
                $em->flush();
                $notifications = $repoNotifications->getNotificationByRecepteur($user);
                $session->set('Notifications', $notifications);


                //$this->get('session')->getFlashBag()->add('notice', 'Vous avez un nouveau message');


                return $this->redirect($this->generateUrl('AeagUserBundle_User'));
            }
        }

        $annee = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));

        $session->set('annee', $annee->getLibelle());

        return $this->render('AeagDecBundle:Admin:envoyerMessage.html.twig', array(
                    'User' => $User,
                    'annee' => $session->get('annee'),
                    'form' => $form->createView()
        ));
    }

    public function majCompteursDeclarationsAction($annee = null) {

        $session = $this->get('session');
        $emDec = $this->getDoctrine()->getManager('dec');
        $user = $this->getUser();
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $declarations = $repoDeclarationCollecteur->getDeclarationCollecteursByAnnee($annee);
        $ok = null;
        foreach ($declarations as $declaration) {
            $ok = CollecteurController::majStatutDeclarationCollecteursAction($declaration->getId(), $user, $emDec, $session);
            //  print_r($declaration->getId() . ' statut : ' . $declaration->getStatut()->getCode());
        }
        return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeDeclarationCollecteurs', array('annee' => $annee, 'statut' => '99')));
    }

    public function majCompteursProducteursAction($annee = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'admin');
        $session->set('fonction', 'majCompteursProducteurs');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $repoDeclarationProducteur = $emDec->getRepository('AeagDecBundle:DeclarationProducteur');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');


//        $declarationProducteurs = $repoDeclarationProducteur->getDeclarationProducteursByAnnee($annee);
//        $declarationProducteurEncours = null;
//        $nbModifier = 0;
//        $nbSupprimer = 0;
//        $nbLus = 0;
//        foreach ($declarationProducteurs as $declarationProducteur) {
//            if (!$declarationProducteurEncours) {
//                $declarationProducteurEncours = $declarationProducteur;
//                $nb = 0;
//            }
//            if ($declarationProducteurEncours->getProducteur() == $declarationProducteur->getProducteur()) {
//                $nb++;
//                if ($nb > 1) {
//                    $nbLus++;
//                    $declarationProducteurEncours->setQuantiteReel($declarationProducteurEncours->getQuantiteReel() + $declarationProducteur->getQuantiteReel());
//                    $declarationProducteurEncours->setQuantiteRet($declarationProducteurEncours->getQuantiteRet() + $declarationProducteur->getQuantiteRet());
//                    $declarationProducteurEncours->setQuantiteAide($declarationProducteurEncours->getQuantiteAide() + $declarationProducteur->getQuantiteAide());
//                    $declarationProducteurEncours->setMontReel($declarationProducteurEncours->getMontReel() + $declarationProducteur->getMontReel());
//                    $declarationProducteurEncours->setMontRet($declarationProducteurEncours->getMontRet() + $declarationProducteur->getMontRet());
//                    $declarationProducteurEncours->setMontAide($declarationProducteurEncours->getMontAide() + $declarationProducteur->getMontAide());
//                    $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsByDeclarationProducteur($declarationProducteur->getId());
//                    foreach ($declarationDetails as $declarationDetail) {
//                        $declarationDetail->setDeclarationProducteur($declarationProducteurEncours);
//                        $emDec->persist($declarationDetail);
// //                       $emDec->flush();
////                        print_r ('declaration detail modifie : ' . $declarationDetail->getId() . '  DeclarationProducteur devient : ' . $declarationProducteurEncours->getId());
//                    }
//                    $emDec->remove($declarationProducteur);
//                    $nbSupprimer++;
////                    print_r('    declaration producteur supprimée : '  . $declarationProducteur->getId());
//                }
//            } else {
//                if ($nb > 1) {
//                    $emDec->persist($declarationProducteurEncours);
//                    $nbModifier++;
//                }
//                $declarationProducteurEncours = $declarationProducteur;
//                $nb = 1;
//            }
//        }
//        if ($nb > 1) {
//            $emDec->persist($declarationProducteurEncours);
//            $nbModifier++;
//        }
//        $emDec->flush();
//        return new Response('nb : ' . count($declarationProducteurs) . ' Lus : ' . $nbLus . ' modifier : ' . $nbModifier . ' supprimer : ' . $nbSupprimer);
//        $producteurs = $repoOuvrage->getAllProducteurs();
//        $producteurEncours = null;
//
//        $nbModifier = 0;
//        $nbSupprimer = 0;
//        $nbLus = 0;
//        foreach ($producteurs as $producteur) {
//            if (!$producteurEncours) {
//                $producteurEncours = clone($producteur);
//                $nb = 0;
//            }
//            if ($producteurEncours->getSiret() == $producteur->getSiret()) {
//                $nb++;
//                if ($nb > 1) {
//                    $nbLus++;
//                    $nb++;
//                    $declarationProducteur = $repoDeclarationProducteur->getDeclarationProducteurByProducteurAnnee($producteur->getId(), $annee);
//                    if ($declarationProducteur) {
//                        $producteurLu = $repoOuvrage->getOuvrageById($declarationProducteur->getProducteur());
//                        if ($producteurLu->getSiret() == $producteurEncours->getSiret()) {
//                            $declarationProducteur->setProducteur($producteurEncours->getId());
//                            if ($declarationProducteur->getQuantiteReel() == 0 and $declarationProducteur->getMontReel() == 0 and
//                                    $declarationProducteur->getQuantiteRet() == 0 and $declarationProducteur->getMontRet() == 0 and
//                                    $declarationProducteur->getQuantiteAide() == 0 and $declarationProducteur->getMontAide() == 0) {
//                                $emDec->remove($declarationProducteur);
//                            } else {
//                                $emDec->persist($declarationProducteur);
//                            }
//                            $nbModifier++;
//                            if ($producteurLu->getId() != $producteurEncours->getId()) {
//                                $em->remove($producteurLu);
//                                $nbSupprimer++;
//                            }
//                        }
//                    }
//                }
//            } else {
//                $producteurEncours = clone($producteur);
//                $nb = 0;
//            }
//        }
//        $emDec->flush();
//        $em->flush();
//        return new Response('nb : ' . count($producteurs) . ' Lus : ' . $nbLus . ' modifier : ' . $nbModifier . ' supprimer : ' . $nbSupprimer);
        //
        $declarationProducteurs = $repoDeclarationProducteur->getDeclarationProducteursByAnnee($annee);
        $nb = 0;
        $total = count($declarationProducteurs);
        foreach ($declarationProducteurs as $declarationProducteur) {
            $statut = $repoStatut->getStatutByCode('10');
            $totQuantiteReel = 0;
            $totMontReel = 0;
            $totQuantiteRet = 0;
            $totMontRet = 0;
            $totQuantiteAide = 0;
            $totMontAide = 0;
            $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsByDeclarationProducteur($declarationProducteur->getId());
            foreach ($declarationDetails as $declarationDetail) {
                if ($statut->getCode() < $declarationDetail->getStatut()->getCode()) {
                    $statut = $declarationDetail->getStatut();
                }
                if ($declarationDetail->getStatut()->getCode() != '11') {
                    $totQuantiteReel += $declarationDetail->getQuantiteReel();
                    $totMontReel += $declarationDetail->getMontreel();
                    $totQuantiteRet += $declarationDetail->getQuantiteRet();
                    $totMontRet += $declarationDetail->getMontret();
                    $totQuantiteAide += $declarationDetail->getQuantiteAide();
                    $totMontAide += $declarationDetail->getMontAide();
                }
            }
            if ($statut->getCode() == '10') {
                $statut = $repoStatut->getStatutByCode('20');
            } elseif ($statut->getCode() == '11') {
                $statut = $repoStatut->getStatutByCode('21');
            }
            $declarationProducteur->setStatut($statut);
            $declarationProducteur->setQuantiteRet($totQuantiteRet);
            $declarationProducteur->setMontRet($totMontRet);
            $declarationProducteur->setQuantiteReel($totQuantiteReel);
            $declarationProducteur->setMontReel($totMontReel);
            $declarationProducteur->setQuantiteAide($totQuantiteAide);
            $declarationProducteur->setMontAide($totMontAide);
            $emDec->persist($declarationProducteur);
            $nb++;
        }
        $emDec->flush();
        return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeDeclarationCollecteurs', array('annee' => $annee, 'statut' => '99')));
    }

    public function ajouterDeclarationAction($annee = null, $statut = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'admin');
        $session->set('fonction', 'ajouterDeclaration');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        if (is_object($user)) {
            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);
        }

        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');

        $Annee = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));
        $annee = $Annee->getLibelle();
        $nb = 0;
        $declarationCollecteurs = $repoDeclarationCollecteur->getDeclarationCollecteursByAnnee($annee);
        if (!$declarationCollecteurs) {
            $users = $repoUsers->getUsersByRole('ROLE_ODEC');
            foreach ($users as $user) {
                if ($user->getEnabled()) {
                    $collecteur = $repoOuvrage->getOuvrageByUserIdType($user->getId(), 'ODEC');
                    if (!$collecteur) {
                        $collecteur = $repoOuvrage->getOuvrageByUserNameType($user->getUsername(), 'ODEC');
                    }
                    if ($collecteur) {
                        $declarationCollecteur = new DeclarationCollecteur();
                        $declarationCollecteur->setAnnee($annee);
                        $declarationCollecteur->setCollecteur($collecteur->getId());
                        $statut = $repoStatut->getStatutByCode('20');
                        $declarationCollecteur->setStatut($statut);
                        $declarationCollecteur->setQuantiteReel(0);
                        $declarationCollecteur->setMontReel(0);
                        $declarationCollecteur->setQuantiteRet(0);
                        $declarationCollecteur->setMontRet(0);
                        $declarationCollecteur->setQuantiteAide(0);
                        $declarationCollecteur->setMontAide(0);
                        $declarationCollecteur->setDossierAide(null);
                        $declarationCollecteur->setMontantAp(0);
                        $declarationCollecteur->setMontantApDispo(0);
                        $emDec->persist($declarationCollecteur);
                        $nb++;
                    }
                }
            }
        }

        $emDec->flush();
        $session->getFlashBag()->add('notice-success', $nb . " dossiers créés pour l\'annee " . $annee . "  avec succès !");
        return $this->redirect($this->generateUrl('AeagDecBundle_admin_listeDeclarationCollecteurs', array('annee' => $annee, 'statut' => $statut->getCode())));
    }

    function Ftp($local_dir = null, $ftp_dir = null) {


        $mess = null;

        $FTP_HOST = "172.30.10.2";
        $FTP_USER = "ftpadmin";
        $FTP_PW = "pulp31";
        $FTP_ROOT_DIR = "D:/";
        $LOCAL_SERVER_DIR = $local_dir . "/";
        $FTP_DIR = $ftp_dir;
        $handle = opendir($LOCAL_SERVER_DIR);
        while (($file = readdir($handle)) !== false) {
            if (!is_dir($file) and $file != "Sauvegardes" and $file != 'Pdf') {
                $f[] = "$file";
            }
        }
        closedir($handle);
        sort($f);
        $count = 0;
        $mode = FTP_BINARY; // or FTP_ASCII
        $conn_id = ftp_connect($FTP_HOST);
        if (!$conn_id) {
            $mess = 'ko';
        } else {
            if (ftp_login($conn_id, $FTP_USER, $FTP_PW)) {
                $mess = $mess . "from: " . $LOCAL_SERVER_DIR . "<br>";
                $mess = $mess . "to: " . $FTP_HOST . $FTP_ROOT_DIR . $FTP_DIR . "<br>";
                ftp_pwd($conn_id);
                ftp_chdir($conn_id, $FTP_DIR);
                foreach ($f as $files) {
                    $from = fopen($LOCAL_SERVER_DIR . $files, "r");
                    if (ftp_fput($conn_id, $files, $from, $mode)) {
                        $count +=1;
                        $mess = $mess . $files . "<br>";
                    }
                }
                ftp_quit($conn_id);
            }
            $mess = $mess . "upload : $count files.";
        }
        return $mess;
    }

    public static function wd_remove_accents($str, $charset = 'utf-8') {


        $str = utf8_encode($str);


        $str = nl2br(strtr(
                        $str, array(
            '&eacute;' => 'é',
            '&ecirc;' => 'ê',
            '&egrave;' => 'è',
            '&Eagrave;' => 'È',
            '&Eacute;' => 'É',
            '&Ecirc;' => 'Ê',
            '&Agarve;' => 'À',
            '&Aacute;' => 'Á',
            '&Acirc;' => 'Â',
            '&Ccirc;' => 'Ç',
            '&Icirc;' => 'Î',
            '&Iuml;' => 'Ï',
            '&Ocirc;' => 'Ô',
            '&Uagrave;' => 'Ù',
            '&Ucirc;' => 'Û',
            '&agrave;' => 'à',
            '&aacute;' => 'á',
            '&acirc;' => 'â',
            '&ccirc;' => 'ç',
            '&icirc;' => 'î',
            '&ocirc;' => 'ô',
            '&ucirc;' => 'û',
            '&#039' => '\'',
            '&#168' => '\'',
            '&#424' => '\'',
            '\'' => ' ',
                ))
        );


        return $str;
    }

}
