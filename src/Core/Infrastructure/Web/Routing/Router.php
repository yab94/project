<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Web\Routing;

use App\Core\Domain\Module;
use App\Core\Infrastructure\Web\Routing\Attribute\Route;
use ReflectionClass;
use ReflectionMethod;

final class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private ?UrlGenerator $urlGenerator = null;

    public function __construct(
        private readonly array $server = [],
        private readonly array $get = [],
        private readonly array $post = [],
        private readonly array $files = [],
        private readonly array $cookie = []
    ) {
    }

    public function server(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->server : ($this->server[$key] ?? $default);
    }

    public function get(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->get : ($this->get[$key] ?? $default);
    }

    public function post(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->post : ($this->post[$key] ?? $default);
    }

    public function files(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->files : ($this->files[$key] ?? $default);
    }

    public function cookie(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->cookie : ($this->cookie[$key] ?? $default);
    }

    public function addGet(string $path, string $handler, ?string $name = null): self
    {
        $this->addRoute('GET', $path, $handler, $name);
        return $this;
    }

    public function addPost(string $path, string $handler, ?string $name = null): self
    {
        $this->addRoute('POST', $path, $handler, $name);
        return $this;
    }

    public function addPut(string $path, string $handler, ?string $name = null): self
    {
        $this->addRoute('PUT', $path, $handler, $name);
        return $this;
    }

    public function addDelete(string $path, string $handler, ?string $name = null): self
    {
        $this->addRoute('DELETE', $path, $handler, $name);
        return $this;
    }

    private function addRoute(string $method, string $path, string $handler, string $name): void
    {
        $this->routes[$method][$path] = $handler;
        $this->namedRoutes[$name] = ['method' => $method, 'path' => $path, 'handler' => $handler];
    }

    public function registerModule(Module $module): self
    {
        foreach ($module->getControllers() as $controllerClass) {
            $this->registerController($controllerClass);
        }

        $module->boot();

        return $this;
    }

    /**
     * Register routes from controller class using attributes
     */
    public function registerController(string $controllerClass): self
    {
        $reflection = new ReflectionClass($controllerClass);
        
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(Route::class, \ReflectionAttribute::IS_INSTANCEOF);
            
            foreach ($attributes as $attribute) {
                $route = $attribute->newInstance();
                $handler = $this->getControllerName($controllerClass) . '@' . $method->getName();
                
                // Auto-generate route name if not provided: ControllerClass.methodName
                $routeName = $route->name;
                if ($routeName === null) {
                    $routeName = $this->generateRouteName($controllerClass, $method->getName());
                }
                
                $this->addRoute($route->method, $route->path, $handler, $routeName);
            }
        }
        
        return $this;
    }

    /**
     * Extract controller short name from full class name
     */
    private function getControllerName(string $controllerClass): string
    {
        // Extract module and controller name from full class name
        // Example: App\CRM\Infrastructure\Web\Controller\PersonController
        // Returns: CRM\PersonController
        
        if (preg_match('/App\\\\([^\\\\]+)\\\\Infrastructure\\\\Web\\\\Controller\\\\(.+)/', $controllerClass, $matches)) {
            return $matches[1] . '\\' . $matches[2];
        }
        
        // Fallback for non-module controllers
        $parts = explode('\\', $controllerClass);
        return end($parts);
    }

    /**
     * Generate route name from controller and method
     * Example: PersonController + index -> person.index
     *          QuoteController + store -> quote.store
     */
    private function generateRouteName(string $controllerClass, string $methodName): string
    {
        // Extract controller name without "Controller" suffix
        $parts = explode('\\', $controllerClass);
        $controllerName = end($parts);
        $controllerName = str_replace('Controller', '', $controllerName);
        
        // Convert to snake_case and remove leading/trailing underscores
        $controllerName = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $controllerName));
        $controllerName = trim($controllerName, '_');
        
        // Handle special cases
        if ($controllerName === 'home' && $methodName === 'index') {
            return 'home';
        }
        
        // Pluralize common resource names (person -> persons, quote -> quotes, etc.)
        if (in_array($controllerName, ['person', 'quote', 'invoice'])) {
            $controllerName .= 's';
        }
        
        return $controllerName . '.' . $methodName;
    }

    public function dispatch(): void
    {
        $method = $this->server['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Handle method override for forms (DELETE, PUT via POST)
        if ($method === 'POST' && isset($this->post['_method'])) {
            $method = strtoupper($this->post['_method']);
        }

        // Match exact route
        if (isset($this->routes[$method][$uri])) {
            $this->executeHandler($this->routes[$method][$uri]);
            return;
        }

        // Match parameterized routes
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            if ($params = $this->matchRoute($route, $uri)) {
                $this->executeHandler($handler, $params);
                return;
            }
        }

        // 404 Not Found
        $this->handleNotFound($uri);
    }

    private function matchRoute(string $route, string $uri): array|false
    {
        // Convert route pattern like /persons/{id} to regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            // Extract only named parameters
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    private function executeHandler(string $handler, array $params = []): void
    {
        [$controllerName, $action] = explode('@', $handler);
        
        // Map module prefix to full namespace
        // CRM\PersonController -> App\CRM\Infrastructure\Web\Controller\PersonController
        // Billing\QuoteController -> App\Billing\Infrastructure\Web\Controller\QuoteController
        if (str_contains($controllerName, '\\')) {
            [$module, $controller] = explode('\\', $controllerName, 2);
            $controllerClass = "App\\{$module}\\Infrastructure\\Web\\Controller\\{$controller}";
        } else {
            // Fallback for controllers without module prefix (like HomeController)
            $controllerClass = "App\\Core\\Infrastructure\\Web\\Controller\\{$controllerName}";
        }

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            echo "Controller not found: {$controllerClass}";
            return;
        }

        $controller = new $controllerClass(router: $this);

        if (!method_exists($controller, $action)) {
            http_response_code(500);
            echo "Method not found: {$controllerClass}::{$action}";
            return;
        }

        // Pass parameters to controller method
        call_user_func_array([$controller, $action], $params);
    }

    private function handleNotFound(string $uri): void
    {
        http_response_code(404);
        
        // Try to load error controller
        $errorController = 'App\\Infrastructure\\Web\\Controller\\ErrorController';
        if (class_exists($errorController)) {
            $controller = new $errorController();
            if (method_exists($controller, 'notFound')) {
                $controller->notFound();
                return;
            }
        }

        // Fallback
        echo "Page not found: {$uri}";
    }

    /**
     * Generate URL from route name and parameters
     */
    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route '{$name}' not found");
        }

        $path = $this->namedRoutes[$name]['path'];

        // Replace path parameters with actual values
        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', (string)$value, $path);
        }

        // Check if there are still unreplaced parameters
        if (preg_match('/\{[^}]+\}/', $path)) {
            throw new \InvalidArgumentException("Missing parameters for route '{$name}'");
        }

        return $path;
    }

    /**
     * Get URL generator instance
     */
    public function urlGenerator(): UrlGenerator
    {
        if ($this->urlGenerator === null) {
            $this->urlGenerator = new UrlGenerator($this);
        }
        
        return $this->urlGenerator;
    }
}
