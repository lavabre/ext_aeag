<?php

namespace Aeag\DecBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\True;
use Aeag\DecBundle\Entity\Browser;
use Aeag\DecBundle\Entity\Parametre;
use Aeag\DecBundle\Form\Collecteur\EnvoyerMessageType;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\AeagBundle\Entity\Ouvrage;
use Aeag\AeagBundle\Entity\Document;
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
use Aeag\DecBundle\DependencyInjection\PdfListeProducteurs;
use Aeag\DecBundle\DependencyInjection\PdfSousDeclarationCollecteur;
use Aeag\AeagBundle\Controller\AeagController;

class CollecteurController extends Controller {

    public function indexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'index');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        if (is_object($user)) {
            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);
        }

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $collecteur = $repoOuvrage->getOuvrageByUserIdType($user->getId(), 'ODEC');
        if (!$collecteur) {
            $collecteur = $repoOuvrage->getOuvrageByUserNameType($user->getUsername(), 'ODEC');
        }
        if (!$collecteur) {
            return $this->redirect($this->generateUrl('aeag_homepage'));
        }
        $session->set('collecteur', $collecteur->getId());
        $declarations = $repoDeclarationCollecteur->getDeclarationCollecteurByCollecteur($collecteur->getId());
        $odec = array();
        $i = 0;
        foreach ($declarations as $declaration) {
            $sousDeclarations = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteur($declaration->getId());
            foreach ($sousDeclarations as $sousDeclaration) {
                $odec[$i] = array(
                    'dec_id' => $declaration->getId(),
                    'dec_annee' => $declaration->getAnnee(),
                    'sousdec_id' => $sousDeclaration->getId(),
                    'sousdec_dateDebut' => $sousDeclaration->getDateDebut());
                $i++;
            }
        }
        $session->set('declarations', $odec);

        return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeDeclarations'));
    }

    public function aideAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'aide');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'aide');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $annee = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));
        return $this->render('AeagDecBundle:Collecteur:aide.html.twig', array(
                    'annee' => $annee,
        ));
    }

    public function majCompteAction(Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'majCompte');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoUser = $em->getRepository('AeagUserBundle:User');
        $repoNotifications = $emDec->getRepository('AeagDecBundle:Notification');

        $entity = $repoUser->find($user->getId());

        if (!$entity) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement.');
        }

        $form = $this->createForm(new MajCompteType(), $entity);
        $message = null;


        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);


            if ($form->isValid()) {

                $emDec->persist($entity);
                $emDec->flush();

                $notification = new Notification();
                $notification->setRecepteur($user->getId());
                $notification->setEmetteur($user->getId());
                $notification->setNouveau(true);
                $notification->setIteration(2);
                $notification->setMessage('Votre compte a été mis à jour.');
                $emDec->persist($notification);
                $emDec->flush();
                $notifications = $repoNotifications->getNotificationByRecepteur($user);
                $session->set('Notifications', $notifications);

//$this->get('session')->getFlashBag()->add('notice', 'Vous avez un nouveau message');


                return $this->redirect($this->generateUrl('aeag_dec'));
            }
        }

        return $this->render('AeagDecBundle:Collecteur:majCompte.html.twig', array(
                    'entity' => $entity,
                    'message' => $message,
                    'form' => $form->createView(),
        ));
    }

    public function envoyerMessageAction(Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'contact');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'envoyerMessage');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoMessages = $em->getRepository('AeagAeagBundle:Message');
        $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');

        $admin = $repoUsers->getUserByUsername('admindec');

        if (!$admin) {
            throw $this->createNotFoundException('Impossible de retouver les adminuistrateurs du site');
        }

        $odec = $repoOuvrage->getOuvrageByUserIdType($user->getId(), 'ODEC');

        $envoyerMessage = new EnvoyerMessage();
        $form = $this->createForm(new EnvoyerMessageType(array($admin->getEmail(), $admin->getEmail1(), $admin->getEmail2())), $envoyerMessage);
        $message = null;


        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);
            if ($form->isValid()) {
                $message = new Message();
                $message->setRecepteur($admin->getId());
                $message->setEmetteur($user->getid());
                $message->setNouveau(true);
                $message->setIteration(2);
                $texte = $envoyerMessage->getMessage();
                $message->setMessage($texte);
                $em->persist($message);
                $notification = new Notification();
                $notification->setRecepteur($user->getId());
                $notification->setEmetteur($user->getId());
                $notification->setNouveau(true);
                $notification->setIteration(2);
                $notification->setMessage('Message envoyé à ' . $admin->getUsername());
                $em->persist($notification);
                $em->flush();
                $notifications = $repoNotifications->getNotificationByRecepteur($user);
                $session->set('Notifications', $notifications);

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


                return $this->redirect($this->generateUrl('AeagDecBundle_collecteur'));
            }
        }


        return $this->render('AeagDecBundle:Collecteur:envoyerMessage.html.twig', array(
                    'User' => $admin,
                    'odec' => $odec,
                    'form' => $form->createView()
        ));
    }

    public function consulterMessageAction($id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'contact');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'consulterMessage');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoMessages = $emDec->getRepository('AeagDecBundle:Message');
        $message = $repoMessages->getMessageById($id);
        $lignes = explode('<br />', nl2br($message->getMessage()));
        return $this->render('AeagDecBundle:Collecteur:consulterMessage.html.twig', array(
                    'message' => $message,
                    'lignes' => $lignes,
        ));
    }

    public function supprimerMessageAction($id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'contact');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'supprimerMessage');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $user = $this->getUser();
        if (is_object($user)) {
            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);
        }

        $messages = null;


        $repoMessages = $emDec->getRepository('AeagDecBundle:Message');
        $message = $repoMessages->getMessageById($id);
        $session->set('Messages', '');
        $em->remove($message);
        $emDec->flush();
        $messages = $repoMessages->getMessageByRecepteur($user);
        $session->set('Messages', $messages);

        return $this->render('AeagDecBundle:Collecteur:listeMessages.html.twig', array(
                    'messages' => $messages,
        ));
    }

    public function consulterCollecteurAction($id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'consulterCollecteur');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');
        $repoInterlocuteur = $em->getRepository('AeagAeagBundle:Interlocuteur');
        $repoUser = $em->getRepository('AeagUserBundle:User');
        $repoCollecteurProducteur = $emDec->getRepository('AeagDecBundle:CollecteurProducteur');

        $ouvrage = $repoOuvrage->getOuvrageById($id);

        if (!$ouvrage) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement : ' . $id);
        }

        $correspondants = $repoOuvrageCorrespondant->getOuvrageCorrespondantByOuvrage($ouvrage->getId());
        $ctdts = $repoOuvrage->getOuvrageByNumeroType($ouvrage->getNumero(), 'CTT');
        $cts = $repoOuvrage->getOuvrageByNumeroType($ouvrage->getNumero(), 'CT');
        $entities = $repoCollecteurProducteur->getCollecteurProducteurByCollecteur($ouvrage->getId());
        $i = 0;
        $pdecs = array();
        foreach ($entities as $collecteur) {
            $pdecs[$i][0] = $collecteur->getCollecteur();
            $producteur = $repoOuvrage->getOuvrageById($collecteur->getProducteur());
            $pdecs[$i]['Producteur'] = $producteur;
            $i++;
        }


        $i = 0;
        $corres = array();
        foreach ($correspondants as $correspondant) {
            $userCorrespondants = $repoUser->getUserByCorrespondant($correspondant->getCorrespondant()->getId());
            foreach ($userCorrespondants as $userCorrespondant) {
                $corres[$i]['correspondant'] = $correspondant;
                $corres[$i]['user'] = $userCorrespondant;
                $interlocuteurs = $repoInterlocuteur->getInterlocuteursByCorrespondant($correspondant->getCorrespondant()->getId());
                $corres[$i]['interlocuteurs'] = $interlocuteurs;
                $i++;
            }
        }

        $session->set('retour', $this->generateUrl('AeagDecBundle_admin_listeCollecteurs'));

        return $this->render('AeagDecBundle:Collecteur:consulterCollecteur.html.twig', array(
                    'odec' => $ouvrage,
                    'corres' => $corres,
                    'ctdts' => $ctdts,
                    'cts' => $cts,
                    'pdecs' => $pdecs,
        ));
    }

    public function consulterCentreTransitAction($id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'consulterCentreTransit');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');

        $ouvrage = $repoOuvrage->getOuvrageById($id);

        if (!$ouvrage) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement : ' . $id);
        }

        $correspondants = $repoOuvrageCorrespondant->getOuvrageCorrespondantByOuvrage($ouvrage->getId());
        $ctdts = $repoOuvrage->getOuvrageByNumeroType($ouvrage->getNumero(), 'CTT');
        $odecs = $repoOuvrage->getOuvrageByNumeroType($ouvrage->getNumero(), 'ODEC');
        $pdecs = $repoOuvrage->getOuvrageByNumeroType($ouvrage->getNumero(), 'PDEC');

        return $this->render('AeagDecBundle:Collecteur:consulterCentreTransit.html.twig', array(
                    'ct' => $ouvrage,
                    'correspondants' => $correspondants,
                    'ctdts' => $ctdts,
                    'odecs' => $odecs,
                    'pdecs' => $pdecs,
        ));
    }

    public function consulterCentreTraitementAction($id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'referentiel');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'consulterCentreTraitement');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');

        $ouvrage = $repoOuvrage->getOuvrageById($id);

        if (!$ouvrage) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement : ' . $id);
        }

        $correspondants = $repoOuvrageCorrespondant->getOuvrageCorrespondantByOuvrage($ouvrage->getId());
        $cts = $repoOuvrage->getOuvrageByNumeroType($ouvrage->getNumero(), 'CT');
        $odecs = $repoOuvrage->getOuvrageByNumeroType($ouvrage->getNumero(), 'ODEC');
        $pdecs = $repoOuvrage->getOuvrageByNumeroType($ouvrage->getNumero(), 'PDEC');

        return $this->render('AeagDecBundle:Collecteur:consulterCentreTraitement.html.twig', array(
                    'ctdt' => $ouvrage,
                    'correspondants' => $correspondants,
                    'cts' => $cts,
                    'odecs' => $odecs,
                    'pdecs' => $pdecs,
        ));
    }

    public function listeProducteursAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'listeProducteurs');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoCollecteurProducteur = $emDec->getRepository('AeagDecBundle:CollecteurProducteur');
        $repoProducteurTauxSpecial = $emDec->getRepository('AeagDecBundle:ProducteurTauxSpecial');

        $collecteur = $repoOuvrage->getOuvrageByUserIdType($user->getId(), 'ODEC');

        $collecteurProducteurs = $repoCollecteurProducteur->getCollecteurProducteurByCollecteur($collecteur->getId());

        $prod = array();
        $i = 0;
        foreach ($collecteurProducteurs as $collecteurProducteur) {
            $producteur = $repoOuvrage->getOuvrageById($collecteurProducteur->getProducteur());
            if ($producteur) {
                $nbCollecteurs = $repoCollecteurProducteur->getNbCollecteurProducteurByProducteur($producteur->getId());
                $producteurTauxSpecial = $repoProducteurTauxSpecial->getProducteurTauxSpecialBySiret($producteur->getSiret());
                if ($producteurTauxSpecial) {
                    $tauxAide = $producteurTauxSpecial->getTaux() / 100;
                    $bonnifier = true;
                } else {
                    $tauxAide = null;
                    $bonnifier = false;
                }
                $prod[$i][0] = $producteur;
                $prod[$i][1] = $nbCollecteurs;
                $prod[$i][2] = $bonnifier;
                $prod[$i][3] = $tauxAide;
                $i++;
            }
        }

        $session->set('retour', $this->generateUrl('AeagDecBundle_collecteur_listeProducteurs'));

        return $this->render('AeagDecBundle:Collecteur:listeProducteurs.html.twig', array(
                    'collecteur' => $collecteur,
                    'producteurs' => $prod
        ));
    }

    /**
     *  Fichier PDF
     */
    public function pdfListeProducteursAction($collecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'pdfListeProducteurs');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoCollecteurProducteur = $emDec->getRepository('AeagDecBundle:CollecteurProducteur');
        $repoProducteurTauxSpecial = $emDec->getRepository('AeagDecBundle:ProducteurTauxSpecial');

        $collecteur = $repoOuvrage->getOuvrageById($collecteur_id);
        $collecteurProducteurs = $repoCollecteurProducteur->getCollecteurProducteurByCollecteur($collecteur->getId());
        $producteurs = array();
        $i = 0;
        foreach ($collecteurProducteurs as $collecteurProducteur) {
            $producteur = $repoOuvrage->getOuvrageById($collecteurProducteur->getProducteur());
            if ($producteur) {
                $producteurTauxSpecial = $repoProducteurTauxSpecial->getProducteurTauxSpecialBySiret($producteur->getSiret());
                if ($producteurTauxSpecial) {
                    $tauxAide = $producteurTauxSpecial->getTaux() / 100;
                    $bonnifier = true;
                } else {
                    $tauxAide = null;
                    $bonnifier = false;
                }
                $producteurs[$i]['producteur'] = $producteur;
                $producteurs[$i]['bonnifier'] = $bonnifier;
                $producteurs[$i]['tauxAide'] = $tauxAide;
                $i++;
            }
        }

        $pdf = new PdfListeProducteurs('P', 'mm', 'A4');
        $titre = 'Liste des producteurs du collecteur ' . $collecteur->getNumero() . ' ' . $collecteur->getlibelle();
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup();
        $pdf->AddPage($collecteur);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($collecteur, $producteurs);
        $fichier = 'DEC_PRODUCTEURS' . '_' . $collecteur->getNumero() . '.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagDecBundle:Collecteur:pdf.html.twig');
    }

    public function consulterProducteurAction($id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'consulterProducteur');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoCollecteurProducteur = $emDec->getRepository('AeagDecBundle:CollecteurProducteur');
        $repoDeclarationProducteur = $emDec->getRepository('AeagDecBundle:DeclarationProducteur');

        $ouvrage = $repoOuvrage->getOuvrageById($id);

        if (!$ouvrage) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement : ' . $id);
        }

        $collecteur = $repoOuvrage->getOuvrageByUserIdType($user->getId(), 'ODEC');

        $odecs = $repoCollecteurProducteur->getNbCollecteurProducteurByProducteur($ouvrage->getId());

        $declarationProducteurs = $repoDeclarationProducteur->getDeclarationProducteurByProducteur($ouvrage->getId());

        return $this->render('AeagDecBundle:Collecteur:consulterProducteur.html.twig', array(
                    'collecteur' => $collecteur,
                    'pdec' => $ouvrage,
                    'odecs' => $odecs,
                    'decls' => $declarationProducteurs,
        ));
    }

    public function producteurCpAction(Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'producteurCp');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoCodePostal = $em->getRepository('AeagAeagBundle:CodePostal');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');


        $critCp = $request->get('cp');

        $villes = $repoCodePostal->getCodePostalByCp($critCp);

        $acheminement = null;
        if ($villes) {
            if (count($villes) == 1) {
                $acheminement = $villes[0]->getAcheminement();
            }
        }

        return $this->render('AeagDecBundle:Collecteur:producteurCp.html.twig', array(
                    'cp' => $critCp,
                    'acheminement' => $acheminement,
                    'villes' => $villes,
        ));
    }

    public function majProducteurAction($collecteur_id = null, $producteur_id = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'majProducteur');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoCodePostal = $em->getRepository('AeagAeagBundle:CodePostal');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');
        $repoCollecteurProducteur = $emDec->getRepository('AeagDecBundle:CollecteurProducteur');
        $repoNaf = $emDec->getRepository('AeagDecBundle:Naf');
        $collecteur = $repoOuvrage->getOuvrageById($collecteur_id);
        $producteur = $repoOuvrage->getOuvrageById($producteur_id);
        $nafs = $repoNaf->getNafsAidables('O');

        if (!$producteur) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement : ' . $id);
        }

        $villes = $repoCodePostal->getCodePostalByCp($producteur->getCp());
        $majProducteur = clone($producteur);
        $form = $this->createForm(new MajProducteurType(), $majProducteur);
        $message = null;
        $maj = false;
        $err = false;

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {



                if (strlen($majProducteur->getSiret()) <> 14) {
                    $err = true;
                    $message = $message . "Le siret " . $majProducteur->getSiret() . " doit faire 14 caractères  \n";
                }

                $ville = $request->get('MajProducteur_ville');
                if ($ville) {
                    $codePostal = $repoCodePostal->getCodePostalByCpAcheminement($majProducteur->getCp(), $ville);
                } else {
                    $codePostal = $repoCodePostal->getCodePostalByCp($majProducteur->getCp());
                }
                if (!$codePostal) {
                    $err = true;
                    $message = $message . "Le code postal " . $majProducteur->getCp() . " est inconnu à l'agence de l'eau";
                    $constraint = new True(array(
                        'message' => "Le code postal " . $majProducteur->getCp() . " est inconnu à l'agence de l'eau"
                    ));
                    $erreurCp = $this->get('validator')->validateValue(false, $constraint);
                } else {
                    foreach ($codePostal as $cp) {
                        if ($cp->getDec() == 'N') {
                            $err = true;
                            $message = $message . "Le code postal " . $majProducteur->getCp() . " n'est pas aidable par l'agence de l'eau";
                            $constraint = new True(array(
                                'message' => "Le code postal " . $majProducteur->getCp() . " n'est pas aidable par l'agence de l'eau"
                            ));
                            $erreurCp = $this->get('validator')->validateValue(false, $constraint);
                        }
                    }
                }

                $ville = $request->get('MajProducteur_ville');


                $naf = $request->get('MajProducteur_naf');

                if (!$err) {
                    $producteur->setLibelle($majProducteur->getLibelle());
                    $producteur->setAdresse($majProducteur->getAdresse());
                    $producteur->setCp($majProducteur->getCp());
                    $producteur->setVille($ville);
                    $producteur->setSiret($majProducteur->getSiret());
                    $producteur->setNaf($naf);
                    $em->persist($producteur);
                    $em->flush();
                    $collecteurProducteur = $repoCollecteurProducteur->getCollecteurProducteurByCollecteurProducteur($collecteur->getId(), $producteur->getId());
                    if (!$collecteurProducteur) {
                        $collecteurProducteur = new CollecteurProducteur();
                        $collecteurProducteur->setCollecteur($collecteur->getId());
                        $collecteurProducteur->setProducteur($producteur->getId());
                        $emDec->persist($collecteurProducteur);
                        $emDec->flush();
                        $session->getFlashBag()->add('notice-success', "Le producteur " . $producteur->getLibelle() . " a été ajouté !");
                    } else {
                        $session->getFlashBag()->add('notice-success', "Le producteur " . $producteur->getLibelle() . " a été mis à jour  !");
                    }
                    $maj = true;
                    return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeProducteurs'));
                }
            }
        }

        return $this->render('AeagDecBundle:Collecteur:majProducteur.html.twig', array(
                    'collecteur' => $collecteur,
                    'entity' => $producteur,
                    'nafs' => $nafs,
                    'villes' => $villes,
                    'message' => $message,
                    'maj' => $maj,
                    'form' => $form->createView(),
        ));
    }

    public function ajouterProducteurAction($collecteur_id = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'ajouterProducteur');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoCodePostal = $em->getRepository('AeagAeagBundle:CodePostal');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');
        $repoCollecteurProducteur = $emDec->getRepository('AeagDecBundle:CollecteurProducteur');
        $repoNaf = $emDec->getRepository('AeagDecBundle:Naf');

        $collecteur = $repoOuvrage->getOuvrageById($collecteur_id);

        $nafs = $repoNaf->getNafsAidables('O');
        $villes = null;

        if (!$collecteur) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement : ' . $collecteur_id);
        }

        $majProducteur = new MajProducteur();
        $form = $this->createForm(new MajProducteurType(), $majProducteur);
        $message = null;
        $err = false;
        $maj = false;

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {


                if (strlen($majProducteur->getSiret()) <> 14) {
                    $err = true;
                    $message = $message . "Le siret " . $majProducteur->getSiret() . " doit faire 14 caractères  \n";
                }

                $ville = $request->get('MajProducteur_ville');
                if ($ville) {
                    $codePostal = $repoCodePostal->getCodePostalByCpAcheminement($majProducteur->getCp(), $ville);
                } else {
                    $codePostal = $repoCodePostal->getCodePostalByCp($majProducteur->getCp());
                }
                //return new Response ('ville : ' . $ville . ' cpl : ' . $majProducteur->getCp() );
                if (!$codePostal) {
                    $err = true;
                    $message = $message . "Le code postal " . $majProducteur->getCp() . " est inconnu à l'agence de l'eau";
                    $constraint = new True(array(
                        'message' => "Le code postal " . $majProducteur->getCp() . " est inconnu à l'agence de l'eau"
                    ));
                    $erreurCp = $this->get('validator')->validateValue(false, $constraint);
                } else {
                    foreach ($codePostal as $cp) {
                        if ($cp->getDec() == 'N') {
                            if (!$err) {
                                $err = true;
                                $message = $message . "Le code postal " . $majProducteur->getCp() . " n'est pas aidable par l'agence de l'eau";
                                $constraint = new True(array(
                                    'message' => "Le code postal " . $majProducteur->getCp() . " n'est pas aidable par l'agence de l'eau"
                                ));
                                $erreurCp = $this->get('validator')->validateValue(false, $constraint);
                            }
                        }
                    }
                }



                $naf = $request->get('MajProducteur_naf');


                if (!$err) {
                    $producteur = null;
                    $producteurs = $repoOuvrage->getOuvragesBySiretType($majProducteur->getSiret(), 'PDEC');
                    if (count($producteurs) > 0) {
                        $producteur = $producteurs[0];
                    }
                    if (!$producteur) {
                        $producteur = new Ouvrage();
                        $producteur->setType('PDEC');
                        $producteur->setLibelle($majProducteur->getLibelle());
                        $producteur->setAdresse($majProducteur->getAdresse());
                        $producteur->setCp($majProducteur->getCp());
                        $producteur->setVille($ville);
                        $producteur->setSiret($majProducteur->getSiret());
                        $producteur->setNaf($naf);
                        $em->persist($producteur);
                        $em->flush();
                    }

                    $collecteurProducteur = $repoCollecteurProducteur->getCollecteurProducteurByCollecteurProducteur($collecteur->getId(), $producteur->getId());
                    if (!$collecteurProducteur) {
                        $collecteurProducteur = new CollecteurProducteur();
                        $collecteurProducteur->setCollecteur($collecteur->getId());
                        $collecteurProducteur->setProducteur($producteur->getId());
                        $emDec->persist($collecteurProducteur);
                        $emDec->flush();
                        $session->getFlashBag()->add('notice-success', "Le producteur " . $producteur->getLibelle() . " a été ajouté !");
                    } else {
                        $session->getFlashBag()->add('notice-warning', "Le producteur " . $producteur->getLibelle() . " est dèjà dans la liste !");
                    }

                    $maj = true;
                    return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeProducteurs'));
                }
            }
        }

        return $this->render('AeagDecBundle:Collecteur:ajouterProducteur.html.twig', array(
                    'entity' => $collecteur,
                    'message' => $message,
                    'maj' => $maj,
                    'nafs' => $nafs,
                    'villes' => $villes,
                    'form' => $form->createView(),
        ));
    }

    public function supprimerProducteurAction($id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'supprimerProducteur');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');
        $repoCollecteurProducteur = $emDec->getRepository('AeagDecBundle:CollecteurProducteur');
        $repoDeclarationProducteur = $emDec->getRepository('AeagDecBundle:DeclarationProducteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');

        $collecteur = $repoOuvrage->getOuvrageByUserIdType($user->getId(), 'ODEC');

        $producteur = $repoOuvrage->getOuvrageById($id);


        if (!$producteur) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement : ' . $id);
        }



        $declarationProducteurs = $repoDeclarationProducteur->getDeclarationProducteurByProducteur($producteur->getid());
        if ($declarationProducteurs) {
            foreach ($declarationProducteurs as $declarationProducteur) {
                $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsByDeclarationProducteur($declarationProducteur->getId());
                if ($declarationDetails) {
                    foreach ($declarationDetails as $declarationDetail) {
                        $emDec->remove($declarationDetail);
                    }
                }
                $emDec->remove($declarationProducteur);
            }
        }


        $collecteurProducteurs = $repoCollecteurProducteur->getCollecteurProducteurByProducteur($producteur->getId());
        if ($collecteurProducteurs) {
            foreach ($collecteurProducteurs as $collecteurProducteur) {
                $declarationCollecteurs = $repoDeclarationCollecteur->getDeclarationCollecteurByCollecteur($collecteurProducteur->getCollecteur());
                foreach ($declarationCollecteurs as $declarationCollecteur) {
                    $ok = $this->majStatutDeclarationCollecteursAction($declarationCollecteur->getId(), $user, $emDec, $session);
                }
                $emDec->remove($collecteurProducteur);
            }
        }


        $ouvrageCorrespondants = $repoOuvrageCorrespondant->getOuvrageCorrespondantByOuvrage($producteur->getid());
        if ($ouvrageCorrespondants) {
            foreach ($ouvrageCorrespondants as $ouvrageCorrespondant) {
                $em->remove($ouvrageCorrespondant);
            }
        }

        $em->remove($producteur);

        $session->getFlashBag()->add('notice-success', "Le producteur " . $producteur->getLibelle() . " a été supprimé !");

        $em->flush();
        $emDec->flush();

        return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeProducteurs'));
    }

    public function tauxSpecialProducteurAction($id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'tauxSpecialProducteur');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoTaux = $emDec->getRepository('AeagDecBundle:Taux');
        $repoProducteurTauxSpecial = $emDec->getRepository('AeagDecBundle:ProducteurTauxSpecial');

        $collecteur = $repoOuvrage->getOuvrageByUserIdType($user->getId(), 'ODEC');

        $producteur = $repoOuvrage->getOuvrageById($id);

        $annee = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));


        if (!$producteur) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement : ' . $id);
        }

        $producteurTauxSpecial = $repoProducteurTauxSpecial->getProducteurTauxSpecialBySiret($producteur->getSiret());
        if ($producteurTauxSpecial) {
            $tauxAide = $producteurTauxSpecial->getTaux() / 100;
        } else {
            $tauxAideAgence = $repoTaux->getTauxByAnneeCode($annee, 'TAUXAIDE');
            $tauxAide = $tauxAideAgence->getValeur();
        }


        return new Response($tauxAide);
    }

    public function majCollecteurAction($id = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'acteurs');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'majCollecteur');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $emDec->getRepository('AeagDecBundle:Ouvrage');
        $repoOuvrageCorrespondant = $emDec->getRepository('AeagDecBundle:OuvrageCorrespondant');
        $repoNotifications = $emDec->getRepository('AeagDecBundle:Notification');

        $ouvrage = $repoOuvrage->getOuvrageById($id);

        if (!$ouvrage) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement : ' . $id);
        }

        $correspondants = $repoOuvrageCorrespondant->getOuvrageCorrespondantByOuvrage($ouvrage->getId());
        $ctdts = $repoOuvrage->getOuvrageByNumeroType($ouvrage->getNumero(), 'CTT');
        $cts = $repoOuvrage->getOuvrageByNumeroType($ouvrage->getNumero(), 'CT');

        $form = $this->createForm(new MajOuvrageType(), $ouvrage);
        $message = null;

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $emDec->persist($ouvrage);
                $emDec->flush();

                $notification = new Notification();
                $notification->setRecepteur($user->getId());
                $notification->setEmetteur($user->getId());
                $notification->setNouveau(true);
                $notification->setIteration(2);
                $notification->setMessage('Le collecteur ' . $ouvrage->getNumero() . ' a été mis à jour.');
                $emDec->persist($notification);
                $emDec->flush();
                $notifications = $repoNotifications->getNotificationByRecepteur($user);
                $session->set('Notifications', $notifications);

//$this->get('session')->getFlashBag()->add('notice', 'Vous avez un nouveau message');


                return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeDeclarations'));
            }
        }

        return $this->render('AeagDecBundle:Collecteur:majCollecteur.html.twig', array(
                    'entity' => $ouvrage,
                    'correspondants' => $correspondants,
                    'ctdts' => $ctdts,
                    'cts' => $cts,
                    'message' => $message,
                    'form' => $form->createView(),
        ));
    }

    public function listeDeclarationsAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'listeDeclarations');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $collecteur = $repoOuvrage->getOuvrageByUserIdType($user->getId(), 'ODEC');
        $declarations = $repoDeclarationCollecteur->getDeclarationCollecteurByCollecteur($collecteur->getId());
        $odec = array();
        $dec = array();
        $i = 0;
        $j = 0;
        $nb2 = 0;
        foreach ($declarations as $declaration) {
            $sousDeclarations = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteur($declaration->getId());
            $odec[$i][0] = $declaration;
            $odec[$i][1] = $sousDeclarations;
            $nb1 = 0;
            foreach ($sousDeclarations as $sousDeclaration) {
                $session->set('statut', $sousDeclaration->getStatut()->getCode());
                $dec[$j] = array(
                    'dec_id' => $declaration->getId(),
                    'dec_annee' => $declaration->getAnnee(),
                    'sousdec_id' => $sousDeclaration->getId(),
                    'sousdec_dateDebut' => $sousDeclaration->getDateDebut());
                $j++;
                $nb1++;
            }
            if ($nb1 > $nb2) {
                $nb2 = $nb1;
            }
            $i++;
        }

        $session->set('declarations', $dec);

        $session->set('retour', $this->generateUrl('AeagDecBundle_collecteur_listeDeclarations'));

        return $this->render('AeagDecBundle:Collecteur:listeDeclarations.html.twig', array(
                    'collecteur' => $collecteur,
                    'entities' => $odec,
                    'nbDec' => $nb2
        ));
    }

    public function ajouterDeclarationAction($collecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'ajouterDeclarations');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagDecBundle:Ouvrage');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');

        $collecteur = $repoOuvrage->getOuvrageById($collecteur_id);
        $annee = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'ANNEE'));
        $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurByCollecteurAnnee($collecteur->getId(), $annee->getLibelle() + 1);

        if (!$declarationCollecteur) {
            $declarationCollecteur = new DeclarationCollecteur();
            $declarationCollecteur->setAnnee($annee->getLibelle() + 1);
            $declarationCollecteur->setCollecteur($collecteur);
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
            $emDec->flush();
            $session->getFlashBag()->add('notice-success', "Le dossier" . $declarationCollecteur->getAnnee() . " a été créée avec succès !");
        } else {
            $session->getFlashBag()->add('notice-warning', "Le dossier " . $declarationCollecteur->getAnnee() . " existe déjà !");
        }

        return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeDeclarations'));
    }

    public function supprimerDeclarationAction($declarationCollecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'supprimerDeclarations');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoDeclarationProducteur = $emDec->getRepository('AeagDecBundle:DeclarationProducteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');

        $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($declarationCollecteur_id);
        $annee = $declarationCollecteur->getAnnee();
        $sousDeclarationCollecteurs = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteur($declarationCollecteur->getId());

        if ($sousDeclarationCollecteurs) {
            foreach ($sousDeclarationCollecteurs as $sousDeclarationCollecteur) {
                $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsBySousDeclarationCollecteur($sousDeclarationCollecteur->getId());
                if ($declarationDetails) {
                    foreach ($declarationDetails as $declarationDetail) {
                        $declarationProducteur = $repoDeclarationProducteur->getDeclarationProducteurByProducteur($declarationDetail->getDeclarationProducteur()->getId());
                        $em->remove($declarationDetail);
                        $ok = $this->majStatutDeclarationProducteursAction($declarationProducteur->getId(), $user, $emDec, $session);
                    }
                }
                $em->remove($sousDeclarationCollecteur);
            }
        }

        $em->remove($declarationCollecteur);
        $emDec->flush();
        $session->getFlashBag()->add('notice-success', "Le dossier " . $annee . " a été supprimé avec succès !");
        return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeDeclarations'));
    }

    public function listeSousDeclarationsAction($declarationCollecteur_id = null) {

        $user = $this->getUser();
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'listeSousDeclarations');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');


        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoParametre = $emDec->getRepository('AeagDecBundle:Parametre');

        if (is_object($user)) {
            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);
            $tabMail = split("@", $user->getEmail());
            if ($tabMail[1] == "a-renseigner-merci.svp") {
                $valider = 'N';
            } else {
                $valider = 'O';
            }
        }

        $parametre = $repoParametre->findOneBy(array('code' => 'REP_IMPORT'));
        $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($declarationCollecteur_id);
        $collecteur = $repoOuvrage->getOuvrageById($declarationCollecteur->getCollecteur());
        $sousDeclarationCollecteurs = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteur($declarationCollecteur->getId());

        $tabSousDeclarationCollecteurs = array();
        $i = 0;
        foreach ($sousDeclarationCollecteurs as $sousDeclarationCollecteur) {
            $tabSousDeclarationCollecteurs[$i]['sousDec'] = $sousDeclarationCollecteur;
            //$nom_fichier = 'dec_' . $collecteur->getNumero() . '_' . str_replace(' ','_',$collecteur->getLibelle()) . '_' . $declarationCollecteur->getAnnee() . '_' . $sousDeclarationCollecteur->getNumero() . '.csv';
            $nom_fichier = 'dec_' . $collecteur->getNumero() . '_' . $collecteur->getLibelle() . '_' . $declarationCollecteur->getAnnee() . '_' . $sousDeclarationCollecteur->getNumero() . '.csv';
            $tabSousDeclarationCollecteurs[$i]['fichier'] = $nom_fichier;
            $i++;
        }

        return $this->render('AeagDecBundle:Collecteur:listeSousDeclarations.html.twig', array(
                    'annee' => $declarationCollecteur->getAnnee(),
                    'collecteur' => $collecteur,
                    'declarationCollecteur' => $declarationCollecteur,
                    'entities' => $tabSousDeclarationCollecteurs,
                    'valider' => $valider
        ));
    }

    public function pdfSousDeclarationAction($sousDeclarationCollecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'pdfSousDeclarations');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurById($sousDeclarationCollecteur_id);
        $collecteur = $repoOuvrage->getOuvrageById($sousDeclarationCollecteur->getDeclarationCollecteur()->getCollecteur());
        $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsBySousDeclarationCollecteur($sousDeclarationCollecteur->getid());
        $tabDec = array();
        $tabDec['sousDeclarationCollecteur'] = $sousDeclarationCollecteur;
        $tabDec['collecteur'] = $collecteur;

        $tab = array();
        $i = 0;
        if ($declarationDetails) {
            foreach ($declarationDetails as $declaration) {
                $producteur = $repoOuvrage->getOuvrageById($declaration->getDeclarationProducteur()->getProducteur());
                if ($declaration->getCentreTraitement()) {
                    $centreTraitement = $repoOuvrage->getOuvrageById($declaration->getCentreTraitement());
                } else {
                    $centreTraitement = null;
                }
                if ($declaration->getCentreTransit()) {
                    $centreTransit = $repoOuvrage->getOuvrageById($declaration->getCentreTransit());
                    $tab[$i]['centreTransit'] = $centreTransit->getNumero();
                } else {
                    $tab[$i]['centreTransit'] = "";
                }
                if ($declaration->getCentreDepot()) {
                    $centreDepot = $repoOuvrage->getOuvrageById($declaration->getCentreDepot());
                } else {
                    $centreDepot = null;
                }
                $tab[$i]['id'] = $declaration->getId();
                $tab[$i]['statutCode'] = $declaration->getStatut()->getCode();
                $tab[$i]['declarationProducteurId'] = $declaration->getDeclarationproducteur()->getId();
                if ($producteur) {
                    $tab[$i]['producteurSiret'] = $producteur->getSiret();
                    $tab[$i]['producteurLibelle'] = $producteur->getLibelle();
                    if (!$producteur->getCommune()) {
                        $tab[$i]['producteurCodePostal'] = $producteur->getCp();
                    } else {
                        $tab[$i]['producteurCodePostal'] = $producteur->getCommune()->getCommune();
                    }
                } else {
                    $tab[$i]['producteurSiret'] = null;
                    $tab[$i]['producteurLibelle'] = null;
                    $tab[$i]['producteurCodePostal'] = null;
                }
                $tab[$i]['declarationProducteurQuantiteReel'] = $declaration->getDeclarationProducteur()->getQuantiteReel();
                $tab[$i]['declarationProducteurQuantiteRet'] = $declaration->getDeclarationProducteur()->getQuantiteRet();
                $tab[$i]['declarationProducteurMontAide'] = $declaration->getDeclarationProducteur()->getMontAide();

                if ($centreTraitement) {
                    $tab[$i]['centreTraitement'] = $centreTraitement->getNumero();
                } else {
                    $tab[$i]['centreTraitement'] = "";
                }
                if ($centreTransit) {
                    $tab[$i]['centreTransit'] = $centreTransit->getNumero();
                } else {
                    $tab[$i]['centreTransit'] = "";
                }
                if ($centreDepot) {
                    $tab[$i]['centreDepot'] = $centreDepot->getNumero();
                } else {
                    $tab[$i]['centreDepot'] = "";
                }
                if ($declaration->getDechet()) {
                    $tab[$i]['dechet'] = $declaration->getDechet()->getCode();
                } else {
                    $tab[$i]['dechet'] = "";
                }
                $tab[$i]['nature'] = $declaration->getNature();
                if ($declaration->getFiliere()) {
                    $tab[$i]['filiere'] = $declaration->getFiliere()->getCode();
                } else {
                    $tab[$i]['filiere'] = "";
                }
                if ($declaration->getTraitFiliere()) {
                    $tab[$i]['traitFiliere'] = $declaration->getTraitFiliere()->getCode();
                } else {
                    $tab[$i]['traitFiliere'] = "";
                }
                if ($declaration->getNaf()) {
                    $tab[$i]['naf'] = $declaration->getNaf()->getCode();
                } else {
                    $tab[$i]['naf'] = "";
                }
                if ($declaration->getDateFacture()) {
                    $tab[$i]['dateFacture'] = $declaration->getDateFacture();
                } else {
                    $tab[$i]['dateFacture'] = "";
                }
                if ($declaration->getNumFacture()) {
                    $tab[$i]['numFacture'] = $declaration->getNumFacture();
                } else {
                    $tab[$i]['numFacture'] = "";
                }
                $tab[$i]['coutFacture'] = $declaration->getCoutFacture();
                $tab[$i]['quantiteReel'] = $declaration->getQuantiteReel();
                $tab[$i]['quantiteRet'] = $declaration->getQuantiteRet();
                $tab[$i]['tauxAide'] = $declaration->getTauxAide();
                $tab[$i]['montAide'] = $declaration->getMontAide();
                $i++;
            }
        }



        $pdf = new PdfSousDeclarationCollecteur('L', 'mm', 'A4');
        $titre = 'Declaration n ' . $sousDeclarationCollecteur->getNumero() . ' de l\'annee ' . $sousDeclarationCollecteur->getdeclarationCollecteur()->getAnnee() . '  du collecteur ' . $collecteur->getNumero() . ' ' . $collecteur->getlibelle();
        $pdf->setTitle($titre);
        $pdf->setAuthor('Joel Lavabre');
        $pdf->AliasNbPages();
        $pdf->StartPageGroup();
        $pdf->AddPage($tabDec);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Formatage($tabDec, $tab);
        $fichier = 'DEC_DECLARATION_' . $collecteur->getNumero() . '_' . $sousDeclarationCollecteur->getdeclarationCollecteur()->getAnnee() . '_' . $sousDeclarationCollecteur->getNumero() . '.pdf';
        $pdf->Output($fichier, 'D');


        return $this->render('AeagDecBundle:Collecteur:pdf.html.twig');
    }

    public function ajouterSousDeclarationAction($declarationCollecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'ajouterSousDeclaration');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');

        $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($declarationCollecteur_id);
        $numero = $repoSousDeclarationCollecteur->getNumeroLibreById($declarationCollecteur->getId());
        if (!$numero) {
            $numero = 1;
        }
        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteurNumero($declarationCollecteur->getId(), $numero);

        if (!$sousDeclarationCollecteur) {
            $sousDeclarationCollecteur = new SousDeclarationCollecteur();
            $sousDeclarationCollecteur->setDeclarationCollecteur($declarationCollecteur);
            $statut = $repoStatut->getStatutByCode('20');
            $sousDeclarationCollecteur->setStatut($statut);
            $sousDeclarationCollecteur->setNumero($numero);
            $dateDebut = new \DateTime(date('Y-m-d'));
            $sousDeclarationCollecteur->setDateDebut($dateDebut);
            $sousDeclarationCollecteur->setQuantiteReel(0);
            $sousDeclarationCollecteur->setMontReel(0);
            $sousDeclarationCollecteur->setQuantiteRet(0);
            $sousDeclarationCollecteur->setMontRet(0);
            $sousDeclarationCollecteur->setQuantiteAide(0);
            $sousDeclarationCollecteur->setMontAide(0);
            $sousDeclarationCollecteur->setDossierAide($declarationCollecteur->getDossierAide());
            $sousDeclarationCollecteur->setMontantAp($declarationCollecteur->getMontantAP());
            $sousDeclarationCollecteur->setMontantApDispo($declarationCollecteur->getMontantAPDispo());
            $emDec->persist($sousDeclarationCollecteur);
            $emDec->flush();
            $ok = $this->majStatutDeclarationCollecteursAction($declarationCollecteur->getId(), $user, $emDec, $session);
            $session->getFlashBag()->add('notice-success', "La déclaration n° " . $sousDeclarationCollecteur->getNumero() . " a été créée avec succès !");
        } else {
            $session->getFlashBag()->add('notice-warning', "La déclaration N°" . $sousDeclarationCollecteur->getNumero() . " existe déjà !");
        }

        return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeSousDeclarations', array('declarationCollecteur_id' => $declarationCollecteur_id)));
    }

    public function validerSousDeclarationAction($sousDeclarationCollecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'validerSousDeclaration');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');
        $repoUser = $em->getRepository('AeagUserBundle:User');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINDEC')) {
            $statut = $repoStatut->getStatutByCode('30');
        } else {
            $statut = $repoStatut->getStatutByCode('22');
        }
        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurById($sousDeclarationCollecteur_id);
        $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($sousDeclarationCollecteur->getDeclarationCollecteur()->getId());
        $collecteur = $repoOuvrage->getOuvrageById($sousDeclarationCollecteur->getDeclarationCollecteur()->getCollecteur());
        $correspondants = $repoOuvrageCorrespondant->getOuvrageCorrespondantByOuvrage($collecteur->getId());

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
            $session->getFlashBag()->add('notice-success', "La déclaration n° " . $sousDeclarationCollecteur->getNumero() . " a été validée avec succès !");
            $emDec->flush();

            if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINDEC')) {
                foreach ($correspondants as $cor) {
                    $notification = new Notification();
                    $correspondant = $repoCorrespondant->getCorrespondantById($cor->getCorrespondant()->getId());
                    $userOdecs = $repoUser->getUserByCorrespondant($correspondant->getId());
                    foreach ($userOdecs as $userOdec) {
                        $notification->setRecepteur($userOdec->getid());
                        $notification->setEmetteur($user->getId());
                        $notification->setNouveau(true);
                        $notification->setIteration(2);
                        $notification->setMessage("La déclaration n° " . $sousDeclarationCollecteur->getNumero() . " a été approuvée par le responsable de l'agence de l'eau.");
                        $em->persist($notification);
                    }
                }
                $em->flush();
            } else {

                $collecteur = $repoOuvrage->getOuvrageById($sousDeclarationCollecteur->getDeclarationCollecteur()->getCollecteur());
                $correspondant = $repoCorrespondant->getCorrespondantById($user->getcorrespondant());
                $admindec = $repoUser->getUserByUsername('admindec');
                $dest = null;
                if ($admindec->getEmail()) {
                    if (!$dest) {
                        $dest = $admindec->getEmail();
                    } else {
                        $dest = $dest . ';' . $admindec->getEmail();
                    }
                }
                if ($admindec->getEmail1()) {
                    if (!$dest) {
                        $dest = $admindec->getEmail1();
                    } else {
                        $dest = $dest . ';' . $admindec->getEmail1();
                    }
                }
                if ($admindec->getEmail2()) {
                    if (!$dest) {
                        $dest = $admindec->getEmail2();
                    } else {
                        $dest = $dest . ';' . $admindec->getEmail2();
                    }
                }

// Récupération du service.
                $mailer = $this->get('mailer');
                $destinataires = explode(";", $dest);
// Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
                foreach ($destinataires as $destinataire) {
                    $mail = \Swift_Message::newInstance('Wonderful Subject')
                            ->setSubject('Déclaration ' . $sousDeclarationCollecteur->getDeclarationCollecteur()->getAnnee() . ' N° ' . $sousDeclarationCollecteur->getNumero() . ' du colledcteur ' . $collecteur->getNumero() . ' ' . $collecteur->getLibelle() . ' validée')
                            ->setFrom('automate@eau-adour-garonne.fr')
                            ->setTo(array($destinataire))
                            ->setBody($this->renderView('AeagDecBundle:Collecteur:prevaliderEmail.txt.twig', array(
                                'user' => $user,
                                'collecteur' => $collecteur,
                                'correspondant' => $correspondant,
                                'declaration' => $sousDeclarationCollecteur)));

// Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
                    $mailer->send($mail);
                }
            }
        } else {
            $session->getFlashBag()->add('notice-warning', "La déclaration N°" . $sousDeclarationCollecteur->getNumero() . " n' existe pas !");
        }
        $ok = $this->majStatutDeclarationCollecteursAction($declarationCollecteur->getId(), $user, $emDec, $session);
        return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeSousDeclarations', array('declarationCollecteur_id' => $declarationCollecteur->getId())));
    }

    public function devaliderSousDeclarationAction($sousDeclarationCollecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'devaliderSousDeclaration');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoUser = $em->getRepository('AeagUserBundle:User');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoOuvrageCorrespondant = $em->getRepository('AeagAeagBundle:OuvrageCorrespondant');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurById($sousDeclarationCollecteur_id);
        $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($sousDeclarationCollecteur->getDeclarationCollecteur()->getId());
        $collecteur = $repoOuvrage->getOuvrageById($sousDeclarationCollecteur->getDeclarationCollecteur()->getCollecteur());
        $correspondants = $repoOuvrageCorrespondant->getOuvrageCorrespondantByOuvrage($collecteur->getId());

        if ($sousDeclarationCollecteur) {
            if ($sousDeclarationCollecteur->getStatut()->getCode() == '40') {
                $statut = $repoStatut->getStatutByCode('30');
            } elseif ($sousDeclarationCollecteur->getStatut()->getcode() == '30') {
                $statut = $repoStatut->getStatutByCode('22');
            } elseif ($sousDeclarationCollecteur->getStatut()->getcode() == '22') {
                $statut = $repoStatut->getStatutByCode('20');
            }
            $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsBySousDeclarationCollecteur($sousDeclarationCollecteur->getId());
            if ($statut->getCode() == '20') {
                $statutDetail = $repoStatut->getStatutByCode('10');
            } else {
                $statutDetail = $statut;
            }
            if ($declarationDetails) {
                foreach ($declarationDetails as $declarationDetail) {
                    $declarationDetail->setStatut($statutDetail);
                    $emDec->persist($declarationDetail);
                }
            }
            $sousDeclarationCollecteur->setStatut($statut);
            $emDec->persist($sousDeclarationCollecteur);
            $session->getFlashBag()->add('notice-success', "La déclaration n° " . $sousDeclarationCollecteur->getNumero() . " a été dévalidée avec succès !");
            $emDec->flush();
        } else {
            $session->getFlashBag()->add('notice-warning', "La déclaration N°" . $sousDeclarationCollecteur->getNumero() . " n' existe pas !");
        }

        foreach ($correspondants as $cor) {
            $notification = new Notification();
            $correspondant = $repoCorrespondant->getCorrespondantById($cor->getCorrespondant()->getId());
            $userOdecs = $repoUser->getUserByCorrespondant($correspondant->getId());
            foreach ($userOdecs as $userOdec) {
                $notification->setRecepteur($userOdec->getId());
                $notification->setEmetteur($user->getId());
                $notification->setNouveau(true);
                $notification->setIteration(2);
                $notification->setMessage("La déclaration n° " . $sousDeclarationCollecteur->getNumero() . " a été remise en préparation par le responsable de l'agence de l'eau. ");
                $em->persist($notification);
            }
        }
        $em->flush();
        $ok = $this->majStatutDeclarationCollecteursAction($declarationCollecteur->getId(), $user, $emDec, $session);
        return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeSousDeclarations', array('declarationCollecteur_id' => $declarationCollecteur->getId())));
    }

    public function supprimerSousDeclarationAction($sousDeclarationCollecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'supprimerSousDeclaration');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoDeclarationProducteur = $emDec->getRepository('AeagDecBundle:DeclarationProducteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');

        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurById($sousDeclarationCollecteur_id);

        if ($sousDeclarationCollecteur) {
            if (($this->get('security.authorization_checker')->isGranted('ROLE_ADMINDEC') and $sousDeclarationCollecteur->getStatut()->getCode() < '40') or ( $this->get('security.authorization_checker')->isGranted('ROLE_ODEC') and $sousDeclarationCollecteur->getStatut()->getCode() < '30')) {
                $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsBySousDeclarationCollecteur($sousDeclarationCollecteur->getId());
                if ($declarationDetails) {
                    foreach ($declarationDetails as $declarationDetail) {
                        $declarationProducteur = $declarationDetail->getDeclarationProducteur();
                        $emDec->remove($declarationDetail);
                        $emDec->flush();
                        if ($declarationProducteur) {
                            $ok = $this->majStatutDeclarationProducteursAction($declarationProducteur->getId(), $user, $emDec, $session);
                        }
                    }
                }


                $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($sousDeclarationCollecteur->getDeclarationCollecteur()->getId());

                $emDec->remove($sousDeclarationCollecteur);

                $session->getFlashBag()->add('notice-success', "La déclaration n° " . $sousDeclarationCollecteur->getNumero() . " a été supprimée avec succès !");

                $emDec->flush();

                $ok = $this->majStatutDeclarationCollecteursAction($declarationCollecteur->getId(), $user, $emDec, $session);
            } else {
                $session->getFlashBag()->add('notice-warning', "La déclaration N°" . $sousDeclarationCollecteur->getNumero() . " ne peut être supprimée !");
            }
        } else {
            $session->getFlashBag()->add('notice-warning', "La déclaration n' existe pas !");
        }

        return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeSousDeclarations', array('declarationCollecteur_id' => $declarationCollecteur->getId())));
    }

    public function listeDeclarationDetailsAction($sousDeclarationCollecteur_id = null, $declarationDetail_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'listeDeclarationDetails');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');

        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurById($sousDeclarationCollecteur_id);
        $declarationCollecteur = $sousDeclarationCollecteur->getDeclarationcollecteur();
        $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsBySousDeclarationCollecteur($sousDeclarationCollecteur->getid());

        $tab = array();
        $i = 0;
        if ($declarationDetails) {
            foreach ($declarationDetails as $declaration) {
                $tab[$i]['id'] = $declaration->getId();
                $tab[$i]['statutCode'] = $declaration->getStatut()->getCode();
                $tab[$i]['statutLibelle'] = $declaration->getStatut()->getLibelle();
                $tab[$i]['declarationProducteurId'] = $declaration->getDeclarationproducteur()->getId();
                $producteur = $repoOuvrage->getOuvrageById($declaration->getDeclarationproducteur()->getProducteur());
                if ($producteur) {
                    $tab[$i]['producteurSiret'] = $producteur->getSiret();
                    $tab[$i]['producteurLibelle'] = $producteur->getLibelle();
                    if (!$producteur->getCommune()) {
                        $tab[$i]['producteurCodePostal'] = $producteur->getCp();
                    } else {
                        $tab[$i]['producteurCodePostal'] = $producteur->getCommune()->getCommune();
                    }
                } else {
                    $tab[$i]['producteurSiret'] = null;
                    $tab[$i]['producteurLibelle'] = null;
                    $tab[$i]['producteurCodePostal'] = null;
                }

                $tab[$i]['declarationProducteurQuantiteReel'] = $declaration->getDeclarationProducteur()->getQuantiteReel();
                $tab[$i]['declarationProducteurQuantiteRet'] = $declaration->getDeclarationProducteur()->getQuantiteRet();
                $tab[$i]['declarationProducteurMontAide'] = $declaration->getDeclarationProducteur()->getMontAide();

                if ($declaration->getCentreTraitement()) {
                    $centreTraitement = $repoOuvrage->getOuvrageById($declaration->getCentreTraitement());
                    if ($centreTraitement) {
                        $tab[$i]['centreTraitement'] = $centreTraitement->getNumero();
                    } else {
                        $tab[$i]['centreTraitement'] = "";
                    }
                } else {
                    $tab[$i]['centreTraitement'] = "";
                }
                if ($declaration->getCentreTransit()) {
                    $centreTransit = $repoOuvrage->getOuvrageById($declaration->getCentreTransit());
                    $tab[$i]['centreTransit'] = $centreTransit->getSiret();
                } else {
                    $tab[$i]['centreTransit'] = "";
                }
                if ($declaration->getCentreDepot()) {
                    $centreDepot = $repoOuvrage->getOuvrageById($declaration->getCentreDepot());
                    $tab[$i]['centreDepot'] = $centreDepot->getNumero();
                } else {
                    $tab[$i]['centreDepot'] = "";
                }
                if ($declaration->getDechet()) {
                    $tab[$i]['dechet'] = $declaration->getDechet()->getCode();
                } else {
                    $tab[$i]['dechet'] = "";
                }
                $tab[$i]['nature'] = $declaration->getNature();
                if ($declaration->getFiliere()) {
                    $tab[$i]['filiere'] = $declaration->getFiliere()->getCode();
                } else {
                    $tab[$i]['filiere'] = "";
                }
                if ($declaration->getTraitFiliere()) {
                    $tab[$i]['traitFiliere'] = $declaration->getTraitFiliere()->getCode();
                } else {
                    $tab[$i]['traitFiliere'] = "";
                }
                if ($declaration->getNaf()) {
                    $tab[$i]['naf'] = $declaration->getNaf()->getCode();
                } else {
                    $tab[$i]['naf'] = "";
                }
                if ($declaration->getDateFacture()) {
                    $tab[$i]['dateFacture'] = $declaration->getDateFacture();
                } else {
                    $tab[$i]['dateFacture'] = "";
                }
                if ($declaration->getNumFacture()) {
                    $tab[$i]['numFacture'] = $declaration->getNumFacture();
                } else {
                    $tab[$i]['numFacture'] = "";
                }
                $tab[$i]['coutFacture'] = $declaration->getCoutFacture();
                $tab[$i]['quantiteReel'] = $declaration->getQuantiteReel();
                $tab[$i]['quantiteRet'] = $declaration->getQuantiteRet();
                $tab[$i]['montAide'] = $declaration->getMontAide();
                $tab[$i]['tauxAide'] = $declaration->getTauxAide();
                if ($declaration->getBonnifie()) {
                    $tab[$i]['bonnifie'] = 'O';
                } else {
                    $tab[$i]['bonnifie'] = 'N';
                }
                $tab[$i]['montReel'] = $declaration->getMontReel();
                $i++;
            }
        }

        if (!$declarationDetail_id) {
            if ($declarationDetails) {
                $declarationDetail_id = $declarationDetails[0]->getId();
            }
        }

        $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($sousDeclarationCollecteur->getDeclarationCollecteur()->getId());
        $collecteur = $repoOuvrage->getOuvrageById($sousDeclarationCollecteur->getDeclarationCollecteur()->getCollecteur());
        $nomFichier = 'dec_fichier_' . $collecteur->getNumero() . '_' . $declarationCollecteur->getAnnee() . '_' . $sousDeclarationCollecteur->getNumero() . '.csv';
        $pathFichier = 'AeagDecBundle_collecteur_ajouterFichierDeclarationDetail';
        $parametreFichier = $sousDeclarationCollecteur->getId();
        return $this->render('AeagDecBundle:Collecteur:listeDeclarationDetails.html.twig', array(
                    'nomFichier' => $nomFichier,
                    'pathFichier' => $pathFichier,
                    'annee' => $sousDeclarationCollecteur->getDeclarationCollecteur()->getAnnee(),
                    'collecteur' => $collecteur,
                    'declarationCollecteur' => $declarationCollecteur,
                    'sousDeclarationCollecteur' => $sousDeclarationCollecteur,
                    'entities' => $tab,
                    'declarationDetail_id' => $declarationDetail_id
        ));
    }

    public function ajouterFichierAction($sousDeclarationCollecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'ajouterFichier');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurById($sousDeclarationCollecteur_id);
        $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($sousDeclarationCollecteur->getDeclarationCollecteur()->getId());
        $collecteur = $repoOuvrage->getOuvrageById($sousDeclarationCollecteur->getDeclarationCollecteur()->getCollecteur());
        $parametre = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'REP_DOCUMENT'));
        $repertoire = $parametre->getLibelle();
        $nomFichier = 'dec_fichier_' . $collecteur->getNumero() . '_' . $declarationCollecteur->getAnnee() . '_' . $sousDeclarationCollecteur->getNumero() . '.csv';
        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurById($sousDeclarationCollecteur_id);


//turn on php error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $response = null;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $name = $_FILES['file']['name'];
            $tmpName = $_FILES['file']['tmp_name'];
            $error = $_FILES['file']['error'];
            $size = $_FILES['file']['size'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            switch ($error) {
                case UPLOAD_ERR_OK:
                    $valid = true;
//validate file extensions
                    if (!in_array($ext, array('csv'))) {
                        $valid = false;
                        $response = 'extension du fichier incorrecte.';
                    }
//validate file size
                    if ($size / 1024 / 1024 > 2) {
                        $valid = false;
                        $response = 'La taille du fichier est plus grande que la taille autorisée.';
                    }
//upload file
                    if ($valid) {
                        $targetPath = $parametre->getLibelle() . '/' . $nomFichier;
//$targetPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $name;
//return new Response ('tmpName : ' . $tmpName . ' targetPath : ' . $targetPath);

                        move_uploaded_file($tmpName, $targetPath);
                        /* header('Location: ' . $this->generateUrl('AeagDecBundle_collecteur_ajouterFichier', array(
                          'sousDeclarationCollecteur_id' => $sousDeclarationCollecteur_id)));
                          exit; */
                    }
                    break;
                case UPLOAD_ERR_INI_SIZE:
                    $response = 'Le fichier téléchargé excède la taille de upload_max_filesize dans php.ini.';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $response = 'Le fichier téléchargé excède la taille de MAX_FILE_SIZE qui a été spécifié dans le formulaire HTML.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $response = 'Le fichier n\'a été que partiellement téléchargé.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $response = 'Aucun fichier sélectionné.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $response = 'Manquantes dans un dossier temporaire. Introduit en PHP 4.3.10 et PHP 5.0.3.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $response = 'Impossible d\'écrire le fichier sur le disque. Introduit en PHP 5.1.0.';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $response = 'Le téléchargement du fichier arrêté par extension. Introduit en PHP 5.2.0.';
                    break;
                default:
                    $response = 'erreur inconnue';
                    break;
            }

            if (!$response) {
                $fichier = fopen($targetPath, "r");
                set_time_limit(10000); // temps dexecution du script le plus longtemps
                $ligne = 0;
                while (!feof($fichier)) {
                    $tab = fgetcsv($fichier, 1024, ';');
                    $num = count($tab);
                    $ligne++;
                    if ($num > 1) {
                        $tab[0] = str_replace(' ', '', $tab[0]);
                        if (strlen($tab[0]) <> 14) {
                            $err = true;
                            $response = $response . "Siret " . $tab[0] . " incorrect à la ligne " . $ligne . " \n";
                        } else {
                            if (substr($tab[0], 9, 5) == '00000') {
                                $err = true;
                                $response = $response . "Siret " . $tab[0] . " incorrect à la ligne " . $ligne . ".  \n";
                            }
                        }
                    }
                }
                if ($response) {
                    $response = $response . "Corriger le fichier puis relancer l'opération.";
                }
            }


            if (!$response) {
                $fichier = $repertoire . "/" . $nomFichier;
                $tabfich = file($fichier);
                $i = 0;
                $nb = 0;
                for ($j = 0; $j < count($tabfich); $j++) {
                    if ($j == 0) {
                        $entete = $tabfich[$i];
                        $nb++;
                        $sousFichier = $repertoire . '/dec_fichier_' . $collecteur->getNumero() . '_' . $declarationCollecteur->getAnnee() . '_' . $sousDeclarationCollecteur->getNumero() . '_' . $nb . ".csv";
                        $sousFic = fopen($sousFichier, "w+");
                        fputs($sousFic, $entete);
                        $i = 0;
                    } else {
                        fputs($sousFic, $tabfich[$j]);
                        $i++;
                        if ($i > 200) {
                            fclose($sousFic);
                            $nb++;
                            $sousFichier = $repertoire . '/dec_fichier_' . $collecteur->getNumero() . '_' . $declarationCollecteur->getAnnee() . '_' . $sousDeclarationCollecteur->getNumero() . '_' . $nb . ".csv";
                            $sousFic = fopen($sousFichier, "w");
                            fputs($sousFic, $entete);
                            $i = 0;
                        }
                    }
                }
                fclose($sousFic);
                $repertoireSauvegardes = $repertoire . '/Sauvegardes';
                if (copy($repertoire . '/' . $nomFichier, $repertoireSauvegardes . '/' . $nomFichier)) {
                    unlink($repertoire . '/' . $nomFichier);
                }

                return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_ajouterFichierDeclarationDetail', array('sousDeclarationCollecteur_id' => $sousDeclarationCollecteur_id)));
            }
        }

        return $this->render('AeagDecBundle:Collecteur:ajouterFichier.html.twig', array(
                    'nomFichier' => $nomFichier,
                    'sousDeclarationCollecteur' => $sousDeclarationCollecteur,
                    'message' => $response
        ));
    }

    public function ajouterFichierDeclarationDetailAction($sousDeclarationCollecteur_id = null) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'ajouterFichierDeclarationDetail');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoDepartement = $em->getRepository('AeagAeagBundle:Departement');
        $repoCommune = $em->getRepository('AeagAeagBundle:Commune');
        $repoCodePostal = $em->getRepository('AeagAeagBundle:CodePostal');
        $repoDeclarationProducteur = $emDec->getRepository('AeagDecBundle:DeclarationProducteur');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoCollecteurProducteur = $emDec->getRepository('AeagDecBundle:CollecteurProducteur');
        $repoProducteurNonPlafonne = $emDec->getRepository('AeagDecBundle:ProducteurNonPlafonne');
        $repoProducteurTauxSpecial = $emDec->getRepository('AeagDecBundle:ProducteurTauxSpecial');
        $repoTaux = $emDec->getRepository('AeagDecBundle:Taux');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoFiliere = $emDec->getRepository('AeagDecBundle:Filiere');

        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurById($sousDeclarationCollecteur_id);

        if (!$sousDeclarationCollecteur) {
            throw $this->createNotFoundException('Impossible de retouver la déclaration : ' . $sousDeclarationCollecteur_id);
        }

        $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($sousDeclarationCollecteur->getDeclarationCollecteur()->getId());
        $collecteur = $repoOuvrage->getOuvrageById($sousDeclarationCollecteur->getDeclarationCollecteur()->getCollecteur());

        $parametre = $emDec->getRepository('AeagDecBundle:Parametre')->findOneBy(array('code' => 'REP_DOCUMENT'));
        $repertoire = $parametre->getLibelle();
        $nomFichier = 'dec_fichier_' . $collecteur->getNumero() . '_' . $declarationCollecteur->getAnnee() . '_' . $sousDeclarationCollecteur->getNumero() . '.csv';

        $fichiers = array();
        $resume = null;
        $resume_nbLignes = 0;
        $resume_nbErreurs = 0;
        $ok = true;
        $i = 0;
        $ajout = 0;
        $modif = 0;
        $erreur = 0;
        $ligne = 0;
        set_time_limit(10000); // temps dexecution du script le plus longtemps
        $dir = opendir($repertoire) or die("Erreur le repertoire $repertoire n\'existe pas");
        while ($fic = readdir($dir)) {
            //print_r('fichier : ' . $fic);
            if (is_file($fic) or ( !in_array($fic, array(".", "..")) and $fic != 'Sauvegardes')) {
                $res = split("_", $fic);
                if ($res[2] == $collecteur->getNumero() and $res[3] == $declarationCollecteur->getAnnee() and $res[4] == $sousDeclarationCollecteur->getNumero()) {
                    $fichier = fopen($repertoire . '/' . $fic, "r");

                    while (!feof($fichier)) {
                        $tab = fgetcsv($fichier, 1024, ';');
                        $num = count($tab);
                        $ligne++;
                        if ($num > 1) {
                            $err = false;
                            $message = "";
                            $tab[0] = str_replace(' ', '', $tab[0]);
                            if (strlen($tab[0]) <> 14) {
                                $err = true;
                                $message = $message . "dans le fichier CSV : siret " . $tab[0] . " incorrect à la ligne " . $ligne . ".  \n";
                            }
                            if (substr($tab[0], 9, 5) == '00000') {
                                $err = true;
                                $message = $message . "dans le fichier CSV : siret " . $tab[0] . " incorrect à la ligne " . $ligne . ".  \n";
                            }
                            $producteurs = $repoOuvrage->getOuvragesBySiretType($tab[0], 'PDEC');
                            $producteur = null;
                            if (count($producteurs) > 0) {
                                $producteur = $producteurs[0];
                            }
                            if (!$producteur) {
                                $producteur = new Ouvrage();
                                $producteur->setLibelle($this->wd_remove_accents($tab[1]));
                                if ($tab[2]) {
                                    $tab[2] = str_replace(' ', '', $tab[2]);
                                    $tab[2] = str_pad($tab[2], 5, '0', STR_PAD_LEFT);
                                    $codePostal = $repoCodePostal->getCodePostalByCp($tab[2]);
                                    if (!$codePostal) {
                                        $dept = substr($tab[2], 0, 2);
                                        $departement = $repoDepartement->getDepartementByDept($dept);
                                        if ($departement->getDec() == 'N') {
                                            $err = true;
                                            $message = $message . "dans le fichier CSV : Département " . $dept . " non aidable par l'agence de l'eau 'adour-garonne' à la ligne " . $ligne . " \n";
                                        } else {
                                            $producteur->setCp($tab[2]);
                                        }
                                    } else {
                                        $aidable = 'O';
                                        foreach ($codePostal as $cp) {
                                            if ($cp->getDec() == 'N') {
                                                $err = true;
                                                $message = $message . "dans le fichier CSV : Commune " . $tab[2] . " non aidable par l'agence de l'eau 'adour-garonne' à la ligne " . $ligne . " \n";
                                            } else {
                                                if (count($codePostal) == 1) {
                                                    $producteur->setCommune($cp->getCommune());
                                                    $producteur->setCp($cp->getCp());
                                                    $producteur->setVille($cp->getAcheminement());
                                                } else {
                                                    $producteur->setCp($cp->getCp());
                                                }
                                                break;
                                            }
                                        }
                                    }
                                } else {
                                    $err = true;
                                    $message = $message . "dans le fichier CSV : Code postal " . $tab[2] . " incorrecte à la ligne " . $ligne . " \n";
                                }
                                $producteur->setType('PDEC');
                                $producteur->setSiret($tab[0]);
                                $producteur->setDec('O');
                                $em->persist($producteur);
                                $em->flush();
                            } else {
                                $producteur->setLibelle($this->wd_remove_accents($tab[1]));
                                if ($tab[2]) {
                                    $tab[2] = str_replace(' ', '', $tab[2]);
                                    $tab[2] = str_pad($tab[2], 5, '0', STR_PAD_LEFT);
                                    $codePostal = $repoCodePostal->getCodePostalByCp($tab[2]);
                                    if (!$codePostal) {
                                        $dept = substr($tab[2], 0, 2);
                                        $departement = $repoDepartement->getDepartementByDept($dept);
                                        if ($departement->getDec() == 'N') {
                                            $err = true;
                                            $message = $message . "dans le fichier CSV : Département " . $dept . " non aidable par l'agence de l'eau 'adour-garonne' à la ligne " . $ligne . " \n";
                                        } else {
                                            $producteur->setCp($tab[2]);
                                        }
                                    } else {
                                        $aidable = 'O';
                                        foreach ($codePostal as $cp) {
                                            if ($cp->getDec() == 'N') {
                                                $err = true;
                                                $message = $message . "dans le fichier CSV : Commune " . $tab[2] . " non aidable par l'agence de l'eau 'adour-garonne' à la ligne " . $ligne . " \n";
                                            } else {
                                                if (count($codePostal) == 1) {
                                                    $producteur->setCommune($cp->getCommune());
                                                    $producteur->setCp($cp->getCp());
                                                    $producteur->setVille($cp->getAcheminement());
                                                } else {
                                                    $producteur->setCp($cp->getCp());
                                                }
                                                break;
                                            }
                                        }
                                    }
                                } else {
                                    $err = true;
                                    $message = $message . "dans le fichier CSV : Code postal " . $tab[2] . " incorrecte à la ligne " . $ligne . " \n";
                                }
                                $em->persist($producteur);
                                $em->flush();
                            }


                            $naf = null;
                            if ($tab[3] != "") {
                                $tab[3] = str_replace(' ', '', $tab[3]);
                                $repoNaf = $emDec->getRepository('AeagDecBundle:Naf');
                                $naf = $repoNaf->getNafByCode($tab[3]);
                                if (!$naf) {
                                    $err = true;
                                    $message = $message . "dans le fichier CSV : Code NAF " . $tab[3] . " incorrect à la ligne " . $ligne . " \n";
                                }
                            } else {
                                $err = true;
                                $message = $message . "dans le fichier CSV : Code NAF obligatoire à la ligne " . $ligne . " \n";
                            }

                            $numFacture = null;
                            if (!$tab[4]) {
                                $err = true;
                                $message = $message . "dans le fichier CSV : Numéro facture obligatoire à la ligne " . $ligne . " \n";
                            } else {
                                $tab[4] = str_replace(' ', '', $tab[4]);
                                $numFacture = $tab[4];
                            }

                            $dateFacture = null;
                            if (!$tab[5]) {
                                $err = true;
                                $message = $message . "dans le fichier CSV : Date facture obligatoire à la ligne " . $ligne . " \n";
                            } else {
                                //print_r('date facture : ' . $tab[5] . ' a la ligne : ' . $ligne);
                                $tab[5] = str_replace(' ', '', $tab[5]);
                                $dateFact = split("/", $tab[5]);
                                if (strlen($dateFact[2] == 2)) {
                                    $dateFact[2] = '20' . $dateFact[2];
                                }
                                $dateFacture = new \DateTime($dateFact[2] . '-' . $dateFact[1] . '-' . $dateFact[0]);
                            }

                            $dechet = null;
                            $dec = str_replace(' ', '', $tab[6]);
                            if (strlen($dec) == 6) {
                                $dec = substr($dec, 0, 2) . ' ' . substr($dec, 2, 2) . ' ' . substr($dec, 4, 2);
                                $repoDechet = $emDec->getRepository('AeagDecBundle:Dechet');
                                $dechet = $repoDechet->getDechetByCode($dec);
                                if (!$dechet) {
                                    $err = true;
                                    $message = $message . "dans le fichier CSV : Code déchet " . $tab[6] . " incorrect à la ligne " . $ligne . " \n";
                                }
                            } else {
                                $err = true;
                                $message = $message . "dans le fichier CSV : Code déchet obligatoire à la ligne " . $ligne . " \n";
                            }

                            $nature = null;
                            if ($tab[7]) {
                                $nature = $this->wd_remove_accents($tab[7]);
                            }

                            $DR = null;
                            if ($tab[8] != "") {
                                $tab[8] = str_replace(' ', '', $tab[8]);
                                $DR = $repoFiliere->getFiliereByCode($tab[8]);
                                if (!$DR) {
                                    $err = true;
                                    $message = $message . "dans le fichier CSV : code D/R " . $tab[8] . " incorrect à la ligne " . $ligne . " \n";
                                }
                            } else {
                                $err = true;
                                $message = $message . "dans le fichier CSV : code D/R obligatoire à la ligne " . $ligne . " \n";
                            }

                            $centreTraitement = null;
                            if ($tab[9] == "") {
                                $tab[9] = str_replace(' ', '', $tab[9]);
                                $centreTraitement = $repoOuvrage->getOuvrageByNumeroType($collecteur->getNumero(), 'CTT');
                                if (!$centreTraitement) {
                                    $centreTraitement = clone $collecteur;
                                    $centreTraitement->setType('CTT');
                                    $em->persist($centreTraitement);
                                    $em->flush();
                                }
                            } else {
                                if (strlen($tab[9]) == 14) {
                                    $centreTraitements = $repoOuvrage->getOuvragesBySiretType($tab[9], 'CTT');
                                    if ($centreTraitements) {
                                        $centreTraitement = null;
                                    }
                                } else {
                                    $centreTraitement = $repoOuvrage->getOuvrageByNumeroType($tab[9], 'CTT');
                                }
                            }
                            if (!$centreTraitement) {
                                $err = true;
                                $message = "dans le fichier CSV : centre de traitement " . $tab[9] . " incorrect à la ligne " . $ligne . " \n";
                            } else {
                                if ($centreTraitement->getDec() == 'N') {
                                    $err = true;
                                    $message = "dans le fichier CSV : centre de traitement " . $tab[9] . " non aidable à la ligne " . $ligne . " \n";
                                }
                            }


                            $repoOuvrageFiliere = $emDec->getRepository('AeagDecBundle:OuvrageFiliere');
                            if ($centreTraitement and $DR) {
                                $ouvrageFiliere = $repoOuvrageFiliere->getOuvrageFiliereByOuvrageFiliere($centreTraitement->getId(), $DR->getCode(), $declarationCollecteur->getAnnee());
                                if (!$ouvrageFiliere) {
                                    $err = true;
                                    $message = $message . "dans le fichier CSV : code D/R " . $tab[8] . " non conventionné pour le centre de traitement " . $tab[9] . " à la ligne " . $ligne . ". \n";
                                }
                            } else {
                                $ouvrageFiliere = null;
                                $err = true;
                                $message = $message . "dans le fichier CSV : code D/R " . $tab[8] . " non conventionné pour le centre de traitement " . $tab[9] . " à la ligne " . $ligne . ". \n";
                            }

                            $statut = $repoStatut->getStatutByCode('20');
                            $declarationProducteur = $repoDeclarationProducteur->getDeclarationProducteurByProducteurAnnee($producteur->getId(), $declarationCollecteur->getAnnee());
                            if (!$declarationProducteur) {
                                $declarationProducteur = new DeclarationProducteur();
                                $declarationProducteur->setProducteur($producteur->getid());
                                $declarationProducteur->setStatut($statut);
                                $declarationProducteur->setAnnee($declarationCollecteur->getAnnee());
                                $declarationProducteur->setQuantiteReel(0);
                                $declarationProducteur->setMontReel(0);
                                $declarationProducteur->setQuantiteRet(0);
                                $declarationProducteur->setMontRet(0);
                                $declarationProducteur->setQuantiteAide(0);
                                $declarationProducteur->setMontAide(0);
                                $emDec->persist($declarationProducteur);
                                $emDec->flush();
                            }

                            $filiere = null;
                            if ($tab[11] != "") {
                                $tab[11] = str_replace(' ', '', $tab[11]);
                                $filiere = $repoFiliere->getFiliereByCode($tab[11]);
                                if (!$filiere) {
                                    $err = true;
                                    $message = $message . "dans le fichier CSV : Code conditionnement " . $tab[11] . " incorrect à la ligne " . $ligne . " \n";
                                }
                            } else {
                                $err = true;
                                $message = $message . "dans le fichier CSV : Code conditionnement obligatoire à la ligne " . $ligne . " \n";
                            }

                            $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
                            $tab[10] = str_replace(' ', '', $tab[10]);
                            $tab[10] = str_replace(',', '.', $tab[10]);
                            $tab[13] = str_replace(' ', '', $tab[13]);
                            $tab[13] = str_replace(',', '.', $tab[13]);
                            if ($dechet and $filiere and $DR) {
                                $declarationDetail = $repoDeclarationDetail->getDeclarationDetail($sousDeclarationCollecteur->getId(), $declarationProducteur->getId(), $dechet->getCode(), $filiere->getCode(), $DR->getCode(), $tab[4], $tab[10], $tab[13], $this->wd_remove_accents($tab[7]), $dateFacture);
                            } else {
                                $declarationDetail = null;
                            }
                            if (!$declarationDetail) {
                                $declarationDetail = new DeclarationDetail();
                                $action = 'AJOUTER';
                            } else {
                                $err = true;
                                $message = $message . "dans le fichier CSV :  la ligne " . $ligne . " est en double  date " . $dateFacture->format('Y-m-d') . "\n";
                                $declarationDetail = $declarationDetail;
                                $ancDeclarationDetail = clone($declarationDetail);
                                if ($ancDeclarationDetail->getStatut()->getCode() == '11') {
                                    $ancDeclarationDetail->setQuantiteReel(0);
                                    $ancDeclarationDetail->setMontReel(0);
                                    $ancDeclarationDetail->setQuantiteRet(0);
                                    $ancDeclarationDetail->setMontRet(0);
                                    $ancDeclarationDetail->setQuantiteAide(0);
                                    $ancDeclarationDetail->setMontAide(0);
                                    $ancDeclarationDetail->setDossierAide(0);
                                    $ancDeclarationDetail->setMontantAp(0);
                                    $ancDeclarationDetail->setMontantApDispo(0);
                                }
                                $action = 'MODIFIER';
                            }
                            $declarationDetail->setSousDeclarationCollecteur($sousDeclarationCollecteur);
                            $declarationDetail->setDeclarationProducteur($declarationProducteur);
                            if ($centreTraitement) {
                                $declarationDetail->setCentreTraitement($centreTraitement->getid());
                            }
                            $centreTransit = null;
                            // print_r(' nb : ' . count($tab));
                            if (count($tab) >= 16) {
                                if ($tab[15]) {
                                    $tab[15] = str_replace(' ', '', $tab[15]);
                                    if (strlen($tab[15]) == 14) {
                                        $centreTransits = $repoOuvrage->getOuvragesBySiretType($tab[15], 'CT');
                                        if ($centreTransits) {
                                            $centreTransit = null;
                                        }
                                    } else {
                                        $centreTransit = $repoOuvrage->getOuvrageByNumeroType($tab[15], 'CT');
                                    }
                                }
                                if ($centreTransit) {
                                    $centreTransitAeag = $repoOuvrage->getOuvrageByNumeroType($collecteur->getNumero(), 'CT');
                                    if ($centreTransit != $centreTransitAeag) {
                                        $err = true;
                                        $message = "dans le fichier CSV : centre de transit " . $tab[15] . " incorrect à la ligne " . $ligne . " \n";
                                    } else {
                                        $declarationDetail->setCentreTransit($centreTransit->getid());
                                    }
                                } else {
                                    $centreTransitAeag = $repoOuvrage->getOuvrageByNumeroType($collecteur->getNumero(), 'ODEC');
                                    if (!$centreTransitAeag) {
                                        $err = true;
                                        $message = "dans le fichier CSV : centre de transit " . $tab[15] . " incorrect à la ligne " . $ligne . " \n";
                                    } else {
                                        $declarationDetail->setCentreTransit($centreTransitAeag->getid());
                                    }
                                }
                            }
                            $centreDepot = null;
                            if (count($tab) >= 15) {
                                if ($tab[14]) {
                                    $tab[14] = str_replace(' ', '', $tab[14]);
                                    if (strlen($tab[14]) == 14) {
                                        $centreDepots = $repoOuvrage->getOuvragesBySiretType($tab[14], 'ODEC');
                                        if ($centreDepots) {
                                            $centreDepot = $centreDepots[0];
                                        } else {
                                            $centreDepot = $repoOuvrage->getOuvrageByNumeroType($tab[14], 'ODEC');
                                        }
                                        if ($centreDepot) {
                                            $declarationDetail->setCentreDepot($centreDepot->getid());
                                        } else {
                                            $err = true;
                                            $message = "dans le fichier CSV : centre d'entreposage " . $tab[14] . " incorrect à la ligne " . $ligne . " \n";
                                        }
                                    }
                                }
                            }

                            if ($dechet) {
                                $declarationDetail->setDechet($dechet);
                            }
                            if ($filiere) {
                                $declarationDetail->setFiliere($filiere);
                            }
                            if ($DR) {
                                $declarationDetail->setTraitFiliere($DR);
                            }

                            if ($naf) {
                                $declarationDetail->setNaf($naf);
                            }

                            if ($nature) {
                                $declarationDetail->setNature($nature);
                            }

                            if ($dateFacture) {
                                $declarationDetail->setDateFacture($dateFacture);
                            }

                            if ($numFacture) {
                                $declarationDetail->setNumFacture($numFacture);
                            }

// controle quantite déclaré par le producteur durand l'annee
                            if ($tab[10] != "") {
                                $tab[10] = str_replace(',', '.', $tab[10]);
                                if (is_numeric($tab[10])) {
                                    $quantiteReel = $tab[10];
                                    if ($action == 'AJOUTER') {
                                        $quantiteAnnuelleProducteur = ($declarationProducteur->getQuantiteRet() + $tab[10]);
                                    } else {
                                        $quantiteAnnuelleProducteur = ($declarationProducteur->getQuantiteRet() + $tab[10] - $ancDeclarationDetail->getQuantiteRet());
                                    }
                                    $producteurNonPlafonne = $repoProducteurNonPlafonne->getProducteurNonPlafonneBySiret($producteur->getSiret());
                                    $tauxValeur = $repoTaux->getTauxByAnneeCode($declarationCollecteur->getAnnee(), 'MAXTONNAGE');
                                    $tauxMaxTonnage = ($tauxValeur->getValeur() * 1000);
                                    if (!$producteurNonPlafonne) {
                                        if ($quantiteAnnuelleProducteur > $tauxMaxTonnage) {

                                            $quantiteRet = ($quantiteReel - ($quantiteAnnuelleProducteur - $tauxMaxTonnage));
                                            if ($quantiteRet < 0) {
                                                $quantiteRet = 0;
                                            }
                                            $err = true;
                                            $message = $message . "dans le fichier CSV : La quantité actuelle du producteur = " . $quantiteAnnuelleProducteur . " kg dépasse la quantité autorisée = " . $tauxMaxTonnage . " kg.";
                                            $message = $message . " La quantité pesée devrait être égale à " . $quantiteRet . " \n";
                                            //$message = $message . "QuantiteRet : " . $declarationProducteur->getQuantiteRet() . " tab(10) : " . $tab[10] . "\n";
                                        } else {
                                            $quantiteRet = $tab[10];
                                        }
                                    } else {
                                        $quantiteRet = $tab[10];
                                    }
                                } else {
                                    $quantiteReel = 0;
                                    $quantiteRet = 0;
                                    $err = true;
                                    $message = $message . "dans le fichier CSV : Quantité pesée " . $tab[10] . " incorrecte à la ligne " . $ligne . " \n";
                                }
                            } else {
                                $quantiteReel = 0;
                                $quantiteRet = 0;
                                $err = true;
                                $message = $message . "dans le fichier CSV : Quantité pesée obligatoire à la ligne " . $ligne . " \n";
                            }

                            $quantiteAide = $quantiteRet;
                            $producteurTauxSpecial = $repoProducteurTauxSpecial->getProducteurTauxSpecialBySiret($producteur->getSiret());
                            if ($producteurTauxSpecial) {
                                $tauxAide = $producteurTauxSpecial->getTaux() / 100;
                                $bonnifie = true;
                            } else {
                                $tauxAideAgence = $repoTaux->getTauxByAnneeCode($declarationCollecteur->getAnnee(), 'TAUXAIDE');
                                $tauxAide = $tauxAideAgence->getValeur();
                                $bonnifie = false;
                            }
                            if ($tab[12] != "") {
                                $tab[12] = str_replace(',', '.', $tab[12]);
                                if (is_numeric($tab[12])) {
                                    $montRet = round(($quantiteRet * $tab[12] * $tauxAide), 2);
                                    $montAide = round(($quantiteAide * $tab[12] * $tauxAide), 2);
                                } else {
                                    $montRet = 0;
                                    $montAide = 0;
                                }
                            } else {
                                $montRet = 0;
                                $montAide = 0;
                                $err = true;
                                $message = $message . "dans le fichier CSV : Coût facturé " . $tab[12] . " incorrect à la ligne " . $ligne . " \n";
                            }
                            if ($tab[13] != "") {
                                $tab[13] = str_replace(',', '.', $tab[13]);
                                if (is_numeric($tab[13])) {
                                    $montReel = round(($tab[13]), 2);
                                    if ($montReel != $montAide) {
                                        $err = true;
                                        $message = $message . "dans le fichier CSV : Montant de l'aide(" . $montReel . " €) doit être égal à  " . $montAide . " € à la ligne " . $ligne . " \n";
//$montAide = $tab[13];
                                        $montRet = $montReel;
                                        $montAide = $montReel;
                                    }
                                } else {
                                    $montReel = 0;
                                    $err = true;
                                    $message = $message . "dans le fichier CSV : montant de l\'aide " . $tab[13] . " incorrect à la ligne " . $ligne . " \n";
                                }
                            } else {
                                $montReel = 0;
                                $montRet = 0;
                                $montAide = 0;
                                $err = true;
                                $message = $message . "dans le fichier CSV : Montant de l'aide obligatoire à la ligne " . $ligne . " \n";
                            }

//                            if ($quantiteAide > 0) {

                            $declarationDetail->setQuantiteReel($quantiteReel);
                            $declarationDetail->setMontReel($montReel);
                            $declarationDetail->setQuantiteRet($quantiteRet);
                            $declarationDetail->setMontRet($montRet);
                            $declarationDetail->setQuantiteAide($quantiteAide);
                            $declarationDetail->setMontAide($montAide);
                            $declarationDetail->setTauxAide($tauxAide);
                            $declarationDetail->setBonnifie($bonnifie);
                            $declarationDetail->setCoutFacture(round($tab[12], 5));
                            $declarationDetail->setDossierAide($sousDeclarationCollecteur->getDossierAide());
                            $declarationDetail->setMontantAp($sousDeclarationCollecteur->getMontantAp());
                            $declarationDetail->setMontantApDispo($sousDeclarationCollecteur->getMontantAp() - $montAide);
                            if ($declarationDetail->getMontantApDispo() <= 0) {
                                $declarationDetail->setMontantApDispo(0);
                                $err = true;
                                $message = $message . "dans le fichier CSV : l'enveloppe budgétaire (" . $declarationDetail->getMontantAp() . ") accordée par l'agence est atteinte pour l'annee " . $sousDeclarationCollecteur->getDeclarationCollecteur()->getAnnee() . " à la ligne " . $ligne . "\n";
                            }


                            if ($err) {
                                $ok = false;
                                $statut = $repoStatut->getStatutByCode('11');
                                $declarationDetail->setMessage($message);
                                $erreur++;
                                $resume_nbErreurs++;
                            } else {
                                $ok = true;
                                $statut = $repoStatut->getStatutByCode('10');
                            }
                            $resume_nbLignes++;
                            $declarationDetail->setStatut($statut);
                            $emDec->persist($declarationDetail);

                            $collecteurProducteur = $repoCollecteurProducteur->getCollecteurProducteurByCollecteurProducteur($collecteur->getId(), $producteur->getId());
                            if (!$collecteurProducteur) {
                                $collecteurProducteur = new CollecteurProducteur();
                                $collecteurProducteur->setCollecteur($collecteur->getid());
                                $collecteurProducteur->setProducteur($producteur->getid());
                                $emDec->persist($collecteurProducteur);
                            }
                            $emDec->flush();
                            $ok = $this->majStatutDeclarationProducteursAction($declarationDetail->getDeclarationProducteur()->getId(), $user, $emDec, $session);
//                            }
                        }
                    }
//return new Response('ici');
                    fclose($fichier);

                    unlink($repertoire . '/' . $fic);
                }
            }
        }

        $ok = $this->majStatutDeclarationCollecteursAction($declarationCollecteur->getid(), $user, $emDec, $session);



        if ($erreur == 0) {
            $session->getFlashBag()->add('notice-success', "fichier intégré avec " . $resume_nbLignes . " lignes à la déclaration  n° " . $sousDeclarationCollecteur->getNumero() . " !");
        } else {
            $session->getFlashBag()->add('notice-error', " fichier intégré avec " . $resume_nbErreurs . " anomalies sur " . $resume_nbLignes . " lignes à la déclaration n° " . $sousDeclarationCollecteur->getNumero() . " !");
        }

        if ($resume_nbErreurs > 0) {
            $resume = " fichier intégré avec " . $resume_nbErreurs . " anomalies sur " . $resume_nbLignes . " lignes";
        } else {
            $resume = "fichier intégré avec succes." . $resume_nbLignes . " lignes rajoutées à la déclaration  : ";
        }
        closedir($dir);
        return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_ajouterFichier', array('sousDeclarationCollecteur_id' => $sousDeclarationCollecteur_id)));
    }

    public function crudDeclarationDetailAction($crud = null, $sousDeclarationCollecteur_id = null, $declarationDetail_id = null, Request $request) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagDecBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'declarations');
        $session->set('controller', 'Collecteur');
        $session->set('fonction', 'crudDeclarationDetail');
        $em = $this->get('doctrine')->getManager();
        $emDec = $this->get('doctrine')->getManager('dec');

        $repoOuvrage = $em->getRepository('AeagAeagBundle:Ouvrage');
        $repoDeclarationProducteur = $emDec->getRepository('AeagDecBundle:DeclarationProducteur');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $repoProducteurNonPlafonne = $emDec->getRepository('AeagDecBundle:ProducteurNonPlafonne');
        $repoProducteurTauxSpecial = $emDec->getRepository('AeagDecBundle:ProducteurTauxSpecial');
        $repoCollecteurProducteur = $emDec->getRepository('AeagDecBundle:CollecteurProducteur');
        $repoTaux = $emDec->getRepository('AeagDecBundle:Taux');
        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoFiliere = $emDec->getRepository('AeagDecBundle:Filiere');
        $repoFiliereAide = $emDec->getRepository('AeagDecBundle:FiliereAide');
        $repoAnnee = $emDec->getRepository('AeagDecBundle:Parametre');
        $annee = $repoAnnee->findOneBy(array('code' => 'ANNEE'));

        $sousDeclarationCollecteur = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurById($sousDeclarationCollecteur_id);
        $declarationCollecteur = $repoDeclarationCollecteur->getDeclarationCollecteurById($sousDeclarationCollecteur->getDeclarationCollecteur()->getId());

        $tauxAideAgence = $repoTaux->getTauxByAnneeCode($declarationCollecteur->getAnnee(), 'TAUXAIDE');
        $tauxAide = $tauxAideAgence->getValeur();
        $collecteur = $repoOuvrage->getOuvrageById($sousDeclarationCollecteur->getDeclarationCollecteur()->getCollecteur());
        $centreTransit = $repoOuvrage->getOuvrageByNumeroType($collecteur->getNumero(), 'CT');
        $centreTraitement = $repoOuvrage->getOuvrageByNumeroType($collecteur->getNumero(), 'CTT');
        $centreDepot = $repoOuvrage->getOuvrageByNumeroType($collecteur->getNumero(), 'ODEC');

        $ancDeclarationDetail = null;

        if ($crud == 'C') {
            $declarationDetail = new DeclarationDetail();
            $producteur = null;
            if ($centreTransit) {
                $declarationDetail->setCentreTransit($centreTransit->getId());
            } else {
                $centreTransitAeag = $repoOuvrage->getOuvrageByNumeroType($collecteur->getNumero(), 'ODEC');
                if ($centreTransitAeag) {
                    $declarationDetail->setCentreTransit($centreTransitAeag->getId());
                }
            }
            if ($centreTraitement) {
                $declarationDetail->setCentreTraitement($centreTraitement->getId());
            }
//            if ($centreDepot) {
//                $declarationDetail->setCentreDepot($centreDepot->getId());
//            }
        } else {
            $declarationDetail = $repoDeclarationDetail->getDeclarationDetailById($declarationDetail_id);
            if (!$declarationDetail) {
                throw $this->createNotFoundException('Impossible de retouver la déclaration : ' . $declarationDetail_id);
            }
            $ancDeclarationDetail = clone $declarationDetail;
            if ($ancDeclarationDetail->getStatut()->getCode() == '11') {
                $ancDeclarationDetail->setQuantiteReel(0);
                $ancDeclarationDetail->setMontReel(0);
                $ancDeclarationDetail->setQuantiteRet(0);
                $ancDeclarationDetail->setMontRet(0);
                $ancDeclarationDetail->setQuantiteAide(0);
                $ancDeclarationDetail->setMontAide(0);
                $ancDeclarationDetail->setTauxAide(0);
                $ancDeclarationDetail->setBonnifie(false);
                $ancDeclarationDetail->setDossierAide(0);
                $ancDeclarationDetail->setMontantAp(0);
                $ancDeclarationDetail->setMontantApDispo(0);
            }
            $declarationProducteur = $repoDeclarationProducteur->getDeclarationProducteurById($declarationDetail->getDeclarationProducteur()->getId());
            $producteur = $repoOuvrage->getOuvrageById($declarationProducteur->getProducteur());
            if ($producteur) {
                $producteurTauxSpecial = $repoProducteurTauxSpecial->getProducteurTauxSpecialBySiret($producteur->getSiret());
            } else {
                $producteurTauxSpecial = null;
            }
            if ($producteurTauxSpecial) {
                $tauxAide = $producteurTauxSpecial->getTaux() / 100;
                $bonnifie = true;
            } else {
                $tauxAideAgence = $repoTaux->getTauxByAnneeCode($declarationCollecteur->getAnnee(), 'TAUXAIDE');
                $tauxAide = $tauxAideAgence->getValeur();
                $bonnifie = false;
            }
        }

        if ($crud == 'D') {
            if ($declarationDetail->getStatut()->getCode() < '30') {

                $emDec->remove($declarationDetail);
                $emDec->flush();

                $ok = $this->majStatutDeclarationCollecteursAction($declarationCollecteur->getid(), $user, $emDec, $session);
                $ok = $this->majStatutDeclarationProducteursAction($declarationProducteur->getId(), $user, $emDec, $session);

                if ($producteur) {
                    $session->getFlashBag()->add('notice-success', "Le producteur " . $producteur->getLibelle() . " a été retiré de la déclaration n° " . $sousDeclarationCollecteur->getNumero() . " !");
                } else {
                    $session->getFlashBag()->add('notice-success', "Le producteur sans siret  a été retiré de la déclaration n° " . $sousDeclarationCollecteur->getNumero() . " !");
                }
                $emDec->flush();
            } else {
                $session->getFlashBag()->add('notice-warning', "Le producteur " . $producteur->getLibelle() . " ne peut être retiré de la déclaration n° " . $sousDeclarationCollecteur->getNumero() . " !");
            }

            return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeDeclarationDetails', array('sousDeclarationCollecteur_id' => $sousDeclarationCollecteur->getId())));
        }


        if (!$sousDeclarationCollecteur) {
            throw $this->createNotFoundException('Impossible de retouver la déclaration : ' . $sousDeclarationCollecteur_id);
        }


        $crudDeclarationDetail = new CrudDeclarationDetail();
        $crudDeclarationDetail->setSousDeclarationCollecteur($sousDeclarationCollecteur);

        if ($crud != 'C') {
            if ($producteur) {
                $crudDeclarationDetail->setProducteur($producteur->getId());
            }
            $crudDeclarationDetail->setDeclarationProducteur($declarationProducteur);
        }
        if ($declarationDetail->getStatut()) {
            $crudDeclarationDetail->setStatut($declarationDetail->getStatut());
        }
        if ($declarationDetail->getCentreTraitement()) {
            $crudDeclarationDetail->setCentreTraitement($declarationDetail->getCentreTraitement());
        }
        if ($declarationDetail->getCentreTransit()) {
            $crudDeclarationDetail->setCentreTransit($declarationDetail->getCentreTransit());
        }
        if ($declarationDetail->getCentreDepot()) {
            $crudDeclarationDetail->setCentreDepot($declarationDetail->getCentreDepot());
        }
        if ($declarationDetail->getDechet()) {
            $crudDeclarationDetail->setDechet($declarationDetail->getDechet());
        }
        if ($declarationDetail->getFiliere()) {
            $filiereAide = $repoFiliereAide->getFiliereByCode($declarationDetail->getFiliere()->getCode());
            $crudDeclarationDetail->setFiliereAide($filiereAide);
        }
        if ($declarationDetail->getTraitFiliere()) {
            $crudDeclarationDetail->setTraitFiliere($declarationDetail->getTraitFiliere());
        }
        if ($declarationDetail->getNaf()) {
            $crudDeclarationDetail->setNaf($declarationDetail->getNaf());
        }
        if ($declarationDetail->getNature()) {
            $crudDeclarationDetail->setNature($declarationDetail->getNature());
        }
        if ($declarationDetail->getDateFacture()) {
            $crudDeclarationDetail->setDateFacture($declarationDetail->getDateFacture());
        }
        if ($declarationDetail->getNumFacture()) {
            $crudDeclarationDetail->setNumFacture($declarationDetail->getNumFacture());
        }
        if ($declarationDetail->getCoutFacture()) {
            $crudDeclarationDetail->setCoutFacture($declarationDetail->getCoutFacture());
        }
        if ($declarationDetail->getQuantiteReel()) {
            $crudDeclarationDetail->setQuantiteReel($declarationDetail->getQuantiteReel());
        }
        if ($declarationDetail->getMontReel()) {
            $crudDeclarationDetail->setMontReel($declarationDetail->getMontReel());
        }
        if ($declarationDetail->getQuantiteRet()) {
            $crudDeclarationDetail->setQuantiteRet($declarationDetail->getQuantiteRet());
        }
        if ($declarationDetail->getMontRet()) {
            $crudDeclarationDetail->setMontRet($declarationDetail->getMontRet());
        }
        if ($declarationDetail->getQuantiteAide()) {
            $crudDeclarationDetail->setQuantiteAide($declarationDetail->getQuantiteAide());
        }
        if ($declarationDetail->getMontAide()) {
            $crudDeclarationDetail->setMontAide($declarationDetail->getMontAide());
            $crudDeclarationDetail->setTauxAide($declarationDetail->getTauxAide());
            $crudDeclarationDetail->setBonnifie($declarationDetail->getBonnifie());
        }

        $collecteurPoducteurs = $repoCollecteurProducteur->getCollecteurProducteurByCollecteur($collecteur->getId());
        $producteurs = array();
        $i = 0;
        foreach ($collecteurPoducteurs as $collecteurPoducteur) {
            $prod = $repoOuvrage->getOuvrageById($collecteurPoducteur->getProducteur());
            if ($prod) {
                $producteurs[$i] = $prod;
                $i++;
            }
        }

        if (count($producteurs) > 0) {
            usort($producteurs, array('self', 'tri_producteurs'));
        }

//return new response ('centre : ' . $crudDeclarationDetail->getCentreTraitement() . ' base : ' . $declarationDetail->getCentreTraitement());
        if ($crudDeclarationDetail->getCentreTraitement()) {
            $idCTT = $crudDeclarationDetail->getCentreTraitement();
        } else {
            $idCTT = null;
        }

        if ($crudDeclarationDetail->getCentreTransit()) {
            $idCT = $crudDeclarationDetail->getCentreTransit();
        } else {
            $idCT = null;
        }

        if ($crudDeclarationDetail->getCentreDepot()) {
            $idCD = $crudDeclarationDetail->getCentreDepot();
        } else {
            $idCD = null;
        }

        if ($crud == 'C') {
            $form = $this->createForm(new CrudDeclarationDetailType(array($crud, $collecteur->getId(), $idCTT, $idCT, $idCD, null, $producteurs)), $crudDeclarationDetail);
        } else {
            if ($producteur) {
                $form = $this->createForm(new CrudDeclarationDetailType(array($crud, $collecteur->getId(), $idCTT, $idCT, $idCD, $producteur->getId(), $producteurs)), $crudDeclarationDetail);
            } else {
                $form = $this->createForm(new CrudDeclarationDetailType(array($crud, $collecteur->getId(), $idCTT, $idCT, $idCD, null, $producteurs)), $crudDeclarationDetail);
            }
        }

        $message = null;
        $erreurProducteur = null;
        $erreurOuvrageFiliere = null;
        $erreurQuantiteReel = null;
        $erreurMontAide = null;
        $maj = '';

        if ($crud != 'R') {
            if ($request->getMethod() == 'POST') {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $message = null;
                    $producteur = $repoOuvrage->getOuvrageById($crudDeclarationDetail->getProducteur());
                    if (!$producteur->getCp()) {
                        $message = $message . "Le code postal du producteur " . $producteur->getSiret() . " doit être renseigné. \n";
                        $declarationDetail->setMessage($message);
//$emDec->persist($declarationDetail);
//$emDec->flush();
                        $constraint = new True(array(
                            'message' => 'Le code postal du producteur ' . $producteur->getSiret() . ' doit être renseigné'
                        ));
                        $erreurProducteur = $this->get('validator')->validateValue(false, $constraint);
                    }
                    $repoOuvrageFiliere = $emDec->getRepository('AeagDecBundle:OuvrageFiliere');
                    $ouvrageFiliere = $repoOuvrageFiliere->getOuvrageFiliereByOuvrageFiliere($crudDeclarationDetail->getCentreTraitement()->getId(), $crudDeclarationDetail->getTraitFiliere()->getCode(), $declarationCollecteur->getAnnee());
                    if (!$ouvrageFiliere) {
                        $message = $message . "Le code D/R " . $crudDeclarationDetail->getTraitFiliere()->getCode() . " est non conventionnée pour le centre de traitement " . $crudDeclarationDetail->getCentreTraitement()->getNumero() . ". \n";
                        $declarationDetail->setMessage($message);
//$emDec->persist($declarationDetail);
//$emDec->flush();
                        $constraint = new True(array(
                            'message' => 'Le code D/R est non conventionnée pour le centre de traitement'
                        ));
                        $erreurOuvrageFiliere = $this->get('validator')->validateValue(false, $constraint);
                    }
                    if (count($erreurProducteur) == 0 and count($erreurOuvrageFiliere) == 0) {

                        $statut = $repoStatut->getStatutByCode('20');
                        if ($crud == 'U') {
                            $declarationProducteur = $repoDeclarationProducteur->getDeclarationProducteurByProducteurAnnee($producteur->getId(), $declarationCollecteur->getAnnee());
                        } else {
                            $declarationProducteur = $repoDeclarationProducteur->getDeclarationProducteurByProducteurAnnee($crudDeclarationDetail->getProducteur(), $declarationCollecteur->getAnnee());
                        }
                        if (!$declarationProducteur) {
                            $declarationProducteur = new DeclarationProducteur();
                            $declarationProducteur->setProducteur($crudDeclarationDetail->getProducteur());
                            $declarationProducteur->setStatut($statut);
                            $declarationProducteur->setAnnee($declarationCollecteur->getAnnee());
                            $declarationProducteur->setQuantiteReel(0);
                            $declarationProducteur->setMontReel(0);
                            $declarationProducteur->setQuantiteRet(0);
                            $declarationProducteur->setMontRet(0);
                            $declarationProducteur->setQuantiteAide(0);
                            $declarationProducteur->setMontAide(0);
                            $emDec->persist($declarationProducteur);
                            $emDec->flush();
                        }

// controle quantite déclaré par le producteur durand l'annee
                        if ($crud == 'C') {
                            $producteurNonPlafonne = null;
                            $quantiteAnnuelleProducteur = ($declarationProducteur->getQuantiteRet() + $crudDeclarationDetail->getQuantiteReel());
                        } else {
                            $producteurNonPlafonne = $repoProducteurNonPlafonne->getProducteurNonPlafonneBySiret($producteur->getSiret());
                            if ($ancDeclarationDetail->getQuantiteRet() == 0) {
                                $quantiteAnnuelleProducteur = ($declarationProducteur->getQuantiteRet() + $crudDeclarationDetail->getQuantiteReel() - $ancDeclarationDetail->getQuantiteReel());
                            } else {
                                $quantiteAnnuelleProducteur = ($declarationProducteur->getQuantiteRet() + $crudDeclarationDetail->getQuantiteReel() - $ancDeclarationDetail->getQuantiteRet());
                            }
                        }
                        $tauxValeur = $repoTaux->getTauxByAnneeCode($declarationCollecteur->getAnnee(), 'MAXTONNAGE');
                        $tauxMaxTonnage = ($tauxValeur->getValeur() * 1000);
                        $quantiteRet = 0;
// controle quantite déclaré par le producteur durand l'annee
                        if (!$crudDeclarationDetail->getQuantiteReel()) {
                            $crudDeclarationDetail->setQuantiteReel(0);
                        }
                        $quantiteReel = $crudDeclarationDetail->getQuantiteReel();
                        if (!$producteurNonPlafonne) {
                            if ($quantiteAnnuelleProducteur > $tauxMaxTonnage) {
                                if ($quantiteReel == 0) {
                                    $quantiteRet = 0;
                                } else {
                                    if ($ancDeclarationDetail) {
                                        $quantiteRet = ($tauxMaxTonnage - $declarationProducteur->getQuantiteRet()) + $ancDeclarationDetail->getQuantiteRet();
                                    } else {
                                        $quantiteRet = ($tauxMaxTonnage - $declarationProducteur->getQuantiteRet());
                                    }
                                }
                                $message = $message . "La quantité actuelle du producteur = " . $quantiteAnnuelleProducteur . " kg dépasse la quantité autorisée = " . $tauxMaxTonnage . " kg.";
                                $message = $message . " La quantité pesée devrait être égale à " . $quantiteRet . " \n";
                                $declarationDetail->setMessage($message);
//$emDec->persist($declarationDetail);
//$emDec->flush();
                                $crudDeclarationDetail->setQuantiteRet($quantiteRet);
                                $crudDeclarationDetail->setQuantiteAide($quantiteRet);
                                $form = $this->createForm(new CrudDeclarationDetailType(array($crud, $collecteur->getId(), $idCTT, $idCT, $idCD, $producteur->getId(), $producteurs)), $crudDeclarationDetail);
                                $constraint = new True(array(
                                    'message' => 'La quantité pesée ne peut pas dépasser ' . $quantiteRet . ' kg pour ce producteur'
                                ));
                                $erreurQuantiteReel = $this->get('validator')->validateValue(false, $constraint);
                            } else {
                                $quantiteRet = $quantiteReel;
                                $crudDeclarationDetail->setQuantiteRet($quantiteRet);
                                $crudDeclarationDetail->setQuantiteAide($quantiteRet);
                            }
                        } else {
                            $quantiteRet = $quantiteReel;
                            $crudDeclarationDetail->setQuantiteRet($quantiteRet);
                            $crudDeclarationDetail->setQuantiteAide($quantiteRet);
                        }

                        if (count($erreurProducteur) == 0 and count($erreurQuantiteReel) == 0) {

                            $producteurTauxSpecial = $repoProducteurTauxSpecial->getProducteurTauxSpecialBySiret($producteur->getSiret());
                            if ($producteurTauxSpecial) {
                                $tauxAide = $producteurTauxSpecial->getTaux() / 100;
                                $bonnifie = true;
                            } else {
                                $tauxAideAgence = $repoTaux->getTauxByAnneeCode($declarationCollecteur->getAnnee(), 'TAUXAIDE');
                                $tauxAide = $tauxAideAgence->getValeur();
                                $bonnifie = false;
                            }

                            if ($crudDeclarationDetail->getFiliereAide()) {
                                /* if ($crudDeclarationDetail->getFiliereAide()->getCode() == '51') {
                                  $montReel = round(($crudDeclarationDetail->getCoutFacture() * $tauxAide->getValeur()), 2);
                                  $montRet = round(($crudDeclarationDetail->getCoutFacture() * $tauxAide->getValeur()), 2);
                                  $montAide = round(($crudDeclarationDetail->getCoutFacture() * $tauxAide->getValeur()), 2);
                                  } else { */
                                $montReel = round((($crudDeclarationDetail->getQuantiteReel()) * $crudDeclarationDetail->getCoutFacture()), 2);
                                $montRet = round((($crudDeclarationDetail->getQuantiteRet()) * $crudDeclarationDetail->getCoutFacture()), 2);
                                $montAide = (($crudDeclarationDetail->getQuantiteRet()) * $crudDeclarationDetail->getCoutFacture() * $tauxAide);
                                //print_R('1 montant avant round : ' . $montAide);
                                //$montAide = round($montAide, 4);
                                $montAide = round($montAide, 2);
                                // print_R('1 montant apres round : ' . $montAide);
                                // return new response (' ici');
//}
                            } else {
                                $montAide = round((($crudDeclarationDetail->getQuantiteRet()) * $crudDeclarationDetail->getCoutFacture() * $tauxAide), 3);
                                //print_R('2 montant avant round : ' . $montAide);
                                //$montAide = round($montAide, 4);
                                $montAide = round($montAide, 2);
                                //print_R('2 montant apres round : ' . $montAide);
                                //return new response ('LA');
                            }
                            if ($montAide <> $crudDeclarationDetail->getMontAide()) {
                                $message = $message . "Le montant de l'aide(" . $crudDeclarationDetail->getMontAide() . ") devrait être égal à " . $montAide . ". \n";
                                $declarationDetail->setMessage($message);
//$emDec->persist($declarationDetail);
//$emDec->flush();
                                $form = $this->createForm(new CrudDeclarationDetailType(array($crud, $collecteur->getId(), $idCTT, $idCT, $idCD, $producteur->getId(), $producteurs)), $crudDeclarationDetail);
                                $constraint = new True(array(
                                    'message' => 'Le montant de l\'aide(' . $crudDeclarationDetail->getMontAide() . ') devrait être égal à ' . $montAide
                                ));
                                $erreurMontAide = $this->get('validator')->validateValue(false, $constraint);
                            }
                            $montantApDispo = ($sousDeclarationCollecteur->getMontantAp() - $crudDeclarationDetail->getMontAide());
                            if ($montantApDispo <= 0) {
                                $declarationDetail->setMontantApDispo(0);
                                $message = $message . "L'enveloppe budgétaire (" . $sousDeclarationCollecteur->getMontantAp() . ") accordée par l'agence est atteinte pour l'annee " . $sousDeclarationCollecteur->getDeclarationCollecteur()->getAnnee() . ".\n";
                                $declarationDetail->setMessage($message);
                                $form = $this->createForm(new CrudDeclarationDetailType(array($crud, $collecteur->getId(), $idCTT, $idCT, $idCD, $producteur->getId(), $producteurs)), $crudDeclarationDetail);
                                $constraint = new True(array(
                                    'message' => 'Le montant de l\'aide ne peut être retenu par l\'agence de l\'eau pour l\'année ' . $sousDeclarationCollecteur->getDeclarationCollecteur()->getAnnee()
                                ));
                                $erreurMontAide = $this->get('validator')->validateValue(false, $constraint);
                            }
                            $crudDeclarationDetail->setMontRet($montRet);
                            $crudDeclarationDetail->setMontReel($montReel);
//$crudDeclarationDetail->setMontAide($montAide);
                            if (count($erreurMontAide) == 0) {
                                $declarationDetail->setMessage(null);
                                $declarationDetail->setSousDeclarationCollecteur($sousDeclarationCollecteur);
                                $declarationDetail->setDeclarationProducteur($declarationProducteur);
                                $statut = $repoStatut->getStatutByCode('10');
                                $declarationDetail->setStatut($statut);
                                if ($crudDeclarationDetail->getCentreTraitement()) {
                                    $declarationDetail->setCentreTraitement($crudDeclarationDetail->getCentreTraitement()->getId());
                                }
                                if ($crudDeclarationDetail->getCentreTransit()) {
                                    $declarationDetail->setCentreTransit($crudDeclarationDetail->getCentreTransit()->getId());
                                }
                                if ($crudDeclarationDetail->getCentreDepot()) {
                                    $declarationDetail->setCentreDepot($crudDeclarationDetail->getCentreDepot()->getId());
                                } else {
                                    $declarationDetail->setCentreDepot(null);
                                }
                                $declarationDetail->setDechet($crudDeclarationDetail->getDechet());
                                $filiere = $repoFiliere->getFiliereByCode($crudDeclarationDetail->getFiliereAide()->getCode());
                                $declarationDetail->setFiliere($filiere);
                                $declarationDetail->setTraitFiliere($crudDeclarationDetail->getTraitFiliere());
                                $declarationDetail->setNaf($crudDeclarationDetail->getNaf());
                                $declarationDetail->setNature($crudDeclarationDetail->getNature());
                                $declarationDetail->setDateFacture($crudDeclarationDetail->getDateFacture());
                                $declarationDetail->setNumFacture($crudDeclarationDetail->getNumFacture());
                                $declarationDetail->setCoutFacture($crudDeclarationDetail->getCoutFacture());
                                $declarationDetail->setQuantiteReel($crudDeclarationDetail->getQuantiteReel());
                                $declarationDetail->setMontReel($crudDeclarationDetail->getMontReel());
                                $declarationDetail->setQuantiteRet($crudDeclarationDetail->getQuantiteRet());
                                $declarationDetail->setMontRet($crudDeclarationDetail->getMontRet());
                                $declarationDetail->setQuantiteAide($crudDeclarationDetail->getQuantiteAide());
                                $declarationDetail->setMontAide($crudDeclarationDetail->getMontAide());
                                $declarationDetail->setTauxAide($tauxAide);
                                $declarationDetail->setBonnifie($bonnifie);
                                $declarationDetail->setDossierAide($sousDeclarationCollecteur->getDossierAide());
                                $declarationDetail->setMontantAp($sousDeclarationCollecteur->getMontantAp());
                                $declarationDetail->setMontantApDispo($sousDeclarationCollecteur->getMontantAp() - $crudDeclarationDetail->getMontAide());
                                if ($declarationDetail->getMontantApDispo() <= 0) {
                                    $declarationDetail->setMontantApDispo(0);
                                    $message = $message . "L'enveloppe budgétaire (" . $declarationDetail->getMontantAp() . ") accordée par l'agence est atteinte pour l'annee " . $sousDeclarationCollecteur->getDeclarationCollecteur()->getAnnee() . ".\n";
                                }
                                $declarationDetail->setMessage($message);
                                $emDec->persist($declarationDetail);


                                $emDec->persist($declarationProducteur);
                                $emDec->flush();

                                $ok = $this->majStatutDeclarationCollecteursAction($declarationCollecteur->getId(), $user, $emDec, $session);
                                $ok = $this->majStatutDeclarationProducteursAction($declarationProducteur->getId(), $user, $emDec, $session);

                                $producteur = $repoOuvrage->getOuvrageById($declarationProducteur->getProducteur());

                                if ($crud == 'C') {
                                    $session->getFlashBag()->add('notice-success', "La ligne " . $producteur->getLibelle() . " a été ajouté à la déclaration.");
                                } else {
                                    $session->getFlashBag()->add('notice-success', "La ligne " . $producteur->getLibelle() . " de la déclaration a été modifié.");
                                }
                                $maj = 'ok';
                                return $this->redirect($this->generateUrl('AeagDecBundle_collecteur_listeDeclarationDetails', array('sousDeclarationCollecteur_id' => $sousDeclarationCollecteur->getId())));
                            }
                        }
                    }
                }
            }
        }

        return $this->render('AeagDecBundle:Collecteur:crudDeclarationDetail.html.twig', array(
                    'collecteur' => $collecteur,
                    'producteur' => $producteur,
                    'producteurs' => $producteurs,
                    'declarationCollecteur' => $declarationCollecteur,
                    'sousDeclarationCollecteur' => $sousDeclarationCollecteur,
                    'declarationDetail' => $declarationDetail,
                    'form' => $form->createView(),
                    'erreurOuvrageFiliere' => $erreurOuvrageFiliere,
                    'erreurQuantiteReel' => $erreurQuantiteReel,
                    'erreurMontAide' => $erreurMontAide,
                    'tauxAide' => $tauxAide,
                    'crud' => $crud,
                    'maj' => $maj
        ));
    }

    public static function majStatutDeclarationCollecteursAction($declarationCollecteur_id = null, $user, $emDec, $session) {

        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $declaration = $repoDeclarationCollecteur->getDeclarationCollecteurById($declarationCollecteur_id);

        $totQuantiteReel = 0;
        $totMontReel = 0;
        $totQuantiteRet = 0;
        $totMontRet = 0;
        $totQuantiteAide = 0;
        $totMontAide = 0;

        $statut = $repoStatut->getStatutByCode('60');
        $sousDeclarations = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteur($declaration->getId());
        if (!$sousDeclarations) {
            $statut = $repoStatut->getStatutByCode('10');
        } else {
            $totQuantiteReel = 0;
            $totMontReel = 0;
            $totQuantiteRet = 0;
            $totMontRet = 0;
            $totQuantiteAide = 0;
            $totMontAide = 0;
            $montDispo = $declaration->getMontantAp();
            foreach ($sousDeclarations as $sousDeclaration) {
                $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsBySousDeclarationCollecteur($sousDeclaration->getId());
                $quantiteReel = 0;
                $montReel = 0;
                $quantiteRet = 0;
                $montRet = 0;
                $quantiteAide = 0;
                $montAide = 0;
                $statut = $repoStatut->getStatutByCode('10');
                foreach ($declarationDetails as $declarationDetail) {
//                    print_r('statut : ' . $statut->getCode());
//                    print_r(' decl : ' . $declarationDetail->getStatut()->getCode());
                    if ($statut->getCode() < $declarationDetail->getStatut()->getCode()) {
                        $statut = $declarationDetail->getStatut();
                    }
                    if ($declarationDetail->getStatut()->getCode() != '11') {
                        $quantiteReel += $declarationDetail->getQuantiteReel();
                        $montReel += $declarationDetail->getMontreel();
                        $quantiteRet += $declarationDetail->getQuantiteRet();
                        $montRet += $declarationDetail->getMontret();
                        $quantiteAide += $declarationDetail->getQuantiteAide();
                        $montAide += $declarationDetail->getMontAide();
                    }
                }
                if ($statut->getCode() == '10') {
                    $statut = $repoStatut->getStatutByCode('20');
                } elseif ($statut->getCode() == '11') {
                    $statut = $repoStatut->getStatutByCode('21');
                }
                $sousDeclaration->setStatut($statut);
                $sousDeclaration->setQuantiteRet($quantiteRet);
                $sousDeclaration->setMontRet($montRet);
                $sousDeclaration->setQuantiteReel($quantiteReel);
                $sousDeclaration->setMontReel($montReel);
                $sousDeclaration->setQuantiteAide($quantiteAide);
                $sousDeclaration->setMontAide($montAide);
                $sousDeclaration->setMontantAp($declaration->getMontantAp());
                $sousDeclaration->setMontantApDispo($montDispo - $montAide);
                $emDec->persist($sousDeclaration);
                $montDispo -= $montAide;
                $totQuantiteReel += $quantiteReel;
                $totMontReel += $montReel;
                $totQuantiteRet += $quantiteRet;
                $totMontRet += $montRet;
                $totQuantiteAide += $quantiteAide;
                $totMontAide += $montAide;
            }
        }
        $declaration->setStatut($statut);
        $declaration->setQuantiteRet($totQuantiteRet);
        $declaration->setMontRet($totMontRet);
        $declaration->setQuantiteReel($totQuantiteReel);
        $declaration->setMontReel($totMontReel);
        $declaration->setQuantiteAide($totQuantiteAide);
        $declaration->setMontAide($totMontAide);
        $declaration->setMontantApDispo($declaration->getMontantAp() - $totMontAide);
        $emDec->persist($declaration);
        $emDec->flush();
        //return 'ok';
        return $declaration->getMontantApDispo();
    }

    public static function majStatutDeclarationProducteursAction($declarationProducteur_id = null, $user, $emDec, $session) {

        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoDeclarationProducteur = $emDec->getRepository('AeagDecBundle:DeclarationProducteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $declarationProducteur = $repoDeclarationProducteur->getDeclarationProducteurById($declarationProducteur_id);

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
        $emDec->flush();
        return $declarationProducteur->getId() . 'mis a jour statut : ' . $statut->getCode() . '\n ';
    }

    public static function majStatutSousDeclarationCollecteursAction($sousDeclarationCollecteur_id = null, $user, $emDec, $session) {

        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');

        $sousDeclaration = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurById($sousDeclarationCollecteur_id);
        $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsBySousDeclarationCollecteur($sousDeclaration->getid());
        $statut = $repoStatut->getStatutByCode('10');
        foreach ($declarationDetails as $declarationDetail) {
            if ($statut->getCode() < $declarationDetail->getStatut()->getCode()) {
                $statut = $declarationDetail->getStatut();
            }
        }
        if ($statut->getCode() == '10') {
            $statut = $repoStatut->getStatutByCode('20');
        } elseif ($statut->getCode() == '11') {
            $statut = $repoStatut->getStatutByCode('21');
        }

        $sousDeclaration->setStatut($statut);
//$emDec->flush();
        return 'ok';
    }

    public static function majCompteursAction($declarationCollecteur_id = null, $user, $emDec, $session) {

        $repoStatut = $emDec->getRepository('AeagDecBundle:Statut');
        $repoDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:DeclarationCollecteur');
        $repoSousDeclarationCollecteur = $emDec->getRepository('AeagDecBundle:SousDeclarationCollecteur');
        $repoDeclarationDetail = $emDec->getRepository('AeagDecBundle:DeclarationDetail');
        $declaration = $repoDeclarationCollecteur->getDeclarationCollecteurById($declarationCollecteur_id);
        $statut = $repoStatut->getStatutByCode('20');
        $sousDeclarations = $repoSousDeclarationCollecteur->getSousDeclarationCollecteurByDeclarationCollecteur($declaration->getId());
        $totQuantiteReel = 0;
        $totMontReel = 0;
        $totQuantiteRet = 0;
        $totMontRet = 0;
        $totQuantiteAide = 0;
        $totMontAide = 0;
        $montDispo = $declaration->getMontantAp();
        foreach ($sousDeclarations as $sousDeclaration) {
            $declarationDetails = $repoDeclarationDetail->getDeclarationDetailsBySousDeclarationCollecteur($sousDeclaration->getid());
            $quantiteReel = 0;
            $montReel = 0;
            $quantiteRet = 0;
            $montRet = 0;
            $quantiteAide = 0;
            $montAide = 0;
            foreach ($declarationDetails as $declarationDetail) {
                if ($declarationDetail->getStatut()->getCode() == '10')
                    $quantiteReel += $declarationDetail->getQuantiteReel();
                $montReel += $declarationDetail->getMontreel();
                $quantiteRet += $declarationDetail->getQuantiteRet();
                $montRet += $declarationDetail->getMontret();
                $quantiteAide += $declarationDetail->getQuantiteAide();
                $montAide += $declarationDetail->getMontAide();
            }

            $sousDeclaration->setQuantiteRet($quantiteRet);
            $sousDeclaration->setMontRet($montRet);
            $sousDeclaration->setQuantiteReel($quantiteReel);
            $sousDeclaration->setMontReel($montReel);
            $sousDeclaration->setQuantiteAide($quantiteAide);
            $sousDeclaration->setMontAide($montAide);
            $sousDeclaration->setMontantAp($declaration->getMontantAp());
            $sousDeclaration->setMontantApDispo($montDispo - $montAide);
            $emDec->persist($sousDeclaration);
            $montDispo -= $montAide;
            $totQuantiteReel += $quantiteReel;
            $totMontReel += $montReel;
            $totQuantiteRet += $quantiteRet;
            $totMontRet += $montRet;
            $totQuantiteAide += $quantiteAide;
            $totMontAide += $montAide;
        }
        $declaration->setQuantiteRet($totQuantiteRet);
        $declaration->setMontRet($totMontRet);
        $declaration->setQuantiteReel($totQuantiteReel);
        $declaration->setMontReel($totMontReel);
        $declaration->setQuantiteAide($totQuantiteAide);
        $declaration->setMontAide($totMontAide);
        $declaration->setMontantApDispo($declaration->getMontantAp() - $totMontAide);
        $emDec->persist($declaration);
        $emDec->flush();
        return 'ok';
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

    static function tri_producteurs($a, $b) {
        if ($a->getSiret() == $b->getSiret())
            return 0;
        return ($a->getSiret() < $b->getSiret()) ? -1 : 1;
    }

}
