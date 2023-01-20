<?php
global $user;

$headers = apache_request_headers();
if (isset($headers['Authorization'])) {
    $auth = explode(' ',  $headers['Authorization']);
    if (count($auth) === 2 && $auth[0] === 'Basic') {
        $userAuth = base64_decode($auth[1]);
        if ($user->data['user_id'] === 1 && $_ENV['DEV_AUTH_STEVE'] === $userAuth) {
            $user->data['user_id'] = 24;
            $user->data['username'] = 'SteveM';
            $user->data['user_avatar'] = "24.jpg";
        }
    }
}
