<?php
if (!defined('PHPBB_ROOT_PATH')) {
    define('PHPBB_ROOT_PATH', '../phpBB3/');
}

require_once(PHPBB_ROOT_PATH . 'progulus_user_data.php');
restore_error_handler();
set_error_handler(null);
ini_set('log_errors', true);
ini_set('error_log', '~/progulus.com/rprweb/logs/php-error.log');
ini_set('display_errors', '0');
error_reporting(E_ALL);

