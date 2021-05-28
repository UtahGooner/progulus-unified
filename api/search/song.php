<?php

require_once 'SamPDO.php';
require_once 'autoload.inc.php';
require_once 'json_utils.php';
require_once './rating_utils.php';
require_once './search_utils.php';
require_once 'phpbb3.userdata.php';

use progulusAPI\SamPDO;

global $user;
$userID = $user->data['user_id'];

$artist = filter_input(INPUT_GET, 'artist', FILTER_SANITIZE_STRING);
$album = filter_input(INPUT_GET, 'album', FILTER_SANITIZE_STRING);
$country = filter_input(INPUT_GET, 'country', FILTER_SANITIZE_STRING);
$title = filter_input(INPUT_GET, 'title', FILTER_SANITIZE_STRING);
$genre = filter_input(INPUT_GET, 'genre', FILTER_SANITIZE_STRING);
$year = filter_input(INPUT_GET, 'year', FILTER_SANITIZE_STRING);
$rated = filter_input(INPUT_GET, 'rated', FILTER_SANITIZE_STRING);
$rating = filter_input(INPUT_GET, 'rating', FILTER_SANITIZE_STRING);
$since = filter_input(INPUT_GET, 'since', FILTER_SANITIZE_STRING);

$rated = splitSearchRating($rated ?? '');
$rating = splitSearchRating($rating ?? '');

$response = [];
$response['userID'] = $user->data['user_id'];
$response['rated'] = $rated;
$response['rating'] = $rating;
$response['search'] = $_GET;

$query = "
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


try {
    $pdo = SamPDO::singleton();
    if (!$pdo) {
        throw new Exception('Unable to connect to database');
    }
    $ps = $pdo->prepare($query);
    $ps->bindValue('userID', $userID, PDO::PARAM_INT);
    $ps->bindValue('artist', $artist);
    $ps->bindValue('country', $country);
    $ps->bindValue('album', $album);
    $ps->bindValue('title', $title);
    $ps->bindValue('genre', $genre);
    $ps->bindValue('year', $year);
    $ps->bindValue('minAvgRating', $rating[0], PDO::PARAM_STR);
    $ps->bindValue('maxAvgRating', $rating[1], PDO::PARAM_STR);
    $ps->bindValue('minUserRating', $rated[0], PDO::PARAM_STR);
    $ps->bindValue('maxUserRating', $rated[1], PDO::PARAM_STR);
    $ps->bindValue('since', $since, PDO::PARAM_INT);
    $ps->execute();
    $rows = $ps->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $index => $row) {
        $rows[$index] = convert_search_result_values($row);
    }
    $response['artists'] = $rows;
} catch (\Exception $ex) {
    $response['error'] = $ex->getMessage();
}


json_send($response);

