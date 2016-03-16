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
        $query .= " and rps.suppr = 'N'";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demande);
        $qb->setParameter('phase', array('R40','R41','R50','R51'));
        return $qb->getResult();
    }
    
     public function getFichierRpsById($id) {
        $query = "select rps";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdFichiersRps rps";
        $query .= " where rps.id  = :id"; 
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
       return $qb->getOneOrNullResult();
    }
}
