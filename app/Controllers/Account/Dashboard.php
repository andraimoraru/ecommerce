<?php
declare(strict_types=1);

namespace App\Controllers\Account;

use App\Core\Controller;

final class Dashboard extends Controller
{
    public function index(): void
    {
        $data = [
            'title' => 'My Account',
        ];

        ob_start();
        require APPROOT . '/Views/account/index.php';
        $content = ob_get_clean();

        require APPROOT . '/Views/layouts/main.php';
    }
}