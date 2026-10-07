# Publishing Tsara PHP

Package: `tsara/tsara-php`
Repository: https://github.com/usetsara/tsara-php

## 1. Validate and push

From the SDK directory:

```bash
composer install
composer check
composer audit
git add composer.json src tests README.md LICENSE .github .gitattributes PUBLISHING.md RELEASE-READINESS.md
git commit -m "Prepare PHP SDK for Packagist"
git push origin main
```

Wait for **PHP SDK checks** to pass on GitHub. CI covers PHP 8.2 through 8.5.
Local validation covers the PHP version installed on your computer.

## 2. Tag the release

Check existing tags first with `git tag`. If `v0.1.0` does not exist:

```bash
git tag -a v0.1.0 -m "Tsara PHP SDK 0.1.0"
git push origin v0.1.0
```

If that version already exists, choose the next unused patch version. Do not
move or overwrite a released tag. Composer reads the version from Git tags;
do not add a `version` field to composer.json. This is an initial 0.1.x release,
not a 1.0 compatibility guarantee.

A GitHub Release with notes is optional for Packagist. Unlike the Node publishing
workflow, Packagist can discover the pushed tag without a GitHub Release.

## 3. Submit to Packagist

1. Sign in at https://packagist.org using GitHub.
2. Open https://packagist.org/packages/submit.
3. Enter `https://github.com/usetsara/tsara-php` and submit.
4. Confirm the package name is `tsara/tsara-php` and the tagged version appears.
5. Enable automatic updates. Allow the Packagist integration access to the
   `usetsara` organization if required; an organization owner may need to approve it.

The repository must be public. The `tsara` vendor namespace must be available
or your Packagist account must already have permission to publish under it.
If a package already exists, update that package rather than resubmitting it.

## 4. Verify a fresh installation

In a separate empty directory:

```bash
composer require tsara/tsara-php:^0.1
composer show tsara/tsara-php
```

Load `vendor/autoload.php` and instantiate `new \Tsara\Client('sk_test_example')`.
This verifies installation and autoloading without making an API request.
Use real sandbox credentials only for your subsequent integration tests.

## Future releases

Run checks, commit and push changes, then push the next unused version tag.
With automatic updates enabled, Packagist imports the version. If it does not,
use the Update button on the package page and check the GitHub integration.

## References

- https://packagist.org/about
- https://getcomposer.org/doc/04-schema.md
