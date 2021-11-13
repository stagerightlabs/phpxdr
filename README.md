![Read and Write XDR with PHP](https://banners.beyondco.de/PHPXDR.png?theme=light&packageManager=composer+require&packageName=stagerightlabs%2Fphpxdr&pattern=wiggle&style=style_1&description=Read+and+write+XDR+with+PHP&md=1&showWatermark=1&fontSize=100px&images=code)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stagerightlabs/phpxdr.svg?style=flat-square)](https://packagist.org/packages/stagerightlabs/phpxdr)
[![Total Downloads](https://img.shields.io/packagist/dt/stagerightlabs/phpxdr.svg?style=flat-square)](https://packagist.org/packages/stagerightlabs/phpxdr)
![GitHub Actions](https://github.com/stagerightlabs/phpxdr/actions/workflows/main.yml/badge.svg)

This package provides an implementation of the [RFC 4506](https://datatracker.ietf.org/doc/html/rfc4506.html) External Data Representation standard for PHP.  It is built to be extensible; you can encode and decode custom data objects as well as primitive generics.

This package is currently in beta; the API is still subject to change.

Important Note: [Quadruple-Precision Floating-Point](https://datatracker.ietf.org/doc/html/rfc4506.html#section-4.8) numbers are not supported by this package as a native type. However tools are provided for you to implement this in your own project should you have the need.

## Installation

You can install the package via composer:

```bash
composer require stagerightlabs/phpxdr
```

## Usage

```php
use StageRightLabs\PhpXdr\XDR;

// Encode
$xdr = XDR::fresh()
    ->write(42, XDR::INT)
    ->write(3.14, XDR::FLOAT)
    ->write('Bad Wolf', XDR::STRING);

$payload = $xdr->asBase64(); // AAAAKkBI9cMAAAAIQmFkIFdvbGY=

// Decode
$xdr = XDR::fromBase64('AAAAKkBI9cMAAAAIQmFkIFdvbGY=');
$int = $xdr->read(XDR::INT); // 42
$float = $xdr->read(XDR::FLOAT); // ~3.14
$string = $xdr->read(XDR::STRING); // 'Bad Wolf'
```

More usage information can be found in the wiki (coming soon.)

### Testing

```bash
./vendor/bin/phpunit
```

### Credits

This package draws a lot of inspiration from both [zulucrypto/stellar-api](https://github.com/zulucrypto/stellar-api) and [stellar/js-xdr](https://github.com/stellar/js-xdr).

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email ryan@stagerightlabs.com instead of using the issue tracker.

## License

The Apache License 2. Please see [License File](LICENSE.md) for more information.

## PHP Package Boilerplate

This package was generated using the [PHP Package Boilerplate](https://laravelpackageboilerplate.com) by [Beyond Code](http://beyondco.de/).
