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
class PgProgMarcheRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgMarches() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarche p";
        $query = $query . " order by p.nomMarche";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgMarcheByid($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarche p";
        $query = $query . " where p.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgMarcheByNomMarche($nomMarche) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarches p";
        $query = $query . " where p.nomMarche = '" . $nomMarche . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgMarcheByTypeMarche($typeMarche) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarche p";
        $query = $query . " where p.typeMarche = '" . $typeMarche . "'";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    /**
     * @return array
     */
    public function getPgProgMarchesType() {
        $query = "select distinct p.typeMarche";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgMarche p";
        $query = $query . " order by p.typeMarche";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

}
