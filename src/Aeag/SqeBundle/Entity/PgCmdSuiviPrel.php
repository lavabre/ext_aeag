<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdSuiviPrel
 *
 * @ORM\Table(name="pg_cmd_suivi_prel", indexes={@ORM\Index(name="IDX_D8A94DEFD8E1F6AA", columns={"prelev_id"}), @ORM\Index(name="IDX_D8A94DEFC6451FDC", columns={"fichier_rps_id"}), @ORM\Index(name="IDX_D8A94DEFA76ED395", columns={"user_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdSuiviPrelRepository")
 */
class PgCmdSuiviPrel
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_cmd_suivi_prel_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_prel", type="datetime", nullable=true)
     */
    private $datePrel;

    /**
     * @var string
     *
     * @ORM\Column(name="statut_prel", type="string", length=2, nullable=true)
     */
    private $statutPrel;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=2000, nullable=true)
     */
    private $commentaire;

    /**
     * @var \PgCmdPrelev
     *
     * @ORM\ManyToOne(targetEntity="PgCmdPrelev")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prelev_id", referencedColumnName="id")
     * })
     */
    private $prelev;

    /**
     * @var \PgCmdFichiersRps
     *
     * @ORM\ManyToOne(targetEntity="PgCmdFichiersRps")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fichier_rps_id", referencedColumnName="id")
     * })
     */
    private $fichierRps;

    /**
     * @var \PgProgWebusers
     *
     * @ORM\ManyToOne(targetEntity="PgProgWebusers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;



    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set datePrel
     *
     * @param \DateTime $datePrel
     *
     * @return PgCmdSuiviPrel
     */
    public function setDatePrel($datePrel)
    {
        $this->datePrel = $datePrel;

        return $this;
    }

    /**
     * Get datePrel
     *
     * @return \DateTime
     */
    public function getDatePrel()
    {
        return $this->datePrel;
    }

    /**
     * Set statutPrel
     *
     * @param string $statutPrel
     *
     * @return PgCmdSuiviPrel
     */
    public function setStatutPrel($statutPrel)
    {
        $this->statutPrel = $statutPrel;

        return $this;
    }

    /**
     * Get statutPrel
     *
     * @return string
     */
    public function getStatutPrel()
    {
        return $this->statutPrel;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     *
     * @return PgCmdSuiviPrel
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set prelev
     *
     * @param \Aeag\SqeBundle\Entity\PgCmdPrelev $prelev
     *
     * @return PgCmdSuiviPrel
     */
    public function setPrelev(\Aeag\SqeBundle\Entity\PgCmdPrelev $prelev = null)
    {
        $this->prelev = $prelev;

        return $this;
    }

    /**
     * Get prelev
     *
     * @return \Aeag\SqeBundle\Entity\PgCmdPrelev
     */
    public function getPrelev()
    {
        return $this->prelev;
    }

    /**
     * Set fichierRps
     *
     * @param \Aeag\SqeBundle\Entity\PgCmdFichiersRps $fichierRps
     *
     * @return PgCmdSuiviPrel
     */
    public function setFichierRps(\Aeag\SqeBundle\Entity\PgCmdFichiersRps $fichierRps = null)
    {
        $this->fichierRps = $fichierRps;

        return $this;
    }

    /**
     * Get fichierRps
     *
     * @return \Aeag\SqeBundle\Entity\PgCmdFichiersRps
     */
    public function getFichierRps()
    {
        return $this->fichierRps;
    }

    /**
     * Set user
     *
     * @param \Aeag\SqeBundle\Entity\PgProgWebusers $user
     *
     * @return PgCmdSuiviPrel
     */
    public function setUser(\Aeag\SqeBundle\Entity\PgProgWebusers $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Aeag\SqeBundle\Entity\PgProgWebusers
     */
    public function getUser()
    {
        return $this->user;
    }
}
