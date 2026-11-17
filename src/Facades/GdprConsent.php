<?php

namespace JeffersonGoncalves\GdprConsent\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JeffersonGoncalves\GdprConsent\GdprConsent
 */
class GdprConsent extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-gdpr-consent';
    }
}
