<?php

/**
 * Description of PgProgLotPrestaRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgProgLotPrestaRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgLotPrestaRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgLotPrestas() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPresta p";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPrestaByTypePresta($typePresta) {
        $query = "select distinct p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPresta p";
        $query = $query . " where p.typePresta = :typePresta";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('typePresta', $typePresta);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPrestaByLot($lot) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPresta p";
        $query = $query . " where p.lot = :lot";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('lot', $lot->getId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPrestaByLotTypePresta($lot, $typePresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPresta p";
        $query = $query . " where p.lot = :lot";
        $query = $query . " and p.typePresta = :typePresta";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('lot', $lot->getId());
        $qb->setParameter('typePresta', $typePresta);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPrestaByPresta($pgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPresta p";
        $query = $query . " where p.presta = :pgRefCorresPresta";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('pgRefCorresPresta', $pgRefCorresPresta->getAdrCorId());
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPrestaByLotPresta($lot, $pgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPresta p";
        $query = $query . " where p.lot = :lot";
        $query = $query . " and p.presta = :pgRefCorresPresta";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('lot', $lot->getId());
        $qb->setParameter('pgRefCorresPresta', $pgRefCorresPresta->getAdrCorId());
        //print_r($query);
        return $qb->getResult();
    }

}
