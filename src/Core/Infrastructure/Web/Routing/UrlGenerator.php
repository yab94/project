<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Web\Routing;

class UrlGenerator
{
    public function __construct(
        private readonly Router $router
    ) {
    }

    /**
     * Generate URL from route name and parameters
     * 
     * @param string $name Route name
     * @param array<string, mixed> $params Route parameters
     * @param array<string, mixed> $query Query string parameters
     * @return string Generated URL
     */
    public function route(string $name, array $params = [], array $query = []): string
    {
        $url = $this->router->url($name, $params);

        // Add query string parameters if provided
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    /**
     * Generate absolute URL from route name
     */
    public function absolute(string $name, array $params = [], array $query = []): string
    {
        $scheme = $this->router->server('REQUEST_SCHEME', 'http');
        $host = $this->router->server('HTTP_HOST', 'localhost');
        
        return $scheme . '://' . $host . $this->route($name, $params, $query);
    }
}
