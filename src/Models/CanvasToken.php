<?php

namespace xcesaralejandro\canvasoauth\Models;

use App\Http\Controllers\CanvasOauthController;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use xcesaralejandro\canvasoauth\Facades\CanvasOauth;

class CanvasToken extends Model
{

    protected $table = 'canvas_tokens';
    private $leeway = 120;

    protected $fillable = ['user_id', 'user_global_id', 'access_token', 'token_type', 'refresh_token', 
    'expires_in', 'expires_at', 'real_user_id', 'real_user_global_id'];
    
    public function scopeExistsForUser(mixed $query, int $user_id) : string {
        $instance = $query->where('user_id', $user_id)->first();
        return !empty($instance);
    }

    public function scopeGetForUser(mixed $query, int $user_id) : string {
        $instance = $query->where('user_id', $user_id)->firstOrFail();
        if($instance->tokenIsExpired()){
            $instance->renewToken();
        }
        return $instance->access_token;
    }

    public function tokenIsExpired() : bool {
        $expired_at =  $this->updated_at->timestamp + ($this->expires_in - $this->leeway);
        $now = Carbon::now()->timestamp;
        return $now >= $expired_at;
    }

    public function renewToken() : string {
        Log::debug('[CanvasToken] [renewToken] Trying renew token.');
        try{
            $url = CanvasOauth::getTokenUrl();
            $client = new Client();
            $params = [
                'grant_type' => 'refresh_token',
                'client_id' => CanvasOauth::getClientId(),
                'client_secret' => CanvasOauth::getClientSecret(),
                'redirect_uri' => route('canvasoauth.code_exchange'),
                'refresh_token' => $this->refresh_token
            ];
            $verify_https = config('canvasoauth.VERIFY_SELF_SIGNED_HTTPS');
            $res = $client->request('POST', $url, ['form_params' => $params, 'verify' => $verify_https]);
            if($res->getStatusCode() == 200){
                $payload = json_decode($res->getBody()->getContents());
                $this->access_token = $payload->access_token;
                $this->token_type = $payload->token_type;
                $this->expires_in = $payload->expires_in;
                $this->update();
                Log::debug('[CanvasToken] [renewToken] Token renewed.');
            }else{
                throw new \Exception('Error trying get new token. The response status from canvas is not 200.');
            }
        }catch(\Exception $e){
            return (new CanvasOauthController())->onRenewTokenError($e);
        }
        return $this->access_token;
    }
}
