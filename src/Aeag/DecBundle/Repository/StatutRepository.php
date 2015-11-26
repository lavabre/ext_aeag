<?php

/**
 * Description of StatutRepository
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class StatutRepository
 * @package Aeag\DecBundle\Repository
 */
class StatutRepository extends EntityRepository
{


    /**
     * @return array
     */
    public function getStatuts()
    {

        $query = "select s";
        $query = $query . " from Aeag\DecBundle\Entity\Statut s";
        $query = $query . " order by s.code";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getStatutByCode($code)
    {

        $query = "select s";
        $query = $query . " from Aeag\DecBundle\Entity\Statut s";
        $query = $query . " where s.code = '" . $code . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }


}
