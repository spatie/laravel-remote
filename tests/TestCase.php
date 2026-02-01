<?php

namespace Spatie\Remote\Tests;

use Illuminate\Testing\TestResponse;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Remote\RemoteServiceProvider;

class TestCase extends Orchestra
{
    protected static ?TestResponse $latestResponse = null;

    protected function getPackageProviders($app): array
    {
        return [
            RemoteServiceProvider::class,
        ];
    }
}
