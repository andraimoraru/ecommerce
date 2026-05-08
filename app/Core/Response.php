<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    // Set the HTTP status code for the current response.
    public function status(int $code): void
    {
        http_response_code($code);
    }

    // Redirect and stop execution immediately.
    public function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    // Send a JSON response and end the request.
    public function json(array $data, int $code = 200): never
    {
        $this->status($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
