<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgSandreParametres
 *
 * @ORM\Table(name="pg_sandre_parametres")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgSandreParametresRepository")
 */
class PgSandreParametres
{
    /**
     * @var string
     *
     * @ORM\Column(name="code_parametre", type="string", length=5, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_sandre_parametres_code_parametre_seq", allocationSize=1, initialValue=1)
     */
    private $codeParametre;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_parametre", type="string", length=255, nullable=false)
     */
    private $nomParametre;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_court", type="string", length=10, nullable=true)
     */
    private $libelleCourt;

    /**
     * @var string
     *
     * @ORM\Column(name="type_parametre", type="string", length=32, nullable=true)
     */
    private $typeParametre;

    /**
     * @var string
     *
     * @ORM\Column(name="code_cas", type="string", length=12, nullable=true)
     */
    private $codeCas;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgSandreUnites", inversedBy="codeParametre")
     * @ORM\JoinTable(name="pg_sandre_unites_possibles_param",
     *   joinColumns={
     *     @ORM\JoinColumn(name="code_parametre", referencedColumnName="code_parametre")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="code_unite", referencedColumnName="code_unite")
     *   }
     * )
     */
    private $codeUnite;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->codeUnite = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Get codeParametre
     *
     * @return string 
     */
    public function getCodeParametre()
    {
        return $this->codeParametre;
    }

    /**
     * Set nomParametre
     *
     * @param string $nomParametre
     * @return PgSandreParametres
     */
    public function setNomParametre($nomParametre)
    {
        $this->nomParametre = $nomParametre;

        return $this;
    }

    /**
     * Get nomParametre
     *
     * @return string 
     */
    public function getNomParametre()
    {
        return $this->nomParametre;
    }

    /**
     * Set libelleCourt
     *
     * @param string $libelleCourt
     * @return PgSandreParametres
     */
    public function setLibelleCourt($libelleCourt)
    {
        $this->libelleCourt = $libelleCourt;

        return $this;
    }

    /**
     * Get libelleCourt
     *
     * @return string 
     */
    public function getLibelleCourt()
    {
        return $this->libelleCourt;
    }

    /**
     * Set typeParametre
     *
     * @param string $typeParametre
     * @return PgSandreParametres
     */
    public function setTypeParametre($typeParametre)
    {
        $this->typeParametre = $typeParametre;

        return $this;
    }

    /**
     * Get typeParametre
     *
     * @return string 
     */
    public function getTypeParametre()
    {
        return $this->typeParametre;
    }

    /**
     * Set codeCas
     *
     * @param string $codeCas
     * @return PgSandreParametres
     */
    public function setCodeCas($codeCas)
    {
        $this->codeCas = $codeCas;

        return $this;
    }

    /**
     * Get codeCas
     *
     * @return string 
     */
    public function getCodeCas()
    {
        return $this->codeCas;
    }

    /**
     * Add codeUnite
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreUnites $codeUnite
     * @return PgSandreParametres
     */
    public function addCodeUnite(\Aeag\SqeBundle\Entity\PgSandreUnites $codeUnite)
    {
        $this->codeUnite[] = $codeUnite;

        return $this;
    }

    /**
     * Remove codeUnite
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreUnites $codeUnite
     */
    public function removeCodeUnite(\Aeag\SqeBundle\Entity\PgSandreUnites $codeUnite)
    {
        $this->codeUnite->removeElement($codeUnite);
    }

    /**
     * Get codeUnite
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCodeUnite()
    {
        return $this->codeUnite;
    }
}
