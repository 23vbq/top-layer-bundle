# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Toasts built on Symfony flash messages: `Toaster` service, container and flashes templates rendered as Turbo Streams, `top_layer_toast*` Twig functions and `top_layer.toasts` configuration
- Toasts stay above modal dialogs and clickable: the new `top-layer` Stimulus controller keeps the toast container in the topmost modal

## [0.1.0] - 2026-11-15

### Added

- Modal and drawer Twig components based on native `<dialog>`
- Bundle wiring: @TopLayer Twig namespace, AssetMapper path and Symfony UX package.json
- Turbo Stream actions `close_dialog`, `reload_frame`, `refresh_frame` and `visit_parent_frame`, with the `TopLayerStream` PHP helper and `top_layer_*` Twig functions
- Requires Turbo 8 (`@hotwired/turbo` `^8.0`, `symfony/ux-turbo` `^2.15`)

[Unreleased]: https://github.com/23vbq/top-layer-bundle/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/23vbq/top-layer-bundle/releases/tag/v0.1.0
