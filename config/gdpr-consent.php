<?php

return [

    'table' => 'gdpr_consents',

    /*
     * Known purposes and the current version of their terms. When set, unknown
     * purposes are rejected, and bumping a version invalidates older consents
     * (has() returns false until the subject consents again).
     *
     * 'marketing' => ['version' => '2026-11', 'expires_after' => '1 year'],
     */
    'purposes' => [],

    /*
     * Store IP address and user agent as proof of consent (GDPR Art. 7(1): the controller must be able to demonstrate consent).
     */
    'store_request' => true,

    /*
     * Optional endpoint for front-end banners: POST {purpose, granted}.
     * Guests are identified by an encrypted cookie holding a random id.
     */
    'route' => [
        'enabled' => false,
        'path' => 'gdpr/consent',
        'middleware' => ['web', 'throttle:60,1'],
        'cookie' => 'gdpr_consent_id',
        // On login, copy the visitor's latest decisions (by cookie id) onto the user.
        'claim_on_login' => true,
    ],

];
