<?php

use progulusAPI\SamPDO;

require_once 'SamPDO.php';
require_once 'autoload.inc.php';
require_once 'json_utils.php';

$querySongs = "SELECT s.id,
           s.duration,
           s.artist,
           s.album,
           s.title,
           s.label,
           s.albumyear,
           s.website,
           s.buycd,
           s.picture,
           s.votes,
           s.rating,
           s.label,
           h.date_played,
           IFNULL(rh.username, 'HAL')                       AS requester,
           IFNULL(rl.name, '')                              AS msgname,
           IFNULL(rl.msg, '')                               AS msg,
           UNIX_TIMESTAMP() - UNIX_TIMESTAMP(h.date_played) AS sinceStart
    FROM samdb.historylist h
         INNER JOIN samdb.songlist s
                    ON s.id = h.songID
         LEFT JOIN  samdb.request_history rh
                    ON rh.requestID = h.requestID
                        AND rh.songID = h.songID
         LEFT JOIN  samdb.requestlist rl
                    ON rl.ID = h.requestID
                        AND rl.songID = h.songID
    ORDER BY h.id DESC
    LIMIT :offset, :limit";

$limit = filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_NUMBER_INT);
if (empty($limit) || $limit > 25) {
    $limit = 10;
}
$offset = filter_input(INPUT_GET, 'offset', FILTER_SANITIZE_NUMBER_INT);
if (empty($offset)) {
    $offset = 0;
}

$response = [];
try {
    $pdo = SamPDO::singleton();
    if (!$pdo) {
        throw new Exception('Unable to connect to database');
    }
    $ps = $pdo->prepare($querySongs);
    $ps->bindValue(':offset', $offset, PDO::PARAM_INT);
    $ps->bindValue(':limit', $limit, PDO::PARAM_INT);
    $ps->execute();
    $rows = $ps->fetchAll(PDO::FETCH_ASSOC);
    $response['songs'] = $rows;
} catch (\Exception $ex) {
    $response['error'] = $ex->getMessage();
}
json_send($response);
