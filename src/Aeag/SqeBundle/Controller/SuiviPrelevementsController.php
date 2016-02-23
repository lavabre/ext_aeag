<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Entity\Message;
use Aeag\AeagBundle\Controller\AeagController;

class SuiviPrelevementsController extends Controller {

    public function indexAction() {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }

        $session = $this->get('session');
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
        $session->set('fonction', 'index');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        // Récupération des programmations
        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $pgProgLotAns = array();
        if ($user->hasRole('ROLE_ADMINSQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByAdmin();
        } else if ($user->hasRole('ROLE_PRESTASQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByPresta($user);
        } else if ($user->hasRole('ROLE_PROGSQE')) {
            $pgProgLotAns = $repoPgProgLotAn->getPgProgLotAnByProg($user);
        }

        return $this->render('AeagSqeBundle:SuiviPrelevements:index.html.twig', array('user' => $user,
                    'lotans' => $pgProgLotAns));
    }

    public function stationsAction($lotanId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
        $session->set('fonction', 'stations');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgProgLotAn = $repoPgProgLotAn->getPgProgLotAnById($lotanId);
        $pgProgLotStationAns = $repoPgProgLotStationAn->getPgProgLotStationAnBylotan($pgProgLotAn);
        $tabStations = array();
        $i = 0;
        foreach ($pgProgLotStationAns as $pgProgLotStationAn){
             $tabStations[$i]['pgProgLotStationAn'] = $pgProgLotStationAn;
             $tabStations[$i]['lien'] = '/sqe_fiches_stations/' . $pgProgLotStationAn->getStation()->getCode() . '.pdf';
        }
        return $this->render('AeagSqeBundle:SuiviPrelevements:stations.html.twig', array('user' => $pgProgWebUser,
                    'lotan' => $pgProgLotAn,
                    'stations' => $tabStations));
    }
    
    public function periodesAction($stationAnId) {
        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'suiviPrelevements');
        $session->set('controller', 'SuiviPrelevements');
        $session->set('fonction', 'periodes');
        $emSqe = $this->get('doctrine')->getManager('sqe');

        $repoPgProgLotAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotAn');
        $repoPgProgLotStationAn = $emSqe->getRepository('AeagSqeBundle:PgProgLotStationAn');
        $repoPgProgLotPeriodeProg = $emSqe->getRepository('AeagSqeBundle:PgProgLotPeriodeProg');
        $repoPgProgWebUsers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');

        $pgProgWebUser = $repoPgProgWebUsers->getPgProgWebusersByExtid($user->getId());
        $pgProgLotStationAn = $repoPgProgLotStationAn->getPgProgLotStationAnById($stationAnId);
        $pgProgLotAn = $pgProgLotStationAn->getLotan();
        $pgProgLotPeriodeProgs = $repoPgProgLotPeriodeProg->getPgProgLotPeriodeProgByStationAn($pgProgLotStationAn);
        $tabPeriodes = array();
        $i = 0;
        foreach ($pgProgLotPeriodeProgs as $pgProgLotPeriodeProg){
                $tabPeriodes[$j]["ordre"] = $pgProgLotPeriodeAn->getPeriode()->getid();
                $tabPeriodes[$j]["periode"] = $pgProgLotPeriodeAn->getPeriode();
        }
        sort($tabPeriodes);
        return $this->render('AeagSqeBundle:SuiviPrelevements:stations.html.twig', array(
                    'user' => $pgProgWebUser,
                    'lotan' => $pgProgLotAn,
                    'stations' => $tabStations));
    }

}
