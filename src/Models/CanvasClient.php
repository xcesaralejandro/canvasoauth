<?php

namespace xcesaralejandro\canvasoauth\Models;

use Illuminate\Database\Eloquent\Model;

class CanvasClient extends Model
{
    const AUTH_RESPONSE_TYPE = 'code';

    protected $table = 'canvas_clients';

    protected $fillable = ['code', 'url', 'client_id', 'client_secret'];

    protected function code(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? mb_strtolower($value, 'UTF-8') : null,
            set: fn(?string $value) => $value ? mb_strtolower($value, 'UTF-8') : null,
        );
    }

    public function users()
    {
        return $this->hasMany(CanvasUser::class, 'canvas_client_id');
    }

    public function getAuthUrl(): string
    {
        return "{$this->url}/login/oauth2/auth";
    }

    public function getTokenUrl(): string
    {
        return "{$this->url}/login/oauth2/token";
    }

    public function getAuthorizationUrl(): string
    {
        $query = http_build_query([
            'client_id' => $this->client_id,
            'response_type' => self::AUTH_RESPONSE_TYPE,
            'redirect_uri' => route('canvas-oauth.callback'),
            'state' => $this->code,
        ]);
        return "{$this->getAuthUrl()}?{$query}";
    }
}
