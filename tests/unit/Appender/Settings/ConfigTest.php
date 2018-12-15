<?php
declare(strict_types=1);

namespace WebProject\Tests\Unit\Log4php\Appender\Settings;

use WebProject\Log4php\Appender\Settings\Config;

/**
 * Class ConfigTest
 */
class ConfigTest extends \Codeception\Test\Unit
{
    /**
     * @param $expected
     * @param \LoggerLevel $logLevel
     * @dataProvider eventAndLevel
     */
    public function testGetIconByLogEvent(
        \LoggerLevel $logLevel, string $expected
    ): void {
        // Arrange
        $config = new Config();
        $logEvent = new \LoggerLoggingEvent(
            'fqcn',
            'TestLogger',
            $logLevel,
            'testMessage'
        );

        // Act
        $actual = $config->getIconByLogEvent($logEvent);

        // Assert
        $this->assertSame($expected, $actual);
    }

    public function eventAndLevel(): array
    {
        return [
            [\LoggerLevel::getLevelError(), ':goberserk:'],
            [\LoggerLevel::getLevelFatal(),':rage4:'],
            [\LoggerLevel::getLevelWarn() , ':feelsgood:'],
            [\LoggerLevel::getLevelInfo() , ':squirrel:'],
            [\LoggerLevel::getLevelDebug(), ':squirrel:'],
            [\LoggerLevel::getLevelTrace(), ':squirrel:'],
        ];
    }

    public function testToArray(): void
    {
        // Arrange
        $config = new Config();

        // Act
        $actual = $config->toArray();

        // Assert
        $this->assertNotEmpty($actual);
        $this->assertCount(13, $actual);
    }

    public function testJsonSerialize(): void
    {
        // Arrange
        $config = new Config();

        // Act
        $actual = $config->jsonSerialize();

        // Assert
        $this->assertNotEmpty($actual);
        $this->assertNotFalse(\json_encode($actual));
    }

    public function testGetterWithValidConfigKey(): void
    {
        // Arrange
        $config = new Config();

        // Act
        $actual = $config->get(Config::KEY_CHANNEL);

        // Assert
        $this->assertSame('#general', $actual);
    }

    public function testGetterWithInvalidConfigKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid setting key: (foo)');
        // Arrange
        $config = new Config();

        // Act
        $config->get('foo');

        // Assert
        // error
    }

    public function testSetterWithValidConfigKey(): void
    {
        // Arrange
        $config = new Config();

        // Act
        $config->set(Config::KEY_CHANNEL, '#test');

        // Assert
        $this->assertSame('#test', $config->get(Config::KEY_CHANNEL));
    }

    public function testSetterWithInvalidConfigKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid setting key: (foo)');
        // Arrange
        $config = new Config();

        // Act
        $config->set('foo', 'bar');

        // Assert
        // error
    }
}
