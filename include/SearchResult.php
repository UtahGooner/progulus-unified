<?php


namespace progulusAPI;


class SearchResult {
    public $id = 0;
    public $artist = null;
    public $album = null;
    public $title = null;
    public $duration = 0;
    public $queued = false;
    public $recent = false;
    public $votes = 0;
    public $voteRatings = 0;
    public $avgRating = 0;
    public $dateLastPlayed = null;
    public $plays = 0;
    public $songs = 0;
    public $userVotes = 0;
    public $userRating = 0;
    public $genre = '';
    public $track = null;
    public $picture = null;
    public $albumyear = null;

    public $debug;

    public function __construct($row)
    {
//        $this->debug = $row;
        $this->artist = $row['artist'];

        $this->genre = $row['genre'] ?? '';
        $this->dateLastPlayed = $row['dateLastPlayed'] ?? null;

        if (isset($row['id'])) {
            $this->id = (int)$row['id'];
        }

        if (isset($row['duration'])) {
            $this->duration = (int)$row['duration'];
        }

        if (isset($row['picture'])) {
            $this->picture = $row['picture'];
        }

        if (isset($row['queued'])) {
            $this->queued = (bool)$row['queued'];
        }
        if (isset($row['recent'])) {
            $this->recent = (bool)$row['recent'];
        }
        if (isset($row['votes'])) {
            $this->votes = (int)$row['votes'];
        }
        if (isset($row['plays'])) {
            $this->plays = (int)$row['plays'];
        }
        if (isset($row['songs'])) {
            $this->songs = (int)$row['songs'];
        }
        if (isset($row['voteRatings'])) {
            $this->voteRatings = (float)$row['voteRatings'];
        }
        if (isset($row['avgRating'])) {
            $this->avgRating = (float)$row['avgRating'];
        }
        if (isset($row['userRating'])) {
            $this->userRating = (float)$row['userRating'];
        }
        if (isset($row['userVotes'])) {
            $this->userVotes = (int)$row['userVotes'];
        }

        if (isset($row['album'])) {
            $this->album = $row['album'];
        }
        if (isset($row['track'])) {
            $this->track = (int)$row['track'];
        }
        if (isset($row['albumyear'])) {
            $this->albumyear = $row['albumyear'];
        }

        if (isset($row['title'])) {
            $this->title = $row['title'];
            $this->songs = 1;
            $this->userVotes = $this->userRating > 0 ? 1 : 0;
        }
    }
}
