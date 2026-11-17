<?php

namespace JeffersonGoncalves\GdprConsent\Events;

use JeffersonGoncalves\GdprConsent\Models\ConsentRecord;

class ConsentRecorded
{
    public function __construct(public ConsentRecord $record) {}
}
