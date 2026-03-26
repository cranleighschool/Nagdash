<?php
require_once '../src/bootstrap.php';

$controller = new \Nagdash\Controllers\SettingsController($nagios_hosts);
$controller->handle();
