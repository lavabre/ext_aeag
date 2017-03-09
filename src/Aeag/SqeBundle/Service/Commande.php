<?php

namespace Aeag\SqeBundle\Service;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Exécution d'une/des commande(s)
 * *
 */
class Commande {

    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function setContainer(\Symfony\Component\DependencyInjection\Container $container) {
        $this->container = $container;
    }

    public function runCommand($command, $arguments = array()) {
        $kernel = $this->container->get('kernel');
        $app = new Application($kernel);

        $args = array_merge(array('command' => $command), $arguments);

        $input = new ArrayInput($args);
        $output = new NullOutput();

        return $app->doRun($input, $output);
    }

}
