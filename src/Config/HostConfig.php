<?php

namespace Spatie\Remote\Config;

class HostConfig
{
    public function __construct(
        public string $name,
        public string $host,
        public int $port,
        public string $user,
        public string $path,
        public ?string $tag,
    ) {
    }
}
