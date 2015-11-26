<?php

/**
 * Description of CategorieRepository
 *
 * @author lavabre
 */

namespace Aeag\AideBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class CategorieRepository
 * @package Aeag\AideBundle\Repository
 */
class CategorieRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getCategories() {

        $query = "select c";
        $query = $query . " from Aeag\AideBundle\Entity\Categorie c";
        $query = $query . " order by c.cate";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $dept
     * @return mixed
     */
    public function getCategorie($cate) {

        $query = "select c";
        $query = $query . " from Aeag\AideBundle\Entity\Categorie c";
        $query = $query . " where c.cate = '" . $cate . "'";
        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}
