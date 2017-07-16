<?php
use Maknz\Slack\Attachment;

/**
 * Class LoggerAppenderSlackTest.
 */
class LoggerAppenderSlackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var LoggerAppenderSlack
     */
    protected $_subject;

    protected function setUp()
    {
        parent::setUp();

        $this->_subject = new LoggerAppenderSlack();
    }

    public function testSetupAppenderAndCheckSettings()
    {
        // Arrange
        $appenderSlack = clone $this->_subject;
        $validEndpoint = LoggerAppenderSlack::ENDPOINT_VALIDATION_STRING;
        $appenderSlack
            ->setUsername('unitTester')
            ->setChannel('#phpunit')
            ->setIcon(':testing:')
            ->setAllowMarkdown(true)
            ->setAsAttachment(true)
            ->setEndpoint($validEndpoint);
        // Act
        $username = self::getObjectAttribute($appenderSlack, '_username');
        $channel = self::getObjectAttribute($appenderSlack, '_channel');
        $icon = self::getObjectAttribute($appenderSlack, '_icon');
        $endpoint = self::getObjectAttribute($appenderSlack, '_endpoint');
        $allowMarkdown = self::getObjectAttribute($appenderSlack, '_allowMarkdown');
        $logAsAttachment = self::getObjectAttribute($appenderSlack, 'asAttachment');
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
    public function testSetupAppenderInvalidEndpointUrlWasPassed()
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
    public function testSetupAppenderInvalidUsernameValue($username)
    {
        // Arrange
        $appenderSlack = clone $this->_subject;
        // Act
        $appenderSlack
            ->setUsername($username);
        // Assert
        // error
    }

    public function _usernameData()
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

    public function testGetSlackClientFromAppender()
    {
        // Arrange
        $appenderSlack = clone $this->_subject;

        $validEndpoint = LoggerAppenderSlack::ENDPOINT_VALIDATION_STRING;
        $appenderSlack
            ->setUsername('unitTester')
            ->setChannel('#phpunit')
            ->setIcon(':testing:')
            ->setEndpoint($validEndpoint);
        // Act
        $username = self::getObjectAttribute($appenderSlack, '_username');
        $channel = self::getObjectAttribute($appenderSlack, '_channel');
        $icon = self::getObjectAttribute($appenderSlack, '_icon');
        $endpoint = self::getObjectAttribute($appenderSlack, '_endpoint');
        // Assert
        $this->assertSame('unitTester', $username, 'username value not correct');
        $this->assertSame('#phpunit', $channel, 'channel value not correct');
        $this->assertSame(':testing:', $icon, 'icon value not correct');
        $this->assertSame($validEndpoint, $endpoint, 'endpoint value not correct');
    }

    public function testGetSlackClient()
    {
        // Arrange
        $method = new ReflectionMethod(get_class($this->_subject), '_getSlackClient');
        $method->setAccessible(true);
        // Act
        $result = $method->invoke($this->_subject);
        // Assert
        $this->assertInstanceOf(\Maknz\Slack\Client::class, $result);
    }

    public function testAppendFunction()
    {
        // Arrange
        $slackClientMock = Mockery::mock(\Maknz\Slack\Client::class);
        $slackClientMock->shouldReceive('sendMessage')->andReturn(true);

        $appenderSlack = clone $this->_subject;
        $appenderSlack->setSlackClient($slackClientMock);

        $method = new ReflectionMethod(get_class($appenderSlack), 'append');
        $method->setAccessible(true);
        // Act
        $eventError = new LoggerLoggingEvent('LoggerAppenderEchoTest', new Logger('TEST'), LoggerLevel::getLevelError(), 'testmessage');
        $method->invoke($appenderSlack, $eventError);
        // Assert
    }

    public function testAppendFunctionNoMarkup()
    {
        // Arrange
        $slackClientMock = Mockery::mock(\Maknz\Slack\Client::class);
        $slackClientMock->shouldReceive('sendMessage')->andReturn(true);

        $appenderSlack = clone $this->_subject;
        $appenderSlack->setAllowMarkdown(false);
        $appenderSlack->setSlackClient($slackClientMock);

        $method = new ReflectionMethod(get_class($appenderSlack), 'append');
        $method->setAccessible(true);
        // Act
        $eventError = new LoggerLoggingEvent('LoggerAppenderEchoTest', new Logger('TEST'), LoggerLevel::getLevelError(), 'testmessage');
        $method->invoke($appenderSlack, $eventError);
        // Assert
    }

    /**
     * @dataProvider _levelAndColorProvider
     * @param mixed $loggerLevel
     * @param mixed $expectedColor
     */
    public function testSetColorByLevelName($loggerLevel, $expectedColor)
    {
        // Arrange
        $slackClientMock = Mockery::mock(\Maknz\Slack\Client::class);
        $slackClientMock->shouldReceive('sendMessage')->andReturn(true);

        $appenderSlack = clone $this->_subject;
        $appenderSlack->setSlackClient($slackClientMock);

        $method = new ReflectionMethod(get_class($appenderSlack), '_setColorByLevelName');
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
    public function _levelAndColorProvider()
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
     *
     * @dataProvider _allowMarkdownSettingData
     */
    public function testAllowMarkdownSetting()
    {
        // Arrange
        $slackClientMock = Mockery::mock(\Maknz\Slack\Client::class);
        $slackClientMock->shouldReceive('sendMessage')->andReturn(true);

        $appenderSlack = clone $this->_subject;
        $appenderSlack->setName('unitTestLogger');
        $appenderSlack->setAllowMarkdown(true);
        $appenderSlack->setSlackClient($slackClientMock);

        // Act
        $message = $appenderSlack->generateMessage();
        $attachments = $message->getAttachments();

        // Assert
        $this->assertTrue(is_array($attachments));
        $this->assertCount(1, $attachments);
        $this->assertArrayHasKey('0', $attachments);
        $this->assertInstanceOf(Attachment::class, $attachments[0]);
        $this->assertTrue(is_array($attachments[0]->getFields()));
        $this->assertCount(2, $attachments[0]->getFields());
        $this->assertSame('Logger', $attachments[0]->getFields()[0]->getTitle());
        $this->assertSame('unitTestLogger', $attachments[0]->getFields()[0]->getValue());
        $this->assertSame('Date', $attachments[0]->getFields()[1]->getTitle());
    }

    /**
     * @param $value
     * @param $expected
     * @dataProvider _allowMarkdownSettingData
     */
    public function testMarkdownSettingValues($value, $expected)
    {
        $subject = clone $this->_subject;
        $subject->setAllowMarkdown($value);

        $method = new ReflectionMethod(get_class($subject), '_isAllowMarkdown');
        $method->setAccessible(true);

        $this->assertSame(
            $method->invoke($subject),
            $expected,
            var_export($value, 1) .' is '. var_export($expected, 1)
        );
    }

    /**
     * @return array
     */
    public function _allowMarkdownSettingData()
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
