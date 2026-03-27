<?php

require_once __DIR__.'/../vendor/autoload.php';

\CranleighSchool\NagDash\NagdashHelpers::setConfig(require __DIR__.'/../config.php');

function nagdash_twig(): \Twig\Environment
{
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/../templates');

    return new \Twig\Environment($loader);
}
