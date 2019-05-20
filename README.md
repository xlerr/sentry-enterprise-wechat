sentry
================

enterprise wechat
----------------

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
                // 'config' => 'wechatSentryConfig', // a string, read from [[kvmanager\models\KeyValue]] by this string.
                // 'config' => function () {
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