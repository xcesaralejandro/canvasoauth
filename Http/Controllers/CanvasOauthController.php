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

    public function onFinish(AuthenticatedUser $user) : mixed {
        Log::debug('[CanvasOauthController] [onFinish] Token was stored successfully.', [json_encode($user)]);
        return null;
    }

    public function onRejectedPermission(Request $request) : mixed {
        Log::debug('[CanvasOauthController] [onRejectedPermission] Permission rejected.', $request->all());
        return null;
    }

    public function onError(\Exception $exception) : mixed {
        Log::error('[CanvasOauthController] [onError] Something went wrong during the credential exchange', [$exception->getMessage()]);
        throw new \Exception($exception);
    }

    public function onRenewTokenError(\Exception $exception) : mixed {
        Log::error('[CanvasOauthController] [onRenewTokenError] Something went wrong trying to renew the user token', [$exception->getMessage()]);
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
            $options = ['form_params' => $params];
            $res = $client->request('POST', CanvasOauth::getTokenUrl(), $options);
            $payload = json_decode($res->getBody()->getContents());
            Log::debug('[CanvasOauthController] [codeExchange] Canvas response for code exchange.', [json_encode($payload)]);
            $this->saveToken($payload);
            $user = $this->getAuthenticatedUser($payload);
            return $this->onFinish($user);
        }catch(\Exception $e){
            return $this->onError($e);
        }
    }

    private function getAuthenticatedUser(object $payload) : AuthenticatedUser {
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

    private function saveToken(object $payload) : void {
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

    private function permissionWasRejected(Request $request){
        return !isset($request->code);
    }

}