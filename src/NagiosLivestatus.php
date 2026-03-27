<?php

namespace CranleighSchool\NagDash;

class NagiosLivestatus extends AbstractNagiosConnection
{
    public function __construct(
        string $hostname,
        int $port = 6315,
        string $protocol = 'https',
        ?string $url = null
    ) {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->protocol = $protocol;
        $this->url = empty($url) ? '/livestatus-api' : $url;
    }

    public function getState(): array
    {
        $hostname = $this->hostname;
        $port = $this->port;
        $protocol = $this->protocol;
        $url = $this->url;

        $ret = NagdashHelpers::fetch_json(
            $hostname, $port, $protocol,
            $url.'/hosts?'.
            'Columns=name,state,acknowledged,last_state_change,downtimes,notifications_enabled,current_attempt,max_check_attempts,plugin_output'
        );

        if ($ret['errors'] == true) {
            return $ret;
        }
        $state = $ret['details']['content'];
        $curl_stats = $ret['curl_stats'];

        $curl_stats["$hostname:$port"]['objects'] = count($state);
        $munge = [];

        foreach ($state as $host) {
            $host['services'] = [];
            $munge[$host['name']] = $host;
        }
        $state = $munge;

        $ret = NagdashHelpers::fetch_json(
            $hostname, $port, $protocol,
            $url.'/services?'.
            'Columns=description,host_name,plugin_output,notifications_enabled,'.
            'downtimes,scheduled_downtime_depth,state,last_state_change,'.
            'current_attempt,max_check_attempts,acknowledged'
        );

        $services = $ret['details']['content'];

        foreach ($services as $service) {
            $hostname = $service['host_name'];
            if ($state[$hostname]) {
                $state[$hostname]['services'][$service['description']] = $service;
            }
        }

        return [
            'errors' => false,
            'details' => $state,
            'curl_stats' => $curl_stats,
        ];
    }

    public function getColumnMapping(): array
    {
        return [
            'state' => 'state',
            'ack' => 'acknowledged',
            'max_attempts' => 'max_check_attempts',
            'service_name' => 'description',
            'host_name' => 'host_name',
        ];
    }

    protected function buildActionUrl(string $method): string
    {
        return "{$this->protocol}://{$this->hostname}:{$this->port}/{$this->url}/{$method}";
    }
}
