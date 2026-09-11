<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\DomainContracts\PolymorphicReferenceIntegrity;
use Illuminate\Console\Command;

final class VerifyPolymorphicIntegrityCommand extends Command
{
    protected $signature = 'songchart:integrity:polymorphic {--json : Emit machine-readable JSON}';

    protected $description = 'Verify canonical polymorphic references resolve to registered entity types and existing rows.';

    public function handle(PolymorphicReferenceIntegrity $integrity): int
    {
        $result = $integrity->scan();

        if ((bool) $this->option('json')) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info('Checked '.$result['checked'].' canonical polymorphic references.');
            foreach ($result['failures'] as $failure) {
                $this->error(implode(' | ', array_map(
                    static fn (string $key, string $value): string => $key.'='.$value,
                    array_keys($failure),
                    array_values($failure),
                )));
            }
        }

        return $result['failures'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
