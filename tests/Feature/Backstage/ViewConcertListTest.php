<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use ConcertFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class ViewConcertListTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        TestResponse::macro('data', function ($key) {
            /** @var Response $this */
            return $this->original->getData()[$key];
        });

        Collection::macro('assertContains', function ($value) {
            /** @var Collection $this */
            Assert::assertTrue(
                $this->contains($value),
                'Failed asserting that the collection contained the specified value.'
            );
        });

        Collection::macro('assertNotContains', function ($value) {
            /** @var Collection $this */
            Assert::assertFalse(
                $this->contains($value),
                'Failed asserting that the collection did not contain the specified value.'
            );
        });

        Collection::macro('assertEquals', function ($items) {
            Assert::assertSameSize($this, $items);
            /** @var Collection $this */
            $this->zip($items)->each(function ($pair) {
                [$a, $b] = $pair;
                Assert::assertTrue($a->is($b));
            });
        });
    }

    /** @test */
    public function guests_cannot_view_a_promoters_concert_list(): void
    {
        $response = $this->get('/backstage/concerts');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function promoters_can_only_view_a_list_of_their_own_concerts(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var User $otherUser */
        $otherUser = factory(User::class)->create();

        // Create concerts in jumbled order to be sure we're really only pulling current user's concerts if we only pull 3
        /** @var array{Concert} $concerts */
        $publishedConcertA = ConcertFactory::createPublished(['user_id' => $user->id]);
        ConcertFactory::createPublished(['user_id' => $otherUser->id]);
        $publishedConcertC = ConcertFactory::createPublished(['user_id' => $user->id]);

        $unpublishedConcertA = ConcertFactory::createUnpublished(['user_id' => $user->id]);
        ConcertFactory::createUnpublished(['user_id' => $otherUser->id]);
        $unpublishedConcertC = ConcertFactory::createUnpublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/backstage/concerts');

        $response->assertStatus(200);

        $response->data('publishedConcerts')->assertEquals([$publishedConcertA, $publishedConcertC]);
        $response->data('unpublishedConcerts')->assertEquals([$unpublishedConcertA, $unpublishedConcertC]);
    }
}
