<?php
namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgLogValidEdilabo
 *
 * @ORM\Table(name="pg_log_valid_edilabo")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgLogValidEdilaboRepository")
 * 
 */
class PgLogValidEdilabo {
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_log_valid_edilabo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="demande_id", type="decimal", precision=38, scale=0, nullable=true)
     */
    private $demandeId;

    /**
     * @var string
     *
     * @ORM\Column(name="fichier_rps_id", type="decimal", precision=38, scale=0, nullable=true)
     */
    private $fichierRpsId;

    /**
     * @var string
     *
     * @ORM\Column(name="code_prelevement", type="string", length=100, nullable=true)
     */
    private $codePrelevement;
    
     /**
     * @var string
     *
     * @ORM\Column(name="type_erreur", type="string", length=10, nullable=true)
     */
    private $typeErreur;
    
     /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=2000, nullable=true)
     */
    private $message;
    
    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=2000, nullable=true)
     */
    private $commentaire;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_log", type="datetime", nullable=false)
     */
    private $dateLog;
    
    public function __construct($demandeId, $fichierRpsId, $typeErreur, $message, \DateTime $dateLog, $codePrelevement = null, $commentaire = null) {
        $this->demandeId = $demandeId;
        $this->fichierRpsId = $fichierRpsId;
        $this->codePrelevement = $codePrelevement;
        $this->typeErreur = $typeErreur;
        $this->message = $message;
        $this->commentaire = $commentaire;
        $this->dateLog = $dateLog;
    }
    
    public function getId() {
        return $this->id;
    }

    public function getDemandeId() {
        return $this->demandeId;
    }

    public function getFichierRpsId() {
        return $this->fichierRpsId;
    }

    public function getCodePrelevement() {
        return $this->codePrelevement;
    }

    public function getTypeErreur() {
        return $this->typeErreur;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getCommentaire() {
        return $this->commentaire;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setDemandeId($demandeId) {
        $this->demandeId = $demandeId;
    }

    public function setFichierRpsId($fichierRpsId) {
        $this->fichierRpsId = $fichierRpsId;
    }

    public function setCodePrelevement($codePrelevement) {
        $this->codePrelevement = $codePrelevement;
    }

    public function setTypeErreur($typeErreur) {
        $this->typeErreur = $typeErreur;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function setCommentaire($commentaire) {
        $this->commentaire = $commentaire;
    }
    
    public function getDateLog() {
        return $this->dateLog;
    }

    public function setDateLog(\DateTime $dateLog) {
        $this->dateLog = $dateLog;
    }
    
}
