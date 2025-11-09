<?php

namespace App\Controller;

use App\Form\UserPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class UserPasswordController extends AbstractController
{
    #[Route('/profil/modifier-mot-de-passe', name: 'app_user_password_edit')]
    public function editPassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->getUser();

        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Vérification de l'ancien mot de passe
            if (!$passwordHasher->isPasswordValid($user, $data['oldPassword'])) {
                $this->addFlash('danger', '❌ L’ancien mot de passe est incorrect.');
            } else {
                // Mise à jour du mot de passe
                $user->setPassword($passwordHasher->hashPassword($user, $data['newPassword']));
                $em->flush();

                $this->addFlash('success', '✅ Votre mot de passe a été mis à jour avec succès.');
                return $this->redirectToRoute('app_user_profile');
            }
        }

        return $this->render('user/edit_password.html.twig', [
            'form' => $form,
        ]);
    }
}