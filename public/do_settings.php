<?php

require_once '../src/bootstrap.php';

$controller = new \CranleighSchool\NagDash\Controllers\SettingsController($nagios_hosts);
$controller->handle();
