<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Web\Controller;

final class ErrorController extends AbstractController
{
    public function notFound(): void
    {
        http_response_code(404);
        $this->render('error', [
            'title' => '404 - Page Not Found',
            'message' => 'The page you are looking for does not exist.',
            'code' => 404
        ]);
    }

    public function serverError(): void
    {
        http_response_code(500);
        $this->render('error', [
            'title' => '500 - Server Error',
            'message' => 'An internal server error occurred.',
            'code' => 500
        ]);
    }

    public function forbidden(): void
    {
        http_response_code(403);
        $this->render('error', [
            'title' => '403 - Forbidden',
            'message' => 'You do not have permission to access this resource.',
            'code' => 403
        ]);
    }
}
