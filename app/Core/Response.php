<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function status(int $code): void
    {
        http_response_code($code);
    }

    public function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    public function json(array $data, int $code = 200): never
    {
        $this->status($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}