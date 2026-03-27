<?php

require_once '../src/bootstrap.php';

if (! function_exists('curl_init')) {
    exit('ERROR: The PHP curl extension must be installed for Nagdash to function');
}

$controller = new \CranleighSchool\NagDash\Controllers\DashboardController;

echo nagdash_twig()->render('dashboard.twig', $controller->getData());
