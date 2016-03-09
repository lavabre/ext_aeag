<?php
namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgSuiviPhases
 *
 * @ORM\Table(name="pg_prog_suivi_phases")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgSuiviPhasesRepository")
 */
class PgProgSuiviPhases {
    
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_suivi_phases_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="type_objet", type="string", length=3, nullable=false)
     */
    private $typeObjet;
    
    /**
     * @var string
     *
     * @ORM\Column(name="obj_id", type="decimal", precision=38, scale=0, nullable=false)
     */
    private $objId;
    
    
    /**
     * @var \PgProgPhases
     *
     * @ORM\ManyToOne(targetEntity="PgProgPhases")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="phase_id", referencedColumnName="id")
     * })
     */
    private $phase;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_phase", type="datetime", nullable=false)
     */
    private $datePhase;
    
    /**
     * @var \PgProgWebusers
     *
     * @ORM\ManyToOne(targetEntity="PgProgWebusers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id")
     * })
     */
    private $user;
    
    public function getId() {
        return $this->id;
    }

    public function getTypeObjet() {
        return $this->typeObjet;
    }

    public function getObjId() {
        return $this->objId;
    }

    public function getPhase() {
        return $this->phase;
    }

    public function getDatePhase() {
        return $this->datePhase;
    }

    public function getUser() {
        return $this->user;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTypeObjet($typeObjet) {
        $this->typeObjet = $typeObjet;
    }

    public function setObjId($objId) {
        $this->objId = $objId;
    }

    public function setPhase(\Aeag\SqeBundle\Entity\PgProgPhases $phase) {
        $this->phase = $phase;
    }

    public function setDatePhase(\DateTime $datePhase) {
        $this->datePhase = $datePhase;
    }

    public function setUser(\Aeag\SqeBundle\Entity\PgProgWebusers $user) {
        $this->user = $user;
    }

}
