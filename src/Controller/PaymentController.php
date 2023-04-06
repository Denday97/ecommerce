<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    #[Route('/payment', name: 'payment')]
    public function index(SessionInterface $session, ProductsRepository $product): Response
    {
        $panier = $session->get('panier', []);
        return $this->render('payment/index.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
    #[Route('/checkout', name: 'checkout')]
    public function checkout($stripeSK): Response
    {
        \Stripe\Stripe::setApiKey($stripeSK);

        $session = \Stripe\Checkout\Session::create([
            'line_items' => [[
              'price_data' => [
                'currency' => 'eur',
                'quantity' => 1,
                'name' => 't-shirt'
              ],
              'unit_amount' => 2000
            ]],
            'mode' => 'payment',
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
          ]);
          dd($session);
        
    }
}
