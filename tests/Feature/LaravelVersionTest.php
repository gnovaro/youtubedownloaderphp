<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Application;

class LaravelVersionTest extends TestCase
{
    /**
     * Test that Laravel version is 11.x after upgrade.
     */
    public function test_laravel_version_is_eleven(): void
    {
        $version = Application::VERSION;
        
        // Assert that we're running Laravel 11.x
        $this->assertTrue(
            version_compare($version, '11.0.0', '>='),
            "Expected Laravel 11.x but got {$version}"
        );
        
        $this->assertTrue(
            version_compare($version, '12.0.0', '<'),
            "Expected Laravel 11.x but got {$version}"
        );
    }
}