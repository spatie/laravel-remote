<?php

namespace Spatie\Remote\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\Remote\Config\HostConfig;
use Spatie\Remote\Config\RemoteConfig;
use Spatie\Ssh\Ssh;
use Symfony\Component\Process\Process;

class RemoteCommand extends Command
{
    public $signature = 'remote {rawCommand} {--host=} {--tag=} {--raw} {--debug}';

    public $description = 'Execute commands on a remote server';

    public function handle()
    {
        $hostConfig = $this->getHostConfig();

        $hostConfig->each(function($host) {
            $this->processCommandOnServer($host);
        });
    }

    protected function getHostConfig()
    {
        $tagName = $this->option('tag');

        if ($tagName) {
            return $this->getHostsByTag($tagName);
        }

        $hostConfigName = $this->option('host') ?? config('remote.default_host');

        return collect([$hostConfigName => RemoteConfig::getHost(
            $this->option('host') ?? config('remote.default_host')
        )]);
    }


    protected function getHostsByTag(string $tag): Collection
    {
        return collect(config('remote.hosts'))->filter(function ($host) use ($tag) {
            return $host['tag'] === $tag;
        })->mapWithKeys(function($host, $key) {
            return [$key => RemoteConfig::getHost($key)];
        });
    }

    protected function processCommandOnServer(HostConfig $hostConfig)
    {
        $ssh = Ssh::create($hostConfig->user, $hostConfig->host)
                  ->onOutput(function ($type, $line) use ($hostConfig) {
                      $this->displayOutput($type, $line, $hostConfig->name);
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

        if (! $this->option('raw')) {
            $command = "php artisan {$command}";
        }

        return [
            "cd {$hostConfig->path}",
            $command,
        ];
    }

    protected function displayOutput($type, $line, $hostName): void
    {
        $this->output->write(trim($hostName) . ': ');

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
