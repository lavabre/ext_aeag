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
        $query = $query . " order by c.theme";
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
        $query = $query . " where c.theme = '" . $theme . "'";
        $query = $query . " order by c.theme";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getThemeById($id) {
        $query = "select c";
        $query = $query . " from Aeag\DieBundle\Entity\Theme c";
        $query = $query . " where c.id = " . $id;
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}
