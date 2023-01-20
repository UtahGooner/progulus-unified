<?php
namespace progulusAPI;

use \Exception;
use \PDO;

require_once 'SamPDO.php';
require_once 'autoload.inc.php';
require_once 'json_utils.php';


global $user;
$userID = $user->data['user_id'];

$response = [];
$response['user'] = $userID;
$response['history'] = [];

try {
    $start = filter_input(INPUT_GET,'start',  FILTER_VALIDATE_INT);
    $limit = filter_input(INPUT_GET,'limit',  FILTER_VALIDATE_INT);
    $me = filter_input(INPUT_GET, 'me', FILTER_VALIDATE_BOOLEAN);

    if (!$start) {
        $start = 0;
    }
    if (!$limit) {
        $limit = 25;
    }
    $response['me'] = $me;
    $response['start'] = $start;
    $sql = file_get_contents('sql/recent.sql', true);
    $pdo = SamPDO::singleton();
    $ps = $pdo->prepare($sql);
    $ps->bindValue(':start', $start, PDO::PARAM_INT);
    $ps->bindValue(':limit', $limit, PDO::PARAM_INT);
    $ps->bindValue(':me', $me === true ? $userID : 0, PDO::PARAM_INT);
    if (!$ps->execute()) {
        trigger_error($ps->errorInfo()[2]);
        throw new Exception($ps->errorInfo()[2]);
    }
    while ($row = $ps->fetch(PDO::FETCH_ASSOC)) {
        $response['history'][] = $row;
    }
} catch (\Exception $ex) {
    $response['error'] = $ex->getMessage();
}

json_send($response, JSON_PRETTY_PRINT);

