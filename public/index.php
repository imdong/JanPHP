<?php
ini_set('display_errors', true);

use Framework\Core;

define('WEB_ROOT_PATH', __DIR__);

require_once '../vendor/autoload.php';

Core::start($_SERVER['REQUEST_URI']);
