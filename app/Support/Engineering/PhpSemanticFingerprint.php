<?php

declare(strict_types=1);

namespace App\Support\Engineering;

final class PhpSemanticFingerprint
{
    public const ALGORITHM = 'php-token-semantic-v1';

    public static function fromFile(string $path): string
    {
        $source = file_get_contents($path);

        if ($source === false) {
            throw new \RuntimeException("Unable to read PHP source [{$path}].");
        }

        return self::fromSource($source);
    }

    public static function fromSource(string $source): string
    {
        $semantic = [];

        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $semantic[] = 'CHAR:'.$token;

                continue;
            }

            [$id, $text] = $token;

            if (in_array($id, [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            if (in_array($id, [T_OPEN_TAG, T_CLOSE_TAG], true)) {
                $semantic[] = token_name($id);

                continue;
            }

            $semantic[] = token_name($id).':'.$text;
        }

        return hash('sha256', implode("\n", $semantic));
    }
}
