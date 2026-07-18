<?php

namespace xcesaralejandro\canvasoauth\Models;

use App\Http\Controllers\CanvasOauthController;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CanvasToken extends Model
{

    protected $table = 'canvas_tokens';
    private $leeway = 120;

    protected $fillable = [
        'canvas_user_id',
        'access_token',
        'token_type',
        'refresh_token',
        'expires_in'
    ];

    public function user()
    {
        return $this->belongsTo(CanvasUser::class, 'canvas_user_id', 'id');
    }

    public function freshToken(): ?string
    {
        $access_token = $this->access_token;
        if (empty($access_token) || $this->isExpired()) {
            $access_token = $this->renew();
        }
        return $access_token;
    }

    public function isExpired(): bool
    {
        return true;
        $expired_at =  $this->updated_at->timestamp + ($this->expires_in - $this->leeway);
        $now = Carbon::now()->timestamp;
        return $now >= $expired_at;
    }

    public function renew(): ?string
    {
        $renewed = null;
        $user = CanvasUser::with('client')->where('id', $this->canvas_user_id)->firstOrFail();
        Log::debug('[CanvasToken] [renew] Trying renew token.');
        try {
            $url = $user->client->getTokenUrl();
            $client = new Client();
            $params = [
                'grant_type' => 'refresh_token',
                'client_id' => $user->client->client_id,
                'client_secret' => $user->client->client_secret,
                'redirect_uri' => route('canvas-oauth.callback'),
                'refresh_token' => $this->refresh_token,
                'state' => $user->client->code
            ];
            $res = $client->request('POST', $url, ['form_params' => $params]);
            if ($res->getStatusCode() == 200) {
                $payload = json_decode($res->getBody()->getContents());
                $this->access_token = $payload->access_token;
                $this->token_type = $payload->token_type;
                $this->expires_in = $payload->expires_in;
                $this->update();
                $renewed = $payload->access_token;
                Log::debug('[CanvasToken] [renew] Token renewed.');
            } else {
                return throw new \Exception('Error trying get new token. The response status from canvas is not 200.');
            }
        } catch (\Exception $e) {
            $this->delete();
        }
        return $renewed;
    }
}
