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
class PgProgWebusersRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getPgProgWebusers() {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query = $query . " order by p.nom";
        $qb = $this->_em->createQuery($query);
        //print_r($query);
        return $qb->getResult();
    }

    /**
     * @return array
     */
    public function getPgProgWebusersByid($id) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query = $query . " where p.id = :id";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('id', $id);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     /**
     * @return array
     */
    public function getPgProgWebusersByExtid($extId) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query = $query . " where p.extId = :extId";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('extId', $extId);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }

    public function getPgProgWebusersByNom($nom) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query = $query . " where p.nom = :nom";
        $qb = $this->_em->createQuery($query);
         $qb->setParameter('nom', $nom);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
    public function getPgProgWebusersByLoginPassword($login,$pwd) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query = $query . " where p.login= :login";
        $query = $query . " and p.pwd= :pwd";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('login', $login);
        $qb->setParameter('pwd', $pwd);
        //print_r($query);
        return $qb->getOneOrNullResult();
    }
    
     public function getPgProgWebusersByPrestataire($prestataire) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query = $query . " where p.prestataire = :prestataire";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('prestataire', $prestataire->getAdrCorId());
        //print_r($query);
         return $qb->getResult();
    }
    
    public function getPgProgWebusersByTypeUser($typeUser) {
        $query = "select p";
        $query = $query . " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query = $query . " where p.typeUser = :typeUser";
        $query = $query . " order by p.nom";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('typeUser', $typeUser);
        //print_r($query);
        return $qb->getResult();
    }
    
        public function getSuppportByPrestataire($pgRefCorresPresta) {
//          $query = "select distinct sup";
//          $query .= " from Aeag\SqeBundle\Entity\PgProgLotParamAn pan,";
//          $query .= "         Aeag\SqeBundle\Entity\\PgProgLotGrparAn gran,";
//          $query .= "         Aeag\SqeBundle\Entity\PgProgGrpParamRef gref,";
//          $query .= "         Aeag\SqeBundle\Entity\PgProgLotAn lan,";
//          $query .= "         Aeag\SqeBundle\Entity\PgSandreSupports sup";
//          $query .= " where lan.phase > 6"; 
//          $query .= " and gran.id = pan.grparan";
//          $query .= " and gref.id = gran.grparRef";
//          $query .= " and lan.id = gran.lotan";
//           $query .= " and sup.codeSupport = gref.support";
//          $query .= " and gref.support is not null";
//          $query .= " and pan.prestataire = :prestataire";
            
          $query = "select distinct sup";
          $query .= " from Aeag\SqeBundle\Entity\PgCmdPrelev prel,";
          $query .= "         Aeag\SqeBundle\Entity\\PgCmdDemande dem,";
          $query .= "         Aeag\SqeBundle\Entity\PgSandreSupports sup";
          $query .= " where dem.id =  prel.demande"; 
          $query .= " and sup.codeSupport = prel.codeSupport";
          $query .= " and prel.codeSupport is not null";
          $query .= " and dem.prestataire = :prestataire";
        
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('prestataire', $pgRefCorresPresta->getAdrCorId()); 
        //print_r($query);
        return $qb->getResult();
    }
    
    public function getNotAdminPgProgWebusersByProducteur($producteur) {
        $query = "select p";
        $query .= " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query .= " where p.producteur = :producteur";
        $query .= " and p.typeUser <> 'ADMIN'";
        $query .= " order by p.nom";
        $qb = $this->_em->createQuery($query);
        
        $qb->setParameter('producteur', $producteur);

        return $qb->getResult();
    }
    
    public function getNotAdminPgProgWebusersByProducteurAndMarche($producteur, $marche) {
        $query = "select p";
        $query .= " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query .= " join Aeag\SqeBundle\Entity\PgProgMarcheUser mu with mu.webuser = p.id";
        $query .= " where p.producteur = :producteur";
        $query .= " and p.typeUser <> 'ADMIN'";
        $query .= " and mu.marche = :marche";
        $query .= " order by p.nom";
        $qb = $this->_em->createQuery($query);
        
        $qb->setParameter('producteur', $producteur);
        $qb->setParameter('marche', $marche);

        return $qb->getResult();
    }
    
    public function getNotAdminPgProgWebusersByProducteurAndMarcheAndTypeMilieu($producteur, $marche, $typeMilieu) {
        $query = "select p";
        $query .= " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query .= " join Aeag\SqeBundle\Entity\PgProgMarcheUser mu with mu.webuser = p.id";
        $query .= " left join Aeag\SqeBundle\Entity\PgProgWebuserTypmil wt with wt.webuser = p.id";
        $query .= " where p.producteur = :producteur";
        $query .= " and p.typeUser <> 'ADMIN'";
        $query .= " and mu.marche = :marche";
        $query .= " and (wt.typmil = :typemilieu or wt.typmil IS NULL)";
        $query .= " order by p.nom";
        $qb = $this->_em->createQuery($query);
        
        $qb->setParameter('producteur', $producteur);
        $qb->setParameter('marche', $marche);
        $qb->setParameter('typemilieu', $typeMilieu);

        return $qb->getResult();
    }
    
    public function getPgProgWebusersByPrestataireAndTypeMilieu($prestataire, $typeMilieu) {
        $query = "select p";
        $query .= " from Aeag\SqeBundle\Entity\PgProgWebusers p";
        $query .= " left join Aeag\SqeBundle\Entity\PgProgWebuserTypmil wt with wt.webuser = p.id";
        $query .= " where p.prestataire = :presta";
        $query .= " and (wt.typmil = :typemilieu or wt.typmil IS NULL)";
        $qb = $this->_em->createQuery($query);
        $qb->setParameter('presta', $prestataire);
        $qb->setParameter('typemilieu', $typeMilieu);
        //print_r($query);
        return $qb->getResult();
    }
   

}

