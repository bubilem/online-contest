<?php
class ScoreModel
{

    private $players;

    public function __construct()
    {
        $this->players = self::shuffle(self::load());
    }

    private static function load(): array
    {
        $players = [];
        foreach (array_diff(scandir('data/users'), array('..', '.')) as $filename) {
            $user = new UserModel(new JsonDataHandler());
            $user->load(substr($filename, 0, -5));
            $players[$user->getEmail()] = $user->getQuestions()->getScore();
        }
        return $players;
    }
    private static function shuffle(array $players): array
    {
        $keys = array_keys($players);
        shuffle($keys);
        $shuffledPlayers = [];
        foreach ($keys as $key) {
            $shuffledPlayers[$key] = $players[$key];
        }
        arsort($shuffledPlayers);
        return $shuffledPlayers;
    }

    public function getPlayers()
    {
        return is_array($this->players) ? $this->players : [];
    }
}
