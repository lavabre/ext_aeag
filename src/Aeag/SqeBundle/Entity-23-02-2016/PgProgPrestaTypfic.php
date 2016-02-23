<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdFichiersRps
 *
 * @ORM\Table(name="pg_prog_presta_typfic")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgPrestaTypficRepository")
 */
class PgProgPrestaTypfic {
    
    /**
     * @var \PgProgTypeMilieu
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="PgProgTypeMilieu")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_milieu", referencedColumnName="code_milieu")
     * })
     */
    private $codeMilieu;
    
    /**
     * @var \PgRefCorresPresta
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
        * @ORM\OneToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prestataire_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $prestataire; 
    
    /**
     * @var string
     *
     * @ORM\Column(name="info_format_fic", type="string", length=50, nullable=true)
     */
    private $formatFic;
    
    /**
     * @var string
     *
     * @ORM\Column(name="info_taille_fic", type="string", length=50, nullable=true)
     */
    private $tailleFic;
    
    public function getCodeMilieu() {
        return $this->codeMilieu;
    }

    public function getPrestataire() {
        return $this->prestataire;
    }

    public function getFormatFic() {
        return $this->formatFic;
    }

    public function getTailleFic() {
        return $this->tailleFic;
    }

    public function setCodeMilieu(\Aeag\SqeBundle\Entity\PgProgTypeMilieu $codeMilieu) {
        $this->codeMilieu = $codeMilieu;
    }

    public function setPrestataire(\Aeag\SqeBundle\Entity\PgRefCorresPresta $prestataire) {
        $this->prestataire = $prestataire;
    }

    public function setFormatFic($formatFic) {
        $this->formatFic = $formatFic;
    }

    public function setTailleFic($tailleFic) {
        $this->tailleFic = $tailleFic;
    }


}
