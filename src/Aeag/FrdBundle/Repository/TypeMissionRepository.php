<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\FrdBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class TypeMissionRepository
 * @package Aeag\FrdBundle\Repository
 */
class TypeMissionRepository extends EntityRepository
{


    /**
     * @return array
     */
    public function getTypeMission()
    {

        $query = "select d";
        $query = $query . " from Aeag\FrdBundle\Entity\TypeMission d";
        $query = $query . " order by d.code";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getTypeMissionByCode($code)
    {

        $query = "select d";
        $query = $query . " from Aeag\FrdBundle\Entity\TypeMission d";
        $query = $query . " where d.code = '" . $code . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }


}
