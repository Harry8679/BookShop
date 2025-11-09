<?php

namespace App\Controller;

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
    #[Route('/add/{id}', name: 'app_cart_add', methods: ['GET'])]
    public function add(Product $product, RequestStack $requestStack): JsonResponse
    {
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        $id = $product->getId();
        if (!isset($cart[$id])) {
            $cart[$id] = 1;
        } else {
            $cart[$id]++;
        }

        $session->set('cart', $cart);

        return new JsonResponse([
            'success' => true,
            'cartCount' => array_sum($cart), // 🔹 total d’articles
        ]);
    }

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
            'cartCount' => array_sum($cart), // 🔹 renvoyé pour MAJ du badge
        ]);
    }
}