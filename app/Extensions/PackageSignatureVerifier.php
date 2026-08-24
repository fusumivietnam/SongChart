<?php

declare(strict_types=1);

namespace App\Extensions;

use App\Models\TrustedPublisher;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class PackageSignatureVerifier
{
    /** @return array{status:string,publisher:?string,warning:?string} */
    public function verify(string $archive, string $checksum, ExtensionManifest $manifest): array
    {
        $sidecar = $archive.'.sig.json';
        if (! is_file($sidecar)) {
            return ['status' => 'unsigned', 'publisher' => null, 'warning' => 'Package has no detached signature.'];
        }
        if (! extension_loaded('sodium')) {
            return ['status' => 'unverified', 'publisher' => null, 'warning' => 'Sodium extension is unavailable.'];
        }
        $data = json_decode((string) file_get_contents($sidecar), true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($data) || ! isset($data['publisher'], $data['signature'])) {
            throw new RuntimeException('Signature sidecar is invalid.');
        }
        if (! Schema::hasTable('trusted_publishers')) {
            return ['status' => 'unverified', 'publisher' => (string) $data['publisher'], 'warning' => 'Trusted publisher registry is not migrated.'];
        }
        $publisher = TrustedPublisher::query()->where('slug', (string) $data['publisher'])->where('status', 'active')->first();
        if (! $publisher || $publisher->revoked_at) {
            throw new RuntimeException('Package publisher is not trusted or has been revoked.');
        }
        $payload = $checksum."\n".$manifest->slug()."\n".$manifest->version();
        $signature = base64_decode((string) $data['signature'], true);
        $publicKey = base64_decode($publisher->public_key, true);
        if ($signature === false || $publicKey === false || ! sodium_crypto_sign_verify_detached($signature, $payload, $publicKey)) {
            throw new RuntimeException('Package signature verification failed.');
        }

        return ['status' => 'trusted_publisher', 'publisher' => $publisher->slug, 'warning' => null];
    }
}
