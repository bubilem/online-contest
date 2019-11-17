<?php

class QuestionModel extends MainModel
{
    protected static $attributes = ['code', 'question', 'answers', 'correct', 'state', 'selected'];

    const STATE_CLOSED = 1;
    const STATE_OPEN = 2;
    const STATE_FILLED = 3;

    public function __construct(IDataHandler $dataHandler = null)
    {
        parent::__construct($dataHandler);
    }

    public static function create(array $data): QuestionModel
    {
        return (new QuestionModel())->fromArray($data);
    }

    public function getAnswer(int $i): string
    {
        return $this->getAnswers()[$i];
    }

    public function getCountOfAnswers(): int
    {
        return count($this->getAnswers());
    }

    public function fromArray(array $data): QuestionModel
    {
        foreach (self::$attributes as $attribute) {
            $setter = 'set' . ucfirst($attribute);
            $this->$setter(isset($data[$attribute]) ? $data[$attribute] : ($attribute == 'state' ? self::STATE_CLOSED : ''));
        }
        return $this;
    }

    public function toArray(): array
    {
        return $this->getData();
    }
}
