<?php


namespace Spatie\Remote\Config;

use Spatie\Remote\Exceptions\CouldNotExecuteCommand;

class RemoteConfig
{
    public static function getHost($hostName): HostConfig
    {
        $configValues = config("remote.hosts.{$hostName}");

        if (is_null($configValues)) {
            throw CouldNotExecuteCommand::hostNotFoundInConfig($hostName);
        }

        foreach (['host', 'port', 'user', 'path'] as $valueName) {
            if (is_null($configValues[$valueName])) {
                throw CouldNotExecuteCommand::requiredConfigValueNotSet($valueName, $valueName);
            }
        }

        return new HostConfig(
            $configValues['host'],
            $configValues['port'],
            $configValues['user'],
            $configValues['path'],
        );
    }
}
