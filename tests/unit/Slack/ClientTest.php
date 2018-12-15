<?php
declare(strict_types=1);

namespace WebProject\Tests\Unit\Log4php\Slack;

use WebProject\Log4php\Appender\Settings\Config;
use WebProject\Log4php\Slack\Client;

/**
 * Class ClientTest.
 */
class ClientTest extends \Codeception\Test\Unit
{
    public function testFactoryDoseNotThrowError(): void
    {
        // Arrange
        // Act
        $client = Client::factory(new Config());
        // Assert
        $this->assertNotEmpty($client);
    }

    public function testSendMessage(): void
    {
        // Arrange
        $apiClient = \Mockery::mock(\Maknz\Slack\Client::class);
        $apiClient->shouldReceive('sendMessage')->once()->andReturnTrue();
        $apiClient->makePartial();

        $client = new Client(new Config(), $apiClient);
        $logEvent = new \LoggerLoggingEvent(
            'fqcn',
            'TestLogger',
            \LoggerLevel::getLevelError(),
            'testMessage'
        );
        $message = $client->generateMessage($logEvent);
        // Act
        $actual = $client->sendMessage($message);
        // Assert
        $this->assertTrue($actual);
    }

    public function testSendMessageNoMarkdown(): void
    {
        // Arrange
        $apiClient = \Mockery::mock(\Maknz\Slack\Client::class);
        $apiClient->shouldReceive('sendMessage')->once()->andReturnTrue();
        $apiClient->makePartial();

        $client = new Client(
            new Config([Config::KEY_ALLOW_MARKDOWN => false]),
            $apiClient
        );
        $logEvent = new \LoggerLoggingEvent(
            'fqcn',
            'TestLogger',
            \LoggerLevel::getLevelError(),
            'testMessage'
        );
        $message = $client->generateMessage($logEvent);
        // Act
        $actual = $client->sendMessage($message);
        // Assert
        $this->assertFalse($message->getAllowMarkdown());
        $this->assertSame('#general', $message->getChannel());
        $this->assertSame(':goberserk:', $message->getIcon());
        $this->assertSame('icon_emoji', $message->getIconType());
        $this->assertSame(
            'ERROR ( Logger: WebProject\Log4php\Slack\Client ): testMessage',
            $message->getText()
        );
        $this->assertSame('Log4php', $message->getUsername());
        $this->assertTrue($actual);
    }

    public function testSendMessageWithDifferentMaxLength(): void
    {
        // Arrange
        $apiClient = \Mockery::mock(\Maknz\Slack\Client::class);
        $apiClient->shouldReceive('sendMessage')->once()->andReturnTrue();
        $apiClient->makePartial();

        $client = new Client(
            new Config([Config::KEY_MAX_MESSAGE_LENGTH => 10]),
            $apiClient
        );
        $logEvent = new \LoggerLoggingEvent(
            'fqcn',
            'TestLogger',
            \LoggerLevel::getLevelError(),
            'message is longer than 10 i guess'
        );
        $message = $client->generateMessage($logEvent);
        // Act
        $actual = $client->sendMessage($message);
        // Assert
        $this->assertTrue($message->getAllowMarkdown());
        $this->assertSame('#general', $message->getChannel());
        $this->assertSame(':goberserk:', $message->getIcon());
        $this->assertSame('icon_emoji', $message->getIconType());
        $this->assertSame(
            '*ERROR* _( Logger: *WebProject\Log4php\Slack\Client* )_: message is',
            $message->getText()
        );
        $this->assertSame('Log4php', $message->getUsername());
        $this->assertTrue($actual);
    }
    public function testSendMessageNoIconByLevel(): void
    {
        // Arrange
        $apiClient = \Mockery::mock(\Maknz\Slack\Client::class);
        $apiClient->shouldReceive('sendMessage')->once()->andReturnTrue();
        $apiClient->makePartial();

        $client = new Client(
            new Config([
                Config::KEY_ALLOW_MARKDOWN        => false,
                Config::KEY_SET_ICON_BY_LOG_LEVEL => false,
            ]),
            $apiClient
        );
        $logEvent = new \LoggerLoggingEvent(
            'fqcn',
            'TestLogger',
            \LoggerLevel::getLevelError(),
            'testMessage'
        );
        $message = $client->generateMessage($logEvent);
        // Act
        $actual = $client->sendMessage($message);
        // Assert
        $this->assertFalse($message->getAllowMarkdown());
        $this->assertSame('#general', $message->getChannel());
        $this->assertNull($message->getIcon());
        $this->assertNull($message->getIconType());
        $this->assertSame(
            'ERROR ( Logger: WebProject\Log4php\Slack\Client ): testMessage',
            $message->getText()
        );
        $this->assertSame('Log4php', $message->getUsername());
        $this->assertNotEmpty($message->getAttachments());
        $this->assertTrue($actual);
    }

    public function testSendMessageNotAttached(): void
    {
        // Arrange
        $apiClient = \Mockery::mock(\Maknz\Slack\Client::class);
        $apiClient->shouldReceive('sendMessage')->once()->andReturnTrue();
        $apiClient->makePartial();

        $client = new Client(
            new Config([
                Config::KEY_ALLOW_MARKDOWN        => false,
                Config::KEY_AS_ATTACHMENT         => false,
                Config::KEY_SET_ICON_BY_LOG_LEVEL => false,
            ]),
            $apiClient
        );
        $logEvent = new \LoggerLoggingEvent(
            'fqcn',
            'TestLogger',
            \LoggerLevel::getLevelError(),
            'testMessage'
        );
        $message = $client->generateMessage($logEvent);
        // Act
        $actual = $client->sendMessage($message);
        // Assert
        $this->assertFalse($message->getAllowMarkdown());
        $this->assertSame('#general', $message->getChannel());
        $this->assertNull($message->getIcon());
        $this->assertNull($message->getIconType());
        $this->assertSame(
            'ERROR ( Logger: WebProject\Log4php\Slack\Client ): testMessage',
            $message->getText()
        );
        $this->assertSame('Log4php', $message->getUsername());
        $this->assertEmpty($message->getAttachments());
        $this->assertTrue($actual);
    }

    public function testGenerateMessage(): void
    {
        // Arrange
        $client = new Client(new Config());
        $logEvent = new \LoggerLoggingEvent(
            'fqcn',
            'TestLogger',
            \LoggerLevel::getLevelError(),
            'testMessage'
        );
        // Act
        $message = $client->generateMessage($logEvent);

        // Assert

        $this->assertTrue($message->getAllowMarkdown());
        $this->assertSame('#general', $message->getChannel());
        $this->assertSame(':goberserk:', $message->getIcon());
        $this->assertSame('icon_emoji', $message->getIconType());
        $this->assertSame(
            '*ERROR* _( Logger: *WebProject\Log4php\Slack\Client* )_: testMessage',
            $message->getText()
        );
        $this->assertSame('Log4php', $message->getUsername());
    }

    protected function _after()
    {
        \Mockery::close();
        parent::_after();
    }
}
