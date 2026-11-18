<?php

namespace JeffersonGoncalves\GdprConsent;

use JeffersonGoncalves\GdprConsent\Events\ConsentRecorded;
use JeffersonGoncalves\GdprConsent\Models\ConsentRecord;
use JeffersonGoncalves\Privacy\Consent\ConsentManager as BaseConsentManager;

/**
 * Append-only consent ledger. A subject is an Eloquent model (a user) or a string anonymous id (a visitor).
 */
class ConsentManager extends BaseConsentManager
{
    protected string $config = 'gdpr-consent';

    protected string $model = ConsentRecord::class;

    protected string $event = ConsentRecorded::class;
}
