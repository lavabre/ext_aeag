<?php

/**
 * Description of SousThemeRepository
 *
 * @author lavabre
 */

namespace Aeag\DieBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class SousThemeRepository
 * @package Aeag\DieBundle\Repository
 */
class SousThemeRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getSousThemes() {
        $query = "select c";
        $query = $query . " from Aeag\DieBundle\Entity\SousTheme c";
        $query = $query . " order by c.theme";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getSousThemesByTheme($theme) {
        $query = "select c";
        $query = $query . " from Aeag\DieBundle\Entity\SousTheme c";
        $query = $query . " where c.theme = ':theme";
        $query = $query . " order by c.theme";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('theme', $theme->getId());
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getSousThemeById($id) {
        $query = "select c";
        $query = $query . " from Aeag\DieBundle\Entity\SousTheme c";
        $query = $query . " where c.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}
