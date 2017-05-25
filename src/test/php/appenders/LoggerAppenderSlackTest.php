<?php

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
            ->setEndpoint($validEndpoint)
        ;
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

    public function testSetupAppenderInvalidEndpointUrlWasPassed()
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid endpoint');
        // Arrange
        $appenderSlack = clone $this->_subject;
        $validEndpoint = 'invalid';
        // Act
        $appenderSlack
            ->setEndpoint($validEndpoint)
        ;
        // Assert
        // error
    }

    /**
     * @dataProvider             _usernameData()
     *
     * @param $username
     */
    public function testSetupAppenderInvalidUsernameValue($username)
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('username invalid');
        // Arrange
        $appenderSlack = clone $this->_subject;
        // Act
        $appenderSlack
            ->setUsername($username)
        ;
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
            ->setEndpoint($validEndpoint)
        ;
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
}
