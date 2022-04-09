<?php

namespace Tests;

use ArrayAccess;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use Mockery;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use ArraySubsetAsserts;

    public function setUp(): void
    {
        parent::setUp();

        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);

        parent::setUp();

        TestResponse::macro('data', function ($key) {
            /** @var Response $this */
            return $this->original->getData()[$key];
        });

        EloquentCollection::macro('assertContains', function ($value) {
            /** @var EloquentCollection $this */
            Assert::assertTrue(
                $this->contains($value),
                'Failed asserting that the collection contained the specified value.'
            );
        });

        EloquentCollection::macro('assertNotContains', function ($value) {
            /** @var EloquentCollection $this */
            Assert::assertFalse(
                $this->contains($value),
                'Failed asserting that the collection did not contain the specified value.'
            );
        });

        EloquentCollection::macro('assertEquals', function ($items) {
            Assert::assertSameSize($this, $items);
            /** @var EloquentCollection $this */
            $this->zip($items)->each(function ($pair) {
                [$a, $b] = $pair;
                Assert::assertTrue($a->is($b));
            });
        });
    }
}
