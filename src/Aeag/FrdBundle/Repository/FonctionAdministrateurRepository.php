<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\FrdBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class FonctionAdministrateurRepository
 * @package Aeag\FrdBundle\Repository
 */
class FonctionAdministrateurRepository extends EntityRepository
{


    /**
     * @return array
     */
    public function getFonctionAdministrateur()
    {

        $query = "select d";
        $query = $query . " from Aeag\FrdBundle\Entity\FonctionAdministrateur d";
        $query = $query . " order by d.code";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getFonctionAdministrateurByCode($code)
    {

        $query = "select d";
        $query = $query . " from Aeag\FrdBundle\Entity\FonctionAdministrateur d";
        $query = $query . " where d.code = '" . $code . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }


}
