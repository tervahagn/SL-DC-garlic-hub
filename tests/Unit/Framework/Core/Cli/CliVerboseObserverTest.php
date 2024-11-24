<?php

namespace Tests\Unit\Framework\Core\Cli;

use App\Framework\Core\Cli\CliVerboseObserver;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class CliVerboseObserverTest extends TestCase
{
    #[Group('units')]
    public function testNotify()
    {
        $message = "Test message...";
        $expected = $message . PHP_EOL;

        $Observer = new CliVerboseObserver();

        ob_start ();
        $Observer->notify($message);
        $output = ob_get_clean();

        $this->assertEquals($expected, $output);
    }
}
