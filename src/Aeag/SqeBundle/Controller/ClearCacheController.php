<?php

namespace Aeag\SqeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;

class ClearCacheController extends Controller{
    //put your code here
    
    public function indexAction() {
        /*$dirname = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
        if ((strpos($dirname, 'dev') !== false) || (strpos($dirname, 'preprod') !== false)) {
            $script = 'cc-vg';
        } else {
            $script = 'cc-prod-vg';
        }
           
        //$cmd = $dirname.'/'.$script;
        
        $cmd = 'php '.$dirname.'/app/console assets:install '.$dirname.'/web';
        echo $cmd;
        
        $process = new Process($cmd);

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'ERR > '.$buffer.'</br>';
            } else {
                echo 'OUT > '.$buffer.'</br>';
            }
        });*/
        
        $fs = new Filesystem();
        $fs->remove($this->container->getParameter('kernel.cache_dir'));
        
        //return $this->render('AeagSqeBundle:ClearCache:index.html.twig', array());
        return new Response();
    }
}
