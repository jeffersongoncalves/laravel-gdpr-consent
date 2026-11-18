<?php

namespace JeffersonGoncalves\GdprConsent\Events;

use JeffersonGoncalves\Privacy\Consent\ConsentRecorded as BaseConsentRecorded;

/**
 * Fired after each grant or revoke ($record is a JeffersonGoncalves\GdprConsent\Models\ConsentRecord).
 */
class ConsentRecorded extends BaseConsentRecorded {}
