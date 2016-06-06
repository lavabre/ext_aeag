<?php

namespace Aeag\FrdBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Aeag\FrdBundle\DependencyInjection\PDF;
use Aeag\AeagBundle\Entity\Message;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Controller\AeagController;

class AdminController extends Controller {

    public function indexAction() {

        $session = $this->get('session');
        $session->set('menu', 'admin');
        $user = $this->getUser();
        return $this->redirect($this->generateUrl('AeagFrdBundle_admin_consulterFraisDeplacementsParAnnee', array('anneeSelect' => date_format($session->get('annee'),'Y'))));
    }

    public function validerFraisDeplacementAction() {


        $id = $_POST['id'];
        $datePhase = $_POST['datePhase'];

        $user = $this->getUser();

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoPhase = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:Phase');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoDept = $em->getRepository('AeagAeagBundle:Departement');
        $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');

        $entity = $repoFraisDeplacement->getFraisDeplacementById($id);

        if (!$entity) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement .');
        }

        $phase = $repoPhase->getPhaseByCode('30');
        $entity->setPhase($phase);
        $now = new \DateTime($datePhase);
        $entity->setDatePhase($now);
        $entity->setDateCourrier($now);
        $entity->setValider('O');
        $entity->setExporter('N');
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

        if ($entity[1]->getEmail()) {
            $this->sendAccuseReceptionCourrier($entity[0]->getId());
        }
        
        $notification = new Notification();
        $notification->setRecepteur($membre->getId());
        $notification->setEmetteur($user->getId());
        $notification->setNouveau(true);
        $notification->setIteration(2);
        $notification->setMessage('Les pièces justificatives de votre demande n° ' . $entity[0]->getId() . ' ont été enregistrées à l\'agence de l\'eau.');
        $em->persist($notification);
        $em->flush();
        $mes = AeagController::notificationAction($user, $em, $session);
        
        $message = 'Courrier reçu le ' . $datePhase . ' pour les frais de déplacement n° ' . $entity[0]->getId();
        $message = $message . ' du ' . $entity[0]->getDateDepart()->format('d/m/Y') . ' ' . $entity[0]->getHeureDepart();
        $message = $message . ' au ' . $entity[0]->getDateRetour()->format('d/m/Y') . ' ' . $entity[0]->getHeureRetour();
        $this->get('session')->getFlashBag()->add('notice-success', $message);

        return $this->render('AeagFrdBundle:Admin:afficherLigneFraisDeplacement.html.twig', array(
                    'entity' => $entity
        ));
    }

    public function devaliderFraisDeplacementAction($id) {

        $user = $this->getUser();

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');

        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');
        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoPhase = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:Phase');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoDept = $em->getRepository('AeagAeagBundle:Departement');
        $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMINFRD')) {
            $entity = $repoFraisDeplacement->getFraisDeplacementById($id);
        } else {
            $entity = $repoFraisDeplacement->getFraisDeplacementByIdUser($id, $user->getId());
        }

        if (!$entity) {
            throw $this->createNotFoundException('Impossible de retouver l\'enregistrement .');
        }

        $phase = $repoPhase->getPhaseByCode($entity->getPhase()->getCode());
        $phaseAvant = $phase->getLibelle();
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
            $entity->setDateCourrier(null);
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
        
        $notification = new Notification();
        $notification->setRecepteur($membre->getId());
        $notification->setEmetteur($user->getId());
        $notification->setNouveau(true);
        $notification->setIteration(2);
        $notification->setMessage('Les frais de déplacement n° ' . $entity[0]->getId() . ' sont repassés à l\'état : ' . $phase->getLibelle() . '.');
        $em->persist($notification);
        $em->flush();
        $mes = AeagController::notificationAction($user, $em, $session);
       
        $message = 'Les frais de déplacement n° ' . $entity[0]->getId();
        $message = $message . ' du ' . $entity[0]->getDateDepart()->format('d/m/Y') . ' ' . $entity[0]->getHeureDepart();
        $message = $message . ' au ' . $entity[0]->getDateRetour()->format('d/m/Y') . ' ' . $entity[0]->getHeureRetour();
        $message = $message . ' sont passés de l\'état : ' . $phaseAvant . ' à l\'état :  ' . $phase->getlibelle() . '.';
        $this->get('session')->getFlashBag()->add('notice-success', $message);


        return $this->render('AeagFrdBundle:Admin:afficherLigneFraisDeplacement.html.twig', array(
                    'entity' => $entity
        ));
    }

    public function exporterListeFraisDeplacementsAction(Request $request) {

        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $emFrd = $this->getDoctrine()->getManager('frd');
        $repoFraisDeplacement = $emFrd->getRepository('AeagFrdBundle:FraisDeplacement');
        $repoParametres = $emFrd->getRepository('AeagFrdBundle:Parametre');
        $repoPhase = $this->getDoctrine()->getManager('frd')->getRepository('AeagFrdBundle:Phase');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoDept = $em->getRepository('AeagAeagBundle:Departement');


        $annee = $repoParametres->findOneBy(array('code' => 'ANNEE'));
        $annee = new \DateTime($annee->getLibelle());
        $annee1 = $annee->format('Y');
        $annee2 = $annee1 - 1 . '-01-01';
        $annee = new \DateTime($annee2);
        $message = null;

        $fraisDeplacements = $repoFraisDeplacement->getFraisDeplacementaExporter($annee);
        $i = 0;
        $entities = array();
        foreach ($fraisDeplacements as $fraisDeplacement) {
            $entities[$i][0] = $fraisDeplacement;
            $membre = $repoUsers->getUserById($fraisDeplacement->getUser());
            $entities[$i][1] = $membre;
            $i++;
        }

        $destinataires = array();
        $frais = array();
        $i = 0;
        $j = 1;

          if ($request->getMethod() == 'POST') {

            $parametre = $repoParametres->findOneBy(array('code' => 'REP_IMPORT'));
            $rep_import = $parametre->getLibelle();
            $date_import = date('Ymd_His');
            $nom_fichier = "frd_import_" . $date_import . ".csv";
            $fic_import = $rep_import . "/" . $nom_fichier;
            $message = "";
            $ok = "ko";
            $fic = fopen($fic_import, "w");

            // entete des lignes
            /* $contenu = "ID|COR_ID|OBJET|DATE_DEPART|HEURE_DEPART|DATE_RETOUR|HEURE_RETOUR|";
              $contenu = $contenu . "TYPMISS_CODE|FINALITE_CODE|SOUS_THEME_CODE|ITINERAIRE|";
              $contenu = $contenu . "DEPT|KM_VP|KM_MOTO|AEROPORT|";
              $contenu = $contenu . "ADMIN_MIDI_SEM|ADMIN_MIDI_WE|ADMIN_SOIR|";
              $contenu = $contenu . "AUTRE_MIDI_SEM|AUTRE_MIDI_WE|AUTRE_SOIR|";
              $contenu = $contenu . "OFFERT_MIDI_SEM|OFFERT_MIDI_WE|OFFERT_SOIR|";
              $contenu = $contenu . "PROVINCE_JUSTIF|PROVINCE_NON_JUSTIF|PARIS_JUSTIF|";
              $contenu = $contenu . "PARIS_NON_JUSTIF|OFFERT_NUIT|ADMIN_NUIT|";
              $contenu = $contenu . "JUSTIF_PARK|NON_JUSTIF_PARK|TOTAL_PARK|";
              $contenu = $contenu . "JUSTIF_PEAGE|NON_JUSTIF_PEAGE|TOTAL_PEAGE|";
              $contenu = $contenu . "JUSTIF_BUS_METRO|NON_JUSTIF_BUS_METRO|TOTAL_BUS_METRO|";
              $contenu = $contenu . "JUSTIF_ORLYVAL|NON_JUSTIF_ORLYVAL|TOTAL_ORLYVAL|";
              $contenu = $contenu . "JUSTIF_TRAIN|NON_JUSTIF_TRAIN|TOTAL_TRAIN|CLASSE_TRAIN|COUCH_TRAIN|";
              $contenu = $contenu . "JUSTIF_AVION|NON_JUSTIF_AVION|TOTAL_AVION|";
              $contenu = $contenu . "JUSTIF_LOCA|NON_JUSTIF_LOCA|TOTAL_LOCA|";
              $contenu = $contenu . "JUSTIF_TAXI|NON_JUSTIF_TAXI|TOTAL_TAXI";
              $contenu = $contenu . "\n";

              fputs($fic, $contenu);
             */

            foreach ($entities as $entity) {
                if ($_POST["tout"] == "ok" or (isset($_POST['exp-' . $entity[0]->getId()]))) {
                    $dept = $repoDept->getDepartementByDept($entity[0]->getdepartement());
                    $contenu = $entity[0]->getId() . ';';
                    $contenu = $contenu . $entity[1]->getCorrespondant() . ';';
                    $contenu = $contenu . $this->wd_remove_pointVirgule($entity[0]->getObjet()) . ';';
                    $contenu = $contenu . $entity[0]->getDateDepart()->format('d/m/Y') . ';';
                    $contenu = $contenu . $entity[0]->getHeureDepart() . ';';
                    $contenu = $contenu . $entity[0]->getDateRetour()->format('d/m/Y') . ';';
                    $contenu = $contenu . $entity[0]->getHeureRetour() . ';';
                    $contenu = $contenu . 'MISS;';
                    $contenu = $contenu . $entity[0]->getFinalite()->getCode() . ';';
                    if ($entity[0]->getSousTheme()) {
                        $contenu = $contenu . $entity[0]->getSousTheme()->getCode() . ';';
                    } else {
                        $contenu = $contenu . ';';
                    }
                    $contenu = $contenu . $this->wd_remove_pointVirgule($entity[0]->getItineraire()) . ';';
                    $contenu = $contenu . $dept->getDept() . ';';
                    $contenu = $contenu . $entity[0]->getKmVoiture() . ';';
                    $contenu = $contenu . $entity[0]->getKmMoto() . ';';
                    $contenu = $contenu . $entity[0]->getAeroport() . ';';
                    $contenu = $contenu . $entity[0]->getAdmiMidiSem() . ';';
                    $contenu = $contenu . $entity[0]->getAdmiMidiWeek() . ';';
                    $contenu = $contenu . $entity[0]->getAdmiSoir() . ';';
                    $contenu = $contenu . $entity[0]->getAutreMidiSem() . ';';
                    $contenu = $contenu . $entity[0]->getAutreMidiWeek() . ';';
                    $contenu = $contenu . $entity[0]->getAutreSoir() . ';';
                    $contenu = $contenu . $entity[0]->getOffertMidiSem() . ';';
                    $contenu = $contenu . $entity[0]->getOffertMidiWeek() . ';';
                    $contenu = $contenu . $entity[0]->getOffertSoir() . ';';
                    $contenu = $contenu . $entity[0]->getProvenceJustif() . ';';
                    $contenu = $contenu . $entity[0]->getProvenceNonJustif() . ';';
                    $contenu = $contenu . $entity[0]->getParisJustif() . ';';
                    $contenu = $contenu . $entity[0]->getParisNonJustif() . ';';
                    $contenu = $contenu . $entity[0]->getoffertNuit() . ';';
                    $contenu = $contenu . $entity[0]->getAdminNuit() . ';';
                    $contenu = $contenu . $entity[0]->getParkJustif() . ';';
                    $contenu = $contenu . $entity[0]->getParkNonJustif() . ';';
                    $contenu = $contenu . $entity[0]->getParkTotal() . ';';
                    $contenu = $contenu . $entity[0]->getPeageJustif() . ';';
                    $contenu = $contenu . $entity[0]->getPeageNonJustif() . ';';
                    $contenu = $contenu . $entity[0]->getPeageTotal() . ';';
                    $contenu = $contenu . $entity[0]->getBusMetroJustif() . ';';
                    $contenu = $contenu . $entity[0]->getBusMetroNonJustif() . ';';
                    $contenu = $contenu . $entity[0]->getBusMetroTotal() . ';';
                    $contenu = $contenu . $entity[0]->getOrlyvalJustif() . ';';
                    $contenu = $contenu . $entity[0]->getOrlyvalNonJustif() . ';';
                    $contenu = $contenu . $entity[0]->getOrlyvalTotal() . ';';
                    $contenu = $contenu . $entity[0]->getTrainJustif() . ';';
                    $contenu = $contenu . $entity[0]->getTrainNonJustif() . ';';
                    $contenu = $contenu . $entity[0]->getTrainTotal() . ';';
                    $contenu = $contenu . $entity[0]->getTrainClasse() . ';';
                    $contenu = $contenu . $entity[0]->getTrainCouchette() . ';';
                    $contenu = $contenu . $entity[0]->getAvionJustif() . ';';
                    $contenu = $contenu . $entity[0]->getAvionNonJustif() . ';';
                    $contenu = $contenu . $entity[0]->getAvionTotal() . ';';
                    $contenu = $contenu . $entity[0]->getReservationJustif() . ';';
                    $contenu = $contenu . $entity[0]->getReservationNonJustif() . ';';
                    $contenu = $contenu . $entity[0]->getReservationTotal() . ';';
                    $contenu = $contenu . $entity[0]->getTaxiJustif() . ';';
                    $contenu = $contenu . $entity[0]->getTaxiNonJustif() . ';';
                    $contenu = $contenu . $entity[0]->getTaxiTotal() . ';';
                    $contenu = $contenu . "\n";
                    fputs($fic, $contenu);

                    $phase = $repoPhase->getPhaseByCode('40');
                    $entity[0]->setPhase($phase);
                    $now = date('Y-m-d');
                    $now = new \DateTime($now);
                    $entity[0]->setDatePhase($now);
                    $entity[0]->setExporter('O');
                    $emFrd->persist($entity[0]);
                    $ok = 'ok';
                    if (count($destinataires) == 0) {
                        $destinataires[$i][0] = $entity[1];
                        $destinataires[$i][1] = $entity[0];
                        $i += 1;
                     } else {
                        if (!in_array($entity[1]->getId(), $destinataires)) {
                            $destinataires[$i][0] = $entity[1];
                            $destinataires[$i][1] = $entity[0];
                            $i += 1;
                         } else {
                            $destinataires[$i][1] = $entity[0];
                            $i += 1;
                         }
                    }
                }
            }
            fclose($fic);
            $emFrd->flush();

            if ($ok == 'ok') {

                $dest = null;
                $frais = array();
                $entities = array();
                $i = 0;
                //var_dump($destinataires);
                foreach ($destinataires as $destinataire) {
                    if (!$dest) {
                        $dest = $destinataire[0];
                        $frais[$i] = $destinataire[1];
                        $i++;
                    }elseif ($dest->getId() <> $destinataire[0]->getId()){
                        $this->sendExporterFraisDeplacement($dest, $frais);
                        $frais = array();
                        $i = 0;
                        $dest = $destinataire[0];
                        $frais[$i] = $destinataire[1];
                        $i++;
                    }else{
                       $frais[$i] = $destinataire[1];
                        $i++; 
                    }
                 }
                $this->sendExporterFraisDeplacement($dest, $frais);
            
                //$message = $this->telechargerFichierAction($nom_fichier, $rep_import);
                $message = $this->Ftp($rep_import, 'Applications/Transfert/Frd/Import/Encours');

                // sauvegardes
                $source = $fic_import;
                $dest = $rep_import . "/Sauvegardes/" . $nom_fichier;
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
            } else {
                $nom_fichier = null;
                $message = null;
            };

            $entities = $repoFraisDeplacement->getFraisDeplacementaExporter($annee);
        }

        return $this->render('AeagFrdBundle:Admin:exporterListeFraisDeplacements.html.twig', array(
                    'entities' => $entities,
                    'message' => $message
        ));
    }

    public function sendAccuseReceptionCourrier($id) {

        $session = $this->get('session');

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
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

        // Récupération du service.
        $mailer = $this->get('mailer');

        // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
        $message = \Swift_Message::newInstance()
                ->setSubject('Courrier reçu pour les frais de déplacement n° ' . $entity[0]->getId())
                ->setFrom('automate@eau-adour-garonne.fr')
                ->setTo($entity[1]->getEmail())
                ->setBody($this->renderView('AeagFrdBundle:Admin:accuseReceptionCourrier.txt.twig', array(
                    'entity' => $entity)));
        // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
        $mailer->send($message);

        $message = 'Le courrier pour les frais de déplacement n° ' . $entity[0]->getId();
        $message = $message . ' du ' . $entity[0]->getDateDepart()->format('d/m/Y') . ' ' . $entity[0]->getHeureDepart();
        $message = $message . ' au ' . $entity[0]->getDateRetour()->format('d/m/Y') . ' ' . $entity[0]->getHeureRetour();
        $message = $message . ' a  été réceptionner à l\agence de l\'eau, vous allez recevoir un mail de notification. ';
        $session->set('notice', $message);
    }

    public function sendExporterFraisDeplacement($dest, $frais) {

        $emFrd = $this->getDoctrine()->getManager('frd');
        $repoParametres = $emFrd->getRepository('AeagFrdBundle:Parametre');
        $parametre = $repoParametres->findOneBy(array('code' => 'REP_IMPORT'));
        $rep_import = $parametre->getLibelle() . "/Pdf";

        $utilisateur = $dest;

        // Récupération du service.
        $mailer = $this->get('mailer');

        // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
        $message = \Swift_Message::newInstance()
                ->setSubject('Demande de remboursement des frais de déplacement transmise à l\'agence de l\'eau "Adour-garonne"')
                ->setFrom('automate@eau-adour-garonne.fr')
                ->setTo($utilisateur->getEmail())
                ->setBody($this->renderView('AeagFrdBundle:Admin:sendExporterFraisDeplacement.txt.twig', array(
                    'utilisateur' => $utilisateur,
                    'frais' => $frais)));

        /*
          foreach ($frais as $entity) {
          $pdf = new PDF('P', 'mm', 'A4');
          $pdf->StartPageGroup();
          $pdf->AddPage($entity);
          $pdf->SetFont('Arial', '', 10);
          $pdf->Formatage($entity);
          $fichier = 'FRD' . '_' . $entity->getUtilisateur()->getUsername();
          $fichier = $fichier . '_' . $entity->getUtilisateur()->getPrenom() . '_' . $entity->getId() . '.pdf';
          $fic_import = $rep_import . "/" . $fichier;
          $pdf->Output($fic_import, 'F');
          $message->attach(\Swift_Attachment::fromPath($fic_import));
          }
         * 
         */

        // Retour au service mailer, nous utilisons sa méthode « send() » pour envoyer notre $message.
        $mailer->send($message);

        // suppression des fichiers pdf envoyés
        /* foreach ($frais as $entity) {
          $fichier = 'FRD' . '_' . $entity->getUtilisateur()->getUsername();
          $fichier = $fichier . '_' . $entity->getUtilisateur()->getPrenom() . '_' . $entity->getId() . '.pdf';
          $fic_import = $rep_import . "/" . $fichier;
          unlink($fic_import);
          } */
    }

    public function telechargerFichierAction($ficent = null, $repertoire = null) {

        $file = $repertoire . "/" . $ficent;

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: no-cache');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            return new response('fichier : ' . $file . ' téléchargé !!!');
        } else {
            return new response('le fichier ' . $file . ' n\existe pas');
        };
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
            if (!is_dir($file) and $file != "Sauvegardes" and $file != "Pdf" and $file != "Encours") {
                $f[] = "$file";
            }
        }
        closedir($handle);
        sort($f);
        $count = 0;
        $mode = FTP_BINARY; // or FTP_ASCII
        $conn_id = ftp_connect($FTP_HOST);
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
        return $mess;
    }

    public static function wd_remove_pointVirgule($str) {


        $str = nl2br(strtr(
                        $str, array(
            ';' => '-',
                ))
        );


        return $str;
    }

}
