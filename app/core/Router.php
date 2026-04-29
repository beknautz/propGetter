<?php
/**
 * PropIntel CRM - Simple front-controller router
 *
 * Supports GET and POST routes with named parameters (:id, :slug).
 * Middleware closures run before the controller callback.
 */

class Router
{
    private array $routes     = [];
    private array $middleware = [];
    private string $prefix    = '';

    // ── Registration ──────────────────────────────────────────────────────────

    public function get(string $path, callable $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function any(string $path, callable $handler, array $middleware = []): void
    {
        $this->add('GET',  $path, $handler, $middleware);
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, callable $handler, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $method,
            'path'       => $this->prefix . $path,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    // ── Dispatch ──────────────────────────────────────────────────────────────

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        // Support method override via hidden _method field or X-HTTP-Method-Override header
        if ($method === 'POST') {
            $override = $_POST['_method'] ?? ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '');
            if (in_array(strtoupper($override), ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = strtoupper($override);
            }
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->match($route['path'], $uri);
            if ($params === null) {
                continue;
            }

            // Run middleware
            foreach ($route['middleware'] as $mw) {
                if ($mw instanceof Closure) {
                    $mw($params);
                }
            }

            // Run handler
            call_user_func($route['handler'], $params);
            return;
        }

        // 404
        http_response_code(404);
        include VIEW_PATH . '/errors/404.php';
    }

    // ── Pattern matching ──────────────────────────────────────────────────────

    private function match(string $routePath, string $uri): ?array
    {
        // Convert :param segments to named regex groups
        $pattern = preg_replace('/\/:([a-zA-Z_]+)/', '/(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $uri, $matches)) {
            return null;
        }

        // Return only string-keyed (named) captures
        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    // ── Common middleware factories ────────────────────────────────────────────

    public static function authMiddleware(): Closure
    {
        return function () {
            Auth::require();
        };
    }

    public static function roleMiddleware(string|array $roles): Closure
    {
        return function () use ($roles) {
            Auth::requireRole($roles);
        };
    }

    public static function csrfMiddleware(): Closure
    {
        return function () {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                Auth::requireCsrf();
            }
        };
    }
}
