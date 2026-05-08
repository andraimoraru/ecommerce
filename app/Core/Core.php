<?php
declare(strict_types=1);

namespace App\Core;

final class Core
{
    private Router $router;

    // Set up the shared router instance for the request lifecycle.
    public function __construct()
    {
        $this->router = new Router();
    }

    // Expose the router so routes can be registered during bootstrap.
    public function router(): Router
    {
        return $this->router;
    }

    // Hand off the current request to the router.
    public function run(): void
    {
        $this->router->dispatch();
    }
}
