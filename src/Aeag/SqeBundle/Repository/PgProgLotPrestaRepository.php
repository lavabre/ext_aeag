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
        $query = $query . " where p.typePresta = '" . $typePresta . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgLotPrestaByLot($lot) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPresta p";
        $query = $query . " where p.lot = " . $lot->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgProgLotPrestaByLotTypePresta($lot, $typePresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPresta p";
        $query = $query . " where p.lot = " . $lot->getId();
        $query = $query . " and p.typePresta = '" . $typePresta . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgLotPrestaByPresta($PgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPresta p";
        $query = $query . " where p.presta = " . $PgRefCorresPresta->getAdrCorId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgLotPrestaByLotPresta($lot, $PgRefCorresPresta) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgLotPresta p";
        $query = $query . " where p.lot = " . $lot->getId();
        $query = $query . " and p.presta = " . $PgRefCorresPresta->getAdrCorId();
        $qb = $this->_em->createQuery($query);
       //print_r($query);
        return $qb->getResult();
    }
    
   
}
