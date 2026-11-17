<?php

namespace JeffersonGoncalves\GdprConsent\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JeffersonGoncalves\Gdpr\GdprServiceProvider;
use JeffersonGoncalves\GdprConsent\GdprConsentServiceProvider;
use JeffersonGoncalves\PestPluginDatabaseMigrations\LoadsPackageMigrations;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use LoadsPackageMigrations;

    protected function getPackageProviders($app): array
    {
        return [
            GdprServiceProvider::class,
            GdprConsentServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('gdpr-consent.route.enabled', true);
        $app['config']->set('gdpr-consent.route.middleware', ['web']);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadPackageMigrations(GdprConsentServiceProvider::class);

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
    }
}
