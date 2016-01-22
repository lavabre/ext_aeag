<?php
namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdDwnldUsrDmd
 *
 * @ORM\Table(name="pg_cmd_dwnld_usr_dmd")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdDwnldUsrDmdRepository")
 */
class PgCmdDwnldUsrDmd {
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_cmd_dwnld_usr_dmd_id_seq", allocationSize=1, initialValue=1)
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
     * @var \PgCmdDemande
     *
     * @ORM\ManyToOne(targetEntity="PgCmdDemande")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="demande_id", referencedColumnName="id")
     * })
     */
    private $demande;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_download", type="datetime", nullable=false)
     */
    private $date;
    
    function getId() {
        return $this->id;
    }

    function getUser() {
        return $this->user;
    }

    function getDemande() {
        return $this->demande;
    }

    function getDate() {
        return $this->date;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setUser(\Aeag\SqeBundle\Entity\PgProgWebusers $user) {
        $this->user = $user;
    }

    function setDemande(\Aeag\SqeBundle\Entity\PgCmdDemande $demande) {
        $this->demande = $demande;
    }

    function setDate(\DateTime $date) {
        $this->date = $date;
    }


    
}
