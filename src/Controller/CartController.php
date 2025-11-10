<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier')]
class CartController extends AbstractController
{
    // 🛒 AJOUTER UN PRODUIT AU PANIER
    #[Route('/add/{id}', name: 'app_cart_add', methods: ['GET'])]
    public function add(Product $product, RequestStack $requestStack): JsonResponse
    {
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        $id = $product->getId();
        $cart[$id] = ($cart[$id] ?? 0) + 1;

        $session->set('cart', $cart);

        return new JsonResponse([
            'success' => true,
            'cartCount' => array_sum($cart),
        ]);
    }

    // 🧾 AFFICHER LE PANIER
    #[Route('/', name: 'app_cart_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);
        $products = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $product = $em->getRepository(Product::class)->find($id);
            if ($product) {
                $subtotal = $product->getPrice() * $quantity;
                $products[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];
                $total += $subtotal;
            }
        }

        return $this->render('cart/index.html.twig', [
            'products' => $products,
            'total' => $total,
        ]);
    }

    // 🔄 METTRE À JOUR UNE QUANTITÉ (AJOUT / RETRAIT)
    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(int $id, RequestStack $requestStack): JsonResponse
    {
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);
        $quantity = (int) $_POST['quantity'];

        if ($quantity <= 0) {
            unset($cart[$id]);
        } else {
            $cart[$id] = $quantity;
        }

        $session->set('cart', $cart);

        return new JsonResponse([
            'success' => true,
            'cartCount' => array_sum($cart),
        ]);
    }

    // 💳 PASSER AU PAIEMENT (CRÉATION DE COMMANDE)
    #[Route('/checkout', name: 'app_cart_checkout')]
    public function checkout(RequestStack $requestStack, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // 🚫 Si pas connecté → redirection vers la page de connexion
        if (!$user) {
            $this->addFlash('warning', 'Veuillez vous connecter pour passer au paiement.');
            return $this->redirectToRoute('app_login');
        }

        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            $this->addFlash('info', 'Votre panier est vide 💔');
            return $this->redirectToRoute('app_cart_index');
        }

        // 🔹 Création de la commande
        $order = new Order();
        $order->setUser($user);
        $order->setStatus(Order::STATUS_PENDING);

        $total = 0;

        foreach ($cart as $id => $quantity) {
            $product = $em->getRepository(Product::class)->find($id);
            if (!$product) continue;

            $item = new OrderItem();
            $item->setProduct($product);
            $item->setQuantity($quantity);
            $item->setPrice($product->getPrice());

            $order->addItem($item);
            $total += $product->getPrice() * $quantity;
        }

        $order->setTotal($total);

        $em->persist($order);
        $em->flush();

        // Sauvegarder l’ID de la commande dans la session
        $session->set('current_order_id', $order->getId());

        // 🟣 Redirection vers le choix d’adresse
        return $this->redirectToRoute('app_order_address');
    }
}