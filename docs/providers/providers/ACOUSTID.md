# AcoustID

Status: P1 optional identification provider.

## Use

Identify an authorized audio file/fingerprint and map it to candidate recordings.

## Rules

- Register a SongChart application key.
- Never obtain audio by downloading from streaming providers.
- Fingerprinting is allowed only for files the user is authorized to process.
- Prefer POST for long fingerprints.
- Return candidates with scores; never auto-merge solely from one fingerprint result.
- Submission of fingerprints requires separate user authorization and keys.
- Store fingerprint hashes/derived data only under an explicit retention policy.

Official reference:
- https://acoustid.org/webservice
