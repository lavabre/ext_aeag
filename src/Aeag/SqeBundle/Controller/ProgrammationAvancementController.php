<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Aeag\SqeBundle\Entity\PgProgLotPeriodeAn;
use Aeag\SqeBundle\Entity\PgProgLotPeriodeProg;
use Aeag\SqeBundle\Entity\PgProgSuiviPhases;
use Aeag\AeagBundle\Controller\AeagController;

class ProgrammationAvancementController extends Controller {

    public function hydroIndexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'hydroIndex');
           
        return $this->render('AeagSqeBundle:Programmation:Avancement\hydroIndex.html.twig');
    }

    public function hydroGlobalAction() {

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
        
        $repoPgProgMarche =$emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = array();
        $tableau= $repoPgProgMarche->getAvancementGlobal1();
        //$tableau1 = $repoPgProgMarche->getAvancementGlobal();
        $tableau1 = array();
       
        $tabMarche = array();
        $j = 0;
        for ($i = 0; $i < count($tableau); $i++){
            $trouve = false;
            for ($j = 0; $j < count($tabMarche);$j++){
                if ($tabMarche[$j] == $tableau[$i]['typeMarche']){
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve){
                $j = count($tabMarche);
                $tabMarche[$j] = $tableau[$i]['typeMarche'];
              }
        }
        
        $tabStatut = array();
        $j = 0;
        for ($i = 0; $i < count($tableau); $i++){
            $trouve = false;
            for ($j=0; $j < count($tabStatut);$j++){
                if ($tabStatut[$j]['code'] == $tableau[$i]['statutPrel']){
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve){
                $j = count($tabStatut) ;
                $tabStatut[$j]['code'] = $tableau[$i]['statutPrel'];
                if ($tableau[$i]['statutPrel'] == 'F'){
                    $tabStatut[$j]['libelle'] = 'Effectué sans FT';
                }elseif ($tableau[$i]['statutPrel'] == 'D'){
                    $tabStatut[$j]['libelle'] = 'Effectué avec FT';
                }elseif ($tableau[$i]['statutPrel'] == 'N'){
                    $tabStatut[$j]['libelle'] = 'Non effectué';
                }elseif ($tableau[$i]['statutPrel'] == 'P'){
                    $tabStatut[$j]['libelle'] = 'Prévisionnel';
                }elseif ($tableau[$i]['statutPrel'] == 'R'){
                    $tabStatut[$j]['libelle'] = 'Reporté';
                }else{
                    $tabStatut[$j]['libelle'] = 'Aucun suivi';
                }
            }
        }
        
        $tableau3 = array();
        $l = 0;
        for ($i = 0; $i < count($tabStatut); $i++){
            for($j = 0; $j < count($tabMarche); $j++){
                for ($k = 0; $k < count($tableau); $k++){
                     if ($tabStatut[$i]['code'] == $tableau[$k]['statutPrel']){
                               $tableau3[$l]['statut'] = $tabStatut[$i]['libelle'];
                              $tableau3[$l]['marche'] = $tabMarche[$j];
                              $tableau3[$l][$j]['nbPrel'] = $tableau[$k]['nb_prel'];
                              $l++;
                      }
                }
            }
        }
        
        $tableau2 = $repoPgProgMarche->getAvancementGlobal2();
       
       
//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 
        
        return $this->render('AeagSqeBundle:Programmation:Avancement\hydroGlobal.html.twig', array(
                                      'tableau' => $tableau,
                                      'tableau2' => $tableau2,
                                     'tableau3' => $tableau3,
                                      'marches' => $tabMarche,
                                      'statuts' => $tabStatut));
    }
    
     public function hydroSupportAction() {

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

        $tableau = array();
        
        return $this->render('AeagSqeBundle:Programmation:Avancement\hydroSupport.html.twig', array(
                                      'tableau' => $tableau));
    }
    
      public function hydroLotAction() {

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

        $tableau = array();
        
        return $this->render('AeagSqeBundle:Programmation:Avancement\hydroLot.html.twig', array(
                                      'tableau' => $tableau));
    }

  
}
