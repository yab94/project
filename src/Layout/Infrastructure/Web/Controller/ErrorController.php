<?php

declare(strict_types=1);

namespace App\Layout\Infrastructure\Web\Controller;

use App\Core\Infrastructure\Web\Controller\AbstractController;
use App\Core\Infrastructure\Web\View\View;

final class ErrorController extends AbstractController
{
    public function notFound(): void
    {
        http_response_code(404);
        $content = new View('error', [
            'url' => $this->urlGenerator(),
            'title' => '404 - Page Not Found',
            'message' => 'The page you are looking for does not exist.',
            'code' => 404
        ]);
        $this->render(new View('layout/default', [
            'content' => $content->render()
        ]));
    }

    public function serverError(): void
    {
        http_response_code(500);
        $content = new View('error', [
            'url' => $this->urlGenerator(),
            'title' => '500 - Server Error',
            'message' => 'An internal server error occurred.',
            'code' => 500
        ]);
        $this->render(new View('layout/default', [
            'content' => $content->render()
        ]));
    }

    public function forbidden(): void
    {
        http_response_code(403);
        $content = new View('error', [
            'url' => $this->urlGenerator(),
            'title' => '403 - Forbidden',
            'message' => 'You do not have permission to access this resource.',
            'code' => 403
        ]);
        $this->render(new View('layout/default', [
            'content' => $content->render()
        ]));
    }
}
