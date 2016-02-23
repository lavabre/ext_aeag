<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgSandreUnites
 *
 * @ORM\Table(name="pg_sandre_unites")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgSandreUnitesRepository")
 */
class PgSandreUnites
{
    /**
     * @var string
     *
     * @ORM\Column(name="code_unite", type="string", length=5, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_sandre_unites_code_unite_seq", allocationSize=1, initialValue=1)
     */
    private $codeUnite;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_unite", type="string", length=255, nullable=false)
     */
    private $nomUnite;

    /**
     * @var string
     *
     * @ORM\Column(name="symbole", type="string", length=50, nullable=true)
     */
    private $symbole;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgSandreParametres", mappedBy="codeUnite")
     */
    private $codeParametre;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->codeParametre = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Get codeUnite
     *
     * @return string 
     */
    public function getCodeUnite()
    {
        return $this->codeUnite;
    }

    /**
     * Set nomUnite
     *
     * @param string $nomUnite
     * @return PgSandreUnites
     */
    public function setNomUnite($nomUnite)
    {
        $this->nomUnite = $nomUnite;

        return $this;
    }

    /**
     * Get nomUnite
     *
     * @return string 
     */
    public function getNomUnite()
    {
        return $this->nomUnite;
    }

    /**
     * Set symbole
     *
     * @param string $symbole
     * @return PgSandreUnites
     */
    public function setSymbole($symbole)
    {
        $this->symbole = $symbole;

        return $this;
    }

    /**
     * Get symbole
     *
     * @return string 
     */
    public function getSymbole()
    {
        return $this->symbole;
    }

    /**
     * Add codeParametre
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre
     * @return PgSandreUnites
     */
    public function addCodeParametre(\Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre)
    {
        $this->codeParametre[] = $codeParametre;

        return $this;
    }

    /**
     * Remove codeParametre
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre
     */
    public function removeCodeParametre(\Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre)
    {
        $this->codeParametre->removeElement($codeParametre);
    }

    /**
     * Get codeParametre
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCodeParametre()
    {
        return $this->codeParametre;
    }
}
