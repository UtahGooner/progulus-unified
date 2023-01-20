<?php

namespace progulusAPI;

use Exception;
use PDO;

require_once 'SearchResult.php';

class Search {
    const SEARCH_FOR_ARTIST = 'artist';
    const SEARCH_FOR_ALBUM = 'album';
    const SEARCH_FOR_SONG = 'song';

    const SEARCH_RESULT_ARTISTS = 'artists';
    const SEARCH_RESULT_ALBUMS = 'albums';
    const SEARCH_RESULT_SONGS = 'songs';

    const SEARCH_YEAR = '/year:[ ]{0,1}(new|[0-9]{4}|[0-9\[\]\|\-\(\)]+)[ ]*/i';
    const SEARCH_COUNTRY = '/country:[ ]{0,1}(multi|[a-z]{2}|[\S\(\)\|\[\]]+)[ ]*/i';
    const SEARCH_RATING = '/(avg|rating):[ ]{0,1}([0-5][\.]*[0-9]*)[\-]*([0-5]*[\.]*[0-9]*)[ ]*/i';
    const SEARCH_RATED = '/(my|rated):[ ]{0,1}([0-5][\.]*[0-9]*)(x[0-9]{0,2})*[\-]*([0-5]*[\.]*[0-9]*)[ ]*/i';
    const SEARCH_SINCE = '/since:[ ]{0,1}([0-9]+)/i';
    const SEARCH_ARTIST = "/artist:[ ]{0,1}([\w]+|[\"'‘“‹«](.*?)[\"'’”›»])[ ]*/i";
    const SEARCH_ALBUM = "/album:[ ]{0,1}([\w]+|[\"'‘“‹«](.*?)[\"'’”›»])[ ]*/i";
    const SEARCH_SONG = "/song:[ ]{0,1}([\w]+|[\"'‘“‹«](.*?)[\"'’”›»])[ ]*/i";
    const SEARCH_GENRE = "/genre:[ ]{0,1}([\w]+|[\"'‘“‹«](.*?)[\"'’”›»])[ ]*/i";



    public $responseType = 'songs';
    public $for = '';
    public $userID = 0;
    public $artist = null;
    public $album = null;
    public $country = null;
    public $title = null;
    public $genre = null;
    public $year = null;
    public $rated = [0, 5];
    public $rating = [0, 5];
    public $since = null;
    public $albums = null;
    public $search = '';

    /**
     * Search constructor.
     * @param string|null $artist
     * @param string|null $album
     * @param string|null $title
     */
    public function __construct(?string $artist = null, ?string $album = null, ?string $title = null)
    {
        global $user;
        $this->userID = $user->data['user_id'];
        if (empty($artist) && empty($album) && empty($title)) {
            $this->artist = '^';
            $this->for = self::SEARCH_FOR_ARTIST;
        } else {
            if (!empty($artist)) {
                $this->artist = '^' . $artist . '$';
                $this->for = self::SEARCH_FOR_ARTIST;
            }
            if (!empty($album)) {
                $this->album = '^' . $album . '$';
                $this->for = self::SEARCH_FOR_ALBUM;
            }
            if (!empty($title)) {
                $this->$title = $title;
                $this->for = self::SEARCH_FOR_SONG;
            }
        }
        $this->responseType = $this->getResponseType();
    }

    public function parseQueryString()
    {
        $this->for = filter_input(INPUT_GET, 'for', FILTER_SANITIZE_STRING) ?? $this->for;
        $this->artist = filter_input(INPUT_GET, 'artist', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $this->album = filter_input(INPUT_GET, 'album', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $this->country = filter_input(INPUT_GET, 'country', FILTER_SANITIZE_STRING);
        if (stripos($this->country, ',') !== false) {
            $countries = preg_split("/,\s*/", $this->country);
            $this->country = "(" . implode("|", $countries) . ")";
        }
        $this->title = filter_input(INPUT_GET, 'title', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $this->genre = filter_input(INPUT_GET, 'genre', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $this->year = filter_input(INPUT_GET, 'year', FILTER_SANITIZE_STRING);
        $rated = filter_input(INPUT_GET, 'rated', FILTER_SANITIZE_STRING);
        $this->rated = splitSearchRating($rated ?? '');
        $rating = filter_input(INPUT_GET, 'rating', FILTER_SANITIZE_STRING);
        $this->rating = splitSearchRating($rating ?? '');
        $this->since = filter_input(INPUT_GET, 'since', FILTER_SANITIZE_STRING);

        $this->search = filter_input(INPUT_GET, 'search', FILTER_UNSAFE_RAW);
        if ($this->search) {
            $matches = null;
            if (!$this->artist && preg_match(self::SEARCH_ARTIST, $this->search, $matches) === 1) {
                trigger_error(json_encode($matches));
                $this->artist = !empty($matches[2]) ? $matches[2] : $matches[1];
                $this->search = preg_replace(self::SEARCH_ARTIST, '', $this->search);
            }
            if (!$this->album && preg_match(self::SEARCH_ALBUM, $this->search, $matches) === 1) {
                trigger_error(json_encode($matches));
                $this->album = !empty($matches[2]) ? $matches[2] : $matches[1];
                $this->search = preg_replace(self::SEARCH_ALBUM, '', $this->search);
            }
            if (!$this->title && preg_match(self::SEARCH_SONG, $this->search, $matches) === 1) {
                $this->title = !empty($matches[2]) ? $matches[2] : $matches[1];
                $this->search = preg_replace(self::SEARCH_SONG, '', $this->search);
            }

            if (!$this->year && preg_match(self::SEARCH_YEAR, $this->search, $matches) === 1) {
                $this->year = $matches[1];
                $this->search = preg_replace(self::SEARCH_YEAR, '', $this->search);
            }
            if (!$this->country && preg_match(self::SEARCH_COUNTRY, $this->search, $matches) === 1) {
                $this->country = strtoupper($matches[1]);
                $this->search = preg_replace(self::SEARCH_COUNTRY, '', $this->search);
            }
            if (preg_match(self::SEARCH_RATED, $this->search, $matches) === 1) {
                if ($matches[2] && $matches[4]) {
                    $this->rated = [(float) $matches[2], (float) $matches[4]];
                } else {
                    $this->rated = splitSearchRating($matches[2]);
                }
                if ($matches[3]) {
                    $this->since = (int) str_replace('x', '', $matches[3]);
                }
                $this->search = preg_replace(self::SEARCH_RATED, '', $this->search);
            }
            if (preg_match(self::SEARCH_RATING, $this->search, $matches) === 1) {
                $this->rating = splitSearchRating($matches[1]);
                $this->search = preg_replace(self::SEARCH_RATING, '', $this->search);
            }
            if (!$this->genre && preg_match(self::SEARCH_GENRE, $this->search, $matches) === 1) {
                $this->genre = !empty($matches[2]) ? $matches[2] : $matches[1];
                $this->search = preg_replace(self::SEARCH_GENRE, '', $this->search);
            }
            if (!$this->since && preg_match(self::SEARCH_SINCE, $this->search, $matches) === 1) {
                $this->since = $matches[1];
                $this->search = preg_replace(self::SEARCH_SINCE, '', $this->search);
            }
            if (!empty($this->search)) {
                switch ($this->for) {
                    case 'songs':
                        if (!$this->title) {
                            $this->title = self::maybeStartsWith($this->search);
                        }
                        break;
                    case 'albums':
                        if (!$this->album) {
                            $this->album = self::maybeStartsWith($this->search);
                        }
                        break;
                    case 'artists':
                        if (!$this->artist) {
                            $this->artist = self::maybeStartsWith($this->search);
                        }
                        break;
                }
            }
        }
        trigger_error($this->artist);
        $this->responseType = $this->getResponseType();
    }

    public static function maybeStartsWith(string $str):string {
        if (preg_match('/[\^\$\[\]]/', $str)) {
            return $str;
        }
        if (preg_match('/[%_]/', $str)) {
            return '^' . str_replace(['%', '_'], ['.*', '.'], $str) . '$';
        }
        return '[[:<:]]'. $str;
    }

    public function setResponseType()
    {
        $this->responseType = $this->getResponseType();
    }

    public function getResponseType(): string
    {
        switch (strtolower($this->for ?? self::SEARCH_FOR_SONG)) {
            case self::SEARCH_FOR_ARTIST:
            case self::SEARCH_RESULT_ARTISTS:
                return self::SEARCH_RESULT_ARTISTS;
            case self::SEARCH_FOR_ALBUM:
            case self::SEARCH_RESULT_ALBUMS:
                return self::SEARCH_RESULT_ALBUMS;
            default:
                return self::SEARCH_RESULT_SONGS;
        }
    }

    private function getSQL(): string
    {
        $this->setResponseType();
        switch ($this->responseType) {
            case 'artists':
                return file_get_contents(__DIR__ . "/sql/search-artists.sql");
            case 'albums':
                return file_get_contents(__DIR__ . '/sql/search-albums.sql');
            default:
                return file_get_contents(__DIR__ . '/sql/search-songs.sql');
        }
    }

    /**
     * @return SearchResult[]
     * @throws Exception
     */
    public function getResults(): array
    {
        $sql = $this->getSQL();
        $pdo = SamPDO::singleton();
        if (!$pdo) {
            throw new Exception('Unable to connect to database');
        }
        $ps = $pdo->prepare($sql);
        $ps->bindValue('userID', $this->userID, PDO::PARAM_INT);
        $ps->bindValue('artist', $this->artist);
        $ps->bindValue('country', $this->country);
        $ps->bindValue('album', $this->album);
        $ps->bindValue('title', $this->title);
        $ps->bindValue('genre', $this->genre);
        $ps->bindValue('year', $this->year);
        $ps->bindValue('minAvgRating', $this->rating[0], PDO::PARAM_STR);
        $ps->bindValue('maxAvgRating', $this->rating[1], PDO::PARAM_STR);
        $ps->bindValue('minUserRating', $this->rated[0], PDO::PARAM_STR);
        $ps->bindValue('maxUserRating', $this->rated[1], PDO::PARAM_STR);
        $ps->bindValue('since', $this->since, PDO::PARAM_INT);
        $results = [];
        if (!$ps->execute()) {
            trigger_error($ps->errorInfo()[2]);
            throw new Exception($ps->errorInfo()[2]);
        }
        while ($row = $ps->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new SearchResult($row);
        }
        $ps->closeCursor();
        return $results;
    }
}
