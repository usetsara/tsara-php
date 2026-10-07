# PHP SDK release readiness

Prepared on 2026-10-06 for `tsara/tsara-php` initial 0.1.x publication.

- Composer metadata and PSR-4 autoloading configured; version comes from Git tags.
- MIT license file supplied to match the existing composer.json license declaration.
- Sandbox business wallet transfers supported, matching the Node SDK.
- HTTP 200 error envelopes map to typed API exceptions while preserving the actual HTTP status.
- Regression tests cover sandbox authorization/idempotency, API error envelopes,
  incoming transfer sender details, and raw-body webhook signatures.
- GitHub checks configured for PHP 8.2, 8.3, 8.4, and 8.5.
- Archive exclusions remove tests and release tooling from consumer packages.

Local proof: PHP 8.2.4, 30 tests and 74 assertions pass; Composer strict validation
passes. Dependency audit and clean archive installation are checked during preparation.
CI on other PHP versions and public Packagist installation require the pushed commit
and first publication. No live financial requests were made.

Review: self-reviewed; no independent reviewer was available for this preparation.
See PUBLISHING.md for the remaining public release steps.
