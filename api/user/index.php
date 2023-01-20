<?php

require_once 'autoload.inc.php';
require_once 'json_utils.php';

global $user;
$response = [];
$resUser = clone $user;
unset ($resUser->data['user_password']);
unset ($resUser->data['user_newpasswd']);
unset ($resUser->data['user_passchg']);
$response['user'] = $resUser->data;
json_send($response, JSON_PRETTY_PRINT);
