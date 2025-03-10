<?php

declare(strict_types=1);

namespace App\Users\User\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class LoginController extends AbstractController
{
	#[Route('/login', name: 'user_login', methods: ['GET'])]
	public function login()
	{
		return $this->render('user/login.html.twig');
	}
}
