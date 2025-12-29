<?php

declare(strict_types=1);

namespace App\Core\Domain;

interface ModuleInterface
{
    /**
     * Get the list of controller classes to register for this module
     * 
     * @return array<class-string>
     */
    public function getControllers(): array;

    /**
     * Get the list of command classes to register for this module
     * 
     * @return array<class-string>
     */
    public function getCommands(): array;

    /**
     * Get the module name
     */
    public function getName(): string;
}
