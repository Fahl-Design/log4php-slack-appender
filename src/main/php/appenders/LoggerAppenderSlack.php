<?php

use Maknz\Slack\Attachment;
use Maknz\Slack\Client;

/**
 * LoggerAppenderSlack appends log events to a Slack channel.
 *
 *
 * @since      2.4.0
 *
 * @author     Benjamin Fahl <ben@webproject.xyz>
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, v2.0
 *
 * @link       http://logging.apache.org/log4php
 */
class LoggerAppenderSlack extends LoggerAppender
{
    const ENDPOINT_VALIDATION_STRING = 'https://hooks.slack.com';

    /**
     * @var Maknz\Slack\Client|null
     */
    protected $_slackClient;

    /**
     * @var string
     */
    protected $_username;

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
    protected $_icon;

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
     * Get LinkNames.
     *
     * @return bool
     */
    protected function _isLinkNames()
    {
        return $this->_linkNames;
    }

    /**
     * Set LinkNames.
     *
     * @param bool $linkNames
     *
     * @return LoggerAppenderSlack
     */
    public function setLinkNames($linkNames)
    {
        $this->_linkNames = (bool) (int) $linkNames;

        return $this;
    }

    /**
     * Set AddEmoji.
     *
     * @param bool $addEmoji
     *
     * @return LoggerAppenderSlack
     */
    public function setAddEmoji($addEmoji)
    {
        $this->_addEmoji = (bool) (int) $addEmoji;

        return $this;
    }

    /**
     * Get UnfurlLinks.
     *
     * @return bool
     */
    protected function _isUnfurlLinks()
    {
        return $this->_unfurlLinks;
    }

    /**
     * Set UnfurlLinks.
     *
     * @param bool $unfurlLinks
     *
     * @return LoggerAppenderSlack
     */
    public function setUnfurlLinks($unfurlLinks)
    {
        $this->_unfurlLinks = (bool) (int) $unfurlLinks;

        return $this;
    }

    /**
     * Get UnfurlMedia.
     *
     * @return bool
     */
    protected function _isUnfurlMedia()
    {
        return $this->_unfurlMedia;
    }

    /**
     * Set UnfurlMedia.
     *
     * @param bool $unfurlMedia
     *
     * @return LoggerAppenderSlack
     */
    public function setUnfurlMedia($unfurlMedia)
    {
        $this->_unfurlMedia = (bool) (int) $unfurlMedia;

        return $this;
    }

    /**
     * Get Text.
     *
     * @return string
     */
    protected function _getText()
    {
        return $this->_text;
    }

    /**
     * Overwrite layout with LoggerLayoutSlack.
     *
     * @return LoggerLayoutSlack
     */
    public function getDefaultLayout()
    {
        return new LoggerLayoutSlack();
    }

    /**
     * Forwards the logging event to the destination.
     *
     * Derived appenders should implement this method to perform actual logging.
     *
     * @param LoggerLoggingEvent $event
     */
    protected function append(LoggerLoggingEvent $event)
    {
        // format text with layout
        $this->_formatEventToText($event);
        // get slack client
        $this->_getSlackClient();
        // generate message
        $message = $this->generateMessage();
        // send message
        $message->send();
    }

    /**
     * Wrapper for layout format.
     *
     * @param LoggerLoggingEvent $event
     *
     * @return $this
     */
    protected function _formatEventToText(LoggerLoggingEvent $event)
    {
        $this->_text = trim($this->layout->format($event));
        $this->_setIconByLevel($event);
        $this->_setLevelName($event->getLevel()->toString());

        return $this;
    }

    /**
     * Get SlackClient.
     *
     * @return Maknz\Slack\Client
     */
    protected function _getSlackClient()
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
     * @return LoggerAppenderSlack
     */
    public function setSlackClient(Client $client = null)
    {
        $this->_slackClient = $client;

        return $this;
    }

    /**
     * Init php slack client.
     *
     * @return $this
     */
    protected function _initSlackClient()
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
    protected function _getEndpoint()
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
     * @return LoggerAppenderSlack
     */
    public function setEndpoint($endpoint)
    {
        if (true === is_string($endpoint)
            && 0 === strpos($endpoint, self::ENDPOINT_VALIDATION_STRING)
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
    protected function _getUsername()
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
    public function setUsername($username)
    {
        if (!empty($username) && is_string($username)) {
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
    protected function _getIcon()
    {
        return $this->_icon;
    }

    /**
     * Set Icon.
     *
     * @param string $icon
     *
     * @return LoggerAppenderSlack
     */
    public function setIcon($icon)
    {
        $this->_icon = $icon;

        return $this;
    }

    /**
     * Get Channel.
     *
     * @return string
     */
    protected function _getChannel()
    {
        return $this->_channel;
    }

    /**
     * Set Channel.
     *
     * @param string $channel
     *
     * @return LoggerAppenderSlack
     */
    public function setChannel($channel)
    {
        $this->_channel = $channel;

        return $this;
    }

    /**
     * Get Flag for allowed markdown.
     *
     * @return bool
     */
    protected function _isAllowMarkdown()
    {
        return (bool) $this->_allowMarkdown;
    }

    /**
     * Set AllowMarkdown.
     *
     * @param bool|string $allowMarkdown
     *
     * @return LoggerAppenderSlack
     */
    public function setAllowMarkdown($allowMarkdown)
    {
        if (is_string($allowMarkdown) && $allowMarkdown === 'false') {
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
    protected function _sendLogAsAttachment()
    {
        return (bool) $this->_asAttachment;
    }

    /**
     * Set SendLogAsAttachment.
     *
     * @param bool $sendLogAsAttachment
     *
     * @return LoggerAppenderSlack
     */
    public function setAsAttachment($sendLogAsAttachment)
    {
        if (is_string($sendLogAsAttachment) && $sendLogAsAttachment === 'false') {
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
    protected function _generateAttachment()
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
        $attachment = $this->_addFieldDate($attachment);

        return $attachment;
    }

    /**
     * Get LevelName.
     *
     * @return string
     */
    public function getLevelName()
    {
        return $this->_levelName;
    }

    /**
     * Set LevelName.
     *
     * @param string $levelName
     *
     * @return LoggerAppenderSlack
     */
    protected function _setLevelName($levelName)
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
    protected function _getMarkdownTitleText($logMessage)
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
    protected function _setColorByLevelName(Attachment $attachment, $levelName)
    {
        switch (true) {
            case strpos($levelName, 'TRACE') !== false:
            case strpos($levelName, 'DEBUG') !== false:
                $attachment->setColor('#BDBDBD');
                break;
            case strpos($levelName, 'INFO') !== false:
                $attachment->setColor('#64B5F6');
                break;
            case strpos($levelName, 'WARN') !== false:
                $attachment->setColor('#FFA726');
                break;
            case strpos($levelName, 'ERROR') !== false:
                $attachment->setColor('#EF6C00');
                break;
            case strpos($levelName, 'FATAL') !== false:
                $attachment->setColor('#D84315');
                break;
            default:
                $attachment->setColor('good');
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
    protected function _addFieldLoggerName(Attachment $attachment)
    {
        $loggerField = new \Maknz\Slack\AttachmentField([]);
        $loggerField
            ->setTitle('Logger')
            ->setValue($this->getName())
            ->setShort(\true);

        $attachment->addField($loggerField);

        return $attachment;
    }

    /**
     * Add date as attachment field.
     *
     * @param Attachment $attachment
     *
     * @return Attachment
     */
    protected function _addFieldDate(Attachment $attachment)
    {
        $dateField = new \Maknz\Slack\AttachmentField([]);
        $dateField
            ->setTitle('Date')
            ->setValue((new \DateTime())->format('Y-m-d H:i:s'))
            ->setShort(\true);

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
    protected function _setMessageTitle(\Maknz\Slack\Message $message)
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
    public function generateMessage()
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
        $message = $this->_setMessageTitle($message);

        return $message;
    }

    /**
     * Set icon By log level.
     *
     * @param LoggerLoggingEvent $event
     *
     * @return null
     */
    protected function _setIconByLevel(\LoggerLoggingEvent $event)
    {
        if ($this->_canAddEmoji() !== true) {
            return null;
        }
        $icon = '';
        if ($event->getLevel()->toInt() === \LoggerLevel::TRACE) {
            $icon = ':squirrel:';
        }
        if ($event->getLevel()->toInt() === \LoggerLevel::DEBUG) {
            $icon = ':suspect:';
        }
        if ($event->getLevel()->toInt() === \LoggerLevel::INFO) {
            $icon = ':suspect:';
        }
        if ($event->getLevel()->toInt() === \LoggerLevel::WARN) {
            $icon = ':feelsgood:';
        }
        if ($event->getLevel()->toInt() === \LoggerLevel::ERROR) {
            $icon = ':goberserk:';
        }
        if ($event->getLevel()->toInt() === \LoggerLevel::FATAL) {
            $icon = ':rage4:';
        }
        $this->setIcon($icon);
    }

    /**
     * Should add emoji to attachment title.
     *
     * @return bool
     */
    protected function _canAddEmoji()
    {
        return $this->_addEmoji;
    }
}
