<?php

/**
 * Description of PgProgStatutRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgStatutRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgStatutRepository extends EntityRepository {

    public function getPgProgStatutByCodeStatut($codeStatut) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgStatut p";
        $query = $query . " where p.codeStatut = :codeStatut";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeStatut', $codeStatut);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}
