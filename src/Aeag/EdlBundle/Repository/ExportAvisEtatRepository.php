<?php

namespace Aeag\EdlBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * ExportAvisEtatRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 * 
 */
class ExportAvisEtatRepository extends EntityRepository {

   /**
     * @return array
     */
    public function getExportAvisEtats() {
        $query = "select a";
        $query = $query . " from Aeag\EdlBundle\Entity\ExportAvisEtat a";
        $query = $query . ' order by a.gOrdre, a.typPordre';
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    /**
     * @return array
     */
    public function getExportAvisEtatByWhere($where) {
        $query = "select a";
        $query = $query . " from Aeag\EdlBundle\Entity\ExportAvisEtat a ";
        if ($where){
        $query = $query . ' where ' . $where;
        }
        $query = $query . ' order by a.gOrdre, a.typPordre';
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

   
 
}