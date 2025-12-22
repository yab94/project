<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Console;

use App\Core\Domain\AbstractModule;
use App\Core\Infrastructure\Console\Attribute\Command;
use App\Core\Infrastructure\Console\Command\AbstractCommand;
use ReflectionClass;
use RuntimeException;

final class CLI
{
    /** @var array<string, array{class: string, description: string}> */
    private array $commands = [];

    public function __construct(
        private readonly array $server = [],
        private readonly array $argv = []
    ) {
    }

    public function argv(?int $index = null): mixed
    {
        return $index === null ? $this->argv : ($this->argv[$index] ?? null);
    }

    public function server(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->server : ($this->server[$key] ?? $default);
    }

    /**
     * Register a module and its commands
     */
    public function registerModule(AbstractModule $module): self
    {
        foreach ($module->getCommands() as $commandClass) {
            $this->registerCommand($commandClass);
        }

        $module->boot();

        return $this;
    }

    /**
     * Register a command class
     */
    private function registerCommand(string $commandClass): self
    {
        if (!class_exists($commandClass)) {
            throw new RuntimeException("Command class {$commandClass} does not exist");
        }

        if (!is_subclass_of($commandClass, AbstractCommand::class)) {
            throw new RuntimeException("Command class {$commandClass} must extend AbstractCommand");
        }

        $reflection = new ReflectionClass($commandClass);
        $attributes = $reflection->getAttributes(Command::class);

        if (empty($attributes)) {
            throw new RuntimeException("Command class {$commandClass} must have a #[Command] attribute");
        }

        /** @var Command $commandAttribute */
        $commandAttribute = $attributes[0]->newInstance();

        $this->commands[$commandAttribute->name] = [
            'class' => $commandClass,
            'description' => $commandAttribute->description,
        ];

        return $this;
    }

    /**
     * Dispatch a command from CLI arguments
     */
    public function dispatch(): int
    {
        $argv = $this->argv;
        
        // Remove script name (first argument)
        array_shift($argv);

        if (empty($argv)) {
            $this->showHelp();
            return 0;
        }

        $commandName = array_shift($argv);

        if ($commandName === 'list' || $commandName === '--help' || $commandName === '-h') {
            $this->showHelp();
            return 0;
        }

        if (!isset($this->commands[$commandName])) {
            echo "\033[31mError: Command '{$commandName}' not found\033[0m\n\n";
            $this->showHelp();
            return 1;
        }

        $commandClass = $this->commands[$commandName]['class'];
        $command = new $commandClass();

        // Parse arguments and options
        [$arguments, $options] = $this->parseArguments($argv);

        try {
            return $command->execute($arguments, $options);
        } catch (\Throwable $e) {
            echo "\033[31mError executing command: {$e->getMessage()}\033[0m\n";
            echo "\033[33m{$e->getTraceAsString()}\033[0m\n";
            return 1;
        }
    }

    /**
     * Parse command arguments and options
     * 
     * @return array{0: array<int, string>, 1: array<string, mixed>}
     */
    private function parseArguments(array $argv): array
    {
        $arguments = [];
        $options = [];

        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--')) {
                // Long option: --key=value or --key
                $parts = explode('=', substr($arg, 2), 2);
                $options[$parts[0]] = $parts[1] ?? true;
            } elseif (str_starts_with($arg, '-')) {
                // Short option: -k or -k=value
                $parts = explode('=', substr($arg, 1), 2);
                $options[$parts[0]] = $parts[1] ?? true;
            } else {
                // Positional argument
                $arguments[] = $arg;
            }
        }

        return [$arguments, $options];
    }

    /**
     * Show available commands
     */
    private function showHelp(): void
    {
        echo "\033[32mAvailable commands:\033[0m\n\n";

        if (empty($this->commands)) {
            echo "  No commands registered.\n";
            return;
        }

        $maxLength = max(array_map('strlen', array_keys($this->commands)));

        foreach ($this->commands as $name => $info) {
            $padding = str_repeat(' ', $maxLength - strlen($name) + 2);
            $description = $info['description'] ?: 'No description';
            echo "  \033[33m{$name}\033[0m{$padding}{$description}\n";
        }

        echo "\n\033[36mUsage:\033[0m\n";
        echo "  php bin/console <command> [arguments] [options]\n";
        echo "  php bin/console list\n\n";
    }

    /**
     * Get all registered commands
     * 
     * @return array<string, array{class: string, description: string}>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
