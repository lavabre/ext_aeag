<?php

/**
 * Description of PgProgGrpParamRefRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ParametreRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgGrpParamRefRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgGrpParamRefs() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrpParamRef p";
        $query = $query . " order by p.codeGrp";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    
     /**
     * @return array
     */
    public function getPgProgGrpParamRefById($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrpParamRef p";
        $query = $query . " where p.id = '" . $id . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    /**
     * @return array
     */
    public function getPgProgGrpParamRefByCodeGrp($codeGrp) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrpParamRef p";
        $query = $query . " where p.codeGrp = '" . $codeGrp . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgGrpParamRefByCodeMilieu($pgProgTypeMilieu) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrpParamRef p";
        $query = $query . " where p.codeMilieu = '" . $pgProgTypeMilieu->getCodeMilieu() . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgGrpParamRefByCodeSupport($pgSandreSupports) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrpParamRef p";
        $query = $query . " where p.support = '" . $pgSandreSupports->getCodeSupport() . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     public function getPgProgGrpParamRefByCodeMilieuCodeSupport($pgProgTypeMilieu,$pgSandreSupports) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgGrpParamRef p";
        $query = $query . " where p.codeMilieu = '" . $pgProgTypeMilieu->getCodeMilieu() . "'";
        $query = $query . " and p.support = '" . $pgSandreSupports->getCodeSupport() . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
   

}
