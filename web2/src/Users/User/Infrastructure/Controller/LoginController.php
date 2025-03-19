<?php

declare(strict_types=1);

namespace App\Users\User\Infrastructure\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
	public function __construct(private readonly AuthenticationUtils $authenticationUtils)
	{
	}

	#[Route(path: '/login', name: 'user_login')]
	public function login(): Response
	{
		return $this->render('user/login.html.twig', [
			// last username entered by the user (if any)
			'last_username' => $this->authenticationUtils->getLastUsername(),
			// last authentication error (if any)
			'error' => $this->authenticationUtils->getLastAuthenticationError(),
		]);
	}

	/**
	 * This is the route the user can use to logout.
	 *
	 * But, this will never be executed. Symfony will intercept this first
	 * and handle the logout automatically. See logout in app/config/security.yaml
	 */
	#[Route(path: '/logout', name: 'user_logout')]
	public function logout(): never
	{
		throw new Exception('This should never be reached!');
	}
}
