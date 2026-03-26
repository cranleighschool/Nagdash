<?php

namespace Nagdash\Controllers;

class ActionController
{
    private array $nagios_hosts;

    private string $api_type;

    public function __construct(array $nagios_hosts, string $api_type)
    {
        $this->nagios_hosts = $nagios_hosts;
        $this->api_type = $api_type;
    }

    public function handle(): string
    {
        $supported_methods = ['ack', 'downtime', 'enable', 'disable'];

        if (! isset($_SESSION['csrf_token'])
            || ! isset($_POST['csrf_token'])
            || ! hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            return 'Invalid or missing CSRF token.';
        }

        if (! isset($_POST['nag_host'])) {
            return 'Are you calling this manually? This should be called by Nagdash only.';
        }

        $nagios_instance = $_POST['nag_host'];
        $action = $_POST['action'];

        if (! in_array($action, $supported_methods)) {
            return "Nagios-api does not support this action ({$action}) yet.";
        }

        $details = [
            'host' => $_POST['hostname'],
            'service' => $_POST['service'] ?: null,
            'author' => function_exists('nagdash_get_user') ? nagdash_get_user() : 'Nagdash',
            'duration' => ! empty($_POST['duration']) ? ((int) $_POST['duration'] * 60) : null,
            'comment' => "{$action} from Nagdash",
        ];

        $nagios_api = null;
        foreach ($this->nagios_hosts as $host) {
            if ($host['tag'] === $nagios_instance) {
                $nagios_api = \NagdashHelpers::get_nagios_api_object(
                    $this->api_type,
                    $host['hostname'],
                    $host['port'],
                    $host['protocol'],
                    $host['url'] ?? null
                );
                break;
            }
        }

        if ($nagios_api === null) {
            return "Unknown Nagios instance: {$nagios_instance}";
        }

        switch ($action) {
            case 'ack':
                $ret = $nagios_api->acknowledge($details);
                break;
            case 'downtime':
                $ret = $nagios_api->setDowntime($details);
                break;
            case 'enable':
                $ret = $nagios_api->enableNotifications($details);
                break;
            case 'disable':
                $ret = $nagios_api->disableNotifications($details);
                break;
        }

        return $ret['details'];
    }
}
