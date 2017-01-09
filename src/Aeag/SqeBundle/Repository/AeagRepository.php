<?php

namespace Aeag\SqeBundle\Repository;

use Doctrine\ORM\EntityRepository;

class AeagRepository extends EntityRepository {
    
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        $result = $this->findBy($criteria, $orderBy);
        if (count($result) > 1) {
            return -1;
        }
        return $result[0];
    }
        
    
    
}
