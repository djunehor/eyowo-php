# eyowo-php
[![CircleCI](https://circleci.com/gh/djunehor/eyowo-php.svg?style=svg)](https://circleci.com/gh/djunehor/eyowo-php)
[![Latest Stable Version](https://poser.pugx.org/djunehor/eyowo-php/v/stable)](https://packagist.org/packages/djunehor/eyowo-php)
[![Total Downloads](https://poser.pugx.org/djunehor/eyowo-php/downloads)](https://packagist.org/packages/djunehor/eyowo-php)
[![License](https://poser.pugx.org/djunehor/eyowo-php/license)](https://packagist.org/packages/djunehor/eyowo-php)
[![StyleCI](https://github.styleci.io/repos/224398453/shield?branch=master)](https://github.styleci.io/repos/224398453)
[![Build Status](https://scrutinizer-ci.com/g/djunehor/eyowo-php/badges/build.png?b=master)](https://scrutinizer-ci.com/g/djunehor/eyowo-php/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/djunehor/eyowo-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/djunehor/eyowo-php/?branch=master)

A PHP API wrapper for [Eyowo](https://eyowo.com/).

## Requirements
- Curl 7.34.0 or more recent (Unless using Guzzle)
- PHP 5.4.0 or more recent
- OpenSSL v1.0.1 or more recent

## Install

### Via Composer

``` bash
    $ composer require djunehor/eyowo-php
```

## Usage

### 1. Get your API KEYS
- Goto [Eyowo Developer Portal](https://developer.eyowo.com/apps)
- Register and Login
- Create an app

### 2. configure package (optional)
- Add `EYOWO_APP_KEY` and `EYOWO_APP_SECRET` to your `.env` and set the values

### 3. Initialise API
```php
use Djunehor\Eyowo\Api;

$production is an optional boolean parameter to specify if to use production URL or sandbox.
// Default is false
$eyowo = new Api($appKey, $production); //if appKey is not passed, package uses value in .env
```
NOTE: The sandbox URL was not responding as at last test. So, you might just set $production as true

#### Validate a user
```php
// phone should be in the format 2348020000000
$eyowo->validate($phone);
```

#### Authenticate user
```php
// sends SMS to user phone
$eyowo->initiateAuthorization($phone);

// $code is the 6-digit number send to user phone
$eyowo->generateToken($phone, $code);
[
'success' => true,
'data' => [
'accessToken' => kjaskajs7a8s6as7a7s68a,
'refreshToken' => askhas7a7s6a7yajgsa67u
]

$walletToken = $eyowo->getAccessToken();
$refreshToken = $eyowo->getRefreshToken();
```

#### Refresh Token
```php
$eyowo->refreshToken($refreshToken);
```

#### Get banks
```php
$output = $eyowo->banks();
[
'success' => true,
'data' => [
    'banks' => [
            [
                        "bankCode" => "090270",
                        "bankName" => "AB MICROFINANCE BANK"
                    ]
...
]
    ]
]

$banks = $eyowo->getBanks();

            [
                        "bankCode" => "090270",
                        "bankName" => "AB MICROFINANCE BANK"
                    ]
...
]
 

```

#### Transfer to phone
```php
//amount should be in kobo
$eyowo->transferToPhone($walletToken, $amount, $phone);
```

#### Transfer to bank
```php
//amount should be in kobo
$eyowo->transferToPhone($walletToken, $amount, $accountName, $accountNumber, $bankCode);
```

#### Wallet Balance
```php
$eyowo->balance($walletToken); returns raw API response
$balance = $eyowo->getBalance(); // returns int|float
```

#### VTU
```php
// provider has to be one of ['mtn', 'glo', 'etisalat', 'airtel'];
$eyowo->vtu($walletToken, $amount, $phone, $provider);
```


## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing
- Clone this repo
- Run `composer install`
- Run `cp .env.sample .env`
- Set your API keys in `.env`
- Run `composer test`

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CONDUCT](.github/CONDUCT.md) for details. Check our [todo list](TODO.md) for features already intended.

## Security

If you discover any security related issues, please email yabacon.valley@gmail.com instead of using the issue tracker.

## Credits

- [Zacchaeus Bolaji](https://github.com/djunehor)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
