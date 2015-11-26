<?php

/**
 * Description of PgProgPhasesRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgPhasesRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgPhasesRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgPhases() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgPhases p";
        $query = $query . " where p.codePhase  like 'P%'";
        $query = $query . " order by p.codePhase";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    
    /**
     * @return array
     */
    public function getPgProgPhasesById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgPhases p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgPhasesByCodePhase($codePhase) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgPhases p";
        $query = $query . " where p.codePhase = '" . $codePhase . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    

}
