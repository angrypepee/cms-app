<?php

namespace App\Bootstrap;

use Illuminate\Foundation\Application;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ProtectProductionDatabase
{
    /**
     * Bootstrap the application services.
     */
    public function bootstrap(Application $app): void
    {
        if (!$app->environment('production')) {
            return;
        }

        $dangerous_commands = [
            'migrate:fresh',
            'migrate:reset',
            'db:seed',
            'tinker',
        ];

        if ($app->has('events')) {
            $app['events']->listen('console.command', function (ConsoleCommandEvent $event) use ($dangerous_commands) {
                $command_name = $event->getCommand()->getName();

                if (in_array($command_name, $dangerous_commands)) {
                    $event->disableOutput();
                    throw new \RuntimeException(
                        "🚨 DANGEROUS COMMAND BLOCKED IN PRODUCTION: {$command_name}\n\n" .
                        "This command DELETES ALL DATA. It is forbidden in production.\n\n" .
                        "If you need to run migrations, use:\n" .
                        "  php artisan migrate (new migrations only)\n\n" .
                        "Before any database change, create a backup:\n" .
                        "  docker compose exec -T db mariadb-dump -u cms -pcms_secret cms_app > backups/backup_\$(date +%s).sql"
                    );
                }
            });
        }
    }
}
