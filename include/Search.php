<?php

use progulusAPI\SamPDO;

$sqlArtist = "
SELECT s.artist,
       SUM(IFNULL(q.id, 0)) > 0                                         AS queued,
       MAX(s.date_played) > DATE_SUB(NOW(), INTERVAL 3 HOUR)            AS recent,
       SUM(IFNULL(s.votes, 0))                                          AS votes,
       SUM(IFNULL(s.rating, 0) * votes)                                 AS voteRatings,
       IF(SUM(IFNULL(s.votes, 0)) = 0, 0,
          SUM(IFNULL(s.rating, 0) * s.votes) / SUM(IFNULL(s.votes, 0))) AS avgRating,
       MAX(s.date_played)                                               AS dateLastPlayed,
       SUM(s.count_played)                                              AS plays,
       COUNT(s.id)                                                      AS songs,
       COUNT(DISTINCT r.songID)                                         AS userVotes,
       IF(COUNT(DISTINCT r.songID) = 0, 0,
          SUM(IFNULL(r.rating, 0)) / COUNT(DISTINCT r.songID))          AS userRating,
       GROUP_CONCAT(DISTINCT s.genre)                                   AS genre
FROM samdb.songlist s
     LEFT JOIN queuelist q
               ON q.songID = s.id
     LEFT JOIN samdb.song_rating r
               ON r.songID = s.ID AND r.userID = :userID
WHERE (ISNULL(NULLIF(:artist, '')) OR s.artist REGEXP :artist)
  AND (ISNULL(NULLIF(:country, '')) OR s.label = :country)
  AND (ISNULL(NULLIF(:album, '')) OR s.album REGEXP :album)
  AND (ISNULL(NULLIF(:title, '')) OR s.title REGEXP :title)
  AND (ISNULL(NULLIF(:genre, '')) OR s.genre REGEXP :genre)
  AND (ISNULL(NULLIF(:year, ''))
    OR IF(:year = 'new', date_added > DATE_SUB(NOW(), INTERVAL 30 DAY), s.albumyear REGEXP :year)
    )
GROUP BY artist
HAVING avgRating BETWEEN :minAvgRating AND :maxAvgRating
   AND userRating BETWEEN :minUserRating AND :maxUserRating
   AND (ISNULL(:since) OR dateLastPlayed <= DATE_SUB(NOW(), INTERVAL :since MONTH))
ORDER BY artist
";

$sqlAlbum = "
SELECT s.artist,
       s.album,
       SUM(IFNULL(q.id, 0)) > 0                                         AS queued,
       MAX(s.date_played) > DATE_SUB(NOW(), INTERVAL 3 HOUR)            AS recent,
       SUM(IFNULL(s.votes, 0))                                          AS votes,
       SUM(IFNULL(s.rating, 0) * votes)                                 AS voteRatings,
       IF(SUM(IFNULL(s.votes, 0)) = 0, 0,
          SUM(IFNULL(s.rating, 0) * s.votes) / SUM(IFNULL(s.votes, 0))) AS avgRating,
       MAX(s.date_played)                                               AS dateLastPlayed,
       SUM(s.count_played)                                              AS plays,
       COUNT(s.id)                                                      AS songs,
       COUNT(DISTINCT r.songID)                                         AS userVotes,
       IF(COUNT(DISTINCT r.songID) = 0, 0,
          SUM(IFNULL(r.rating, 0)) / COUNT(DISTINCT r.songID))          AS userRating,
       GROUP_CONCAT(DISTINCT s.genre)                                   AS genre
FROM samdb.songlist s
     LEFT JOIN queuelist q
               ON q.songID = s.id
     LEFT JOIN samdb.song_rating r
               ON r.songID = s.ID AND r.userID = :userID
WHERE (ISNULL(NULLIF(:artist, '')) OR s.artist REGEXP :artist)
  AND (ISNULL(NULLIF(:country, '')) OR s.label = :country)
  AND (ISNULL(NULLIF(:album, '')) OR s.album REGEXP :album)
  AND (ISNULL(NULLIF(:title, '')) OR s.title REGEXP :title)
  AND (ISNULL(NULLIF(:genre, '')) OR s.genre REGEXP :genre)
  AND (ISNULL(NULLIF(:year, ''))
    OR IF(:year = 'new', date_added > DATE_SUB(NOW(), INTERVAL 30 DAY), s.albumyear REGEXP :year)
    )
GROUP BY artist, album
HAVING avgRating BETWEEN :minAvgRating AND :maxAvgRating
   AND userRating BETWEEN :minUserRating AND :maxUserRating
   AND (ISNULL(:since) OR dateLastPlayed <= DATE_SUB(NOW(), INTERVAL :since MONTH))
ORDER BY artist, album
";


$sqlSong = "
SELECT s.artist,
       s.album,
       s.title,
       IFNULL(q.id, 0) > 0                              AS queued,
       s.date_played > DATE_SUB(NOW(), INTERVAL 3 HOUR) AS recent,
       IFNULL(s.votes, 0)                               AS votes,
       IFNULL(s.rating, 0) * votes                      AS voteRatings,
       IF(IFNULL(s.votes, 0) = 0, 0,
          (IFNULL(s.rating, 0) * s.votes) / s.votes)    AS avgRating,
       s.date_played                                    AS dateLastPlayed,
       s.count_played                                   AS plays,
       r.rating                                         AS userRating,
       s.genre                                          AS genre
FROM samdb.songlist s
     LEFT JOIN queuelist q
               ON q.songID = s.id
     LEFT JOIN samdb.song_rating r
               ON r.songID = s.ID AND r.userID = :userID
WHERE (ISNULL(NULLIF(:artist, '')) OR s.artist REGEXP :artist)
  AND (ISNULL(NULLIF(:country, '')) OR s.label = :country)
  AND (ISNULL(NULLIF(:album, '')) OR s.album REGEXP :album)
  AND (ISNULL(NULLIF(:title, '')) OR s.title REGEXP :title)
  AND (ISNULL(NULLIF(:genre, '')) OR s.genre REGEXP :genre)
  AND (ISNULL(NULLIF(:year, ''))
    OR IF(:year = 'new', date_added > DATE_SUB(NOW(), INTERVAL 30 DAY), s.albumyear REGEXP :year)
    )
  AND IFNULL(s.rating, 0) BETWEEN :minAvgRating AND :maxAvgRating
  AND IFNULL(r.rating, 0) BETWEEN :minUserRating AND :maxUserRating
  AND (ISNULL(:since) OR s.date_played <= DATE_SUB(NOW(), INTERVAL :since MONTH))
ORDER BY artist, album, trackno
";

class Search {
    public $responseType = 'songs';
    public $for = 'song';
    public $userID = 0;
    public $artist = '';
    public $album = '';
    public $country = '';
    public $title = '';
    public $genre = '';
    public $year = '';
    public $rated = [0,5];
    public $rating = [0,5];
    public $since = null;

    public function __construct()
    {
        global $user;
        $this->userID = $user->data['user_id'];
        $this->for = filter_input(INPUT_GET, 'for', FILTER_SANITIZE_STRING);
        $this->artist = filter_input(INPUT_GET, 'artist', FILTER_SANITIZE_STRING);
        $this->album = filter_input(INPUT_GET, 'album', FILTER_SANITIZE_STRING);
        $this->country = filter_input(INPUT_GET, 'country', FILTER_SANITIZE_STRING);
        $this->title = filter_input(INPUT_GET, 'title', FILTER_SANITIZE_STRING);
        $this->genre = filter_input(INPUT_GET, 'genre', FILTER_SANITIZE_STRING);
        $this->year = filter_input(INPUT_GET, 'year', FILTER_SANITIZE_STRING);
        $rated = filter_input(INPUT_GET, 'rated', FILTER_SANITIZE_STRING);
        $this->rated = splitSearchRating($rated ?? '');
        $rating = filter_input(INPUT_GET, 'rating', FILTER_SANITIZE_STRING);
        $this->rating = splitSearchRating($rating ?? '');
        $this->since = filter_input(INPUT_GET, 'since', FILTER_SANITIZE_STRING);
        $this->responseType = $this->getResponseType();

    }

    public function getResponseType():string {
        switch (strtolower($this->for ?? 'song')) {
            case 'artist':
            case 'artists':
                return 'artists';
            case 'album':
            case 'albums':
                return 'albums';
            default:
                return 'songs';
        }
    }

    private function getSQL():string {
        global  $sqlArtist, $sqlAlbum, $sqlSong;
        switch ($this->responseType) {
            case 'artists':
                return $sqlArtist;
            case 'albums':
                return $sqlAlbum;
            default:
                return $sqlSong;
        }
    }

    public static function convertValues ($row) {
        if (isset($row['queued'])) {
            $row['queued'] = (bool) $row['queued'];
        }
        if (isset($row['recent'])) {
            $row['recent'] = (bool) $row['recent'];
        }
        if (isset($row['votes'])) {
            $row['votes'] = (int)$row['votes'];
        }
        if (isset($row['plays'])) {
            $row['plays'] = (int)$row['plays'];
        }
        if (isset($row['songs'])) {
            $row['songs'] = (int)$row['songs'];
        }
        if (isset($row['voteRatings'])) {
            $row['voteRatings'] = (float)$row['voteRatings'];
        }
        if (isset($row['avgRating'])) {
            $row['avgRating'] = (float)$row['avgRating'];
        }
        if (isset($row['userRating'])) {
            $row['userRating'] = (float)$row['userRating'];
        }
        if (isset($row['userVotes'])) {
            $row['userVotes'] = (int)$row['userVotes'];
        }
        return $row;
    }

    /**
     * @throws Exception
     */
    public function getResults() {
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
        $ps->execute();
        if (!$ps->execute()) {
            throw new Exception($ps->errorInfo(), $ps->errorCode());
        }
        $rows = $ps->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $index => $row) {
            $rows[$index] = self::convertValues($row);
        }
        $ps->closeCursor();
        return $rows;
    }
}
