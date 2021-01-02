<?php


namespace Tests\Unit\Billing;


use App\Billing\PaymentGateway;
use App\Billing\StripePaymentGateway;
use Tests\TestCase;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    protected function getPaymentGateway(): PaymentGateway
    {
        return new StripePaymentGateway(config('services.stripe.secret'));
    }
}
