<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;

final class Pages extends Controller
{
    // Placeholder constructor for consistency with other controllers.
    public function __construct()
    {
        // optional
    }

    // Render the storefront home page with featured products.
    public function index(): void
    {
        $products = (new Product())->allActiveForStorefront(12);
        $categories = (new Category())->allActiveForStorefront();

        $data = [
            'title' => SITENAME . ' | Women Jewellery',
            'products' => $products,
            'categories' => array_slice($categories, 0, 8),
            'styles' => ['home'],
            'body_class' => 'home-body',
        ];
        $this->render('pages/index', $data, 'main');
    }

    // Render the static about page.
    public function about(): void
    {
        $data = [
            'title' => 'About Us',
        ];
        $this->render('pages/about', $data, 'main');
    }
}
