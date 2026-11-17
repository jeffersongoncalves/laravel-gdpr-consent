<?php

namespace JeffersonGoncalves\GdprConsent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use JeffersonGoncalves\Gdpr\Attributes\PersonalData;
use JeffersonGoncalves\Gdpr\Concerns\InteractsWithPersonalData;
use JeffersonGoncalves\Gdpr\Contracts\HasPersonalData;
use JeffersonGoncalves\Gdpr\Enums\LegalBasis;
use JeffersonGoncalves\GdprConsent\Enums\ConsentAction;
use LogicException;

/**
 * One immutable consent event. The ledger is append-only: a new decision is a new row.
 * Only the personal data columns may change, so a data subject can still be anonymized.
 *
 * @property ConsentAction $action
 * @property string $purpose
 * @property string|null $version
 * @property Carbon|null $expires_at
 */
#[PersonalData('ip_address', purpose: 'Proof of consent (Art. 7(1))', legalBasis: LegalBasis::LegalObligation)]
#[PersonalData('user_agent', purpose: 'Proof of consent (Art. 7(1))', legalBasis: LegalBasis::LegalObligation)]
class ConsentRecord extends Model implements HasPersonalData
{
    use InteractsWithPersonalData;

    public const UPDATED_AT = null;

    protected $guarded = [];

    public function getTable(): string
    {
        return config('gdpr-consent.table', 'gdpr_consents');
    }

    protected function casts(): array
    {
        return [
            'action' => ConsentAction::class,
            'expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (ConsentRecord $record): void {
            $allowed = array_map(fn (PersonalData $field): string => $field->column, static::personalDataFields());

            if (array_diff(array_keys($record->getDirty()), $allowed) !== []) {
                throw new LogicException('Consent records are append-only: record a new grant or revoke instead.');
            }
        });

        static::deleting(function (): void {
            throw new LogicException('Consent records are append-only and cannot be deleted. Anonymize them instead.');
        });
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function isActive(): bool
    {
        return $this->action === ConsentAction::Granted
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
