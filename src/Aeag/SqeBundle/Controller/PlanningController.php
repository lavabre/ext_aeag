<?php

namespace Aeag\SqeBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PlanningController extends Controller {

    public function indexAction($typeMilieu) {

        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');

        // Récupération des semaines
        $semaines = array();
        for($i = 1; $i <= 53; $i++) {
            $semaines[] = $i; 
        }
        
        // Récupération des années
        $annees = array();
        $date = new \DateTime();
        $date->sub(new \DateInterval('P4Y'));
        $annee = $date->format('Y');
        for($i = $annee; $i <= ($annee + 6); $i++){
            $annees[] = $i;
        }
        
        // Récupération des stations
        $pgRefStationMesure = $repoPgCmdSuiviPrel->getStationsFromSuiviPrelByCodeMilieu($typeMilieu);

        // Récupération des supports
        $pgSandreSupport = $repoPgCmdSuiviPrel->getSupportsFromSuiviPrelByCodeMilieu($typeMilieu);

        // Récupération des prestataires
        $pgRefCorresPresta = $repoPgCmdSuiviPrel->getPrestatairesFromSuiviPrelByCodeMilieu($typeMilieu);

        // Récupération des type de milieu
        $pgProgTypesMilieu = $repoPgCmdSuiviPrel->getTypesMilieuFromSuiviPrelByCodeMilieu($typeMilieu);

        return $this->render('AeagSqeBundle:Planning:index.html.twig', array('semaines'=> $semaines,'annees'=> $annees, 'stations' => $pgRefStationMesure, 'supports' => $pgSandreSupport, 'prestataires' => $pgRefCorresPresta, 'typesmilieu' => $pgProgTypesMilieu, 'typemilieuVal' => $typeMilieu));
    }

    public function tableAction($typeMilieu) {
        $request = $this->get('request');

        $emSqe = $this->get('doctrine')->getManager('sqe');

        $semaine = $request->get('semaine');
        $annee = $request->get('annee');
        $support = $request->get('support');
        $station = $request->get('station');
        $presta = $request->get('presta');
        $typemilieu = $request->get('typemilieu');

        if ($semaine < 10) {
            $semaine = '0' . $semaine;
        }

        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');

        $joursSemaine = array();
        for ($day = 1; $day <= 7; $day++) {
            $joursSemaine[] = $this->dateFR($annee . "W" . $semaine . $day);
        }

        // Récupération des rdv
        $evenements = array();
        for ($day = 1; $day <= 7; $day++) {
            $date = new \DateTime($annee . "W" . $semaine . $day);
            $evenements[$day] = $repoPgCmdSuiviPrel->getEvenements($date, $support, $station, $presta, $typemilieu);
        }

        return $this->render('AeagSqeBundle:Planning:table.html.twig', array("joursSemaine" => $joursSemaine, "evenements" => $evenements, 'typemilieuVal' => $typeMilieu));
    }

    public function modalAction() {
        $request = $this->get('request');

        $emSqe = $this->get('doctrine')->getManager('sqe');

        $evt = $request->get('evt');

        $repoPgCmdSuiviPrel = $emSqe->getRepository('AeagSqeBundle:PgCmdSuiviPrel');
        $pgCmdSuiviPrel = $repoPgCmdSuiviPrel->findOneById($evt);

        return $this->render('AeagSqeBundle:Planning:modal.html.twig', array('evenement' => $pgCmdSuiviPrel));
    }
    
    protected function dateFR($time) {
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        return strftime("%A %d %B", strtotime($time));
    }

}
