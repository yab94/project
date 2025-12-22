# Module Architecture

## Overview

The application uses a modular architecture where each module extends the abstract `Module` class and declares its controllers.

## Adding a New Module

### 1. Create the Module Class

Create a `Module.php` file in your module's root directory:

```php
<?php

declare(strict_types=1);

namespace App\Modules\YourModule;

use App\Modules\Shared\Domain\Module as BaseModule;
use App\Modules\YourModule\Infrastructure\Web\Controller\YourController;

class Module extends BaseModule
{
    public function getName(): string
    {
        return 'YourModule';
    }

    public function getControllers(): array
    {
        return [
            YourController::class,
            // Add more controllers here
        ];
    }

    // Optional: Override boot() for module initialization
    public function boot(): void
    {
        parent::boot();
        // Custom initialization logic
    }
}
```

### 2. Register the Module

Add your module to the Shared Module in `src/Modules/Shared/Module.php`:

```php
public function boot(): void
{
    // Register Shared module controllers
    foreach ($this->getControllers() as $controllerClass) {
        $this->router->registerController($controllerClass);
    }

    // Initialize and register all application modules
    $modules = [
        new CRMModule(),
        new BillingModule(),
        new BankingModule(),
        new YourModule(), // Add your module here
    ];

    foreach ($modules as $module) {
        $module->boot();
        
        foreach ($module->getControllers() as $controllerClass) {
            $this->router->registerController($controllerClass);
        }
    }
}
```

### 3. Create Controllers with Attributes

Your controllers should use route attributes:

```php
<?php

namespace App\Modules\YourModule\Infrastructure\Web\Controller;

use App\Modules\Shared\Infrastructure\Web\Controller\AbstractController;
use App\Modules\Shared\Infrastructure\Web\Routing\Attribute\Get;

class YourController extends AbstractController
{
    #[Get('/your-route', 'your.route.name')]
    public function index(): void
    {
        $this->render('your-module/index', [
            'title' => 'Your Module'
        ]);
    }
}
```

## Benefits

- **Decoupling**: Shared module doesn't know about specific controllers
- **Encapsulation**: Each module declares its own controllers
- **Testability**: Easy to test module configuration independently
- **Scalability**: Adding/removing modules is straightforward
- **DDD-compliant**: Respects bounded context boundaries

## Shared Module

The Shared Module is special - it orchestrates the entire application by:

1. **Managing the Router**: Receives the Router instance in its constructor
2. **Registering itself**: Registers its own controllers (HomeController, etc.)
3. **Bootstrapping other modules**: Instantiates and boots all application modules
4. **Centralizing module registration**: Single point of configuration

### Structure

```php
// src/Modules/Shared/Module.php
class Module extends BaseModule
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function boot(): void
    {
        // 1. Register Shared controllers
        foreach ($this->getControllers() as $controllerClass) {
            $this->router->registerController($controllerClass);
        }

        // 2. Bootstrap all modules
        $modules = [
            new CRMModule(),
            new BillingModule(),
            new BankingModule(),
        ];

        foreach ($modules as $module) {
            $module->boot();
            
            foreach ($module->getControllers() as $controllerClass) {
                $this->router->registerController($controllerClass);
            }
        }
    }
}
```

### Bootstrap Process

```php
// public/index.php
$router = new Router();
$sharedModule = new SharedModule($router);
$sharedModule->boot();
Router::setInstance($router);
$router->dispatch();
```

This bootstrap process:
- Creates the router
- Creates the Shared Module with the router
- Boots the application (which registers all modules)
- Makes router available as singleton
- Dispatches the HTTP request
