<?php

/**
 * Description of CorrespndantRepository
 *
 * @author lavabre
 */

namespace Aeag\AgentBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class AgentRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getAgents() {
        $query = "select c";
        $query = $query . " from Aeag\AgentBundle\Entity\Agent c";
        $query = $query . " where c.dateSortie is null";
        $query = $query . " order by c.nom, c.prenom";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    public function getAgentBymatricule($matricule) {

        $query = "select  c";
        $query = $query . " from Aeag\AgentBundle\Entity\Agent c";
        $query = $query . " where c.matricule = " . $matricule;

        $qb = $this->_em->createQuery($query);

        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getRechercheEtatAgent($nomPrenom) {
        
        $query = "call PB_RECHERCHE_ETAT_AGENT('". $nomPrenom . "')";
        return $this->getEntityManager()
                ->getConnection()
                ->query($query);
 
    }
    
    public function getAgentResultats() {
        $query = "select c";
        $query = $query . " from Aeag\AgentBundle\Entity\AgentResultat c";
        $query = $query . " order by c.nomPrenom";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getRechercheEtatService($service) {

       $query = "call PB_RECHERCHE_ETAT_SERVICE('". $service . "')";
        return $this->getEntityManager()
                ->getConnection()
                ->query($query);
    }
    
    public function getServiceResultats() {
        $query = "select c";
        $query = $query . " from Aeag\AgentBundle\Entity\ServiceResultat c";
        $query = $query . " order by c.nomPrenom";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

}
