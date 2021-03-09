<?php


namespace Spatie\Remote\Config;

class RemoteConfig
{
    public static function getHost($hostName): HostConfig
    {
        $configValues = config("remote.hosts.{$hostName}");

        return new HostConfig(
            $configValues['host'],
            $configValues['port'],
            $configValues['user'],
            $configValues['path'],
        );
    }
}
