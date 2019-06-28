<?php

namespace xlerr\sentry\ewechat\test;

use PHPUnit\Framework\TestCase;
use xlerr\sentry\ewechat\Target;

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

final class Test extends TestCase
{
    private $target;

    public function setUp(): void
    {
        $this->target = new Target([
            'config' => [],
        ]);
    }

    public function testInit()
    {
        $this->assertInstanceOf(Target::class, $this->target, 'alksjdf');
    }
}
