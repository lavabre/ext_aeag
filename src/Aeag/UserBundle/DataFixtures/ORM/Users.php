<?php

namespace Aeag\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Aeag\UserBundle\Entity\User;

/**
 * Description of Users
 *
 * @author lavabre
 */
class Users implements FixtureInterface {

    public function load(ObjectManager $manager) {

        // les nom des utilisateurs à créer
        $noms = array('admin', 'lavabre');
        $emails= array('jle@eau-adour-garonne.fr','lajoem@free.fr');
        foreach ($noms as $i => $nom) {
            // on crée l'utilisateur
            $users[$i] = new User;
            // le nom d'ututilisateur et le mot de passe sont identique
            $users[$i]->setUsername($nom);
            $users[$i]->setPassword($nom);
            // le sel et les rôles sont vides pour l'instant
            $users[$i]->setSalt('');
            $users[$i]->setEnabled(true);
            if ($nom == 'admin') {
                $users[$i]->setRoles(array('ROLE_ADMIN'));
                $users[$i]->setPassword('aeag31');
                $users[$i]->setEmail($emails[0]);
                $users[$i]->setEmailCanonical($emails[0]);
            } else {
                $users[$i]->setRoles(array('ROLE_AEAG'));
                $users[$i]->setPassword('malula');
                $users[$i]->setEmail($emails[1]);
                $users[$i]->setEmailCanonical($emails[1]);
            }
            // on le persiste
            $manager->persist($users[$i]);
        }
        // on déclanche l'enregistrement
        $manager->flush();
    }

}
