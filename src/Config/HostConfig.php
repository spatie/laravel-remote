<?php

namespace Spatie\Remote\Config;

class HostConfig
{
    public function __construct(
        public string $host,
        public int $port,
        public string $user,
        public string $path,
    ) {
    }
}
