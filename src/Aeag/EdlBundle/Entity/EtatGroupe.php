<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EtatGroupe
 *
 * @ORM\Table(name="etat_groupe")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Repository\EtatGroupeRepository")
 */
class EtatGroupe
{
    /**
     * @var string
     *
     * @ORM\Column(name="cd_groupe", type="string", length=16, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="etat_groupe_cd_groupe_seq", allocationSize=1, initialValue=1)
     */
    private $cdGroupe;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255, nullable=false)
     */
    private $libelle;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="integer", nullable=false)
     */
    private $ordre;



    /**
     * Get cdGroupe
     *
     * @return string
     */
    public function getCdGroupe()
    {
        return $this->cdGroupe;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return etatGroupe
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     *
     * @return etatGroupe
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer
     */
    public function getOrdre()
    {
        return $this->ordre;
    }
}
