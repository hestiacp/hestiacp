<?php

declare(strict_types=1);

namespace App\Users\User\Infrastructure\Controller;

use App\Users\User\Application\Query\UserQueryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function var_dump;

class UserListController extends AbstractController
{
    public function __construct(private UserQueryRepository $userQueryRepository)
    {
    }

    #[Route(path: '/users', name: 'user_list')]
    #[IsGranted("ROLE_ADMIN")]
    public function userList(): Response
    {
        $users = $this->userQueryRepository->getUserList();

        return $this->render('user/list.html.twig', ['users' => $users->users]);
    }
}
