<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/NagiosConnection.php';
require_once __DIR__.'/utils.php';
require_once __DIR__.'/timeago.php';
require_once __DIR__.'/NagiosApi.php';
require_once __DIR__.'/NagiosLivestatus.php';
require_once __DIR__.'/../config.php';

function nagdash_twig(): \Twig\Environment
{
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/../templates');

    return new \Twig\Environment($loader);
}
