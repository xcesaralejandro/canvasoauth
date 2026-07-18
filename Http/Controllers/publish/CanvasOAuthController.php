<?php

namespace App\Http\Controllers;

use xcesaralejandro\canvasoauth\DataStructures\AuthenticatedUser;
use xcesaralejandro\canvasoauth\Http\Controllers\CanvasOAuthController as CanvasOAuthControllerBase;
use Illuminate\Http\Request;

class CanvasOAuthController extends CanvasOAuthControllerBase
{

    public function onPermissionGranted(AuthenticatedUser $user, Request $request): mixed
    {
        return parent::onPermissionGranted($user, $request); // you can skip this, only creates debug log :)
        // At this point the oauth flow has finished successfully and the user has granted permissions.
    }

    public function onPermissionDenied(Request $request): mixed
    {
        return parent::onPermissionDenied($request); // you can skip this, only creates debug log :)
        // At this point the user has canceled the grant of permissions.
    }

    public function onError(\Exception $exception): mixed
    {
        return parent::onError($exception); // you can skip this, only creates debug log
        // Any error that may arise during the oauth flow will be thrown here
    }
}
