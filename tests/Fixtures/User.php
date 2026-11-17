<?php

namespace JeffersonGoncalves\GdprConsent\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use JeffersonGoncalves\GdprConsent\Concerns\HasConsents;

class User extends Authenticatable
{
    use HasConsents;

    protected $guarded = [];

    public $timestamps = false;
}
