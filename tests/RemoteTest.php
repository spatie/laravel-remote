<?php

namespace Spatie\Remote\Tests;

use Illuminate\Support\Facades\Artisan;
use Spatie\Remote\Exceptions\CouldNotExecuteCommand;
use Spatie\Snapshots\MatchesSnapshots;

class RemoteTest extends TestCase
{
    use MatchesSnapshots;

    public function setUp(): void
    {
        parent::setUp();

        config()->set('remote.hosts.default', [
            'host' => 'example.com',
            'port' => 22,
            'user' => 'user',
            'path' => '/home/forge/test-path',
        ]);
    }

    /** @test */
    public function it_can_execute_a_remote_command()
    {
        Artisan::call('remote test --debug');

        $this->assertMatchesSnapshot(Artisan::output());
    }

    /** @test */
    public function it_can_execute_a_raw_command()
    {
        Artisan::call('remote test --debug --raw');

        $this->assertMatchesSnapshot(Artisan::output());
    }

    /** @test */
    public function it_will_throw_an_exception_if_a_host_does_not_exist()
    {
        config()->set('remote.hosts', []);

        $this->expectException(CouldNotExecuteCommand::class);
        $this->expectExceptionMessage("Could not find a host named `default` in the config file");

        Artisan::call('remote test --debug');
    }

    /** @test */
    public function it_will_throw_an_exception_if_a_required_property_of_a_host_is_not_set()
    {
        config()->set('remote.hosts.default.port', null);

        $this->expectException(CouldNotExecuteCommand::class);
        $this->expectExceptionMessage("The required config value `port` is not set for host `default`");

        Artisan::call('remote test --debug');
    }
}
