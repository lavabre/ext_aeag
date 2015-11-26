<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\FrdBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class SousThemeRepository
 * @package Aeag\FrdBundle\Repository
 */
class SousThemeRepository extends EntityRepository
{


    /**
     * @return array
     */
    public function getSousThemes()
    {

        $query = "select d";
        $query = $query . " from Aeag\FrdBundle\Entity\SousTheme d";
        $query = $query . " order by d.code";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getSousThemeByCode($code)
    {

        $query = "select d";
        $query = $query . " from Aeag\FrdBundle\Entity\SousTheme d";
        $query = $query . " where d.code = '" . $code . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     /**
     * @param $finalite
     * @return mixed
     */
    public function getSousThemeByFinalite($finalite)
    {

        $query = "select d";
        $query = $query . " from Aeag\FrdBundle\Entity\SousTheme d";
        $query = $query . " where d.finalite = '" . $finalite . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }


}
