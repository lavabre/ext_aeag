<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ParametreRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgRefCorresProducteurRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgRefCorresProducteurs() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefCorresProducteur p";
        $query = $query . " order by p.ancnum";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgRefCorresProducteurByAdrCorId($adrCorId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefCorresProducteur p";
        $query = $query . " where p.adrCorId = " . $adrCorId;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    public function getPgRefCorresProducteurByAncnum($ancnum) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefCorresProducteur p";
        $query = $query . " where p.ancnum = '" . $ancnum . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}
