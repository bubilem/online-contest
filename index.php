<?php
error_reporting(E_ALL);
mb_internal_encoding("UTF-8");
session_start();
require_once('app/utils/Loader.php');
spl_autoload_register('Loader::loadClass');
PageController::create()->run();
