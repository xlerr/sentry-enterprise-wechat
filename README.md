sentry
================

enterprise wechat 
----------------

![Packagist Version](https://img.shields.io/packagist/v/xlerr/sentry-ewechat.svg)
![PHP from Packagist](https://img.shields.io/packagist/php-v/xlerr/sentry-ewechat.svg)
[![Build Status](https://www.travis-ci.org/xlerr/sentry-ewechat.svg?branch=master)](https://www.travis-ci.org/xlerr/sentry-ewechat)
![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/xlerr/sentry-ewechat.svg)

### install

```shell
composer require xlerr/sentry-ewechat
```

### config

```php
[
    "log" => [
        'targets' => [
            [
                'class' => \xlerr\sentry\ewechat\Target::class,
                'levels' => ['error', 'warning'],
                // 'config' => null, // default value, is disabled.
                // 'config' => function () {
                //     return \kvmanager\models\KeyValue::getValueAsArray('sentry_ewechat_config');
                //     // or
                //     return [];
                // },
                'config' => [
                    'host' => 'http://api.myhost.com',
                    'chatId' => 'chatId',
                    // 'enabled' => true,
                    // 'categories' => [],
                    // 'except' => [],
                    // 'logVars' => [],
                    // 'msgMaxLength' => 0,
                    // 'exceptMatchMsg' => [],
                    // 'system' => "[DEPOSIT]\n",
                ],
            ],
        ],
    ],
]
```
