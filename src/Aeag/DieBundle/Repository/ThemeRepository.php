<?php

/**
 * Description of ThemeRepository
 *
 * @author lavabre
 */

namespace Aeag\DieBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ThemeRepository
 * @package Aeag\DieBundle\Repository
 */
class ThemeRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getThemes() {
        $query = "select c";
        $query = $query . " from Aeag\DieBundle\Entity\Theme c";
        $query = $query . " order by c.ordre";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getThemesByTheme($theme) {
        $query = "select c";
        $query = $query . " from Aeag\DieBundle\Entity\Theme c";
        $query = $query . " where c.theme = :theme";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('theme', $theme->getId());
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    /**
     * @return array
     */
    public function getThemeById($id) {
        $query = "select c";
        $query = $query . " from Aeag\DieBundle\Entity\Theme c";
        $query = $query . " where c.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}
