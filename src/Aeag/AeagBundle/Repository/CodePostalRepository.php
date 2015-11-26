<?php

/**
 * Description of CodePostalRepository
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class CodePostalRepository
 * @package Aeag\AeagBundle\Repository
 */
class CodePostalRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getCodePostals() {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\CodePostal d,";
        $query = $query . "      Aeag\AeagBundle\Entity\Commune c";
        $query = $query . " where c.id = d.commune";
        $query = $query . " order by c.commune";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getCodePostalsByDec() {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\CodePostal d,";
        $query = $query . "      Aeag\AeagBundle\Entity\Commune c";
        $query = $query . " where c.id = d.commune";
        $query = $query . " where d.dec = 'O";
        $query = $query . " order by c.commune";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getCodePostalById($id) {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\CodePostal d";
        $query = $query . " where d.id = " . $id;

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    /**
     * @param $commune
     * @return mixed
     */
    public function getCodePostalByCommune($commune) {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\CodePostal d";
        $query = $query . " where d.commune = " . $commune;

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $commune
     * @return mixed
     */
    public function getCodePostalByCp($cp) {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\CodePostal d";
        $query = $query . " where d.cp = '" . $cp . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $commune
     * @return mixed
     */
    public function getCodePostalByCommuneCp($commune, $cp) {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\CodePostal d";
        $query = $query . " where d.commune = " . $commune;
        $query = $query . " and d.cp = '" . $cp . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    /**
     * @param $commune
     * @return mixed
     */
    public function getCodePostalByCpAcheminement($cp, $acheminement) {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\CodePostal d";
        $query = $query . " where d.cp = '" . $cp . "'";
        $query = $query . " and d.acheminement = '" . $acheminement . "'";

        $qb = $this->_em->createQuery($query);

       //print_r($query);
        return $qb->getResult();
    }

    /**
     * @param $commune
     * @return mixed
     */
    public function getCodePostalByCpAcheminementLocalite($cp, $acheminement, $localite) {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\CodePostal d";
        $query = $query . " where d.cp = '" . $cp . "'";
        $query = $query . " and d.acheminement = '" . $acheminement . "'";
        $query = $query . " and d.localite = '" . $localite . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    /**
     * @param $commune
     * @return mixed
     */
    public function getCodePostalByCommuneCpAcheminementLocalite($commune, $cp, $acheminement, $localite) {

        $query = "select d";
        $query = $query . " from Aeag\AeagBundle\Entity\CodePostal d";
        $query = $query . " where d.commune = " . $commune;
        $query = $query . " and d.cp = '" . $cp . "'";
        $query = $query . " and d.acheminement = '" . $acheminement . "'";
        $query = $query . " and d.localite = '" . $localite . "'";

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}
