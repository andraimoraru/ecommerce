<?php
declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

final class AuthMiddleware implements Middleware
{
    public function handle(Request $request, Response $response): bool
    {
        if (empty($_SESSION['user_id'])) {
            // optional: save intended url
            $_SESSION['redirect_after_login'] = $request->rawPath();
            $response->redirect(URLROOT . '/login');
            return false;
        }
        return true;
    }
}