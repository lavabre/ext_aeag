<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdSuiviPrelRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdSuiviPrelRepository extends EntityRepository {
    
      
    /**
     * @return array
     */
    public function getPgCmdSuiviPrels() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getPgCmdSuiviPrelById($id) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c";
        $query = $query . " where c.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    /**
     * @return array
     */
    public function getPgCmdSuiviPrelByPrelev($pgCmdPrelev) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId() ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getPgCmdSuiviPrelByPrelevOrderDate($pgCmdPrelev) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId() ;
        $query = $query .  " order by c.datePrel desc, c.id desc";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getPgCmdSuiviPrelByPrelevStatutPrel($pgCmdPrelev, $statutPrel) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\PgCmdSuiviPrel c";
        $query = $query . " where c.prelev = " . $pgCmdPrelev->getId() ;
        $query = $query . " and c.statutPrel = '" . $statutPrel . "'" ;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    
}
