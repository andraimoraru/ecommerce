<?php
declare(strict_types=1);

namespace App\Core;

final class ErrorPage
{
    // Render a friendly error page inside the normal storefront layout.
    public static function render(int $statusCode, string $title, string $message, array $actions = []): void
    {
        http_response_code($statusCode);

        $data = [
            'title' => $title,
            'status_code' => $statusCode,
            'error_title' => $title,
            'error_message' => $message,
            'actions' => $actions ?: [
                [
                    'label' => 'Go to homepage',
                    'url' => URLROOT,
                    'class' => 'btn',
                ],
                [
                    'label' => 'Browse products',
                    'url' => URLROOT . '/products',
                    'class' => 'btn secondary',
                ],
            ],
        ];

        $viewFile = APPROOT . '/Views/errors/status.php';
        $layoutFile = APPROOT . '/Views/layouts/main.php';

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }

    // Shortcut for missing pages or records.
    public static function notFound(string $message = 'We could not find the page you were looking for. It may have moved, been removed, or the link may be incorrect.'): void
    {
        self::render(404, 'Page not found', $message);
    }

    // Shortcut for protected pages the current user cannot access.
    public static function forbidden(string $message = 'This page is restricted. Please log in with an account that has permission to view it.'): void
    {
        self::render(403, 'Access not available', $message, [
            [
                'label' => 'Go to homepage',
                'url' => URLROOT,
                'class' => 'btn',
            ],
            [
                'label' => 'Log in',
                'url' => URLROOT . '/login',
                'class' => 'btn secondary',
            ],
        ]);
    }
}
