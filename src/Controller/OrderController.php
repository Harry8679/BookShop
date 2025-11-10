<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Address;
use App\Entity\DeliveryMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/commande')]
class OrderController extends AbstractController
{
    #[Route('/adresse', name: 'app_order_address')]
    public function selectAddress(RequestStack $requestStack, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $session = $requestStack->getSession();
        $orderId = $session->get('current_order_id');
        $order = $em->getRepository(Order::class)->find($orderId);

        if (!$order || $order->getUser() !== $user) {
            $this->addFlash('danger', 'Aucune commande en cours.');
            return $this->redirectToRoute('app_cart_index');
        }

        $addresses = $user->getAddresses();

        return $this->render('order/address.html.twig', [
            'order' => $order,
            'addresses' => $addresses,
        ]);
    }

    #[Route('/adresse/{id}/valider', name: 'app_order_set_address')]
    public function setAddress(int $id, RequestStack $requestStack, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $session = $requestStack->getSession();
        $orderId = $session->get('current_order_id');

        $order = $em->getRepository(Order::class)->find($orderId);
        $address = $em->getRepository(Address::class)->find($id);

        if (!$order || !$address || $order->getUser() !== $user || $address->getUser() !== $user) {
            $this->addFlash('danger', 'Adresse invalide ou non autorisée.');
            return $this->redirectToRoute('app_order_address');
        }

        $order->setShippingAddress($address);
        $em->flush();

        // Étape suivante
        return $this->redirectToRoute('app_order_delivery');
    }

    // 🚚 CHOIX DU TRANSPORTEUR
    #[Route('/livreur', name: 'app_order_delivery')]
    public function selectDelivery(RequestStack $requestStack, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $session = $requestStack->getSession();
        $orderId = $session->get('current_order_id');

        $order = $em->getRepository(Order::class)->find($orderId);
        if (!$order || $order->getUser() !== $user) {
            $this->addFlash('danger', 'Aucune commande en cours.');
            return $this->redirectToRoute('app_cart_index');
        }

        $deliveries = $em->getRepository(DeliveryMethod::class)->findBy(['isActive' => true]);

        return $this->render('order/delivery.html.twig', [
            'order' => $order,
            'deliveries' => $deliveries,
        ]);
    }

    #[Route('/livreur/{id}/valider', name: 'app_order_set_delivery')]
    public function setDelivery(int $id, RequestStack $requestStack, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $session = $requestStack->getSession();
        $orderId = $session->get('current_order_id');

        $order = $em->getRepository(Order::class)->find($orderId);
        $delivery = $em->getRepository(DeliveryMethod::class)->find($id);

        if (!$order || !$delivery || $order->getUser() !== $user) {
            $this->addFlash('danger', 'Transporteur invalide.');
            return $this->redirectToRoute('app_order_delivery');
        }

        // 🔹 On ajoute la méthode de livraison
        $order->setDeliveryMethod($delivery);
        $order->setTotal($order->getTotal() + $delivery->getPrice());
        $em->flush();

        // Étape suivante : paiement Stripe (à venir)
        return $this->redirectToRoute('app_payment_checkout');
    }
}