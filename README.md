# Laravel Callmebot

[![Packagist](https://img.shields.io/packagist/v/guysolamour/laravel-dkim.svg)](https://packagist.org/packages/guysolamour/laravel-dkim)
[![Packagist](https://poser.pugx.org/guysolamour/laravel-dkim/d/total.svg)](https://packagist.org/packages/guysolamour/laravel-dkim)
[![Packagist](https://img.shields.io/packagist/l/guysolamour/laravel-dkim.svg)](https://packagist.org/packages/guysolamour/laravel-dkim)


## Installation

Install via composer
```bash
composer require guysolamour/laravel-dkim
```


After that, you should publish the config file with:

```bash
php artisan vendor:publish --provider="Guysolamour\Dkim\ServiceProvider"
```

The ServiceProvider extends the MailServiceProvider and overwrites a method that we need for our own behavior.

Next we need to create a private and public key pair for signing and verifying the email.

There are many tools available to generate the necessary keys but here is one which is easy to use:

https://tools.socketlabs.com/dkim/generator

Enter your domain and in the "selector" field enter `default`, or adjust the "selector" in the laravel config file accordingly. Leave the remaining fields as they are.

After you have generated the keys and added the public key to your dns record, here is a tool to validate it:

https://www.mail-tester.com/spf-dkim-check

Finally, store the private key for example in `storage/app/dkim/dkim.private.key` and configure your settings in `.env`:

You need to set the **full absolute path** in the environment variable.

```ini
DKIM_DOMAIN=example.com
```

## Credits

- [Guy-roland ASSALE](https://github.com/guysolamour/laravel-dkim)
- [All contributors](https://github.com/guysolamour/laravel-dkim/graphs/contributors)
