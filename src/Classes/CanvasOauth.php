<?php
namespace xcesaralejandro\canvasoauth\Classes;

use App\Models\CanvasToken;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class CanvasOauth {

    const AUTH_RESPONSE_TYPE = 'code';

    public function __construct(){
        $this->assertConfigWasSet();
    }

    public function getInitialAuthenticationUrl() : string {
        $auth_url = $this->getAuthUrl(); 
        $client_id = $this->getClientId();
        $response_type = self::AUTH_RESPONSE_TYPE;
        $redirect_uri = route('canvasoauth.code_exchange');
        $url = "{$auth_url}?client_id={$client_id}&response_type={$response_type}&redirect_uri={$redirect_uri}";
        return $url;
    }

    public function getAuthUrl() : string {
        return "{$this->getCanvasUrl()}/login/oauth2/auth";
    }

    public function getTokenUrl() : string {
        return "{$this->getCanvasUrl()}/login/oauth2/token";
    }

    public function getCanvasUrl() : ?string {
        return config('canvasoauth.CANVAS_DOMAIN_URL');
    }

    public function getClientId() : null | string | int {
        return config('canvasoauth.CANVAS_CLIENT_ID');
    }

    public function getClientSecret() : ?string {
        return config('canvasoauth.CANVAS_CLIENT_SECRET');
    }

    private function assertConfigWasSet() : void {
        $config_keys = ['CANVAS_DOMAIN_URL', 'CANVAS_CLIENT_ID', 'CANVAS_CLIENT_SECRET'];
        foreach($config_keys as $key){
            $config_value =  config("canvasoauth.{$key}");
            if(!isset($config_value) || empty($config_value)){
                throw new \Exception("The config key: {$key} is required. Please set it.");
            }
        }
    }

} 