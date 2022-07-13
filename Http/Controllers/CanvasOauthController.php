<?php 
namespace xcesaralejandro\canvasoauth\Http\Controllers;

use App\Models\CanvasToken;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use xcesaralejandro\canvasoauth\Facades\CanvasOauth;

class CanvasOauthController {

    const AUTH_RESPONSE_TYPE = 'code';


    public function onRejectedPermission(Request $request) : mixed {
        Log::debug('[CanvasOauthController] [onRejectedPermission] Permission rejected.', $request->all());
        echo 'Permission rejected.';
        return null;
    }

    public function onError(\Exception $exception) : mixed {
        Log::error('[CanvasOauthController] [onError] Something went wrong during the credential exchange', [$exception->getMessage()]);
        throw new \Exception($exception);
    }

    public function codeExchange(Request $request){
        Log::debug('[CanvasOauthController] [codeExchange] Trying code exchange.', $request->all());
        try{
            if($this->permissionWasRejected($request)){
                return $this->onRejectedPermission($request);
            }
            $client = new Client();
            $options = ['form_params' => CanvasOauth::buildTokenRequestParams($request->code)];
            $res = $client->request('POST', CanvasOauth::getTokenUrl(), $options);
            $payload = json_decode($res->getBody()->getContents());
            Log::debug('[CanvasOauthController] [codeExchange] Canvas response for code exchange.', [json_encode($payload)]);
            $this->saveToken($payload);
        }catch(\Exception $e){
            return $this->onError($e);
        }
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