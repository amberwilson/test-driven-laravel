<?php

namespace Tests\Unit;

use App\RandomOrderConfirmationNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RandomOrderConfirmationNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_only_contain_uppercase_letters_and_numbers(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();
        $confirmationNumber = $generator->generate();

        self::assertMatchesRegularExpression('/^[A-Z0-9]+$/', strlen($confirmationNumber));
    }

    /** @test */
    public function cannot_contain_ambiguous_characters(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();
        $confirmationNumber = $generator->generate();

        self::assertFalse(strpos($confirmationNumber, '0'));
        self::assertFalse(strpos($confirmationNumber, 'O'));
        self::assertFalse(strpos($confirmationNumber, '1'));
        self::assertFalse(strpos($confirmationNumber, 'I'));
    }

    /** @test */
    public function must_be_24_characters_long(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();
        $confirmationNumber = $generator->generate();

        self::assertEquals(24, strlen($confirmationNumber));
    }

    /** @test */
    public function confirmation_numbers_must_be_unique(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();

        $confirmationNumbers = array_map(static function () use ($generator) {
            return $generator->generate();
        }, range(1, 100));

        $this->assertCount(100, array_unique($confirmationNumbers));
    }
}
