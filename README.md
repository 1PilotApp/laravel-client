# 1Pilot.io connector for Laravel applications

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]

[1Pilot.io](1pilot.io) is a central Dashboard to manage your websites. It offers you a simple way to have all your websites
and applications monitored on the same place. For Laravel applications you will benefit of the Uptime and Certificate 
monitoring as well as the report of installed package and updates available: a simple way to keep your apps up-to-date
and secure.

## Install

``` bash
composer require 1pilotapp/laravel-client
```

You need to publish the config and set `private_key` to a random string
```
php artisan vendor:publish --provider="OnePilot\Client\ClientServiceProvider"
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email support@1pilot.io instead of using the issue tracker.

## Credits

- [1Pilot.io][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/1PilotApp/laravel-client.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/1PilotApp/laravel-client/master.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/1pilotapp/laravel-client.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/1pilotapp/laravel-client
[link-travis]: https://travis-ci.org/1PilotApp/laravel-client
[link-downloads]: https://packagist.org/packages/1PilotApp/laravel-client
[link-author]: https://github.com/1PilotApp
[link-contributors]: ../../contributors
