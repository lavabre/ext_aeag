<?php
namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdDwnldUsrRps
 *
 * @ORM\Table(name="pg_cmd_dwnld_usr_rps", indexes={@ORM\Index(name="IDX_62E9BC2AC6451FDC", columns={"fichier_rps_id"}), @ORM\Index(name="IDX_62E9BC2A49279951", columns={"webuser_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdDwnldUsrRpsRepository")
 */
class PgCmdDwnldUsrRps {
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_cmd_dwnld_usr_rps_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
    
    /**
     * @var \PgProgWebusers
     *
     * @ORM\ManyToOne(targetEntity="PgProgWebusers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id")
     * })
     */
    private $user;
    
    /**
     * @var \PgCmdFichiersRps
     *
     * @ORM\ManyToOne(targetEntity="PgCmdFichiersRps")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fichier_rps_id", referencedColumnName="id")
     * })
     */
    private $fichierReponse;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_download", type="datetime", nullable=false)
     */
    private $date;
    
    /**
     * @var string
     *
     * @ORM\Column(name="type_fichier", type="string", length=5, nullable=false)
     */
    private $typeFichier;
    
    function getId() {
        return $this->id;
    }

    function getUser() {
        return $this->user;
    }

    function getFichierReponse() {
        return $this->fichierReponse;
    }

    function getDate() {
        return $this->date;
    }

    function getTypeFichier() {
        return $this->typeFichier;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setUser(\Aeag\SqeBundle\Entity\PgProgWebusers $user) {
        $this->user = $user;
    }

    function setFichierReponse(\Aeag\SqeBundle\Entity\PgCmdFichiersRps $fichierReponse) {
        $this->fichierReponse = $fichierReponse;
    }

    function setDate(\DateTime $date) {
        $this->date = $date;
    }

    function setTypeFichier($typeFichier) {
        $this->typeFichier = $typeFichier;
    }




    
}
