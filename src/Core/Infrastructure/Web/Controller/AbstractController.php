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
     * Render a view with optional layout composition
     * 
     * @param string $view View path (e.g., 'crm/person/index')
     * @param array $data Data to pass to the view
     * @param string|null $layout Layout name (e.g., 'default', 'minimal') or null for no layout
     */
    protected function render(string $view, array $data = [], ?string $layout = 'default'): void
    {
        // Make URL generator available globally for views and layouts
        $urlGenerator = $this->urlGenerator();
        $GLOBALS['url'] = $urlGenerator;
        
        // Also add it to the data so it's available as $url in views
        $data['url'] = $urlGenerator;
        
        // Create the content view
        $contentView = new View($view, $data);
        
        if ($layout === null) {
            // No layout - just output the view
            echo $contentView->render();
        } else {
            // Compose view with layout
            $layoutView = new View("layout/{$layout}", [
                'content' => $contentView->render()
            ]);
            echo $layoutView->render();
        }
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
        $this->render('error', [
            'title' => '404 - Not Found',
            'message' => 'The resource you are looking for was not found.',
            'code' => 404
        ]);
    }
}
