<?php

namespace Aeag\SqeBundle\Command;

//use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessRaiCommand extends ContainerAwareCommand  {

    protected function configure() {
        $this
                ->setName('rai:process')
                ->setDescription('Validation des RAI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('test');
    }
}
