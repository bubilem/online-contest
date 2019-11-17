<?php

/**
 * Message container
 */
class MessageModel
{

    const ALERT = 'Alert';
    const SUCCESS = 'Success';

    /**
     * Array of messages
     *
     * @var array
     */
    private $messages;

    /**
     * Constructor
     * 
     * Allows direct insertion of one message
     * @param string $message
     * @param string $type
     */
    public function __construct(string $message = '', string $type = self::ALERT)
    {
        $this->messages = [];
        if (!empty($message)) {
            $this->add($message, $type);
        }
    }

    /**
     * Static factory
     *
     * @return MessageModel
     */
    public static function create(): MessageModel
    {
        return new MessageModel();
    }

    /**
     * Add message to messages container
     *
     * @param string $message
     * @param string $type
     * @return MessageModel
     */
    public function add(string $message, string $type = self::ALERT): MessageModel
    {
        $this->messages[] = ['type' => $type, 'content' => $message];
        return $this;
    }

    /**
     * Generates html string from messages container
     *
     * @return string
     */
    public function render(): string
    {
        $str = '';
        foreach ($this->messages as $message) {
            $str .= (string) new Template('message', ['type' => strtolower($message['type']), 'content' => $message['content']]);
        }
        $this->messages = [];
        return $str;
    }

    /**
     * Transfer object to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
