<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\ForceStripeAccount;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ForceStripeAccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_without_a_stripe_account_a_forced_to_connect_with_stripe(): void
    {
        $this->be(
            User::factory()->create(
                [
                    'stripe_account_id' => null,
                ]
            )
        );

        $middleware = new ForceStripeAccount();

        $response = $middleware->handle(new Request(), static function () {
            self::fail('Next middleware was called when it should not have been.');
        });

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertEquals(route('backstage.stripe-connect.connect'), $response->getTargetUrl());
    }

    /** @test */
    public function users_with_a_stripe_account_can_continue(): void
    {
        $this->be(
            User::factory()->create(
                [
                    'stripe_account_id' => 'stripe-account-id',
                ]
            )
        );

        $middleware = new ForceStripeAccount();

        $request = new Request();

        $next = new class {
            public bool $called = false;

            public function __invoke(Request $request): Request
            {
                $this->called = true;

                return $request;
            }
        };

        $response = $middleware->handle($request, $next);

        self::assertTrue($next->called);
        self::assertSame($request, $response);
    }

    /** @test */
    public function middleware_is_applied_to_all_backstage_routes(): void
    {
        $routes = [
            'backstage.concerts.new',
            'backstage.concerts.index',
            'backstage.concerts.store',
            'backstage.concerts.edit',
            'backstage.concerts.update',
            'backstage.published-concerts.store',
            'backstage.published-concert-orders.index',
            'backstage.concert-messages.new',
            'backstage.concert-messages.store'
        ];

        foreach ($routes as $route) {
            self::assertContains(
                ForceStripeAccount::class,
                Route::getRoutes()->getByName($route)->gatherMiddleware()
            );
        }
    }
}
