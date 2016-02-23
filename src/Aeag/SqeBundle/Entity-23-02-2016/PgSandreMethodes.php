<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgSandreMethodes
 *
 * @ORM\Table(name="pg_sandre_methodes")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgSandreMethodesRepository")
 */
class PgSandreMethodes
{
    /**
     * @var string
     *
     * @ORM\Column(name="code_methode", type="string", length=5, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_sandre_methodes_code_methode_seq", allocationSize=1, initialValue=1)
     */
    private $codeMethode;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_methode", type="string", length=270, nullable=false)
     */
    private $nomMethode;



    /**
     * Get codeMethode
     *
     * @return string 
     */
    public function getCodeMethode()
    {
        return $this->codeMethode;
    }

    /**
     * Set nomMethode
     *
     * @param string $nomMethode
     * @return PgSandreMethodes
     */
    public function setNomMethode($nomMethode)
    {
        $this->nomMethode = $nomMethode;

        return $this;
    }

    /**
     * Get nomMethode
     *
     * @return string 
     */
    public function getNomMethode()
    {
        return $this->nomMethode;
    }
}
