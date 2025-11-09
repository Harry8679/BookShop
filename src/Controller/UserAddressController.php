<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserAddressController extends AbstractController
{
    #[Route('/user/address', name: 'app_user_address')]
    public function index(): Response
    {
        return $this->render('user_address/index.html.twig', [
            'controller_name' => 'UserAddressController',
        ]);
    }
}
