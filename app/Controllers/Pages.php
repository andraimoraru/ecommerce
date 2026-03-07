<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

final class Pages extends Controller
{
    public function __construct()
    {
        // optional
    }

    public function index(): void
    {
        $products = (new Product())->allActiveForStorefront(12);

        $data = [
            'title' => 'Welcome to the modular e-commerce platform',
            'products' => $products,
        ];
        $this->render('pages/index', $data, 'main');
    }

    public function about(): void
    {
        $data = [
            'title' => 'About Us',
        ];
        $this->render('pages/about', $data, 'main');
    }
}