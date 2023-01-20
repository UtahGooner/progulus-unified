<?php


namespace progulusAPI;

use \PDO;

require_once 'SamPDO.php';
require_once 'autoload.inc.php';


class CurrentSong {
    public $id = 0;
    public $artist = '';
    public $album = '';
    public $title = '';
    public $duration = 0;
    public $picture = '';
    public $albumYear = '';
    public $votes = 0;
    public $rating = 0;
    public $userRating = 0;
    public $dateLastPlayed = null;
    public $website = '';
    public $country = '';

    public $buycd = '';
    public $sinceStart = 0;
    public $now = 0;
    public $requester = 'HAL';
    public $msgname = '';
    public $msg = '';
    public $listeners = 0;
    public $ratings = null;

    public function __construct($row)
    {
        $this->id = (int) $row['id'] ?? 0;
        $this->duration = (int) $row['duration'] ?? 0;
        $this->artist = $row['artist'] ?? '';
        $this->album = $row['album'] ?? '';
        $this->title = $row['title'] ?? '';
        $this->country = $row['country'] ?? '';
        $this->albumYear = $row['albumYear'] ?? '';
        $this->website = $row['website'] ?? '';
        $this->buycd = $row['buycd'] ?? '';
        $this->picture = $row['picture'] ?? '';
        $this->votes = (int) $row['votes'] ?? 0;
        $this->rating = (float) $row['rating'] ?? 0;
        $this->dateLastPlayed = isset($row['dateLastPlayed']) ? (int) $row['dateLastPlayed'] : null;
        $this->requester = $row['requester'] ?? '';
        $this->msgname = $row['msgname'] ?? '';
        $this->msg = $row['msg'] ?? '';
        $this->sinceStart = (int) $row['sinceStart'] ?? 0;
        $this->userRating = (float) $row['userRating'] ?? 0;
        $this->now = isset($row['listeners']) ? (int) $row['now'] : time();
        $this->listeners = isset($row['listeners']) ? (int) $row['listeners'] : 0;
    }

    /**
     * @param int $userID
     * @param int $limit
     * @param int $offset
     * @param int $since
     * @return CurrentSong[]
     * @throws \Exception
     */
    public static function loadCurrentSongs(int $userID = 1, int $limit = 10, int $offset = 0, int $since = 0): array
    {
        $songs = [];

        $pdo = SamPDO::singleton();
        if (!$pdo) {
            throw new \Exception('Unable to connect to database');
        }

        $querySongs = file_get_contents(__DIR__ . '/sql/current.sql');

        $ps = $pdo->prepare($querySongs);
        $ps->bindValue(':offset', $offset ?? 0, PDO::PARAM_INT);
        $ps->bindValue(':limit', $limit ?? 10, PDO::PARAM_INT);
        $ps->bindValue(':userID', $userID, PDO::PARAM_INT);
        $ps->bindValue(':before', $before ?? 0, PDO::PARAM_INT);

        if (!$ps->execute()) {
            throw new \Exception($ps->errorInfo()[2]);
        }

        while ($row = $ps->fetch(PDO::FETCH_ASSOC)) {
            $songs[] = new CurrentSong($row);
        }

        return $songs;
    }

    public static function loadQueue(int $userID = 1) {
        $queue = [];

        $pdo = SamPDO::singleton();
        if (!$pdo) {
            throw new \Exception('Unable to connect to database');
        }

        $queryQueue = file_get_contents(__DIR__ . '/sql/queue.sql', true);

        $ps = $pdo->prepare($queryQueue);
        $ps->bindValue('userID', $userID, PDO::PARAM_INT);
        if (!$ps->execute()) {
            throw new \Exception($ps->errorInfo()[2]);
        }

        while ($row = $ps->fetch(PDO::FETCH_ASSOC)) {
            $queue[] = new CurrentSong($row);
        }

        return $queue;
    }

    public static function DurationToMMSS($duration = 0) {
        $ss = round($duration / 1000);
        $mm = (int)($ss / 60);
        $ss = ($ss % 60);
        if($ss<10) $ss="0$ss";
        return "{$mm}:{$ss}";
    }
}
