<?php

declare(strict_types=1);

namespace App\Support\DomainContracts;

use App\Domain\Catalog\Enums\EntityType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use JsonException;
use RuntimeException;

final readonly class PolymorphicReferenceIntegrity
{
    public function __construct(private DomainContractRegistry $contracts) {}

    /** @return array{checked:int, failures:list<array<string, string>>} */
    public function scan(): array
    {
        $checked = 0;
        $failures = [];

        foreach ($this->surfaces() as $surface) {
            $table = $surface['table'];
            $typeColumn = $surface['type_column'];
            $idColumn = $surface['id_column'];

            if (! Schema::hasTable($table)) {
                $failures[] = [
                    'table' => $table,
                    'type_column' => $typeColumn,
                    'id_column' => $idColumn,
                    'reason' => 'missing_table',
                ];
                continue;
            }

            foreach (DB::table($table)->select([$typeColumn, $idColumn])->cursor() as $row) {
                $rawType = $row->{$typeColumn} ?? null;
                $rawId = $row->{$idColumn} ?? null;

                if ($rawType === null || $rawId === null) {
                    continue;
                }

                $checked++;
                $type = EntityType::tryFrom((string) $rawType);

                if ($type === null) {
                    $failures[] = [
                        'table' => $table,
                        'type_column' => $typeColumn,
                        'id_column' => $idColumn,
                        'entity_type' => (string) $rawType,
                        'entity_id' => (string) $rawId,
                        'reason' => 'unknown_entity_type',
                    ];
                    continue;
                }

                $entity = $this->contracts->entity($type);
                $entityTable = $entity['table'] ?? null;

                if (! is_string($entityTable) || $entityTable === '') {
                    throw new RuntimeException("Domain contract table for [{$type->value}] is missing.");
                }

                if (! DB::table($entityTable)->where('id', (string) $rawId)->exists()) {
                    $failures[] = [
                        'table' => $table,
                        'type_column' => $typeColumn,
                        'id_column' => $idColumn,
                        'entity_type' => $type->value,
                        'entity_id' => (string) $rawId,
                        'reason' => 'missing_entity_row',
                    ];
                }
            }
        }

        return ['checked' => $checked, 'failures' => $failures];
    }

    /** @return list<array{table:string,type_column:string,id_column:string}> */
    private function surfaces(): array
    {
        $path = base_path('docs/project/governance/polymorphic-reference-contract.json');
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read polymorphic reference contract [{$path}].");
        }

        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Polymorphic reference contract is invalid JSON.', previous: $exception);
        }

        $surfaces = is_array($decoded) ? ($decoded['surfaces'] ?? null) : null;

        if (! is_array($surfaces)) {
            throw new RuntimeException('Polymorphic reference contract surfaces are missing.');
        }

        $normalized = [];
        foreach ($surfaces as $surface) {
            if (! is_array($surface)) {
                throw new RuntimeException('Polymorphic reference surface must be an object.');
            }

            $table = $surface['table'] ?? null;
            $typeColumn = $surface['type_column'] ?? null;
            $idColumn = $surface['id_column'] ?? null;

            if (! is_string($table) || $table === '' || ! is_string($typeColumn) || $typeColumn === '' || ! is_string($idColumn) || $idColumn === '') {
                throw new RuntimeException('Polymorphic reference surface is incomplete.');
            }

            $normalized[] = ['table' => $table, 'type_column' => $typeColumn, 'id_column' => $idColumn];
        }

        return $normalized;
    }
}
