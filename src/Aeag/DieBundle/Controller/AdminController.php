<?php

namespace Aeag\DieBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{

	public function indexAction()
	{
		return $this->render('AeagDieBundle:Admin:index.html.twig');

			
	}
}
