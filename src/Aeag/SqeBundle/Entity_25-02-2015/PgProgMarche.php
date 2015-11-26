<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgMarche
 *
 * @ORM\Table(name="pg_prog_marche", indexes={@ORM\Index(name="IDX_8D2F8149703368CA", columns={"resp_adr_cor_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgMarcheRepository")
 */
class PgProgMarche
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_marche_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type_marche", type="string", length=4, nullable=false)
     */
    private $typeMarche;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_marche", type="string", length=50, nullable=false)
     */
    private $nomMarche;

    /**
     * @var string
     *
     * @ORM\Column(name="annee_deb", type="decimal", precision=4, scale=0, nullable=false)
     */
    private $anneeDeb;

    /**
     * @var string
     *
     * @ORM\Column(name="duree", type="decimal", precision=2, scale=0, nullable=false)
     */
    private $duree;

    /**
     * @var \PgRefCorresProducteur
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresProducteur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="resp_adr_cor_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $respAdrCor;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgProgWebusers", mappedBy="marche")
     */
    private $webuser;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->webuser = new \Doctrine\Common\Collections\ArrayCollection();
    }


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
     * Set typeMarche
     *
     * @param string $typeMarche
     * @return PgProgMarche
     */
    public function setTypeMarche($typeMarche)
    {
        $this->typeMarche = $typeMarche;

        return $this;
    }

    /**
     * Get typeMarche
     *
     * @return string 
     */
    public function getTypeMarche()
    {
        return $this->typeMarche;
    }

    /**
     * Set nomMarche
     *
     * @param string $nomMarche
     * @return PgProgMarche
     */
    public function setNomMarche($nomMarche)
    {
        $this->nomMarche = $nomMarche;

        return $this;
    }

    /**
     * Get nomMarche
     *
     * @return string 
     */
    public function getNomMarche()
    {
        return $this->nomMarche;
    }

    /**
     * Set anneeDeb
     *
     * @param string $anneeDeb
     * @return PgProgMarche
     */
    public function setAnneeDeb($anneeDeb)
    {
        $this->anneeDeb = $anneeDeb;

        return $this;
    }

    /**
     * Get anneeDeb
     *
     * @return string 
     */
    public function getAnneeDeb()
    {
        return $this->anneeDeb;
    }

    /**
     * Set duree
     *
     * @param string $duree
     * @return PgProgMarche
     */
    public function setDuree($duree)
    {
        $this->duree = $duree;

        return $this;
    }

    /**
     * Get duree
     *
     * @return string 
     */
    public function getDuree()
    {
        return $this->duree;
    }

    /**
     * Set respAdrCor
     *
     * @param \Aeag\SqeBundle\Entity\PgRefCorresProducteur $respAdrCor
     * @return PgProgMarche
     */
    public function setRespAdrCor(\Aeag\SqeBundle\Entity\PgRefCorresProducteur $respAdrCor = null)
    {
        $this->respAdrCor = $respAdrCor;

        return $this;
    }

    /**
     * Get respAdrCor
     *
     * @return \Aeag\SqeBundle\Entity\PgRefCorresProducteur 
     */
    public function getRespAdrCor()
    {
        return $this->respAdrCor;
    }

    /**
     * Add webuser
     *
     * @param \Aeag\SqeBundle\Entity\PgProgWebusers $webuser
     * @return PgProgMarche
     */
    public function addWebuser(\Aeag\SqeBundle\Entity\PgProgWebusers $webuser)
    {
        $this->webuser[] = $webuser;

        return $this;
    }

    /**
     * Remove webuser
     *
     * @param \Aeag\SqeBundle\Entity\PgProgWebusers $webuser
     */
    public function removeWebuser(\Aeag\SqeBundle\Entity\PgProgWebusers $webuser)
    {
        $this->webuser->removeElement($webuser);
    }

    /**
     * Get webuser
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWebuser()
    {
        return $this->webuser;
    }
}
