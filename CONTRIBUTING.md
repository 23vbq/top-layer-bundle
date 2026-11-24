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
docker compose run --rm php composer validate --strict
```

`composer cs-fix` fixes the coding standard violations reported by `cs-check`.

The CI runs the tests across the supported PHP and Symfony versions, including the lowest dependencies on PHP 8.2. To reproduce the lowest dependencies job locally:

```bash
docker compose run --rm php composer update --prefer-lowest --prefer-stable
docker compose run --rm php composer test
```

Run `composer update` afterwards to go back to the highest dependencies. `composer.lock` is not committed, so this only changes your local install.

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

`composer.json` has no `version` field, Composer takes the version from the Git tag. Its `extra.branch-alias` maps `dev-main` to `0.x-dev`; update it in the pull request that starts a new major version, e.g. to `1.x-dev` for `1.0.0`.

Then:

1. Open the release pull request titled `chore: release X.Y.Z` and merge it into `main`.
2. Tag the squash commit of the release pull request on `main` with an annotated `vX.Y.Z` tag and push the tag. Tag that commit by its SHA, not the current `HEAD`, which may already contain pull requests merged after the release. Before tagging, check that the commit's `assets/package.json` version is `X.Y.Z`:

    ```bash
    git switch main
    git pull --ff-only
    git show <release-commit-sha>:assets/package.json | grep '"version"'
    git tag -a vX.Y.Z -m "vX.Y.Z" <release-commit-sha>
    git push origin vX.Y.Z
    ```

3. Optionally create a GitHub release for the tag, using the version's section of `CHANGELOG.md` as the notes.

Two checks guard the versions:

- the `testPackageVersionMatchesLatestRelease` test fails when `assets/package.json` does not match the latest released heading in `CHANGELOG.md`, so a release pull request that bumps only one of them fails CI,
- the `Release check` workflow runs on every pushed `v*` tag and fails when the tag does not match both `assets/package.json` and `CHANGELOG.md`.

If the `Release check` fails, the tag name or the tagged commit is wrong. Delete the tag (`git push origin :refs/tags/vX.Y.Z` and `git tag -d vX.Y.Z`) and tag the release commit again with the version from `CHANGELOG.md`. If the wrong tag may already have been installed, release a new patch version instead of reusing the tag.
