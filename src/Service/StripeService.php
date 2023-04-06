<?php

namespace App\Service;

use Stripe\Stripe;

class StripePayment
 {
    public function __construct( private string
    $clientSecret)
    {
        Stripe::setApiKey($this->clientSecret);
        
    }

    public function startPaiment()
    {

    }
}