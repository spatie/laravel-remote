<?php

namespace Spatie\Remote\Commands;

use Illuminate\Console\Command;
use Spatie\Remote\Config\HostConfig;
use Spatie\Remote\Config\RemoteConfig;
use Spatie\Ssh\Ssh;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Process\Process;

class RemoteCommand extends Command
{
    public $signature = 'remote {rawCommand} {--host=} {--raw} {--debug} {--jump=}';

    public $description = 'Execute commands on a remote server';

    protected static $terminalWidthResolver;

    public function handle()
    {
        $hostConfigName = $this->option('host') ?? config('remote.default_host');

        $hostConfig = RemoteConfig::getHost($hostConfigName);

        $ssh = Ssh::create($hostConfig->user, $hostConfig->host)
            ->onOutput(function ($type, $line) {
                $this->displayOutput($type, $line);
            })
            ->usePort($hostConfig->port);

        if ($hostConfig->privateKeyPath) {
            $ssh->usePrivateKey($hostConfig->privateKeyPath);
        }

        if ($jump = $this->option('jump')) {
            $ssh->useJumpHost($jump);
        }

        $commandsToExecute = $this->getCommandsToExecute($hostConfig);

        if ($this->failsConfirmationPrompt($hostConfig)) {
            return $this->failedConfirmationPromptOutput();
        }

        if ($this->option('debug')) {
            $this->line($ssh->getExecuteCommand($commandsToExecute));

            return 0;
        }

        $this->output->write("\n");
        $process = $ssh->execute($commandsToExecute);
        $this->output->write("\n");

        return $process->getExitCode();
    }

    protected function getCommandsToExecute(HostConfig $hostConfig): array
    {
        $command = $this->argument('rawCommand');

        if (! $this->option('raw')) {
            $command = "{$hostConfig->phpPath} artisan {$command} --ansi";
        }

        return [
            "export COLUMNS=". $this->getTerminalWidth(),
            "cd {$hostConfig->path}",
            $command,
        ];
    }

    protected function displayOutput($type, $line): void
    {
        $lines = explode("\n", $line);

        foreach ($lines as $index => $line) {
            if (strlen(trim($line)) === 0) {
                continue;
            }

            if ($type == Process::OUT) {
                $this->output->write($line . PHP_EOL);

                continue;
            }

            $this->output->write('<fg=red>' . trim($line) . '</>' . PHP_EOL);
        }
    }

    protected function failsConfirmationPrompt(HostConfig $hostConfig): ?bool
    {
        if (! config('remote.needs_confirmation')) {
            return false;
        }

        return ! $this->confirm(
            "Are you sure you want to execute this command on the following remote server {$hostConfig->host}?"
        );
    }

    protected function failedConfirmationPromptOutput(): int
    {
        $this->error('Remote command aborted');

        return 0;
    }

    protected function getTerminalWidth(): int
    {
        return is_null(static::$terminalWidthResolver)
            ? (new Terminal())->getWidth()
            : call_user_func(static::$terminalWidthResolver);
    }

    public static function resolveTerminalWidthUsing($resolver)
    {
        static::$terminalWidthResolver = $resolver;
    }
}
