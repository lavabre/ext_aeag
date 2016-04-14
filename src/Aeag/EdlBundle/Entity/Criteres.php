<?php

namespace Aeag\EdlBundle\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\MaxLength;

class Criteres {

    protected $codecle;
    protected $massecle;
    protected $deptcle;
    protected $typecle;
    protected $territoirecle;

    public function getCodecle() {
        return $this->codecle;
    }

    public function setCodecle($codecle) {
        $this->codecle = $codecle;
    }

    public function getMassecle() {
        return $this->massecle;
    }

    public function setMassecle($massecle) {
        $this->massecle = $massecle;
    }

    public function getDeptcle() {
        return $this->deptcle;
    }

    public function setDeptcle($deptcle) {
        $this->deptcle = $deptcle;
    }

    public function getTypecle() {
        return $this->typecle;
    }

    public function setTypecle($typecle) {
        $this->typecle = $typecle;
    }

    public function getTerritoirecle() {
        return $this->territoirecle;
    }

    public function setTerritoirecle($territoirecle) {
        $this->territoirecle = $territoirecle;
    }

 
   
}
