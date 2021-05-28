<?php

if (!defined('PHPBB_ROOT_PATH')) {
    define('PHPBB_ROOT_PATH', '/mnt/projects/progulus/www/progulus.com/phpBB3/');
}

require_once 'phpbb3.userdata.php';

// default time zone should be set in case the server does not have it configured.
//default timezone for date() functions - required for PHP5
ini_set('date.timezone',       'America/Denver');
date_default_timezone_set(     'America/Denver');


/*
 * DEFINE THE FORUM LOGIN VALUES HERE, RATHER THAN IN THE VARIABLES
 * The values assinged here will be assiged to the variables later on
 */
if (!defined('DB_CONSTANTS_DEFINED')) {
  define('FORUM_HOST',             $_ENV['FORUM_HOST']);
  define('FORUM_USERNAME',         $_ENV['FORUM_USERNAME']);
  define('FORUM_PASSWORD',         $_ENV['FORUM_PASSWORD']);
  define('FORUM_DATABASE',         $_ENV['FORUM_DATABASE']);
  define('FORUM_DSN',              'mysql:host=' . FORUM_HOST . ';dbname=' . FORUM_DATABASE . ';');

  define('SAM_HOST',               $_ENV['SAM_HOST']);
  define('SAM_USERNAME',           $_ENV['SAM_USERNAME']);
  define('SAM_PASSWORD',           $_ENV['SAM_PASSWORD']);
  define('SAM_DATABASE',           $_ENV['SAM_DATABASE']);
  define('SAM_DSN',                'mysql:host=' . SAM_HOST . ';dbname=' . SAM_DATABASE . ';port=3306');

  define('DB_CONSTANTS_DEFINED',            true);
}
