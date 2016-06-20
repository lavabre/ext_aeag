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
        $qb->setParameter('phase', array('R40','R41','R42','R45','R50','R51'));
        return $qb->getResult();
    }
    
    public function getReponsesValidesDb() {
        $query = "select rps";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdFichiersRps rps, Aeag\SqeBundle\Entity\PgProgPhases pha";
        $query .= " where rps.phaseFichier = pha.id";
        $query .= " and pha.codePhase IN (:phase)";
        $query .= " and rps.typeFichier = 'RPS'";
        $query .= " and rps.suppr = 'N'";
        $query .= " order by rps.id asc";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('phase', array('R40','R41'));
        $qb->setMaxResults(1);
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
        $query .= " and rps.id NOT IN ";
        $query .= " (select suivi.objId ";
        $query .= " from Aeag\SqeBundle\Entity\PgProgSuiviPhases suivi";
        $query .= " where suivi.phase = :phase";
        $query .= " group by suivi.objId";
        $query .= " having count(suivi) >= 5)";
        $query .= " order by rps.id asc";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('phase', $phase);
        $qb->setMaxResults(1);
        return $qb->getResult();
    }
    
    public function getReponsesHorsLacBackup($phase) {
        $query = "select rps2";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdFichiersRps rps2";
        $query .= " where rps2.id IN ( ";
        $query .= " select suivi.objId";
        $query .= " from Aeag\SqeBundle\Entity\PgProgSuiviPhases suivi";
        $query .= " join Aeag\SqeBundle\Entity\PgCmdFichiersRps rps with rps.id = suivi.objId";
        $query .= " join rps.demande dmd";
        $query .= " join dmd.lotan lotan";
        $query .= " join lotan.lot lot";
        $query .= " where suivi.phase = :phase";
        $query .= " and rps.typeFichier = 'RPS'";
        $query .= " and rps.suppr = 'N'";
        $query .= " and rps.phaseFichier = :phase";
        $query .= " and lot.codeMilieu <> 'LPC'";
        $query .= " group by suivi.objId";
        $query .= " having DATE_ADD(max(suivi.datePhase),1, 'day') < CURRENT_TIMESTAMP()";
        $query .= " order by suivi.objId)";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('phase', $phase);
        return $qb->getResult();
    }
}
