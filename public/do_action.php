<?php

error_reporting(E_ALL ^ E_NOTICE);
session_start();
require_once '../src/bootstrap.php';

$controller = new \CranleighSchool\NagDash\Controllers\ActionController;
echo $controller->handle();
