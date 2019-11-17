<?php

class UserModel extends MainModel
{
    const SESSION_NAME = 'user_session';
    protected static $attributes = ['email', 'password', 'questions'];

    public function __construct(IDataHandler $dataHandler)
    {
        parent::__construct($dataHandler);
        if (!$this->getSigned() || !$this->load($_SESSION[self::SESSION_NAME])) {
            $this->clear();
        }
    }

    public function sign(string $email, string $password): bool
    {
        if (empty($email) || empty($password)) {
            $this->message->add("Chybí údaje.", MessageModel::ALERT);
            return false;
        }
        $this->load($email);
        if ($this->getEmail() != $email || $this->getPassword() != self::hash($password)) {
            $this->clear();
            $this->message->add("Špatné přihlašovací údaje.", MessageModel::ALERT);
            return false;
        }
        $_SESSION[self::SESSION_NAME] = $this->getEmail();
        $this->message->add("Uživatel byl přihlášen.", MessageModel::SUCCESS);
        return true;
    }

    public function register(string $email, string $password, string $password2): bool
    {
        if (empty($email) || empty($password) || empty($password2)) {
            $this->message->add("Chybí údaje.", MessageModel::ALERT);
            return false;
        }
        if ($password != $password2) {
            $this->message->add("Neshodují se hesla.", MessageModel::ALERT);
            return false;
        }
        $this->setEmail($email)->setPassword($this->hash($password))->setQuestions((new QuestionsModel($this->dataHandler))->load());
        if ($this->exists()) {
            $this->clear();
            $this->message->add("Uživatel je již registrován.", MessageModel::ALERT);
            return false;
        }
        if (!$this->save()) {
            $this->clear();
            $this->message->add("Chyba při ukládání záznamu.", MessageModel::ALERT);
            return false;
        }
        $_SESSION[self::SESSION_NAME] = $this->getEmail();
        $this->message->add("Uživatel byl úspěšně registrován.", MessageModel::SUCCESS);
        return true;
    }

    public function exists($email = null): bool
    {
        return $this->dataHandler->setFilename('users/' . $email ?? $this->getEmail())->exists();
    }

    public function load($email = null): bool
    {
        $data = $this->dataHandler->setFilename('users/' . $email ?? $this->getEmail())->load();
        if (empty($data['email']) || empty($data['password']) || empty($data['questions']) || !is_array($data['questions'])) {
            $this->clear();
            return false;
        }
        $this->setEmail($data['email']);
        $this->setPassword($data['password']);
        $this->setQuestions((new QuestionsModel())->fromArray($data['questions']));
        return true;
    }

    public function save(): bool
    {
        $data = [
            'email' => $this->getEmail(),
            'password' => $this->getPassword(),
            'questions' => $this->getQuestions()->toArray()
        ];
        return $this->dataHandler->setFilename('users/' . $this->getEmail())->save($data);
    }

    public function clear()
    {
        $this->data = [];
        if (isset($_SESSION[self::SESSION_NAME])) {
            unset($_SESSION[self::SESSION_NAME]);
        }
    }

    public function getSigned(): bool
    {
        return !empty($_SESSION[self::SESSION_NAME]);
    }
}
