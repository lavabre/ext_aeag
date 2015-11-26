<?php
/**
 * Description de Theme
 *
 * @author lavabre
 */

namespace Aeag\DieBundle\Entity;
use Doctrine\ORM\EntityRepository;

class ThemeRepository extends EntityRepository
{
	public function myFindOne($id)
	{
	  // On passe par le QueryBuilder vide de l'EntityManager pour l'exemple
            $qb = $this->_em->createQueryBuilder();

            $qb->select('a')
            ->from('AeagDieBundle:Theme', 'a')
            ->where('a.id = :id')
                ->setParameter('id', $id);

            return $qb;

	}
}
 

