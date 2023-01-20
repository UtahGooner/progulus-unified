<?php

require_once 'autoload.inc.php';
require_once 'json_utils.php';

global $user;
$response = [];

global $template;
if (function_exists('add_form_key')) {
    add_form_key('login', '_LOGIN');
    $response['token'] = $template->retrieve_var('S_FORM_TOKEN_LOGIN');
}

echo $response['token'];
