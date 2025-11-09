<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CartExtension extends AbstractExtension
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_cart_quantity', [$this, 'getCartQuantity']),
        ];
    }

    public function getCartQuantity(): int
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        // Si ton panier est un tableau [id => quantité]
        return array_sum($cart);
    }
}