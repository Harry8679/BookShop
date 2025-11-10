<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/mes-commandes')]
class UserOrderController extends AbstractController
{
    /**
     * 🟣 Liste des commandes de l'utilisateur connecté
     */
    #[Route('/', name: 'app_user_orders')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $orders = $em->getRepository(Order::class)->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('user/orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * 🟣 Détail d'une commande spécifique
     */
    #[Route('/{id}', name: 'app_user_order_show', requirements: ['id' => '\d+'])]
    public function show(Order $order): Response
    {
        $user = $this->getUser();

        // 🚫 Sécurité : l'utilisateur ne peut voir que ses propres commandes
        if ($order->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à cette commande.');
        }

        return $this->render('user/order_show.html.twig', [
            'order' => $order,
        ]);
    }
}