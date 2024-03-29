<?php
namespace xcesaralejandro\canvasoauth\Http\Controllers;

use App\Models\CanvasToken;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use xcesaralejandro\canvasoauth\DataStructures\AuthenticatedUser;
use xcesaralejandro\canvasoauth\DataStructures\CanvasUser;
use xcesaralejandro\canvasoauth\Facades\CanvasOauth;

class CanvasOauthController {

    public function onFinish(AuthenticatedUser $user, Request $request) : mixed {
        Log::debug('[CanvasOauthController] [onFinish] Token was stored successfully.', [json_encode($user), $request->all()]);
        return 'onFinish() method.';
    }

    public function onRejectedPermission(Request $request) : mixed {
        Log::debug('[CanvasOauthController] [onRejectedPermission] Permission rejected.', $request->all());
        return 'onRejectedPermission() method.';
    }

    public function onError(\Exception $exception) : mixed {
        Log::error('[CanvasOauthController] [onError] Something went wrong during the credential exchange', [$exception->getMessage()]);
        throw new \Exception($exception);
    }

    public function codeExchange(Request $request) : mixed {
        Log::debug('[CanvasOauthController] [codeExchange] Trying code exchange.', $request->all());
        try{
            if($this->permissionWasRejected($request)){
                return $this->onRejectedPermission($request);
            }
            $client = new Client();
            $params = [
                "client_id" => CanvasOauth::getClientId(),
                "client_secret" => CanvasOauth::getClientSecret(),
                "code" => $request->code];
            $verify_https = config('canvasoauth.VERIFY_SELF_SIGNED_HTTPS');
            $options = ['form_params' => $params, 'verify' => $verify_https];
            $res = $client->request('POST', CanvasOauth::getTokenUrl(), $options);
            $payload = json_decode($res->getBody()->getContents());
            Log::debug('[CanvasOauthController] [codeExchange] Canvas response for code exchange.', [json_encode($payload)]);
            $this->saveToken($payload);
            $user = $this->getAuthenticatedUser($payload);
            return $this->onFinish($user, $request);
        }catch(\Exception $e){
            return $this->onError($e);
        }
    }

    protected function getAuthenticatedUser(object $payload) : AuthenticatedUser {
        Log::debug('[CanvasOauthController] [getAuthenticatedUser] Filling authenticated user.');
        $authenticated = new AuthenticatedUser();
        $authenticated->standard->id = $payload->user->id;
        $authenticated->standard->name = $payload->user->name;
        $authenticated->standard->global_id = $payload->user->global_id;
        $authenticated->standard->effective_locale = $payload->user->effective_locale ?? null;
        if(isset($payload->real_user)){
            $authenticated->supplanted_by = new CanvasUser();
            $authenticated->supplanted_by->id = $payload->real_user->id;
            $authenticated->supplanted_by->name = $payload->real_user->name;
            $authenticated->supplanted_by->global_id = $payload->real_user->global_id;
            $authenticated->supplanted_by->effective_locale = $payload->real_user->effective_locale ?? null;
        }
        Log::debug('[CanvasOauthController] [getAuthenticatedUser] User fields was filled.');
        return $authenticated;
    }

    protected function saveToken(object $payload) : void {
        Log::debug('[CanvasOauthController] [saveToken] Triying save the token...');
        $fields = [
            'user_global_id' => $payload->user->global_id,
            'access_token' => $payload->access_token,
            'token_type' => $payload->token_type,
            'refresh_token' => $payload->refresh_token,
            'expires_in' => $payload->expires_in];
        $conditions = ['user_id' => $payload->user->id];
        $result = CanvasToken::updateOrCreate($conditions, $fields);
        Log::debug('[CanvasOauthController] [saveToken] Token was created or update.', [$result]);
    }

    protected function permissionWasRejected(Request $request){
        return !isset($request->code);
    }

}
