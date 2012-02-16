<?php

namespace Tedivm\StashBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('TedivmStashBundle:Default:index.html.twig');
    }
}
