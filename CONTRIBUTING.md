# Contributing

Thanks for your interest in TopLayerBundle! This document describes how to set up the project, what is expected from a pull request and how releases are made.

## Development environment

The repository ships a Docker Compose setup with PHP and Composer, so nothing besides Docker is needed on the host:

```bash
docker compose run --rm php composer install
```

The PHP version defaults to 8.2 and can be changed with `PHP_VERSION`:

```bash
PHP_VERSION=8.5 docker compose run --rm php composer install
```

A local PHP 8.2+ with Composer works as well, just drop the `docker compose run --rm php` prefix from the commands below.

## Checks

Run the same checks as the CI before opening a pull request:

```bash
docker compose run --rm php composer test
docker compose run --rm php composer phpstan
docker compose run --rm php composer cs-check
```

`composer cs-fix` fixes the coding standard violations reported by `cs-check`.

The CI also runs `composer validate --strict` and the tests across the supported PHP and Symfony versions, including the lowest dependencies.

## Pull requests

- Branch off `main` and open the pull request against `main`.
- Name the branch `<type>/<topic>`, e.g. `feat/toasts` or `docs/contributing`.
- Title the pull request after [Conventional Commits](https://www.conventionalcommits.org/): `feat: ...`, `fix: ...`, `docs: ...`, `ci: ...`, `chore: ...`. Pull requests are squash-merged, so the title becomes the commit message on `main`.
- Keep one topic per pull request.
- Add or update tests for the changed behaviour and update the documentation in `docs/` (and `README.md` if needed) when the public API or configuration changes.
- **Add an entry to `CHANGELOG.md`** under `## [Unreleased]`. The changelog is written by hand, following [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), with the sections `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed` and `Security`. Describe the change from the user's point of view.

## Releases

The project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html). While the version is `0.x`, a minor bump (`0.2.0` → `0.3.0`) may contain breaking changes.

The version lives in two places and **both must be bumped in the release pull request**:

1. **`CHANGELOG.md`**
    - rename `## [Unreleased]` to `## [X.Y.Z] - YYYY-MM-DD` and add a new empty `## [Unreleased]` above it,
    - update the comparison links at the bottom: `[Unreleased]` compares `vX.Y.Z...HEAD` and a new `[X.Y.Z]` link compares the previous tag with `vX.Y.Z`.
2. **`assets/package.json`** - set `"version"` to `X.Y.Z`. This is the version of the bundle's Symfony UX JavaScript package, and it must match the tag.

`composer.json` has no `version` field, Composer takes the version from the Git tag.

Then:

1. Open the release pull request titled `chore: release X.Y.Z` and merge it into `main`.
2. Tag the merge commit on `main` with `vX.Y.Z` and push the tag:

    ```bash
    git switch main
    git pull
    git tag vX.Y.Z
    git push origin vX.Y.Z
    ```

3. Optionally create a GitHub release for the tag, using the version's section of `CHANGELOG.md` as the notes.

Two checks guard the versions:

- the `testPackageVersionMatchesLatestRelease` test fails when `assets/package.json` does not match the latest released heading in `CHANGELOG.md`, so a release pull request that bumps only one of them fails CI,
- the `Release check` workflow runs on every pushed `v*` tag and fails when the tag does not match both `assets/package.json` and `CHANGELOG.md`.
