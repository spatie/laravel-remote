<?php

namespace Spatie\Remote\Exceptions;

use Exception;

class CouldNotExecuteCommand extends Exception
{
    public static function hostNotFoundInConfig(string $hostName): self
    {
        return new static("Could not find a host named `{$hostName}` in the config file");
    }

    public static function requiredConfigValueNotSet(string $hostName, string $configValue): self
    {
        return new static("The required config value `{$configValue}` is not set for host `{$hostName}`");
    }
}
