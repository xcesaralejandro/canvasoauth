<?php 
namespace App\Http\Controllers;

use xcesaralejandro\canvasoauth\DataStructures\AuthenticatedUser;
use xcesaralejandro\canvasoauth\Http\Controllers\CanvasOauthController as CanvasOauthControllerBase;
use Illuminate\Http\Request;

class CanvasOauthController extends CanvasOauthControllerBase {

    public function onFinish(AuthenticatedUser $user, Request $request) : mixed {
        return parent::onFinish($user, $request); // you can skip this, only creates debug log :)
        // At this point the oauth flow has finished successfully and the user has granted permissions.
    }

    public function onRejectedPermission(Request $request) : mixed {
        return parent::onRejectedPermission($request); // you can skip this, only creates debug log :)
        // At this point the user has canceled the grant of permissions.
    }

    public function onError(\Exception $exception) : mixed {
        return parent::onError($exception); // you can skip this, only creates debug log
        // Any error that may arise during the oauth flow will be thrown here
    }

    public function onRenewTokenError(\Exception $exception, int $canvas_token_id) : mixed {
        return parent::onRenewTokenError($exception); // you can skip this, only creates debug log
        // Any errors that may arise during the renewal of a token for the user
    } 

}