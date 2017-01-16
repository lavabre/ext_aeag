<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\FrdBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class FinaliteRepository
 * @package Aeag\FrdBundle\Repository
 */
class FinaliteRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getFinalites() {

        $query = "select d";
        $query = $query . " from Aeag\FrdBundle\Entity\Finalite d";
        $query = $query . " order by d.code";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getFinaliteByCode($code) {

        $query = "select d";
        $query = $query . " from Aeag\FrdBundle\Entity\Finalite d";
        $query = $query . " where d.code = :code";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('code', $code);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}
