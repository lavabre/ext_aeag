<?php

/**
 * Description of CorrespndantRepository
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CorrespondantRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getCorrespondants() {
        $query = "select c";
        $query = $query . " from Aeag\AeagBundle\Entity\Correspondant c";
        $query = $query . " order by c.identifiant";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getCorrespondantById($id) {

        $query = "select  c";
        $query = $query . " from Aeag\AeagBundle\Entity\Correspondant c";
        $query = $query . " where c.id = :id";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getCorrespondant($identifiant) {

        $query = "select  c";
        $query = $query . " from Aeag\AeagBundle\Entity\Correspondant c";
        $query = $query . " where c.identifiant = :identifiant";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('identifiant', $identifiant);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getCorrespondantByAdr1Cp($adr1, $cp) {

        $query = "select  c";
        $query = $query . " from Aeag\AeagBundle\Entity\Correspondant c";
        $query = $query . " where c.adr1 = :adr1";
        $query = $query . " and c.cp = :cp";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('adr1', $adr1);
        $qb->setParameter('cp', $cp);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getCorrespondantByCorId($corId) {

        $query = "select  c";
        $query = $query . " from Aeag\AeagBundle\Entity\Correspondant c";
        $query = $query . " where c.corId = :corId";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('corId', $corId);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getCorrespondantBySiret($siret) {

        $query = "select  c";
        $query = $query . " from Aeag\AeagBundle\Entity\Correspondant c";
        $query = $query . " where c.siret = :siret";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('siret', $siret);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getListeDossiers($annee) {

        $query = "select distinct c.id, c.identifiant, c.adr1, c.adr2";
        $query = $query . "  from Aeag\AeagBundle\Entity\Correspondant c,";
        $query = $query . "  Aeag\AeagBundle\Entity\Ouvrages o,";
        $query = $query . "  Aeag\AeagBundle\Entity\CorrespondantsOuvrages co";
        $query = $query . " where c.id = co.correspondant";
        $query = $query . " and o.id = co.ouvrage";
        $query = $query . " and (o.anneeFin = ''  or o.anneeFin >= :annee)";
        $query = $query . " and o.type = 'STEP'";
        $query = $query . " order by c.identifiant";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        //print_r($query);
        return $qb->getResult();
    }

    public function getListeCorrespondantDossiers($annee, $user) {

        $query = "select distinct c.id, c.identifiant, c.adr1, c.adr2";
        $query = $query . " from Aeag\AeagBundle\Entity\Correspondants c,";
        $query = $query . "  Aeag\AeagBundle\Entity\Ouvrages o,";
        $query = $query . "  Aeag\AeagBundle\Entity\CorrespondantsOuvrages co";
        $query = $query . " where c.id = co.correspondant";
        $query = $query . " and o.id = co.ouvrage";
        $query = $query . " and (o.anneeFin = ''  or o.anneeFin >= :annee)";
        $query = $query . " and o.type = 'STEP'";
        $query = $query . " and u.id = :user";
        $query = $query . " order by c.identifiant";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        $qb->setParameter('user', $user->getId());
        return $qb->getResult();
    }

    public function getListeOuvrages($annee, $correspondant) {


        $query = "select o";
        $query = $query . " from Aeag\AeagBundle\Entity\CorrespondantsOuvrages co";
        $query = $query . " , Aeag\AeagBundle\Entity\Ouvrages o";
        $query = $query . " where co.correspondant = :correspondant";
        $query = $query . " and co.ouvrage = o.id";
        $query = $query . " and (o.anneeFin = ''  or o.anneeFin >= :annee)";
        $query = $query . " and o.type = 'STEP'";
        $query = $query . " order by o.numero";
        //print_r($query);
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('annee', $annee);
        $qb->setParameter('correspondant', $correspondant);
        return $qb->getResult();
    }

}
