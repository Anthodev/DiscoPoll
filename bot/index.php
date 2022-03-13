<?php

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Discord\WebSockets\Event;

require_once __DIR__ . '/presence_command.php';

$discord = new Discord(
    [
        'token' => getenv('BOT_TOKEN'),
    ]
);

$discord->on('ready', function (Discord $discord) {
    require_once __DIR__ . '/commands.php';

    echo "Bot is ready!", PHP_EOL;

    $discord->listenCommand('presence', function (Interaction $interaction) use ($discord) {
        $interaction->acknowledge();

        $officeMessageBuilder = createOfficeMessageBuilder($discord);

        $interaction->sendFollowUpMessage($officeMessageBuilder);
    });

    $discord->on(Event::INTERACTION_CREATE, function (Interaction $interaction) use ($discord) {
        $interaction->acknowledge();

        switch ($interaction->data->custom_id) {
            case 'button_office_Lundi':
                handleOfficeButton($discord, $interaction, 'Lundi');
                break;
            case 'button_office_Mardi':
                handleOfficeButton($discord, $interaction, 'Mardi');
                break;
            case 'button_office_Mercredi':
                handleOfficeButton($discord, $interaction, 'Mercredi');
                break;
            case 'button_office_Jeudi':
                handleOfficeButton($discord, $interaction, 'Jeudi');
                break;
            case 'button_office_Vendredi':
                handleOfficeButton($discord, $interaction, 'Vendredi');
                break;
        }
    });
});

$discord->run();
