<?php

namespace JeffersonGoncalves\GdprConsent\Models;

use JeffersonGoncalves\Gdpr\Attributes\PersonalData;
use JeffersonGoncalves\Gdpr\Concerns\InteractsWithPersonalData;
use JeffersonGoncalves\Gdpr\Contracts\HasPersonalData;
use JeffersonGoncalves\Gdpr\Enums\LegalBasis;
use JeffersonGoncalves\GdprConsent\Enums\ConsentAction;
use JeffersonGoncalves\Privacy\Consent\ConsentRecord as BaseConsentRecord;

/**
 * One immutable consent event. The ledger is append-only: a new decision is a new row.
 * Only the personal data columns may change, so the person can still be anonymized.
 *
 * @property ConsentAction $action
 */
#[PersonalData('ip_address', purpose: 'Proof of consent (Art. 7(1))', legalBasis: LegalBasis::LegalObligation)]
#[PersonalData('user_agent', purpose: 'Proof of consent (Art. 7(1))', legalBasis: LegalBasis::LegalObligation)]
class ConsentRecord extends BaseConsentRecord implements HasPersonalData
{
    use InteractsWithPersonalData;

    protected string $actionEnum = ConsentAction::class;

    public function getTable(): string
    {
        return config('gdpr-consent.table', 'gdpr_consents');
    }
}
