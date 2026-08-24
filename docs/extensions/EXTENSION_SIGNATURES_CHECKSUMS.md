# Extension Signatures and Checksums

## Checksum

Mọi package phải lưu:
- filename;
- byte size;
- SHA-256;
- actor;
- upload time;
- source;
- inspection result.

Checksum kiểm tra integrity, không chứng minh publisher đáng tin.

## Signature status

- unsigned
- self_signed
- trusted_publisher
- songchart_verified
- invalid
- revoked

Local có thể cài unsigned sau cảnh báo.
Production mặc định chặn package bên thứ ba unsigned, trừ khi super-admin override rõ ràng.

Signature nên bao phủ:
- package SHA-256;
- canonical manifest JSON;
- slug;
- version.

Private signing key không được nằm trong SongChart hoặc package.
