<?php

namespace CranleighSchool\NagDash;

interface NagiosConnection
{
    /**
     * get the current state of the nagios instance
     *
     * Returns an array of the form
     *
     * @return array{errors: bool, details: string}
     */
    public function getState(): array;

    /**
     * acknowledge a problem
     *
     * Parameter
     *  $details - array with problem meta data like
     *             [
     *              "host" => $host,
     *              "service" => $service,
     *              "comment" => $comment,
     *              "author" => $author,
     *              "duration" => $duration
     *              ]
     *
     * @param  array{host: string, service: string, comment: string, author: string, duration: int}  $details
     * @return array{errors: bool, details: string}
     */
    public function acknowledge(array $details): array;

    /**
     * enable notifications for a host/service
     *
     * Parameter
     *  $target - host or service to enable notifications for
     *
     * Returns an array of the form
     *  ["errors" => true/false, "details" => "message"]
     */
    public function enableNotifications(array $target): array;

    /**
     * disable notifications for a host/service
     *
     * Parameter
     *  $target - host or service to disable notifications for
     *
     * Returns an array of the form
     *  ["errors" => true/false, "details" => "message"]
     */
    public function disableNotifications(array $target): array;

    /**
     * set downtime for a host or service
     *
     * Parameter:
     *  $target - host or service to set downtime fork
     *
     * Returns an array of the form
     *  ["errors" => true/false, "details" => "message"]
     */
    public function setDowntime(array $target): array;
}
