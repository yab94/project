<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Console\Command;

use App\Core\Infrastructure\CLI\CLI;

abstract class AbstractCommand
{
    public function __construct(protected CLI $cli) {}
    
    /**
     * Execute the command
     * 
     * @param array<string, mixed> $arguments Parsed command arguments
     * @param array<string, mixed> $options Parsed command options
     * @return int Exit code (0 = success, non-zero = error)
     */
    abstract public function execute(array $arguments = [], array $options = []): int;

    /**
     * Output a message to the console
     */
    protected function output(string $message): void
    {
        echo $message . PHP_EOL;
    }

    /**
     * Output an info message (cyan)
     */
    protected function info(string $message): void
    {
        $this->output("\033[36m{$message}\033[0m");
    }

    /**
     * Output a success message (green)
     */
    protected function success(string $message): void
    {
        $this->output("\033[32m{$message}\033[0m");
    }

    /**
     * Output an error message (red)
     */
    protected function error(string $message): void
    {
        $this->output("\033[31m{$message}\033[0m");
    }

    /**
     * Output a warning message (yellow)
     */
    protected function warning(string $message): void
    {
        $this->output("\033[33m{$message}\033[0m");
    }
}
