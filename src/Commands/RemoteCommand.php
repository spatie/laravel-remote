<?php

namespace Spatie\Remote\Commands;

use Illuminate\Console\Command;

class RemoteCommand extends Command
{
    public $signature = 'laravel-remote';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
