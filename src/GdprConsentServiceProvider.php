<?php

namespace JeffersonGoncalves\GdprConsent;

use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use JeffersonGoncalves\GdprConsent\Http\ConsentController;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class GdprConsentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('gdpr-consent')
            ->hasConfigFile()
            ->hasMigrations(['create_gdpr_consents_table']);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ConsentManager::class);
    }

    public function packageBooted(): void
    {
        if (! config('gdpr-consent.route.enabled', false)) {
            return;
        }

        if (config('gdpr-consent.route.claim_on_login', true)) {
            Event::listen(Login::class, function (Login $event): void {
                $id = request()->cookie((string) config('gdpr-consent.route.cookie', 'gdpr_consent_id'));

                if (is_string($id) && $id !== '' && $event->user instanceof Model) {
                    $this->app->make(ConsentManager::class)->claim($id, $event->user);
                }
            });
        }

        Route::middleware((array) config('gdpr-consent.route.middleware', ['web']))
            ->post((string) config('gdpr-consent.route.path', 'gdpr/consent'), ConsentController::class)
            ->name('gdpr-consent.store');
    }
}
