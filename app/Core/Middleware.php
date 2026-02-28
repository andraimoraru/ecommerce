<?php
declare(strict_types=1);

namespace App\Core;

interface Middleware
{
    /**
     * Return true to continue request; false to stop (middleware should handle response).
     */
    public function handle(Request $request, Response $response): bool;
}