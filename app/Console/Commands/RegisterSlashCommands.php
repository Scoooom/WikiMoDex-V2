<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RegisterSlashCommands extends Command
{
    protected $signature = 'discord:register-commands';
    protected $description = 'Register slash commands with Discord';

    public function handle()
    {
        $token     = trim(file_get_contents('/home/void/.discord_token'));
        $appId     = env('DISCORD_CLIENT_ID');

        $commands = [
            [
                'name'        => 'form',
                'description' => 'Look up a Glitch, Core, Smitty, or Smitty Form',
                'options'     => [
                    [
                        'type'         => 3,
                        'name'         => 'name',
                        'description'  => 'The name of the form to look up',
                        'required'     => true,
                        'autocomplete' => true,
                    ]
                ]
            ],
            [
                'name'        => 'wiki',
                'description' => 'Get a link to the WikiMoDex and its features',
            ],
            [
                'name'        => 'ability',
                'description' => 'Look up a PokeVoid ability and its description',
                'options'     => [
                    [
                        'type'         => 3,
                        'name'         => 'name',
                        'description'  => 'The name of the ability to look up',
                        'required'     => true,
                        'autocomplete' => true,
                    ]
                ]
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bot {$token}",
            'Content-Type'  => 'application/json',
        ])->put("https://discord.com/api/v10/applications/{$appId}/commands", $commands);

        if ($response->successful()) {
            $this->info('Slash commands registered successfully!');
            $this->line(json_encode($response->json(), JSON_PRETTY_PRINT));
        } else {
            $this->error('Failed to register commands: ' . $response->body());
        }
    }
}
