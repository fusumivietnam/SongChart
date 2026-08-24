<?php

declare(strict_types=1);

namespace App\Support\Engineering;

final class LaravelMigrationColumnExtractor
{
    /**
     * @return list<string>
     */
    public function extract(string $source): array
    {
        $columns = [];

        if (preg_match('/\$table->id\(\s*\)/', $source) === 1) {
            $columns[] = 'id';
        }

        if (preg_match_all('/\$table->([A-Za-z_][A-Za-z0-9_]*)\(\s*[\'"]([^\'"]+)[\'"]/', $source, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                $method = $match[1];
                if (in_array($method, ['morphs', 'nullableMorphs'], true)) {
                    continue;
                }

                $columns[] = $match[2];
            }
        }

        if (preg_match_all('/\$table->nullableMorphs\(\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*[\'"][^\'"]+[\'"])?\s*\)/', $source, $matches) > 0) {
            foreach ($matches[1] as $name) {
                $columns[] = $name.'_type';
                $columns[] = $name.'_id';
            }
        }

        if (preg_match('/\$table->timestamps\(\s*\)/', $source) === 1) {
            $columns[] = 'created_at';
            $columns[] = 'updated_at';
        }

        return array_values(array_unique($columns));
    }
}
