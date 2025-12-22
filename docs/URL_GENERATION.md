# URL Generation

## Overview

The application uses an object-oriented `UrlGenerator` service to generate URLs from route names, avoiding hardcoded URLs throughout the codebase.

## Route Naming

### Automatic Route Names

**NEW:** Routes without explicit names are automatically named using the pattern `controller.method`.

```php
class ExampleController extends AbstractController
{
    // Auto-generated name: "example.index"
    #[Get('/example')]
    public function index(): void { }
    
    // Auto-generated name: "example.show"
    #[Get('/example/{id}')]
    public function show(string $id): void { }
}
```

**Naming Rules:**
- Controller name is converted to snake_case and "Controller" suffix is removed
- Common resources (person, quote, invoice) are automatically pluralized
- HomeController + index method generates just `"home"` (special case)
- Format: `{controller}.{method}` (e.g., `example.show`, `persons.create`)

### Explicit Route Names

You can still provide explicit names for clarity or custom naming:

```php
class QuoteController extends AbstractController
{
    // Explicit name takes precedence
    #[Get('/quotes', 'quotes.index')]
    public function index(): void { }
    
    // Explicit custom name
    #[Post('/quotes/{id}/send', 'quotes.send')]
    public function sendToClient(string $id): void { }
}
```

**Best Practice:** Use explicit names for public-facing or API routes, auto-generation for internal routes.

## In Controllers

The `AbstractController` provides access to the URL generator via the `urlGenerator()` method:

```php
class PersonController extends AbstractController
{
    public function store(): void
    {
        // ... create person logic ...
        
        // Redirect using generated URL
        $url = $this->urlGenerator()->route('persons.index');
        $this->redirect($url);
    }
}
```

## In Views

The URL generator is automatically available in views as `$url`:

```php
<!-- Simple route -->
<a href="<?= $url->route('quotes.index') ?>">View Quotes</a>

<!-- Route with parameters -->
<a href="<?= $url->route('quotes.view', ['id' => $quote->id()]) ?>">View Quote</a>

<!-- Route with query string -->
<a href="<?= $url->route('quotes.create', [], ['person_id' => $person->id()]) ?>">Create Quote</a>

<!-- Absolute URL -->
<a href="<?= $url->absolute('quotes.view', ['id' => $quote->id()]) ?>">Share Link</a>
```

## API

### `UrlGenerator::route(string $name, array $params = [], array $query = []): string`

Generate a relative URL from a route name.

**Parameters:**
- `$name`: The route name (e.g., 'quotes.create')
- `$params`: Route parameters to replace in the path (e.g., ['id' => '123'])
- `$query`: Query string parameters (e.g., ['search' => 'term'])

**Returns:** A relative URL string

**Example:**
```php
$url->route('quotes.view', ['id' => '123'], ['edit' => 'true'])
// Returns: /quotes/123?edit=true
```

### `UrlGenerator::absolute(string $name, array $params = [], array $query = []): string`

Generate an absolute URL including scheme and host.

**Example:**
```php
$url->absolute('quotes.view', ['id' => '123'])
// Returns: http://localhost:8080/quotes/123
```

## Benefits

- **Type-safe**: No string concatenation, less error-prone
- **Refactoring-friendly**: Change a route path, URLs update automatically
- **Testable**: Easy to mock in tests
- **IDE-friendly**: Auto-completion and type hints
- **Object-oriented**: Follows SOLID principles
- **No global functions**: Clean, namespaced code

## Testing

```php
use App\Modules\Shared\Infrastructure\Web\Routing\Router;
use App\Modules\Shared\Infrastructure\Web\Routing\UrlGenerator;

class MyTest extends TestCase
{
    public function testUrlGeneration(): void
    {
        $router = new Router();
        $router->get('/quotes/{id}', 'QuoteController@view', 'quotes.view');
        
        $urlGenerator = $router->urlGenerator();
        $url = $urlGenerator->route('quotes.view', ['id' => '123']);
        
        $this->assertEquals('/quotes/123', $url);
    }
}
```
