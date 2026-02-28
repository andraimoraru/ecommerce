<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Base Controller
 * - Loads Models (via Composer autoload)
 * - Loads Views (via APPROOT)
 */
abstract class Controller
{
    /**
     * Load a model class and return an instance.
     * Example: $this->model('Product') -> new \App\Models\Product()
     */
    protected function model(string $model)
    {
        $fqcn = "App\\Models\\{$model}";

        if (!class_exists($fqcn)) {
            throw new \RuntimeException("Model not found: {$fqcn}");
        }

        return new $fqcn();
    }

    /**
     * Load a view file.
     * Example: $this->view('pages/index', ['title' => '...'])
     */
    protected function view(string $view, array $data = []): void
    {
        $viewFile = APPROOT . '/Views/' . $view . '.php';

        if (!is_file($viewFile)) {
            http_response_code(500);
            echo "View does not exist: " . htmlspecialchars($viewFile, ENT_QUOTES, 'UTF-8');
            return;
        }

        // Make $data available inside the view 
        require $viewFile;
    }
}