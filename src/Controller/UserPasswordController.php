<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserPasswordController extends AbstractController
{
    #[Route('/user/password', name: 'app_user_password')]
    public function index(): Response
    {
        return $this->render('user_password/index.html.twig', [
            'controller_name' => 'UserPasswordController',
        ]);
    }
}
