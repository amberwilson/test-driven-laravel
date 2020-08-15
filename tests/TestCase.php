<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp(): void {
        parent::setUp();

        // Make sure we see all exceptions that arise during test runs
        $this->withoutExceptionHandling();
    }
}
