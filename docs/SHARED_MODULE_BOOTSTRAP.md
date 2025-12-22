# Shared Module Bootstrap Architecture

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        public/index.php                         │
│                                                                 │
│  $router = new Router();                                        │
│  $sharedModule = new SharedModule($router);                     │
│  $sharedModule->boot();  ────────────────────────┐              │
│  Router::setInstance($router);                   │              │
│  $router->dispatch();                            │              │
└──────────────────────────────────────────────────┼──────────────┘
                                                   │
                                                   ▼
┌─────────────────────────────────────────────────────────────────┐
│              src/Modules/Shared/Module.php                      │
│                                                                 │
│  public function boot(): void                                   │
│  {                                                              │
│      // 1. Register Shared controllers                         │
│      $this->router->registerController(HomeController::class); │
│                                                                 │
│      // 2. Bootstrap application modules                       │
│      $modules = [                                               │
│          new CRMModule(),      ──────────────────┐              │
│          new BillingModule(),  ──────────────────┼─────┐        │
│          new BankingModule(),  ──────────────────┼─────┼───┐    │
│      ];                                          │     │   │    │
│                                                  │     │   │    │
│      foreach ($modules as $module) {             │     │   │    │
│          $module->boot();                        │     │   │    │
│          // Register module controllers          │     │   │    │
│      }                                           │     │   │    │
│  }                                               │     │   │    │
└──────────────────────────────────────────────────┼─────┼───┼────┘
                                                   │     │   │
                    ┌──────────────────────────────┘     │   │
                    │  ┌───────────────────────────────┘   │
                    │  │  ┌────────────────────────────────┘
                    ▼  ▼  ▼
    ┌───────────────────────────────────────────────────────────┐
    │                   Module Instances                        │
    ├───────────────────────────────────────────────────────────┤
    │                                                           │
    │  ┌─────────────────┐  ┌──────────────────┐  ┌──────────┐│
    │  │  CRM Module     │  │  Billing Module  │  │ Banking  ││
    │  │                 │  │                  │  │  Module  ││
    │  │ • boot()        │  │ • boot()         │  │ • boot() ││
    │  │ • getName()     │  │ • getName()      │  │ • getName()│
    │  │ • getControllers│  │ • getControllers │  │ • getControllers│
    │  │                 │  │                  │  │          ││
    │  │ Returns:        │  │ Returns:         │  │ Returns: ││
    │  │ - PersonCtrl    │  │ - QuoteCtrl      │  │ (none)   ││
    │  └─────────────────┘  │ - InvoiceCtrl    │  └──────────┘│
    │                       └──────────────────┘              │
    └───────────────────────────────────────────────────────────┘
                                   │
                                   ▼
    ┌───────────────────────────────────────────────────────────┐
    │                  Router (Fully Configured)                │
    │                                                           │
    │  Named Routes:                                            │
    │  • home → /                                               │
    │  • persons.index → /persons                               │
    │  • persons.create → /persons/create                       │
    │  • quotes.index → /quotes                                 │
    │  • invoices.index → /invoices                             │
    │  • ... (all routes from all modules)                      │
    └───────────────────────────────────────────────────────────┘
```

## Key Benefits

### 1. **Single Responsibility**
- `routes.php` → Bootstrap entry point (3 lines!)
- `Shared/Module.php` → Application orchestration
- Other modules → Self-contained business logic

### 2. **Simplified index.php**

**Before (with routes.php):**
```php
// public/index.php
$router = require_once __DIR__ . '/../src/Modules/Shared/Infrastructure/Web/routes.php';
Router::setInstance($router);
$router->dispatch();
```

**After (direct bootstrap):**
```php
// public/index.php - everything in one place!
$router = new Router();
$sharedModule = new SharedModule($router);
$sharedModule->boot();
Router::setInstance($router);
$router->dispatch();
```

### 3. **Centralized Module Management**

All module registration happens in one place: `Shared/Module.php`

```php
$modules = [
    new CRMModule(),
    new BillingModule(),
    new BankingModule(),
    // Add new modules here
];
```

### 4. **Testability**

Easy to test module registration:

```php
public function testSharedModuleRegistersAllModules(): void
{
    $router = new Router();
    $sharedModule = new SharedModule($router);
    $sharedModule->boot();
    
    // Verify all routes are registered
    $this->assertNotNull($router->url('home'));
    $this->assertNotNull($router->url('persons.index'));
    $this->assertNotNull($router->url('quotes.index'));
}
```

## Bootstrap Flow

```
1. public/index.php
   └─> Creates Router
   └─> Creates SharedModule(router)
   └─> Calls SharedModule->boot()
   └─> Sets Router singleton for views
   └─> Dispatches request

2. SharedModule->boot()
   └─> Registers Shared controllers (HomeController)
   └─> Creates module instances (CRM, Billing, Banking)
   └─> For each module:
       └─> Calls module->boot()
       └─> Registers module->getControllers()

3. Router
   └─> Now contains all routes from all modules
   └─> Ready to dispatch requests
```

## Adding a New Module

Simply add it to the `$modules` array in `Shared/Module.php`:

```php
public function boot(): void
{
    // Register Shared controllers
    foreach ($this->getControllers() as $controllerClass) {
        $this->router->registerController($controllerClass);
    }

    // Initialize and register all application modules
    $modules = [
        new CRMModule(),
        new BillingModule(),
        new BankingModule(),
        new YourNewModule(),  // ← Add here!
    ];

    foreach ($modules as $module) {
        $module->boot();
        
        foreach ($module->getControllers() as $controllerClass) {
            $this->router->registerController($controllerClass);
        }
    }
}
```

## Comparison

### Old Architecture (routes.php as intermediary)
```
public/index.php
└─> routes.php (30 lines)
    ├─ Router creation
    ├─ Shared controller registration
    ├─ Module instantiation
    ├─ Module iteration
    └─ Controller registration
```

### New Architecture (direct bootstrap)
```
public/index.php
├─ Router creation
├─ SharedModule creation & boot
├─ Router singleton setup
└─ Request dispatch

Shared/Module.php
├─ Shared controller registration
├─ Module instantiation
├─ Module orchestration
└─ Controller registration
```

## Summary

✅ **Simpler:** No intermediate routes.php file needed  
✅ **Direct:** Bootstrap happens directly in index.php  
✅ **DDD-compliant:** Shared Module is responsible for orchestration  
✅ **Testable:** Easy to test module registration logic  
✅ **Maintainable:** Single place to add/remove modules  
✅ **Scalable:** Adding modules is a one-line change  
✅ **Type-safe:** Full IDE support and type checking  
✅ **Fewer files:** One less file to maintain
