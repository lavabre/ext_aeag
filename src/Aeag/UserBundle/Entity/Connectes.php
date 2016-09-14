<?php

namespace Aeag\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Connectes
 *
 * @ORM\Table(name="connectes")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\UserBundle\Repository\ConnectesRepository")
 */
class Connectes {

      /**
      * @ORM\Id
     * @ORM\Column(type="string", length=15, name="ip", nullable=false)
     */
    protected $ip;

    
       /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $time;
    
   
    /**
     *
     */
    public function __construct() {
        $this->setTime(time());
    }

    function getIp() {
        return $this->ip;
    }

    function getTime() {
        return $this->time;
    }

    function setIp($ip) {
        $this->ip = $ip;
    }

    function setTime($time) {
        $this->time = $time;
    }

}
