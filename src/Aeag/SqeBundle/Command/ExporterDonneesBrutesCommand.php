<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Console\Input\InputArgument;

class ExporterDonneesBrutesCommand extends AeagCommand {
    
    protected function configure() {
        $this
                ->setName('rai:export_db')
                ->setDescription('Export des données brutes')
                ->addArgument('zgeorefs',
                InputArgument::REQUIRED,
                        'zgeorefs')
                ->addArgument('codemilieu',
                InputArgument::REQUIRED,
                        'codemilieu')
                ->addArgument('datedeb',
                InputArgument::REQUIRED,
                        'datedeb')
                ->addArgument('datefin',
                InputArgument::REQUIRED,
                        'datefin')
                ->addArgument('user',
                InputArgument::REQUIRED,
                        'user')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        parent::execute($input, $output);

        // Récupération des parametres
        // Zone géo, code milieu, date début, date fin, utilisateur
        $zgeorefs = $this->input->getArgument('zgeorefs');
        $codemilieu = $this->input->getArgument('codemilieu');
        $datedeb = $this->input->getArgument('datedeb');
        $datefin = $this->input->getArgument('datefin');
        $user = $this->input->getArgument('user');
        
        $zgeorefs = explode(',', $zgeorefs);
        
        $pgProgZoneGeoRefs = array();
        foreach($zgeorefs as $zgeoref) {
            $pgProgZoneGeoRefs[] = $this->repoPgProgZoneGeoRef->findOneById($zgeoref);
        }

        $pgProgTypeMilieu = $this->repoPgProgTypeMilieu->findOneByCodeMilieu($codemilieu);
        $donneesBrutes = $this->repoPgCmdPrelev->getDonneesBrutesExport($zgeorefs, $codemilieu, $datedeb, $datefin);
        
        $pathBase = $this->getContainer()->getParameter('repertoire_exportdb');
        $nomFichierCsv = $codemilieu.'_'.$zgeoref.'_'.str_replace('/', '', $datedeb).'_'.str_replace('/', '', $datefin).'_'.time().'.csv';
        $fullFileName = $pathBase.$nomFichierCsv;
        // Construction du fichier csv
        $this->getContainer()->get('aeag_sqe.process_rai')->createFileDonneesBrutes($fullFileName, $donneesBrutes);
        // Rajouter un fichier pdf et générer une archive zip au lieu d'un csv
        $nomFichierPdf = $this->getContainer()->getParameter('repertoire_echange').'AvertissementDonneesBrutes.pdf';
        $files = array($nomFichierCsv => $fullFileName, 'AvertissementDonneesBrutes.pdf' => $nomFichierPdf);
        $nomArchive = str_replace('.csv', '.zip', $nomFichierCsv);
        $fullNomArchive = $pathBase.$nomArchive;
        if ($this->createZip($files, $fullNomArchive)) {
            // Suppression du fichier csv
            unlink($fullFileName);
            
            // Envoi du mail
            $tabZgeoRefs = array();
            foreach($pgProgZoneGeoRefs as $pgProgZoneGeoRef) {
                $tabZgeoRefs[] = $pgProgZoneGeoRef->getNomZoneGeo();
            }
            $strZgeoRefs = implode(',', $tabZgeoRefs);
            $destinataire = $this->repoPgProgWebUsers->findOneByExtId($user);
            $url = $this->getContainer()->get('router')->generate('AeagSqeBundle_exportdonneesbrutes_telecharger', array("nomFichier" => $nomArchive), UrlGeneratorInterface::ABSOLUTE_URL);
            $objetMessage = "SQE - RAI : Fichier csv de l'export des données brutes disponible ";
            if ($this->getEnv() !== 'prod') {
                $objetMessage .= " - ".$this->getEnv();
            }            
            $txtMessage = "Le fichier csv de l'export des données brutes est disponible. <br/><br/>";
            $txtMessage .= "Zone géographique : " . $strZgeoRefs . "<br/>";
            $txtMessage .= "Code Milieu : " . $pgProgTypeMilieu->getNomMilieu()  . "<br/>";
            $txtMessage .= "Date début : " . $datedeb . "<br/>";
            $txtMessage .= "Date fin : " . $datefin . "<br/><br/>";
            $txtMessage .= 'Vous pouvez récupérer le fichier csv à l\'adresse suivante: <a href="'.$url.'">'.$nomArchive.'</a><br/>';
            $mailer = $this->getContainer()->get('mailer');
            $this->getContainer()->get('aeag_sqe.message')->createMail($this->em, $mailer, $txtMessage, $destinataire, $objetMessage);
        }
         
        
        
    }
    
    protected function createZip($files = array(),$destination = '',$overwrite = false) {
	//if the zip file already exists and overwrite is false, return false
	if(file_exists($destination) && !$overwrite) { 
            return false; 
        }
        
	//vars
	$validFiles = array();
	//if files were passed in...
	if(is_array($files)) {
		//cycle through each file
		foreach($files as $fileName => $file) {
			//make sure the file exists
			if(file_exists($file)) {
				$validFiles[$fileName] = $file;
			}
		}
	}
	//if we have good files...
	if(count($validFiles)) {
		//create the archive
		$zip = new \ZipArchive();
		if($zip->open($destination,$overwrite ? \ZipArchive::OVERWRITE : \ZipArchive::CREATE) !== true) {
			return false;
		}
		//add the files
		foreach($validFiles as $fileName => $file) {
			$zip->addFile($file,$fileName);
		}
		//debug
		//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
		
		//close the zip -- done!
		$zip->close();
		
		//check to make sure the file exists
		return file_exists($destination);
	}
	else
	{
		return false;
	}
}
}
