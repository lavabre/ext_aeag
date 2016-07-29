<?php

/**
 * Description of ParametreRepository
 *
 * @author lavabre
 */

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ParametreRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgProgTypeMilieuRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgTypeMilieux() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgTypeMilieu p";
        $query = $query . " order by p.codeMilieu";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

   
    public function getPgProgTypeMilieuByCodeMilieu($codeMilieu) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgTypeMilieu p";
        $query = $query . " where p.codeMilieu = '" . $codeMilieu . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    
    public function getPgProgTypeMilieuByTypePeriode($PgProgTypePeriode) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgTypeMilieu p";
        $query = $query . " where p.typePeriode = " . $PgProgTypePeriode->getId();
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     /**
     * @return array
     */
    public function getPgProgCatMilieux() {
        $query = "select distinct(p.categorieMilieu)";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgTypeMilieu p";
        $query = $query . " order by p.categorieMilieu";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
     public function getPgProgTypeMilieuByCategorieMilieu($categorieMilieu) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgTypeMilieu p";
        $query = $query . " where p.categorieMilieu = '" . $categorieMilieu . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getPgProgTypesMilieuxByCodeMilieu($codeMilieu) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgTypeMilieu p";
        $query = $query . " where p.codeMilieu LIKE '%" . $codeMilieu . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    

}
