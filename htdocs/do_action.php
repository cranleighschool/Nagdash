<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once '../src/bootstrap.php';

$controller = new \Nagdash\Controllers\ActionController($nagios_hosts, $api_type);
echo $controller->handle();
