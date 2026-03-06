<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class Pages extends Controller
{
    public function __construct()
    {
        // optional
    }

    public function index(): void
    {
        $data = [
            'title' => 'Welcome'
        ];

        ob_start();
        require APPROOT . '/Views/pages/index.php';
        $content = ob_get_clean();

        require APPROOT . '/Views/layouts/main.php';
    }

    public function about(): void
    {
        $data = [
            'title' => 'About Us',
        ];
        $this->view('pages/about', $data);
    }
}