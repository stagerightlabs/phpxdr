# Changelog

All notable changes to `phpxdr` will be documented in this file

## 0.0.9 - 2022-01-01

### Changed

- The previous update did not account for unions that represent fixed length values.  The `XdrUnion` interface has been altered so that fixed lengths are now also defined statically and will be looked up by discriminator.

## 0.0.8 - 2021-12-31

### Removed

- Removed `setValueFromXdr()` method from the `XdrUnion` interface. This method felt redundant from a usability perspective though it had a good technical reason to be there.

### Added

- Added a new static `getXdrArms` method to the `XdrUnion` interface. This will ensure that we can determine the union's value type without having to instantiate an empty class ahead of time. Going forward all union arms will have to be defined statically, which is probably for the best.

## 0.0.7 - 2021-12-18

### Added

- Added [phpstan](https://phpstan.org/) as a dev dependency.

### Changed

- The fix in 0.0.5 for accepting class names as type indicators did not quite go far enough. More test coverage has been added and some further adjustments made to hopefully fix this issue for real.

## 0.0.6 - 2021-12-16

### Changed

- Allow union values to be XDR::VOID (aka null.)

## 0.0.5 - 2021-12-16

### Changed

- Fix bug preventing the encoding of variable arrays.
- Allow valid class names to be accepted as XDR types. The values are inspected to determine their true xdr types.

## 0.0.4 - 2021-12-04

### Changed

- Adjusted the `XdrOptional` interface to hopefully be more intuitive. Removed references to "evaluation" in favor of "hasValue".

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
