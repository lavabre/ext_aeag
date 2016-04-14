<?php

namespace Aeag\EdlBundle\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\MaxLength;

class Contact {

    protected $name;
    protected $email;
    protected $subject;
    protected $body;

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function setSubject($subject) {
        $this->subject = $subject;
    }

    public function getBody() {
        return $this->body;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata) {
        $metadata->addPropertyConstraint('name', new NotBlank(array(
                    'message' => 'Votre nom est obligatoire !'
                )));

        $metadata->addPropertyConstraint('email', new Email(array(
                    'message' => 'Votre adresse email est incorrecte !'
                )));

        $metadata->addPropertyConstraint('subject', new NotBlank(array(
                    'message' => 'l\'objet est obligatoire !'
                )));
        $metadata->addPropertyConstraint('subject', new MaxLength(100));

        $metadata->addPropertyConstraint('body', new MinLength(50));
    }

}
