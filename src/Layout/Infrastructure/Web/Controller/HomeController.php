<?php

declare(strict_types=1);

namespace App\Layout\Infrastructure\Web\Controller;

use App\Core\Infrastructure\Web\Controller\AbstractController;
use App\Core\Infrastructure\Web\Attribute\Get;
use App\Core\Infrastructure\Web\View\View;

class HomeController extends AbstractController
{
    #[Get('/', 'home')]
    public function index(): void
    {
        $content = new View('home/index', [
            'url' => $this->urlGenerator(),
            'title' => 'CRM / Quotes / Invoices'
        ]);
        $this->render(new View('layout/default', [
            'content' => $content->render()
        ]));
    }
}
