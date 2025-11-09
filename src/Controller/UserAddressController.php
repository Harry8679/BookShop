<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/profil/adresses')]
class UserAddressController extends AbstractController
{
    #[Route('/', name: 'app_user_addresses')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $addresses = $em->getRepository(Address::class)->findBy(['user' => $user]);

        return $this->render('user/address/index.html.twig', [
            'addresses' => $addresses,
        ]);
    }

    #[Route('/ajouter', name: 'app_user_address_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($user);
            $em->persist($address);
            $em->flush();

            $this->addFlash('success', '✅ Adresse ajoutée avec succès !');
            return $this->redirectToRoute('app_user_addresses');
        }

        return $this->render('user/address/add.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/modifier/{id}', name: 'app_user_address_edit')]
    public function edit(Address $address, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ADDRESS_EDIT', $address);
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✅ Adresse modifiée avec succès !');
            return $this->redirectToRoute('app_user_addresses');
        }

        return $this->render('user/address/edit.html.twig', [
            'form' => $form,
            'address' => $address,
        ]);
    }

    #[Route('/supprimer/{id}', name: 'app_user_address_delete')]
    public function delete(Address $address, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ADDRESS_EDIT', $address);
        $em->remove($address);
        $em->flush();

        $this->addFlash('success', '🗑️ Adresse supprimée avec succès.');
        return $this->redirectToRoute('app_user_addresses');
    }
}