# E1 Implementation Status

Implemented:
- database extension/release/operation registry;
- ZIP inspection without package execution;
- SHA-256 package checksum;
- core/PHP/PHP-extension compatibility;
- plugin dependency/conflict checks;
- extraction limits and duplicate-case path protection;
- versioned `releases/<version>` installation;
- install disabled by default;
- health-gated plugin enable;
- plugin disable preserving code/data;
- theme activation;
- database-backed runtime with file-registry recovery fallback;
- audit records for install/enable/disable/activate.

Deferred to E2:
- upgrade command and previous-release switching;
- migration declarations/execution sandbox;
- snapshots and rollback command;
- admin extension UI;
- package signatures and trusted publishers.
