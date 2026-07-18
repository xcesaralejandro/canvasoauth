<?php

namespace xcesaralejandro\canvasoauth\Models;

use App\Http\Controllers\CanvasOauthController;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CanvasToken extends Model
{

    protected $table = 'canvas_tokens';
    private const LEEWAY_SECONDS = 120;

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
        $expiredAt = $this->updated_at->timestamp + ($this->expires_in - self::LEEWAY_SECONDS);
        return Carbon::now()->timestamp >= $expiredAt;
    }

    public function renew(): ?string
    {
        $renewed = null;
        $user = CanvasUser::with('client')->findOrFail($this->canvas_user_id);
        Log::debug('[CanvasToken] [renew] Trying renew token.');
        try {
            $url = $user->client->getTokenUrl();
            $client = new Client();
            $response = Http::asForm()->post($url, [
                'grant_type' => 'refresh_token',
                'client_id' => $user->client->client_id,
                'client_secret' => $user->client->client_secret,
                'redirect_uri' => route('canvas-oauth.callback'),
                'refresh_token' => $this->refresh_token,
                'state' => $user->client->code
            ]);
            if ($response->successful()) {
                $payload = $response->object();
                $this->update([
                    'access_token' => $payload->access_token,
                    'token_type' => $payload->token_type,
                    'expires_in' => $payload->expires_in,
                ]);
                Log::debug('[CanvasToken] [renew] Token renovado exitosamente.');
                return $payload->access_token;
            }
            throw new \Exception("Canvas API respondió con estado: {$response->status()}");
        } catch (\Exception $e) {
            $this->delete();
        }
        return $renewed;
    }
}
