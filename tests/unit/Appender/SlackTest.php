<?php
declare(strict_types=1);

namespace WebProject\Tests\Unit\Log4php\Appender;

use WebProject\Log4php\Appender\Slack;

/**
 * Class SlackTest
 */
class SlackTest extends \Codeception\Test\Unit
{
    public function testGetDefaultLayout()
    {
        // Arrange
        $appender = new Slack('test');

        // Act
        $layout = $appender->getDefaultLayout();
        // Assert
        $this->assertInstanceOf(\WebProject\Log4php\Layouts\Slack::class, $layout);
    }
}
