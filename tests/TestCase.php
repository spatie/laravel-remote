<?php

namespace Spatie\Remote\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Remote\RemoteServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            RemoteServiceProvider::class,
        ];
    }
}
