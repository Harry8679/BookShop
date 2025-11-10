<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/paiement')]
class PaymentController extends AbstractController
{
    #[Route('/checkout', name: 'app_payment_checkout')]
    public function checkout(RequestStack $requestStack, EntityManagerInterface $em, UrlGeneratorInterface $urlGen): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $session = $requestStack->getSession();
        $orderId = $session->get('current_order_id');

        // 🔹 Vérifie que la commande existe
        $order = $em->getRepository(Order::class)->find($orderId);

        if (!$order || $order->getUser() !== $user) {
            $this->addFlash('danger', 'Aucune commande valide trouvée.');
            return $this->redirectToRoute('app_cart_index');
        }

        // 🔹 Clé secrète Stripe (configurée dans .env.local)
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        // 🔹 Liste des articles
        $lineItems = [];
        foreach ($order->getItems() as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) ($item->getPrice() * 100), // en centimes
                    'product_data' => [
                        'name' => $item->getProduct()->getName(),
                    ],
                ],
                'quantity' => $item->getQuantity(),
            ];
        }

        // 🔹 Frais de livraison éventuels
        if ($order->getDeliveryMethod()) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) ($order->getDeliveryMethod()->getPrice() * 100),
                    'product_data' => [
                        'name' => 'Frais de livraison - ' . $order->getDeliveryMethod()->getName(),
                    ],
                ],
                'quantity' => 1,
            ];
        }

        // ✅ Génère les URLs avec l'ID de la commande
        $successUrl = $urlGen->generate(
            'app_payment_success',
            ['id' => $order->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $cancelUrl = $urlGen->generate(
            'app_payment_cancel',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // 🔹 Crée la session de paiement Stripe
        $checkoutSession = Session::create([
            'customer_email' => $user->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
        ]);

        // 🔹 Met à jour le statut avant le paiement
        $order->setStatus(Order::STATUS_PENDING);
        $em->flush();

        // 🔹 Redirige vers la page Stripe Checkout
        return $this->redirect($checkoutSession->url);
    }

    #[Route('/success/{id}', name: 'app_payment_success')]
    public function success(int $id, EntityManagerInterface $em): Response
    {
        $order = $em->getRepository(Order::class)->find($id);

        if (!$order) {
            $this->addFlash('danger', 'Commande introuvable.');
            return $this->redirectToRoute('app_cart_index');
        }

        // ✅ Marque la commande comme payée
        $order->setStatus(Order::STATUS_PAID);
        $em->flush();

        return $this->render('payment/success.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/cancel', name: 'app_payment_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé.');
        return $this->redirectToRoute('app_cart_index');
    }
}