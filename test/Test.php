<?php

namespace xlerr\sentry\ewechat\test;

use PHPUnit\Framework\TestCase;

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

final class Test extends TestCase
{
    public function testInit()
    {
        $this->assertCount(1, [1], 'count error');
    }
}
