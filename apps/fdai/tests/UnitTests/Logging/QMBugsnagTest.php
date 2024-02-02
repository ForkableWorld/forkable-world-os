<?php

namespace Tests\UnitTests\Logging;

use App\Logging\QMLog;
use PHPUnit\Framework\TestCase;

class QMBugsnagTest extends TestCase
{

    public function testPreSendReportConfigCallback()
    {
		QMLog::error("I am an error");

    }
}
