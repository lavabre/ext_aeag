<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\FrdBundle\Repository;

use Doctrine\ORM\EntityRepository;
use DoctrineExtensions\Query\Postgresql;


/**
 * Class MandatementRepository
 * @package Aeag\FrdBundle\Repository
 */
class MandatementRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getListeMandatementAll() {

        $query = "select f";
        $query = $query . "  Aeag\FrdBundle\Entity\Mandatement f";
        $query = $query . " order by f.execice, f.numMandat, f.numBordereau";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getResult();
    }

      
     /**
     *
     * @return array
     */
    public function getMandatementByEtfrId($etfrId) {

        $query = "select f";
        $query = $query . " from  Aeag\FrdBundle\Entity\Mandatement f";
        $query = $query . " where f.etfrId = " . $etfrId;
      
       //print_r($query);
        $qb = $this->_em->createQuery($query);
        return $qb->getOneOrNullResult();
    }

}
