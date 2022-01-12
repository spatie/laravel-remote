<?php

namespace Spatie\Remote\Commands;

use Illuminate\Console\Command;
use Spatie\Remote\Config\HostConfig;
use Spatie\Remote\Config\RemoteConfig;
use Spatie\Ssh\Ssh;
use Symfony\Component\Process\Process;

class RemoteCommand extends Command
{
    public $signature = 'remote {rawCommand} {--host=} {--raw} {--debug} {--script}';

    public $description = 'Execute commands on a remote server';

    public function handle()
    {
        $hostConfigName = $this->option('host') ?? config('remote.default_host');

        $hostConfig = RemoteConfig::getHost($hostConfigName);

        $ssh = Ssh::create($hostConfig->user, $hostConfig->host)
            ->onOutput(function ($type, $line) {
                $this->displayOutput($type, $line);
            })
            ->usePort($hostConfig->port);

        $commandsToExecute = $this->getCommandsToExecute($hostConfig);

        if ($this->option('debug')) {
            $this->line($ssh->getExecuteCommand($commandsToExecute));

            return 0;
        }

        $process = $ssh->execute($commandsToExecute);

        return $process->getExitCode();
    }

    protected function getCommandsToExecute(HostConfig $hostConfig): array
    {
        $command = $this->argument('rawCommand');

        if (! $this->option('raw') && !$this->option('script')) {
            $command = "php artisan {$command}";
        }

        if($this->option('script')){
            $command = file_get_contents($command);
        }

        
        return [
            "cd {$hostConfig->path}",
            $command,
        ];
    }

    protected function displayOutput($type, $line): void
    {
        $lines = explode("\n", $line);

        foreach ($lines as $line) {
            if (strlen(trim($line)) === 0) {
                continue;
            }

            if ($type == Process::OUT) {
                $this->output->write(trim($line) . PHP_EOL);

                continue;
            }

            $this->output->write('<fg=red>' . trim($line) . '</>' . PHP_EOL);
        }
    }
}
