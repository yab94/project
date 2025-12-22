<?php

declare(strict_types=1);

use App\Core\Infrastructure\Web\Routing\Router;

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap application through Shared Module
$router = new Router($_SERVER, $_GET, $_POST, $_FILES, $_COOKIE);
$router->registerModule(new \App\Core\Module());
$router->registerModule(new \App\CRM\Module());
$router->registerModule(new \App\Billing\Module());
$router->registerModule(new \App\Banking\Module());

// Dispatch request
$router->dispatch();
