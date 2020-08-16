<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
    use CreatesApplication;

    public function setUp(): void {
        parent::setUp();

        // Uncomment this to see all exceptions that arise during test runs
        // $this->withoutExceptionHandling();
    }
}
