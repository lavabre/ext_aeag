<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdFichiersRpsRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdFichiersRpsRepository extends EntityRepository {
    
    public function getReponsesValidesByDemande($demande) {
        $query = "select rps";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdFichiersRps rps, Aeag\SqeBundle\Entity\PgProgPhases pha";
        $query .= " where rps.demande = :demande"; 
        $query .= " and rps.phaseFichier = pha.id";
        $query .= " and pha.codePhase IN (:phase)";
        $query .= " and rps.typeFichier = 'RPS'";
        $query .= " and rps.suppr = 'N'";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demande);
        $qb->setParameter('phase', array('R40','R41','R50','R51'));
        return $qb->getResult();
    }
    
    public function getReponsesValides() {
        $query = "select rps";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdFichiersRps rps, Aeag\SqeBundle\Entity\PgProgPhases pha";
        $query .= " where rps.phaseFichier = pha.id";
        $query .= " and pha.codePhase IN (:phase)";
        $query .= " and rps.typeFichier = 'RPS'";
        $query .= " and rps.suppr = 'N'";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('phase', array('R40','R41'));
        return $qb->getResult();
    }
    
    public function getReponsesHorsLac($phase) {
        $query = "select rps";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdFichiersRps rps";
        $query .= " join rps.phaseFichier phase";
        $query .= " join rps.demande dmd";
        $query .= " join dmd.lotan lotan";
        $query .= " join lotan.lot lot";
        $query .= " where phase = :phase";
        $query .= " and rps.typeFichier = 'RPS'";
        $query .= " and rps.suppr = 'N'";
        $query .= " and lot.codeMilieu <> 'LPC'";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('phase', $phase);
        return $qb->getResult();
        
    }
}
