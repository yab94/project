<?php

declare(strict_types=1);

namespace App\CRM\Infrastructure\Console\Command;

use App\Core\Infrastructure\CLI\CLI;
use App\CRM\Infrastructure\Persistence\PDOPersonRepository;
use App\Core\Infrastructure\Console\Command\AbstractCommand;
use App\Core\Infrastructure\Console\Attribute\Command;

#[Command(
    name: 'crm:list-persons',
    description: 'List all persons in the CRM'
)]
class ListPersonsCommand extends AbstractCommand
{
    public function __construct(
        CLI $cli,
        private readonly PDOPersonRepository $personRepository = new PDOPersonRepository()
    ) {
        parent::__construct($cli);
    }

    public function execute(array $arguments = [], array $options = []): int
    {
        $this->info('Listing all persons...');
        $this->output('');

        $persons = $this->personRepository->findAll();

        if (empty($persons)) {
            $this->warning('No persons found in the database.');
            return 0;
        }

        $count = count($persons);
        $this->success("Found {$count} person(s):");
        $this->output('');

        foreach ($persons as $person) {
            $this->output("ID: {$person->id()->value()}");
            $this->output("Type: {$person->type()->value}");
            $this->output("Name: {$person->fullName()}");
            
            // Display addresses
            if (!empty($person->addresses())) {
                $this->output("Addresses: " . count($person->addresses()));
            }
            
            // Display contacts
            if (!empty($person->contacts())) {
                $this->output("Contacts: " . count($person->contacts()));
            }
            
            $this->output('---');
        }

        return 0;
    }
}
