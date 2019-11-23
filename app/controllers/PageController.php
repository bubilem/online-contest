<?php

class PageController
{

    private $page;
    private $user;

    public function __construct()
    {
        $this->page = new Template('page');
        $this->user = new UserModel(new JsonDataHandler());
    }

    public static function create(): PageController
    {
        return new PageController();
    }

    public function run(): void
    {
        if (!$this->user->getSigned()) {
            $this->login();
        }
        if ($this->user->getSigned()) {
            switch (filter_input(INPUT_GET, 'a')) {
                case 's':
                    $this->score();
                    break;
                case 'o':
                    if (!filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING)) {
                        $this->user->clear();
                        $this->run();
                        return;
                    } else {
                        $this->home();
                        break;
                    }
                case 'q':
                    $this->question(filter_input(INPUT_GET, 'c', FILTER_SANITIZE_STRING));
                    break;
                case 'a':
                    $this->answer(filter_input(INPUT_GET, 'c', FILTER_SANITIZE_STRING));
                default:
                    $this->home();
            }
            $this->page->setData('signedInfo', new Template('signedInfo', ['email' => $this->user->getEmail()]));
        }
        echo $this->page;
    }

    private function score()
    {
        $content = '';
        foreach ((new ScoreModel())->getPlayers() as $email => $score) {
            $content .= new Template('scoreListItem', [
                'email' => UserModel::hideEmail($email),
                'score' => $this->user->getQuestions()->isClosed() ? $score : '-'
            ]);
        }
        $this->page->setData([
            'title' => 'ŠkolaVDF',
            'caption' => $this->user->getQuestions()->getTitle(),
            'description' => $this->user->getQuestions()->getDescription(),
            'message' => $this->user->getMessage(),
            'content' => new Template('scoreList', ['content' => $content])
        ]);
    }

    private function answer(string $code)
    {
        $question = $this->user->getQuestions()->getQuestionByCode($code);
        if (!$question instanceof QuestionModel || $question->getState() == QuestionModel::STATE_CLOSED) {
            $this->user->getMessage()->add("Dějou se tu nějaké podivnosti.", MessageModel::ALERT);
            return;
        }
        if (!$this->user->getQuestions()->isActive()) {
            $this->user->getMessage()->add("Otázka je zamknutá.", MessageModel::ALERT);
            return;
        }
        if ($question->getState() != QuestionModel::STATE_OPEN) {
            $this->user->getMessage()->add("Odpověď jste už asi vybral.", MessageModel::ALERT);
            return;
        }
        $answerNumber = filter_input(INPUT_GET, 'n', FILTER_SANITIZE_NUMBER_INT);
        if ($answerNumber < 0 || $answerNumber >= $question->getCountOfAnswers()) {
            $this->user->getMessage()->add("Taková odpověď neexistuje.", MessageModel::ALERT);
            return;
        }
        $question->setState(QuestionModel::STATE_FILLED)->setSelected($answerNumber);
        if ($this->user->save()) {
            $this->user->getMessage()->add("Odpověď zaznamenána.", MessageModel::SUCCESS);
        } else {
            $this->user->getMessage()->add("Nastala chyba při zaznamenávání opodvědi.", MessageModel::ALERT);
        }
    }

    private function question(string $code)
    {
        $question = $this->user->getQuestions()->getQuestionByCode($code);
        if (!$question instanceof QuestionModel) {
            $this->user->getMessage()->add("Dějou se tu nějaké podivnosti.", MessageModel::ALERT);
            $this->home();
            return;
        }
        if (!$this->user->getQuestions()->isActive()) {
            $this->user->getMessage()->add("Otázka je zamknutá.", MessageModel::ALERT);
            $this->home();
            return;
        }
        if ($question->getState() == QuestionModel::STATE_CLOSED) {
            $question->setState(QuestionModel::STATE_OPEN);
            if ($this->user->save()) {
                $this->user->getMessage()->add("Otázka byla odemknuta.", MessageModel::SUCCESS);
            }
        }
        if ($question->getState() != QuestionModel::STATE_OPEN) {
            $this->user->getMessage()->add("Odpověď jste už asi vybral.", MessageModel::ALERT);
            $this->home();
            return;
        }
        $this->page->setData([
            'title' => 'ŠkolaVDF',
            'caption' => $this->user->getQuestions()->getTitle(),
            'description' => $this->user->getQuestions()->getDescription(),
            'message' => $this->user->getMessage(),
            'content' => new Template('questionDetail', [
                'question' => $question->getQuestion(),
                'code' => $question->getCode(),
                'answer-0' => $question->getAnswer(0),
                'answer-1' => $question->getAnswer(1),
                'answer-2' => $question->getAnswer(2),
            ])
        ]);
    }

    private function login()
    {
        $questions = (new QuestionsModel(new JsonDataHandler))->load();
        switch (filter_input(INPUT_POST, 'a', FILTER_SANITIZE_STRING)) {
            case 'login':
                $success = $this->user->sign(
                    filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                    filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING)
                );
                break;
            case 'register':
                if (isset($_SESSION['r']) || !$questions->isActive()) {
                    break;
                }
                $success = $this->user->register(
                    filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                    filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING),
                    filter_input(INPUT_POST, 'password2', FILTER_SANITIZE_STRING)
                );
                if ($success) {
                    $_SESSION['r'] = 1;
                }
                break;
            default:
                $success = false;
        }
        if (!$success) {
            $this->user->getMessage()->add("Pro soutěžení nebo nahlížení na své odpovědi musíte být přihlášen.", MessageModel::ALERT);
            $content = Template::create('sign');
            if (!isset($_SESSION['r'])) {
                if ($questions->isActive()) {
                    $content .= Template::create('register');
                } else {
                    $this->user->getMessage()->add("Není možné se registrovat, pokud není aktivní soutěž.", MessageModel::ALERT);
                }
            }
            $this->page->setData([
                'title' => 'ŠkolaVDF',
                'caption' => $questions->getTitle(),
                'description' => $questions->getDescription(),
                'message' => $this->user->getMessage(),
                'content' => $content
            ]);
        }
    }

    private function home()
    {
        $content = '';
        foreach ($this->user->getQuestions()->get() as $key => $question) {
            switch ($question->getState()) {
                case QuestionModel::STATE_FILLED:
                    $content .= new Template('questionTabFilled', [
                        'number' => $key + 1,
                        'question' => $question->getQuestion(),
                        'yourAnswer' => $question->getAnswer($question->getSelected()),
                        'rightAnswer' => $this->user->getQuestions()->isClosed() ? $question->getAnswer($question->getCorrect()) : '-'
                    ]);
                    break;
                case QuestionModel::STATE_OPEN:
                    if ($this->user->getQuestions()->isActive()) {
                        $content .= new Template('questionTabOpen', [
                            'code' => $question->getCode(),
                            'number' => $key + 1
                        ]);
                        break;
                    }
                default:
                    $content .= new Template('questionTabClosed', [
                        'number' => $key + 1
                    ]);
            }
        }
        $this->page->setData([
            'title' => 'ŠkolaVDF',
            'caption' => $this->user->getQuestions()->getTitle(),
            'description' => $this->user->getQuestions()->getDescription(),
            'message' => $this->user->getMessage(),
            'content' => new Template('questions', [
                'content' => $content
            ])
        ]);
    }
}
