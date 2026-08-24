<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\TrustedPublisher;
use Illuminate\Console\Command;

final class AddTrustedPublisherCommand extends Command
{
    protected $signature = 'extension:publisher-add {slug} {name} {public-key-file}';

    protected $description = 'Register an Ed25519 trusted publisher public key.';

    public function handle(): int
    {
        $raw = trim((string) file_get_contents((string) $this->argument('public-key-file')));
        $key = base64_decode($raw, true);
        if ($key === false || strlen($key) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            $this->components->error('Public key must be base64 Ed25519 public key.');

            return self::FAILURE;
        } TrustedPublisher::query()->updateOrCreate(['slug' => (string) $this->argument('slug')], ['name' => (string) $this->argument('name'), 'public_key' => $raw, 'fingerprint' => hash('sha256', $key), 'status' => 'active', 'valid_from' => now()]);
        $this->components->info('Trusted publisher registered.');

        return self::SUCCESS;
    }
}
