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
class PgSandreHbNomemclaturesRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgSandreHbNomemclatures() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreHbNomemclatures p";
        $query = $query . " order by p.codeNomemclature, p.code_element";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreHbNomemclaturesByCodeNomemclature($codenomemclature) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreHbNomemclatures p";
        $query = $query . " where p.codeNomemclature = :codenomemclature";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codenomemclature', $codenomemclature);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreHbNomemclaturesByCodeNomemclatureCodeElement($codenomemclature, $codeElement) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreHbNomemclatures p";
        $query = $query . " where p.codeNomemclature = :codenomemclature";
        $query = $query . " and p.codeElement = :codeElement";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codenomemclature', $codenomemclature);
        $qb->setParameter('codeElement', $codeElement);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgSandreHbNomemclaturesByCodeNomemclatureCodeSupport($codenomemclature, $codeSupport) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreHbNomemclatures p";
        $query = $query . " where p.codeNomemclature = :codenomemclature";
        $query = $query . " and p.codeSupport = :codeSupport";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codenomemclature', $codenomemclature);
        $qb->setParameter('codeSupport', $codeSupport);
        //print_r($query);
        return $qb->getResult();
    }

    public function getPgSandreHbNomemclaturesByCodeNomemclatureCodeElementCodeSupport($codenomemclature, $codeElement, $codeSupport) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgSandreHbNomemclatures p";
        $query = $query . " where p.codeNomemclature = :codenomemclature";
        $query = $query . " and p.codeElement = :codeElement";
        $query = $query . " and p.codeSupport = :codeSupport";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('codenomemclature', $codenomemclature);
        $qb->setParameter('codeElement', $codeElement);
        $qb->setParameter('codeSupport', $codeSupport);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

}
