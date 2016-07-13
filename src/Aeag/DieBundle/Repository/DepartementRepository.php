<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\DieBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class DepartementRepository
 * @package Aeag\DieBundle\Repository
 */
class DepartementRepository extends EntityRepository
{


    /**
     * @return array
     */
    public function getDepartements()
    {

        $query = "select d";
        $query = $query . " from Aeag\DieBundle\Entity\Departement d";
        $query = $query . " order by d.dept";

        $qb = $this->_em->createQuery($query);

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
        $query = $query . " from Aeag\DieBundle\Entity\Departement d";
        $query = $query . " where d.dept = '" . $dept . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
      /**
     * @param $dept
     * @return mixed
     */
    public function getDepartementByLibelle($libelle)
    {

        $query = "select d";
        $query = $query . " from Aeag\DieBundle\Entity\Departement d";
        $query = $query . " where d.libelle = '" . $libelle . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }


}
