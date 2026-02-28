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
            'title' => 'Welcome to the modular e-commerce platform',
        ];
        $this->view('pages/index', $data);
    }

    public function about(): void
    {
        $data = [
            'title' => 'About Us',
        ];
        $this->view('pages/about', $data);
    }
}