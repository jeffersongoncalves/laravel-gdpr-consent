<?php

namespace JeffersonGoncalves\GdprConsent;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class GdprConsentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-gdpr-consent')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
