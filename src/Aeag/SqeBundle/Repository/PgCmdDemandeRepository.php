<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PgCmdDemandeRepository
 * @package Aeag\SqeBundle\Repository
 */
class PgCmdDemandeRepository extends EntityRepository {
    //put your code here
    
    public function getNbReponseByDemande($demande) {
        $query = "SELECT count(pean.periode)";
        $query .= " FROM Aeag\SqeBundle\Entity\PgCmdDemande dmd, Aeag\SqeBundle\Entity\PgProgLotPeriodeAn pean, Aeag\SqeBundle\Entity\PgRefCorresPresta presta";
        $query .= " WHERE dmd.lotan = pean.lotan";
        $query .= " AND presta.adrCorId = dmd.prestataire";
        $query .= " AND pean.codeStatut <> 'INV'";
        $query .= " AND dmd.id = :demande";
        $query .= " GROUP BY pean.lotan";

        $qb = $this->_em->createQuery($query);
        $qb->setParameter('demande', $demande);
        
        return $qb->getOneOrNullResult();

    }
}
