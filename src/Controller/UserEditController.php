<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserEditController extends AbstractController
{
    #[Route('/user/edit', name: 'app_user_edit')]
    public function index(): Response
    {
        return $this->render('user_edit/index.html.twig', [
            'controller_name' => 'UserEditController',
        ]);
    }
}
