<?php

namespace Spatie\Remote\Config;

use Spatie\Remote\Exceptions\CouldNotExecuteCommand;

class RemoteConfig
{
    public static function getHost(string $hostName): HostConfig
    {
        $configValues = config("remote.hosts.{$hostName}");

        if (is_null($configValues)) {
            throw CouldNotExecuteCommand::hostNotFoundInConfig($hostName);
        }

        foreach (['host', 'port', 'user', 'path'] as $configValue) {
            if (is_null($configValues[$configValue])) {
                throw CouldNotExecuteCommand::requiredConfigValueNotSet($hostName, $configValue);
            }
        }

        return new HostConfig(...$configValues);
    }
}
