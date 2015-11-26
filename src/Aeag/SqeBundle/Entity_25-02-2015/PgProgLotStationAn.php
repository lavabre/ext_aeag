<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotStationAn
 *
 * @ORM\Table(name="pg_prog_lot_station_an", indexes={@ORM\Index(name="IDX_56468CEE21BDB235", columns={"station_id"}), @ORM\Index(name="IDX_56468CEEE980DBA9", columns={"lotan_id"}), @ORM\Index(name="IDX_56468CEE52C6DF80", columns={"preleveur_id"}), @ORM\Index(name="IDX_56468CEE59E5BC7E", columns={"labo_dft_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgLotStationAnRepository")
 */
class PgProgLotStationAn
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_lot_station_an_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="rsx_id", type="decimal", precision=38, scale=0, nullable=true)
     */
    private $rsxId;

    /**
     * @var \PgRefStationMesure
     *
     * @ORM\ManyToOne(targetEntity="PgRefStationMesure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="station_id", referencedColumnName="ouv_fonc_id")
     * })
     */
    private $station;

    /**
     * @var \PgProgLotAn
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotAn")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lotan_id", referencedColumnName="id")
     * })
     */
    private $lotan;

    /**
     * @var \PgRefCorresPresta
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="preleveur_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $preleveur;

    /**
     * @var \PgRefCorresPresta
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="labo_dft_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $laboDft;



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
     * Set rsxId
     *
     * @param string $rsxId
     * @return PgProgLotStationAn
     */
    public function setRsxId($rsxId)
    {
        $this->rsxId = $rsxId;

        return $this;
    }

    /**
     * Get rsxId
     *
     * @return string 
     */
    public function getRsxId()
    {
        return $this->rsxId;
    }

    /**
     * Set station
     *
     * @param \Aeag\SqeBundle\Entity\PgRefStationMesure $station
     * @return PgProgLotStationAn
     */
    public function setStation(\Aeag\SqeBundle\Entity\PgRefStationMesure $station = null)
    {
        $this->station = $station;

        return $this;
    }

    /**
     * Get station
     *
     * @return \Aeag\SqeBundle\Entity\PgRefStationMesure 
     */
    public function getStation()
    {
        return $this->station;
    }

    /**
     * Set lotan
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotAn $lotan
     * @return PgProgLotStationAn
     */
    public function setLotan(\Aeag\SqeBundle\Entity\PgProgLotAn $lotan = null)
    {
        $this->lotan = $lotan;

        return $this;
    }

    /**
     * Get lotan
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotAn 
     */
    public function getLotan()
    {
        return $this->lotan;
    }

    /**
     * Set preleveur
     *
     * @param \Aeag\SqeBundle\Entity\PgRefCorresPresta $preleveur
     * @return PgProgLotStationAn
     */
    public function setPreleveur(\Aeag\SqeBundle\Entity\PgRefCorresPresta $preleveur = null)
    {
        $this->preleveur = $preleveur;

        return $this;
    }

    /**
     * Get preleveur
     *
     * @return \Aeag\SqeBundle\Entity\PgRefCorresPresta 
     */
    public function getPreleveur()
    {
        return $this->preleveur;
    }

    /**
     * Set laboDft
     *
     * @param \Aeag\SqeBundle\Entity\PgRefCorresPresta $laboDft
     * @return PgProgLotStationAn
     */
    public function setLaboDft(\Aeag\SqeBundle\Entity\PgRefCorresPresta $laboDft = null)
    {
        $this->laboDft = $laboDft;

        return $this;
    }

    /**
     * Get laboDft
     *
     * @return \Aeag\SqeBundle\Entity\PgRefCorresPresta 
     */
    public function getLaboDft()
    {
        return $this->laboDft;
    }
}
