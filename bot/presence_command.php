<?php

declare(strict_types=1);

use Discord\Discord;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Embed\Embed;

function initializeButton(string $day, string $label): Button
{
    $button = new Button(
        Button::STYLE_PRIMARY,
        'button_office_' . $day,
    );

    $button->setLabel($label);

    return $button;
}

function createOfficeMessageBuilder(
    Discord $discord,
    ?Interaction $interaction = null,
    ?array $messagePresences = null,
    ?bool $isUpdated = false,
): MessageBuilder {
    $originalDate = new DateTime();
    $currentDate = new DateTime();

    $startDate = null;
    $endDate = null;

    if (!$isUpdated) {
        if (($originalDate->format('w') == 0 || $originalDate->format('w') >= 2)) {
            $startDate = $currentDate->modify('next monday')->format('d-m');
            $endDate = $currentDate->modify('next friday')->format('d-m');
        } else {
            $startDate = $currentDate->modify('monday this week')->format('d-m');
            $endDate = $currentDate->modify('friday this week')->format('d-m');
        }
    } else {
        foreach ($interaction->message->embeds as $embed) {
            if ($embed->type === Embed::TYPE_RICH) {
                $title = $embed->title;

                preg_match('/\d+/', $title, $matches);

                if ($originalDate->modify('monday this week')->format('d') === $matches[0]) {
                    $startDate = $currentDate->modify('monday this week')->format('d-m');
                    $endDate = $currentDate->modify('friday this week')->format('d-m');
                } else {
                    $startDate = $currentDate->modify('next monday')->format('d-m');
                    $endDate = $currentDate->modify('next friday')->format('d-m');
                }
            }
        }
    }

    $builder = MessageBuilder::new();

    // Create a new embed for week of the day
    $embed = new Embed(
        $discord,
        [
            'title' => 'Présence au bureau pour la semaine du ' . $startDate . ' au ' . $endDate,
            'type' => Embed::TYPE_RICH,
            'description' => 'Indiquer vos jours de présence prévues au bureau pour la semaine indiquée.',
            'color' => '004daa',
        ]
    );

    if (null !== $messagePresences) {
        foreach ($messagePresences as $day => $value) {
            $embed->addFieldValues($day, $value);
        }
    } else {
        $embed->addFieldValues('Lundi', '---');
        $embed->addFieldValues('Mardi', '---');
        $embed->addFieldValues('Mercredi', '---');
        $embed->addFieldValues('Jeudi', '---');
        $embed->addFieldValues('Vendredi', '---');
    }

    // Create several components for the message
    $actionRow = new ActionRow();
    $actionRow
        ->addComponent(initializeButton('Lundi', 'Lundi'))
        ->addComponent(initializeButton('Mardi', 'Mardi'))
        ->addComponent(initializeButton('Mercredi', 'Mercredi'))
        ->addComponent(initializeButton('Jeudi', 'Jeudi'))
        ->addComponent(initializeButton('Vendredi', 'Vendredi'));

    $builder
        ->addEmbed($embed)
        ->addComponent($actionRow);

    return $builder;
}

function handleOfficeButton(
    Discord $discord,
    Interaction $interaction,
    string $day,
): void {
    $messagePresences = [];

    foreach ($interaction->message->embeds as $embed) {
        if ($embed->type === Embed::TYPE_RICH) {
            foreach ($embed->fields as $field) {
                if ($field->name === $day) {
                    $users = [];

                    if (!strpos($field->value, '---')) {
                        $users = explode(', ', $field->value);
                    }

                    if (in_array('---', $users, true)) {
                        unset($users[array_search('---', $users, true)]);
                    }

                    if (!in_array($interaction->user->username, $users, true)) {
                        if (strpos($field->value, '---')) {
                            $field->value = '';
                            $users = [$interaction->user->username];
                        } else {
                            $users[] = $interaction->user->username;
                        }
                    } else {
                        unset($users[array_search($interaction->user->username, $users, true)]);
                    }

                    $values = implode(', ', $users);

                    $field->value = $values;

                    if (count($users) === 0) {
                        $field->value = '---';
                    }
                }

                $messagePresences[$field->name] = $field->value;
            }
        }
    }

    $interaction->message->edit(createOfficeMessageBuilder($discord, $interaction, $messagePresences, true));
}
