<?php

namespace progulusAPI;
use \Exception;
use \PDO;


require_once 'SamPDO.php';
require_once 'autoload.inc.php';
require_once 'json_utils.php';
require_once 'CurrentSong.php';

global $user;
$userID = $user->data['user_id'];

$response = [];
$response['queue'] = [];
try {
    $queryQueue = file_get_contents('./queue.sql');
    $pdo = SamPDO::singleton();
    if (!$pdo) {
        throw new Exception('Unable to connect to database');
    }
    $ps = $pdo->prepare($queryQueue);
    $ps->bindValue('userID', $userID, PDO::PARAM_INT);
    if (!$ps->execute()) {
        throw new Exception($ps->errorInfo()[2]);
    }

    while ($row = $ps->fetch(PDO::FETCH_ASSOC)) {
        $response['queue'][] = new CurrentSong($row);
    }

} catch (Exception $ex) {
    $response['error'] = $ex->getMessage();
}
json_send($response, JSON_PRETTY_PRINT);
