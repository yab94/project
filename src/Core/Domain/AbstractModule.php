<?php

declare(strict_types=1);

namespace App\Core\Domain;

abstract class AbstractModule
{
    /**
     * Get the list of controller classes to register for this module
     * 
     * @return array<class-string>
     */
    abstract public function getControllers(): array;

    /**
     * Get the list of command classes to register for this module
     * 
     * @return array<class-string>
     */
    abstract public function getCommands(): array;

    /**
     * Get the module name
     */
    abstract public function getName(): string;

    /**
     * Boot the module (optional override)
     */
    public function boot(): void {}
}
