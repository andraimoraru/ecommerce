<?php
declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\ErrorPage;

final class AdminMiddleware implements Middleware
{
    // Block access unless the current session belongs to an admin.
    public function handle(Request $request, Response $response): bool
    {
        if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
            ErrorPage::forbidden('This area is for administrators only. If you believe you should have access, please log in with an admin account.');
            return false;
        }
        return true;
    }
}
