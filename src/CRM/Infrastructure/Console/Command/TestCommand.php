<?php

declare(strict_types=1);

namespace App\CRM\Infrastructure\Console\Command;

use App\Core\Infrastructure\Console\Command\AbstractCommand;
use App\Core\Infrastructure\Console\Attribute\Command;

#[Command(
    name: 'crm:test',
    description: 'Test command for CRM module'
)]
class TestCommand extends AbstractCommand
{
    public function execute(array $arguments = [], array $options = []): int
    {
        $this->info('CRM Test Command');
        $this->output('');
        
        if (!empty($arguments)) {
            $this->output('Arguments:');
            foreach ($arguments as $index => $arg) {
                $this->output("  [{$index}] = {$arg}");
            }
            $this->output('');
        }
        
        if (!empty($options)) {
            $this->output('Options:');
            foreach ($options as $key => $value) {
                $valueStr = is_bool($value) ? ($value ? 'true' : 'false') : $value;
                $this->output("  --{$key} = {$valueStr}");
            }
            $this->output('');
        }
        
        $this->success('Command executed successfully!');
        
        return 0;
    }
}
