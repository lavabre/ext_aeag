<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdFichiersRpsRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdFichiersRpsRepository extends EntityRepository {
    
    public function getReponseByDemande($demande) {
        $query = "select rps";
        $query .= " from Aeag\SqeBundle\Entity\PgCmdFichiersRps rps";
        $query .= " where rps.demande = :demande"; 
        $query .= " and rps.phaseFichier IN (:phase)";
        $query .= " and rps.suppr = 'N'";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demande);
        $qb->setParameter('phase', array('19','20','21'));
        
        return $qb->getResult();
    }
}
