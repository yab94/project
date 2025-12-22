<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Web\Controller;

use App\Core\Infrastructure\Web\Routing\Attribute\Get;

class HomeController extends AbstractController
{
    #[Get('/', 'home')]
    public function index(): void
    {
        $this->render('home/index', [
            'title' => 'CRM / Quotes / Invoices'
        ]);
    }
}
