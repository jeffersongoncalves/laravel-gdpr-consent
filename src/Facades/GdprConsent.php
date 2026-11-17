<?php

namespace JeffersonGoncalves\GdprConsent\Facades;

use Illuminate\Support\Facades\Facade;
use JeffersonGoncalves\GdprConsent\ConsentManager;

/**
 * @method static \JeffersonGoncalves\GdprConsent\Models\ConsentRecord grant(\Illuminate\Database\Eloquent\Model|string $subject, string $purpose, ?string $version = null, ?string $source = null, array $metadata = [])
 * @method static \JeffersonGoncalves\GdprConsent\Models\ConsentRecord revoke(\Illuminate\Database\Eloquent\Model|string $subject, string $purpose, ?string $source = null, array $metadata = [])
 * @method static bool has(\Illuminate\Database\Eloquent\Model|string $subject, string $purpose, ?string $version = null)
 * @method static \JeffersonGoncalves\GdprConsent\Models\ConsentRecord|null latest(\Illuminate\Database\Eloquent\Model|string $subject, string $purpose)
 * @method static \Illuminate\Database\Eloquent\Collection<int, \JeffersonGoncalves\GdprConsent\Models\ConsentRecord> history(\Illuminate\Database\Eloquent\Model|string $subject, ?string $purpose = null)
 * @method static \Illuminate\Database\Eloquent\Collection<int, \JeffersonGoncalves\GdprConsent\Models\ConsentRecord> claim(string $anonymousId, \Illuminate\Database\Eloquent\Model $subject)
 *
 * @see ConsentManager
 */
class GdprConsent extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConsentManager::class;
    }
}
