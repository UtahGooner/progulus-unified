<?php


namespace progulusAPI;


class CurrentSong {
    public $id = 0;
    public $duration = 0;
    public $artist = '';
    public $album = '';
    public $title = '';
    public $country = '';
    public $website = '';
    public $buycd = '';
    public $picture = '';
    public $votes = 0;
    public $rating = 0;
    public $dateLastPlayed = null;
    public $sinceStart = 0;
    public $now = 0;
    public $requester = 'HAL';
    public $msgname = '';
    public $msg = '';
    public $albumYear = '';
    public $userRating = 0;
    public $listeners = 0;

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
        $this->dateLastPlayed = (int) $row['dateLastPlayed'] ?? null;
        $this->requester = $row['requester'] ?? '';
        $this->msgname = $row['msgname'] ?? '';
        $this->msg = $row['msg'] ?? '';
        $this->sinceStart = (int) $row['sinceStart'] ?? 0;
        $this->userRating = (int) $row['userRating'] ?? 0;
        $this->now = (int) $row['now'] ?? time();
        $this->listeners = (int) $row['listeners'] ?? 0;
    }

}
