<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Web\Controller;

use App\Core\Infrastructure\Web\Router;
use App\Core\Infrastructure\Web\UrlGenerator;
use App\Core\Infrastructure\Web\View\View;

abstract class AbstractController
{
    public function __construct(protected Router $router) {}

    /**
     * Render a view and output it
     * 
     * The controller is responsible for composing views.
     * Compose views explicitly in your action:
     * 
     * Example with layout:
     *   $content = new View('crm/person/index', ['persons' => $persons]);
     *   $this->render(new View('layout/default', ['content' => $content->render()]));
     * 
     * Example with complex composition:
     *   $this->render(new View('layout/dashboard', [
     *       'sidebar' => new View('layout/sidebar', ['user' => $user])->render(),
     *       'main' => new View('dashboard', ['stats' => $stats])->render(),
     *   ]));
     * 
     * Example without layout:
     *   $this->render(new View('api/response', ['data' => $data]));
     * 
     * @param View $view The view to render
     */
    protected function render(View $view): void
    {
        // Make URL generator available globally for views and layouts
        $GLOBALS['url'] = $this->urlGenerator();
        
        echo $view->render();
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

    /**
     * Generate URL from route name
     */
    protected function url(string $name, array $params = []): string
    {
        return $this->router->url($name, $params);
    }

    /**
     * Return 404 not found response
     */
    protected function notFound(): void
    {
        http_response_code(404);
        $content = new View('error', [
            'url' => $this->urlGenerator(),
            'title' => '404 - Not Found',
            'message' => 'The resource you are looking for was not found.',
            'code' => 404
        ]);
        $this->render(new View('layout/default', [
            'content' => $content->render()
        ]));
    }
}
