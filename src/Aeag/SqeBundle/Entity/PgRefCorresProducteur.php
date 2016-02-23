<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgRefCorresProducteur
 *
 * @ORM\Table(name="pg_ref_corres_producteur")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgRefCorresProducteurRepository")
 */
class PgRefCorresProducteur
{
    /**
     * @var string
     *
     * @ORM\Column(name="adr_cor_id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_ref_corres_producteur_adr_cor_id_seq", allocationSize=1, initialValue=1)
     */
    private $adrCorId;

    /**
     * @var string
     *
     * @ORM\Column(name="ancnum", type="string", length=9, nullable=false)
     */
    private $ancnum;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_corres", type="string", length=255, nullable=false)
     */
    private $nomCorres;

    /**
     * @var string
     *
     * @ORM\Column(name="code_siret", type="string", length=14, nullable=true)
     */
    private $codeSiret;

    /**
     * @var string
     *
     * @ORM\Column(name="code_sandre", type="string", length=14, nullable=true)
     */
    private $codeSandre;



    /**
     * Get adrCorId
     *
     * @return string 
     */
    public function getAdrCorId()
    {
        return $this->adrCorId;
    }

    /**
     * Set ancnum
     *
     * @param string $ancnum
     * @return PgRefCorresProducteur
     */
    public function setAncnum($ancnum)
    {
        $this->ancnum = $ancnum;

        return $this;
    }

    /**
     * Get ancnum
     *
     * @return string 
     */
    public function getAncnum()
    {
        return $this->ancnum;
    }

    /**
     * Set nomCorres
     *
     * @param string $nomCorres
     * @return PgRefCorresProducteur
     */
    public function setNomCorres($nomCorres)
    {
        $this->nomCorres = $nomCorres;

        return $this;
    }

    /**
     * Get nomCorres
     *
     * @return string 
     */
    public function getNomCorres()
    {
        return $this->nomCorres;
    }

    /**
     * Set codeSiret
     *
     * @param string $codeSiret
     * @return PgRefCorresProducteur
     */
    public function setCodeSiret($codeSiret)
    {
        $this->codeSiret = $codeSiret;

        return $this;
    }

    /**
     * Get codeSiret
     *
     * @return string 
     */
    public function getCodeSiret()
    {
        return $this->codeSiret;
    }

    /**
     * Set codeSandre
     *
     * @param string $codeSandre
     * @return PgRefCorresProducteur
     */
    public function setCodeSandre($codeSandre)
    {
        $this->codeSandre = $codeSandre;

        return $this;
    }

    /**
     * Get codeSandre
     *
     * @return string 
     */
    public function getCodeSandre()
    {
        return $this->codeSandre;
    }
}
