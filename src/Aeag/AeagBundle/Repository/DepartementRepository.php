<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class DepartementRepository
 * @package Aeag\AeagBundle\Repository
 */
class DepartementRepository extends EntityRepository
{


    /**
     * @return array
     */
    public function getDepartements()
    {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\Departement d";
        $query = $query . " order by d.dept";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getDepartementsByDec()
    {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\Departement d";
        $query = $query . " where d.dec = 'O'";
        $query = $query . " order by d.dept";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getDepartementsByRegion($reg)
    {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\Departement d";
        $query = $query . " where d.Region = :reg";
        $query = $query . " order by d.dept";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('reg', $reg);

        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getDepartementsByRegionDec($reg)
    {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\Departement d";
        $query = $query . " where d.dec = 'O'";
        $query = $query . " and d.Region = :reg";
        $query = $query . " order by d.dept";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('reg', $reg);

        //print_r($query);
        return $qb->getResult();
    }


    /**
     * @param $dept
     * @return mixed
     */
    public function getDepartementByDept($dept)
    {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\Departement d";
        $query = $query . " where d.dept = :dept";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('dept', $dept);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }


}
