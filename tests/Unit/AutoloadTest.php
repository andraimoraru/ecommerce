<?php

use PHPUnit\Framework\TestCase;

final class AutoloadTest extends TestCase
{
    public function testControllersAreAutoloadable(): void
    {
        $this->assertTrue(class_exists('\App\\Controllers\\Pages'), 'App\\Controllers\\Pages should be autoloadable');

        $pages = new \App\Controllers\Pages();
        $this->assertInstanceOf(\App\Controllers\Pages::class, $pages);
    }

    public function testCoreDatabaseIsAutoloadable(): void
    {
        $this->assertTrue(class_exists('\App\\Core\\Database'), 'App\\Core\\Database should be autoloadable');

        // Use reflection instead of instantiating to avoid DB connection attempts during unit test
        $ref = new ReflectionClass('\App\\Core\\Database');
        $this->assertTrue($ref->hasMethod('__construct'), 'Database should have a constructor');
    }
}
