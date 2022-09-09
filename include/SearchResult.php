<?php


namespace progulusAPI;


class SearchResult {
    // BasicSong props
    public $id = 0;
    public $artist = null;
    public $album = null;
    public $title = null;
    public $duration = 0;
    public $picture = null;
    public $albumYear = null;
    public $votes = 0;
    public $rating = 0;
    public $userRating = 0;
    public $dateLastPlayed = null;
    public $website = '';
    public $country = '';

    //SearchResult Props
    public $queued = false;
    public $recent = false;
    public $popularity = 0;
    public $plays = 0;
    public $songs = 0;
    public $userVotes = 0;
    public $genre = '';
    public $track = null;
    public $albums;

    public $debug;

    public function __construct($row)
    {
//        $this->debug = $row;
        foreach ($row as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value ?? '';
            }
        }
        if (isset($row['id'])) {
            $this->id = (int)$row['id'];
        }

        if (isset($row['duration'])) {
            $this->duration = (int)$row['duration'];
        }

        if (isset($row['dateLastPlayed'])) {
            $this->dateLastPlayed = (int)$row['dateLastPlayed'];
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

        if (isset($row['popularity'])) {
            $this->popularity = (float)$row['popularity'];
        }

        if (isset($row['rating'])) {
            $this->rating = (float)$row['rating'];
        }

        if (isset($row['userRating'])) {
            $this->userRating = (float)$row['userRating'];
        }

        if (isset($row['userVotes'])) {
            $this->userVotes = (int)$row['userVotes'];
        }

        if (isset($row['track'])) {
            $this->track = (int)$row['track'];
        }

        if (isset($row['albumYear'])) {
            $this->albumYear = $row['albumYear'];
        }

        if (isset($row['title'])) {
            $this->title = $row['title'];
            $this->songs = 1;
            $this->userVotes = $this->userRating > 0 ? 1 : 0;
        }

        if (isset($row['albums'])) {
            $this->albums = (int)$row['albums'];
        }
    }
}
