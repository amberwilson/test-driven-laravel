<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessPosterImage;
use Database\Factories\ConcertFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessPosterImageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_resizes_the_poster_image_to_600px_wide(): void
    {
        Storage::fake('public');

        $posterPath = 'posters/example-poster.png';
        Storage::disk('public')->put(
            $posterPath,
            file_get_contents(base_path('tests/__fixtures__/full-size-poster.png'))
        );
        $concert = ConcertFactory::createUnpublished(['poster_image_path' => $posterPath]);

        ProcessPosterImage::dispatch($concert);

        $resizedImage = Storage::disk('public')->get($posterPath);
        [$width, $height] = getimagesizefromstring($resizedImage);

        self::assertEquals(600, $width);
        self::assertEquals(776, $height);

        $optimizedImageContents = Storage::disk('public')->get($posterPath);
        $controlImageContents = file_get_contents(base_path('tests/__fixtures__/optimized-poster.png'));
        self::assertEquals($controlImageContents, $optimizedImageContents);
    }

    /** @test */
    public function it_optimizes_the_poster_image(): void
    {
        Storage::fake('public');

        $originalPosterPath = 'tests/__fixtures__/full-size-poster.png';
        $posterPath = 'posters/example-poster.png';

        Storage::disk('public')->put(
            $posterPath,
            file_get_contents(base_path($originalPosterPath))
        );
        $concert = ConcertFactory::createUnpublished(['poster_image_path' => $posterPath]);

        ProcessPosterImage::dispatch($concert);

        $optimizedImageSize = Storage::disk('public')->size($posterPath);
        $originalSize = filesize(base_path($originalPosterPath));
        self::assertLessThan($originalSize, $optimizedImageSize);

        $optimizedImageContents = Storage::disk('public')->get($posterPath);
        $controlImageContents = file_get_contents(base_path('tests/__fixtures__/optimized-poster.png'));
        self::assertEquals($controlImageContents, $optimizedImageContents);
    }
}
