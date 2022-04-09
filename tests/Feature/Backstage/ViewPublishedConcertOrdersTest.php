<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Carbon\Carbon;
use Database\Factories\ConcertFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Database\Factories\OrderFactory;
use Tests\TestCase;

class ViewPublishedConcertOrdersTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function a_promoter_can_view_the_orders_of_their_own_published_concert()
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Concert $concert */
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.published-concert-orders.index');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    function a_promoter_can_view_the_10_most_recent_orders_for_their_concert()
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Concert $concert */
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $oldOrder = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('11 days ago')], 2);
        $recentOrder1 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('10 days ago')], 2);
        $recentOrder2 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('9 days ago')], 2);
        $recentOrder3 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('8 days ago')], 2);
        $recentOrder4 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('7 days ago')], 2);
        $recentOrder5 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('6 days ago')], 2);
        $recentOrder6 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('5 days ago')], 2);
        $recentOrder7 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('4 days ago')], 2);
        $recentOrder8 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('3 days ago')], 2);
        $recentOrder9 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('2 days ago')], 2);
        $recentOrder10 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('1 days ago')], 2);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->data('orders')->assertNotContains($oldOrder);

        // make sure orders are all there and in right order (the newest first)
        $response->data('orders')->assertEquals(
            [
                $recentOrder10,
                $recentOrder9,
                $recentOrder8,
                $recentOrder7,
                $recentOrder6,
                $recentOrder5,
                $recentOrder4,
                $recentOrder3,
                $recentOrder2,
                $recentOrder1,
            ]
        );
    }

    /** @test */
    function a_promoter_cannot_view_the_orders_of_unpublished_concerts()
    {
        $user = User::factory()->create();
        $concert = ConcertFactory::createUnpublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /** @test */
    function a_promoter_cannot_view_the_orders_of_another_published_concert()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $concert = ConcertFactory::createPublished(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /** @test */
    function a_guest_cannot_view_the_orders_of_any_published_concert()
    {
        $concert = ConcertFactory::createPublished();

        $response = $this->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertRedirect('/login');
    }
}
