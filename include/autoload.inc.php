<?php
namespace progulusAPI;
require_once 'autoload.php';

use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$docRoot = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_UNSAFE_RAW);

ini_set('include_path',
    ini_get('include_path')
    . PATH_SEPARATOR . "{$docRoot}"
    . PATH_SEPARATOR . "{$docRoot}/api"
    . PATH_SEPARATOR . "{$docRoot}/phpBB3"
);

require_once 'sam-config.php';
