# Changelog

All notable changes to `phpxdr` will be documented in this file

## 0.0.4 - 2021-12-04

### Changed

- Adjusted the `XdrOptional` interface to hopefully be more intuitive.  Removed references to "evaluation" in favor of "hasValue".

## 0.0.3 - 2021-11-28

### Added

- Added better return types to `getXdrDiscriminator()` method in the `XdrUnion` interface.

### Removed

- Removed the `isValidXdrDiscriminator()` method from the `XdrUnion` interface.

## 0.0.2 - 2021-11-27

### Added

- comprehensive usage guid in project wiki.
- base16 alias for hex methods.

### Changed

- Moved phpunit config from `phpunit.xml` to `phpunit.xml.dist`

### Removed

- Removed redundant methods from the `XdrUnion` interface.

## 0.0.1 - 2021-11-12

### Added

- initial proof of concept and beta release.
