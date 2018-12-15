<?php
declare(strict_types=1);

namespace WebProject\Tests\Unit\Layouts;

use WebProject\Log4php\Layouts\Slack;
use WebProject\Log4php\Settings\Config;

/**
 * Class SlackTest
 */
class SlackTest extends \Codeception\Test\Unit
{
    public function testFormatWithLoggerNameActive()
    {
        // Arrange
        $layout = new Slack(new Config([]));
        $testString = 'testString';
        $expected = 'Logger (TestLogger) testString'.\PHP_EOL;
        $logEvent = new \LoggerLoggingEvent(
            'fqcn',
            'TestLogger',
            \LoggerLevel::getLevelError(),
            $testString
        );
        $actual = $layout->format($logEvent);
        // Assert
        $this->assertSame($expected, $actual);
    }

    public function testFormatWithLoggerNameInActive()
    {
        // Arrange
        $layout = new Slack(
            new Config([Config::KEY_ADD_LOGGER_TO_MESSAGE => false])
        );
        $testString = 'testString';
        $expected = 'testString'.\PHP_EOL;
        $logEvent = new \LoggerLoggingEvent(
            'fqcn',
            'TestLogger',
            \LoggerLevel::getLevelError(),
            $testString
        );
        $actual = $layout->format($logEvent);
        // Assert
        $this->assertSame($expected, $actual);
    }
}
