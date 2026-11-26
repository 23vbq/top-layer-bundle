# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.1] - 2026-11-26

### Added

- `CONTRIBUTING.md` with the development setup, pull request rules and release process

### Changed

- Documentation moved from the README to `docs/`. Links to README sections such as `README.md#toasts` or `README.md#styling` no longer work; the sections are now in `docs/modal-and-drawer.md`, `docs/turbo-streams.md`, `docs/toasts.md` and `docs/styling.md`

### Fixed

- The `assets/package.json` version now tracks the released version (it still said `0.1.0` in the 0.2.0 release); a test checks that it matches the latest `CHANGELOG.md` release heading, so the release commit must bump both

## [0.2.0] - 2026-11-21

### Added

- Toasts built on Symfony flash messages: `Toaster` service, container and flashes templates rendered as Turbo Streams, `top_layer_toast*` Twig functions and `top_layer.toasts` configuration
- Toasts stay above modal dialogs and clickable: the new `top-layer` Stimulus controller keeps the toast container in the topmost modal
- Pending flashes are added as toasts to Turbo Stream responses and inside the requested Turbo Frame, so templates no longer need to include `_flashes.html.twig`; turned off with `top_layer.toasts.inject_into_turbo_responses: false`
- The toasts container template renders the pending flashes itself, leaving those of a Turbo Frame request to be added inside the frame, so the layout no longer includes `_flashes.html.twig`

## [0.1.0] - 2026-11-15

### Added

- Modal and drawer Twig components based on native `<dialog>`
- Bundle wiring: @TopLayer Twig namespace, AssetMapper path and Symfony UX package.json
- Turbo Stream actions `close_dialog`, `reload_frame`, `refresh_frame` and `visit_parent_frame`, with the `TopLayerStream` PHP helper and `top_layer_*` Twig functions
- Requires Turbo 8 (`@hotwired/turbo` `^8.0`, `symfony/ux-turbo` `^2.15`)

[Unreleased]: https://github.com/23vbq/top-layer-bundle/compare/v0.2.1...HEAD
[0.2.1]: https://github.com/23vbq/top-layer-bundle/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/23vbq/top-layer-bundle/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/23vbq/top-layer-bundle/releases/tag/v0.1.0
