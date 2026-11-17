<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use JeffersonGoncalves\GdprConsent\Enums\ConsentAction;
use JeffersonGoncalves\GdprConsent\Events\ConsentRecorded;
use JeffersonGoncalves\GdprConsent\Facades\GdprConsent;
use JeffersonGoncalves\GdprConsent\Models\ConsentRecord;
use JeffersonGoncalves\GdprConsent\Tests\Fixtures\User;

function user(): User
{
    return User::create(['name' => 'Maria']);
}

it('grants and revokes, keeping the full history', function () {
    Event::fake([ConsentRecorded::class]);
    $user = user();

    expect($user->hasConsent('marketing'))->toBeFalse();

    $user->grantConsent('marketing', source: 'signup');
    expect($user->hasConsent('marketing'))->toBeTrue();

    $user->revokeConsent('marketing');
    expect($user->hasConsent('marketing'))->toBeFalse()
        ->and(GdprConsent::history($user, 'marketing')->pluck('action')->all())->toBe([ConsentAction::Granted, ConsentAction::Revoked])
        ->and($user->consents)->toHaveCount(2);

    Event::assertDispatchedTimes(ConsentRecorded::class, 2);
});

it('invalidates consent when the terms version is bumped', function () {
    config()->set('gdpr-consent.purposes', ['marketing' => ['version' => 'v1']]);
    $user = user();
    $user->grantConsent('marketing');

    expect($user->hasConsent('marketing'))->toBeTrue()
        ->and($user->consents()->first()->version)->toBe('v1');

    config()->set('gdpr-consent.purposes.marketing.version', 'v2');

    expect($user->hasConsent('marketing'))->toBeFalse();
});

it('expires consent after the configured period', function () {
    config()->set('gdpr-consent.purposes', ['marketing' => ['expires_after' => '1 year']]);
    $user = user();
    $user->grantConsent('marketing');

    $this->travel(13)->months();

    expect($user->hasConsent('marketing'))->toBeFalse();
});

it('rejects unknown purposes once purposes are configured', function () {
    config()->set('gdpr-consent.purposes', ['marketing' => []]);

    user()->grantConsent('newsletter');
})->throws(InvalidArgumentException::class);

it('records anonymous visitors by id', function () {
    GdprConsent::grant('visitor-1', 'analytics');

    expect(GdprConsent::has('visitor-1', 'analytics'))->toBeTrue()
        ->and(GdprConsent::has('visitor-2', 'analytics'))->toBeFalse();
});

it('is append-only but can be anonymized', function () {
    $record = user()->grantConsent('marketing');

    expect(fn () => $record->update(['action' => ConsentAction::Revoked]))->toThrow(LogicException::class)
        ->and(fn () => $record->delete())->toThrow(LogicException::class);

    $record->refresh()->anonymizePersonalData();

    expect($record->fresh()->ip_address)->toBeNull()
        ->and($record->fresh()->action)->toBe(ConsentAction::Granted);
});

it('stores guest choices from the endpoint with a cookie id', function () {
    $response = $this->withoutMiddleware(VerifyCsrfToken::class)
        ->post('/gdpr/consent', ['purpose' => 'analytics', 'granted' => true])
        ->assertNoContent()
        ->assertCookie('gdpr_consent_id');

    $id = $response->getCookie('gdpr_consent_id')->getValue();
    $record = ConsentRecord::sole();

    expect($record->anonymous_id)->toBe($id)
        ->and($record->ip_address)->toBe('127.0.0.1')
        ->and($record->source)->toBe('route');

    // Returning visitor keeps the same id.
    $this->withoutMiddleware(VerifyCsrfToken::class)
        ->withCookie('gdpr_consent_id', $id)
        ->post('/gdpr/consent', ['purpose' => 'analytics', 'granted' => false])
        ->assertNoContent();

    expect(GdprConsent::has($id, 'analytics'))->toBeFalse()
        ->and(GdprConsent::history($id))->toHaveCount(2);
});

it('claims a visitor\'s latest decisions for the user, once', function () {
    GdprConsent::grant('visitor-1', 'analytics');
    GdprConsent::revoke('visitor-1', 'analytics');
    GdprConsent::grant('visitor-1', 'marketing');
    $user = user();
    $user->grantConsent('newsletter');

    $claimed = GdprConsent::claim('visitor-1', $user);

    expect($claimed)->toHaveCount(2)
        ->and($user->hasConsent('analytics'))->toBeFalse()
        ->and($user->hasConsent('marketing'))->toBeTrue()
        ->and(GdprConsent::latest($user, 'marketing'))
        ->source->toBe('claimed')
        ->metadata->toMatchArray(['anonymous_id' => 'visitor-1'])
        ->ip_address->toBe('127.0.0.1')
        ->and(GdprConsent::history('visitor-1'))->toHaveCount(3)
        ->and(GdprConsent::claim('visitor-1', $user))->toHaveCount(0);
});

it('keeps the user\'s own later decision over the visitor\'s', function () {
    GdprConsent::grant('visitor-1', 'marketing');
    $user = user();
    $user->revokeConsent('marketing');

    expect(GdprConsent::claim('visitor-1', $user))->toHaveCount(0)
        ->and($user->hasConsent('marketing'))->toBeFalse();
});

it('orders claims by the original decision, across visitor ids', function () {
    GdprConsent::grant('device-a', 'analytics');
    GdprConsent::revoke('device-b', 'analytics');
    $user = user();

    GdprConsent::claim('device-a', $user);
    expect($user->hasConsent('analytics'))->toBeTrue();

    // device-b decided after device-a: its revoke wins even though it is claimed later.
    expect(GdprConsent::claim('device-b', $user))->toHaveCount(1)
        ->and($user->hasConsent('analytics'))->toBeFalse()
        ->and(GdprConsent::claim('device-a', $user))->toHaveCount(0)
        ->and($user->hasConsent('analytics'))->toBeFalse();
});

it('claims the cookie id on login', function () {
    GdprConsent::grant('visitor-1', 'analytics');
    $user = user();

    Route::middleware('web')->post('/test-login', function () use ($user) {
        Auth::login($user);

        return response()->noContent();
    });

    $this->withoutMiddleware(VerifyCsrfToken::class)
        ->withCookie('gdpr_consent_id', 'visitor-1')
        ->post('/test-login')
        ->assertNoContent();

    expect($user->hasConsent('analytics'))->toBeTrue();
});

it('stores logged-in choices on the user', function () {
    $user = user();

    $this->actingAs($user)
        ->withoutMiddleware(VerifyCsrfToken::class)
        ->post('/gdpr/consent', ['purpose' => 'analytics', 'granted' => false])
        ->assertNoContent();

    expect($user->consents()->sole()->action)->toBe(ConsentAction::Revoked);
});
