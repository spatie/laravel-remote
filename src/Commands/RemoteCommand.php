<?php

namespace Spatie\Remote\Commands;

use Illuminate\Console\Command;
use Spatie\Remote\Config\HostConfig;
use Spatie\Remote\Config\RemoteConfig;
use Spatie\Ssh\Ssh;

class RemoteCommand extends Command
{
    public $signature = 'remote {rawCommand} {--host=default} {--raw} {--debug}';

    public $description = 'Execute commands on a remote server';

    public function handle()
    {
        $hostConfig = RemoteConfig::getHost($this->option('host'));

        $ssh = Ssh::create($hostConfig->user, $hostConfig->host)
            ->onOutput(function ($type, $line) {
                echo $line;
            })
            ->usePort($hostConfig->port);

        $commandsToExecute = $this->getCommandsToExecute($hostConfig);

        if ($this->option('debug')) {
            $this->line($ssh->getExecuteCommand($commandsToExecute));

            return 0;
        }

        $process = $ssh->execute($this->getCommandsToExecute($hostConfig));

        return $process->getExitCode();
    }

    protected function getCommandsToExecute(HostConfig $hostConfig): array
    {
        $command = $this->argument('rawCommand');

        if (! $this->option('raw')) {
            $command = "php artisan {$command}";
        }

        return [
            "cd {$hostConfig->path}",
            $command,
        ];
    }
}
