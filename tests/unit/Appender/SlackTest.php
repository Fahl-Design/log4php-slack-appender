<?php
declare(strict_types=1);

namespace WebProject\Tests\Unit\Log4php\Appender;

use WebProject\Log4php\Appender\Settings\Config;
use WebProject\Log4php\Appender\Slack;
use WebProject\Log4php\Layouts\Slack as SlackLayout;

/**
 * Class SlackTest
 */
class SlackTest extends \Codeception\Test\Unit
{
    /**
     * @var \WebProject\Tests\UnitTester
     */
    protected $tester;

    public function testGetDefaultLayout(): void
    {
        // Arrange
        $appender = new Slack('test');
        // Act
        $layout = $appender->getDefaultLayout();
        // Assert
        $this->assertInstanceOf(SlackLayout::class, $layout);
    }

    public function testAppendCanSendMessage(): void
    {
        // Arrange
        $client = \Mockery::mock(\WebProject\Log4php\Slack\Client::class);
        $client->shouldAllowMockingProtectedMethods();
        $client->shouldReceive('sendMessage')->once()->andReturnTrue();
        $client->shouldReceive('_getConfig')->zeroOrMoreTimes()->andReturn(new Config());
        $client->makePartial();

        $appender = $this->_getMockedAppender($client);

        $logEvent = new \LoggerLoggingEvent(
            'fqcn',
            'TestLogger',
            \LoggerLevel::getLevelError(),
            'testMessage'
        );

        // Act
        $reflectionMethod = $this->tester->getReflectionMethod(
            Slack::class, 'append'
        );
        $result = $reflectionMethod->invokeArgs($appender, [$logEvent]);

        // Assert
        $this->assertTrue($result);
    }

    public function testAppendErrorFromApiClient(): void
    {
        // Arrange
        $client = \Mockery::mock(\WebProject\Log4php\Slack\Client::class);
        $client->shouldAllowMockingProtectedMethods();
        $client
            ->shouldReceive('sendMessage')
            ->once()
            ->andThrow(\GuzzleHttp\Exception\TransferException::class);
        $client
            ->shouldReceive('_getConfig')
            ->zeroOrMoreTimes()
            ->andReturn(new Config());
        $client->makePartial();

        $appender = $this->_getMockedAppender($client);

        $logEvent = new \LoggerLoggingEvent(
            'fqcn',
            'TestLogger',
            \LoggerLevel::getLevelError(),
            'testMessage'
        );

        // Act
        $reflectionMethod = $this->tester->getReflectionMethod(
            Slack::class, 'append'
        );
        $result = $reflectionMethod->invokeArgs($appender, [$logEvent]);

        // Assert
        $this->assertFalse($result);
    }

    public function testAppendErrorNotFromApiClient(): void
    {
        // Arrange
        $this->expectException(\RuntimeException::class);

        $client = \Mockery::mock(\WebProject\Log4php\Slack\Client::class);
        $client->shouldAllowMockingProtectedMethods();
        $client
            ->shouldReceive('sendMessage')
            ->once()
            ->andThrow(\RuntimeException::class);
        $client
            ->shouldReceive('_getConfig')
            ->zeroOrMoreTimes()
            ->andReturn(new Config());
        $client->makePartial();

        $appender = $this->_getMockedAppender($client);

        $logEvent = new \LoggerLoggingEvent(
            'fqcn',
            'TestLogger',
            \LoggerLevel::getLevelError(),
            'testMessage'
        );

        // Act
        $reflectionMethod = $this->tester->getReflectionMethod(
            Slack::class, 'append'
        );
        $result = $reflectionMethod->invokeArgs($appender, [$logEvent]);

        // Assert
        $this->assertFalse($result);
    }

    public function testConfiguration(): void
    {
        // Arrange
        \Logger::configure(
            require __DIR__.'/../../../examples/resources/config.dist.php'
        );
        // Act
        /** @var Slack $loggerAppender */
        $loggerAppender = \Logger::getRootLogger()
            ->getAppender('slack_appender');
        $layout = $loggerAppender->getLayout();

        $method = $this->tester->getReflectionMethod(
            Slack::class, '_getSlackClient'
        );
        $slackClient = $method->invoke($loggerAppender);
        /** @var \WebProject\Log4php\Slack\Client $slackClient */
        // Assert
        $this->assertInstanceOf(
            \WebProject\Log4php\Slack\Client::class,
            $slackClient
        );
        $this->assertInstanceOf(
            \WebProject\Log4php\Layouts\Slack::class,
            $layout
        );
        \Logger::clear();
    }

    /**
     * @dataProvider configValidationData
     *
     * @param string $setter
     * @param string $value
     * @param string $errorMessage
     */
    public function testConfigSetterValidation(string $setter, string $value, string $errorMessage): void
    {
        // Arrange
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($errorMessage);

        $appender = new Slack('test');

        // Act
        $appender->{$setter}($value);
        // Assert
        // error
    }

    public function configValidationData(): array
    {
        return [
            ['setIcon','','icon invalid'],
            ['setEndpoint','foobar','invalid endpoint'],
            ['setUsername','','username invalid'],
            ['setChannel','','channel invalid'],
        ];
    }

    /**
     * @param $client
     *
     * @return \Mockery\MockInterface|Slack
     */
    protected function _getMockedAppender($client): Slack
    {
        $appender = \Mockery::mock(Slack::class);
        $appender->shouldAllowMockingProtectedMethods();
        $appender->shouldReceive('_getSlackClient')->once()->andReturn($client);
        $appender->shouldReceive('getConfig')->once()->andReturn(new Config());
        $appender->makePartial();

        return $appender;
    }
}
