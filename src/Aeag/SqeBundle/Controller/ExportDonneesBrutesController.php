<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;

class ExportDonneesBrutesController extends Controller {

    public function indexAction(Request $request) {

        $user = $this->getUser();
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $session = $this->get('session');
        $session->set('menu', 'donnees');
        $session->set('controller', 'ExportDonneesBrutes');
        $session->set('fonction', 'index');

        $env = $this->get('kernel')->getEnvironment();
        $rootPath = $this->get('kernel')->getRootDir();
        
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgTypeMilieu = $emSqe->getRepository('AeagSqeBundle:PgProgTypeMilieu');
        
        // Récupération des zones géo en fonction de l'utilisateur
        $pgProgWebusers = $repoPgProgWebusers->findOneByExtId($user->getId());
        // Récupération des codes milieux
        $pgProgTypesMilieux = $repoPgProgTypeMilieu->getPgProgTypesMilieuxByCodeMilieu('PC');

        if (!is_null($request->get('zgeorefs')) && !is_null($request->get('codemilieu')) && !is_null($request->get('datedeb')) && !is_null($request->get('datefin'))) {
            // Récupération des valeurs du formulaire
            $zgeorefs = $request->get('zgeorefs');
            $codemilieu = $request->get('codemilieu');
            $datedeb = $request->get('datedeb');
            $datefin = $request->get('datefin');

            // Lancement du traitement en ligne de commande
            $zgeorefs = implode(',', $zgeorefs);
            $commandLine = 'php '.$rootPath.'/console_process_rai rai:export_db -e '. $env . ' ' . $zgeorefs . ' ' . $codemilieu . ' ' . $datedeb . ' ' . $datefin . ' ' . $user->getId().' > /tmp/vgu.log';

            $process = new Process($commandLine);

            $process->start(function ($type, $buffer) {
                if ('err' === $type) {
                    echo 'ERR > ' . $buffer;
                } else {
                    echo 'OUT > ' . $buffer;
                }
            });

            $session->getFlashBag()->add('notice-success', 'L\'export des données brutes est en cours. Vous recevrez un email lorsque celui ci sera terminé.');
        } else {
            $zgeorefs = '';
            $codemilieu = '';
            $date = new \DateTime();
            $datedeb = '01/01/' . $date->format('Y');
            $datefin = $date->format('d/m/Y');
        }

        return $this->render('AeagSqeBundle:ExportDonneesBrutes:index.html.twig', array('webUser' => $pgProgWebusers, 'typesMilieux' => $pgProgTypesMilieux, 'zgeorefVals' => $zgeorefs, 'codemilieuVal' => $codemilieu, 'datedeb' => $datedeb, 'datefin' => $datefin));
    }
    
    public function zoneGeoAction(Request $request) {
        $emSqe = $this->get('doctrine')->getManager('sqe');
        $codemilieu = $request->get('codemilieu');
        $user = $request->get('user');
        
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');
        $repoPgProgTypeMilieu = $emSqe->getRepository('AeagSqeBundle:PgProgTypeMilieu');
        
        $pgProgWebuser = $repoPgProgWebusers->findOneById($user);
        $pgProgTypeMilieu = $repoPgProgTypeMilieu->findOneByCodeMilieu($codemilieu);
        
        $zgeoRefs = $pgProgWebuser->getZgeoRef();
        
        $zgeoRefsOk = array();
        foreach($zgeoRefs as $zgeoRef) {
            if ($zgeoRef->getCodeMilieu()->indexOf($pgProgTypeMilieu) !== FALSE) {
                $zgeoRefsOk[$zgeoRef->getId()] = $zgeoRef->getNomZoneGeo();
            }
        }

        echo json_encode($zgeoRefsOk);
        exit();
        
    }
    
    public function telechargerAction($nomFichier) {
        $chemin = $this->getParameter('repertoire_exportdb');
        
        header('Content-Type', 'application/zip');
        header('Content-disposition: attachment; filename="' . $nomFichier . '"');
        header('Content-Length: ' . filesize($chemin . $nomFichier));
        readfile($chemin . $nomFichier);
        exit();
        
    }

}
