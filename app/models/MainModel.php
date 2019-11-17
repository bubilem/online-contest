<?php

abstract class MainModel
{
    protected $data;
    protected $dataHandler;
    protected $message;

    public function __construct(IDataHandler $dataHandler = null)
    {
        $this->data = [];
        if ($dataHandler !== null) {
            $this->setDataHandler($dataHandler);
        }
        $this->message = new MessageModel();
    }

    public function setDataHandler(IDataHandler $dataHandler)
    {
        $this->dataHandler = $dataHandler;
        return $this;
    }

    public function __call($name, $arguments)
    {
        if (strlen($name) < 4) {
            return;
        }
        $operation = substr($name, 0, 3);
        $attribute = strtolower(substr($name, 3));
        if (!in_array($attribute, static::$attributes)) {
            return;
        }
        switch ($operation) {
            case 'get':
                return isset($this->data[$attribute]) ? $this->data[$attribute] : null;
            case 'set':
                $value = isset($arguments[0]) ? $arguments[0] : null;
                $this->data[$attribute] = $value;
                return $this;
            case 'clr':
                $this->data[$attribute] = null;
                return $this;
        }
    }

    protected function clear()
    {
        $this->data = [];
        return $this;
    }

    public static function hash($value)
    {
        return hash('sha256', $value);
    }

    public function getMessage(): MessageModel
    {
        return $this->message;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
