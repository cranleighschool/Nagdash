<?php

error_reporting(E_ALL ^ E_NOTICE);
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../src/bootstrap.php';

$unwanted_hosts = [];
if (array_key_exists('nagdash_unwanted_hosts', $_COOKIE)) {
    $u = unserialize($_COOKIE['nagdash_unwanted_hosts']);
    if (is_array($u)) {
        $unwanted_hosts = $u;
    }
}

echo nagdash_twig()->render('layout.twig', [
    'csrf_token' => $_SESSION['csrf_token'],
    'nagios_hosts' => $nagios_hosts,
    'refresh_every_ms' => $refresh_every_ms ?? 20000,
    'show_refresh_spinner' => $show_refresh_spinner ?? false,
    'extra_css' => $extra_css ?? '',
    'unwanted_hosts' => $unwanted_hosts,
    'select_last_state_change_options' => $select_last_state_change_options ?? [],
    'sort_by_time' => $sort_by_time ?? false,
    'cookie_hostfilter' => $_COOKIE['nagdash_hostfilter'] ?? '',
    'cookie_select_last_state_change' => isset($_COOKIE['select_last_state_change']) ? (int) $_COOKIE['select_last_state_change'] : null,
    'cookie_sort_by_time' => isset($_COOKIE['sort_by_time']) ? (int) $_COOKIE['sort_by_time'] : null,
    'cookie_sort_descending' => isset($_COOKIE['sort_descending']) ? (int) $_COOKIE['sort_descending'] : null,
]);
