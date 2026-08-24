<?php

declare(strict_types=1);

namespace App\Extensions;

use RuntimeException;
use ZipArchive;

final class SafeZipExtractor
{
    private const MAX_FILES = 5000;

    private const MAX_UNCOMPRESSED_BYTES = 268435456;

    public function extract(string $archive, string $destination): void
    {
        $zip = new ZipArchive;

        if ($zip->open($archive) !== true) {
            throw new RuntimeException('Unable to open ZIP archive.');
        }

        try {
            if ($zip->numFiles > self::MAX_FILES) {
                throw new RuntimeException('ZIP contains too many files.');
            }

            $total = 0;
            $seen = [];

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                $stat = $zip->statIndex($i);

                if ($name === false || $this->isUnsafe($name)) {
                    throw new RuntimeException('Unsafe path detected in ZIP archive.');
                }

                $key = mb_strtolower(str_replace('\\', '/', $name));

                if (isset($seen[$key])) {
                    throw new RuntimeException('ZIP contains duplicate case-insensitive paths.');
                }

                $seen[$key] = true;
                $total += (int) ($stat['size'] ?? 0);

                if ($total > self::MAX_UNCOMPRESSED_BYTES) {
                    throw new RuntimeException('ZIP extracted size exceeds limit.');
                }
            }

            if (! is_dir($destination) && ! mkdir($destination, 0755, true) && ! is_dir($destination)) {
                throw new RuntimeException('Unable to create extraction directory.');
            }

            if (! $zip->extractTo($destination)) {
                throw new RuntimeException('Unable to extract ZIP archive.');
            }
        } finally {
            $zip->close();
        }
    }

    private function isUnsafe(string $name): bool
    {
        $normalized = str_replace('\\', '/', $name);

        return str_starts_with($normalized, '/')
            || preg_match('/^[A-Za-z]:\//', $normalized) === 1
            || in_array('..', explode('/', $normalized), true)
            || str_contains($normalized, "\0");
    }
}
