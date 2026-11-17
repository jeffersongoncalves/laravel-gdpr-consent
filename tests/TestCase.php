<?php

namespace JeffersonGoncalves\GdprConsent\Tests;

use JeffersonGoncalves\GdprConsent\GdprConsentServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            GdprConsentServiceProvider::class,
        ];
    }
}
