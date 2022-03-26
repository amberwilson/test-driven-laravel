<?php

namespace Tests;

use ArrayAccess;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use Mockery;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();

        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);

        parent::setUp();

        TestResponse::macro('data', function ($key) {
            /** @var Response $this */
            return $this->original->getData()[$key];
        });
    }

    /**
     * Asserts that an array has a specified subset.
     *
     * @param array|ArrayAccess $subset
     * @param array|ArrayAccess $array
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     *
     * @codeCoverageIgnore
     *
     * @note Ported from PHPUnit since this function is going to be removed in PHPUnit 9
     *
     */
    public static function assertArraySubset(
        $subset,
        $array,
        bool $checkForObjectIdentity = false,
        string $message = ''
    ): void {
        if (!(\is_array($subset) || $subset instanceof ArrayAccess)) {
            throw InvalidArgumentException::create(
                1,
                'array or ArrayAccess'
            );
        }

        if (!(\is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentException::create(
                2,
                'array or ArrayAccess'
            );
        }

        $constraint = new CustomArraySubset($subset, $checkForObjectIdentity);

        static::assertThat($array, $constraint, $message);
    }
}
