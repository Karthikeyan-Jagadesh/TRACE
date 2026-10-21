<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, array $handler, array $middleware = []): self
    {
        $normalizedPath = '/' . trim($path, '/');
        // Convert route parameters like {id} or {code} to regex captures
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $normalizedPath);
        $regex = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $normalizedPath,
            'regex' => $regex,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
        return $this;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $uri, $matches)) {
                // Extract named parameter matches
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware pipeline
                foreach ($route['middleware'] as $mwClass) {
                    if (class_exists($mwClass) && method_exists($mwClass, 'handle')) {
                        $mwClass::handle($request);
                    }
                }

                [$controllerClass, $action] = $route['handler'];
                if (!class_exists($controllerClass)) {
                    throw new \RuntimeException("Controller class not found: {$controllerClass}");
                }

                $controller = new $controllerClass();
                if (!method_exists($controller, $action)) {
                    throw new \RuntimeException("Action method {$action} not found on controller {$controllerClass}");
                }

                // Call controller action with Request and route parameters
                $controller->$action($request, $params);
                return;
            }
        }

        // No route matched: Render 404 view
        http_response_code(404);
        try {
            View::render('home.404', ['pageTitle' => '404 &mdash; Signal Lost']);
        } catch (\Throwable $e) {
            echo "<h1>404 &mdash; Page Not Found</h1><p>The requested route {$uri} was not found on TRACE.</p>";
        }
        exit;
    }
}
