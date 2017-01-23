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
class PgRefReseauMesureRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgRefReseauMesures() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefReseauMesure p";
        $query = $query . " order by p.codeAeagRsx";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgRefReseauMesureByGroupementId($groupementId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefReseauMesure p";
        $query = $query . " where p.groupementId = :groupementId";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('groupementId', $groupementId);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgRefReseauMesureByCodeAeagRsx($codeAeagRsx) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefReseauMesure p";
        $query = $query . " where p.codeAeagRsx = :codeAeagRsx";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeAeagRsx', $codeAeagRsx);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgRefReseauMesureByCodeSandre($codeSandre) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgRefReseauMesure p";
        $query = $query . " where p.codeSandre = :codeSandre";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codeSandre', $codeSandre);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}
