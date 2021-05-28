<?php

use progulusAPI\SamPDO;

require_once 'SamPDO.php';
require_once 'autoload.inc.php';
require_once 'json_utils.php';

$queryQueue = "
    SELECT s.id,
           s.duration,
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
           NULL                       AS date_played,
           IFNULL(rh.username, 'HAL') AS requester,
           IFNULL(rl.name, '')        AS msgname,
           IFNULL(rl.msg, '')         AS msg,
           NULL                       AS sinceStart
    FROM queuelist q
         INNER JOIN songlist s
                    ON s.id = q.songID
         LEFT JOIN  request_history rh
                    ON rh.requestID = q.requestID
                        AND rh.songID = q.songID
         LEFT JOIN  requestlist rl
                    ON rl.ID = q.requestID
                        AND rl.songID = q.songID
    ORDER BY sortID
";

$response = [];
try {
    $pdo = SamPDO::singleton();
    if (!$pdo) {
        throw new Exception('Unable to connect to database');
    }
    $ps = $pdo->prepare($queryQueue);
    $ps->execute();
    $rows = $ps->fetchAll(PDO::FETCH_ASSOC);
    $response['queue'] = $rows;
} catch (\Exception $ex) {
    $response['error'] = $ex->getMessage();
}
json_send($response);
