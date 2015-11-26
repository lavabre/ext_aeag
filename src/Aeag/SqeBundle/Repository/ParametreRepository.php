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
class ParametreRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getParametres() {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\Parametre c";
        $query = $query . " order by c.code";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getParametreByCode($code) {
        $query = "select c";
        $query = $query . " from Aeag\SqeBundle\Entity\Parametre c";
        $query = $query . " where c.code = '" . $code . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}
