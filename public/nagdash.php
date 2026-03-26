<?php

error_reporting(E_ALL);
require_once '../src/bootstrap.php';

if (! function_exists('curl_init')) {
    exit('ERROR: The PHP curl extension must be installed for Nagdash to function');
}

$controller = new \CranleighSchool\NagDash\Controllers\DashboardController([
    'nagios_hosts' => $nagios_hosts,
    'api_type' => $api_type,
    'filter' => $filter ?? '',
    'sort_by_time' => $sort_by_time ?? false,
    'enable_blinking' => $enable_blinking ?? false,
    'mock_state_file' => $mock_state_file ?? null,
]);

echo nagdash_twig()->render('dashboard.twig', $controller->getData());

