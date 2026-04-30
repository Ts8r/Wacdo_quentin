<?php

declare(strict_types=1);

namespace App\Http;

final class Router
{
    /** @var array<string, array<int, array{pattern:string, handler:callable}>> */
    private array $routes = [];

    public function __construct(
        private readonly string $method,
        private readonly string $uri,
    ) {
    }

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function patch(string $path, callable $handler): void
    {
        $this->add('PATCH', $path, $handler);
    }

    public function dispatch(): void
    {
        if (Cors::handlePreflight($this->method, $this->uri)) {
            return;
        }

        $path = parse_url($this->uri, PHP_URL_PATH) ?: '/';

        foreach ($this->routes[$this->method] ?? [] as $route) {
            if (preg_match($route['pattern'], $path, $matches) !== 1) {
                continue;
            }

            $params = array_filter(
                $matches,
                static fn (string|int $key): bool => is_string($key),
                ARRAY_FILTER_USE_KEY,
            );

            ($route['handler'])(...$params);
            return;
        }

        JsonResponse::send([
            'error' => 'not_found',
            'message' => sprintf('Route %s %s introuvable.', $this->method, $path),
        ], 404);
    }

    private function compile(string $path): string
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path) ?? $path;

        return '#^' . $pattern . '$#';
    }

    private function add(string $method, string $path, callable $handler): void
    {
        $this->routes[$method][] = [
            'pattern' => $this->compile($path),
            'handler' => $handler,
        ];
    }
}
