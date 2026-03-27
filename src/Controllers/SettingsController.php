<?php

namespace CranleighSchool\NagDash\Controllers;

use CranleighSchool\NagDash\NagdashHelpers;

class SettingsController
{
    public function handle(): void
    {
        if (! isset($_SERVER['HTTP_REFERER'])) {
            echo 'Woah, what did you just try and do?';

            return;
        }

        $return_path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
        $hosts = array_column(NagdashHelpers::config('nagios_hosts', []), 'tag');

        $hostfilter = $_POST['hostfilter'] ?? '';
        unset($_POST['hostfilter']);
        setcookie('nagdash_hostfilter', $hostfilter, time() + 60 * 60 * 24 * 365);

        $select_last_state_change = $_POST['select_last_state_change'] ?: '0';
        setcookie('select_last_state_change', $select_last_state_change, time() + 60 * 60 * 24 * 365);

        setcookie('sort_by_time', isset($_POST['sort_by_time']) ? '1' : '0', time() + 60 * 60 * 24 * 365);
        setcookie('sort_descending', isset($_POST['sort_descending']) ? '1' : '0', time() + 60 * 60 * 24 * 365);

        $submitted_hosts = $_POST;
        $unwanted_hosts = array_diff($hosts, array_keys($submitted_hosts));
        setcookie('nagdash_unwanted_hosts', serialize($unwanted_hosts), time() + 60 * 60 * 24 * 365);

        header("Location: {$return_path}");
    }
}
