<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Core\Infrastructure\Web\Router;
use App\Core\Infrastructure\Web\UrlGenerator;

class UrlGeneratorTest extends TestCase
{
    private Router $router;
    private UrlGenerator $urlGenerator;

    protected function setUp(): void
    {
        $this->router = new Router();
        $this->urlGenerator = $this->router->urlGenerator();
        
        // Register some test routes
        $this->router->addGet('/quotes/create', 'QuoteController@create', 'quotes.create');
        $this->router->addGet('/quotes/{id}', 'QuoteController@view', 'quotes.view');
    }

    protected function tearDown(): void
    {
        // Cleanup
    }

    public function testUrlGeneratorGeneratesSimpleUrl(): void
    {
        $url = $this->urlGenerator->route('quotes.create');
        $this->assertEquals('/quotes/create', $url);
    }

    public function testUrlGeneratorGeneratesUrlWithParameters(): void
    {
        $url = $this->urlGenerator->route('quotes.view', ['id' => '123']);
        $this->assertEquals('/quotes/123', $url);
    }

    public function testUrlGeneratorGeneratesUrlWithQueryString(): void
    {
        $url = $this->urlGenerator->route('quotes.create', [], ['person_id' => '456']);
        $this->assertEquals('/quotes/create?person_id=456', $url);
    }

    public function testUrlGeneratorGeneratesUrlWithParametersAndQueryString(): void
    {
        $url = $this->urlGenerator->route('quotes.view', ['id' => '123'], ['edit' => 'true']);
        $this->assertEquals('/quotes/123?edit=true', $url);
    }

    public function testUrlGeneratorThrowsExceptionForUnknownRoute(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Route 'unknown.route' not found");
        
        $this->urlGenerator->route('unknown.route');
    }

    public function testUrlGeneratorThrowsExceptionWhenMissingParameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing parameters for route 'quotes.view'");
        
        $this->urlGenerator->route('quotes.view'); // Missing 'id' parameter
    }
}
