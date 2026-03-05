<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;

final class Dashboard extends Controller
{
    public function index(): void
    {
        $data = [
            'title' => 'Admin',
        ];

        ob_start();
        // Make $data available to the view file
        require APPROOT . '/Views/admin/index.php';
        $content = ob_get_clean();

        require APPROOT . '/Views/layouts/admin.php';
    }
}