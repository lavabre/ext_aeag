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

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementHydrobioGlobal();


//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 

        return $this->render('AeagSqeBundle:Programmation:Avancement\hydroGlobal.html.twig', array(
                    'tableau' => $tableau,
        ));
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

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementHydrobioSupport();

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

        $repoPgProgMarche = $emSqe->getRepository('AeagSqeBundle:PgProgMarche');

        $tableau = $repoPgProgMarche->getAvancementHydrobioLot();

        return $this->render('AeagSqeBundle:Programmation:Avancement\hydroLot.html.twig', array(
                    'tableau' => $tableau));
    }

    public function analyseIndexAction() {

        $user = $this->getUser();
        if (!$user) {
            return $this->render('AeagSqeBundle:Default:interdit.html.twig');
        }
        $session = $this->get('session');
        $session->set('menu', 'programmation');
        $session->set('controller', 'ProgrammationAvancement');
        $session->set('fonction', 'analyseIndex');

        return $this->render('AeagSqeBundle:Programmation:Avancement\analyseIndex.html.twig');
    }

    public function analyseGlobalAction() {

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

        $tableau = $repoPgProgMarche->getAvancementAnalyseGlobal();


//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 

        return $this->render('AeagSqeBundle:Programmation:Avancement\analyseGlobal.html.twig', array(
                    'tableau' => $tableau,
        ));
    }

    public function analysePeriodeAction() {

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

        $tableau = $repoPgProgMarche->getAvancementAnalysePeriode();


//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 

        return $this->render('AeagSqeBundle:Programmation:Avancement\analysePeriode.html.twig', array(
                    'tableau' => $tableau,
        ));
    }
    
    public function analyseLotAction() {

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

        $tableau = $repoPgProgMarche->getAvancementAnalyseLot();


//          \Symfony\Component\VarDumper\VarDumper::dump($tableau);
//          return new Response (''); 

        return $this->render('AeagSqeBundle:Programmation:Avancement\analyseLot.html.twig', array(
                    'tableau' => $tableau,
        ));
    }

}
