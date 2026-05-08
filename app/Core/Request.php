<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    // Return the current HTTP verb in a consistent uppercase format.
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Raw path from REQUEST_URI (e.g. /ecommerce/products/abc)
     */
    public function rawPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return parse_url($uri, PHP_URL_PATH) ?? '/';
    }

    // Read a query-string value with an optional fallback.
    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    // Read a posted form value with an optional fallback.
    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    // Return the full POST payload as a plain array.
    public function allPost(): array
    {
        return $_POST ?? [];
    }
}
