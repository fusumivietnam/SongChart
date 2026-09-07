<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

final class DevelopmentDatabaseStatusCommand extends Command
{
    protected $signature = 'development:database-status {--json}';

    protected $description = 'Inspect the configured SongChart development PostgreSQL authority.';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            return $this->failCommand('Development database diagnostics are available only in the local environment.');
        }

        $configuration = (array) config('songchart.development.database', []);
        $mode = (string) ($configuration['mode'] ?? '');
        $expectedDatabase = trim((string) ($configuration['expected_database'] ?? ''));

        try {
            $row = DB::connection()->selectOne(
                <<<'SQL'
select
    current_database() as database_name,
    current_user as database_user,
    coalesce(inet_server_addr()::text, 'local-socket') as server_address,
    current_setting('server_version') as server_version
SQL
            );
        } catch (Throwable $exception) {
            return $this->failCommand(
                'Configured development PostgreSQL is unreachable: '.$exception->getMessage(),
                [
                    'mode' => $mode,
                    'reachable' => false,
                ],
            );
        }

        $actualDatabase = (string) ($row->database_name ?? '');

        if ($expectedDatabase !== '' && $actualDatabase !== $expectedDatabase) {
            return $this->failCommand(
                sprintf(
                    'Development database identity mismatch: expected [%s], connected [%s].',
                    $expectedDatabase,
                    $actualDatabase,
                ),
                [
                    'mode' => $mode,
                    'reachable' => true,
                    'expected_database' => $expectedDatabase,
                    'database' => $actualDatabase,
                ],
            );
        }

        $payload = [
            'ok' => true,
            'mode' => $mode,
            'reachable' => true,
            'database' => $actualDatabase,
            'database_user' => (string) ($row->database_user ?? ''),
            'server_address' => (string) ($row->server_address ?? ''),
            'server_version' => (string) ($row->server_version ?? ''),
            'expected_database' => $expectedDatabase !== '' ? $expectedDatabase : null,
            'identity_matches' => $expectedDatabase === '' || $expectedDatabase === $actualDatabase,
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode(
                $payload,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            ));

            return self::SUCCESS;
        }

        $this->components->info('SongChart development PostgreSQL authority');
        $this->table(
            ['Property', 'Value'],
            [
                ['Mode', $payload['mode']],
                ['Reachable', 'yes'],
                ['Database', $payload['database']],
                ['Expected database', $payload['expected_database'] ?? '(not constrained)'],
                ['User', $payload['database_user']],
                ['Server address', $payload['server_address']],
                ['Server version', $payload['server_version']],
                ['Identity matches', $payload['identity_matches'] ? 'yes' : 'no'],
            ],
        );

        return self::SUCCESS;
    }

    /** @param array<string, mixed> $payload */
    private function failCommand(string $message, array $payload = []): int
    {
        if ($this->option('json')) {
            $this->line((string) json_encode(
                [
                    'ok' => false,
                    'error' => $message,
                    ...$payload,
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            ));

            return self::FAILURE;
        }

        $this->components->error($message);

        return self::FAILURE;
    }
}
