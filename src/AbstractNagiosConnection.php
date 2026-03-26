<?php

namespace CranleighSchool\NagDash;

abstract class AbstractNagiosConnection implements NagiosConnection
{
    public string $hostname;
    public int $port;
    public string $protocol;
    public string $url;

    abstract protected function buildActionUrl(string $method): string;

    public function acknowledge(array $details): array
    {
        return $this->post_to_api('acknowledge_problem', $details);
    }

    public function enableNotifications(array $target): array
    {
        return $this->post_to_api('enable_notifications', $target);
    }

    public function disableNotifications(array $target): array
    {
        return $this->post_to_api('disable_notifications', $target);
    }

    public function setDowntime(array $target): array
    {
        return $this->post_to_api('schedule_downtime', $target);
    }

    /**
     * send an action to the api
     *
     * Parameters:
     *  $method - endpoint to POST to
     *  $details - details about hostname, service, etc
     *
     * Returns ["errors" => true/false, "details" => "details"]
     */
    public function post_to_api(string $method, array $details): array
    {
        $payload = json_encode($details);
        $params = ['http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/json',
            'content' => $payload,
        ],
        ];
        $service = $details['service'];
        $hostname = $details['host'];
        $context = stream_context_create($params);
        $nagios_url = $this->buildActionUrl($method);
        if (! $result = file_get_contents($nagios_url, false, $context)) {
            $error = error_get_last();

            return ['errors' => true,
                'details' => "Command {$method} failed! <pre>{$error}</pre>"];
        } else {
            $return = json_decode($result);
            if ($return->success) {
                $service = (isset($service)) ? "-> {$service}" : null;

                return ['errors' => true,
                    'details' => "Command {$method} succeeded on {$hostname} {$service}"];
            } else {
                return ['errors' => true,
                    'details' => "Command {$method} failed! <pre>{$return->content}</pre>"];
            }
        }
    }
}
