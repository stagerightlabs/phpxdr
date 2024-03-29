# Changelog

All notable changes to `phpxdr` will be documented in this file

## 0.01.01 - 2023-11-10

### Removed

- Removed redundant doc block return types from interface definitions.

## 0.01.00 - 2023-11-09

## Changed

- Breaking change: altered the `XdrUnion` interface; hopefully it is now easier to understand and use.

## 0.0.15 - 2022-06-01

## Added

- Added a `toBytes()` convenience method for returning buffer contents as a string of raw bytes.

## Changed

- Adjusted interface class doc-blocks: 'Retrieve' is now 'Return'

## 0.0.14 - 2022-01-31

## Added

- Added test coverage generation to the PHPUnit configuration.  Requires xdebug or PCOV.
- Added some additional test coverage.

## Changed

- The method return types have been tweaked a bit to hopefully improve IDE introspection and code coverage.
- Fixed a bug in a Union test that had previously been missed.


## 0.0.13 - 2022-01-24

### Changed

- Altered the `XdrEnum` interface: `getXdrValue` is now `getXdrSelection` And `isValidXdrValue` is now `isValidXdrSelection`.

## 0.0.12 - 2022-01-04

### Changed

- Encoding Fixed length arrays will now throw an error exception if the array count does not exactly match the defined fixed length.

## 0.0.11 - 2022-01-03

### Added

- Added more test coverage for the `XdrArray` interface methods.

### Changed

- Fixed a bug that prevented fixed length arrays from being read correctly.

## 0.0.10 - 2022-01-03

### Changed

- The `getXdrFixedCount()` method in the `XdrArray` interface has been renamed to `getXdrLength()` which should hopefully be more clear.

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
