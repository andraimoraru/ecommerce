<?php
declare(strict_types=1);

namespace App\Core;

use ReflectionMethod;
use RuntimeException;

final class Router
{
    /**
     * @var array<string, array<int, array{pattern:string, handler:string, middleware:array<int,string>}>>
     */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    // Register a GET route.
    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    // Register a POST route.
    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    // Store a normalized route definition for later dispatch.
    private function add(string $httpMethod, string $path, string $handler, array $middleware): void
    {
        $path = $this->normalize($path);

        // Convert /products/{slug} into regex with named captures
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);
        $regex = '#^' . $regex . '$#';

        $this->routes[$httpMethod][] = [
            'pattern' => $regex,
            'handler' => $handler,
            'middleware' => $middleware, // array of middleware FQCN strings
        ];
    }

    // Match the request, run middleware, and invoke the controller action.
    public function dispatch(?Request $request = null, ?Response $response = null): void
    {
        $request ??= new Request();
        $response ??= new Response();

        $method = $request->method();
        $path = $this->normalize($this->stripBasePath($request->rawPath()));

        foreach ($this->routes[$method] ?? [] as $route) {
            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            $params = $this->extractNamedParams($matches);

            // Middleware pipeline
            foreach ($route['middleware'] as $mwClass) {
                if (!class_exists($mwClass)) {
                    throw new RuntimeException("Middleware not found: {$mwClass}");
                }

                $mw = new $mwClass();

                if (!$mw instanceof Middleware) {
                    throw new RuntimeException("Middleware must implement App\\Core\\Middleware: {$mwClass}");
                }

                if ($mw->handle($request, $response) === false) {
                    return; // middleware handled response (redirect/403/etc.)
                }
            }

            $this->invokeHandler($route['handler'], $params);
            return;
        }

        // No route matched
        $response->status(404);
        echo '404 Not Found';
    }

    // Resolve a controller string like `Products@show` and call it.
    private function invokeHandler(string $handler, array $params): void
    {
        if (!str_contains($handler, '@')) {
            throw new RuntimeException("Invalid handler '{$handler}'. Expected 'Controller@method'.");
        }

        [$controllerShort, $method] = explode('@', $handler, 2);

        // Allow 'Admin\\Orders' etc.
        $controllerClass = 'App\\Controllers\\' . $controllerShort;

        if (!class_exists($controllerClass)) {
            throw new RuntimeException("Controller not found: {$controllerClass}");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            throw new RuntimeException("Action not found: {$controllerClass}::{$method}");
        }

        // If action expects a parameter, pass $params (assoc array)
        $ref = new ReflectionMethod($controller, $method);

        if ($ref->getNumberOfParameters() > 0) {
            $controller->$method($params);
        } else {
            $controller->$method();
        }
    }

    // Keep only the named regex captures from a route match.
    private function extractNamedParams(array $matches): array
    {
        $params = [];
        foreach ($matches as $k => $v) {
            if (is_string($k)) {
                $params[$k] = $v;
            }
        }
        return $params;
    }

    /**
     * Makes it work whether you're hosted at:
     * - http://localhost/ecommerce  (base path: /ecommerce)
     * - http://example.com          (base path: '')
     */
    private function stripBasePath(string $path): string
    {
        // URLROOT is defined in config.php and comes from APP_URL
        $base = defined('URLROOT') ? (parse_url(URLROOT, PHP_URL_PATH) ?: '') : '';

        if ($base !== '' && $base !== '/' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
            if ($path === '') {
                $path = '/';
            }
        }

        return $path;
    }

    // Ensure paths use the same slash format before matching.
    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }
}
