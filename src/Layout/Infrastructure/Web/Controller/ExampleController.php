<?php

declare(strict_types=1);

namespace App\Layout\Infrastructure\Web\Controller;

use App\Core\Infrastructure\Web\Attribute\Get;
use App\Core\Infrastructure\Web\Controller\AbstractController;
use App\Core\Infrastructure\Web\View\View;

/**
 * Example controller WITHOUT explicit route names
 * Route names will be auto-generated as: example.index, example.show, example.about
 */
class ExampleController extends AbstractController
{
    #[Get('/example')]
    public function index(): void
    {
        $content = new View('example/index', [
            'url' => $this->urlGenerator(),
            'title' => 'Example Page',
            'message' => 'This route was auto-named as "example.index"'
        ]);
        $this->render(new View('layout/default', [
            'content' => $content->render()
        ]));
    }

    #[Get('/example/{id}')]
    public function show(?string $id = null): void
    {
        $content = new View('example/show', [
            'url' => $this->urlGenerator(),
            'title' => 'Example Detail',
            'id' => $id,
            'message' => 'This route was auto-named as "example.show"'
        ]);
        $this->render(new View('layout/default', [
            'content' => $content->render()
        ]));
    }

    #[Get('/example/about')]
    public function about(): void
    {
        $content = new View('example/about', [
            'url' => $this->urlGenerator(),
            'title' => 'About Example',
            'message' => 'This route was auto-named as "example.about"'
        ]);
        $this->render(new View('layout/default', [
            'content' => $content->render()
        ]));
    }
}
