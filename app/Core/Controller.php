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

        if (!file_exists($viewFile)) {
            throw new \RuntimeException('View not found: ' . $viewFile);
        }

        require $viewFile;
    }

    public function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewFile = APPROOT . '/Views/' . $view . '.php';
        $layoutFile = APPROOT . '/Views/layouts/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException('View not found: ' . $viewFile);
        }

        if (!file_exists($layoutFile)) {
            throw new \RuntimeException('Layout not found: ' . $layoutFile);
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }

    // Show a user-friendly 404 page instead of raw text.
    protected function notFound(string $message = ''): void
    {
        ErrorPage::notFound($message !== '' ? $message : 'We could not find the page you were looking for.');
    }

    // Show a user-friendly restricted-access page.
    protected function forbidden(string $message = ''): void
    {
        ErrorPage::forbidden($message !== '' ? $message : 'This page is restricted.');
    }
}
