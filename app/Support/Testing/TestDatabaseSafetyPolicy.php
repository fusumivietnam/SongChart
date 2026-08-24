<?php

declare(strict_types=1);

namespace App\Support\Testing;

final class TestDatabaseSafetyPolicy
{
    /** @return list<string> */
    public function validateNames(string $developmentDatabase, string $testDatabase): array
    {
        $developmentDatabase = trim($developmentDatabase);
        $testDatabase = trim($testDatabase);
        $errors = [];

        if ($testDatabase === '') {
            $errors[] = 'TEST_PGSQL_DATABASE must be explicitly configured.';
        }

        if ($developmentDatabase !== '' && $testDatabase === $developmentDatabase) {
            $errors[] = sprintf('PostgreSQL test database "%s" is the same as the development database.', $testDatabase);
        }

        if ($testDatabase !== '' && ! str_ends_with(mb_strtolower($testDatabase), '_test')) {
            $errors[] = sprintf('PostgreSQL test database "%s" must end with _test.', $testDatabase);
        }

        return $errors;
    }
}
