<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotPresta
 *
 * @ORM\Table(name="pg_prog_lot_presta", 
 *                       indexes={@ORM\Index(name="IDX_PgProgLotPresta_lot", columns={"lot_id"}),
 *                                     @ORM\Index(name="IDX_PgProgLotPresta_presta", columns={"presta_id"}),
 *                                     @ORM\Index(name="IDX_PgProgLotPresta_type", columns={"type_presta"}) })
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgLotPrestaRepository")
 */
class PgProgLotPresta
{
    /**
     * @var string
     *
     * @ORM\Column(name="type_presta", type="string", length=1, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $typePresta;

    /**
     * @var string
     *
     * @ORM\Column(name="defaut", type="string", length=1, nullable=false)
     */
    private $defaut;

    /**
     * @var \PgProgLot
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="PgProgLot")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     * })
     */
    private $lot;

    /**
     * @var \PgRefCorresPresta
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="presta_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $presta;



    /**
     * Set typePresta
     *
     * @param string $typePresta
     * @return PgProgLotPresta
     */
    public function setTypePresta($typePresta)
    {
        $this->typePresta = $typePresta;

        return $this;
    }

    /**
     * Get typePresta
     *
     * @return string 
     */
    public function getTypePresta()
    {
        return $this->typePresta;
    }

    /**
     * Set defaut
     *
     * @param string $defaut
     * @return PgProgLotPresta
     */
    public function setDefaut($defaut)
    {
        $this->defaut = $defaut;

        return $this;
    }

    /**
     * Get defaut
     *
     * @return string 
     */
    public function getDefaut()
    {
        return $this->defaut;
    }

    /**
     * Set lot
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLot $lot
     * @return PgProgLotPresta
     */
    public function setLot(\Aeag\SqeBundle\Entity\PgProgLot $lot)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * Get lot
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLot 
     */
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * Set presta
     *
     * @param \Aeag\SqeBundle\Entity\PgRefCorresPresta $presta
     * @return PgProgLotPresta
     */
    public function setPresta(\Aeag\SqeBundle\Entity\PgRefCorresPresta $presta)
    {
        $this->presta = $presta;

        return $this;
    }

    /**
     * Get presta
     *
     * @return \Aeag\SqeBundle\Entity\PgRefCorresPresta 
     */
    public function getPresta()
    {
        return $this->presta;
    }
}
