<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\BufferedOutput;

class ExportDonneesBrutesController extends Controller {
     
    public function indexAction(Request $request) {
        
        $user = $this->getUser();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $session = $this->get('session');
        $session->set('menu', 'echangeFichier');
        $session->set('controller', 'ExportDonneesBrutes');
        $session->set('fonction', 'index');
        
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgTypeMilieu = $emSqe->getRepository('AeagSqeBundle:PgProgTypeMilieu');
        
        // Récupération des zones géo en fonction de l'utilisateur
        $pgProgWebusers = $repoPgProgWebusers->findOneByExtId($user->getId());
        // Récupération des codes milieux
        $pgProgTypesMilieux = $repoPgProgTypeMilieu->findAll();
        
        if (!is_null($request->get('zgeoref')) && !is_null($request->get('codemilieu')) && !is_null($request->get('datedeb')) && !is_null($request->get('datefin'))) {
            // Récupération des valeurs du formulaire
            $zgeoref = $request->get('zgeoref');
            $codemilieu = $request->get('codemilieu');
            $datedeb = $request->get('datedeb');
            $datefin = $request->get('datefin');

            // Lancement du traitement en ligne de commande
            $kernel = $this->get('kernel');
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput(array(
                'command' => 'rai:export_db',
                'zgeoref' => $zgeoref,
                'codemilieu' => $codemilieu,
                'datedeb' => $datedeb,
                'datefin' => $datefin,
                'user' => $user,
            ));
            // You can use NullOutput() if you don't need the output
            $output = new NullOutput();
            //$output = new BufferedOutput();
            $application->run($input, $output);

            //$content = $output->fetch();

            // Affichage d'une alerte
            // n lignes vont être exportées. Un email vous sera envoyé lorsque le fichier sera disponible
            //$session->getFlashBag()->add('notice-success', $content);
            $session->getFlashBag()->add('notice-success', 'L\'export des données brutes est en cours. Vous recevrez un email lorsque celui ci sera terminé.');

        } else {
            $zgeoref = '';
            $codemilieu = '';
            $date = new \DateTime();
            $datedeb = '01/01/'.$date->format('Y');
            $datefin = $date->format('d/m/Y');
        }
        
        return $this->render('AeagSqeBundle:ExportDonneesBrutes:index.html.twig', array('webUser'=> $pgProgWebusers, 'typesMilieux' => $pgProgTypesMilieux, 'zgeorefVal' => $zgeoref, 'codemilieuVal' => $codemilieu, 'datedeb' => $datedeb, 'datefin' => $datefin));    
    }
}
