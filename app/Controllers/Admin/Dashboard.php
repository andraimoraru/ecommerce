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

        $this->render('admin/index', $data, 'admin');
    }
}