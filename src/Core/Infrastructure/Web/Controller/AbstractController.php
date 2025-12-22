<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Web\Controller;

use App\Core\Infrastructure\Web\Routing\Router;
use App\Core\Infrastructure\Web\Routing\UrlGenerator;

abstract class AbstractController
{
    public function __construct(protected Router $router) {}

    protected function render(string $view, array $data = []): void
    {
        extract($data);
        
        // Make URL generator available in views
        $url = $this->urlGenerator();
        
        // Determine view path based on module
        // View format: 'crm/person/index' -> src/CRM/Infrastructure/Web/View/person/index.php
        // __DIR__ = src/Core/Infrastructure/Web/Controller
        $rootDir = __DIR__ . '/../../../../../';  // Go up to project root (5 levels)
        
        if (preg_match('#^(crm|billing|banking)/([a-z_]+)/([a-z_]+)$#i', $view, $matches)) {
            [, $modulePrefix, $resource, $action] = $matches;
            // CRM, Billing, or Banking (proper casing for directory names)
            $module = match(strtolower($modulePrefix)) {
                'crm' => 'CRM',
                'billing' => 'Billing',
                'banking' => 'Banking',
            };
            $viewPath = $rootDir . "src/{$module}/Infrastructure/Web/View/{$resource}/{$action}.php";
        } else {
            // Fallback for non-module views (like home, errors, etc.)
            $viewPath = __DIR__ . '/../View/' . $view . '.php';
        }
        
        if (!file_exists($viewPath)) {
            http_response_code(404);
            echo "View not found: {$view} (looked in {$viewPath})";
            return;
        }
        
        require_once __DIR__ . '/../View/layout/header.php';
        require_once $viewPath;
        require_once __DIR__ . '/../View/layout/footer.php';
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        // No exit needed - the script will terminate naturally
        // and the redirect header will be sent
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        // No exit needed - output is sent, script terminates naturally
    }

    /**
     * Get URL generator for creating URLs from route names
     */
    protected function urlGenerator(): UrlGenerator
    {
        return $this->router->urlGenerator();
    }

    /**
     * Get POST data
     */
    protected function post(?string $key = null, mixed $default = null): mixed
    {
        return $this->router->post($key, $default);
    }

    /**
     * Get GET data
     */
    protected function get(?string $key = null, mixed $default = null): mixed
    {
        return $this->router->get($key, $default);
    }

    /**
     * Get SERVER data
     */
    protected function server(?string $key = null, mixed $default = null): mixed
    {
        return $this->router->server($key, $default);
    }
}
