<?php

namespace CranleighSchool\NagDash\Controllers;

use CranleighSchool\NagDash\NagdashHelpers;

class DashboardController
{
    private array $nagios_hosts;

    private string $api_type;

    private string $filter;

    private bool $sort_by_time;

    private bool $enable_blinking;

    private ?string $mock_state_file;

    public function __construct(array $config)
    {
        $this->nagios_hosts = $config['nagios_hosts'];
        $this->api_type = $config['api_type'];
        $this->filter = $config['filter'] ?? '';
        $this->sort_by_time = (bool) ($config['sort_by_time'] ?? false);
        $this->enable_blinking = (bool) ($config['enable_blinking'] ?? false);
        $this->mock_state_file = $config['mock_state_file'] ?? null;
    }

    public function getData(): array
    {
        $unwanted_hosts = [];
        if (array_key_exists('nagdash_unwanted_hosts', $_COOKIE)) {
            $u = unserialize($_COOKIE['nagdash_unwanted_hosts']);
            if (is_array($u)) {
                $unwanted_hosts = $u;
            }
        }

        $filter = $this->filter;
        if (! empty($_COOKIE['nagdash_hostfilter'])) {
            $candidate = $_COOKIE['nagdash_hostfilter'];
            if (@preg_match('/'.$candidate.'/', '') !== false) {
                $filter = $candidate;
            }
        }

        $filter_select_last_state_change = isset($_COOKIE['select_last_state_change'])
            ? (int) $_COOKIE['select_last_state_change']
            : 0;

        $filter_sort_by_time = isset($_COOKIE['sort_by_time'])
            ? (int) $_COOKIE['sort_by_time']
            : null;

        if ($this->mock_state_file !== null) {
            $data = json_decode(file_get_contents($this->mock_state_file), true);
            $state = $data['content'];
            $errors = [];
            $curl_stats = [];
            $api_cols = [];
        } else {
            [$state, $api_cols, $errors, $curl_stats] = NagdashHelpers::get_nagios_host_data(
                $this->nagios_hosts, $unwanted_hosts, $this->api_type
            );
        }

        NagdashHelpers::deep_ksort($state);

        [$host_summary, $service_summary, $down_hosts, $known_hosts, $known_services, $broken_services]
            = NagdashHelpers::parse_nagios_host_data(
                $state, $filter, $api_cols, $filter_select_last_state_change
            );

        if (($filter_sort_by_time === 1) || $this->sort_by_time) {
            usort($broken_services, [NagdashHelpers::class, 'cmp_last_state_change']);
            usort($known_services, [NagdashHelpers::class, 'cmp_last_state_change']);
        }

        $host_count = count($this->nagios_hosts);
        foreach ($down_hosts as &$host) {
            $host['tag_html'] = NagdashHelpers::print_tag($host['tag'], $host_count);
        }
        foreach ($known_hosts as &$host) {
            $host['tag_html'] = NagdashHelpers::print_tag($host['tag'], $host_count);
        }
        foreach ($broken_services as &$service) {
            $service['tag_html'] = NagdashHelpers::print_tag($service['tag'], $host_count);
        }
        foreach ($known_services as &$service) {
            $service['tag_html'] = NagdashHelpers::print_tag($service['tag'], $host_count);
        }

        return [
            'errors' => $errors,
            'curl_stats' => $curl_stats,
            'host_summary' => $host_summary,
            'service_summary' => $service_summary,
            'down_hosts' => $down_hosts,
            'known_hosts' => $known_hosts,
            'broken_services' => $broken_services,
            'known_services' => $known_services,
            'nagios_host_status' => [0 => 'UP', 1 => 'DOWN', 2 => 'UNREACHABLE'],
            'nagios_service_status' => [0 => 'OK', 1 => 'WARNING', 2 => 'CRITICAL', 3 => 'UNKNOWN'],
            'nagios_host_status_colour' => [0 => 'status_green', 1 => 'status_red', 2 => 'status_yellow'],
            'nagios_service_status_colour' => [0 => 'status_green', 1 => 'status_yellow', 2 => 'status_red', 3 => 'status_grey'],
            'enable_blinking' => $this->enable_blinking,
            'timespans' => [
                '10 minutes' => 10,
                '30 minutes' => 30,
                '60 minutes' => 60,
                '2 hours' => 120,
                '12 hours' => 720,
                '1 day' => 1440,
                '7 days' => 10080,
            ],
        ];
    }
}
