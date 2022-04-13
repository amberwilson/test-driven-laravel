<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Database\Factories\ConcertFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishConcertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_promoter_can_publish_their_own_concert(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Concert $concert */
        $concert = Concert::factory()->unpublished()->create(
            ['user_id' => $user->id, 'ticket_quantity' => 3]
        );

        $response = $this->actingAs($user)->post(
            '/backstage/published-concerts',
            ['concert_id' => $concert->id]
        );

        $concert = $concert->fresh();
        $response->assertRedirect('/backstage/concerts');
        self::assertTrue($concert->isPublished());
        self::assertEquals(3, $concert->ticketsRemaining());
    }

    /** @test */
    public function a_concert_can_only_be_published_once(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Concert $concert */
        $concert = ConcertFactory::createPublished(
            ['user_id' => $user->id, 'ticket_quantity' => 3]
        );

        $response = $this->actingAs($user)->post(
            '/backstage/published-concerts',
            ['concert_id' => $concert->id]
        );

        $response->assertStatus(422);
        self::assertEquals(3, $concert->fresh()->ticketsRemaining());
    }

    /** @test */
    function a_promoter_cannot_publish_other_concerts()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
                                                                 'user_id' => $otherUser->id,
                                                                 'ticket_quantity' => 3,
                                                             ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(404);
        $concert = $concert->fresh();
        $this->assertFalse($concert->isPublished());
        $this->assertEquals(0, $concert->ticketsRemaining());
    }

    /** @test */
    function a_guest_cannot_publish_concerts()
    {
        $concert = Concert::factory()
            ->unpublished()
            ->create(
                [
                    'ticket_quantity' => 3,
                ]
            );

        $response = $this->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertRedirect('/login');
        $concert = $concert->fresh();
        $this->assertFalse($concert->isPublished());
        $this->assertEquals(0, $concert->ticketsRemaining());
    }

    /** @test */
    function concerts_that_do_not_exist_cannot_be_published()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            '/backstage/published-concerts',
            [
                'concert_id' => 999,
            ]
        );

        $response->assertStatus(404);
    }
}
