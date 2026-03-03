<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;

final class Dashboard extends Controller
{
    public function index(): void
    {
        $this->view('admin/index', [
            'title' => 'Admin',
        ]);
    }
}