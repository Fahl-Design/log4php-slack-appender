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

    public function testGetDefaultLayout()
    {
        // Arrange
        $appender = new Slack('test');
        // Act
        $layout = $appender->getDefaultLayout();
        // Assert
        $this->assertInstanceOf(SlackLayout::class, $layout);
    }

    public function testAppendCanSendMessage()
    {
        // Arrange
        $client = \Mockery::mock(\WebProject\Log4php\Slack\Client::class);
        $client->shouldAllowMockingProtectedMethods();
        $client->shouldReceive('sendMessage')->once()->andReturnTrue();
        $client->shouldReceive('_getConfig')->zeroOrMoreTimes()->andReturn(new Config());
        $client->makePartial();

        $appender = new Slack('test', $client, new Config());

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

    public function testConfiguration()
    {
        // Arrange
        \Logger::configure(
            include __DIR__.'/../../../examples/resources/config.dist.php'
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
}
