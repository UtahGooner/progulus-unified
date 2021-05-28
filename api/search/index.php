<?php


require_once 'SamPDO.php';
require_once 'autoload.inc.php';
require_once 'json_utils.php';
require_once './rating_utils.php';
require_once './search_utils.php';
require_once 'phpbb3.userdata.php';
require_once 'Search.php';

$response = [];
try {
    $search = new Search();
    $response['search'] = $search;
    $response[$search->responseType] = $search->getResults();
} catch (\Exception $ex) {
    $response['error'] = $ex->getMessage();
}

json_send($response);
