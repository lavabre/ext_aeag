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
class PgSandreMethodesRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgSandreMethodes() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreMethodes p";
        $query = $query . " order by p.codeMethode";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreMethodesByCodeMethode($codeMethode) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreMethodes p";
        $query = $query . " where p.codeMethode = :codeMethode";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeMethode', $codeMethode);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}
