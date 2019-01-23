sentry
================

enterprise wechat
----------------

### install

```shell
composer require xlerr\sentry-enterprise-wechat
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
                'config' => [
                    'host' => 'http://api.myhost.com',
                    'chatId' => 'chatId',
                ],
            ],
        ],
    ],
]
```

array config
```json
{
    "host": "http://api.myhost.com",
    "chatId": "chatId",
    "enabled": true, // @see yii\log\Target::$enabled
    "categories": [],  // @see yii\log\Target::$categories
    "except": [], // @see yii\log\Target::$except
    "logVars": [], // @see yii\log\Target::$logVars, defaults to []
    "msgMaxLength": 500, // max length of message, defaults to 500
}
```