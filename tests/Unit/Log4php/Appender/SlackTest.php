<?php
declare(strict_types=1);

namespace WebProject\Tests\Unit\Log4php\Appender;

use InvalidArgumentException;
use Logger;
use LoggerLevel;
use LoggerLoggingEvent;
use Maknz\Slack\Attachment;
use Mockery;
use ReflectionMethod;
use WebProject\Log4php\Appender\Slack;

/**
 * Class SlackTest.
 */
class SlackTest extends \Codeception\Test\Unit
{
    /**
     * @var Slack
     */
    protected $_subject;

    public function _before()
    {
        $slackClientMock = Mockery::mock(\Maknz\Slack\Client::class);
        $slackClientMock->shouldReceive('sendMessage')->zeroOrMoreTimes()->andReturn(true);

        $appender = new Slack();
        $appender->setSlackClient($slackClientMock);
        $appender->setChannel('#test');
        $appender->setEndpoint(Slack::ENDPOINT_VALIDATION_STRING);

        $this->_subject = $appender;
    }

    public function testSetupAppenderAndCheckSettings(): void
    {
        // Arrange
        $appenderSlack = clone $this->_subject;
        $validEndpoint = Slack::ENDPOINT_VALIDATION_STRING;
        $appenderSlack
            ->setUsername('unitTester')
            ->setChannel('#phpunit')
            ->setIcon(':testing:')
            ->setAllowMarkdown(true)
            ->setAsAttachment(true)
            ->setEndpoint($validEndpoint);
        // Act
        $username = $this->getObjectAttribute($appenderSlack, '_username');
        $channel = $this->getObjectAttribute($appenderSlack, '_channel');
        $icon = $this->getObjectAttribute($appenderSlack, '_icon');
        $endpoint = $this->getObjectAttribute($appenderSlack, '_endpoint');
        $allowMarkdown = $this->getObjectAttribute($appenderSlack, '_allowMarkdown');
        $logAsAttachment = $this->getObjectAttribute($appenderSlack, '_asAttachment');
        // Assert
        $this->assertSame('unitTester', $username, 'username value not correct');
        $this->assertSame('#phpunit', $channel, 'channel value not correct');
        $this->assertSame(':testing:', $icon, 'icon value not correct');
        $this->assertSame($validEndpoint, $endpoint, 'endpoint value not correct');
        $this->assertTrue($allowMarkdown, 'allow markdown value not correct');
        $this->assertTrue($logAsAttachment, 'send as attachment value not correct');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage invalid endpoint
     */
    public function testSetupAppenderInvalidEndpointUrlWasPassed(): void
    {
        // Arrange
        $appenderSlack = clone $this->_subject;
        $validEndpoint = 'invalid';
        // Act
        $appenderSlack
            ->setEndpoint($validEndpoint);
        // Assert
        // error
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage username invalid
     * @dataProvider             _usernameData()
     *
     * @param $username
     */
    public function testSetupAppenderInvalidUsernameValue($username): void
    {
        // Arrange
        $appenderSlack = clone $this->_subject;
        // Act
        $appenderSlack
            ->setUsername($username);
        // Assert
        // error
    }

    public function _usernameData(): array
    {
        return [
            [''],
            [null],
            [0],
            [1],
            [['']],
            [[1]],
        ];
    }

    public function testGetSlackClientFromAppender(): void
    {
        // Arrange
        $appenderSlack = clone $this->_subject;

        $validEndpoint = Slack::ENDPOINT_VALIDATION_STRING;
        $appenderSlack
            ->setUsername('unitTester')
            ->setChannel('#phpunit')
            ->setIcon(':testing:')
            ->setEndpoint($validEndpoint);
        // Act
        $username = $this->getObjectAttribute($appenderSlack, '_username');
        $channel = $this->getObjectAttribute($appenderSlack, '_channel');
        $icon = $this->getObjectAttribute($appenderSlack, '_icon');
        $endpoint = $this->getObjectAttribute($appenderSlack, '_endpoint');
        // Assert
        $this->assertSame('unitTester', $username, 'username value not correct');
        $this->assertSame('#phpunit', $channel, 'channel value not correct');
        $this->assertSame(':testing:', $icon, 'icon value not correct');
        $this->assertSame($validEndpoint, $endpoint, 'endpoint value not correct');
    }

    public function testGetSlackClient(): void
    {
        // Arrange
        $this->_subject->setEndpoint(Slack::ENDPOINT_VALIDATION_STRING);
        $method = new ReflectionMethod(\get_class($this->_subject), '_getSlackClient');
        $method->setAccessible(true);
        // Act
        $result = $method->invoke($this->_subject);
        // Assert
        $this->assertInstanceOf(\Maknz\Slack\Client::class, $result);
    }

    public function testAppendFunction(): void
    {
        // Arrange
        $appenderSlack = clone $this->_subject;
        $appenderSlack->setChannel('#test');

        $method = new ReflectionMethod(\get_class($this->_subject), 'append');
        $method->setAccessible(true);
        // Act
        $eventError = new LoggerLoggingEvent('EchoTest', new Logger('TEST'), LoggerLevel::getLevelError(), 'testmessage');
        // Assert
        $result = $method->invoke($this->_subject, $eventError);
        $this->assertTrue($result);
    }

    public function testAppendFunctionNoMarkup(): void
    {
        // Arrange
        $appenderSlack = clone $this->_subject;
        $appenderSlack->setAllowMarkdown(false);
        $appenderSlack->setChannel('#test');

        $method = new ReflectionMethod(\get_class($this->_subject), 'append');
        $method->setAccessible(true);
        // Act
        $eventError = new LoggerLoggingEvent('EchoTest', new Logger('TEST'), LoggerLevel::getLevelError(), 'testmessage');
        // Assert
        $result = $method->invoke($this->_subject, $eventError);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider _levelAndColorProvider
     *
     * @param mixed $loggerLevel
     * @param mixed $expectedColor
     */
    public function testSetColorByLevelName($loggerLevel, $expectedColor): void
    {
        // Arrange
        $slackClientMock = Mockery::mock(\Maknz\Slack\Client::class);
        $slackClientMock->shouldReceive('sendMessage')->andReturn(true);

        $appenderSlack = clone $this->_subject;
        $appenderSlack->setSlackClient($slackClientMock);

        $method = new ReflectionMethod(\get_class($appenderSlack), '_setColor');
        $method->setAccessible(true);

        $attachment = new \Maknz\Slack\Attachment([]);
        // Act
        $result = $method->invoke($appenderSlack, $attachment, $loggerLevel);
        // Assert
        $this->assertSame($expectedColor, $result->getColor());
    }

    /**
     * @return array
     */
    public function _levelAndColorProvider(): array
    {
        return [
            ['DEBUG', '#BDBDBD'],
            ['INFO', '#64B5F6'],
            ['WARN', '#FFA726'],
            ['ERROR', '#EF6C00'],
            ['FATAL', '#D84315'],
            ['', 'good']
        ];
    }

    /**
     * @dataProvider _allowMarkdownSettingData
     */
    public function testAllowMarkdownSetting(): void
    {
        // Arrange
        $slackClientMock = Mockery::mock(\Maknz\Slack\Client::class);
        $slackClientMock->shouldReceive('sendMessage')->andReturn(true);

        $appenderSlack = clone $this->_subject;
        $appenderSlack->setName('unitTestLogger');
        $appenderSlack->setAllowMarkdown(true);
        $appenderSlack->setSlackClient($slackClientMock);
        $appenderSlack->setChannel('test');

        // Act
        $message = $appenderSlack->generateMessage();
        $attachments = $message->getAttachments();

        // Assert
        $this->assertTrue(\is_array($attachments));
        $this->assertCount(1, $attachments);
        $this->assertArrayHasKey('0', $attachments);
        $this->assertInstanceOf(Attachment::class, $attachments[0]);
        $this->assertTrue(\is_array($attachments[0]->getFields()));
        $this->assertCount(2, $attachments[0]->getFields());
        $this->assertSame('Logger', $attachments[0]->getFields()[0]->getTitle());
        $this->assertSame('unitTestLogger', $attachments[0]->getFields()[0]->getValue());
        $this->assertSame('Date', $attachments[0]->getFields()[1]->getTitle());
    }

    /**
     * @param $value
     * @param $expected
     *
     * @dataProvider _allowMarkdownSettingData
     *
     * @throws \ReflectionException
     */
    public function testMarkdownSettingValues($value, $expected): void
    {
        $subject = clone $this->_subject;
        $subject->setAllowMarkdown($value);

        $method = new ReflectionMethod(\get_class($subject), '_isAllowMarkdown');
        $method->setAccessible(true);

        $this->assertSame(
            $method->invoke($subject),
            $expected,
            \var_export($value, true).' is '.\var_export($expected, true)
        );
    }

    /**
     * @return array
     */
    public function _allowMarkdownSettingData(): array
    {
        return [
            ['', false],
            [null, false],
            ['1', true],
            ['true', true],
            ['0', false],
            ['false', false],
        ];
    }
}
