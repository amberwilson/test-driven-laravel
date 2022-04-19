<?php

namespace Tests\Browser;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Stripe\Account;
use Tests\DuskTestCase;

class ConnectWithStripeTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function connecting_a_stripe_account_successfully()
    {
        $user = User::factory()->create(
            [
                'stripe_account_id' => null,
                'stripe_access_token' => null,
            ]
        );

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/backstage/stripe-connect/connect')
                ->clickLink('Connect with Stripe')
                ->assertUrlIs('https://connect.stripe.com/oauth/v2/authorize')
                ->assertQueryStringHas('response_type', 'code')
                ->assertQueryStringHas('scope', 'read_write')
                ->assertQueryStringHas('client_id', config('services.stripe.client_id'))
                ->press('Skip this form')
                ->waitForReload()
                ->assertRouteIs('backstage.concerts.index');

            tap($user->fresh(), static function (User $user) {
                self::assertNotNull($user->stripe_account_id);
                self::assertNotNull($user->stripe_access_token);

                $connectedAccount = Account::retrieve(null, [
                    'api_key' => $user->stripe_access_token,
                ]);

                self::assertEquals($connectedAccount->id, $user->stripe_account_id);
            });
        });
    }
}
