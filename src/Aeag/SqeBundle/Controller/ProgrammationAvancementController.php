<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Aeag\SqeBundle\Entity\PgProgLotPeriodeAn;
use Aeag\SqeBundle\Entity\PgProgLotPeriodeProg;
use Aeag\SqeBundle\Entity\PgProgSuiviPhases;
use Aeag\AeagBundle\Controller\AeagController;
use Aeag\SqeBundle\Entity\PgCmdDwnldUsrRps;

class ProgrammationAvancementController extends Controller {

    public function hydroIndexAction($anneeProg) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'hydroIndex');

        return $this->render('AeagSqeBundle:Programmation:Avancement\hydroIndex.html.twig', array(
                    'anneeProg' => $anneeProg
        ));
    }

    public function hydroGlobalAction($anneeProg) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'hydroGlobal');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementHydrobioGlobal($anneeProg);


//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 

        return $this->render('AeagSqeBundle:Programmation:Avancement\hydroGlobal.html.twig', array(
                    'anneeProg' => $anneeProg,
                    'tableau' => $tableau,
        ));
    }

    public function hydroSupportAction($anneeProg) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'hydroSupport');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementHydrobioSupport($anneeProg);

        return $this->render('AeagSqeBundle:Programmation:Avancement\hydroSupport.html.twig', array(
                    'anneeProg' => $anneeProg,
                    'tableau' => $tableau));
    }

    public function hydroLotAction($anneeProg) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'hydroLot');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementHydrobioLot($anneeProg);

        return $this->render('AeagSqeBundle:Programmation:Avancement\hydroLot.html.twig', array(
                    'anneeProg' => $anneeProg,
                    'tableau' => $tableau));
    }

    public function hydroStationAction($anneeProg) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'hydroStation');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementHydrobioStation($anneeProg);

        return $this->render('AeagSqeBundle:Programmation:Avancement\hydroStation.html.twig', array(
                    'anneeProg' => $anneeProg,
                    'tableau' => $tableau));
    }

    public function analyseIndexAction($anneeProg) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'analyseIndex');

        return $this->render('AeagSqeBundle:Programmation:Avancement\analyseIndex.html.twig', array(
                    'anneeProg' => $anneeProg
        ));
    }

    public function analyseGlobalAction($anneeProg) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'analyseGlobal');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementAnalyseGlobal($anneeProg);

//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 

        return $this->render('AeagSqeBundle:Programmation:Avancement\analyseGlobal.html.twig', array(
                    'anneeProg' => $anneeProg,
                    'tableau' => $tableau,
        ));
    }

    public function analysePeriodeAction($anneeProg) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'analysePeriode');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementAnalysePeriode($anneeProg);


//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 

        return $this->render('AeagSqeBundle:Programmation:Avancement\analysePeriode.html.twig', array(
                    'anneeProg' => $anneeProg,
                    'tableau' => $tableau,
        ));
    }

    public function analyseLotAction($anneeProg) {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'analyseLotPrestataire');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementAnalyseLot($anneeProg);


//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 

        return $this->render('AeagSqeBundle:Programmation:Avancement\analyseLot.html.twig', array(
                    'anneeProg' => $anneeProg,
                    'tableau' => $tableau,
        ));
    }

    public function prelevementIndexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'analyseIndex');

        return $this->render('AeagSqeBundle:Programmation:Avancement\prelevementIndex.html.twig');
    }

    public function prelevementGlobalAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'analyseGlobal');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementPrelevementGlobal();


//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 

        return $this->render('AeagSqeBundle:Programmation:Avancement\prelevementGlobal.html.twig', array(
                    'tableau' => $tableau,
        ));
    }

    public function prelevementTypeMilieuAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'analysePeriode');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementPrelevementTypeMilieu();


//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 

        return $this->render('AeagSqeBundle:Programmation:Avancement\prelevementTypeMilieu.html.twig', array(
                     'tableau' => $tableau,
        ));
    }

    public function prelevementTypeMarcheAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'analyseLotPrestataire');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementPrelevementTypeMarche();


//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 

        return $this->render('AeagSqeBundle:Programmation:Avancement\prelevementTypeMarche.html.twig', array(
                    'tableau' => $tableau,
        ));
    }

    public function programmationIndexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationIndex');
        $session->set('fonction', 'analyseIndex');

        return $this->render('AeagSqeBundle:Programmation:Avancement\programmationIndex.html.twig');
    }

    public function programmationGlobalAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'programmationtGlobal');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $anneeProgs = $repoPgProgLotAn->getPgProgLotAnDistinctAnnee();

        $tab = array();
        $tableau = array();
        $i = 0;
        foreach ($anneeProgs as $anneeProg) {
            $tab[$i] = $repoPgProgMarche->getAvancementProgrammationGlobal($anneeProg["anneeProg"]);
            $i++;
        }

        $tableau = $tab;


//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 

        return $this->render('AeagSqeBundle:Programmation:Avancement\programmationGlobal.html.twig', array(
                    'tableau' => $tableau,
        ));
    }

    public function programmationMilieuAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationMilieu');
        $session->set('fonction', 'analysePeriode');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementProgrammationMilieu();


//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 

        return $this->render('AeagSqeBundle:Programmation:Avancement\programmationMilieu.html.twig', array(
                    'tableau' => $tableau,
        ));
    }

    public function programmationMarcheAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationMarche');
        $session->set('fonction', 'analyseLotPrestataire');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementProgrammationMarche();


//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 

        return $this->render('AeagSqeBundle:Programmation:Avancement\programmationMarche.html.twig', array(
                    'tableau' => $tableau,
        ));
    }

    public function unzipAction($suiviPrelId = null) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'unzip');
        $em = $this->get('doctrine')->getManager();
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->getPgCmdSuiviPrelById($suiviPrelId);
        $pgCmdPrelev = $pgCmdSuiviPrel->getPrelev();
        $pgCmdFichiersRps = $pgCmdSuiviPrel->getFichierRps();
        $pathBase = $this->getCheminEchange($pgCmdSuiviPrel);
        $fichierZip = $pgCmdFichiersRps->getNomFichier();
        $ext = strtolower(pathinfo($fichierZip, PATHINFO_EXTENSION));

        $pgCmdDwnldUsrRps = new PgCmdDwnldUsrRps();
        $pgCmdDwnldUsrRps->setUser($pgProgWebUser);
        $pgCmdDwnldUsrRps->setFichierReponse($pgCmdFichiersRps);
        $pgCmdDwnldUsrRps->setDate(new \DateTime());
        $pgCmdDwnldUsrRps->setTypeFichier($pgCmdFichiersRps->getTypeFichier());
        $emSqe->persist($pgCmdDwnldUsrRps);
        $emSqe->flush();

        $chemin = $pathBase . '/' . $fichierZip;
        //print_r('chemin : ' . $chemin);
        $fichiers = $this->unzip($chemin, $pathBase . '/');
        $tabFichiers = array();
        $i = 0;
        foreach ($fichiers as $fichier) {
            $ext = strtolower(pathinfo($fichier, PATHINFO_EXTENSION));
            if ($ext) {
                $tabFichiers[$i]['fichier'] = $fichier;
                $tabFichiers[$i]['chemin'] = $pathBase . '/' . $fichier;
                chmod($pathBase . '/' . $fichier, 0775);
                chown($pathBase . '/' . $fichier, 'www-data');
                $repertoire = "fichiers";
                rename($pathBase . '/' . $fichier, $repertoire . "/" . $fichier);
                $i++;
            }
        }
        return $this->render('AeagSqeBundle:Programmation:Avancement\unzip.html.twig', array(
                    'suiviPrel' => $pgCmdSuiviPrel,
                    'repertoire' => $pathBase,
                    'fichier' => $fichierZip,
                    'chemin' => $chemin,
                    'fichiers' => $tabFichiers));
    }

    protected function getCheminEchange($pgCmdSuiviPrel) {
        $chemin = $this->container->getParameter('repertoire_echange');
        $chemin .= $pgCmdSuiviPrel->getPrelev()->getDemande()->getAnneeProg() . '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getCommanditaire()->getNomCorres();
        $chemin .= '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getLot()->getId() . '/' . $pgCmdSuiviPrel->getPrelev()->getDemande()->getLotan()->getId();
        $chemin .= '/SUIVI/' . $pgCmdSuiviPrel->getPrelev()->getId() . '/' . $pgCmdSuiviPrel->getId();

        return $chemin;
    }

    protected function unzip($file, $path = '', $effacer_zip = false) {/* Méthode qui permet de décompresser un fichier zip $file dans un répertoire de destination $path
      et qui retourne un tableau contenant la liste des fichiers extraits
      Si $effacer_zip est égal à true, on efface le fichier zip d'origine $file */

        $tab_liste_fichiers = array(); //Initialisation

        $zip = zip_open($file);

        if ($zip) {
            while ($zip_entry = zip_read($zip)) { //Pour chaque fichier contenu dans le fichier zip
                if (zip_entry_filesize($zip_entry) >= 0) {
                    $complete_path = $path . dirname(zip_entry_name($zip_entry));

                    /* On supprime les éventuels caractères spéciaux et majuscules */
                    $nom_fichier = zip_entry_name($zip_entry);
                    $nom_fichier = strtr($nom_fichier, "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ", "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn");
                    $nom_fichier = strtolower($nom_fichier);
                    $nom_fichier = ereg_replace('[^a-zA-Z0-9.]', '-', $nom_fichier);

                    /* On ajoute le nom du fichier dans le tableau */
                    array_push($tab_liste_fichiers, $nom_fichier);

                    $complete_name = $path . $nom_fichier; //Nom et chemin de destination

                    if (!file_exists($complete_path)) {
                        $tmp = '';
                        foreach (explode('/', $complete_path) AS $k) {
                            $tmp .= $k . '/';

                            if (!file_exists($tmp)) {
                                mkdir($tmp, 0755);
                            }
                        }
                    }

                    /* On extrait le fichier */
                    if (zip_entry_open($zip, $zip_entry, "r")) {
                        $fd = fopen($complete_name, 'w');

                        fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));

                        fclose($fd);
                        zip_entry_close($zip_entry);
                    }
                }
            }

            zip_close($zip);

            /* On efface éventuellement le fichier zip d'origine */
            if ($effacer_zip === true)
                unlink($file);
        }

        return $tab_liste_fichiers;
    }

}
