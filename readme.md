# Easy OAuth2 Client for Laravel

Multipurpose OAuth2 client, configurable for a sleuth of providers through the config file.

## Installation

Via Composer

``` bash
$ composer require logicly/easy-oauth-client
```

## Usage

Publish the config
```bash
$ php artisan vendor:publish --provider="Logicly\EasyOAuthClient\EasyOAuthClientServiceProvider"
```
Configure the config using the provided example.

Then use the package as follows:
```php
use Logicly\EasyOAuthClient\Client;

// ...

$oAuthClient = new Client("vendorname");

// Returns array defined in config
$response = $oAuthClient->getToken($code);

//Returns array defined in config
$response = $oAuthClient->getInfo($accesstoken);

//Returns array defined in config
$response = $oAuthClient->refreshToken($refreshtoken);
```

## Config
Example of config provided, edit values to match vendor spec:
```php
<?php

return [
    'vendor1' => [
        'client_id' => '1234',
        'client_secret' => '12345',
        'redirect_uri' => 'https://www.example.com/oauth2/vendor1',
        'token' => [
            'url' => 'https://login.vendor.example.com/oauth2/token',
            'method' => 'POST',
            'grant_type' => 'authorization_code',
            'fields' => [
                'access_token' => 'access_token',
                'expires_in' => 'expires_in',
                'refresh_token' => 'refresh_token',
            ],
            'auth' => 'body',
        ],
        'refresh' => [
            'url' => 'https://login.vendor.example.com/oauth2/token',
            'method' => 'POST',
            'grant_type' => 'authorization_code',
            'fields' => "*",
            'auth' => 'body',
        ],
        'info' => [
            'url' => 'https://login.vendor.example.com/oauth2/metadata',
            'method' => 'GET',
            'fields' => [
                'metadata1',
                'metadata2',
            ],
        ],
    ],
    'vendor2' => ['...'],
];

```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

[link-contributors]: ../../contributors
