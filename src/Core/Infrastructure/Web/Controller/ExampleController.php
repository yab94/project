<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Web\Controller;

use App\Core\Infrastructure\Web\Routing\Attribute\Get;

/**
 * Example controller WITHOUT explicit route names
 * Route names will be auto-generated as: example.index, example.show, example.about
 */
class ExampleController extends AbstractController
{
    #[Get('/example')]
    public function index(): void
    {
        $this->render('example/index', [
            'title' => 'Example Page',
            'message' => 'This route was auto-named as "example.index"'
        ]);
    }

    #[Get('/example/{id}')]
    public function show(?string $id = null): void
    {
        $this->render('example/show', [
            'title' => 'Example Detail',
            'id' => $id,
            'message' => 'This route was auto-named as "example.show"'
        ]);
    }

    #[Get('/example/about')]
    public function about(): void
    {
        $this->render('example/about', [
            'title' => 'About Example',
            'message' => 'This route was auto-named as "example.about"'
        ]);
    }
}
