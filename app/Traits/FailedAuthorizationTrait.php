<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;

trait FailedAuthorizationTrait
{
    protected function failedAuthorization()
    {
        throw new AuthorizationException("You don't have permission for this action.");
    }
}
