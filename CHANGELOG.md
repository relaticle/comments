# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed
- All user-facing strings now resolve from the `comments::` translation namespace, so `php artisan vendor:publish --tag=comments-translations` overrides actually take effect (#21).

### Changed
- BREAKING (translations only): All UI strings now resolve from `comments::comments.<key>` (or `comments::comments.<group>.<key>` for grouped sections). Apps that published an earlier draft of `comments.php` should re-publish with `php artisan vendor:publish --tag=comments-translations --force`.

## v1.0.0-alpha.7 - 2026-04-22

<!-- Release notes generated using configuration in .github/release.yml at 1.x -->
### What's Changed

#### Other Changes

* feat: add configurable user name resolution for mentions by @Ilyapashayan20 in https://github.com/relaticle/comments/pull/18

**Full Changelog**: https://github.com/relaticle/comments/compare/v1.0.0-alpha.6...v1.0.0-alpha.7

## v1.0.0-alpha.6 - 2026-04-15

<!-- Release notes generated using configuration in .github/release.yml at 1.x -->
### What's Changed

#### Other Changes

* fix: resolve comment badge showing wrong count in table list view by @Ilyapashayan20 in https://github.com/relaticle/comments/pull/16

**Full Changelog**: https://github.com/relaticle/comments/compare/v1.0.0-alpha.5...v1.0.0-alpha.6

## v1.0.0-alpha.5 - 2026-04-14

<!-- Release notes generated using configuration in .github/release.yml at 1.x -->
### What's Changed

#### Other Changes

* fix: show reply instantly after adding without page refresh by @Ilyapashayan20 in https://github.com/relaticle/comments/pull/10
* test: add Livewire feature tests for validation errors and user inter… by @Ilyapashayan20 in https://github.com/relaticle/comments/pull/12
* Fix/multiple bugs by @Ilyapashayan20 in https://github.com/relaticle/comments/pull/13
* fix: resolve mention display name from user ID instead of stored label by @Ilyapashayan20 in https://github.com/relaticle/comments/pull/15

### New Contributors

* @Ilyapashayan20 made their first contribution in https://github.com/relaticle/comments/pull/10

**Full Changelog**: https://github.com/relaticle/comments/compare/v1.0.0-alpha.3...v1.0.0-alpha.5

## v1.1.0 - 2026-04-10

### What's Changed

#### New Features

* feat: implement multi-tenancy support with TenantScope, config flag, global scope, and policy checks
* feat: add `tenant_column_type` config option to support UUID and string tenant keys in migrations
* feat: add `FeatureConfigurator` and `CommentsFeature` enum for optional feature toggling

#### Bug Fixes

* fix: fail closed in `CommentPolicy` when tenant resolver returns null on web requests
* fix: implement `__set_state` on `FeatureConfigurator` for `config:cache` compatibility
* fix: remove unused `MULTI_TENANCY` case from `CommentsFeature` enum
* fix: make mention regex robust to any `@` encoding from HTML sanitizer

#### Documentation

* docs: add multi-tenancy setup guide to README and docs site

**Full Changelog**: https://github.com/relaticle/comments/compare/v1.0.0-alpha.4...v1.1.0

## v1.0.0-alpha.4 - 2026-03-31

<!-- Release notes generated using configuration in .github/release.yml at 1.x -->
### What's Changed

#### Other Changes

* fix: show reply instantly after adding without page refresh by @Ilyapashayan20 in https://github.com/relaticle/comments/pull/10

### New Contributors

* @Ilyapashayan20 made their first contribution in https://github.com/relaticle/comments/pull/10

**Full Changelog**: https://github.com/relaticle/comments/compare/v1.0.0-alpha.3...v1.0.0-alpha.4
