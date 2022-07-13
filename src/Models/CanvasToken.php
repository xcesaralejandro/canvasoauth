<?php

namespace xcesaralejandro\canvasoauth\Models;

use Illuminate\Database\Eloquent\Model;

class CanvasToken extends Model
{

    protected $table = 'canvas_tokens';

    protected $fillable = ['user_id', 'user_global_id', 'access_token', 'token_type',
                        'refresh_token', 'expires_in', 'expires_at', 'real_user_id', 
                        'real_user_global_id'];
    
}
