<?php

require_once 'SamPDO.php';
require_once 'autoload.inc.php';
require_once 'json_utils.php';
require_once 'CurrentSong.php';

global $user;
$userID = $user->data['user_id'];

if (!$userID || $userID === 1) {
    header('Location: /rprweb/');
    exit;
}
phpinfo();
