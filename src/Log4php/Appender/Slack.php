<?php

declare(strict_types=1);

namespace WebProject\Log4php\Appender;

use LoggerAppender;
use LoggerLoggingEvent;
use Maknz\Slack\Attachment;
use Maknz\Slack\Client;

/**
 * Slack appends log events to a Slack channel.
 *
 * @since      2.4.0
 *
 * @author     Benjamin Fahl <ben@webproject.xyz>
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, v2.0
 *
 * @link http://logging.apache.org/log4php
 */
class Slack extends LoggerAppender
{
    public const ENDPOINT_VALIDATION_STRING = 'https://hooks.slack.com';
    public const COLOR_DEBUG = '#BDBDBD';
    public const COLOR_INFO = '#64B5F6';
    public const COLOR_WARN = '#FFA726';
    public const COLOR_ERROR = '#EF6C00';
    public const COLOR_FATAL = '#D84315';
    public const COLOR_DEFAULT = 'good';

    // todo: config array and const.
    // todo: cleanup

    /**
     * @var \Maknz\Slack\Client
     */
    protected $_slackClient;

    /**
     * @var string
     */
    protected $_username = 'log4php';

    /**
     * Endpoint (slack hook url 'https://hooks.slack.com/...').
     *
     * @var string
     */
    protected $_endpoint;

    /**
     * @var string
     */
    protected $_channel;

    /**
     * @var string
     */
    protected $_icon = ':ghost:';

    /**
     * @var string
     */
    protected $_text;

    /**
     * @var bool
     */
    protected $_allowMarkdown = \true;

    /**
     * @var bool
     */
    protected $_asAttachment = \true;

    /**
     * Parsed level name from event.
     *
     * @var string
     */
    protected $_levelName;

    /**
     * @var bool
     */
    protected $_linkNames = \true;

    /**
     * @var bool
     */
    protected $_unfurlLinks = \false;

    /**
     * @var bool
     */
    protected $_unfurlMedia = \true;

    /**
     * @var bool
     */
    protected $_addEmoji = false;

    /**
     * Should add emoji to attachment title.
     *
     * @return bool
     */
    protected function _canAddEmoji(): bool
    {
        return $this->_addEmoji;
    }

    /**
     * Get LinkNames.
     *
     * @return bool
     */
    protected function _isLinkNames(): bool
    {
        return $this->_linkNames;
    }

    /**
     * Set LinkNames.
     *
     * @param bool $linkNames
     *
     * @return Slack
     */
    public function setLinkNames($linkNames): self
    {
        $this->_linkNames = (bool) (int) $linkNames;

        return $this;
    }

    /**
     * Set AddEmoji.
     *
     * @param bool $addEmoji
     *
     * @return Slack
     */
    public function setAddEmoji($addEmoji): self
    {
        $this->_addEmoji = (bool) (int) $addEmoji;

        return $this;
    }

    /**
     * Get UnfurlLinks.
     *
     * @return bool
     */
    protected function _isUnfurlLinks(): bool
    {
        return $this->_unfurlLinks;
    }

    /**
     * Set UnfurlLinks.
     *
     * @param bool $unfurlLinks
     *
     * @return Slack
     */
    public function setUnfurlLinks($unfurlLinks): self
    {
        $this->_unfurlLinks = (bool) (int) $unfurlLinks;

        return $this;
    }

    /**
     * Get UnfurlMedia.
     *
     * @return bool
     */
    protected function _isUnfurlMedia(): bool
    {
        return $this->_unfurlMedia;
    }

    /**
     * Set UnfurlMedia.
     *
     * @param bool $unfurlMedia
     *
     * @return Slack
     */
    public function setUnfurlMedia($unfurlMedia): self
    {
        $this->_unfurlMedia = (bool) (int) $unfurlMedia;

        return $this;
    }

    /**
     * Get Text.
     *
     * @return string
     */
    protected function _getText(): string
    {
        return $this->_text ?? '';
    }

    /**
     * Overwrite layout with LoggerLayoutSlack.
     *
     * @return \WebProject\Log4php\Layouts\Slack
     */
    public function getDefaultLayout()
    {
        return new \WebProject\Log4php\Layouts\Slack();
    }

    /**
     * Forwards the logging event to the destination.
     *
     * Derived appenders should implement this method to perform actual logging.
     *
     * @param LoggerLoggingEvent $event
     *
     * @return bool
     */
    protected function append(LoggerLoggingEvent $event)
    {
        try {
            // format text with layout
            $this->_formatEventToText($event);
            // get slack client
            $this->_getSlackClient();
            // generate message
            $message = $this->generateMessage();
            // send message
            $message->send();

            return true;
        } catch (\Throwable $e) {
            dump($e);

            return false;
        }
    }

    /**
     * Wrapper for layout format.
     *
     * @param LoggerLoggingEvent $event
     *
     * @return $this
     */
    protected function _formatEventToText(LoggerLoggingEvent $event): self
    {
        $this->_text = \trim($this->layout->format($event));
        $this->_setIconByLevel($event);
        $this->_setLevelName($event->getLevel()->toString());

        return $this;
    }

    /**
     * Get SlackClient.
     *
     * @return \Maknz\Slack\Client
     */
    protected function _getSlackClient(): Client
    {
        if (null === $this->_slackClient) {
            $this->_initSlackClient();
        }

        return $this->_slackClient;
    }

    /**
     * Set SlackClient.
     *
     * @param Client $client
     *
     * @return Slack
     */
    public function setSlackClient(Client $client): self
    {
        $this->_slackClient = $client;

        return $this;
    }

    /**
     * Init php slack client.
     *
     * @return $this
     */
    protected function _initSlackClient(): self
    {
        $settings = [
            'link_names'   => $this->_isLinkNames(),
            'unfurl_media' => $this->_isUnfurlMedia(),
            'unfurl_link'  => $this->_isUnfurlMedia(),
        ];
        $slackClient = new Client($this->_getEndpoint(), $settings);

        $this->_slackClient = $slackClient;

        return $this;
    }

    /**
     * Get Endpoint.
     *
     * @return string
     */
    protected function _getEndpoint(): string
    {
        return $this->_endpoint;
    }

    /**
     * Set Endpoint.
     *
     * @param string $endpoint
     *
     * @throws \InvalidArgumentException
     *
     * @return Slack
     */
    public function setEndpoint($endpoint): self
    {
        if (true === \is_string($endpoint)
            && 0 === \strpos($endpoint, self::ENDPOINT_VALIDATION_STRING)
        ) {
            $this->_endpoint = $endpoint;

            return $this;
        }

        throw new \InvalidArgumentException('invalid endpoint');
    }

    /**
     * Get Username.
     *
     * @return string
     */
    protected function _getUsername(): string
    {
        return $this->_username;
    }

    /**
     * Set Username.
     *
     * @param string $username
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setUsername($username): self
    {
        if (!empty($username) && \is_string($username)) {
            $this->_username = (string) $username;

            return $this;
        }

        throw new \InvalidArgumentException('username invalid');
    }

    /**
     * Get Icon.
     *
     * @return string
     */
    protected function _getIcon(): string
    {
        return $this->_icon;
    }

    /**
     * Set Icon.
     *
     * @param string $icon
     *
     * @return Slack
     */
    public function setIcon($icon): self
    {
        $this->_icon = $icon;

        return $this;
    }

    /**
     * Get Channel.
     *
     * @return string
     */
    protected function _getChannel(): string
    {
        return $this->_channel;
    }

    /**
     * Set Channel.
     *
     * @param string $channel
     *
     * @return Slack
     */
    public function setChannel($channel): self
    {
        $this->_channel = $channel;

        return $this;
    }

    /**
     * Get Flag for allowed markdown.
     *
     * @return bool
     */
    protected function _isAllowMarkdown(): bool
    {
        return $this->_allowMarkdown;
    }

    /**
     * Set AllowMarkdown.
     *
     * @param bool|string $allowMarkdown
     *
     * @return Slack
     */
    public function setAllowMarkdown($allowMarkdown): self
    {
        if (\is_string($allowMarkdown) && 'false' === $allowMarkdown) {
            $allowMarkdown = false;
        }
        $this->_allowMarkdown = (bool) $allowMarkdown;

        return $this;
    }

    /**
     * Get SendLogAsAttachment.
     *
     * @return bool
     */
    protected function _sendLogAsAttachment(): bool
    {
        return $this->_asAttachment;
    }

    /**
     * Set SendLogAsAttachment.
     *
     * @param bool|string $sendLogAsAttachment
     *
     * @return Slack
     */
    public function setAsAttachment($sendLogAsAttachment): self
    {
        if ('false' === $sendLogAsAttachment) {
            $sendLogAsAttachment = false;
        }
        $this->_asAttachment = (bool) $sendLogAsAttachment;

        return $this;
    }

    /**
     * Generate attachment.
     *
     * @return Attachment
     */
    protected function _generateAttachment(): Attachment
    {
        $attachment = new Attachment([]);
        $attachment->setAuthorName('Full '.$this->getLevelName().' Message');
        $attachment->setAuthorIcon(':ghost:');

        if ($this->_isAllowMarkdown()) {
            $attachment->setMarkdownFields(['text', 'author_name']);
        }
        // add text to attachment
        $attachment->setText($this->_getText());
        // inject color to attachment
        $attachment = $this->_setColorByLevelName(
            $attachment,
            $this->getLevelName()
        );
        // inject field of logger name
        $attachment = $this->_addFieldLoggerName($attachment);
        // inject field of date
        return $this->_addFieldDate($attachment);
    }

    /**
     * Get LevelName.
     *
     * @return string
     */
    public function getLevelName(): string
    {
        return $this->_levelName ?? '';
    }

    /**
     * Set LevelName.
     *
     * @param string $levelName
     *
     * @return Slack
     */
    protected function _setLevelName($levelName): self
    {
        $this->_levelName = $levelName;

        return $this;
    }

    /**
     * Get Title with markdown.
     *
     * @param string $logMessage
     *
     * @return string
     */
    protected function _getMarkdownTitleText($logMessage): string
    {
        return '*'.$this->getLevelName().'* '.
     '_( Logger: *'.$this->getName().'* )_ : _'.$logMessage.'_';
    }

    /**
     * Get color by level name.
     *
     * @param Attachment $attachment
     * @param string     $levelName
     *
     * @return Attachment
     */
    protected function _setColorByLevelName(Attachment $attachment, $levelName): Attachment
    {
        switch (true) {
            case false !== \strpos($levelName, 'TRACE'):
            case false !== \strpos($levelName, 'DEBUG'):
                $attachment->setColor(self::COLOR_DEBUG);

                break;
            case false !== \strpos($levelName, 'INFO'):
                $attachment->setColor(self::COLOR_INFO);

                break;
            case false !== \strpos($levelName, 'WARN'):
                $attachment->setColor(self::COLOR_WARN);

                break;
            case false !== \strpos($levelName, 'ERROR'):
                $attachment->setColor(self::COLOR_ERROR);

                break;
            case false !== \strpos($levelName, 'FATAL'):
                $attachment->setColor(self::COLOR_FATAL);

                break;
            default:
                $attachment->setColor(self::COLOR_DEFAULT);
        }

        return $attachment;
    }

    /**
     * Add logger name as attachment field.
     *
     * @param Attachment $attachment
     *
     * @return Attachment
     */
    protected function _addFieldLoggerName(Attachment $attachment): Attachment
    {
        $loggerField = new \Maknz\Slack\AttachmentField([]);
        $loggerField
            ->setTitle('Logger')
            ->setValue($this->getName())
            ->setShort('1');

        $attachment->addField($loggerField);

        return $attachment;
    }

    /**
     * Add date as attachment field.
     *
     * @param Attachment $attachment
     *
     * @throws \Exception
     *
     * @return Attachment
     */
    protected function _addFieldDate(Attachment $attachment): Attachment
    {
        $dateField = new \Maknz\Slack\AttachmentField([]);
        $dateField
            ->setTitle('Date')
            ->setValue((new \DateTime())->format('Y-m-d H:i:s'))
            ->setShort('1');

        $attachment->addField($dateField);

        return $attachment;
    }

    /**
     * Set message title.
     *
     * @param \Maknz\Slack\Message $message
     *
     * @return \Maknz\Slack\Message
     */
    protected function _setMessageTitle(\Maknz\Slack\Message $message): \Maknz\Slack\Message
    {
        $logMessage = $this->_getText();

        if (\strlen($logMessage) > 150) {
            $logMessage = \substr($logMessage, 0, 150);
        }

        $message->setText(
            $this->getLevelName().' '.$this->getName().' '.$logMessage
        );
        if ($this->_isAllowMarkdown()) {
            $message->setText($this->_getMarkdownTitleText($logMessage));
        }

        return $message;
    }

    /**
     * Generate message to send.
     *
     * @return \Maknz\Slack\Message
     */
    public function generateMessage(): \Maknz\Slack\Message
    {
        // create message
        $message = new \Maknz\Slack\Message($this->_getSlackClient());
        // set username
        $message->from($this->_getUsername());
        // set icon
        $message->setIcon($this->_getIcon());
        // set channel
        $message->setChannel($this->_getChannel());
        // allow markdown in message
        $message->setAllowMarkdown($this->_isAllowMarkdown());
        // send log message as attachment
        if (\true === $this->_sendLogAsAttachment()) {
            // inject formatted message from event
            $message->attach($this->_generateAttachment());
        }
        // set name of logger as text
        return $this->_setMessageTitle($message);
    }

    /**
     * Set icon By log level.
     *
     * @param LoggerLoggingEvent $event
     *
     * @return Slack
     */
    protected function _setIconByLevel(LoggerLoggingEvent $event): self
    {
        if (true !== $this->_canAddEmoji()) {
            return $this;
        }
        $icon = '';
        if (\LoggerLevel::TRACE === $event->getLevel()->toInt()) {
            $icon = ':squirrel:';
        }
        if (\LoggerLevel::DEBUG === $event->getLevel()->toInt()) {
            $icon = ':suspect:';
        }
        if (\LoggerLevel::INFO === $event->getLevel()->toInt()) {
            $icon = ':suspect:';
        }
        if (\LoggerLevel::WARN === $event->getLevel()->toInt()) {
            $icon = ':feelsgood:';
        }
        if (\LoggerLevel::ERROR === $event->getLevel()->toInt()) {
            $icon = ':goberserk:';
        }
        if (\LoggerLevel::FATAL === $event->getLevel()->toInt()) {
            $icon = ':rage4:';
        }
        $this->setIcon($icon);

        return $this;
    }
}
