<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdFichiersRps
 *
 * @ORM\Table(name="pg_cmd_fichiers_rps", indexes={@ORM\Index(name="IDX_C08360FA80E95E18", columns={"demande_id"}), @ORM\Index(name="IDX_C08360FA9834F876", columns={"presta_usr_id"}), @ORM\Index(name="IDX_C08360FAF5092A8D", columns={"phase_fic_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdFichiersRpsRepository")
 */
class PgCmdFichiersRps {
     
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_cmd_fichiers_rps_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
    
    /**
     * @var \PgCmdDemande
     *
     * @ORM\ManyToOne(targetEntity="PgCmdDemande")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="demande_id", referencedColumnName="id")
     * })
     */
    private $demande;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nom_fichier", type="string", length=255, nullable=true)
     */
    private $nomFichier;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_depot", type="datetime", nullable=true)
     */
    private $dateDepot;
    
    /**
     * @var string
     *
     * @ORM\Column(name="type_fichier", type="string", length=3, nullable=false)
     */
    private $typeFichier;
    
    /**
     * @var \PgProgPhases
     *
     * @ORM\ManyToOne(targetEntity="PgProgPhases")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="phase_fic_id", referencedColumnName="id")
     * })
     */
    private $phaseFichier;
    
    /**
     * @var \PgProgWebusers
     *
     * @ORM\ManyToOne(targetEntity="PgProgWebusers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="presta_usr_id", referencedColumnName="id")
     * })
     */
    private $user;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nom_fic_don_brutes", type="string", length=255, nullable=true)
     */
    private $nomFichierDonneesBrutes;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nom_fic_cr", type="string", length=255, nullable=true)
     */
    private $nomFichierCompteRendu;
    
    /**
     * @var string
     *
     * @ORM\Column(name="lien_acquit_sandre", type="string", length=255, nullable=true)
     */
    private $lienAcquitSandre;
    
    /**
     * @var string
     *
     * @ORM\Column(name="lien_certif_sandre", type="string", length=255, nullable=true)
     */
    private $lienCertifSandre;
    
    /**
     * @var string
     *
     * @ORM\Column(name="suppr", type="string", length=1, nullable=true)
     */
    private $suppr;
    
    function getId() {
        return $this->id;
    }

    function getDemande() {
        return $this->demande;
    }

    function getNomFichier() {
        return $this->nomFichier;
    }

    function getDateDepot() {
        return $this->dateDepot;
    }

    function getTypeFichier() {
        return $this->typeFichier;
    }

    function getPhaseFichier() {
        return $this->phaseFichier;
    }

    function getUser() {
        return $this->user;
    }

    function getNomFichierDonneesBrutes() {
        return $this->nomFichierDonneesBrutes;
    }

    function getNomFichierCompteRendu() {
        return $this->nomFichierCompteRendu;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setDemande(\Aeag\SqeBundle\Entity\PgCmdDemande $demande) {
        $this->demande = $demande;
    }

    function setNomFichier($nomFichier) {
        $this->nomFichier = $nomFichier;
    }

    function setDateDepot(\DateTime $dateDepot) {
        $this->dateDepot = $dateDepot;
    }

    function setTypeFichier($typeFichier) {
        $this->typeFichier = $typeFichier;
    }

    function setPhaseFichier(\Aeag\SqeBundle\Entity\PgProgPhases $phaseFichier) {
        $this->phaseFichier = $phaseFichier;
    }

    function setUser(\Aeag\SqeBundle\Entity\PgProgWebusers $user) {
        $this->user = $user;
    }

    function setNomFichierDonneesBrutes($nomFichierDonneesBrutes) {
        $this->nomFichierDonneesBrutes = $nomFichierDonneesBrutes;
    }

    function setNomFichierCompteRendu($nomFichierCompteRendu) {
        $this->nomFichierCompteRendu = $nomFichierCompteRendu;
    }
    
    function getSuppr() {
        return $this->suppr;
    }

    function setSuppr($suppr) {
        $this->suppr = $suppr;
    }
    
    function getLienAcquitSandre() {
        return $this->lienAcquitSandre;
    }

    function getLienCertifSandre() {
        return $this->lienCertifSandre;
    }

    function setLienAcquitSandre($lienAcquitSandre) {
        $this->lienAcquitSandre = $lienAcquitSandre;
    }

    function setLienCertifSandre($lienCertifSandre) {
        $this->lienCertifSandre = $lienCertifSandre;
    }



}
