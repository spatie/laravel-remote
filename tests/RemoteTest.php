<?php

use Illuminate\Support\Facades\Artisan;
use Spatie\Remote\Commands\RemoteCommand;
use Spatie\Remote\Exceptions\CouldNotExecuteCommand;

use function Pest\Laravel\artisan;
use function Spatie\Snapshots\assertMatchesSnapshot;

beforeEach(function () {
    RemoteCommand::resolveTerminalWidthUsing(fn () => 50);

    config()->set('remote.hosts.default', [
        'host' => 'example.com',
        'port' => 22,
        'user' => 'user',
        'path' => '/home/forge/test-path',
    ]);
});

it('can execute a remote command', function () {
    Artisan::call('remote test --debug');

    assertMatchesSnapshot(Artisan::output());
});

it('cannot execute a remote command without confirming when confirmation option is on', function () {
    config()->set('remote.needs_confirmation', true);

    $host = config('remote.hosts.default.host');

    artisan('remote test --debug')
        ->expectsConfirmation("Are you sure you want to execute this command on the following remote server {$host}?", 'no')
        ->expectsOutput('Remote command aborted')
        ->assertExitCode(0);
});

it('can execute a remote command with confirming when confirmation option is on', function () {
    config()->set('remote.needs_confirmation', true);

    $host = config('remote.hosts.default.host');

    artisan('remote test --debug')
        ->expectsConfirmation("Are you sure you want to execute this command on the following remote server {$host}?", 'yes')
        ->doesntExpectOutput("Remote command aborted");
});

it('can execute a raw command', function () {
    Artisan::call('remote test --debug --raw');

    assertMatchesSnapshot(Artisan::output());
});

it('will throw an exception if a host does not exist', function () {
    config()->set('remote.hosts', []);

    Artisan::call('remote test --debug');
})->throws(
    CouldNotExecuteCommand::class,
    "Could not find a host named `default` in the config file"
);

it('will throw an exception if a required property of a host is not set', function () {
    config()->set('remote.hosts.default.port', null);

    Artisan::call('remote test --debug');
})->throws(
    CouldNotExecuteCommand::class,
    "The required config value `port` is not set for host `default`"
);

it('can get the config with dots', function () {
    config()->set('remote.hosts', [
        'example.com' => [
            'host' => 'example.com',
            'port' => 22,
            'user' => 'user',
            'path' => '/home/forge/test-path',
        ],
    ]);

    Artisan::call('remote test --debug --host=example.com');

    assertMatchesSnapshot(Artisan::output());
});
