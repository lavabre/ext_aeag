<?php

namespace Aeag\SqeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AeagCommand extends ContainerAwareCommand {

    protected $emSqe;
    protected $em;
    protected $output;
    protected $input;
    protected $repoPgCmdFichiersRps;
    protected $repoPgProgPhases;
    protected $repoPgTmpValidEdilabo;
    protected $repoPgLogValidEdilabo;
    protected $repoPgCmdDemande;
    protected $repoPgRefCorresPresta;
    protected $repoPgProgLotLqParam;
    protected $repoPgProgUnitesPossiblesParam;
    protected $repoPgSandreFractions;
    protected $repoPgCmdPrelev;
    protected $repoPgCmdPrelevPc;
    protected $repoPgProgWebUsers;
    protected $repoPgSandreParametres;
    protected $repoPgSandreUnites;
    protected $repoPgSandreZoneVerticaleProspectee;

    protected function configure() {
        $this
                ->setName('rai:aeag')
                ->setDescription('Classe parente des autres commandes. A ne pas utiliser directement!!')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {

        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->emSqe = $this->getContainer()->get('doctrine')->getManager('sqe');

        $this->output = $output;
        $this->input = $input;

        $this->repoPgCmdFichiersRps = $this->emSqe->getRepository('AeagSqeBundle:PgCmdFichiersRps');
        $this->repoPgCmdDemande = $this->emSqe->getRepository('AeagSqeBundle:PgCmdDemande');
        $this->repoPgProgPhases = $this->emSqe->getRepository('AeagSqeBundle:PgProgPhases');
        $this->repoPgTmpValidEdilabo = $this->emSqe->getRepository('AeagSqeBundle:PgTmpValidEdilabo');
        $this->repoPgLogValidEdilabo = $this->emSqe->getRepository('AeagSqeBundle:PgLogValidEdilabo');
        $this->repoPgRefCorresPresta = $this->emSqe->getRepository('AeagSqeBundle:PgRefCorresPresta');
        $this->repoPgProgLotLqParam = $this->emSqe->getRepository('AeagSqeBundle:PgProgLotLqParam');
        $this->repoPgProgUnitesPossiblesParam = $this->emSqe->getRepository('AeagSqeBundle:PgProgUnitesPossiblesParam');
        $this->repoPgSandreFractions = $this->emSqe->getRepository('AeagSqeBundle:PgSandreFractions');
        $this->repoPgCmdPrelev = $this->emSqe->getRepository('AeagSqeBundle:PgCmdPrelev');
        $this->repoPgCmdPrelevPc = $this->emSqe->getRepository('AeagSqeBundle:PgCmdPrelevPc');
        $this->repoPgProgWebUsers = $this->emSqe->getRepository('AeagSqeBundle:PgProgWebUsers');
        $this->repoPgSandreParametres = $this->emSqe->getRepository('AeagSqeBundle:PgSandreParametres');
        $this->repoPgSandreUnites = $this->emSqe->getRepository('AeagSqeBundle:PgSandreUnites');
        $this->repoPgSandreZoneVerticaleProspectee = $this->emSqe->getRepository('AeagSqeBundle:PgSandreZoneVerticaleProspectee');
        $this->repoPgProgLotParamAn = $this->emSqe->getRepository('AeagSqeBundle:PgProgLotParamAn');
    }

    protected function _envoiMessage($txtMessage, $destinataire, $objet, $pgCmdFichierRps, $expediteur = 'automate@eau-adour-garonne.fr') {
        $htmlMessage = "<html><head></head><body>";
        $htmlMessage .= "Bonjour, <br/><br/>";
        $htmlMessage .= $txtMessage;
        $htmlMessage .= "<br/><br/>Cordialement, <br/>L'équipe SQE";
        $htmlMessage .= "</body></html>";
        try {
            // Récupération du service
            $mailer = $this->getContainer()->get('mailer');
            // Création de l'e-mail : le service mailer utilise SwiftMailer, donc nous créons une instance de Swift_Message.
            $mail = \Swift_Message::newInstance('Wonderful Subject')
                    ->setSubject($objet)
                    ->setFrom($expediteur)
                    ->setTo($destinataire->getMail())
                    ->setBody($htmlMessage, 'text/html');

            $mailer->send($mail);

            $this->em->flush();
        } catch (\Swift_TransportException $ex) {
            $this->_addLog('warning', $pgCmdFichierRps->getDemande()->getId(), $pgCmdFichierRps->getId(), "Erreur lors de l\'envoi de mail dans le process de verification des RAIs", null, $ex->getMessage());
        }
    }

    protected function _updatePhase($pgCmdFichierRps, $phase) {
        $pgProgPhases = $this->repoPgProgPhases->findOneByCodePhase($phase);
        $pgCmdFichierRps->setPhaseFichier($pgProgPhases);
        $this->emSqe->persist($pgCmdFichierRps);

        $pgProgSuiviPhases = new \Aeag\SqeBundle\Entity\PgProgSuiviPhases;
        $pgProgSuiviPhases->setTypeObjet('RPS');
        $pgProgSuiviPhases->setObjId($pgCmdFichierRps->getId());
        $pgProgSuiviPhases->setDatePhase(new \DateTime());
        $pgProgSuiviPhases->setPhase($pgProgPhases);
        $this->emSqe->persist($pgProgSuiviPhases);

        $this->emSqe->flush();
    }
    
    protected function _addLog($typeErreur, $demandeId, $fichierRpsId, $message, $codePrelevement = null, $commentaire = null) {
        $dateLog = new \DateTime();
        if (!is_null($commentaire) && is_array($commentaire)) {
            $commentaire = $this->_convertMultiArray($commentaire);
        }
        $pgLogValidEdilabo = new \Aeag\SqeBundle\Entity\PgLogValidEdilabo($demandeId, $fichierRpsId, $typeErreur, $message, $dateLog, $codePrelevement, $commentaire);

        $this->emSqe->persist($pgLogValidEdilabo);
        $this->emSqe->flush();
    }

}
