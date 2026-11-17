<?php

namespace JeffersonGoncalves\GdprConsent\Enums;

enum ConsentAction: string
{
    case Granted = 'granted';
    case Revoked = 'revoked';
}
