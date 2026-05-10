<?php

declare(strict_types=1);

use AichaDigital\LaraContent\Tests\Integration\Mysql\MysqlIntegrationTestCase;
use AichaDigital\LaraContent\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');
uses(MysqlIntegrationTestCase::class)->in('Integration/Mysql');
