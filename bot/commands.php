<?php

declare(strict_types=1);

use Discord\Parts\Interactions\Command\Command;

$presenceCommand = new Command(
    $discord,
    [
        'name' => 'presence',
        'description' => 'Demander qui sera au bureau !',
    ]
);

$discord->application->commands->save($presenceCommand);
