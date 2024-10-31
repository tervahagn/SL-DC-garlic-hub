<?php

namespace App\Modules\Companies\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class Legals extends AbstractController
{
	#[Route('/legals', name: 'legals')]
	public function legals(): Response
	{
		return $this->render('companies/legals.html.twig');
	}
}