<?php
declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

final class AdminMiddleware implements Middleware
{
    // Block access unless the current session belongs to an admin.
    public function handle(Request $request, Response $response): bool
    {
        if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
            $response->status(403);
            echo '403 Forbidden';
            return false;
        }
        return true;
    }
}
