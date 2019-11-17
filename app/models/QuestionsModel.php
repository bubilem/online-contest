<?php

class QuestionsModel extends MainModel
{
    protected static $attributes = ['title', 'description', 'begin', 'end', 'questions'];

    public function __construct(IDataHandler $dataHandler = null)
    {
        parent::__construct($dataHandler);
    }

    public function load(): QuestionsModel
    {
        return $this->fromArray($this->dataHandler->setFilename("questions")->load());
    }

    public function isActive(): bool
    {
        return $this->getBegin() && $this->getEnd() && $this->getBegin() <= Date::now() && Date::now() <= $this->getEnd();
    }

    public function isClosed(): bool
    {
        return !$this->getEnd() || Date::now() > $this->getEnd();
    }

    public function addQuestion(QuestionModel $question)
    {
        $this->data['questions'][] = $question;
    }

    public function getQuestion(int $i): QuestionModel
    {
        if (isset($this->getQuestions()[$i])) {
            return $this->getQuestions()[$i];
        }
        return null;
    }

    public function get(): array
    {
        return $this->getQuestions();
    }

    public function getQuestionByCode(string $code): QuestionModel
    {
        foreach ($this->getQuestions() as $question) {
            if ($question->getCode() == $code) {
                return $question;
            }
        }
        return null;
    }

    public function getCountOfQuestions(): int
    {
        return is_array($this->getQuestions()) ? count($this->getQuestions()) : 0;
    }

    public function clear(): QuestionsModel
    {
        $this->data = [];
        return $this;
    }

    public function fromArray(array $data): QuestionsModel
    {
        foreach (self::$attributes as $attribute) {
            if ($attribute == 'questions' &&  is_array($data['questions'])) {
                foreach ($data['questions'] as $question) {
                    $this->addQuestion(QuestionModel::create($question));
                }
            } else if (isset($data[$attribute])) {
                $fce = 'set' . ucfirst($attribute);
                $this->$fce($data[$attribute]);
            }
        }
        return $this;
    }

    public function toArray(): array
    {
        $data = [];
        foreach (self::$attributes as $attribute) {
            if ($attribute == 'questions') {
                foreach ($this->getQuestions() as $question) {
                    $data['questions'][] = $question->toArray();
                }
            } else {
                $fce = 'get' . ucfirst($attribute);
                $data[$attribute] = $this->$fce();
            }
        }
        return $data;
    }
}
