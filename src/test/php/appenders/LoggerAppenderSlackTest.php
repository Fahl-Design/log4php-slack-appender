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

    public function testInit()
    {
        // Arrange

        // Act

        // Assert
        $this->assertTrue(true);
    }
}
