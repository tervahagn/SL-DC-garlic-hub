<?php

namespace App\Modules\Auth\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
	#[Route('/login', name: 'app_login')]
	public function login(AuthenticationUtils $authenticationUtils): Response
	{
		if ($this->getUser())
			return $this->redirectToRoute('home');

		return $this->render('auth/login.html.twig', [
			'last_username' =>  $authenticationUtils->getLastUsername(),
			'error' => $authenticationUtils->getLastAuthenticationError(),
		]);
	}

	#[Route('/logout', name: 'app_logout', methods: ['GET'])]
	public function logout(): void
	{
		throw new \Exception('Error: Logout should procceed through the firewall-system.');
	}
}
