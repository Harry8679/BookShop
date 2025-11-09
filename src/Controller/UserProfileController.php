<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UserProfileController extends AbstractController
{
    #[Route('/profil', name: 'app_user_profile')]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à votre profil.');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }
}
