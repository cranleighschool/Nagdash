<?php

namespace CranleighSchool\NagDash;

class NagiosApi extends AbstractNagiosConnection
{
    public function __construct($hostname, $port = 6315, $protocol = 'https',
        $url = null)
    {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->protocol = $protocol;
        $this->url = empty($url) ? '/state' : $url;
    }

    public function getState(): array
    {
        return NagdashHelpers::fetch_json($this->hostname, $this->port,
            $this->protocol, $this->url);
    }

    public function getColumnMapping(): array
    {
        return [
            'state' => 'current_state',
            'ack' => 'problem_has_been_acknowledged',
            'max_attempts' => 'max_attempts',
            'service_name' => 'service_name',
            'host_name' => 'name',
        ];
    }

    protected function buildActionUrl(string $method): string
    {
        return "{$this->protocol}://{$this->hostname}:{$this->port}/{$method}";
    }
}
